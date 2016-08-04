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

    var confDays = $('.conf-days__item'),
        confAgenda = $('.conf-agenda'),
        confAgendaRow = $('.conf-agenda__row'),
        confRoom = $('.conf-room__item');

    // confDays.click(function() {
    //     var day = $(this).data('filter');
    //     confDays.removeClass('conf-days__item--selected');
    //     $(this).addClass('conf-days__item--selected');
    //     confAgenda.removeClass('conf-agenda--active');
    //     $('[data-day="' + day + '"]').addClass('conf-agenda--active');
    // });
    //
    // confRoom.click(function() {
    //     var room = $(this).data('filter');
    //     if (room === 'all') {
    //         confRoom.removeClass('conf-room__item--selected');
    //         $(this).addClass('conf-room__item--selected');
    //         confAgendaRow.removeClass('conf-agenda__row--hidden');
    //     }
    //     else {
    //         confRoom.removeClass('conf-room__item--selected');
    //         $(this).addClass('conf-room__item--selected');
    //         confAgendaRow.addClass('conf-agenda__row--hidden');
    //         $('[data-room="' + room + '"]').removeClass('conf-agenda__row--hidden');
    //     }
    // });
});
