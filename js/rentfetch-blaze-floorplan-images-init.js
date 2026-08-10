(function () {
	'use strict';

	var cornerInset = 5;
	var cornerColorTolerance = 8;
	var classificationCache = new Map();

	function rentfetchColorsMatch(first, second) {
		return first.slice(0, 4).every(function (channel, index) {
			return Math.abs(channel - second[index]) <= cornerColorTolerance;
		});
	}

	function rentfetchFindMatchingCornerColor(colors) {
		for (var index = 0; index < colors.length; index++) {
			var matches = colors.filter(function (color) {
				return rentfetchColorsMatch(colors[index], color);
			});

			if (matches.length >= 3) {
				return colors[index];
			}
		}

		return null;
	}

	function rentfetchSampleImageCorners(image) {
		var canvas = document.createElement('canvas');
		var context = canvas.getContext('2d', { willReadFrequently: true });
		var width = image.naturalWidth;
		var height = image.naturalHeight;

		if (!context || width <= cornerInset * 2 || height <= cornerInset * 2) {
			return null;
		}

		canvas.width = 4;
		canvas.height = 1;
		context.fillStyle = '#fff';
		context.fillRect(0, 0, 4, 1);

		var points = [
			[cornerInset, cornerInset],
			[width - cornerInset - 1, cornerInset],
			[cornerInset, height - cornerInset - 1],
			[width - cornerInset - 1, height - cornerInset - 1],
		];
		var colors = points.map(function (point, index) {
			context.drawImage(image, point[0], point[1], 1, 1, index, 0, 1, 1);
			return Array.from(context.getImageData(index, 0, 1, 1).data);
		});
		var matchingColor = rentfetchFindMatchingCornerColor(colors);

		return matchingColor
			? { contained: true, color: 'rgb(' + matchingColor.slice(0, 3).join(', ') + ')' }
			: { contained: false, color: null };
	}

	function rentfetchClassifyFloorplanImage(source) {
		return new Promise(function (resolve) {
			var image = new Image();

			try {
				if (new URL(source, window.location.href).origin !== window.location.origin) {
					image.crossOrigin = 'anonymous';
				}
			} catch (error) {
				resolve(null);
				return;
			}

			image.onload = function () {
				try {
					resolve(rentfetchSampleImageCorners(image));
				} catch (error) {
					resolve(null);
				}
			};
			image.onerror = function () {
				resolve(null);
			};
			image.src = source;
		});
	}

	function rentfetchGetFloorplanImageClass(classification) {
		if (!classification) {
			return '';
		}

		return classification.contained ? 'is-contained-image' : 'is-photo-image';
	}

	function rentfetchApplyFloorplanImageClassification(sliderElement, index, classification) {
		var elements = sliderElement.querySelectorAll('[data-floorplan-image-index="' + index + '"]');
		var className = rentfetchGetFloorplanImageClass(classification);

		if (!className) {
			return;
		}

		elements.forEach(function (element) {
			element.classList.remove('is-contained-image', 'is-photo-image');
			element.classList.add(className);
			if (classification.color) {
				element.style.setProperty('--rentfetch-floorplan-image-background', classification.color);
			} else {
				element.style.removeProperty('--rentfetch-floorplan-image-background');
			}
		});
	}

	function rentfetchGetFloorplanImageClassification(source) {
		if (!classificationCache.has(source)) {
			classificationCache.set(source, rentfetchClassifyFloorplanImage(source));
		}

		return classificationCache.get(source);
	}

	function rentfetchInitializeFloorplanSingleImage(container) {
		var image = container.querySelector('img');
		if (!image) {
			return;
		}

		rentfetchGetFloorplanImageClassification(image.currentSrc || image.src).then(function (classification) {
			var className = rentfetchGetFloorplanImageClass(classification);
			if (!className) {
				return;
			}

			container.classList.remove('is-contained-image', 'is-photo-image');
			container.classList.add(className);
			if (classification.color) {
				container.style.setProperty('--rentfetch-floorplan-image-background', classification.color);
			} else {
				container.style.removeProperty('--rentfetch-floorplan-image-background');
			}
		});
	}

	function rentfetchClassifyFloorplanSingleImages(root) {
		(root || document).querySelectorAll('.floorplan-single-image-wrap').forEach(rentfetchInitializeFloorplanSingleImage);
	}

	function rentfetchImageLoads(source) {
		return new Promise(function (resolve) {
			var image = new Image();
			image.onload = function () {
				resolve(true);
			};
			image.onerror = function () {
				resolve(false);
			};
			image.src = source;
		});
	}

	function rentfetchReindexFloorplanSlider(element) {
		element.querySelectorAll('.floorplan-image-slide').forEach(function (slide, index) {
			slide.dataset.floorplanImageIndex = index;
		});
		element.querySelectorAll('.floorplan-image-thumbnail').forEach(function (thumbnail, index) {
			thumbnail.dataset.floorplanImageIndex = index;
			thumbnail.setAttribute('aria-label', 'View photo ' + (index + 1));
			thumbnail.classList.toggle('is-active', index === 0);
			if (index === 0) {
				thumbnail.setAttribute('aria-current', 'true');
			} else {
				thumbnail.removeAttribute('aria-current');
			}
		});

		var buttons = element.querySelector('.blaze-buttons');
		if (buttons) {
			buttons.hidden = element.querySelectorAll('.floorplan-image-slide').length < 2;
		}
	}

	function rentfetchRemoveFloorplanSliderImage(element, index) {
		element.querySelectorAll('[data-floorplan-image-index="' + index + '"]').forEach(function (item) {
			item.remove();
		});
		rentfetchReindexFloorplanSlider(element);
	}

	function rentfetchPrepareFloorplanSlider(element) {
		var thumbnails = Array.from(element.querySelectorAll('.floorplan-image-thumbnail'));
		var candidates = thumbnails.length ? thumbnails : Array.from(element.querySelectorAll('.floorplan-image-slide'));

		return Promise.all(candidates.map(function (candidate) {
			var image = candidate.querySelector('img');
			var source = candidate.dataset.floorplanSampleSrc || (image ? image.currentSrc || image.src : '');
			var index = Number(candidate.dataset.floorplanImageIndex);

			return source ? rentfetchImageLoads(source).then(function (loaded) {
				return { index: index, loaded: loaded };
			}) : Promise.resolve({ index: index, loaded: false });
		})).then(function (results) {
			results.filter(function (result) {
				return !result.loaded;
			}).sort(function (first, second) {
				return second.index - first.index;
			}).forEach(function (result) {
				rentfetchRemoveFloorplanSliderImage(element, result.index);
			});

			rentfetchReindexFloorplanSlider(element);
			if (element.querySelector('.floorplan-image-slide')) {
				rentfetchInitializeFloorplanSlider(element);
			}
		});
	}

	function rentfetchSetActiveFloorplanThumbnail(sliderElement, index) {
		var thumbnails = sliderElement.querySelectorAll('.floorplan-image-thumbnail');

		thumbnails.forEach(function (thumbnail) {
			var active = Number(thumbnail.dataset.floorplanImageIndex) === index;
			thumbnail.classList.toggle('is-active', active);
			if (active) {
				thumbnail.setAttribute('aria-current', 'true');
				var rail = thumbnail.parentElement;
				rail.scrollTo({
					left: thumbnail.offsetLeft - (rail.clientWidth - thumbnail.offsetWidth) / 2,
					behavior: 'smooth',
				});
			} else {
				thumbnail.removeAttribute('aria-current');
			}
		});
	}

	function rentfetchInitializeFloorplanSlider(element) {
		var slider = new BlazeSlider(element, {
			all: {
				enableAutoplay: false,
				slidesToShow: 1,
			},
		});

		if (!element.classList.contains('has-floorplan-image-thumbnails')) {
			return;
		}

		var thumbnails = Array.from(element.querySelectorAll('.floorplan-image-thumbnail'));
		var currentIndex = 0;
		if (!thumbnails.length) {
			element.querySelectorAll('.floorplan-image-slide').forEach(function (slide) {
				var image = slide.querySelector('img');
				var index = Number(slide.dataset.floorplanImageIndex);
				if (image) {
					rentfetchGetFloorplanImageClassification(image.currentSrc || image.src).then(function (classification) {
						rentfetchApplyFloorplanImageClassification(element, index, classification);
					});
				}
			});
			return;
		}

		thumbnails.forEach(function (thumbnail) {
			var index = Number(thumbnail.dataset.floorplanImageIndex);
			var source = thumbnail.dataset.floorplanSampleSrc;

			rentfetchGetFloorplanImageClassification(source).then(function (classification) {
				rentfetchApplyFloorplanImageClassification(element, index, classification);
			});

			thumbnail.addEventListener('click', function () {
				var currentThumbnails = Array.from(element.querySelectorAll('.floorplan-image-thumbnail'));
				var total = currentThumbnails.length;
				index = currentThumbnails.indexOf(thumbnail);
				var forward = (index - currentIndex + total) % total;
				var backward = (currentIndex - index + total) % total;

				currentIndex = index;
				rentfetchSetActiveFloorplanThumbnail(element, currentIndex);
				if (forward <= backward) {
					slider.next(forward);
				} else {
					slider.prev(backward);
				}
			});
		});

		slider.onSlide(function (stateIndex, firstVisibleSlide) {
			currentIndex = firstVisibleSlide;
			rentfetchSetActiveFloorplanThumbnail(element, currentIndex);
		});

		var previous = element.querySelector('.blaze-prev');
		var next = element.querySelector('.blaze-next');
		if (previous) {
			previous.addEventListener('click', function () {
				var total = element.querySelectorAll('.floorplan-image-slide').length;
				currentIndex = (currentIndex - 1 + total) % total;
				rentfetchSetActiveFloorplanThumbnail(element, currentIndex);
			});
		}
		if (next) {
			next.addEventListener('click', function () {
				var total = element.querySelectorAll('.floorplan-image-slide').length;
				currentIndex = (currentIndex + 1) % total;
				rentfetchSetActiveFloorplanThumbnail(element, currentIndex);
			});
		}
	}

	function rentfetchInitializeFloorplanGallery(gallery) {
		gallery.querySelectorAll('.floorplan-gallery-image').forEach(function (container) {
			var source = container.dataset.floorplanSampleSrc;
			rentfetchGetFloorplanImageClassification(source).then(function (classification) {
				var className = rentfetchGetFloorplanImageClass(classification);
				if (!className) {
					return;
				}

				container.classList.add(className);
				if (classification.color) {
					container.style.setProperty('--rentfetch-floorplan-image-background', classification.color);
				}
			});
		});
	}

	function rentfetchPrepareFloorplanGallery(gallery) {
		var containers = Array.from(gallery.querySelectorAll('.floorplan-gallery-image'));

		return Promise.all(containers.map(function (container) {
			return rentfetchImageLoads(container.dataset.floorplanSampleSrc).then(function (loaded) {
				if (!loaded) {
					container.remove();
				}
			});
		})).then(function () {
			var remaining = Array.from(gallery.querySelectorAll('.floorplan-gallery-image'));
			if (remaining.length < 3) {
				gallery.closest('.container-floorplan-gallery').remove();
				return;
			}

			remaining.forEach(function (container, index) {
				container.classList.toggle('is-lightbox-only', index > 2);
			});
			gallery.querySelectorAll('.floorplan-gallery-view-all').forEach(function (label) {
				label.remove();
			});
			var label = document.createElement('span');
			label.className = 'floorplan-gallery-view-all';
			label.textContent = 'View all ' + remaining.length + ' photos';
			remaining[2].querySelector('.floorplan-gallery-link').appendChild(label);
			rentfetchInitializeFloorplanGallery(gallery);
		});
	}

	if (typeof module !== 'undefined' && module.exports) {
		module.exports = {
			rentfetchColorsMatch: rentfetchColorsMatch,
			rentfetchFindMatchingCornerColor: rentfetchFindMatchingCornerColor,
			rentfetchGetFloorplanImageClass: rentfetchGetFloorplanImageClass,
		};
	}

	if (typeof document !== 'undefined') {
		var preparationTasks = [];
		window.rentfetchClassifyFloorplanSingleImages = rentfetchClassifyFloorplanSingleImages;
		rentfetchClassifyFloorplanSingleImages();
		if (typeof BlazeSlider !== 'undefined') {
			document.querySelectorAll('.floorplan-images-slider').forEach(function (slider) {
				preparationTasks.push(rentfetchPrepareFloorplanSlider(slider));
			});
		}
		document.querySelectorAll('.floorplan-gallery-grid').forEach(function (gallery) {
			preparationTasks.push(rentfetchPrepareFloorplanGallery(gallery));
		});
		window.rentfetchFloorplanImagesReady = Promise.all(preparationTasks);
	}
})();
