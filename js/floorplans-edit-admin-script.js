jQuery(document).ready(function ($) {
	$('table.wp-list-table')
		.find('tr')
		.each(function () {
			if ($(this).find('td.floorplan_source').text() === 'yardi') {
				var synced =
					'.title,.property_id,.property_name,.floorplan_id,.floorplan_source,.unit_type_mapping,.floorplan_images,.floorplan_video_or_tour,.beds,.baths,.minimum_deposit,.maximum_deposit,.minimum_rent,.maximum_rent,.minimum_sqft,.maximum_sqft,.availability_date,.property_show_specials,.has_specials,.availability_url,.available_units,.api_response';

				$(this).find(synced).css({
					color: '#066cd3',
					'background-color': '#066cd314',
				});
			}
		});
});

jQuery(document).ready(function ($) {
	$('table.wp-list-table')
		.find('tr')
		.each(function () {
			if ($(this).find('td.floorplan_source').text() === 'yardi') {
				var synced =
					'.title,.property_id,.floorplan_id,.floorplan_source,.unit_type_mapping,.floorplan_images,.floorplan_video_or_tour,.beds,.baths,.minimum_deposit,.maximum_deposit,.minimum_rent,.maximum_rent,.minimum_sqft,.maximum_sqft,.availability_date,.property_show_specials,.has_specials,.availability_url,.available_units,.api_response';

				$(this).find(synced).css({
					color: '#066cd3',
					'background-color': '#066cd314',
				});
			}
		});
});
