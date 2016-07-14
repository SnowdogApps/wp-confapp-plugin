<?php
/*
 Template Name: ConfApp Template
 */
?>
<?php
get_header();
?>
<div id="primary" class="content-areas">
    <main id="main" class="site-main" role="main">

        test
        <?php
        // Start the loop.
        while (have_posts()) : the_post();

            // Include the page content template.
            get_template_part('template-parts/content', 'page');

            // If comments are open or we have at least one comment, load up the comment template.
            if (comments_open() || get_comments_number()) {
                comments_template();
            }

            // End of the loop.
        endwhile;
        ?>
        <?php getConfrenceData(); ?>
        <?php getConfrenceDays(); ?>
        <?php getConfrenceFloors(); ?>
        <?php getConfrencePresentations($dayId, $trackId); ?>
        <?php getSpeaker($speakerId); ?>
        <?php getTracks(); ?>

        <ul class="conf-days">
            <li class="conf-days__item conf-days__item--selected" data-filter="19/09">
                <div class="conf-days__day">Poniedzialek</div>
                <div class="conf-days__date">19/09</div>
            </li>
            <li class="conf-days__item" data-filter="20/09">
                <div class="conf-days__day">Wtorek</div>
                <div class="conf-days__date">20/09</div>
            </li>
        </ul>
        <ul class="conf-room">
            <li class="conf-room__item" data-filter="Open Space">Open Space</li>
            <li class="conf-room__item" data-filter="Creative Room">Creative Room</li>
            <li class="conf-room__item" data-filter="Devs Room">Devs Room</li>
            <li class="conf-room__item conf-room__item--selected" data-filter="all">All</li>
        </ul>
        <ul class="conf-agenda conf-agenda--active" data-day="19/09">
            <li class="conf-agenda__item">
                <div class="conf-agenda__row" data-room="Devs Room">
                    <div class="conf-agenda__hour">19:00</div>
                    <div class="conf-agenda__presentation">
                        <div class="conf-agenda__presentation-subject">Magento 2</div>
                        <div class="conf-agenda__presentation-speaker">Ben Marks</div>
                    </div>
                    <div class="conf-agenda__info">
                        <div class="conf-agenda__language conf-agenda__language--polish"></div>
                        <div class="conf-agenda__room-type">Devs Room</div>
                    </div>
                </div>
                <div class="conf-agenda__row" data-room="Open Space">
                    <div class="conf-agenda__hour">19:00</div>
                    <div class="conf-agenda__presentation">
                        <div class="conf-agenda__presentation-subject">Magento 2</div>
                        <div class="conf-agenda__presentation-speaker">Ben Marks</div>
                    </div>
                    <div class="conf-agenda__info">
                        <div class="conf-agenda__language">Polish</div>
                        <div class="conf-agenda__room-type">Open Space</div>
                    </div>
                </div>
            </li>
            <li class="conf-agenda__item">
                <div class="conf-agenda__row" data-room="Devs Room">
                    <div class="conf-agenda__hour">21:00</div>
                    <div class="conf-agenda__presentation">
                        <div class="conf-agenda__presentation-subject">Magento 2</div>
                        <div class="conf-agenda__presentation-speaker">Ben Marks</div>
                    </div>
                    <div class="conf-agenda__info">
                        <div class="conf-agenda__language">Polish</div>
                        <div class="conf-agenda__room-type">Devs Room</div>
                    </div>
                </div>
                <div class="conf-agenda__row" data-room="Open Space">
                    <div class="conf-agenda__hour">21:00</div>
                    <div class="conf-agenda__presentation">
                        <div class="conf-agenda__presentation-subject">Magento 2</div>
                        <div class="conf-agenda__presentation-speaker">Ben Marks</div>
                    </div>
                    <div class="conf-agenda__info">
                        <div class="conf-agenda__language">Polish</div>
                        <div class="conf-agenda__room-type">Open Space</div>
                    </div>
                </div>
            </li>
        </ul>
        <ul class="conf-agenda conf-agenda--active" data-day="20/09">
            <li class="conf-agenda__item">
                <div class="conf-agenda__row" data-room="Devs Room">
                    <div class="conf-agenda__hour">19:00</div>
                    <div class="conf-agenda__presentation">
                        <div class="conf-agenda__presentation-subject">Magento 2</div>
                        <div class="conf-agenda__presentation-speaker">Ben Marks</div>
                    </div>
                    <div class="conf-agenda__info">
                        <div class="conf-agenda__language conf-agenda__language--polish"></div>
                        <div class="conf-agenda__room-type">Devs Room</div>
                    </div>
                </div>
                <div class="conf-agenda__row" data-room="Open Space">
                    <div class="conf-agenda__hour">19:00</div>
                    <div class="conf-agenda__presentation">
                        <div class="conf-agenda__presentation-subject">Magento 2</div>
                        <div class="conf-agenda__presentation-speaker">Ben Marks</div>
                    </div>
                    <div class="conf-agenda__info">
                        <div class="conf-agenda__language">Polish</div>
                        <div class="conf-agenda__room-type">Open Space</div>
                    </div>
                </div>
            </li>
            <li class="conf-agenda__item">
                <div class="conf-agenda__row" data-room="Devs Room">
                    <div class="conf-agenda__hour">21:00</div>
                    <div class="conf-agenda__presentation">
                        <div class="conf-agenda__presentation-subject">Magento 2</div>
                        <div class="conf-agenda__presentation-speaker">Ben Marks</div>
                    </div>
                    <div class="conf-agenda__info">
                        <div class="conf-agenda__language">Polish</div>
                        <div class="conf-agenda__room-type">Devs Room</div>
                    </div>
                </div>
                <div class="conf-agenda__row" data-room="Open Space">
                    <div class="conf-agenda__hour">21:00</div>
                    <div class="conf-agenda__presentation">
                        <div class="conf-agenda__presentation-subject">Magento 2</div>
                        <div class="conf-agenda__presentation-speaker">Ben Marks</div>
                    </div>
                    <div class="conf-agenda__info">
                        <div class="conf-agenda__language">Polish</div>
                        <div class="conf-agenda__room-type">Open Space</div>
                    </div>
                </div>
            </li>
        </ul>

    </main><!-- .site-main -->
</div><!-- .content-area -->
<?php get_footer(); ?>
