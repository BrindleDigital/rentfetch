jQuery(document).ready(function ($) {
	const canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
	const featuredFilters = $('#featured-filters');

	// For pointer devices: handle hover regardless of viewport width.
	if (canHover) {
		featuredFilters.on('mouseenter', '.toggle, .input-wrap', function () {
			var fieldset = $(this).closest('fieldset');
			var inputWrap = fieldset.find('.input-wrap');

			inputWrap.addClass('active').removeClass('inactive');
			featuredFilters.find('.input-wrap').not(inputWrap).removeClass('active');
		});

		// Track the active fieldset
		let activeFieldset = null;

		featuredFilters.on('mouseover', 'fieldset', function () {
			activeFieldset = $(this);
		});

		$(document).on('mouseover', function (e) {
			if (
				activeFieldset &&
				!$(e.target).closest('#featured-filters fieldset').is(activeFieldset) &&
				!$(e.target).closest('#ui-datepicker-div').length
			) {
				activeFieldset.find('.input-wrap').removeClass('active');
				activeFieldset = null;
			}
		});
	}

	// For touch devices: maintain click functionality.
	if (!canHover) {
		featuredFilters.on('click', '.toggle', function () {
			var inputWrap = $(this).closest('fieldset').find('.input-wrap');
			inputWrap.toggleClass('active inactive');
			featuredFilters.find('.input-wrap').not(inputWrap).removeClass('active');
		});

		$(document).on('click touchstart', function (event) {
			if (
				!$(event.target).closest('.toggle').length &&
				!$(event.target).closest('.input-wrap').length &&
				!$(event.target).is('input') &&
				!$(event.target).closest('#ui-datepicker-div').length
			) {
				featuredFilters.find('.input-wrap').removeClass('active');
			}
		});

		featuredFilters.on('click touchstart', '.input-wrap input', function (event) {
			event.stopPropagation();
		});
	}
});
