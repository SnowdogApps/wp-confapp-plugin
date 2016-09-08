<?php
/*
 * ConfApp Agenda Template
 */
?>
<div class="confapp-agenda-wrapper">
    <?php
        $days          = getConfrenceDays();
        $langs         = getConfrenceLangs();
        $tracks        = getConfrenceTracks();
        $localizations = getConfrenceLocalizations();

        function sortData(&$data, $key) {
            usort($data, function($a, $b) use ($key) {
                return strcmp($a->$key, $b->$key);
            });
        }

        sortData($days, 'date');
        sortData($langs, 'locale');
        sortData($tracks, 'name');
        sortData($localizations, 'name');

        $getTrackDetails = function ($trackId) use ($tracks)
        {
            foreach ($tracks as $track) {
                if ($track->id === $trackId ) {
                    return (object) array(
                        "name" => $track->name,
                        "color" => $track->color
                    );
                }
            }
            return false;
        };

        $getLocalizationName = function ($localizationId) use ($localizations)
        {
            foreach ($localizations as $localization) {
                if ($localization->id === $localizationId) {
                    return $localization->name;
                }
            }
            return false;
        };
    ?>

    <?php if (sizeof($days) > 1): ?>
        <div class="conf-days">
            <?php foreach ($days as $index => $day): ?>
                <button type="button"
                        class="conf-days__item <?= $index === 0 ? 'conf-days__item--selected' : ''; ?>"
                        data-day-filter="<?= $day->date; ?>"
                        title="<?= __('Day') . ' ' . ($index + 1) . ' - ' . $day->date ?>"
                >
                    <?= __('Day') . '&nbsp;' . ($index + 1) ?>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="conf-filters-wrapper">
        <button type="button" class="conf-filters-dropdown-trigger">
            <span class="conf-filters-dropdown-trigger__icon">
                <?php include 'assets/images/conf-filters-dropdown-trigger.svg'; ?>
            </span>
            <?= __('Filter') ?>
        </button>

        <div class="conf-filters-dropdown">
            <?php if (sizeof($tracks) > 1): ?>
                <div class="conf-filter conf-filter--tracks">
                    <h2 class="conf-filter__label">
                        <?= __('Tracks') ?>
                    </h2>
                    <button class="conf-filter__item conf-filter__item--selected"
                            data-track-filter="all"
                    >
                        <?= __('All') ?>
                    </button>
                    <?php foreach ($tracks as $track): ?>
                        <button class="conf-filter__item"
                                data-track-filter="<?= $track->id ?>"
                        >
                            <span class="conf-filter__item-color"
                                  style="background-color: <?= $track->color ?>"
                            >
                                &nbsp;
                            </span>
                            <?= $track->name ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (sizeof($localizations) > 1): ?>
                <div class="conf-filter conf-filter--localizations">
                    <h2 class="conf-filter__label">
                        <?= __('Localizations') ?>
                    </h2>
                    <button type="button"
                            class="conf-filter__item conf-filter__item--selected"
                            data-localization-filter="all"
                    >
                        <?= __('All') ?>
                    </button>
                    <?php foreach ($localizations as $localization): ?>
                        <button type="button"
                                class="conf-filter__item"
                                data-localization-filter="<?= $localization->id ?>"
                        >
                            <?= $localization->name ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (sizeof($langs) > 1): ?>
                <div class="conf-filter conf-filter--languages">
                    <h2 class="conf-filter__label">
                        <?= __('Languages') ?>
                    </h2>
                    <button type="button"
                            class="conf-filter__item conf-filter__item--selected"
                            data-lang-filter="all"
                    >
                        <?= __('All') ?>
                    </button>
                    <?php foreach ($langs as $lang): ?>
                        <button type="button"
                                class="conf-filter__item"
                                data-lang-filter="<?= $lang->locale ?>"
                        >
                            <img class="conf-filter__item-icon"
                                 src="<?= plugins_url('assets/images/flags/' . $lang->locale . '.svg', __FILE__) ?>"
                                 alt="<?= $presentation->locale ?>"
                            />
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php foreach ($days as $day): ?>
        <ul class="conf-agenda"
            <?= sizeof($days) > 1 ? 'data-day="'. $day->date . '"' : ''; ?>
        >
            <?php
                $presentationsByDate = [];
                foreach (getConfrencePresentations($day->id) as $presentation) {
                    if ($presentationsByDate[$presentation->date]) {
                        array_push($presentationsByDate[$presentation->date], $presentation);
                    }
                    else {
                        $presentationsByDate[$presentation->date] = [$presentation];
                    }
                }
                ksort($presentationsByDate);
            ?>

            <?php foreach ($presentationsByDate as $date => $presentationsInSameTime): ?>
                <li class="conf-agenda__item">
                    <?php foreach ($presentationsInSameTime as $key => $presentation): ?>
                        <?php
                            $_speaker = getSpeaker($presentation->speaker_id)[0];

                            preg_match("/(\[[a-z]{2}\]) (.*)/", $presentation->name, $_parsingResults);
                            if (count($_parsingResults) === 3) {
                                $_presentationName = $_parsingResults[2];
                                $_presentationLang = str_replace(["[","]"], "", $_parsingResults[1]);
                            }
                            else {
                                $_presentationName = $presentation->name;
                                $_presentationLang = null;
                            }
                        ?>

                        <div class="conf-agenda__hour">
                            <?= date_format(date_create($date), 'H:i'); ?>
                        </div>

                        <div class="conf-agenda__row <?= 'conf-agenda__row--track-' . $presentation->track_id ?>"
                             <?= sizeof($localizations) > 1 ? 'data-localization="'. $presentation->localization_id . '"' : ''; ?>
                             <?= sizeof($tracks) > 1 ? 'data-track="'. $presentation->track_id . '"' : ''; ?>
                             <?= sizeof($langs) > 1 && $_presentationLang ? 'data-lang="'. $_presentationLang . '"' : ''; ?>
                        >
                            <div class="conf-agenda__presentation">
                                <div class="conf-agenda__presentation-subject">
                                    <?= $_presentationName ?>
                                </div>
                                <div class="conf-agenda__presentation-speaker">
                                    <?= $_speaker->name ?>
                                    <?= $_speaker->company ? ' - ' . $_speaker->company : ''; ?>
                                </div>
                            </div>
                            <div class="conf-agenda__info">

                                <?php if (sizeof($langs) > 1 && $_presentationLang): ?>
                                    <div class="conf-agenda__info-item conf-agenda__info-item--language">
                                        <img src="<?= plugins_url('assets/images/flags/' . $_presentationLang . '.svg', __FILE__) ?>"
                                             alt="<?= $_presentationLang ?>"
                                        />
                                    </div>
                                <?php endif; ?>

                                <?php if (sizeof($tracks) > 1): ?>
                                    <?php $track = $getTrackDetails($presentation->track_id); ?>
                                    <div class="conf-agenda__info-item conf-agenda__info-item--track">
                                        <span class="conf-agenda__info-item-color"
                                              style="background-color: <?= $track->color ?>"
                                        >
                                            &nbsp;
                                        </span>
                                        <?= $track->name ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (sizeof($localizations) > 1): ?>
                                    <div class="conf-agenda__info-item">
                                        <?= $getLocalizationName($presentation->localization_id); ?>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endforeach; ?>
</div>
