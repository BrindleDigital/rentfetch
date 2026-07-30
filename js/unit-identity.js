(function ($, document) {
	'use strict';

	function bindDialog(dialog, onConfirm, onCancel) {
		var cancelButton;
		var confirmButton;

		if (!dialog) {
			return;
		}

		cancelButton = dialog.querySelector('[data-rf-dialog-cancel]');
		confirmButton = dialog.querySelector('[data-rf-dialog-confirm]');

		function cancel() {
			dialog.close();
			if ('function' === typeof onCancel) {
				onCancel();
			}
		}

		if (cancelButton) {
			cancelButton.addEventListener('click', cancel);
		}

		if (confirmButton) {
			confirmButton.addEventListener('click', function () {
				dialog.close();
				onConfirm();
			});
		}

		dialog.addEventListener('click', function (event) {
			if (event.target === dialog) {
				cancel();
			}
		});

		dialog.addEventListener('cancel', function (event) {
			event.preventDefault();
			cancel();
		});
	}

	function openDialog(dialog, fallbackMessage, onConfirm) {
		if (dialog && 'function' === typeof dialog.showModal) {
			dialog.showModal();
			return;
		}

		if (window.confirm(fallbackMessage)) {
			onConfirm();
		}
	}

	function initProtectedIdentityFields($identity) {
		var $source = $identity.find('#unit_source');
		var $sourceConfirmed = $identity.find(
			'[data-rf-unit-source-confirmed]'
		);
		var $unitId = $identity.find('#unit_id');
		var $unitIdConfirmed = $identity.find('[data-rf-unit-id-confirmed]');
		var sourceDialog = document.querySelector('[data-rf-unit-source-dialog]');
		var unitIdDialog = document.querySelector('[data-rf-unit-id-dialog]');

		function protect(
			$field,
			$confirmed,
			dialog,
			message,
			onUnlock,
			required
		) {
			if (!$field.length || !required) {
				return;
			}

			function requestUnlock(event) {
				if ('1' === $confirmed.val()) {
					return;
				}

				event.preventDefault();
				$field.trigger('blur');
				openDialog(dialog, message, onUnlock);
			}

			$field.on('pointerdown click', requestUnlock);
			$field.on('keydown', function (event) {
				if (
					'Enter' === event.key ||
					' ' === event.key ||
					'ArrowDown' === event.key ||
					'ArrowUp' === event.key
				) {
					requestUnlock(event);
				}
			});
		}

		function unlockSource() {
			$sourceConfirmed.val('1');
			$source.removeAttr('aria-readonly aria-haspopup').trigger('focus');
		}

		function unlockUnitId() {
			$unitIdConfirmed.val('1');
			$unitId
				.prop('readonly', false)
				.removeAttr('aria-readonly aria-haspopup')
				.trigger('focus')
				.trigger('select');
		}

		bindDialog(sourceDialog, unlockSource);
		bindDialog(unitIdDialog, unlockUnitId);
		protect(
			$source,
			$sourceConfirmed,
			sourceDialog,
			'Changing the unit source can affect syncing and which fields are controlled by an integration.',
			unlockSource,
			true
		);
		protect(
			$unitId,
			$unitIdConfirmed,
			unitIdDialog,
			'Changing the Unit ID can disconnect synced API data or cause the unit to be recreated.',
			unlockUnitId,
			Boolean($unitId.val())
		);
	}

	function initRelationships($identity) {
		var $propertyCombobox = $identity.find(
			'[data-rf-unit-property-relationship]'
		);
		var $floorplanCombobox = $identity.find(
			'[data-rf-unit-floorplan-relationship]'
		);
		var $propertySelect = $propertyCombobox.find('#property_id');
		var $floorplanSelect = $floorplanCombobox.find('#floorplan_id');
		var $confirmed = $identity.find(
			'[data-rf-unit-relationship-confirmed]'
		);
		var dialog = document.querySelector('[data-rf-unit-relationship-dialog]');
		var originalPropertyId = $propertySelect.val();
		var originalFloorplanId = $floorplanSelect.val();
		var relationshipsUnlocked =
			!originalPropertyId && !originalFloorplanId;
		var resetSearches = function () {};

		function unlockRelationships() {
			relationshipsUnlocked = true;
			$confirmed.val('1');
			$propertyCombobox
				.find('.rf-property-relationship-search')
				.trigger('focus')
				.autocomplete('search', '');
		}

		function restoreRelationships() {
			$propertySelect.val(originalPropertyId);
			$floorplanSelect.val(originalFloorplanId);
			resetSearches();
		}

		function requestUnlock(event) {
			if (relationshipsUnlocked) {
				return true;
			}

			event.preventDefault();
			event.stopImmediatePropagation();
			openDialog(
				dialog,
				'Changing this unit’s Property ID or Floor Plan ID moves it in the hierarchy and may affect synced records.',
				unlockRelationships
			);
			return false;
		}

		function enhanceCombobox($combobox, filterChoices, afterSelect) {
			var $select = $combobox.find('select');
			var $search = $combobox.find('.rf-property-relationship-search');
			var $toggle = $combobox.find('.rf-property-relationship-toggle');
			var choices;

			if (
				!$combobox.length ||
				!$select.length ||
				!$search.length ||
				'function' !== typeof $search.autocomplete
			) {
				return function () {};
			}

			choices = $select
				.find('option[value!=""]')
				.map(function () {
					return {
						label: $(this).text(),
						value: this.value,
						propertyId: $(this).attr('data-property-id') || '',
					};
				})
				.get();

			function selectedLabel() {
				return $select.find('option:selected').text().trim();
			}

			function reset() {
				$search.val($select.val() ? selectedLabel() : '');
			}

			$search
				.prop('hidden', false)
				.prop('required', $select.prop('required'))
				.autocomplete({
					appendTo: document.body,
					delay: 0,
					minLength: 0,
					source: function (request, response) {
						var matcher = new RegExp(
							$.ui.autocomplete.escapeRegex(request.term),
							'i'
						);
						var available = choices.filter(filterChoices);

						response(
							available.filter(function (choice) {
								return matcher.test(choice.label);
							})
						);
					},
					select: function (event, ui) {
						$select.val(ui.item.value).trigger('change');
						$search.val(ui.item.label);
						if ('function' === typeof afterSelect) {
							afterSelect(ui.item);
						}
						return false;
					},
					classes: {
						'ui-autocomplete': 'rf-property-relationship-menu',
					},
				})
				.on('pointerdown', requestUnlock)
				.on('keydown', function (event) {
					if (
						'Enter' === event.key ||
						' ' === event.key ||
						'ArrowDown' === event.key ||
						'ArrowUp' === event.key
					) {
						requestUnlock(event);
					}
				})
				.on('focus', function () {
					this.select();
				})
				.on('blur', function () {
					window.setTimeout(reset, 100);
				});

			$toggle.prop('hidden', false).on('click', function (event) {
				if (false === requestUnlock(event)) {
					return;
				}
				$search.trigger('focus').autocomplete('search', '');
			});

			$select.prop('required', false).attr({
				'aria-hidden': 'true',
				tabindex: '-1',
			});
			$combobox.addClass('is-enhanced');
			reset();

			return reset;
		}

		var resetProperty = enhanceCombobox(
			$propertyCombobox,
			function () {
				return true;
			},
			function () {
				var selectedFloorplan = $floorplanSelect.find(
					'option:selected'
				);

				if (
					$floorplanSelect.val() &&
					selectedFloorplan.attr('data-property-id') !==
						$propertySelect.val()
				) {
					$floorplanSelect.val('');
					resetSearches();
				}
			}
		);
		var resetFloorplan = enhanceCombobox(
			$floorplanCombobox,
			function (choice) {
				return (
					!$propertySelect.val() ||
					choice.propertyId === $propertySelect.val()
				);
			}
		);

		resetSearches = function () {
			resetProperty();
			resetFloorplan();
		};

		bindDialog(dialog, unlockRelationships, restoreRelationships);
	}

	$(function () {
		var $identity = $('[data-rf-unit-identity]');

		if (!$identity.length) {
			return;
		}

		initProtectedIdentityFields($identity);
		initRelationships($identity);
	});
})(jQuery, document);
