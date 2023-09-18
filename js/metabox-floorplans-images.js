jQuery(document).ready(function ($) {
    // Get the container for the gallery images
    var galleryContainer = $('#gallery-container');

    // Get the hidden input field for the gallery IDs
    var galleryIdsField = $('#images');

    // Get the "Select Images" button
    var imagesButton = $('#images_button');

    // convert gallleryIdsField value to an array
    var selectedImageIds = galleryIdsField.val().split(',');

    var selection;

    var galleryFrame;

    //* Handle the "Select Images" button click event
    imagesButton.click(function (e) {
        e.preventDefault();

        //* If the frame already exists, open it
        if (galleryFrame) {
            galleryFrame.open();
            return;
        }

        //* Create the media frame
        galleryFrame = wp.media({
            title: 'Select Images',
            button: {
                text: 'Add to Gallery',
            },
            multiple: true,
            library: {
                type: 'image',
            },
        });

        //* When the frame is opened, restore the selection
        galleryFrame.on('open', function () {
            var selection = galleryFrame.state().get('selection');
            var selecteds = galleryIdsField.val(); // the id of the image

            // remove commas at the start of the string
            selecteds = selecteds.replace(/^,/, '');

            // convert the string to an array
            selecteds = selecteds.split(',');

            // for each id in the array
            for (let i = 0; i < selecteds.length; i++) {
                // add the image to the selection
                selection.add(wp.media.attachment(selecteds[i]));
            }
        });

        //* Handle the selection when confirmed
        galleryFrame.on('select', function () {
            // Get the selection
            var selection = galleryFrame.state().get('selection');

            selection.map(function (attachment) {
                // Convert the attachment to a JSON object
                attachment = attachment.toJSON();

                // Check if the image is already in the gallery
                if (
                    attachment.id &&
                    selectedImageIds.indexOf(attachment.id) === -1
                ) {
                    // Add the image to the gallery
                    selectedImageIds.push(attachment.id);

                    // Add the image to the gallery container
                    galleryContainer.append(
                        '<div class="gallery-image" data-id="' +
                            attachment.id +
                            '"><img src="' +
                            attachment.sizes.thumbnail.url +
                            '"><button class="remove-image">Remove</button></div>'
                    );

                    // check the data-id of all divs in the gallery container, and remove any with duplicate data-id
                    var ids = [];
                    galleryContainer.find('div').each(function () {
                        var id = $(this).data('id');
                        if (ids.indexOf(id) !== -1) {
                            $(this).remove();
                        } else {
                            ids.push(id);
                        }
                    });

                    // Update the hidden input field
                    galleryIdsField.val(selectedImageIds.join(','));
                }
            });
        });

        galleryFrame.open();
    });

    //* Handle the "Remove" button click event
    galleryContainer.on('click', '.remove-image', function () {
        var imageDiv = $(this).closest('.gallery-image');
        var imageId = imageDiv.data('id');

        selectedImageIds = galleryIdsField.val().split(',');

        selectedImageIds.splice(selectedImageIds.indexOf(imageId), 1);
        galleryIdsField.val(selectedImageIds.join(','));
        imageDiv.remove();
    });
});
