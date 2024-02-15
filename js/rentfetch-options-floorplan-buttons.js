jQuery(document).ready(function ($) {
	$('input[name="rentfetch_options_availability_button_enabled"]')
		.on('change', function () {
			if (this.checked) {
				$('.availability .white-box:not(.always-visible)').show();
			} else {
				$('.availability .white-box:not(.always-visible)').hide();
			}
		})
		.trigger('change');

	$('input[name="rentfetch_options_contact_button_enabled"]')
		.on('change', function () {
			// console.log( this );

			if (this.checked) {
				$('.contact .white-box:not(.always-visible)').show();
			} else {
				$('.contact .white-box:not(.always-visible)').hide();
			}
		})
		.trigger('change');

	$('input[name="rentfetch_options_tour_button_enabled"]')
		.on('change', function () {
			// console.log( this );

			if (this.checked) {
				$('.tour .white-box:not(.always-visible)').show();
			} else {
				$('.tour .white-box:not(.always-visible)').hide();
			}
		})
		.trigger('change');
});
