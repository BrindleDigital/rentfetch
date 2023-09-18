jQuery(document).ready(function ($) {
    // Get the URL input field and add an event listener for when the input changes
    const videoInput = $('input#video');

    $(videoInput).on('input', function () {
        // Get the oembed container element
        const oembedContainer = $('#video-container');

        // Check if the oembed container is empty
        if (videoInput.val() === '') {
            oembedContainer.empty(); // Remove any existing oembed content
            return; // Stop right there and don't fetch anything
        }

        // Get the video ID from the YouTube URL
        const videoID = videoInput.val();

        // Create an oembed URL for the video
        const oembedUrl = `https://www.youtube.com/oembed?url=${videoID}`;

        // Fetch the oembed data from the API
        $.getJSON(oembedUrl)
            .done(function (data) {
                // Create a new HTML element for the oembed content
                const oembedContent = $('<div></div>').html(data.html);

                // Clear any existing oembed content
                oembedContainer.empty();

                // Add the oembed content to the container element
                oembedContainer.append(oembedContent);
            })
            .fail(function (error) {
                // Clear any existing oembed content
                oembedContainer.empty();

                oembedContainer.append(
                    'There was an error fetching the video.'
                );
            });
    });

    // on page load, trigger the input event to load the video
    $('input#video').trigger('input');
});
