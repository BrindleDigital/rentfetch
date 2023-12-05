// Code to run on page load
jQuery(document).ready(function ($) {
	// Your code to run on page load goes here
	var lightboxVideo = GLightbox({
		selector: '.tour-link',
	});
});

// Code to run after completion of any AJAX request
jQuery(document).ajaxComplete(function ($) {
	// Your code to run after AJAX request completion goes here
	var lightboxVideo = GLightbox({
		selector: '.tour-link',
	});
});
