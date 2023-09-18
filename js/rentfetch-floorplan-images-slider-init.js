(function ($) {
    /**
     * initializeBlock
     *
     * Adds custom JavaScript to the block HTML.
     *
     * @date    15/4/19
     * @since   1.0.0
     *
     * @param   object $block The block jQuery element.
     * @param   object attributes The block attributes (only available when editing).
     * @return  void
     */
    var initializeBlock = function ($block) {
        $block.find('.floorplan-slider').not('.slick-initialized').slick({
            dots: false,
            infinite: false,
            arrows: true,
            speed: 500,
            fade: true,
            cssEase: 'linear',
            lazyLoad: 'ondemand',
        });
    };

    // Initialize each block on page load (front end).
    $(document).ready(function () {
        $('.floorplangrid').each(function () {
            // when this is used in the context of a block
            initializeBlock($(this));
        });

        // when this is used in another context
        $('.floorplan-slider').not('.slick-initialized').slick({
            dots: false,
            infinite: false,
            arrows: true,
            speed: 500,
            fade: true,
            cssEase: 'linear',
            lazyLoad: 'ondemand',
        });
    });

    // Initialize dynamic block preview (editor).
    if (window.acf) {
        window.acf.addAction(
            'render_block_preview/type=floorplangrid',
            initializeBlock
        );
    }
})(jQuery);
