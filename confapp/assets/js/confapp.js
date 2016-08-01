jQuery(function($) {
    var confDays = $('.conf-days__item'),
        confAgenda = $('.conf-agenda'),
        confAgendaRow = $('.conf-agenda__row'),
        confRoom = $('.conf-room__item');

    confDays.click(function() {
        var day = $(this).data('filter');
        confDays.removeClass('conf-days__item--selected');
        $(this).addClass('conf-days__item--selected');
        confAgenda.removeClass('conf-agenda--active');
        $('[data-day="' + day + '"]').addClass('conf-agenda--active');
    });

    confRoom.click(function() {
        var room = $(this).data('filter');
        if (room === 'all') {
            confRoom.removeClass('conf-room__item--selected');
            $(this).addClass('conf-room__item--selected');
            confAgendaRow.removeClass('conf-agenda__row--hidden');
        }
        else {
            confRoom.removeClass('conf-room__item--selected');
            $(this).addClass('conf-room__item--selected');
            confAgendaRow.addClass('conf-agenda__row--hidden');
            $('[data-room="' + room + '"]').removeClass('conf-agenda__row--hidden');
        }
    });
});
