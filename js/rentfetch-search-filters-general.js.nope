jQuery(document).ready(function ($) {
    //* Vars from localization
    var maximumBeds = parseInt(searchoptions.maximum_bedrooms_to_search);

    // console.log('max beds: ' + maximumBeds);

    //* Other vars
    var bedsInputs = $('.input-wrap-beds input');
    var currentBedsInput = $('input[data-beds=' + maximumBeds + ']');
    var maximumBedsChecked = null;

    // Hide beds over the maximum on load
    bedsInputs.each(hideEachBedrooms);

    // onload, hide the dropdowns
    $('.dropdown-menu').removeClass('show');

    // when a dropdown toggle is clicked, toggle the dropdown menu next to it
    $('.dropdown button').click(function () {
        $('.dropdown-menu').removeClass('show');
        $(this).siblings('.dropdown-menu').toggleClass('show');
    });

    // if the target of the click isn't the container nor a descendant of the container
    $(document).mousedown(function (e) {
        var container = $('.dropdown-menu, .flatpickr-calendar');
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            container.removeClass('show');
        }
    });

    //* Submission events
    $('a.apply-local').click(function (e) {
        e.preventDefault();
        $(this).parents('.dropdown-menu').removeClass('show');
    });

    //* if there's a maximum number of bedrooms, hide all the ones above that
    function hideEachBedrooms() {
        if (!maximumBeds) return;

        var currentNumber = $(this).attr('data-beds');

        // if the one we're looking at is higher than the max...
        if (currentNumber > maximumBeds) {
            // hide the parent element (including both the input and the parent label)
            $(this).parent().hide();
        }

        if (currentNumber == maximumBeds) {
            $(this)
                .siblings('span')
                .text(currentNumber + '+ Bedroom');
        }
    }

    //* duplicate the state of the checked beds
    function duplicateCheckedStateBeds() {
        var currentNumber = $(this).attr('data-beds');

        // if the one we're looking at is higher than the max...
        if (currentNumber > maximumBeds) {
            // hide the parent element (including both the input and the parent label)
            // $(this).parent().hide();
            if (maximumBedsChecked == true) {
                $(this).prop('checked', true);
            } else {
                $(this).prop('checked', false);
            }
        }
    }

    //* detect checked bedrooms
    function detectCheckedBeds() {
        maximumBedsChecked = $(
            'input[data-beds=' + maximumBeds + ']:checked'
        ).length;

        if (maximumBedsChecked == 0) {
            maximumBedsChecked = false;
        } else {
            maximumBedsChecked = true;
        }

        bedsInputs.each(duplicateCheckedStateBeds);
    }

    //* text for baths button
    function importBathsToButton() {
        var inputs = $('.input-wrap-baths input');
        var button = $('.input-wrap-baths button');
        var bathsArray = [];

        inputs.each(function () {
            if (this.checked) {
                bathNumber = $(this).attr('data-baths');
                bathsArray.push(bathNumber);
            }
        });

        if (jQuery.isEmptyObject(bathsArray) == false) {
            var text = bathsArray.join(', ');
            text = text + ' Bathroom';
            button.text(text);
            button.addClass('active');
        } else {
            text = button.attr('data-reset');
            button.text(text);
            button.removeClass('active');
        }
    }

    //* text for beds button
    function importBedsToButton() {
        var inputs = $('.input-wrap-beds input');
        var button = $('.input-wrap-beds button');
        var bedsArray = [];

        inputs.each(function () {
            if (this.checked) {
                bathNumber = $(this).attr('data-beds');
                bedsArray.push(bathNumber);
            }
        });

        if (jQuery.isEmptyObject(bedsArray) == false) {
            var text = bedsArray.join(', ');
            text = text + ' Bedroom';
            button.text(text);
            button.addClass('active');
        } else {
            text = button.attr('data-reset');
            button.text(text);
            button.removeClass('active');
        }
    }

    //* text for propertytypes button
    function importPropertyTypes() {
        var inputs = $('.input-wrap-propertytypes input');
        var button = $('.input-wrap-propertytypes button');
        var propertyTypeNames = [];

        inputs.each(function () {
            if (this.checked) {
                propertyTypeName = $(this).attr('data-propertytypesname');
                propertyTypeNames.push(propertyTypeName);
            }
        });

        if (jQuery.isEmptyObject(propertyTypeNames) == false) {
            var text = propertyTypeNames.join(', ');
            button.text(text);
            button.addClass('active');
        } else {
            text = button.attr('data-reset');
            button.text(text);
            button.removeClass('active');
        }
    }

    //* text for amenities button
    function importAmenities() {
        var inputs = $('.input-wrap-amenities input');
        var button = $('.input-wrap-amenities button');
        var AmenityNames = [];

        inputs.each(function () {
            if (this.checked) {
                AmenityName = $(this).attr('data-amenities-name');
                AmenityNames.push(AmenityName);
            }
        });

        if (jQuery.isEmptyObject(AmenityNames) == false) {
            var text = AmenityNames.join(', ');
            button.text(text);
            button.addClass('active');
        } else {
            text = button.attr('data-reset');
            button.text(text);
            button.removeClass('active');
        }
    }

    //* text for neighborhoods button
    function importNeighborhoods() {
        var inputs = $('.input-wrap-neighborhoods input');
        var button = $('.input-wrap-neighborhoods button');
        var NeighborhoodNames = [];

        inputs.each(function () {
            if (this.checked) {
                NeighborhoodName = $(this).attr('data-neighborhoods-name');
                NeighborhoodNames.push(NeighborhoodName);
            }
        });

        if (jQuery.isEmptyObject(NeighborhoodNames) == false) {
            var text = NeighborhoodNames.join(', ');
            button.text(text);
            button.addClass('active');
        } else {
            text = button.attr('data-reset');
            button.text(text);
            button.removeClass('active');
        }
    }

    //* text for pets button
    function importPetsToButton() {
        var selectedPetPolicy = $('.input-wrap-pets input:checked').attr(
            'data-pets-name'
        );
        // console.log(selectedPetPolicy);

        $('.input-wrap-pets button').addClass('active');
        $('.input-wrap-pets button').text(selectedPetPolicy);
    }

    function textInputActive() {
        var textsearchval = $(this).val();
        if (textsearchval.length > 0) {
            $(this).addClass('active');
        } else {
            $(this).removeClass('active');
        }
    }

    function clearDropdown() {
        $(this)
            .closest('.dropdown-menu')
            .find('input[type="checkbox"]')
            .prop('checked', false);
        $(this)
            .closest('.dropdown-menu')
            .find('input[type="radio"]')
            .prop('checked', false);
        var button = $(this).closest('.input-wrap').find('button');
        var text = button.attr('data-reset');
        button.text(text);
        button.removeClass('active');
    }

    function showSearch() {
        $('form.property-search-filters, form.property-search-starter').css(
            'opacity',
            '1'
        );
    }

    // on load, do these functions
    importBedsToButton();
    importBathsToButton();
    importPropertyTypes();
    showSearch();

    // do some of these functions when something happens
    $('.input-wrap-baths input').on('change', importBathsToButton);
    currentBedsInput.on('change', detectCheckedBeds);
    $('.input-wrap-beds input').on('change', importBedsToButton);
    $('.input-wrap-pets input').on('change', importPetsToButton);
    $('.input-wrap-propertytypes input').on('change', importPropertyTypes);
    $('.input-wrap-amenities input').on('change', importAmenities);
    $('.input-wrap-neighborhoods input').on('change', importNeighborhoods);
    $('.clear').on('click', clearDropdown);
    $('input[type="text"]').on('change', textInputActive);
});
