jQuery(document).ready(function ($) {

    function loadSlick() {
        $('.property-slider').not('.slick-initialized').slick({
            dots: true,
            infinite: false,
            arrows: true,
            speed: 500,
            fade: true,
            cssEase: 'linear',
            lazyLoad: 'ondemand',
        });
    }

    // run when the page loads
    loadSlick();

    // run when ajax completes
    $(document).on('ajaxComplete', loadSlick);

});
