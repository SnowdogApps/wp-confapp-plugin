WebFont.load({
    google: {
        families: ['Open Sans:400,700:latin,latin-ext']
    }
});

jQuery(function($) {
    var filtersDropdownTrigger = $('.conf-filters-dropdown-trigger'),
        filtersDropdown = $('.conf-filters-dropdown');

    filtersDropdownTrigger.click(function() {
        filtersDropdownTrigger.toggleClass('conf-filters-dropdown-trigger--is-open');
        filtersDropdown.toggleClass('conf-filters-dropdown--is-open');
    });

    $(document).on('click', function(event) {
        if (!$(event.target).closest('.conf-filters-dropdown-trigger, .conf-filters-dropdown').length) {
            if (filtersDropdown.hasClass('conf-filters-dropdown--is-open')) {
                filtersDropdown.removeClass('conf-filters-dropdown--is-open');
                filtersDropdownTrigger.removeClass('conf-filters-dropdown-trigger--is-open');
            }
        }
    });

    var days                 = $('.conf-agenda'),
        daysFitlerButtons    = $('.conf-days__item'),
        filtersButtons       = $('.conf-filter__item'),
        presentationsWrapper = $('.conf-agenda__item'),
        presentations        = $('.conf-agenda__row'),
        filters              = {
                                'track'       : 'all',
                                'localization': 'all',
                                'lang'        : 'all'
                               };

    function applyFilters(type, value) {
        var searchQuery = [];
        filters[type] = value;

        Object.keys(filters).forEach(function(filter) {
            if (filters[filter] !== 'all') {
                searchQuery.push('[data-' + filter +'="' + filters[filter] + '"]');
            }
        });
        if (searchQuery.length > 0) {
            presentationsWrapper.css('display', 'none');
            presentations.css('display', 'none');

            presentations.filter(searchQuery.join('')).parent('.conf-agenda__item').css('display', 'block');
            presentations.filter(searchQuery.join('')).css('display', 'block');
        }
        else {
            presentationsWrapper.css('display', 'block');
            presentations.css('display', 'block');
        }
    }

    days.css('display', 'none');
    days.first().css('display', 'block');

    daysFitlerButtons.click(function(event) {
        days.css('display', 'none');
        days.filter('[data-day="' + $(this).data('day-filter') + '"]').css('display', 'block');
        daysFitlerButtons.removeClass('conf-days__item--selected');
        $(this).addClass('conf-days__item--selected');
    });

    Object.keys(filters).forEach(function(filter) {
        var buttons = filtersButtons.filter('[data-' + filter + '-filter]');
        buttons.click(function(event) {
            buttons.removeClass('conf-filter__item--selected');
            $(this).addClass('conf-filter__item--selected');
            applyFilters(filter, $(this).data(filter + '-filter'));
        });
    });

    function markOngoingPressenations() {
        var date                 = new Date(),
            currentTime          = date.getHours() + ':'
                                   + (date.getMinutes() < 10 ? '0' : '')
                                   + date.getMinutes(),
            currentDay           = date.getFullYear() + '-'
                                   + (date.getMonth() + 1 < 10 ? '0' : '')
                                   + (date.getMonth() + 1) + '-'
                                   + (date.getDate() < 10 ? '0' : '')
                                   + date.getDate(),
            currentDayWrapper    = $('[data-day="' + currentDay + '"]'),
            avalivableStartTimes = [],
            avalivableEndTimes   = [];

        // Build avaliabce start and end times arrays
        currentDayWrapper.find('.conf-agenda__item').each(function(index, el) {
            avalivableStartTimes.push($(this).data('start-time'));
            avalivableEndTimes.push($(this).data('end-time'));
        });

        avalivableStartTimes.sort().reverse();
        avalivableEndTimes.sort();

        // Clear previeous data
        currentDayWrapper
            .find('.conf-agenda__item')
            .removeClass('conf-agenda__item--past conf-agenda__item--ongoing');

        function findClosestStartTime(value) {
            return value <= currentTime;
        }

        function findClosestEndTime(value) {
            return value >= currentTime;
        }

        var closestStartTime      = avalivableStartTimes.find(findClosestStartTime),
            closestEndTime        = avalivableEndTimes.find(findClosestEndTime)
            clossestStartTimeElem = currentDayWrapper
                .find('.conf-agenda__time--start:contains(' + closestStartTime + ')')
                .parents('.conf-agenda__item'),
            clossestEndTimeElem   = currentDayWrapper
                .find('.conf-agenda__time--end:contains(' + closestEndTime + ')')
                .parents('.conf-agenda__item');
            ongoingPressenations  = clossestStartTimeElem.add(clossestEndTimeElem);

         ongoingPressenations
            .last()
            .prevAll(ongoingPressenations)
            .addClass('conf-agenda__item--past');
         ongoingPressenations
            .removeClass('conf-agenda__item--past')
            .addClass('conf-agenda__item--ongoing');
     }

    markOngoingPressenations();
    setInterval(markOngoingPressenations, 60000);
});
