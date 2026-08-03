<?php
/**
 * Shared editor taxonomy controls.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render WordPress's native hierarchical taxonomy controls in a tabbed editor.
 *
 * Taxonomy labels come from the registered taxonomy object so WordPress label
 * filters are reflected throughout these controls.
 *
 * @param WP_Post $post Current post.
 * @return void
 */
function rentfetch_editor_taxonomies_metabox_callback( $post ) {
	$taxonomy_names = array(
		'properties' => array( 'propertytypes', 'propertycategories', 'amenities' ),
		'floorplans' => array( 'floorplancategory', 'floorplantype' ),
	)[ $post->post_type ] ?? array();

	foreach ( $taxonomy_names as $taxonomy_name ) {
		$taxonomy = get_taxonomy( $taxonomy_name );

		if ( ! $taxonomy || ! in_array( $post->post_type, $taxonomy->object_type, true ) ) {
			continue;
		}
		?>
		<div class="rf-property-taxonomy" data-rf-taxonomy-search>
			<h3><?php echo esc_html( $taxonomy->labels->name ); ?></h3>
			<label for="rf-property-taxonomy-search-<?php echo esc_attr( $taxonomy_name ); ?>">
				<?php
				printf(
					/* translators: %s: taxonomy label. */
					esc_html__( 'Search %s', 'rentfetch' ),
					esc_html( $taxonomy->labels->name )
				);
				?>
			</label>
			<input
				type="search"
				class="regular-text"
				id="rf-property-taxonomy-search-<?php echo esc_attr( $taxonomy_name ); ?>"
				placeholder="<?php esc_attr_e( 'Type to filter terms…', 'rentfetch' ); ?>"
				autocomplete="off"
				aria-controls="<?php echo esc_attr( $taxonomy_name ); ?>checklist"
				data-rf-taxonomy-search-input
			>
			<p class="rf-property-taxonomy-no-results" role="status" aria-live="polite" hidden data-rf-taxonomy-no-results>
				<?php esc_html_e( 'No matching terms.', 'rentfetch' ); ?>
			</p>
			<?php
			post_categories_meta_box(
				$post,
				array(
					'args' => array( 'taxonomy' => $taxonomy_name ),
				)
			);
			?>
		</div>
		<?php
	}
}
