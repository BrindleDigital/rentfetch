<?php
/**
 * Hookable sections for the single floorplan template.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register the default single-floorplan sections.
 *
 * @return void
 */
function rentfetch_single_floorplans_set_up_parts() {
	add_action( 'rentfetch_do_single_floorplans_parts', 'rentfetch_single_floorplans_parts_overview', 10 );
	add_action( 'rentfetch_do_single_floorplans_parts', 'rentfetch_single_floorplans_parts_units', 20 );
	add_action( 'rentfetch_do_single_floorplans_parts', 'rentfetch_single_floorplans_parts_features', 40 );
	add_action( 'rentfetch_do_single_floorplans_parts', 'rentfetch_single_floorplans_parts_tour', 45 );
	add_action( 'rentfetch_do_single_floorplans_parts', 'rentfetch_single_floorplans_parts_gallery', 50 );
	add_action( 'rentfetch_do_single_floorplans_parts', 'rentfetch_single_floorplans_parts_similar', 60 );
	add_action( 'rentfetch_do_single_floorplans_parts', 'rentfetch_single_floorplans_parts_property_fees', 70 );
}
add_action( 'wp_loaded', 'rentfetch_single_floorplans_set_up_parts' );

/**
 * Use the redesigned unit table only on single floorplan pages.
 *
 * The original action remains in place so existing third-party callbacks keep
 * running and the new default can still be removed or reordered normally.
 *
 * @return void
 */
function rentfetch_single_floorplans_set_up_unit_table() {
	if ( ! is_singular( 'floorplans' ) || locate_template( array( 'single-floorplans.php' ) ) ) {
		return;
	}

	remove_action( 'rentfetch_floorplan_do_unit_table', 'rentfetch_floorplan_unit_table' );
	remove_action( 'rentfetch_floorplan_do_unit_table', 'rentfetch_floorplan_unit_list' );
	add_action( 'rentfetch_floorplan_do_unit_table', 'rentfetch_single_floorplan_unit_table' );
}
add_action( 'wp', 'rentfetch_single_floorplans_set_up_unit_table', 20 );

/**
 * Output a standard full-width floorplan section.
 *
 * @param string   $classes  Additional outer classes.
 * @param callable $callback Section callback.
 * @param string   $id       Optional section ID.
 * @return void
 */
function rentfetch_single_floorplans_section( $classes, $callback, $id = '' ) {
	printf(
		'<div%s class="single-floorplans-container-outer single-floorplans-section %s"><div class="single-floorplans-container-inner">',
		$id ? ' id="' . esc_attr( $id ) . '"' : '',
		esc_attr( $classes )
	);
	call_user_func( $callback );
	echo '</div></div>';
}

/**
 * Select one video and one virtual tour for the top media tabs.
 *
 * Manual media wins within its matching type. Synced source types provide the
 * fallback distinction between conventional video and virtual tours.
 *
 * @param int|null $post_id Floorplan post ID.
 * @return array{video:array|null,tour:array|null}
 */
function rentfetch_single_floorplan_get_featured_media( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$media   = array(
		'video' => null,
		'tour'  => null,
	);
	$manual  = rentfetch_parse_tour_value( get_post_meta( $post_id, 'tour', true ) );

	if ( $manual ) {
		$media[ in_array( $manual['type'], array( 'matterport', 'virtual_tour' ), true ) ? 'tour' : 'video' ] = $manual;
	}

	$synced_tours = get_post_meta( $post_id, 'synced_tours', true );
	foreach ( is_array( $synced_tours ) ? $synced_tours : array() as $synced_tour ) {
		if ( ! is_array( $synced_tour ) || empty( $synced_tour['url'] ) ) {
			continue;
		}

		$tour        = rentfetch_parse_tour_value( $synced_tour['url'] );
		$source_type = $synced_tour['type'] ?? '';
		$parsed_type = $tour['type'] ?? '';
		$media_type  = in_array( $parsed_type, array( 'matterport', 'virtual_tour' ), true ) || ( ! in_array( $parsed_type, array( 'youtube', 'vimeo', 'google_drive' ), true ) && in_array( $source_type, array( 'virtual_tour', 'tour_360' ), true ) ) ? 'tour' : 'video';

		if ( $tour && ! $media[ $media_type ] ) {
			$media[ $media_type ] = $tour;
		}
	}

	return apply_filters( 'rentfetch_single_floorplan_featured_media', $media, $post_id );
}

