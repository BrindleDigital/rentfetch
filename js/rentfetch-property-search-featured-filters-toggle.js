jQuery(document).ready(function ($) {
    $('.toggle').on('click', function () {
        var inputWrap = $(this).closest('fieldset').find('.input-wrap');

        inputWrap.toggleClass('active inactive');

        // close all other input-wraps
        $('.input-wrap').not(inputWrap).removeClass('active');
    });

    // for clicks outside of any toggle or input-wrap, close all of the input-wraps
    $(document).on('click touchstart', function (event) {
        if (
            !$(event.target).closest('.toggle').length &&
            !$(event.target).closest('.input-wrap').length &&
            !$(event.target).is('input')
        ) {
            $('.input-wrap').removeClass('active');
        }
    });

    // prevent closing input-wrap when clicking inside input elements
    $('.input-wrap input').on('click touchstart', function (event) {
        event.stopPropagation();
    });
});
