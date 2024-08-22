// Code to run on page load
jQuery(document).ready(function ($) {
	rentfetch_glightbox_init();
});

// Code to run after completion of any AJAX request
jQuery(document).ajaxComplete(function () {
	rentfetch_glightbox_init();
});

function rentfetch_glightbox_init() {
	var lightboxVideo = GLightbox({
		selector: '.tour-link',
	});

	var lightboxPropertyGallery = GLightbox({
		selector: '.property-image-grid-link',
	});

	var lightboxFloorplanGallery = GLightbox({
		selector: '.floorplan-image-gallery',
	});
}
