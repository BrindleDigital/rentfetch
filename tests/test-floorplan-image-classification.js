const assert = require('node:assert/strict');
const {
	rentfetchColorsMatch,
	rentfetchFindMatchingCornerColor,
	rentfetchGetFloorplanImageClass,
} = require('../js/rentfetch-blaze-floorplan-images-init.js');

assert.equal(rentfetchColorsMatch([255, 255, 255, 255], [255, 255, 255, 255]), true);
assert.equal(rentfetchColorsMatch([255, 255, 255, 255], [254, 255, 255, 255]), true);
assert.equal(rentfetchColorsMatch([255, 255, 255, 255], [246, 255, 255, 255]), false);
assert.deepEqual(
	rentfetchFindMatchingCornerColor(
		[
			[255, 255, 255, 255],
			[255, 255, 255, 255],
			[120, 90, 60, 255],
			[255, 255, 255, 255],
		]
	),
	[255, 255, 255, 255]
);
assert.equal(
	rentfetchFindMatchingCornerColor(
		[
			[10, 20, 30, 255],
			[80, 90, 100, 255],
			[140, 150, 160, 255],
			[210, 220, 230, 255],
		]
	),
	null
);
assert.equal(rentfetchGetFloorplanImageClass(null), '');
assert.equal(rentfetchGetFloorplanImageClass({ contained: true }), 'is-contained-image');
assert.equal(rentfetchGetFloorplanImageClass({ contained: false }), 'is-photo-image');

console.log('Floorplan image corner classification tests passed.');
