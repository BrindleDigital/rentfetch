// const el = document.querySelector('.blaze-slider');
// new BlazeSlider(el);

document.querySelectorAll('.floorplan-images-slider').forEach((el) => {
	new BlazeSlider(el, {
		all: {
			enableAutoplay: false,
			// autoplayInterval: 3000,
			// transitionDuration: 600,
			slidesToShow: 1,
		},
	});
});
