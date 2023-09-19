// const el = document.querySelector('.blaze-slider');
// new BlazeSlider(el);

document.querySelectorAll('.blaze-slider').forEach((el) => {
    new BlazeSlider(el, {
        all: {
            enableAutoplay: true,
            autoplayInterval: 3000,
            transitionDuration: 600,
            slidesToShow: 3,
        },
        '(max-width: 900px)': {
            slidesToShow: 2,
        },
        '(max-width: 500px)': {
            slidesToShow: 1,
        },
    });
});
