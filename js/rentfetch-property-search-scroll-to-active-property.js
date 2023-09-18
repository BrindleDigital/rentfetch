// Define the scrollToActiveProperty function globally
function scrollToActiveProperty(i) {
    if (jQuery('.rent-fetch-property-search-default-layout').length) {
        // Scroll to the active div and center it on the screen
        var activeDiv = jQuery('.type-properties[data-id=' + i + ']');
        var windowHeight = jQuery(window).height();
        var divHeight = activeDiv.outerHeight();
        var scrollTop =
            activeDiv.offset().top - windowHeight / 2 + divHeight / 2;

        jQuery('html, body').animate(
            {
                scrollTop: scrollTop,
            },
            1000
        );
    }
}
