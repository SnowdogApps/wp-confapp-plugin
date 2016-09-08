<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' ); ?>
<?php
/*
Plugin Name: Snowdog_Confapp
Plugin URI:
Description: Integration with confapp
Version: 1.0
Author: Dawid Czaja
Author URI:
License:
*/

add_action('admin_menu', 'confapp_setup_menu');

/**
 * Add item to backend menu.
 */
function confapp_setup_menu()
{
    add_menu_page('ConfApp', 'ConfApp', 'manage_options', 'confapp', 'confapp_init');
}

/**
 * Render config page.
 */
function confapp_init()
{
    echo '<h1>ConfApp</h1>';
    echo '<div class="wrap">';
    echo '<form action="' . get_page_url() . '" method="POST">';
    settings_errors('confapp_admin');
    do_settings_sections('confapp_admin_page');
    echo '</form>';
    echo '</div>';
    echo '<div class="wrap">';
    echo '<form action="' . get_page_url() . '" method="POST">';
    echo '<button value="1" name="synchronize" class="left button button-primary">Manual synchronize</button>';
    echo '</form>';
    echo '</div>';

    if (isset($_POST['synchronize'])) {
        synchronizeConference();
    }
}

add_action('admin_init', 'confapp_admin_init');

if (get_option('confapp_general')) {
    $confAppGeneral = get_option('confapp_general');
} else {
    add_option(
        'confapp_general', array(
            'base_url' => '',
            'api_key' => '',
            'conference' => '',
            'default_language' => ''
        )
    );
}

/**
 * Save post data from confapp configuration form.
 */
function confapp_admin_init()
{
    global $confAppGeneral;
    if (isset($_POST['base_url']) && isset($_POST['api_key'])) {
        $new_values = array(
            'base_url' => htmlentities($_POST['base_url'], ENT_QUOTES),
            'api_key' => htmlentities($_POST['api_key'], ENT_QUOTES),
            'conference' => htmlentities($_POST['conference'], ENT_QUOTES),
            'default_language' => htmlentities(
                $_POST['default_language'],
                ENT_QUOTES
            )
        );

        $confAppGeneral = $new_values;
        update_option('confapp_general', $new_values);
    }

    register_setting('confapp_admin', 'confapp_admin', 'confapp_admin_sanitize');

    add_settings_section(
        'confapp_admin_selection',
        'General',
        'confapp_admin_selection_callback',
        'confapp_admin_page'
    );
    add_settings_field(
        'confapp_admin_selection_base_url',
        'Base url api',
        'confapp_admin_selection_base_url_callback',
        'confapp_admin_page',
        'confapp_admin_selection'
    );
    add_settings_field(
        'confapp_admin_selection_api_key',
        'Api key',
        'confapp_admin_selection_api_key_callback',
        'confapp_admin_page',
        'confapp_admin_selection'
    );
    add_settings_field(
        'confapp_admin_selection_conference',
        'Select conference',
        'confapp_admin_selection_conference_callback',
        'confapp_admin_page',
        'confapp_admin_selection'
    );
    add_settings_field(
        'confapp_admin_selection_default_language',
        'Select default language',
        'confapp_admin_selection_default_language_callback',
        'confapp_admin_page',
        'confapp_admin_selection'
    );

}

function confapp_admin_selection_callback()
{
}

/**
 * Render default language select.
 */
function confapp_admin_selection_default_language_callback()
{
    global $confAppGeneral;
    global $wpdb;
    $tablenameLanguage = $wpdb->prefix . 'confapp_conference_translations';
    $results = $wpdb->get_results("SELECT locale FROM $tablenameLanguage");
    echo '<select name="default_language">';
    foreach ($results as $language) {
        echo '<option ';
        if ($language->locale == $confAppGeneral['default_language']) {
            echo 'selected';
        }
        echo ' value="'
            . $language->locale . '"/>'
            . $language->locale . '</option>';
    }
    echo '</select>';

}

/**
 * Render base url input.
 */
function confapp_admin_selection_base_url_callback()
{
    global $confAppGeneral;

    echo '<input type="text" name="base_url" value="';
    if (isset($confAppGeneral['base_url']) && $confAppGeneral['base_url'] !== '') {
        echo $confAppGeneral['base_url'];
    }
    echo '"/>';
}

/**
 * Render api key input.
 */
function confapp_admin_selection_api_key_callback()
{
    global $confAppGeneral;

    echo '<input type="text" name="api_key" value="';
    if (isset($confAppGeneral['api_key']) && $confAppGeneral['api_key'] !== '') {
        echo $confAppGeneral['api_key'];
    }
    echo '"/>';
}

