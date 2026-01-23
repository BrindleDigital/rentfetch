/* global window, document, rentfetchAnalyticsSettings */

(function () {
	'use strict';

	var body = document.body;
	if ( ! body ) {
		return;
	}

	var settings = window.rentfetchAnalyticsSettings || {};
	var enabledSetting = /^(1|true|yes|on)$/i.test( String( settings.enabled ) );
	var debugEnabled = /^(1|true|yes|on)$/i.test( String( settings.debug ) );
	var debugAllowed = /^(1|true|yes|on)$/i.test( String( settings.debugAllowed ) );
	debugEnabled = debugEnabled && debugAllowed;
	var debugOverride = '';
	if ( window.location && window.location.search ) {
		try {
			debugOverride = new URLSearchParams( window.location.search ).get( 'rentfetch_debug' ) || '';
		} catch ( error ) {
			debugOverride = '';
		}
	}
	if ( debugOverride ) {
		debugEnabled = /^(1|true|yes|on)$/i.test( debugOverride );
	}
	var hasGtag = typeof window.gtag === 'function';
	var hasDataLayer = Array.isArray( window.dataLayer );

	function getAnalyticsInfo() {
		var info = {
			hasGtag: hasGtag,
			hasDataLayer: hasDataLayer,
			dataLayerLength: hasDataLayer ? window.dataLayer.length : 0,
			measurementIds: [],
			gtmContainers: []
		};

		function collectIdsFromValue( value, seen, depth ) {
			if ( ! value || depth > 6 ) {
				return;
			}
			if ( typeof value === 'string' ) {
				if ( /^G-[A-Z0-9]+$/i.test( value ) ) {
					info.measurementIds.push( value );
				}
				if ( /^GTM-[A-Z0-9]+$/i.test( value ) ) {
					info.gtmContainers.push( value );
				}
				return;
			}
			if ( typeof value !== 'object' ) {
				return;
			}
			if ( seen.has( value ) ) {
				return;
			}
			seen.add( value );
			if ( Array.isArray( value ) ) {
				value.forEach( function ( entry ) {
					collectIdsFromValue( entry, seen, depth + 1 );
				} );
				return;
			}
			Object.keys( value ).forEach( function ( key ) {
				collectIdsFromValue( value[ key ], seen, depth + 1 );
			} );
		}

		if ( hasDataLayer ) {
			window.dataLayer.forEach( function ( entry ) {
				if ( Array.isArray( entry ) ) {
					if ( entry[ 0 ] === 'config' && entry[ 1 ] ) {
						info.measurementIds.push( entry[ 1 ] );
					}
					if ( entry[ 0 ] === 'js' && entry[ 1 ] ) {
						info.jsTimestamp = entry[ 1 ];
					}
				} else if ( entry && typeof entry === 'object' ) {
					if ( entry['gtm.id'] ) {
						info.gtmContainers.push( entry['gtm.id'] );
					}
					if ( entry['gtm.containerId'] ) {
						info.gtmContainers.push( entry['gtm.containerId'] );
					}
					if ( entry['event'] === 'config' && entry['config'] ) {
						info.measurementIds.push( entry['config'] );
					}
				}
			} );
		}

		if ( window.google_tag_data ) {
			collectIdsFromValue( window.google_tag_data, new Set(), 0 );
		}

		info.measurementIds = Array.from( new Set( info.measurementIds.filter( Boolean ) ) );
		info.gtmContainers = Array.from( new Set( info.gtmContainers.filter( Boolean ) ) );

		return info;
	}

	var analyticsInfo = getAnalyticsInfo();

	window.rentfetchAnalyticsStatus = {
		enabled: enabledSetting,
		debug: debugEnabled,
		debugAllowed: debugAllowed,
		hasGtag: hasGtag,
		hasDataLayer: hasDataLayer,
		analyticsInfo: analyticsInfo
	};

	if ( window.console && typeof window.console.log === 'function' ) {
		window.console.log( '[rentfetch] analytics events script loaded', window.rentfetchAnalyticsStatus, {
			settings: settings,
			debugOverride: debugOverride
		} );
	}

	function getContextFromElement( element ) {
		var context = {};
		var node = element;

		while ( node ) {
			if ( node.dataset ) {
				if ( ! context.property_id && node.dataset.rentfetchPropertyId ) {
					context.property_id = node.dataset.rentfetchPropertyId;
				}
				if ( ! context.property_name && node.dataset.rentfetchPropertyName ) {
					context.property_name = node.dataset.rentfetchPropertyName;
				}
				if ( ! context.property_city && node.dataset.rentfetchPropertyCity ) {
					context.property_city = node.dataset.rentfetchPropertyCity;
				}
				if ( ! context.floorplan_id && node.dataset.rentfetchFloorplanId ) {
					context.floorplan_id = node.dataset.rentfetchFloorplanId;
				}
				if ( ! context.floorplan_name && node.dataset.rentfetchFloorplanName ) {
					context.floorplan_name = node.dataset.rentfetchFloorplanName;
				}
			}
			node = node.parentElement;
		}

		return context;
	}

	function trackEvent( eventName, params ) {
		if ( ! eventName ) {
			return { sent: false, method: 'none' };
		}

		params = params || {};

		if ( hasGtag ) {
			if ( analyticsInfo && Array.isArray( analyticsInfo.measurementIds ) && analyticsInfo.measurementIds.length ) {
				params = Object.assign( { send_to: analyticsInfo.measurementIds }, params );
			}
			window.gtag( 'event', eventName, params );
			return { sent: true, method: 'gtag' };
		}

		if ( hasDataLayer ) {
			window.dataLayer.push( Object.assign( { event: eventName }, params ) );
			return { sent: true, method: 'dataLayer' };
		}

		return { sent: false, method: 'none' };
	}

	function showDebugPanel( eventName, params, result, info ) {
		if ( ! debugEnabled ) {
			return;
		}

		var panel = document.getElementById( 'rentfetch-analytics-debug-panel' );
		if ( ! panel ) {
			panel = document.createElement( 'div' );
			panel.id = 'rentfetch-analytics-debug-panel';
			panel.style.position = 'fixed';
			panel.style.right = '16px';
			panel.style.bottom = '16px';
			panel.style.zIndex = '999999';
			panel.style.width = '360px';
			panel.style.maxHeight = '70vh';
			panel.style.overflow = 'auto';
			panel.style.background = '#1d2327';
			panel.style.color = '#f0f0f1';
			panel.style.padding = '12px 14px';
			panel.style.borderRadius = '8px';
			panel.style.boxShadow = '0 8px 24px rgba(0,0,0,0.2)';
			panel.style.fontSize = '12px';
			panel.style.fontFamily = 'Consolas, Monaco, monospace';
			panel.style.whiteSpace = 'pre-wrap';
			panel.style.pointerEvents = 'auto';

			var header = document.createElement( 'div' );
			header.textContent = 'Rentfetch Analytics Debug';
			header.style.fontWeight = '600';
			header.style.marginBottom = '8px';

			var note = document.createElement( 'div' );
			note.textContent = 'Verify in GA4 DebugView or Tag Assistant.';
			note.style.color = '#c3c4c7';
			note.style.marginBottom = '8px';

			var list = document.createElement( 'div' );
			list.id = 'rentfetch-analytics-debug-list';
			list.textContent = 'Click a tracked link or control to see the event payload sent to Google Analytics.';
			list.style.color = '#c3c4c7';
			list.style.fontStyle = 'italic';

			panel.appendChild( header );
			panel.appendChild( note );
			panel.appendChild( list );
			document.body.appendChild( panel );
		}

		var listContainer = document.getElementById( 'rentfetch-analytics-debug-list' );
		if ( ! listContainer ) {
			return;
		}

		var entry = document.createElement( 'div' );
		entry.textContent =
			'Event: ' + eventName +
			'\nSent: ' + ( result && result.sent ? 'yes' : 'no' ) +
			'\nMethod: ' + ( result && result.method ? result.method : 'none' ) +
			'\nData: ' + JSON.stringify( params, null, 2 ) +
			'\nAnalytics detected: ' + JSON.stringify( info || {}, null, 2 );

		listContainer.innerHTML = '';
		listContainer.appendChild( entry );
	}

	if ( debugEnabled ) {
		showDebugPanel( 'Waiting for click', {}, { sent: false, method: 'none' }, analyticsInfo );
	}

	function findTrackedTarget( element ) {
		var node = element;
		while ( node ) {
			if ( node.getAttribute && node.getAttribute( 'data-rentfetch-event' ) ) {
				return node;
			}
			node = node.parentElement || node.parentNode;
		}
		return null;
	}

	document.addEventListener( 'click', function ( event ) {
		if ( ! enabledSetting ) {
			return;
		}

		if ( event.button && event.button !== 0 ) {
			return;
		}

		var target = null;
		if ( event.target && typeof event.target.closest === 'function' ) {
			target = event.target.closest( '[data-rentfetch-event]' );
		}
		if ( ! target ) {
			target = findTrackedTarget( event.target );
		}

		if ( target ) {
			var eventName = target.dataset.rentfetchEvent;
			var context = getContextFromElement( target );
			var href = target.getAttribute( 'href' );

			if ( href ) {
				context.link_url = href;
			}

			var result = trackEvent( eventName, context );

			if ( debugEnabled && debugAllowed && ! event.metaKey && ! event.ctrlKey ) {
				event.preventDefault();
				event.stopImmediatePropagation();
				event.stopPropagation();
				showDebugPanel( eventName, context, result, analyticsInfo );
			}

			return;
		}

		if ( body.classList.contains( 'single-properties' ) ) {
			var specialLink = event.target.closest( '.specials a' );

			if ( specialLink ) {
				var specialContext = getContextFromElement( specialLink );
				var specialHref = specialLink.getAttribute( 'href' );

				if ( specialHref ) {
					specialContext.link_url = specialHref;
				}

				var specialResult = trackEvent( 'rentfetch_special_view', specialContext );

				if ( debugEnabled && debugAllowed && ! event.metaKey && ! event.ctrlKey ) {
					event.preventDefault();
					event.stopImmediatePropagation();
					event.stopPropagation();
					showDebugPanel( 'rentfetch_special_view', specialContext, specialResult, analyticsInfo );
				}

				return;
			}
		}
	}, true );
})();
