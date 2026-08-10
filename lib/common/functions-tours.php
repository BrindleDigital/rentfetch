<?php
/**
 * Shared manual tour parsing and embeds.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Check whether a host is a domain or one of its subdomains.
 *
 * @param string $host   URL host.
 * @param string $domain Expected provider domain.
 * @return bool
 */
function rentfetch_tour_host_matches( $host, $domain ) {
	return (bool) preg_match( '~(?:^|\.)' . preg_quote( $domain, '~' ) . '$~i', $host );
}

/**
 * Normalize a direct tour URL or iframe embed code.
 *
 * @param string $value Direct URL or iframe embed code.
 * @return array{url:string, embed_url:string, link_url:string, type:string}|array{}
 */
function rentfetch_parse_tour_value( $value ) {
	$original_value = (string) $value;
	$value          = html_entity_decode( trim( $original_value ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$is_iframe      = false;

	if ( preg_match( '~<iframe[^>]+src\s*=\s*(["\'])(.*?)\1~i', $value, $matches ) ) {
		$value     = $matches[2];
		$is_iframe = true;
	}

	$url = esc_url_raw( trim( $value ), array( 'http', 'https' ) );
	if ( ! $url ) {
		return array();
	}

	$parts = wp_parse_url( $url );
	$host  = strtolower( (string) ( $parts['host'] ?? '' ) );
	$path  = trim( (string) ( $parts['path'] ?? '' ), '/' );
	$query = array();
	parse_str( (string) ( $parts['query'] ?? '' ), $query );

	$tour = array(
		'url'       => $url,
		'embed_url' => $url,
		'link_url'  => $url,
		'type'      => $is_iframe ? 'iframe' : 'link',
	);

	$youtube_id = '';
	if ( rentfetch_tour_host_matches( $host, 'youtu.be' ) ) {
		$youtube_id = strtok( $path, '/' );
	} elseif ( rentfetch_tour_host_matches( $host, 'youtube.com' ) || rentfetch_tour_host_matches( $host, 'youtube-nocookie.com' ) ) {
		$youtube_id = (string) ( $query['v'] ?? '' );
		if ( ! $youtube_id && preg_match( '~(?:^|/)(?:embed|shorts)/([^/]+)~', $path, $matches ) ) {
			$youtube_id = $matches[1];
		}
	}

	if ( $youtube_id ) {
		$tour['embed_url'] = 'https://www.youtube.com/embed/' . rawurlencode( $youtube_id );
		$tour['link_url']  = 'https://www.youtube.com/watch?v=' . rawurlencode( $youtube_id );
		$tour['type']      = 'youtube';
	} elseif ( rentfetch_tour_host_matches( $host, 'matterport.com' ) ) {
		$tour['type'] = 'matterport';
	} elseif ( rentfetch_tour_host_matches( $host, 'theviewvr.com' ) ) {
		$tour['type'] = 'virtual_tour';
	} elseif ( rentfetch_tour_host_matches( $host, 'zillow.com' ) && 0 === strpos( $path, 'view-3d-home/' ) ) {
		$tour['type'] = 'virtual_tour';
	} elseif ( rentfetch_tour_host_matches( $host, 'vimeo.com' ) && preg_match( '~(?:^|/)(\d+)$~', $path, $matches ) ) {
		$tour['embed_url'] = 'https://player.vimeo.com/video/' . $matches[1];
		$tour['type']      = 'vimeo';
	} elseif ( 'drive.google.com' === $host && false !== strpos( $path, 'file/d/' ) ) {
		$tour['embed_url'] = preg_replace( '~/view(?:\?.*)?$~', '/preview', $url );
		$tour['type']      = 'google_drive';
	}

	$tour = apply_filters( 'rentfetch_parsed_tour_value', $tour, $original_value );

	return is_array( $tour ) ? $tour : array();
}

/**
 * Normalize and deduplicate a list of tour URLs or iframe codes.
 *
 * @param array $values Tour URLs or iframe codes.
 * @return array[] Parsed tours.
 */
function rentfetch_parse_tour_values( $values ) {
	$tours = array();

	foreach ( (array) $values as $value ) {
		$tour = rentfetch_parse_tour_value( $value );
		if ( ! empty( $tour['embed_url'] ) ) {
			$tours[ $tour['embed_url'] ] = $tour;
		}
	}

	return array_values( $tours );
}

/**
 * Get post tours in display priority: manual, virtual tours, videos, other.
 *
 * @param int|null $post_id Post ID. Defaults to the current post.
 * @return array[] Parsed tours.
 */
function rentfetch_get_post_tours( $post_id = null ) {
	$post_id      = null === $post_id ? get_the_ID() : $post_id;
	$synced_tours = get_post_meta( $post_id, 'synced_tours', true );
	$values       = array( get_post_meta( $post_id, 'tour', true ) );
	$priorities   = array( 'virtual_tour', 'tour_360', 'video' );

	foreach ( $priorities as $priority ) {
		foreach ( is_array( $synced_tours ) ? $synced_tours : array() as $tour ) {
			if ( is_array( $tour ) && ( $tour['type'] ?? '' ) === $priority && ! empty( $tour['url'] ) ) {
				$values[] = $tour['url'];
			}
		}
	}

	foreach ( is_array( $synced_tours ) ? $synced_tours : array() as $tour ) {
		if ( is_array( $tour ) && ! in_array( $tour['type'] ?? '', $priorities, true ) && ! empty( $tour['url'] ) ) {
			$values[] = $tour['url'];
		}
	}

	return rentfetch_parse_tour_values( $values );
}

/**
 * Get property tours.
 *
 * @param int|null $post_id Property post ID. Defaults to the current post.
 * @return array[] Parsed tours.
 */
function rentfetch_get_property_tours( $post_id = null ) {
	return rentfetch_get_post_tours( $post_id );
}

/**
 * Get floorplan tours.
 *
 * @param int|null $post_id Floorplan post ID. Defaults to the current post.
 * @return array[] Parsed tours.
 */
function rentfetch_get_floorplan_tours( $post_id = null ) {
	return rentfetch_get_post_tours( $post_id );
}

/**
 * Render a safe iframe for a direct tour URL or iframe embed code.
 *
 * @param string $value Direct URL or iframe embed code.
 * @return string Iframe markup.
 */
function rentfetch_get_tour_embed_html( $value ) {
	$tour = rentfetch_parse_tour_value( $value );
	if ( empty( $tour['embed_url'] ) ) {
		return '';
	}
	if ( 'link' === ( $tour['type'] ?? 'link' ) ) {
		$embed = function_exists( 'wp_oembed_get' ) ? wp_oembed_get( $tour['url'] ) : '';
		return is_string( $embed ) ? $embed : '';
	}

	return sprintf(
		'<iframe src="%s" title="Video or virtual tour" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="autoplay; encrypted-media; fullscreen; picture-in-picture; xr-spatial-tracking" allowfullscreen></iframe>',
		esc_url( $tour['embed_url'] )
	);
}
