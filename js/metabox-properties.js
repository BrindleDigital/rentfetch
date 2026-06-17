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
		var floorplanLink =
			'/wp-admin/edit.php?ac-actions-form=1&orderby=607b962c381064&order=asc&post_status=all&post_type=floorplans&layout=6048fca7a7894&action=-1&paged=1&action2=-1';
		var unitLink =
			'/wp-admin/edit.php?ac-actions-form=1&orderby=607b962c381064&order=asc&post_status=all&post_type=units&layout=6048fca7a7894&action=-1&paged=1&action2=-1';

		if (propertyId) {
			floorplanLink += '&s=' + propertyId;
			unitLink += '&s=' + propertyId;
		}

		$('#view-related-floorplans').html(
			'<a href="' +
				floorplanLink +
				'" target="_blank">View Related Floorplans</a>'
		);
		$('#view-related-units').html(
			'<a href="' + unitLink + '" target="_blank">View Related Units</a>'
		);
	}

	// On load.
	updateLink();
	initSpecialsDateRange();

	// On change.
	$('#property_id').on('change', function () {
		updateLink();
	});
});
