// Code to run on page load
jQuery(document).ready(function ($) {
	rentfetch_glightbox_init_when_images_ready();
});

// Code to run after completion of any AJAX request
jQuery(document).ajaxComplete(function () {
	rentfetch_glightbox_init_when_images_ready();
});

function rentfetch_glightbox_init_when_images_ready() {
	if (window.rentfetchFloorplanImagesReady && typeof window.rentfetchFloorplanImagesReady.then === 'function') {
		window.rentfetchFloorplanImagesReady.then(rentfetch_glightbox_init);
		return;
	}

	rentfetch_glightbox_init();
}

function rentfetch_glightbox_init() {
	if (!window.rentfetchLightboxEscapeInitialized) {
		document.addEventListener('keydown', function (event) {
			if (event.key !== 'Escape') {
				return;
			}

			var closeButton = document.querySelector('.glightbox-container .gclose');
			if (closeButton) {
				event.preventDefault();
				event.stopPropagation();
				closeButton.click();
				return;
			}

			window.setTimeout(function () {
				var delayedCloseButton = document.querySelector('.glightbox-container .gclose');
				if (delayedCloseButton) {
					delayedCloseButton.click();
				}
			}, 50);
		}, true);
		window.rentfetchLightboxEscapeInitialized = true;
	}

	var lightboxVideo = GLightbox({
		selector: '.tour-link',
	});

	var lightboxPropertyGallery = GLightbox({
		selector: '.property-image-grid-link',
		loop: true,
	});

	var propertyGalleryLinks = document.querySelectorAll('.property-image-grid-link');
	rentfetch_enable_gallery_thumbnails(lightboxPropertyGallery, propertyGalleryLinks, 'Property image gallery');

	var lightboxFloorplanGallery = GLightbox({
		selector: '.floorplan-image-gallery',
		loop: true,
	});
	var floorplanImageLinks = document.querySelectorAll('.floorplan-image-gallery');
	rentfetch_enable_gallery_thumbnails(lightboxFloorplanGallery, floorplanImageLinks, 'Floor plan image gallery');

	document.querySelectorAll('.unit-image-gallery').forEach(function (gallery) {
		var unitGalleryLinks = gallery.querySelectorAll('.unit-gallery-link');
		var galleryName = unitGalleryLinks.length ? unitGalleryLinks[0].getAttribute('data-gallery') : '';
		if (!galleryName) {
			return;
		}

		var lightboxUnitGallery = GLightbox({
			selector: '[data-gallery="' + galleryName + '"]',
			loop: true,
		});
		rentfetch_enable_gallery_thumbnails(lightboxUnitGallery, unitGalleryLinks, 'Unit image gallery');
	});

	var floorplanGalleryLinks = document.querySelectorAll('.floorplan-gallery-link');
	var lightboxFloorplanLowerGallery = GLightbox({
		selector: '.floorplan-gallery-link',
		loop: true,
	});
	rentfetch_enable_gallery_thumbnails(lightboxFloorplanLowerGallery, floorplanGalleryLinks, 'Floor plan image gallery');
}

function rentfetch_enable_gallery_thumbnails(lightbox, links, label) {
	if (links.length < 2) {
		return;
	}

	lightbox.on('open', function () {
		if (window.matchMedia('(min-width: 769px)').matches) {
			rentfetch_add_property_gallery_thumbnails(lightbox, links, label);
		}
	});
	lightbox.on('slide_before_change', function (event) {
		rentfetch_set_active_property_gallery_thumbnail(event.current.slideIndex);
	});
	lightbox.on('slide_changed', function () {
		rentfetch_set_active_property_gallery_thumbnail(lightbox.getActiveSlideIndex());
	});
	lightbox.on('close', function () {
		document.documentElement.classList.remove('rentfetch-property-gallery-open');
	});
}

function rentfetch_add_property_gallery_thumbnails(lightbox, links, label) {
	var container = document.querySelector('#glightbox-body .gcontainer');
	if (!container || container.querySelector('.rentfetch-property-gallery-thumbnails')) {
		return;
	}

	var thumbnails = document.createElement('div');
	var track = document.createElement('div');
	thumbnails.className = 'rentfetch-property-gallery-thumbnails';
	thumbnails.setAttribute('aria-label', label);
	track.className = 'rentfetch-property-gallery-thumbnail-track';

	links.forEach(function (link, index) {
		var button = document.createElement('button');
		var image = document.createElement('img');
		button.type = 'button';
		button.setAttribute('aria-label', 'View image ' + (index + 1));
		image.alt = '';
		image.loading = 'lazy';
		image.decoding = 'async';
		image.src = rentfetch_get_property_gallery_thumbnail_url(link);
		button.appendChild(image);
		button.addEventListener('click', function (event) {
			event.preventDefault();
			event.stopPropagation();
			rentfetch_set_active_property_gallery_thumbnail(index);
			lightbox.goToSlide(index);
		});
		track.appendChild(button);
	});

	thumbnails.appendChild(track);
	container.appendChild(thumbnails);
	document.documentElement.classList.add('rentfetch-property-gallery-open');
	rentfetch_set_active_property_gallery_thumbnail(lightbox.getActiveSlideIndex());
}

function rentfetch_set_active_property_gallery_thumbnail(index) {
	var buttons = document.querySelectorAll('.rentfetch-property-gallery-thumbnails button');
	buttons.forEach(function (button, buttonIndex) {
		var active = buttonIndex === index;
		button.classList.toggle('is-active', active);
		if (active) {
			button.setAttribute('aria-current', 'true');
			var rail = button.closest('.rentfetch-property-gallery-thumbnails');
			var track = button.parentElement;
			var railStyle = window.getComputedStyle(rail);
			var railWidth = rail.clientWidth - parseFloat(railStyle.paddingLeft) - parseFloat(railStyle.paddingRight);
			var maximumOffset = Math.max(0, track.scrollWidth - railWidth);
			var targetOffset = button.offsetLeft - (railWidth - button.offsetWidth) / 2;
			targetOffset = Math.max(0, Math.min(targetOffset, maximumOffset));
			track.style.transform = 'translateX(-' + targetOffset + 'px)';
		} else {
			button.removeAttribute('aria-current');
		}
	});
}

function rentfetch_get_property_gallery_thumbnail_url(link) {
	var image = link.matches('img') ? link : link.querySelector('img');
	var srcset = image ? image.getAttribute('srcset') : '';
	var url = srcset ? srcset.split(',')[0].trim().split(/\s+/)[0] : (image ? image.currentSrc || image.src : link.getAttribute('data-href') || link.href);

	try {
		var thumbnailUrl = new URL(url, window.location.href);
		var hostname = thumbnailUrl.hostname.toLowerCase();
		if ((hostname === 'rentcafe.com' || hostname.slice(-13) === '.rentcafe.com') && thumbnailUrl.pathname.toLowerCase().indexOf('/dmslivecafe/') !== -1) {
			thumbnailUrl.searchParams.set('width', '160');
			thumbnailUrl.searchParams.set('height', '-1');
			thumbnailUrl.searchParams.set('quality', '70');
			return thumbnailUrl.toString();
		}
	} catch (error) {
		return url;
	}

	return url;
}
