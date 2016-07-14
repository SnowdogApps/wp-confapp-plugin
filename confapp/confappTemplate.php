<?php
/*
 Template Name: ConfApp Template
 */
?>
<?php
get_header();
?>

<?php
$_confrence_days = getConfrenceDays();

?>
<div id="primary" class="content-areas">
    <main id="main" class="site-main" role="main">

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

        <ul class="conf-days">
            <?php foreach ($_confrence_days as $_confrence_day): ?>
                <li class="conf-days__item conf-days__item--selected" data-filter="19/09">
                    <div class="conf-days__day"><?php echo $_confrence_day->name ?></div>
                </li>
            <?php endforeach; ?>
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




        getConfrenceData: <?php var_dump(getConfrenceData()); ?><br/>
        getConfrenceDays: <?php var_dump(getConfrenceDays()); ?><br/>
        getConfrenceFloors: <?php var_dump(getConfrenceFloors()); ?><br/>
        getConfrencePresentations: <?php var_dump(getConfrencePresentations($dayId, $trackId)); ?><br/>
        getSpeaker: <?php var_dump(getSpeaker($speakerId)); ?><br/>
        getTracks: <?php var_dump(getTracks()); ?><br/>


    </main><!-- .site-main -->
</div><!-- .content-area -->
<?php get_footer(); ?>
