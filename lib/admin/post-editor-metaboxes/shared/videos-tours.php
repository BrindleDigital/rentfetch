<?php
/**
 * Shared video and tour editor fields.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get a plain-language label for a synced tour's API source field.
 *
 * @param array $tour Normalized tour record.
 * @return string Source label.
 */
function rentfetch_get_synced_tour_source_label( $tour ) {
	$source       = trim( (string) ( $tour['source'] ?? '' ) );
	$source_label = $source ? ucwords( str_replace( array( '-', '_' ), ' ', $source ) ) : 'API';
	$field_labels = array(
		'propertyVideoEmbedCode'  => 'property video embed code',
		'propertyVirtualTourUrl'  => 'property virtual tour URL',
		'tour360EmbedCode'        => '360° tour embed code',
		'fpVideoEmbedCode'        => 'floor plan video embed code',
		'floorplanVirtualTourUrl' => 'floor plan virtual tour URL',
		'unitEmbedVideo'          => 'unit video embed code',
		'unitVirtualTourUrl'      => 'unit virtual tour URL',
	);

	return $source_label . ' ' . ( $field_labels[ $tour['source_field'] ?? '' ] ?? 'synced video or tour' );
}

/**
 * Get a short provider ID for identifying duplicate media.
 *
 * @param string $url Synced video or tour URL.
 * @return string Provider ID or stable URL fingerprint.
 */
function rentfetch_get_synced_tour_identifier( $url ) {
	$tour  = rentfetch_parse_tour_value( $url );
	$url   = $tour['embed_url'] ?? '';
	$parts = wp_parse_url( $url );
	$host  = strtolower( (string) ( $parts['host'] ?? '' ) );
	$path  = trim( (string) ( $parts['path'] ?? '' ), '/' );
	$query = array();

	parse_str( (string) ( $parts['query'] ?? '' ), $query );

	if ( rentfetch_tour_host_matches( $host, 'matterport.com' ) && ! empty( $query['m'] ?? $query['model'] ?? '' ) ) {
		return (string) ( $query['m'] ?? $query['model'] );
	}

	if ( rentfetch_tour_host_matches( $host, 'matterport.com' ) && preg_match( '~^models/([^/]+)~', $path, $matches ) ) {
		return $matches[1];
	}

	if ( rentfetch_tour_host_matches( $host, 'youtu.be' ) ) {
		return strtok( $path, '/' );
	}

	if ( ( rentfetch_tour_host_matches( $host, 'youtube.com' ) || rentfetch_tour_host_matches( $host, 'youtube-nocookie.com' ) ) && preg_match( '~(?:^|/)embed/([^/]+)~', $path, $matches ) ) {
		return $matches[1];
	}

	if ( rentfetch_tour_host_matches( $host, 'vimeo.com' ) && preg_match( '~(?:^|/)(\d+)$~', $path, $matches ) ) {
		return $matches[1];
	}

	if ( 'drive.google.com' === $host && preg_match( '~(?:^|/)file/d/([^/]+)~', $path, $matches ) ) {
		return $matches[1];
	}

	return substr( hash( 'sha256', $url ), 0, 12 );
}

/**
 * Render synced video and tour previews for any supported post type.
 *
 * @param WP_Post $post Current property, floor plan, or unit.
 * @return void
 */
function rentfetch_synced_tours_metabox_callback( $post ) {
	$tours = get_post_meta( $post->ID, 'synced_tours', true );
	$tours = is_array( $tours ) ? $tours : array();
	$tours = array_filter(
		$tours,
		static function ( $tour ) {
			return is_array( $tour ) && ! empty( rentfetch_parse_tour_value( $tour['url'] ?? '' ) );
		}
	);
	?>
	<div class="rf-metabox">
		<div class="field">
			<div class="column">
				<label>Synced Videos and Tours</label>
				<p class="description rf-synced-tours-description">Read-only; updated by the sync source.</p>
			</div>
			<div class="column">
				<?php if ( empty( $tours ) ) : ?>
					<p class="description">No synced videos or tours are available.</p>
				<?php else : ?>
					<div class="rf-synced-tours-grid">
						<?php foreach ( $tours as $tour ) : ?>
							<?php
							$parsed_tour = rentfetch_parse_tour_value( $tour['url'] );
							$embed_html  = rentfetch_get_tour_embed_html( $tour['url'] );
							$identifier  = rentfetch_get_synced_tour_identifier( $tour['url'] );
							?>
							<div class="rf-synced-tour-card">
								<h4><?php echo esc_html( rentfetch_get_synced_tour_source_label( $tour ) ); ?></h4>
								<?php
								if ( $embed_html ) :
									?>
									<div class="rf-synced-tour-preview"><?php echo wp_kses( $embed_html, rentfetch_get_allowed_embed_html() ); ?></div><?php endif; ?>
								<div class="rf-synced-tour-actions">
									<a class="rf-synced-tour-link" href="<?php echo esc_url( $parsed_tour['link_url'] ); ?>" target="_blank" rel="noopener noreferrer">Open video or tour</a>
									<code>ID: <?php echo esc_html( $identifier ); ?></code>
								</div>
							</div>
						<?php endforeach; ?>
						</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}
