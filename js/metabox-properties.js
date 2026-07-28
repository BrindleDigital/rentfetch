jQuery(document).ready(function ($) {
	function initSpecialsDateRange() {
		var $wrapper = $('.rf-specials-date-range');
		var $rangeInput = $('#specials_date_range');
		var $startInput = $('#specials_start_date');
		var $endInput = $('#specials_end_date');
		var picker = null;

		if (!$wrapper.length || !$rangeInput.length || 'function' !== typeof window.flatpickr) {
			return;
		}

		function setInitialMode() {
			var startDate = $startInput.val();
			var endDate = $endInput.val();
			var initialMode = 'range';

			if (startDate && !endDate) {
				initialMode = 'start';
			} else if (!startDate && endDate) {
				initialMode = 'end';
			}

			$wrapper.find('input[name="specials_date_mode"][value="' + initialMode + '"]').prop('checked', true);
		}

		function getMode() {
			return $wrapper.find('input[name="specials_date_mode"]:checked').val() || 'range';
		}

		function normalizeDates() {
			var startDate = $startInput.val();
			var endDate = $endInput.val();

			if (startDate && endDate && startDate > endDate) {
				$startInput.val(endDate);
				$endInput.val(startDate);
			}
		}

		function updateDisplay() {
			var startDate = $startInput.val();
			var endDate = $endInput.val();
			var displayValue = '';
			var formatDisplayDate = function (dateString) {
				if (!dateString || !picker) {
					return dateString;
				}

				return picker.formatDate(picker.parseDate(dateString, 'Y-m-d'), 'F j, Y');
			};

			if (startDate && endDate) {
				displayValue = formatDisplayDate(startDate) + ' to ' + formatDisplayDate(endDate);
			} else if (startDate) {
				displayValue = 'Starts ' + formatDisplayDate(startDate);
			} else if (endDate) {
				displayValue = 'Ends ' + formatDisplayDate(endDate);
			}

			$rangeInput.val(displayValue);
		}

		function getPickerDates() {
			var startDate = $startInput.val();
			var endDate = $endInput.val();
			var mode = getMode();
			var parseStoredDate = function (dateString) {
				var parts = dateString ? dateString.split('-') : [];

				if (3 !== parts.length) {
					return null;
				}

				return new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
			};

			if ('range' === mode && startDate && endDate) {
				return [parseStoredDate(startDate), parseStoredDate(endDate)];
			}

			if ('end' === mode && endDate) {
				return [parseStoredDate(endDate)];
			}

			if (startDate) {
				return [parseStoredDate(startDate)];
			}

			return [];
		}

		function rebuildPicker() {
			var mode = getMode();

			if (picker) {
				picker.destroy();
			}

			picker = window.flatpickr($rangeInput[0], {
				allowInput: false,
				clickOpens: true,
				altFormat: 'F j, Y',
				altInput: false,
				dateFormat: 'F j, Y',
				defaultDate: getPickerDates(),
				mode: 'range' === mode ? 'range' : 'single',
				onReady: function () {
					this.calendarContainer.classList.add('rf-specials-flatpickr-calendar');
				},
				onChange: function (selectedDates) {
					if ('range' === mode) {
						$startInput.val(selectedDates[0] ? this.formatDate(selectedDates[0], 'Y-m-d') : '');
						$endInput.val(selectedDates[1] ? this.formatDate(selectedDates[1], 'Y-m-d') : '');
					} else if ('end' === mode) {
						$startInput.val('');
						$endInput.val(selectedDates[0] ? this.formatDate(selectedDates[0], 'Y-m-d') : '');
					} else {
						$startInput.val(selectedDates[0] ? this.formatDate(selectedDates[0], 'Y-m-d') : '');
						$endInput.val('');
					}

					normalizeDates();
					updateDisplay();
				},
				onClose: updateDisplay,
			});

			updateDisplay();
		}

		$wrapper.on('change', 'input[name="specials_date_mode"]', function () {
			if ('start' === getMode()) {
				$endInput.val('');
			} else if ('end' === getMode()) {
				$startInput.val('');
			}

			rebuildPicker();
			picker.open();
		});

		$wrapper.on('click', '.rf-specials-date-clear', function () {
			$startInput.val('');
			$endInput.val('');
			if (picker) {
				picker.clear();
			}
			updateDisplay();
		});

		normalizeDates();
		setInitialMode();
		rebuildPicker();
	}

	function updateLink() {
		var propertyId = $('#property_id').val();
		$('[data-rf-related-record-links] a[data-base-url]').each(
			function () {
				var url = new URL($(this).data('base-url'), window.location.href);
				if (propertyId) {
					url.searchParams.set('s', propertyId);
				} else {
					url.searchParams.delete('s');
				}
				this.href = url.toString();
			}
		);
	}

	function initPropertyIdentity() {
		var $identity = $('[data-rf-property-identity]');
		var $propertyId = $identity.find('#property_id');
		var $propertyIdConfirmed = $identity.find(
			'[data-rf-property-id-confirmed]'
		);
		var $propertySource = $identity.find('#property_source');
		var $propertySourceConfirmed = $identity.find(
			'[data-rf-property-source-confirmed]'
		);
		var propertyIdDialog = document.querySelector(
			'[data-rf-property-id-dialog]'
		);
		var propertySourceDialog = document.querySelector(
			'[data-rf-property-source-dialog]'
		);

		if (!$identity.length || !$propertyId.length) {
			return;
		}

		function unlockPropertyId() {
			$propertyId.prop('readonly', false).removeAttr('aria-readonly aria-haspopup');
			$propertyIdConfirmed.val('1');
			$propertyId.trigger('focus').trigger('select');
		}

		function unlockPropertySource() {
			$propertySource.removeAttr('aria-readonly aria-haspopup');
			$propertySourceConfirmed.val('1');
			$propertySource.trigger('focus');
		}

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
			if (!dialog) {
				return;
			}

			var cancelButton = dialog.querySelector(
				'[data-rf-dialog-cancel]'
			);
			var confirmButton = dialog.querySelector(
				'[data-rf-dialog-confirm]'
			);

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
		}

		bindConfirmation(propertyIdDialog, unlockPropertyId);
		bindConfirmation(propertySourceDialog, unlockPropertySource);

		function bindProtectedField($field, $confirmed, dialog, fallbackMessage, unlock, requiresConfirmation) {
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
				if ('Enter' === event.key || ' ' === event.key || 'ArrowDown' === event.key || 'ArrowUp' === event.key) {
					requestUnlock(event);
				}
			});
		}

		bindProtectedField(
			$propertyId,
			$propertyIdConfirmed,
			propertyIdDialog,
			'Changing this identifier can disconnect the property from its floor plans, units, or synced API data. Continue only if you understand the consequences.',
			unlockPropertyId,
			Boolean($propertyId.val())
		);

		bindProtectedField(
			$propertySource,
			$propertySourceConfirmed,
			propertySourceDialog,
			'Changing the property source can affect syncing and which fields are controlled by an integration. Continue only if you understand the consequences.',
			unlockPropertySource,
			true
		);

		[propertyIdDialog, propertySourceDialog].forEach(function (dialog) {
			if (dialog) {
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
		});
	}

	// On load.
	updateLink();
	initPropertyIdentity();
	initSpecialsDateRange();

	// On change.
	$('#property_id').on('input change', function () {
		updateLink();
	});

	document.addEventListener(
		'rentfetch:property-tab-content-loaded',
		function (event) {
			if (event.detail && 'diagnostics' === event.detail.tabId) {
				updateLink();
			}
		}
	);
});