/**
 * Output the floorplan photo, video, and virtual-tour tabs.
 *
 * @param array|null $media Featured media, when already resolved by the caller.
 * @return void
 */
function rentfetch_single_floorplan_media_tabs( $media = null ) {
	$post_id = get_the_ID();
	$media   = is_array( $media ) ? $media : rentfetch_single_floorplan_get_featured_media( $post_id );
	$embeds  = array(
		'video' => ! empty( $media['video']['url'] ) ? rentfetch_get_tour_embed_html( $media['video']['url'] ) : '',
		'tour'  => ! empty( $media['tour']['url'] ) ? rentfetch_get_tour_embed_html( $media['tour']['url'] ) : '',
	);
	$tabs    = array(
		'photos' => 'Photos',
	);

	if ( $embeds['video'] ) {
		$tabs['video'] = 'Video Tour';
	}
	if ( $embeds['tour'] ) {
		$tabs['tour'] = 'Virtual Tours';
	}
	if ( count( $tabs ) < 2 ) {
		do_action( 'rentfetch_do_floorplan_images' );
		return;
	}

	echo '<div class="floorplan-media-tabs" role="tablist" aria-label="Floorplan media">';
	foreach ( $tabs as $tab => $label ) {
		$selected = 'photos' === $tab;
		printf(
			'<button id="floorplan-media-tab-%1$s-%2$s" type="button" role="tab" aria-selected="%3$s" aria-controls="floorplan-media-panel-%1$s-%2$s" tabindex="%4$s" data-floorplan-media-tab="%1$s">%5$s</button>',
			esc_attr( $tab ),
			(int) $post_id,
			$selected ? 'true' : 'false',
			$selected ? '0' : '-1',
			esc_html( $label )
		);
	}
	echo '</div>';

	printf( '<div id="floorplan-media-panel-photos-%1$s" class="floorplan-media-panel" role="tabpanel" aria-labelledby="floorplan-media-tab-photos-%1$s" data-floorplan-media-panel="photos">', (int) $post_id );
	do_action( 'rentfetch_do_floorplan_images' );
	echo '</div>';

	foreach ( array( 'video', 'tour' ) as $media_type ) {
		if ( ! $embeds[ $media_type ] ) {
			continue;
		}
		printf(
			'<div id="floorplan-media-panel-%1$s-%2$s" class="floorplan-media-panel" role="tabpanel" aria-labelledby="floorplan-media-tab-%1$s-%2$s" data-floorplan-media-panel="%1$s" hidden>%3$s</div>',
			esc_attr( $media_type ),
			(int) $post_id,
			wp_kses( $embeds[ $media_type ], rentfetch_get_allowed_embed_html() )
		);
	}

	wp_enqueue_script( 'rentfetch-single-floorplans', RENTFETCH_PATH . 'js/rentfetch-single-floorplans.js', array(), RENTFETCH_VERSION, true );
}

/**
 * Output the floorplan media and primary details.
 *
 * @return void
 */
