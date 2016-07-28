<?php
/*
 Template Name: ConfApp Template
 */
?>

<?php get_header(); ?>

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

        <?php
          $days = getConfrenceDays();
          $tracks = getConfrenceTracks();
          $localizations = getConfrenceLocalizations();

          function getTrackName($tracks, $trackId) {
            foreach ($tracks as $track) {
              return $track->id === $trackId ? $track->name : false;
            }
          }
        ?>


        <ul class="conf-tracks">
          <li class="conf-tracks__item conf-tracks__item--selected" data-filter="all">
            All tracks
          </li>
          <?php foreach ($tracks as $track): ?>
            <li class="conf-tracks__item" data-track-filter="<?= $track->id ?>">
              <?= $track->name ?>
            </li>
          <?php endforeach; ?>
        </ul>

        <ul class="conf-room">
          <li class="conf-room__item conf-room__item--selected" data-room-filter="all">
            All rooms
          </li>
          <?php foreach ($localizations as $localization): ?>
            <li class="conf-room__item" data-room-filter="<?= $localization->id ?>">
              <?= $localization->name ?>
            </li>
          <?php endforeach; ?>
        </ul>

        <div class="conf-days">
          <?php foreach ($days as $day): ?>
            <button class="conf-days__item" data-day-filter="<?= $day->date; ?>">
              <?= $day->date; ?> / <?= $day->name; ?>
            </button>
          <?php endforeach; ?>
        </div>

        <?php foreach ($days as $day): ?>
          <ul class="conf-agenda" data-day="<?= $day->date ?>">
            <?php $presentationsByDate = []; ?>
            <?php
              foreach (getConfrencePresentations($day->id) as $presentation) {
                if ($presentationsByDate[$presentation->date]) {
                  array_push($presentationsByDate[$presentation->date], $presentation);
                }
                else {
                  $presentationsByDate[$presentation->date] = [$presentation];
                }
              }
            ?>
            <?php ksort($presentationsByDate); ?>

            <?php foreach ($presentationsByDate as $date => $presentationsInSameTime): ?>
              <li class="conf-agenda__item">
                <?php foreach ($presentationsInSameTime as $key => $presentation): ?>
                  <pre>
                    <?php print_r($presentation); ?>
                  </pre>

                  <div class="conf-agenda__row"
                       data-room=""
                       data-track="<?= $presentation->track_id ?>"
                  >
                      <div class="conf-agenda__hour">
                        <?= date_format(date_create($date), 'H:i'); ?>
                      </div>
                      <div class="conf-agenda__presentation">
                          <div class="conf-agenda__presentation-subject">
                            Magento 2
                          </div>
                          <div class="conf-agenda__presentation-speaker">
                            Ben Marks
                          </div>
                      </div>
                      <div class="conf-agenda__info">
                          <div class="conf-agenda__language conf-agenda__language--<?= $presentation->locale ?>"></div>
                          <div class="conf-agenda__room-type">
                            <?= $presentation->localization_id ?>
                          </div>

                          <div class="conf-agenda__track-type">
                            <?= getTrackName($tracks, $presentation->track_id) ?>
                          </div>
                      </div>
                  </div>
                <?php endforeach; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endforeach; ?>

    </main><!-- .site-main -->
</div><!-- .content-area -->
<?php get_footer(); ?>
