<?php
/**
 * JSON utilities for Rentfetch.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Attempt to pretty-print or lightly repair API JSON responses.
 *
 * This will accept a string/array/object and return a pretty-printed JSON string
 * when possible. It performs conservative repairs (remove trailing commas before
 * closers, append missing closing bracket/brace) and returns the original input
 * when it cannot be parsed/repaired.
 *
 * @param mixed $value     JSON string or array/object.
 * @param bool  $repaired  Optional. Passed by reference; set to true when a repair was applied.
 * @return string Pretty-printed JSON or original string when not parseable.
 */
function rentfetch_pretty_json( $value, &$repaired = null ) {
    $repaired = false;

    // If we already have a PHP array/object, pretty-print directly.
    if ( is_array( $value ) || is_object( $value ) ) {
        $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;
        if ( defined( 'JSON_INVALID_UTF8_SUBSTITUTE' ) ) {
            $flags |= JSON_INVALID_UTF8_SUBSTITUTE;
        }
        if ( defined( 'JSON_PARTIAL_OUTPUT_ON_ERROR' ) ) {
            $flags |= JSON_PARTIAL_OUTPUT_ON_ERROR;
        }
        return json_encode( $value, $flags );
    }

    if ( ! is_string( $value ) ) {
        return (string) $value;
    }

    $raw = $value;

    // Build safe encode flags for later use.
    $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;
    if ( defined( 'JSON_INVALID_UTF8_SUBSTITUTE' ) ) {
        $flags |= JSON_INVALID_UTF8_SUBSTITUTE;
    }
    if ( defined( 'JSON_PARTIAL_OUTPUT_ON_ERROR' ) ) {
        $flags |= JSON_PARTIAL_OUTPUT_ON_ERROR;
    }

    // 1) Try decoding strictly (use JSON_THROW_ON_ERROR when available).
    if ( defined( 'JSON_THROW_ON_ERROR' ) ) {
        try {
            $decoded = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );
            return json_encode( $decoded, $flags );
        } catch ( \JsonException $e ) {
            // fall through to repair attempts
        }
    } else {
        $decoded = json_decode( $raw, true );
        if ( null !== $decoded && json_last_error() === JSON_ERROR_NONE ) {
            return json_encode( $decoded, $flags );
        }
    }

    // 2) If input contains invalid UTF-8, try a best-effort conversion and decode again.
    if ( function_exists( 'mb_check_encoding' ) && ! mb_check_encoding( $raw, 'UTF-8' ) ) {
        // Try converting from common legacy encodings (Windows-1252) to UTF-8.
        $attempt = @mb_convert_encoding( $raw, 'UTF-8', 'Windows-1252' );
        if ( $attempt && $attempt !== $raw ) {
            if ( defined( 'JSON_THROW_ON_ERROR' ) ) {
                try {
                    $decoded = json_decode( $attempt, true, 512, JSON_THROW_ON_ERROR );
                    $repaired = true;
                    return json_encode( $decoded, $flags );
                } catch ( \JsonException $e ) {
                    // continue
                }
            } else {
                $decoded = json_decode( $attempt, true );
                if ( null !== $decoded && json_last_error() === JSON_ERROR_NONE ) {
                    $repaired = true;
                    return json_encode( $decoded, $flags );
                }
            }
        }
    }

    // 3) Lightweight repairs: trim, remove trailing commas before closers, append missing closers
    $repair = trim( $raw );
    $repair = preg_replace( '/,\s*(\]|\})/', '$1', $repair );

    $first_char = substr( $repair, 0, 1 );
    $last_char  = substr( $repair, -1 );
    if ( '[' === $first_char && ']' !== $last_char ) {
        $repair .= ']';
    } elseif ( '{' === $first_char && '}' !== $last_char ) {
        $repair .= '}';
    }

    if ( defined( 'JSON_THROW_ON_ERROR' ) ) {
        try {
            $decoded = json_decode( $repair, true, 512, JSON_THROW_ON_ERROR );
            $repaired = true;
            return json_encode( $decoded, $flags );
        } catch ( \JsonException $e ) {
            // continue to more aggressive repair
        }
    } else {
        $decoded = json_decode( $repair, true );
        if ( null !== $decoded && json_last_error() === JSON_ERROR_NONE ) {
            $repaired = true;
            return json_encode( $decoded, $flags );
        }
    }

    // 4) As a last-ditch, conservatively escape unescaped inner double-quotes inside string values.
    $escaped_inner = '';
    $len = strlen( $repair );
    $in_string = false;
    for ( $i = 0; $i < $len; $i++ ) {
        $char = $repair[ $i ];

        // Handle backslash escapes: copy backslash and next char literally.
        if ( '\\' === $char ) {
            $escaped_inner .= $char;
            if ( $i + 1 < $len ) {
                $escaped_inner .= $repair[ $i + 1 ];
                $i++;
            }
            continue;
        }

        if ( '"' === $char ) {
            // Count preceding backslashes to see if this quote is escaped.
            $j = $i - 1;
            $slashes = 0;
            while ( $j >= 0 && $repair[ $j ] === '\\' ) {
                $slashes++;
                $j--;
            }

            $is_escaped = ( $slashes % 2 ) === 1;

            if ( ! $is_escaped ) {
                if ( ! $in_string ) {
                    // Opening quote
                    $in_string = true;
                    $escaped_inner .= '"';
                    continue;
                }

                // Look ahead to next non-space char to decide if this is a closer.
                $k = $i + 1;
                while ( $k < $len && ctype_space( $repair[ $k ] ) ) {
                    $k++;
                }

                $next = $k < $len ? $repair[ $k ] : '';
                if ( in_array( $next, array( ':', ',', '}', ']' ), true ) ) {
                    // closing quote
                    $in_string = false;
                    $escaped_inner .= '"';
                    continue;
                }

                // otherwise escape this inner quote
                $escaped_inner .= '\\\"';
                continue;
            }
        }

        $escaped_inner .= $char;
    }

    if ( $escaped_inner && $escaped_inner !== $repair ) {
        if ( defined( 'JSON_THROW_ON_ERROR' ) ) {
            try {
                $decoded = json_decode( $escaped_inner, true, 512, JSON_THROW_ON_ERROR );
                $repaired = true;
                return json_encode( $decoded, $flags );
            } catch ( \JsonException $e ) {
                // fall through
            }
        } else {
            $decoded = json_decode( $escaped_inner, true );
            if ( null !== $decoded && json_last_error() === JSON_ERROR_NONE ) {
                $repaired = true;
                return json_encode( $decoded, $flags );
            }
        }
    }

    // Nothing worked — return original raw input.
    return $raw;
}