function rentfetch_single_floorplans_parts_overview() {
	rentfetch_single_floorplans_section(
		'container-current-floorplan-info',
		function () {
			$title            = rentfetch_get_floorplan_title();
			$pricing          = rentfetch_get_floorplan_pricing();
			$beds             = rentfetch_get_floorplan_bedrooms();
			$baths            = rentfetch_get_floorplan_bathrooms();
			$square_feet      = rentfetch_get_floorplan_square_feet();
			$available_units  = (int) rentfetch_get_floorplan_units_count_from_cpt();
			$specials         = rentfetch_get_floorplan_specials();
			$specials_callout = rentfetch_get_floorplan_specials_callout();
			$property_post_id = rentfetch_get_connected_property_post_id_for_floorplan( get_the_ID() );
			$property_counts  = wp_count_posts( 'properties' );
			$property_count   = isset( $property_counts->publish ) ? (int) $property_counts->publish : 0;
			$featured_media   = rentfetch_single_floorplan_get_featured_media();
			$has_media_tabs   = (bool) ( $featured_media['video'] || $featured_media['tour'] );
			?>
			<div class="current-floorplan-info<?php echo $has_media_tabs ? ' has-media-tabs' : ''; ?>">
				<div class="images-column"><?php rentfetch_single_floorplan_media_tabs( $featured_media ); ?></div>
				<div class="content-column">
					<?php if ( $property_post_id && $property_count > 3 ) : ?>
						<a class="floorplan-property-link" href="<?php echo esc_url( get_permalink( $property_post_id ) ); ?>">&larr; <?php echo esc_html( get_the_title( $property_post_id ) ); ?></a>
					<?php endif; ?>

					<?php if ( $specials_callout ) : ?>
						<?php echo wp_kses( $specials_callout, rentfetch_get_specials_callout_allowed_html() ); ?>
					<?php elseif ( $specials ) : ?>
						<p class="specials"><?php echo esc_html( $specials ); ?></p>
					<?php endif; ?>

					<?php
					if ( $title ) :
						?>
						<h1><?php echo esc_html( $title ); ?></h1><?php endif; ?>
					<?php
					if ( $pricing ) :
						?>
						<p class="pricing"><?php echo wp_kses_post( $pricing ); ?></p><?php endif; ?>

					<div class="floorplan-stats floorplan-attributes" aria-label="Floorplan details">
						<?php
						if ( $beds ) :
							?>
							<p class="floorplan-stat beds"><svg class="floorplan-stat-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5v9m16.5 0v-5.25a3 3 0 0 0-3-3H9.75v8.25m-6 0h16.5m-16.5-6h6m-6 0V6.75A1.5 1.5 0 0 1 5.25 5.25h3A1.5 1.5 0 0 1 9.75 6.75v1.5" /></svg><span class="floorplan-stat-text"><span class="floorplan-stat-label">Bedrooms</span><span class="floorplan-stat-value"><?php echo wp_kses_post( $beds ); ?></span></span></p><?php endif; ?>
						<?php
						if ( $baths ) :
							?>
							<p class="floorplan-stat baths"><svg class="floorplan-stat-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5v1.5a4.5 4.5 0 0 1-4.5 4.5h-7.5a4.5 4.5 0 0 1-4.5-4.5V12Zm2.25 0V6.75a2.25 2.25 0 0 1 4.5 0M6.75 18v1.5M17.25 18v1.5" /></svg><span class="floorplan-stat-text"><span class="floorplan-stat-label">Bathrooms</span><span class="floorplan-stat-value"><?php echo wp_kses_post( $baths ); ?></span></span></p><?php endif; ?>
						<?php
						if ( $square_feet ) :
							?>
							<p class="floorplan-stat square-feet"><svg class="floorplan-stat-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.25h13.5v13.5H5.25z" /><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15.75v-7.5h7.5" /></svg><span class="floorplan-stat-text"><span class="floorplan-stat-label">Square Feet</span><span class="floorplan-stat-value"><?php echo wp_kses_post( $square_feet ); ?></span></span></p><?php endif; ?>
					</div>

					<div class="floorplan-buttons">
						<?php if ( $available_units > 0 ) : ?>
							<a class="rentfetch-button rentfetch-floorplan-apply-now-button" href="#available-units">View Availability</a>
						<?php endif; ?>
						<?php do_action( 'rentfetch_do_floorplan_buttons' ); ?>
					</div>
				</div>
			</div>
			<?php
		}
	);
}

/**
 * Output available units.
 *
 * @return void
 */
