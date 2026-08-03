(function (document) {
	'use strict';

	var currentTooltip = null;
	var currentTooltipTarget = null;
	var pendingTooltipTarget = null;
	var tooltipActivationTimer = null;
	var tooltipRemovalTimer = null;
	var tooltipSelector =
		'.rentfetch-hierarchy [data-tooltip], .rf-hierarchy-navigation [data-tooltip]';

	function cancelTooltipActivation() {
		window.clearTimeout(tooltipActivationTimer);
		tooltipActivationTimer = null;
		pendingTooltipTarget = null;
	}

	function cancelTooltipRemoval() {
		window.clearTimeout(tooltipRemovalTimer);
		tooltipRemovalTimer = null;
	}

	function removeTooltip() {
		cancelTooltipActivation();
		cancelTooltipRemoval();
		if (currentTooltip && currentTooltip.parentNode) {
			currentTooltip.parentNode.removeChild(currentTooltip);
		}
		currentTooltip = null;
		currentTooltipTarget = null;
	}

	function scheduleTooltipRemoval() {
		cancelTooltipRemoval();
		tooltipRemovalTimer = window.setTimeout(removeTooltip, 150);
	}

	function keepTooltipOpen() {
		cancelTooltipActivation();
		cancelTooltipRemoval();
	}

	function diagnosticsAreActive() {
		var activeDiagnosticsTab = document.querySelector(
			'[data-rf-property-tab="diagnostics"][aria-selected="true"]'
		);

		return (
			'#diagnostics' === window.location.hash ||
			Boolean(activeDiagnosticsTab)
		);
	}

	function preserveDiagnostics(link) {
		var url;

		if (!link || !diagnosticsAreActive()) {
			return;
		}

		try {
			url = new URL(link.href, window.location.href);
		} catch (error) {
			return;
		}

		url.hash = 'diagnostics';
		link.href = url.toString();
	}

	// Keep relationship navigation in diagnostics, including a trip through the
	// current unit editor before that editor has tabs of its own.
	document.addEventListener('click', function (event) {
		var link = event.target.closest('[data-rf-debug-navigation]');

		if (link) {
			preserveDiagnostics(link);
		}
	});

	document.addEventListener('click', function (event) {
		var toggle = event.target.closest('.rentfetch-hierarchy .show-more-link');
		if (!toggle) {
			return;
		}

		event.preventDefault();
		event.stopPropagation();

		var unitsGrid = toggle.closest('.units-grid');
		var hiddenUnits = unitsGrid
			? unitsGrid.querySelector('.units-hidden')
			: null;
		if (!hiddenUnits) {
			return;
		}

		var willShow = hiddenUnits.hidden;
		hiddenUnits.hidden = !willShow;
		toggle.textContent = willShow
			? 'Show less'
			: 'Show ' +
				hiddenUnits.querySelectorAll('.hierarchy-item.unit').length +
				' more…';
	});

	function showTooltip(target) {
		var tooltipText = target.getAttribute('data-tooltip');
		if (!tooltipText) {
			return;
		}

		currentTooltip = document.createElement('div');
		currentTooltip.className = 'rentfetch-hierarchy-tooltip';
		currentTooltip.innerHTML = tooltipText;
		currentTooltip.addEventListener('mouseenter', keepTooltipOpen);
		currentTooltip.addEventListener('mouseleave', scheduleTooltipRemoval);
		document.body.appendChild(currentTooltip);

		var positioningTarget = target.closest(
			'.rf-hierarchy-navigation-floorplan-label-row'
		)
			? target.closest('.rf-hierarchy-navigation-floorplan-card')
			: target;
		var rect = positioningTarget.getBoundingClientRect();
		var tooltipRect = currentTooltip.getBoundingClientRect();
		var edge = 5;
		var spaceAbove = Math.max(0, rect.top - edge);
		var spaceBelow = Math.max(
			0,
			window.innerHeight - rect.bottom - edge
		);
		var placeBelow =
			spaceBelow >= tooltipRect.height || spaceBelow >= spaceAbove;
		var availableHeight = placeBelow ? spaceBelow : spaceAbove;

		currentTooltip.classList.add(placeBelow ? 'is-below' : 'is-above');
		tooltipRect = currentTooltip.getBoundingClientRect();

		if (tooltipRect.height > availableHeight) {
			currentTooltip.style.maxHeight = availableHeight + 'px';
			tooltipRect = currentTooltip.getBoundingClientRect();
		}

		var top = placeBelow
			? rect.bottom
			: rect.top - tooltipRect.height;
		var left = rect.left + (rect.width - tooltipRect.width) / 2;

		left = Math.max(edge, Math.min(left, window.innerWidth - tooltipRect.width - edge));
		currentTooltip.style.left = left + 'px';
		currentTooltip.style.top = top + 'px';
	}

	function refreshTooltip(target) {
		var config = window.rentfetchHierarchy;
		var postId = target.getAttribute('data-post-id');

		showTooltip(target);

		if (!postId || !config) {
			return;
		}

		var body = new window.FormData();
		body.append('action', config.action);
		body.append('nonce', config.nonce);
		body.append('post_id', postId);

		window.fetch(config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			cache: 'no-store',
			body: body
		})
			.then(function (response) {
				if (!response.ok) {
					throw new Error('Sync tooltip request failed.');
				}
				return response.json();
			})
			.then(function (response) {
				if (!response || !response.success || !response.data) {
					throw new Error('Sync tooltip data was unavailable.');
				}

				target.setAttribute('data-tooltip', response.data.tooltip || '');
			})
			.catch(function () {
				// Keep the embedded tooltip and retry the refresh on the next hover.
			});
	}

	function activateTooltip(target) {
		removeTooltip();
		currentTooltipTarget = target;
		refreshTooltip(target);
	}

	function scheduleTooltipActivation(target) {
		cancelTooltipActivation();
		pendingTooltipTarget = target;
		tooltipActivationTimer = window.setTimeout(function () {
			activateTooltip(target);
		}, 250);
	}

	document.addEventListener('mouseover', function (event) {
		var target = event.target.closest(tooltipSelector);
		if (!target || target.contains(event.relatedTarget)) {
			return;
		}

		cancelTooltipRemoval();
		if (target === currentTooltipTarget) {
			cancelTooltipActivation();
		} else if (currentTooltip) {
			scheduleTooltipActivation(target);
		} else {
			activateTooltip(target);
		}
	});

	document.addEventListener('mouseout', function (event) {
		var target = event.target.closest(tooltipSelector);
		var currentUnitCard =
			currentTooltipTarget &&
			currentTooltipTarget.closest('.rf-hierarchy-navigation-unit-list')
				? currentTooltipTarget.closest(
					'.rf-hierarchy-navigation-floorplan-card'
				)
				: null;

		if (
			currentUnitCard &&
			currentUnitCard.contains(event.target) &&
			event.relatedTarget &&
			(currentUnitCard.contains(event.relatedTarget) ||
				(currentTooltip && currentTooltip.contains(event.relatedTarget)))
		) {
			return;
		}

		if (!target) {
			if (currentUnitCard && currentUnitCard.contains(event.target)) {
				scheduleTooltipRemoval();
			}
			return;
		}

		if (target.contains(event.relatedTarget)) {
			return;
		}

		if (target === pendingTooltipTarget) {
			cancelTooltipActivation();
		}

		if (
			(!currentTooltip || !currentTooltip.contains(event.relatedTarget)) &&
			(!event.relatedTarget ||
				!event.relatedTarget.closest ||
				!event.relatedTarget.closest(tooltipSelector))
		) {
			scheduleTooltipRemoval();
		}
	});

	document.addEventListener('mouseleave', removeTooltip);
	window.addEventListener('resize', removeTooltip);
	window.addEventListener('scroll', removeTooltip, true);
})(document);
