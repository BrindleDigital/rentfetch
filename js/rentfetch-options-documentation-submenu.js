jQuery(document).ready(function ($) {
	$('a[href="admin.php?page=rentfetch-documentation"').each(function () {
		if ($(this).text() == 'Documentation') {
			$(this).css('color', 'yellow');
			$(this).attr('href', 'https://rentfetch.io/docs/getting-started/');
			$(this).attr('target', '_blank');
		}
	});
});