function rentfetch_single_floorplans_parts_units() {
	if ( rentfetch_get_floorplan_units_count_from_cpt() < 1 ) {
		return;
	}

	rentfetch_single_floorplans_section(
		'container-units',
		function () {
			echo '<div class="units">';
			echo wp_kses_post( apply_filters( 'rentfetch_single_floorplan_units_headline', '<h2>Available Units</h2>' ) );
			do_action( 'rentfetch_floorplan_do_unit_table' );
			echo '</div>';
		},
		'available-units'
	);
}

/**
 * Split a unit amenity value into display pills.
 *
 * @param mixed $value Stored or filtered amenity value.
 * @return string[]
 */
function rentfetch_single_floorplan_get_unit_amenities( $value ) {
	$values = is_array( $value ) ? $value : preg_split( '/\s*[,;|]\s*/', (string) $value );
	$values = array_map( 'sanitize_text_field', $values );

	return array_values( array_unique( array_filter( $values ) ) );
}

/**
 * Output amenity pills.
 *
 * @param mixed $amenities Amenity value.
 * @return void
 */
function rentfetch_single_floorplan_unit_amenity_pills( $amenities ) {
	$amenities = rentfetch_single_floorplan_get_unit_amenities( $amenities );
	if ( ! $amenities ) {
		return;
	}

	echo '<span class="unit-amenity-pills">';
	foreach ( $amenities as $amenity ) {
		printf( '<span class="unit-amenity-pill">%s</span>', esc_html( $amenity ) );
	}
	echo '</span>';
}

/**
 * Output availability with the current-date state highlighted.
 *
 * @param string $availability Availability label.
 * @return void
 */
function rentfetch_single_floorplan_unit_availability( $availability ) {
	$class = 'available now' === strtolower( trim( $availability ) ) ? ' is-available-now' : '';
	printf( '<span class="unit-availability-label%s">%s</span>', esc_attr( $class ), esc_html( $availability ) );
}

/**
 * Output the single-floorplan unit table and mobile unit list.
 *
 * @return void
 */