/**
 * Render Conference select.
 */
function confapp_admin_selection_conference_callback()
{
    global $confAppGeneral;
    $conferences = getConferences();

    if ($conferences) {
        echo '<select name="conference">';
        foreach ($conferences as $conference) {
            echo '<option ';
            if ($conference['id'] == $confAppGeneral['conference']) {
                echo 'selected';
            }
            echo ' value="' .
                $conference['id'] . '"/>' .
                $conference['name'] . '</option>';
        }
        echo '</select>';
    } else {
        echo 'Wrong base url or api key';
    }
    echo '<p class="submit"><input type="submit" class="button-primary" value="SaveChanges" /></p>';
}

/**
 * Get page url.
 *
 * @param string $page
 * @return string
 */
function get_page_url($page = 'config')
{

    if ($page = 'config') {
        $args = array('page' => 'confapp');
    }

    $url = add_query_arg($args, admin_url('admin.php'));

    return $url;
}

/**
 * Synchronization WP data with confApp.
 */
function synchronizeConference()
{
    global $confAppGeneral;

    global $wpdb;

    //synchronize conference
    $conferencesData = array_shift(
        getDataByCurl('conferences/' . $confAppGeneral['conference'] . '.json')
    );

    if ($conferencesData) {
        //add conference
        $table = $wpdb->prefix . 'confapp_conferences';
        $wpdb->query("TRUNCATE TABLE  $table");
        $wpdb->insert(
            $table,
            array(
                'id' => $conferencesData['id'],
                'avatar' => $conferencesData['avatar']['normal'],
                'register_www' => $conferencesData['register_www'],
                'email' => $conferencesData['email'],
                'updated_at' => $conferencesData['updated_at'],
                'begin' => $conferencesData['begin'],
                'end' => $conferencesData['end'],
                'twitter_hashtag' => $conferencesData['twitter_hashtag'],
                'twitter_handle' => $conferencesData['twitter_handle'],
            )
        );

        //add conference translations
        $table = $wpdb->prefix . 'confapp_conference_translations';
        $wpdb->query("TRUNCATE TABLE $table");
        $conferencesLanguage = getDataByCurl(
            'conferences/' . $conferencesData['id'] .
            '/translations/conference_translations/' .
            $conferencesData['id'] . '/conference_id.json'
        );
        foreach ($conferencesLanguage as $translation) {
            $wpdb->insert(
                $table,
                array(
                    'id' => $translation['id'],
                    'conference_id' => $translation['conference_id'],
                    'locale' => $translation['locale'],
                    'updated_at' => $translation['updated_at'],
                    'name' => $translation['name'],
                    'description' => $translation['description'],
                )
            );
        }
    }

    //synchronize day
    $daysData = getDataByCurl(
        'conferences/' . $confAppGeneral['conference'] . '/days.json'
    );
    $table = $wpdb->prefix . 'confapp_day';
    $tableTranslation = $wpdb->prefix . 'confapp_day_translations';
    $wpdb->query("TRUNCATE TABLE $tableTranslation");
    $wpdb->query("TRUNCATE TABLE $table");
    if ($daysData) {
        foreach ($daysData as $day) {
            //add day
            $wpdb->insert(
                $table,
                array(
                    'id' => $day['id'],
                    'date' => $day['date'],
                    'conference_id' => $day['conference_id'],
                    'updated_at' => $day['updated_at'],
                )
            );
        }

        //add day translations
        $dayLanguage = getDataByCurl(
            'conferences/' . $conferencesData['id'] .
            '/translations/day_translations/' . $day['id'] . '/day_id.json'
        );
        if ($dayLanguage) {
            foreach ($dayLanguage as $translation) {
                $wpdb->insert(
                    $tableTranslation,
                    array(
                        'id' => $translation['id'],
                        'day_id' => $translation['day_id'],
                        'locale' => $translation['locale'],
                        'updated_at' => $translation['updated_at'],
                        'name' => $translation['name'],
                    )
                );
            }
        }
    }

    //synchronize maps
    $maps = getDataByCurl($confAppGeneral['conference'] . '/maps.json');
    $table = $wpdb->prefix . 'confapp_maps';
    $tableTranslation = $wpdb->prefix . 'confapp_maps_translations';
    $wpdb->query("TRUNCATE TABLE $table");
    $wpdb->query("TRUNCATE TABLE $tableTranslation");
    if ($maps) {
        foreach ($maps as $map) {
            $wpdb->insert(
                $table,
                array(
                    'id' => $map['id'],
                    'avatar' => $map['url'],
                    'order' => $map['order'],
                    'conference_id' => $confAppGeneral['conference'],
                )
            );

            //add map translatio
            $mapLanguage = getDataByCurl(
                'conferences/'
                . $conferencesData['id']
                . '/translations/map_translations/'
                . $map['id']
                . '/map_id.json'
            );
            if ($mapLanguage) {
                foreach ($mapLanguage as $translation) {
                    $wpdb->insert(
                        $tableTranslation,
                        array(
                            'id' => $translation['id'],
                            'map_id' => $translation['map_id'],
                            'locale' => $translation['locale'],
                            'updated_at' => $translation['updated_at'],
                            'name' => $translation['name'],
                        )
                    );
                }
            }
        }
    }

    //synchronize localizations
    $localizations = getDataByCurl(
        'conferences/' . $confAppGeneral['conference'] . '/localizations.json'
    );
    $table = $wpdb->prefix . 'confapp_localizations';
    $tableTranslation = $wpdb->prefix . 'confapp_localizations_translations';
    $wpdb->query("TRUNCATE TABLE $table");
    $wpdb->query("TRUNCATE TABLE $tableTranslation");
    if ($localizations) {
        foreach ($localizations as $localization) {
            $wpdb->insert(
                $table,
                array(
                  'id' => $localization['id'],
                  'name' => $localization['name'],
                  'description' => $localization['description'],
                  'address' => $localization['address'],
                  'email' => $localization['email'],
                  'phone' => $localization['phone'],
                  'room' => $localization['room'],
                  'lon' => $localization['lon'],
                  'lat' => $localization['lat'],
                  'localization_type_id' => $localization['localization_type_id'],
                )
            );

            //add localization translatio
            $localizationLanguage = getDataByCurl(
                'conferences/'
                . $conferencesData['id']
                . '/translations/localization_translations/'
                . $localization['id']
                . '/localization_id.json'
            );
            if ($localizationLanguage) {
                foreach ($localizationLanguage as $translation) {
                    $wpdb->insert(
                        $tableTranslation,
                        array(
                            'id' => $translation['id'],
                            'localization_id' => $translation['localization_id'],
                            'locale' => $translation['locale'],
                            'created_at' => $translation['created_at'],
                            'updated_at' => $translation['updated_at'],
                            'name' => $translation['name'],
                            'description' => $translation['description'],
                        )
                    );
                }
            }
        }
    }

    //synchronize presentation
    $presentations = getDataByCurl(
        'conferences/' . $confAppGeneral['conference'] . '/presentations.json'
    );
    $table = $wpdb->prefix . 'confapp_presentation';
    $tableTranslation = $wpdb->prefix . 'confapp_presentation_translations';
    $wpdb->query("TRUNCATE TABLE $table");
    $wpdb->query("TRUNCATE TABLE $tableTranslation");
    if ($presentations) {
        foreach ($presentations as $presentation) {
            $wpdb->insert(
                $table,
                array(
                    'id' => $presentation['id'],
                    'avatar' => $presentation['avatar']['normal'],
                    'date' => $presentation['date'],
                    'duration' => $presentation['duration'],
                    'conference_id' => $confAppGeneral['conference'],
                    'track_id' => $presentation['track_id'],
                    'day_id' => $presentation['day_id'],
                    'updated_at' => $presentation['updated_at'],
                    'localization_id' => $presentation['localization_id'],
                )
            );

            //add presentation translations
            $presentationLanguage = getDataByCurl(
                'conferences/' . $conferencesData['id'] .
                '/translations/presentation_translations/' .
                $presentation['id'] . '/presentation_id.json'
            );
            if ($presentationLanguage) {
                foreach ($presentationLanguage as $translation) {
                    $wpdb->insert(
                        $tableTranslation,
                        array(
                            'id' => $translation['id'],
                            'presentation_id' => $translation['presentation_id'],
                            'locale' => $translation['locale'],
                            'updated_at' => $translation['updated_at'],
                            'name' => $translation['name'],
                            'description' => $translation['description'],
                        )
                    );
                }
            }
        }
    }

    //synchronize speeches
    $speeches = getDataByCurl(
        'conferences/' . $confAppGeneral['conference'] . '/speeches.json'
    );
    $table = $wpdb->prefix . 'confapp_speaches';
    $wpdb->query("TRUNCATE TABLE $table");
    if ($speeches) {
        foreach ($speeches as $speech) {
            $wpdb->insert(
                $table,
                array(
                    'id' => $speech['id'],
                    'speaker_id' => $speech['speaker_id'],
                    'presentation_id' => $speech['presentation_id'],
                    'updated_at' => $speech['updated_at']
                )
            );
        }
    }

    //synchronize speaker
    $speakers = getDataByCurl(
        'conferences/' . $confAppGeneral['conference'] . '/speakers.json'
    );
    $table = $wpdb->prefix . 'confapp_speaker';
    $tableTranslation = $wpdb->prefix . 'confapp_speaker_translations';
    $wpdb->query("TRUNCATE TABLE $table");
    $wpdb->query("TRUNCATE TABLE $tableTranslation");
    if ($speakers) {
        foreach ($speakers as $speaker) {
            $wpdb->insert(
                $table,
                array(
                    'id' => $speaker['id'],
                    'company' => $speaker['company'],
                    'avatar' => $speaker['avatar']['normal'],
                    'updated_at' => $speaker['updated_at'],
                    'www' => $speaker['www'],
                    'email' => $speaker['email'],
                    'phone' => $speaker['phone'],
                    'conference_id' => $speaker['conference_id'],
                    'twitter_handle' => $speaker['twitter_handle'],
                )
            );

            //add presentation translations
            $speakerLanguage = getDataByCurl(
                'conferences/' . $conferencesData['id'] .
                '/translations/speaker_translations/' .
                $speaker['id'] . '/speaker_id.json'
            );
            if ($speakerLanguage) {
                foreach ($speakerLanguage as $translation) {
                    $wpdb->insert(
                        $tableTranslation,
                        array(
                            'id' => $translation['id'],
                            'speaker_id' => $translation['speaker_id'],
                            'locale' => $translation['locale'],
                            'updated_at' => $translation['updated_at'],
                            'name' => $translation['name'],
                            'description' => $translation['description'],
                        )
                    );
                }
            }
        }
    }

    //synchronize tracks
    $tracks = getDataByCurl(
        'conferences/' . $confAppGeneral['conference'] . '/tracks.json'
    );
    $table = $wpdb->prefix . 'confapp_track';
    $tableTranslation = $wpdb->prefix . 'confapp_track_translations';
    $wpdb->query("TRUNCATE TABLE $table");
    $wpdb->query("TRUNCATE TABLE $tableTranslation");
    if ($tracks) {
        foreach ($tracks as $track) {
            $wpdb->insert(
                $table,
                array(
                    'id' => $track['id'],
                    'color' => $track['color'],
                    'updated_at' => $track['updated_at'],
                    'conference_id' => $track['conference_id'],
                )
            );

            //add tracks translations
            $trackLanguage = getDataByCurl(
                'conferences/' . $conferencesData['id'] .
                '/translations/track_translations/' . $track['id'] . '/track_id.json'
            );
            if ($trackLanguage) {
                foreach ($trackLanguage as $translation) {
                    $wpdb->insert(
                        $tableTranslation,
                        array(
                            'id' => $translation['id'],
                            'track_id' => $translation['track_id'],
                            'locale' => $translation['locale'],
                            'conference_id' => $conferencesData['id'],
                            'updated_at' => $translation['updated_at'],
                            'name' => $translation['name'],
                        )
                    );
                }
            }
        }
    }
}

