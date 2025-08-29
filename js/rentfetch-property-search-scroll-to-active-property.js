// Define the scrollToActiveProperty function globally
// Scroll to the active property in a performant way.
function scrollToActiveProperty(i) {
	// Quick guard: bail if layout container missing.
	if (!document.querySelector('.rent-fetch-property-search-default-layout')) {
		return;
	}

	var selector = '.type-properties[data-id="' + i + '"]';
	var el = document.querySelector(selector);
	if (!el) return;

	// Batch DOM reads inside rAF to avoid layout thrashing.
	window.requestAnimationFrame(function () {
		var rect = el.getBoundingClientRect();
		var absoluteTop = window.pageYOffset + rect.top;
		var target = Math.max(
			0,
			Math.floor(absoluteTop - window.innerHeight / 2 + rect.height / 2)
		);

		// Prefer native smooth scroll which is handled by the browser and GPU where possible.
		if (
			'scrollBehavior' in document.documentElement.style &&
			typeof window.scrollTo === 'function'
		) {
			window.scrollTo({ top: target, behavior: 'smooth' });
			return;
		}

		// Fallback to jQuery animate for older browsers.
		if (window.jQuery) {
			jQuery('html, body').animate({ scrollTop: target }, 250);
		} else {
			// Simple instant fallback.
			window.scrollTo(0, target);
		}
	});
}
