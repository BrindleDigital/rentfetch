jQuery(document).ready(function ($) {

    // get settings from localization
    var defaultValSmall = parseInt(settings.valueSmall);
    var defaultValBig = parseInt(settings.valueBig);
    var defaultValStep = parseInt(settings.step);

    var slider = document.getElementById('price-slider');

    noUiSlider.create(slider, {
        start: [defaultValSmall, defaultValBig],
        connect: true,
        margin: 100,
        step: defaultValStep,
        range: {
            'min': defaultValSmall,
            'max': defaultValBig
        },
    });

    var valuesInputs = [
        document.getElementById('pricesmall'),
        document.getElementById('pricebig'),
    ];

    var diffDivs = [
        document.getElementById('range-diff-1'),
        document.getElementById('range-diff-2'),
        document.getElementById('range-diff-3')
    ];

    // When the slider value changes, update the input and span
    slider.noUiSlider.on('update', function (values, handle) {
        var values = slider.noUiSlider.get();
        values[handle] = parseInt(values[handle]);
        valuesInputs[handle].value = values[handle];

        // need to grab these values from the fields so that they always exist
        valSmall = $('#pricesmall').val();
        valBig = $('#pricebig').val();

        if (valSmall != defaultValSmall || valBig != defaultValBig) {
            // update the button
            $('.input-wrap-prices button.dropdown-toggle').addClass('active');
            $('.input-wrap-prices button.dropdown-toggle').text('$' + valSmall + '-' + valBig);
        }

    });

    function updateRange() {
        valSmall = $('#pricesmall').val();
        valBig = $('#pricebig').val();
        slider.noUiSlider.set([valSmall, valBig]);
    }

    function resetRange() {
        console.log('reset');
        slider.noUiSlider.set([defaultValSmall, defaultValBig]);
        $('#pricesmall').val(defaultValSmall);
        $('#pricebig').val(defaultValBig);
    }

    $('#pricesmall, #pricebig').on('change', updateRange);
    $('.clear').on('click', resetRange);

});