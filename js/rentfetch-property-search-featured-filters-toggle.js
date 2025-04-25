jQuery(document).ready(function ($) {
	// For desktop: handle hover
	if (window.matchMedia('(min-width: 1024px)').matches) {
		$('.toggle, .input-wrap').on('mouseenter', function () {
			var fieldset = $(this).closest('fieldset');
			var inputWrap = fieldset.find('.input-wrap');

			inputWrap.addClass('active').removeClass('inactive');
			$('.input-wrap').not(inputWrap).removeClass('active');
		});

		// Track the active fieldset
		let activeFieldset = null;

		$('fieldset').on('mouseover', function () {
			activeFieldset = $(this);
		});

		$(document).on('mouseover', function (e) {
			if (
				activeFieldset &&
				!$(e.target).closest('fieldset').is(activeFieldset) &&
				!$(e.target).closest('#ui-datepicker-div').length
			) {
				activeFieldset.find('.input-wrap').removeClass('active');
				activeFieldset = null;
			}
		});
	}

	// For mobile: maintain click functionality
	if (window.matchMedia('(max-width: 1023px)').matches) {
		$('.toggle').on('click', function () {
			var inputWrap = $(this).closest('fieldset').find('.input-wrap');
			inputWrap.toggleClass('active inactive');
			$('.input-wrap').not(inputWrap).removeClass('active');
		});

		$(document).on('click touchstart', function (event) {
			if (
				!$(event.target).closest('.toggle').length &&
				!$(event.target).closest('.input-wrap').length &&
				!$(event.target).is('input') &&
				!$(event.target).closest('#ui-datepicker-div').length
			) {
				$('.input-wrap').removeClass('active');
			}
		});

		$('.input-wrap input').on('click touchstart', function (event) {
			event.stopPropagation();
		});
	}
});