function rentfetch_single_floorplan_unit_table() {
	global $post;

	$floorplan_post = $post;
	$floorplan_id   = get_post_meta( $floorplan_post->ID, 'floorplan_id', true );
	$property_id    = get_post_meta( $floorplan_post->ID, 'property_id', true );
	$args           = apply_filters(
		'rentfetch_floorplan_unit_display_args',
		array(
			'post_type'      => 'units',
			'posts_per_page' => -1,
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_key'       => 'availability_date', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'   => 'property_id',
					'value' => $property_id,
				),
				array(
					'key'   => 'floorplan_id',
					'value' => $floorplan_id,
				),
			),
		),
	);
	$columns        = rentfetch_floorplan_unit_display_get_columns( $args );
	$post           = $floorplan_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	setup_postdata( $post );
	$units_query = new WP_Query( $args );

	if ( ! $units_query->have_posts() ) {
		wp_reset_postdata();
		return;
	}

	$square_feet_label = rentfetch_get_unit_square_feet_heading_label();
	$unit_posts        = array();
	?>
	<div class="unit-details-table-scroll" tabindex="0" aria-label="Available units table">
		<table class="unit-details-table single-floorplan-unit-table">
			<caption class="screen-reader-text"><?php echo esc_html( sprintf( 'Available units for %s', rentfetch_get_floorplan_title() ) ); ?></caption>
			<thead><tr>
				<?php
				if ( in_array( 'title', $columns, true ) ) :
					?>
					<th class="unit-title" scope="col">Apt #</th><?php endif; ?>
				<?php
				if ( in_array( 'building_name', $columns, true ) ) :
					?>
					<th class="unit-building-name unit-buliding-name" scope="col">Building</th><?php endif; ?>
				<?php
				if ( in_array( 'floor_number', $columns, true ) ) :
					?>
					<th class="unit-floor-number building-floor-number" scope="col">Floor</th><?php endif; ?>
				<?php
				if ( in_array( 'sqrft', $columns, true ) ) :
					?>
					<th class="unit-sqrft" scope="col"><?php echo esc_html( $square_feet_label ); ?></th><?php endif; ?>
				<?php
				if ( in_array( 'pricing', $columns, true ) ) :
					?>
					<th class="unit-starting-at" scope="col">Starting At</th><?php endif; ?>
				<?php
				if ( in_array( 'deposit', $columns, true ) ) :
					?>
					<th class="unit-deposit" scope="col">Deposit</th><?php endif; ?>
				<?php
				if ( in_array( 'availability_date', $columns, true ) ) :
					?>
					<th class="unit-availability" scope="col">Availability</th><?php endif; ?>
				<?php
				if ( in_array( 'amenities', $columns, true ) ) :
					?>
					<th class="unit-amenities" scope="col">Amenities</th><?php endif; ?>
				<?php
				if ( in_array( 'specials', $columns, true ) ) :
					?>
					<th class="unit-specials" scope="col">Specials</th><?php endif; ?>
				<th class="unit-buttons" scope="col"><span class="screen-reader-text">Actions</span></th>
			</tr></thead>
			<tbody>
			<?php while ( $units_query->have_posts() ) : ?>
				<?php
				$units_query->the_post();
				$availability_date = rentfetch_get_unit_availability_date();
				$amenities         = rentfetch_get_unit_amenities();
				$specials          = rentfetch_get_unit_specials();
				$unit_posts[]      = get_post();
				?>
				<tr>
					<?php
					if ( in_array( 'title', $columns, true ) ) :
						?>
						<th class="unit-title" scope="row"><?php echo esc_html( rentfetch_get_unit_title() ); ?></th><?php endif; ?>
					<?php
					if ( in_array( 'building_name', $columns, true ) ) :
						?>
						<td class="unit-building-name"><?php echo esc_html( rentfetch_get_unit_building_name() ); ?></td><?php endif; ?>
					<?php
					if ( in_array( 'floor_number', $columns, true ) ) :
						?>
						<td class="unit-floor-number"><?php echo esc_html( rentfetch_get_unit_floor_number() ); ?></td><?php endif; ?>
					<?php
					if ( in_array( 'sqrft', $columns, true ) ) :
						?>
						<td class="unit-sqrft"><?php echo esc_html( rentfetch_get_unit_square_feet() ); ?></td><?php endif; ?>
					<?php
					if ( in_array( 'pricing', $columns, true ) ) :
						?>
						<td class="unit-starting-at"><?php echo wp_kses_post( rentfetch_get_unit_pricing() ); ?></td><?php endif; ?>
					<?php
					if ( in_array( 'deposit', $columns, true ) ) :
						?>
						<td class="unit-deposit"><?php echo esc_html( rentfetch_get_unit_deposit() ); ?></td><?php endif; ?>
					<?php
					if ( in_array( 'availability_date', $columns, true ) ) :
						?>
						<td class="unit-availability"><?php rentfetch_single_floorplan_unit_availability( $availability_date ); ?></td><?php endif; ?>
					<?php
					if ( in_array( 'amenities', $columns, true ) ) :
						?>
						<td class="unit-amenities"><?php rentfetch_single_floorplan_unit_amenity_pills( $amenities ); ?></td><?php endif; ?>
					<?php
					if ( in_array( 'specials', $columns, true ) ) :
						?>
						<td class="unit-specials">
						<?php
						if ( $specials ) :
							?>
							<span class="unit-specials-pill"><?php echo esc_html( $specials ); ?></span><?php endif; ?></td><?php endif; ?>
					<td class="unit-buttons">
						<div class="unit-button-group">
							<?php do_action( 'rentfetch_do_unit_button' ); ?>
						</div>
					</td>
				</tr>
			<?php endwhile; ?>
			</tbody>
		</table>
	</div>
	<div class="unit-details-list single-floorplan-unit-list">
		<?php foreach ( $unit_posts as $unit_post ) : ?>
			<?php
			$post = $unit_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			setup_postdata( $post );
			$availability_date = rentfetch_get_unit_availability_date();
			$amenities         = rentfetch_get_unit_amenities();
			$specials          = rentfetch_get_unit_specials();
			$pricing           = rentfetch_get_unit_pricing();
			?>
			<details class="unit-details">
				<summary class="unit-summary">
					<p class="unit-summary-title unit-title">
						<?php
						printf( 'Apt #%s', esc_html( rentfetch_get_unit_title() ) );
						if ( $pricing ) {
							printf( ', <span class="label">starting at</span> %s', wp_kses_post( $pricing ) );
						}
						?>
						<span class="dropdown" aria-hidden="true"></span>
					</p>
				</summary>
				<ul class="unit-details-list-wrap">
					<?php
					if ( in_array( 'building_name', $columns, true ) && rentfetch_get_unit_building_name() ) :
						?>
						<li class="unit-building-name"><span class="unit-detail-label label">Building</span><span><?php echo esc_html( rentfetch_get_unit_building_name() ); ?></span></li><?php endif; ?>
					<?php
					if ( in_array( 'floor_number', $columns, true ) && rentfetch_get_unit_floor_number() ) :
						?>
						<li class="unit-floor-number"><span class="unit-detail-label label">Floor</span><span><?php echo esc_html( rentfetch_get_unit_floor_number() ); ?></span></li><?php endif; ?>
					<?php
					if ( in_array( 'sqrft', $columns, true ) && rentfetch_get_unit_square_feet() ) :
						?>
						<li class="unit-sqrft"><span class="unit-detail-label label"><?php echo esc_html( $square_feet_label ); ?></span><span><?php echo esc_html( rentfetch_get_unit_square_feet() ); ?></span></li><?php endif; ?>
					<?php
					if ( in_array( 'pricing', $columns, true ) ) :
						?>
						<li class="unit-starting-at"><span class="unit-detail-label label">Starting At</span><span><?php echo wp_kses_post( $pricing ); ?></span></li><?php endif; ?>
					<?php
					if ( in_array( 'deposit', $columns, true ) && rentfetch_get_unit_deposit() ) :
						?>
						<li class="unit-deposit"><span class="unit-detail-label label">Deposit</span><span><?php echo esc_html( rentfetch_get_unit_deposit() ); ?></span></li><?php endif; ?>
					<?php
					if ( in_array( 'availability_date', $columns, true ) && $availability_date ) :
						?>
						<li class="unit-availability"><span class="unit-detail-label label">Availability</span><?php rentfetch_single_floorplan_unit_availability( $availability_date ); ?></li><?php endif; ?>
					<?php
					if ( in_array( 'amenities', $columns, true ) && $amenities ) :
						?>
						<li class="unit-amenities"><span class="unit-detail-label label">Amenities</span><?php rentfetch_single_floorplan_unit_amenity_pills( $amenities ); ?></li><?php endif; ?>
					<?php
					if ( in_array( 'specials', $columns, true ) && $specials ) :
						?>
						<li class="unit-specials"><span class="unit-detail-label label">Specials</span><span class="unit-specials-pill"><?php echo esc_html( $specials ); ?></span></li><?php endif; ?>
					<li class="unit-buttons"><?php do_action( 'rentfetch_do_unit_button' ); ?></li>
				</ul>
			</details>
		<?php endforeach; ?>
	</div>
	<?php

	$post = $floorplan_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	setup_postdata( $post );
}

