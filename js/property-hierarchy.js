(function (document) {
	'use strict';

	var currentTooltip = null;

	function removeTooltip() {
		if (currentTooltip && currentTooltip.parentNode) {
			currentTooltip.parentNode.removeChild(currentTooltip);
		}
		currentTooltip = null;
	}

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

	document.addEventListener('mouseover', function (event) {
		var target = event.target.closest('.rentfetch-hierarchy [data-tooltip]');
		if (!target || target.contains(event.relatedTarget)) {
			return;
		}

		removeTooltip();
		var tooltipText = target.getAttribute('data-tooltip');
		if (!tooltipText) {
			return;
		}

		currentTooltip = document.createElement('div');
		currentTooltip.className = 'rentfetch-hierarchy-tooltip';
		currentTooltip.innerHTML = tooltipText;
		document.body.appendChild(currentTooltip);

		var rect = target.getBoundingClientRect();
		currentTooltip.style.left = rect.left + rect.width / 2 + 'px';
		currentTooltip.style.top = Math.max(5, rect.top - 30) + 'px';
		currentTooltip.style.transform = 'translate(-50%, -100%)';
	});

	document.addEventListener('mouseout', function (event) {
		var target = event.target.closest('.rentfetch-hierarchy [data-tooltip]');
		if (
			target &&
			!target.contains(event.relatedTarget) &&
			(!event.relatedTarget ||
				!event.relatedTarget.closest ||
				!event.relatedTarget.closest(
					'.rentfetch-hierarchy [data-tooltip]'
				))
		) {
			removeTooltip();
		}
	});

	document.addEventListener('mouseleave', removeTooltip);
})(document);
