jQuery(document).ready(function ($) {
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

	// On change.
	$('#property_id').on('change', function () {
		updateLink();
	});
});
