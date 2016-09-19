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

    function closest (num, arr) {
         var mid,
             lo = 0,
             hi = arr.length - 1;

         while (hi - lo > 1) {
             mid = Math.floor ((lo + hi) / 2);
             if (arr[mid] < num) {
                 lo = mid;
             } else {
                 hi = mid;
             }
         }
         if (num - arr[lo] <= arr[hi] - num) {
             return arr[lo];
         }
         return arr[hi];
     }

    showCurrentTime();
    setInterval(showCurrentTime, 60000);

    function showCurrentTime() {
        var date = new Date(),
            currentTime = date.getHours() + ':' + date.getMinutes(),
            currentDay = date.getFullYear() + '-'
                        + (date.getMonth() + 1 < 10 ? '0' : '')
                        + (date.getMonth() + 1) + '-'
                        + (date.getDate() < 10 ? '0' : '')
                        + date.getDate(),
            currentDayWrapper = $('[data-day="' + currentDay + '"]'),
            allHoursAvalivable = [];

        currentDayWrapper.find('.conf-agenda__hour').each(function(index, el) {
            allHoursAvalivable.push($.trim($(this).text()));
        });

        // Clear previeous data
        currentDayWrapper.find('.conf-agenda__item').removeClass('conf-agenda__item--past conf-agenda__item--ongoing ');

        var currentPresentationWrapper = currentDayWrapper.find('.conf-agenda__hour:contains(' + closest(currentTime, allHoursAvalivable) + ')').first().parents('.conf-agenda__item');
        currentPresentationWrapper = currentPresentationWrapper.prev();
        currentPresentationWrapper.prevAll().addClass('conf-agenda__item--past');
        currentPresentationWrapper.addClass('conf-agenda__item--ongoing');
    }
});
