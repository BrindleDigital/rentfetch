jQuery(document).ready(function ($) {
    //* Get the floorplans parameter in case we need it
    var urlParams = new URLSearchParams(window.location.search);
    var currentOnLoad = urlParams.get('filter');

    if (currentOnLoad) {
        updatePlans(currentOnLoad);
        updateButton(currentOnLoad);
    }

    function updateFloorplans(e) {
        e.preventDefault();
        var current = $(this).data('filter');
        updatePlans(current);
        updateButton(current);
        updateSlick(current);
    }

    function reinitializeSliders() {
        console.log('reload');
        $('.floorplan-slider').slick('refresh');
    }

    function updateSlick(current) {
        var currentClass = '.' + current + ' .floorplan-slider';
        $(currentClass).slick('refresh');
    }

    function updatePlans(current) {
        $('.floorplangrid .floorplans').hide();
        $('.floorplangrid .' + current).show();

        if ('URLSearchParams' in window) {
            var searchParams = new URLSearchParams(window.location.search);
            searchParams.set('filter', current);
            window.history.pushState('', '', '?filter=' + current);
        }
    }

    //* Update the active class on the button
    function updateButton(current) {
        $('.filter-select').removeClass('active');
        $('[data-filter=' + current + ']').addClass('active');
    }

    //* Update the URL
    function UpdateQueryString(key, value, url) {
        if (!url) url = window.location.href;
        var re = new RegExp('([?&])' + key + '=.*?(&|#|$)(.*)', 'gi'),
            hash;

        if (re.test(url)) {
            if (typeof value !== 'undefined' && value !== null) {
                return url.replace(re, '$1' + key + '=' + value + '$2$3');
            } else {
                hash = url.split('#');
                url = hash[0].replace(re, '$1$3').replace(/(&|\?)$/, '');
                if (typeof hash[1] !== 'undefined' && hash[1] !== null) {
                    url += '#' + hash[1];
                }
                return url;
            }
        } else {
            if (typeof value !== 'undefined' && value !== null) {
                var separator = url.indexOf('?') !== -1 ? '&' : '?';
                hash = url.split('#');
                url = hash[0] + separator + key + '=' + value;
                if (typeof hash[1] !== 'undefined' && hash[1] !== null) {
                    url += '#' + hash[1];
                }
                return url;
            } else {
                return url;
            }
        }
    }

    // update the floorplans when we click a filter
    $('.filter-select').on('click', updateFloorplans);

    // reinitialize the sliders when we click anywhere on the page. This is done because in situations where the slider is hidden, then shown, the image sizing isn't properly detected and the sliders can appear blank. Reinitializing fixes it, but we can't predict **what** the user will be clicking to do this.
    $('body').on('click', reinitializeSliders);
});
