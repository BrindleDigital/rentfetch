jQuery(document).ready(function ($) {
	// Handle download current fees button
	document.getElementById('download-current-fees').addEventListener(
		'click',
		function (e) {
			e.preventDefault();

			// Get JSON from textarea (or CodeMirror if available)
			var jsonText = document.querySelector('.rentfetch-fees-json').value;

			try {
				var feesData = JSON.parse(jsonText);

				if (!Array.isArray(feesData)) {
					alert(
						'Invalid JSON format. Please ensure the JSON is a valid array of fee objects.'
					);
					return;
				}

				// Convert to CSV
				var csvContent = 'description,price,frequency,notes,category\n';
				feesData.forEach(function (fee) {
					var description = (fee.description || '').replace(
						/"/g,
						'""'
					);
					var price = fee.price || 0;
					var frequency = (fee.frequency || '').replace(/"/g, '""');
					var notes = (fee.notes || '').replace(/"/g, '""');
					var category = (fee.category || '').replace(/"/g, '""');

					csvContent +=
						'"' +
						description +
						'",' +
						price +
						',"' +
						frequency +
						'","' +
						notes +
						'","' +
						category +
						'"\n';
				});

				// Get property information
				var button = e.target;
				var propertyTitle =
					button.getAttribute('data-property-title') || 'Property';
				var propertyId =
					button.getAttribute('data-property-id') || 'Unknown';

				// Generate timestamp
				var now = new Date();
				var timestamp =
					now.getFullYear() +
					'-' +
					String(now.getMonth() + 1).padStart(2, '0') +
					'-' +
					String(now.getDate()).padStart(2, '0') +
					'_' +
					String(now.getHours()).padStart(2, '0') +
					'-' +
					String(now.getMinutes()).padStart(2, '0') +
					'-' +
					String(now.getSeconds()).padStart(2, '0');

				// Sanitize filename components
				var safeTitle = propertyTitle
					.replace(/[^a-zA-Z0-9]/g, '_')
					.substring(0, 30);
				var safeId = propertyId
					.replace(/[^a-zA-Z0-9]/g, '_')
					.substring(0, 20);
				var filename =
					safeTitle + '_' + safeId + '_' + timestamp + '.csv';

				// Create and trigger download
				var blob = new Blob([csvContent], {
					type: 'text/csv;charset=utf-8;',
				});
				var link = document.createElement('a');
				if (link.download !== undefined) {
					var url = URL.createObjectURL(blob);
					link.setAttribute('href', url);
					link.setAttribute('download', filename);
					link.style.visibility = 'hidden';
					document.body.appendChild(link);
					link.click();
					document.body.removeChild(link);
				} else {
					alert(
						'Your browser does not support file downloads. Please copy the JSON and convert manually.'
					);
				}
			} catch (error) {
				alert('Error parsing JSON: ' + error.message);
			}
		},
		{ passive: true }
	);
});