/**
 * Get deduplicated amenities from the units belonging to a floorplan.
 *
 * @param int|null $floorplan_post_id Floorplan post ID.
 * @return string[]
 */
function rentfetch_single_floorplan_get_aggregated_unit_amenities( $floorplan_post_id = null ) {
	$floorplan_post_id = $floorplan_post_id ? (int) $floorplan_post_id : get_the_ID();
	$floorplan_id      = get_post_meta( $floorplan_post_id, 'floorplan_id', true );
	$property_id       = get_post_meta( $floorplan_post_id, 'property_id', true );

	if ( ! $floorplan_id || ! $property_id ) {
		return array();
	}

	$unit_ids = get_posts(
		array(
			'post_type'      => 'units',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'   => 'property_id',
					'value' => $property_id,
				),
				array(
					'key'   => 'floorplan_id',
					'value' => $floorplan_id,
				),
			),
		)
	);

	$unit_features = array();
	foreach ( $unit_ids as $unit_id ) {
		foreach ( rentfetch_single_floorplan_get_unit_amenities( get_post_meta( $unit_id, 'amenities', true ) ) as $amenity ) {
			$key = strtolower( $amenity );
			if ( ! isset( $unit_features[ $key ] ) ) {
				$unit_features[ $key ] = $amenity;
			}
		}
	}

	return apply_filters( 'rentfetch_single_floorplan_aggregated_unit_amenities', array_values( $unit_features ), $floorplan_post_id );
}

