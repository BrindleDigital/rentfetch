const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

// Exercise the initializer with image-bearing anchors, as used by all three galleries.
const links = ['Property kitchen & dining room', 'Birch floor plan', 'Apartment 101'].map((alt) => ({
	querySelector: () => ({ alt }),
	setAttribute(name, value) { this[name] = value; },
}));
const galleries = [];
const context = {
	window: { rentfetchLightboxEscapeInitialized: true },
	document: {
		querySelectorAll: (selector) => selector.includes(',') ? links : [],
	},
	jQuery: () => ({ ready() {}, ajaxComplete() {} }),
	GLightbox: (options) => {
		galleries.push(options.selector);
		return { on() {} };
	},
};
vm.createContext(context);
vm.runInContext(fs.readFileSync(require.resolve('../js/rentfetch-glightbox-init.js'), 'utf8'), context);
context.rentfetch_glightbox_init();
assert.deepEqual(links.map((link) => link['data-alt']), ['Property kitchen & dining room', 'Birch floor plan', 'Apartment 101']);
assert(galleries.includes('.property-image-grid-link'));
assert(galleries.includes('.floorplan-gallery-link'));
console.log('Gallery image alt checks passed.');
