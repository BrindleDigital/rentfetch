jQuery(document).ready(function ($) {
    // when the user clicks .toggle-subnav, toggle the .open class on .toggle-subnav, and toggle the .active class on .nav-content
    $('.toggle-subnav').click(function (event) {
        event.preventDefault();
        $(this).toggleClass('open');
        $('ul.subnav').toggleClass('active');
        return false; // Add this line to prevent the click event from scrolling to the top of the page
    });
});