/**
 * Output the description, unit features, and connected property amenities.
 *
 * @return void
 */
function rentfetch_single_floorplans_parts_features() {
	$description      = rentfetch_get_floorplan_description();
	$property_post_id = rentfetch_get_connected_property_post_id_for_floorplan( get_the_ID() );
	$amenities        = $property_post_id ? get_the_terms( $property_post_id, 'amenities' ) : array();
	$amenities        = is_wp_error( $amenities ) ? array() : $amenities;
	$unit_features    = rentfetch_single_floorplan_get_aggregated_unit_amenities();

	if ( ! $description && ! $amenities && ! $unit_features ) {
		return;
	}

	rentfetch_single_floorplans_section(
		'container-features',
		function () use ( $description, $amenities, $unit_features ) {
			echo '<div class="floorplan-features">';
			echo wp_kses_post( apply_filters( 'rentfetch_single_floorplan_features_headline', '<h2>Features</h2>' ) );
			if ( $description ) {
				printf( '<div class="floorplan-description">%s</div>', wp_kses_post( $description ) );
			}
			if ( $unit_features ) {
				echo '<div class="floorplan-unit-features"><h3>Unit Features</h3><ul>';
				foreach ( $unit_features as $unit_feature ) {
					printf( '<li>%s</li>', esc_html( $unit_feature ) );
				}
				echo '</ul></div>';
			}
			if ( $amenities ) {
				echo '<div class="floorplan-property-amenities"><h3>Property Amenities</h3><ul>';
				foreach ( $amenities as $amenity ) {
					printf( '<li>%s</li>', esc_html( $amenity->name ) );
				}
				echo '</ul></div>';
			}
			echo '</div>';
		},
		'features'
	);
}

/**
 * Output the connected property fee embed.
 *
 * @return void
 */
function rentfetch_single_floorplans_parts_property_fees() {
	$embed = rentfetch_get_property_fee_embed_from_floorplan_id( get_the_ID() );
	if ( ! $embed ) {
		return;
	}

	rentfetch_single_floorplans_section(
		'container-property-fees',
		function () use ( $embed ) {
			echo '<div class="property-fees">';
			echo wp_kses_post( apply_filters( 'rentfetch_single_floorplan_property_fees_headline', '<h2>Property Fees</h2>' ) );
			echo wp_kses( $embed, rentfetch_get_allowed_embed_html() );
			echo '</div>';
		},
		'property-fees'
	);
}

/**
 * Output the lower-page floorplan gallery when at least three images exist.
 *
 * @return void
 */
