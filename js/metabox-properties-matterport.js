jQuery(document).ready(function ($) {
    // Get the URL input field and add an event listener for when the input changes
    $('input#matterport').on('input', function () {
        // Get the oembed container element
        const oembedContainer = $('#matterport-preview');

        // Remove any existing oembed content
        oembedContainer.empty();

        // Get the Matterport iframe code from the input field
        const iframeCode = this.value;

        // Create a new HTML element for the iframe code
        const iframeContent = $('<div></div>').html(iframeCode);

        // Add the iframe content to the container element
        oembedContainer.append(iframeContent);
    });
});
