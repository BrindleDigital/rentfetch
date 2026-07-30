(function ($, document) {
	'use strict';

	function initProtectedFields($identity) {
		var $floorplanSource = $identity.find('#floorplan_source');
		var $floorplanSourceConfirmed = $identity.find(
			'[data-rf-floorplan-source-confirmed]'
		);
		var $floorplanId = $identity.find('#floorplan_id');
		var $floorplanIdConfirmed = $identity.find(
			'[data-rf-floorplan-id-confirmed]'
		);
		var sourceDialog = document.querySelector(
			'[data-rf-floorplan-source-dialog]'
		);
		var floorplanIdDialog = document.querySelector(
			'[data-rf-floorplan-id-dialog]'
		);

		function openConfirmation(dialog, fallbackMessage, onConfirm) {
			if (dialog && 'function' === typeof dialog.showModal) {
				dialog.showModal();
				return;
			}

			if (window.confirm(fallbackMessage)) {
				onConfirm();
			}
		}

		function bindConfirmation(dialog, onConfirm) {
			var cancelButton;
			var confirmButton;

			if (!dialog) {
				return;
			}

			cancelButton = dialog.querySelector('[data-rf-dialog-cancel]');
			confirmButton = dialog.querySelector('[data-rf-dialog-confirm]');

			if (cancelButton) {
				cancelButton.addEventListener('click', function () {
					dialog.close();
				});
			}

			if (confirmButton) {
				confirmButton.addEventListener('click', function () {
					dialog.close();
					onConfirm();
				});
			}

			dialog.addEventListener('click', function (event) {
				if (event.target === dialog) {
					dialog.close();
				}
			});

			dialog.addEventListener('cancel', function (event) {
				event.preventDefault();
				dialog.close();
			});
		}

		function bindProtectedField(
			$field,
			$confirmed,
			dialog,
			fallbackMessage,
			unlock,
			requiresConfirmation
		) {
			if (!$field.length || !requiresConfirmation) {
				return;
			}

			function requestUnlock(event) {
				if ('1' === $confirmed.val()) {
					return;
				}

				event.preventDefault();
				$field.trigger('blur');
				openConfirmation(dialog, fallbackMessage, unlock);
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
			$floorplanSource.removeAttr('aria-readonly aria-haspopup');
			$floorplanSourceConfirmed.val('1');
			$floorplanSource.trigger('focus');
		}

		function unlockFloorplanId() {
			$floorplanId
				.prop('readonly', false)
				.removeAttr('aria-readonly aria-haspopup');
			$floorplanIdConfirmed.val('1');
			$floorplanId.trigger('focus').trigger('select');
		}

		bindConfirmation(sourceDialog, unlockSource);
		bindConfirmation(floorplanIdDialog, unlockFloorplanId);

		bindProtectedField(
			$floorplanSource,
			$floorplanSourceConfirmed,
			sourceDialog,
			'Changing the floor plan source can affect syncing and which fields are controlled by an integration. Continue only if you understand the consequences.',
			unlockSource,
			true
		);

		bindProtectedField(
			$floorplanId,
			$floorplanIdConfirmed,
			floorplanIdDialog,
			'Changing the Floor Plan ID can disconnect units or synced API data. Continue only if you understand the consequences.',
			unlockFloorplanId,
			Boolean($floorplanId.val())
		);
	}

	function initPropertyRelationship($identity) {
		var $combobox = $identity.find('[data-rf-property-relationship]');
		var $select = $combobox.find('#property_id');
		var $search = $combobox.find('.rf-property-relationship-search');
		var $toggle = $combobox.find('.rf-property-relationship-toggle');
		var $confirmed = $identity.find(
			'[data-rf-floorplan-property-id-confirmed]'
		);
		var dialog = document.querySelector(
			'[data-rf-floorplan-property-id-dialog]'
		);
		var originalPropertyId = $select.val();
		var relationshipUnlocked = !originalPropertyId;
		var choices;

		if (
			!$combobox.length ||
			!$select.length ||
			!$search.length ||
			'function' !== typeof $search.autocomplete
		) {
			return;
		}

		function unlockRelationship() {
			relationshipUnlocked = true;
			$confirmed.val('1');
			$search.trigger('focus').autocomplete('search', '');
		}

		function requestRelationshipUnlock(event) {
			var confirmMessage =
				'Changing the Property ID moves this floor plan to another property and can disconnect related or synced records. Continue only if you intend to move it.';

			if (relationshipUnlocked) {
				return true;
			}

			event.preventDefault();
			event.stopImmediatePropagation();
			$search.trigger('blur');

			if (dialog && 'function' === typeof dialog.showModal) {
				dialog.showModal();
			} else if (window.confirm(confirmMessage)) {
				unlockRelationship();
			}

			return false;
		}

		if (dialog) {
			var cancelButton = dialog.querySelector('[data-rf-dialog-cancel]');
			var confirmButton = dialog.querySelector('[data-rf-dialog-confirm]');

			if (cancelButton) {
				cancelButton.addEventListener('click', function () {
					dialog.close();
					$select.val(originalPropertyId);
				});
			}

			if (confirmButton) {
				confirmButton.addEventListener('click', function () {
					dialog.close();
					unlockRelationship();
				});
			}

			dialog.addEventListener('click', function (event) {
				if (event.target === dialog) {
					dialog.close();
					$select.val(originalPropertyId);
				}
			});

			dialog.addEventListener('cancel', function (event) {
				event.preventDefault();
				dialog.close();
				$select.val(originalPropertyId);
			});
		}

		choices = $select
			.find('option[value!=""]')
			.map(function () {
				return {
					label: $(this).text(),
					value: this.value,
				};
			})
			.get();

		function getSelectedLabel() {
			return $select.find('option:selected').text().trim();
		}

		function resetSearch() {
			$search.val($select.val() ? getSelectedLabel() : '');
		}

		$search
			.prop('hidden', false)
			.autocomplete({
				appendTo: document.body,
				delay: 0,
				minLength: 0,
				source: function (request, response) {
					var matcher = new RegExp(
						$.ui.autocomplete.escapeRegex(request.term),
						'i'
					);

					response(
						choices.filter(function (choice) {
							return matcher.test(choice.label);
						})
					);
				},
				select: function (event, ui) {
					$select.val(ui.item.value).trigger('change');
					$search.val(ui.item.label);
					return false;
				},
				classes: {
					'ui-autocomplete': 'rf-property-relationship-menu',
				},
			})
			.on('focus', function () {
				this.select();
			})
			.on('blur', function () {
				window.setTimeout(resetSearch, 100);
				});

		$search.on('pointerdown', requestRelationshipUnlock).on(
			'keydown',
			function (event) {
				if (
					'Enter' === event.key ||
					' ' === event.key ||
					'ArrowDown' === event.key ||
					'ArrowUp' === event.key
				) {
					requestRelationshipUnlock(event);
				}
			}
		);

		$toggle.prop('hidden', false).on('click', function (event) {
			if (false === requestRelationshipUnlock(event)) {
				return;
			}
			$search.trigger('focus').autocomplete('search', '');
		});

		$search.prop('required', $select.prop('required'));
		$select.prop('required', false);
		$select.attr({
			'aria-hidden': 'true',
			tabindex: '-1',
		});
		$combobox.addClass('is-enhanced');
		resetSearch();
	}

	$(function () {
		var $identity = $('[data-rf-floorplan-identity]');

		if (!$identity.length) {
			return;
		}

		initProtectedFields($identity);
		initPropertyRelationship($identity);
	});
})(jQuery, document);
