jQuery(document).ready(function ($) {

    var heart = $('.favorite-heart');
    var list = [];

    // onload
    function saveCookieToList() {
        if (Cookies.get('favorite_properties')) {
            list = Cookies.get('favorite_properties');
            list = list.split(',');
            // console.log(list);
        }

        updateStateFromList();
    }

    // update our variable
    function updateList(id) {
        inArr = $.inArray(id, list);

        // if not found...
        if (inArr == -1) {
            list.push(id);
        } else {
            list = jQuery.grep(list, function (value) {
                return value != id;
            });
        }

        // console.log(list);

        saveListToCookie();
    }

    // after click
    function saveListToCookie() {
        // console.log(list);
        Cookies.set('favorite_properties', list);
        updateStateFromList();
    }

    // onload and ajax, update the stats of each heart
    function updateStateFromList() {

        // remove active from all
        $('.favorite-heart').removeClass('active');

        $.each(list, function (handle, value) {



            // add active to the ones that should be
            $('.favorite-heart[data-property-id=' + list[handle] + ']').addClass('active');
        })
    }

    // because the hearts might not have been there on pageload, we need to load click events like this
    $(document).on('click', '.favorite-heart', function (e) {
        e.preventDefault();

        var id = $(this).attr('data-property-id');

        updateList(id);

        return false;
    })

    $(window).on('load', saveCookieToList);
    $(window).on('load ajaxComplete', updateStateFromList);

});