/**
 * Get conferences from api.
 */
function getConferences()
{
    return getDataByCurl('conferences.json');
}

/**
 * Get data by curl.
 */
function getDataByCurl($url)
{
    global $confAppGeneral;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(
        $ch,
        CURLOPT_URL,
        $confAppGeneral['base_url'] . $url . '?key=' . $confAppGeneral['api_key']
    );
    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}

/**
 * Installer.
 */
function confapp_activate()
{
    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $tableNameConferences = $wpdb->prefix . 'confapp_conferences';

    if (
        $wpdb->get_var('SHOW TABLES LIKE ' . $tableNameConferences) != $tableNameConferences
    ) {
        $sql = 'CREATE TABLE ' . $tableNameConferences . '(
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `avatar` varchar(255) DEFAULT NULL,
                `www` varchar(255) DEFAULT NULL,
                `register_www` varchar(255) DEFAULT NULL,
                `email` varchar(255) DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                `begin` datetime DEFAULT NULL,
                `end` datetime DEFAULT NULL,
                `twitter_hashtag` varchar(255) DEFAULT NULL,
                `twitter_handle` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id`))';

        dbDelta($sql);
    }

    $tableNameConferenceTranslation = $wpdb->prefix . 'confapp_conference_translations';

    if ($wpdb->get_var('SHOW TABLES LIKE ' . $tableNameConferenceTranslation) != $tableNameConferenceTranslation) {
        $sql = 'CREATE TABLE ' . $tableNameConferenceTranslation . '(
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `conference_id` int(11) NOT NULL,
                `locale` varchar(255) NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `name` varchar(255) DEFAULT NULL,
                `description` text,
                PRIMARY KEY (`id`))';

        dbDelta($sql);
    }

    $tableNameDay = $wpdb->prefix . 'confapp_day';

    if ($wpdb->get_var('SHOW TABLES LIKE ' . $tableNameDay) != $tableNameDay) {
        $sql = 'CREATE TABLE ' . $tableNameDay . '(
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `date` date DEFAULT NULL,
                `conference_id` int(11) DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`))';

        dbDelta($sql);
    }

    $tableNameDayTranslations = $wpdb->prefix . 'confapp_day_translations';

    if ($wpdb->get_var('SHOW TABLES LIKE ' . $tableNameDayTranslations) != $tableNameDayTranslations) {
        $sql = 'CREATE TABLE ' . $tableNameDayTranslations . '(
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `day_id` int(11) NOT NULL,
                `locale` varchar(255) NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `name` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id`))';

        dbDelta($sql);
    }

    $tableNamePresentation = $wpdb->prefix . 'confapp_presentation';

    if ($wpdb->get_var('SHOW TABLES LIKE ' . $tableNamePresentation) != $tableNamePresentation) {
        $sql = 'CREATE TABLE ' . $tableNamePresentation . '(
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `date` datetime DEFAULT NULL,
                `duration` int(11) DEFAULT NULL,
                `avatar` varchar(255) DEFAULT NULL,
                `track_id` int(11) DEFAULT NULL,
                `day_id` int(11) DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                `localization_id` int(11) DEFAULT NULL,
                `conference_id` int(11) DEFAULT NULL,
                PRIMARY KEY (`id`))';

        dbDelta($sql);
    }

    $tableNamePresentationTranslations = $wpdb->prefix . 'confapp_presentation_translations';

    if ($wpdb->get_var('SHOW TABLES LIKE ' . $tableNamePresentationTranslations) != $tableNamePresentationTranslations) {
        $sql = 'CREATE TABLE ' . $tableNamePresentationTranslations . '(
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `presentation_id` int(11) NOT NULL,
                `locale` varchar(255) NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `name` varchar(255) DEFAULT NULL,
                `description` text,
                PRIMARY KEY (`id`))';

        dbDelta($sql);
    }

    $tableNameSpeaker = $wpdb->prefix . 'confapp_speaker';

    if ($wpdb->get_var('SHOW TABLES LIKE ' . $tableNameSpeaker) != $tableNameSpeaker) {
        $sql = 'CREATE TABLE ' . $tableNameSpeaker . '(
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `company` varchar(255) DEFAULT NULL,
                `avatar` varchar(255) DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                `www` varchar(255) DEFAULT NULL,
                `email` varchar(255) DEFAULT NULL,
                `phone` varchar(255) DEFAULT NULL,
                `conference_id` int(11) DEFAULT NULL,
                `twitter_handle` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id`))';

        dbDelta($sql);
    }

    $tableNameSpeakerTranslations = $wpdb->prefix . 'confapp_speaker_translations';

    if ($wpdb->get_var('SHOW TABLES LIKE ' . $tableNameSpeakerTranslations) != $tableNameSpeakerTranslations) {
        $sql = 'CREATE TABLE ' . $tableNameSpeakerTranslations . '(
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `speaker_id` int(11) NOT NULL,
                `locale` varchar(255) NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `name` varchar(255) DEFAULT NULL,
                `description` text,
                PRIMARY KEY (`id`))';

        dbDelta($sql);
    }

    $tableNameSpeaches = $wpdb->prefix . 'confapp_speaches';

    if ($wpdb->get_var('SHOW TABLES LIKE ' . $tableNameSpeaches) != $tableNameSpeaches) {
        $sql = 'CREATE TABLE ' . $tableNameSpeaches . '(
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `speaker_id` int(11) DEFAULT NULL,
                `presentation_id` int(11) DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`))';

        dbDelta($sql);
    }

    $tableTrack = $wpdb->prefix . 'confapp_track';

    if ($wpdb->get_var('SHOW TABLES LIKE ' . $tableTrack) != $tableTrack) {
        $sql = 'CREATE TABLE ' . $tableTrack . '(
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `color` varchar(11) DEFAULT NULL,
                `conference_id` int(11) DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`))';

        dbDelta($sql);
    }

    $tableNameTrackTranslations = $wpdb->prefix . 'confapp_track_translations';

    if ($wpdb->get_var('SHOW TABLES LIKE ' . $tableNameTrackTranslations) != $tableNameTrackTranslations) {
        $sql = 'CREATE TABLE ' . $tableNameTrackTranslations . '(
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `track_id` varchar(255) DEFAULT NULL,
                `conference_id` int(11) DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                `name` varchar(255) NOT NULL DEFAULT \'\',
                `locale` varchar(2) DEFAULT NULL,
                PRIMARY KEY (`id`))';

        dbDelta($sql);
    }

    $tableMaps = $wpdb->prefix . 'confapp_maps';

    if ($wpdb->get_var('SHOW TABLES LIKE ' . $tableMaps) != $tableMaps) {
        $sql = 'CREATE TABLE ' . $tableMaps . '(
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `conference_id` int(11) DEFAULT NULL,
                `avatar` varchar(255) DEFAULT NULL,
                `order` int(11) DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`))';

        dbDelta($sql);
    }

    $tableNameMapsTranslations = $wpdb->prefix . 'confapp_maps_translations';

    if ($wpdb->get_var('SHOW TABLES LIKE ' . $tableNameMapsTranslations) != $tableNameMapsTranslations) {
        $sql = 'CREATE TABLE ' . $tableNameMapsTranslations . '(
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `map_id` int(11) DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                `locale` varchar(10) DEFAULT NULL,
                `name` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id`))';

        dbDelta($sql);
    }


    $tableLocalizations = $wpdb->prefix . 'confapp_localizations';

    if ($wpdb->get_var('SHOW TABLES LIKE ' . $tableLocalizations) != $tableLocalizations) {
        $sql = 'CREATE TABLE ' . $tableLocalizations . '(
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` varchar(255) DEFAULT NULL,
                  `description` text,
                  `address` varchar(255) DEFAULT NULL,
                  `email` varchar(255) DEFAULT NULL,
                  `phone` varchar(255) DEFAULT NULL,
                  `room` varchar(255) DEFAULT NULL,
                  `lon` decimal(13,10) DEFAULT NULL,
                  `lat` decimal(13,10) DEFAULT NULL,
                  `localization_type_id` int(11) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                )';

        dbDelta($sql);
    }

    $tableNameLocalizationsTranslations = $wpdb->prefix . 'confapp_localizations_translations';

    if ($wpdb->get_var('SHOW TABLES LIKE ' . $tableNameLocalizationsTranslations) != $tableNameLocalizationsTranslations) {
        $sql = 'CREATE TABLE ' . $tableNameLocalizationsTranslations . '(
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `localization_id` int(11) NOT NULL,
                  `locale` varchar(255) NOT NULL,
                  `created_at` datetime DEFAULT NULL,
                  `updated_at` datetime DEFAULT NULL,
                  `name` varchar(255) DEFAULT NULL,
                  `description` text,
                  PRIMARY KEY (`id`)
                )';

        dbDelta($sql);
    }

    add_option('confapp_database_version', '1.0');
}

/**
 * Load agenda template and set static assets
 */
function get_agenda_template()
{
  include dirname( __FILE__ ) . '/agenda_template.php';


  wp_enqueue_style( 'confapp', plugins_url( 'assets/css/confapp.css' , __FILE__ ) );
  wp_enqueue_script( 'webfont', '//ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js', null, '1.6.16', true  );
  wp_enqueue_script( 'confapp', plugins_url( 'assets/js/confapp.js' , __FILE__ ), array( 'jquery', 'webfont' ), '1.0', true  );
}

/**
 * Register shortcode.
 */
add_shortcode( 'conffapp_agenda', 'get_agenda_template' );

/**
 * Activate module hook.
 */
register_activation_hook(__FILE__, 'confapp_activate');

/**
 * Get Days from database.
 *
 * @return mixed
 */
function getConfrenceDays()
{
    global $confAppGeneral;
    global $wpdb;
    $tablename = $wpdb->prefix . 'confapp_day';
    $tablenameLanguage = $wpdb->prefix . 'confapp_day_translations';
    $language = getConfrenceLang();

    $results = $wpdb->get_results("
      SELECT $tablename.*, $tablenameLanguage.name, $tablenameLanguage.locale FROM $tablename
      LEFT JOIN $tablenameLanguage ON $tablenameLanguage.day_id =  $tablename.id
      AND $tablenameLanguage.locale = '$language'
    ");

    if (!isset($results[0]->name) || $results[0]->name == null) {
        $results = $wpdb->get_results("
          SELECT $tablename.*, $tablenameLanguage.name, $tablenameLanguage.locale FROM $tablename
          LEFT JOIN $tablenameLanguage ON $tablenameLanguage.day_id =  $tablename.id
          AND $tablenameLanguage.locale = '{$confAppGeneral['default_language']}'
      ");
    }

    return $results;
}

/**
 * Get Maps from database.
 */
function getConfrenceMaps()
{
    global $confAppGeneral;
    global $wpdb;
    $tablename = $wpdb->prefix . 'confapp_maps';
    $tablenameLanguage = $wpdb->prefix . 'confapp_maps_translations';
    $language = getConfrenceLang();

    $results = $wpdb->get_results("
      SELECT $tablename.*, $tablenameLanguage.name, $tablenameLanguage.locale  FROM $tablename
      LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.map_id
      AND  $tablenameLanguage.locale = '$language'
    ");

    if (!isset($results[0]->name) || $results[0]->name == null) {
        $results = $wpdb->get_results("
          SELECT $tablename.*, $tablenameLanguage.name, $tablenameLanguage.locale  FROM $tablename
          LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.map_id
          AND  $tablenameLanguage.locale = '{$confAppGeneral['default_language']}'
      ");
    }

    return $results;
}

/**
 * Get Presentations from database.
 *
 * @param $day
 * @param $track
 * @return mixed
 */
function getConfrencePresentations($day)
{
    global $confAppGeneral;
    global $wpdb;
    $tablename = $wpdb->prefix . 'confapp_presentation';
    $tablenameLanguage = $wpdb->prefix . 'confapp_presentation_translations';
    $tablenameSpekaer = $wpdb->prefix . 'confapp_speaker';
    $tablenameSpekaerTranslation = $wpdb->prefix . 'confapp_speaker_translations';
    $tablenameSpeeches = $wpdb->prefix . 'confapp_speaches';
    $language = getConfrenceLang();

    $results = $wpdb->get_results("
      SELECT $tablename.*, $tablenameLanguage.name, $tablenameLanguage.description, $tablenameLanguage.locale
      FROM $tablename
      LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.presentation_id
      AND $tablenameLanguage.locale = '$language'
      WHERE $tablename.day_id = $day
      GROUP BY $tablename.id
    ");

    if (!isset($results[0]->name) || $results[0]->name == null) {
        $results = $wpdb->get_results("
          SELECT $tablename.*, $tablenameLanguage.name, $tablenameLanguage.description, $tablenameLanguage.locale
          FROM $tablename
          LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.presentation_id
          AND  $tablenameLanguage.locale = '{$confAppGeneral['default_language']}'
          WHERE $tablename.day_id = $day
          GROUP BY $tablename.id
    ");
    }

    foreach ($results as $key => $result) {
        $speaker = $wpdb->get_results("
          SELECT $tablenameSpekaerTranslation.name, $tablenameSpekaerTranslation.description,
                 $tablenameSpekaer.company
          FROM $tablenameSpeeches
          LEFT JOIN $tablenameSpekaer ON $tablenameSpekaer.id = $tablenameSpeeches.speaker_id
          LEFT JOIN $tablenameSpekaerTranslation ON $tablenameSpekaerTranslation.speaker_id = $tablenameSpeeches.speaker_id
          WHERE $tablenameSpeeches.presentation_id = $result->id
          GROUP BY $tablenameSpekaer.id"
        );

        if (!isset($speaker[0]->name) || $speaker[0]->name == null) {
            $speaker = $wpdb->get_results("
          SELECT $tablenameSpekaerTranslation.name, $tablenameSpekaerTranslation.description,
                 $tablenameSpekaer.company
          FROM $tablenameSpeeches
          LEFT JOIN $tablenameSpekaer ON $tablenameSpekaer.id = $tablenameSpeeches.speaker_id
          LEFT JOIN $tablenameSpekaerTranslation ON $tablenameSpekaerTranslation.speaker_id = $tablenameSpeeches.speaker_id
          AND $tablenameSpekaerTranslation.locale = '{$confAppGeneral['default_language']}'
          WHERE $tablenameSpeeches.presentation_id = $result->id
          GROUP BY $tablenameSpekaer.id"
            );
        }

        $result->speakers = $speaker;
    }

    return $results;
}

/**
 * Get Speaker data form database.
 *
 * @param $speakerId
 * @return mixed
 */
function getSpeaker($speakerId)
{
    global $confAppGeneral;
    global $wpdb;
    $tablename = $wpdb->prefix . 'confapp_speaker';
    $tablenameLanguage = $wpdb->prefix . 'confapp_speaker_translations';
    $language = getConfrenceLang();

    $results = $wpdb->get_results("
      SELECT $tablename.*, $tablenameLanguage.name, $tablenameLanguage.description, $tablenameLanguage.locale FROM $tablename
      LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.speaker_id
      AND  $tablenameLanguage.locale = '$language'
      WHERE $tablename.id = $speakerId
    ");

    if (!isset($results[0]->name) || $results[0]->name == null) {
        $results = $wpdb->get_results("
          SELECT $tablename.*, $tablenameLanguage.name, $tablenameLanguage.description, $tablenameLanguage.locale FROM $tablename
          LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.speaker_id
          AND  $tablenameLanguage.locale = '{$confAppGeneral['default_language']}'
          WHERE $tablename.id = $speakerId
      ");
    }

    return $results;
}

/**
 * Get Track data from database.
 *
 * @return mixed
 */
function getConfrenceTracks()
{
    global $confAppGeneral;
    global $wpdb;
    $tablename = $wpdb->prefix . 'confapp_track';
    $tablenameLanguage = $wpdb->prefix . 'confapp_track_translations';
    $language = getConfrenceLang();

    $results = $wpdb->get_results("
      SELECT $tablename.*, $tablenameLanguage.name, $tablenameLanguage.locale FROM $tablename
      LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.track_id
      AND  $tablenameLanguage.locale = '$language'
    ");

    if (!isset($results[0]->name) || $results[0]->name == null) {
        $results = $wpdb->get_results("
          SELECT $tablename.*, $tablenameLanguage.name, $tablenameLanguage.locale FROM $tablename
          LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.track_id
          AND  $tablenameLanguage.locale = '{$confAppGeneral['default_language']}'
      ");
    }

    return $results;
}

/**
 * Get Localization data from database.
 *
 * @return mixed
 */
function getConfrenceLocalizations()
{
    global $confAppGeneral;
    global $wpdb;
    $tablename = $wpdb->prefix . 'confapp_localizations';
    $tablenameLanguage = $wpdb->prefix . 'confapp_localizations_translations';
    $language = getConfrenceLang();

    $results = $wpdb->get_results("
      SELECT $tablename.*, $tablenameLanguage.name, $tablenameLanguage.description, $tablenameLanguage.locale FROM $tablename
      LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.localization_id
      AND $tablenameLanguage.locale = '$language'
    ");

    if (!isset($results[0]->name) || $results[0]->name == null) {
        $results = $wpdb->get_results("
          SELECT $tablename.*, $tablenameLanguage.name, $tablenameLanguage.description, $tablenameLanguage.locale FROM $tablename
          LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.localization_id
          AND $tablenameLanguage.locale = '{$confAppGeneral['default_language']}'
      ");
    }

    return $results;
}

/**
 * Get conference data from database.
 *
 * @return mixed
 */
function getConfrenceData()
{
    global $confAppGeneral;
    global $wpdb;
    $tablename = $wpdb->prefix . 'confapp_conferences';
    $tablenameLanguage = $wpdb->prefix . 'confapp_conference_translations';
    $language = getConfrenceLang();

    $results = $wpdb->get_results("
      SELECT $tablename.*, $tablenameLanguage.name, $tablenameLanguage.locale FROM $tablename
      LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.conference_id
      AND  $tablenameLanguage.locale = '$language'
      LIMIT 1
    ");

    if (!isset($results[0]->name) || $results[0]->name == null) {
        $results = $wpdb->get_results("
          SELECT $tablename.*, $tablenameLanguage.name, $tablenameLanguage.locale FROM $tablename
          LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.conference_id
          AND  $tablenameLanguage.locale = '{$confAppGeneral['default_language']}'
          LIMIT 1
      ");
    }

    return array_shift($results);
}

/**
 * Get language
 *
 * @return string
 */
function getConfrenceLang()
{
    return substr(get_locale(), 0, 2);
}


function getConfrenceLangs()
{
    global $wpdb;
    $tableName = $wpdb->prefix . 'confapp_conference_translations';
    return $wpdb->get_results("SELECT locale FROM $tableName");
}
