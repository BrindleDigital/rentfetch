<?php

/**
 * Force the documentation link to go to a third-party URL.
 */
function rentfetch_documentation_submenu_open_new_tab() {
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('a[href="admin.php?page=rent_fetch_documentation"').each(function () {
			if ($(this).text() == 'Documentation') {
				$(this).css('color', 'yellow');
				$(this).attr('href', 'https://github.com/BrindleDigital/rentfetch');
				$(this).attr('target','_blank');
			}
		});
	});
	</script>
	<?php
}
add_action( 'admin_footer', 'rentfetch_documentation_submenu_open_new_tab' );