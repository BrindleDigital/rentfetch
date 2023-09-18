jQuery(document).ready(function ($) {


    // flatpickr("#datepicker", {
    //     altInput: true,
    //     altFormat: "F j, Y",
    //     dateFormat: "Ymd",
    // });


    $('.flatpickr').flatpickr(
        {
            altInput: true,
            altFormat: "M. j",
            dateFormat: "Ymd",
            mode: "range",
            minDate: "today",
            wrap: true,
            onClose: submitForm,
            onChange: makeActive,
            // inline: true,
            // disable: [
            //     function (date) {
            //         // disable every multiple of 8
            //         return !(date.getDate() % 8);
            //     }
            // ]
        }
    );

    function makeActive() {
        $('.input-wrap-date-available .form-control').addClass('active');
    }

    function submitForm() {
        $('#filter').submit();
    }

});
