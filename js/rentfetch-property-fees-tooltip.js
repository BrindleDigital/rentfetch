/**
 * Property Fees Tooltip functionality
 *
 * Handles non-native tooltips for displaying HTML content
 * associated with property fee descriptions.
 *
 * @package rentfetch
 */

(function ($) {
	'use strict';

	// Tooltip element reference
	let $tooltip = null;
	let hideTimeout = null;

	/**
	 * Initialize tooltip functionality
	 */
	function init() {
		// Create tooltip element if it doesn't exist
		if (!$tooltip) {
			$tooltip = $(
				'<div class="rentfetch-fee-tooltip" role="tooltip" aria-hidden="true"></div>'
			);
			$('body').append($tooltip);
		}

		// Bind events using event delegation for dynamically loaded content
		$(document).on(
			'mouseenter',
			'.fee-description-with-tooltip',
			showTooltip
		);
		$(document).on(
			'mouseleave',
			'.fee-description-with-tooltip',
			hideTooltipDelayed
		);
		$(document).on('focus', '.fee-description-with-tooltip', showTooltip);
		$(document).on('blur', '.fee-description-with-tooltip', hideTooltip);
		$(document).on(
			'click',
			'.fee-description-with-tooltip .fee-info-icon',
			toggleTooltipOnClick
		);

		// Keep tooltip visible when hovering over it
		$(document).on('mouseenter', '.rentfetch-fee-tooltip', function () {
			clearTimeout(hideTimeout);
		});
		$(document).on('mouseleave', '.rentfetch-fee-tooltip', hideTooltip);

		// Close tooltip when clicking outside
		$(document).on('click', function (e) {
			if (
				!$(e.target).closest(
					'.fee-description-with-tooltip, .rentfetch-fee-tooltip'
				).length
			) {
				hideTooltip();
			}
		});

		// Close tooltip on escape key
		$(document).on('keydown', function (e) {
			if (e.key === 'Escape') {
				hideTooltip();
			}
		});
	}

	/**
	 * Show tooltip
	 *
	 * @param {Event} e The event object
	 */
	function showTooltip(e) {
		clearTimeout(hideTimeout);

		const $trigger = $(this);
		const content = $trigger.attr('data-tooltip-content');

		if (!content) {
			return;
		}

		// Set content (HTML is allowed)
		$tooltip.html(content);

		// Position tooltip
		positionTooltip($trigger);

		// Show tooltip
		$tooltip.addClass('is-visible').attr('aria-hidden', 'false');
	}

	/**
	 * Hide tooltip with a small delay (for hover between trigger and tooltip)
	 */
	function hideTooltipDelayed() {
		hideTimeout = setTimeout(hideTooltip, 150);
	}

	/**
	 * Hide tooltip immediately
	 */
	function hideTooltip() {
		clearTimeout(hideTimeout);
		if ($tooltip) {
			$tooltip.removeClass('is-visible').attr('aria-hidden', 'true');
		}
	}

	/**
	 * Toggle tooltip on click (for mobile/touch devices)
	 *
	 * @param {Event} e The event object
	 */
	function toggleTooltipOnClick(e) {
		e.preventDefault();
		e.stopPropagation();

		const $trigger = $(this).closest('.fee-description-with-tooltip');

		if ($tooltip.hasClass('is-visible')) {
			hideTooltip();
		} else {
			showTooltip.call($trigger[0], e);
		}
	}

	/**
	 * Position tooltip relative to the info icon element
	 *
	 * @param {jQuery} $trigger The trigger element
	 */
	function positionTooltip($trigger) {
		// Find the info icon within the trigger to center on it
		const $icon = $trigger.find('.fee-info-icon');
		const $positionElement = $icon.length ? $icon : $trigger;

		const elementOffset = $positionElement.offset();
		const elementWidth = $positionElement.outerWidth();
		const elementHeight = $positionElement.outerHeight();

		// First, make tooltip visible but off-screen to measure dimensions
		$tooltip.css({
			visibility: 'hidden',
			display: 'block',
			left: '-9999px',
		});

		const tooltipWidth = $tooltip.outerWidth();
		const tooltipHeight = $tooltip.outerHeight();
		const windowWidth = $(window).width();
		const windowHeight = $(window).height();
		const scrollTop = $(window).scrollTop();
		const scrollLeft = $(window).scrollLeft();

		// Calculate the center point of the trigger element (where caret should point)
		const triggerCenterX = elementOffset.left + elementWidth / 2;

		// Calculate ideal position (below and centered on the icon)
		let idealLeft = triggerCenterX - tooltipWidth / 2;
		let left = idealLeft;
		let top = elementOffset.top + elementHeight + 10;

		// Adjust horizontal position if tooltip goes off screen
		if (left < scrollLeft + 10) {
			left = scrollLeft + 10;
		} else if (left + tooltipWidth > scrollLeft + windowWidth - 10) {
			left = scrollLeft + windowWidth - tooltipWidth - 10;
		}

		// Calculate caret position as percentage based on where trigger center is relative to tooltip
		// The caret should point to the trigger's center
		const caretPositionPx = triggerCenterX - left;
		const caretPositionPercent = (caretPositionPx / tooltipWidth) * 100;
		// Clamp between 10% and 90% to keep caret within tooltip bounds
		const clampedCaretPercent = Math.max(
			10,
			Math.min(90, caretPositionPercent)
		);

		// If tooltip would go below viewport, show it above the icon instead
		if (top + tooltipHeight > scrollTop + windowHeight - 10) {
			top = elementOffset.top - tooltipHeight - 10;
			$tooltip.addClass('is-above');
		} else {
			$tooltip.removeClass('is-above');
		}

		// Ensure tooltip doesn't go above viewport
		if (top < scrollTop + 10) {
			top = scrollTop + 10;
		}

		// Apply position and caret position
		$tooltip.css({
			visibility: 'visible',
			left: left + 'px',
			top: top + 'px',
			'--caret-left': clampedCaretPercent + '%',
		});
	}

	// Initialize when DOM is ready
	$(document).ready(init);

	// Reinitialize on AJAX content load (for dynamic content)
	$(document).ajaxComplete(function () {
		// Tooltip element already exists, no need to reinitialize
		// Event delegation handles dynamically loaded content
	});
})(jQuery);