function rentfetch_single_floorplans_parts_gallery() {
	$images = rentfetch_get_floorplan_images();
	if ( count( (array) $images ) < 3 ) {
		return;
	}

	wp_enqueue_style( 'rentfetch-glightbox-style' );
	wp_enqueue_script( 'rentfetch-glightbox-script' );
	wp_enqueue_script( 'rentfetch-glightbox-init' );

	rentfetch_single_floorplans_section(
		'container-floorplan-gallery',
		function () use ( $images ) {
			echo '<div class="floorplan-gallery">';
			echo wp_kses_post( apply_filters( 'rentfetch_single_floorplan_gallery_headline', '<h2>Photos</h2>' ) );
			echo '<div class="floorplan-gallery-grid">';
			foreach ( $images as $index => $image ) {
				$image_url     = $image['url'];
				$display_width = 0 === $index ? 900 : 600;
				$display_url   = function_exists( 'rentfetch_get_resized_rentcafe_image_url' ) ? rentfetch_get_resized_rentcafe_image_url( $image_url, $display_width ) : $image_url;
				$sample_url    = $image['thumbnail_url'] ?? $display_url;
				$alt           = $image['alt'] ?? get_the_title();
				$hidden_class  = $index > 2 ? ' is-lightbox-only' : '';
				printf( '<div class="floorplan-gallery-image%s" data-floorplan-image-index="%s" data-floorplan-sample-src="%s">', esc_attr( $hidden_class ), (int) $index, esc_url( $sample_url ) );
				printf( '<a class="floorplan-gallery-link" href="%s" data-gallery="floorplan-lower-gallery"><img src="%s" alt="%s" loading="lazy" decoding="async">', esc_url( $image_url ), esc_url( $display_url ), esc_attr( $alt ) );
				if ( 2 === $index ) {
					printf( '<span class="floorplan-gallery-view-all">View all %s photos</span>', (int) count( $images ) );
				}
				echo '</a></div>';
			}
			echo '</div></div>';
		},
		'photos'
	);
}

/**
 * Get every unique manual or synced tour for the current floorplan.
 *
 * @return array[] Parsed tours.
 */
function rentfetch_get_single_floorplan_tours() {
	return rentfetch_get_floorplan_tours( get_the_ID() );
}

/**
 * Output the floorplan video and virtual-tour grid.
 *
 * @return void
 */
function rentfetch_single_floorplans_parts_tour() {
	$tours = rentfetch_get_single_floorplan_tours();
	if ( ! $tours ) {
		return;
	}
	$embeds = array();
	foreach ( $tours as $index => $tour ) {
		$embed = rentfetch_get_tour_embed_html( $tour['url'] );
		if ( 0 === $index ) {
			$embed = apply_filters( 'rentfetch_filter_floorplan_tour_embed', $embed );
		}
		if ( $embed ) {
			$embeds[] = $embed;
		}
	}
	if ( ! $embeds ) {
		return;
	}
	$embeds = apply_filters( 'rentfetch_single_floorplan_tour_embeds', $embeds, $tours );
	$embeds = is_array( $embeds ) ? $embeds : array();
	if ( ! $embeds ) {
		return;
	}
	$tour_count_class = count( $embeds ) >= 5 ? 'tour-count-5-plus' : 'tour-count-' . count( $embeds );

	rentfetch_single_floorplans_section(
		'container-tour floorplan-tours-section',
		function () use ( $embeds, $tour_count_class ) {
			echo '<div class="tour">';
			echo wp_kses_post( apply_filters( 'rentfetch_single_floorplan_tour_headline', '<h2>Take a look around</h2>' ) );
			printf( '<div class="floorplan-tours-grid %s">', esc_attr( $tour_count_class ) );
			foreach ( $embeds as $embed ) {
				echo '<div class="floorplan-tour-embed">';
				echo wp_kses( $embed, rentfetch_get_allowed_embed_html() );
				echo '</div>';
			}
			echo '</div>';
			echo '</div>';
		},
		'tour'
	);
}

/**
 * Output similar floorplans.
 *
 * @return void
 */
function rentfetch_single_floorplans_parts_similar() {
	if ( ! rentfetch_get_similar_floorplans() ) {
		return;
	}

	rentfetch_single_floorplans_section(
		'container-similar-floorplans',
		function () {
			global $floorplan_images_use_slider;

			$floorplan_images_use_slider = false;
			echo '<div class="similar-floorplans">';
			echo wp_kses_post( apply_filters( 'rentfetch_single_floorplan_more_floorplans_headline', '<h2>Similar Floor Plans</h2>' ) );
			rentfetch_similar_floorplans();
			echo '</div>';
			$floorplan_images_use_slider = null;
		},
		'similar-floorplans'
	);
}
