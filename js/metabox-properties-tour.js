jQuery(document).ready(function ($) {
	function updateTourPreview() {
		// Get the URL input field and its value
		const tourInput = $('input#tour');
		const iframeCode = tourInput.val();

		// Get the oembed container element
		const oembedContainer = $('#tour-preview');

		// Remove any existing oembed content
		oembedContainer.empty();

		// Check if the input is not empty
		if (iframeCode.trim() !== '') {
			// Create a new HTML element for the iframe code
			const iframeContent = $('<div></div>').html(iframeCode);

			// Add the iframe content to the container element
			oembedContainer.append(iframeContent);
		}
	}

	// Trigger the function when the input changes
	$('input#tour').on('input', updateTourPreview);

	// Trigger on page load
	updateTourPreview();
});
