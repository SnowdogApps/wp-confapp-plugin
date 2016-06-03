<?php
/*
Plugin Name: Snowdog_Confapp
Plugin URI:
Description: Integration with confapp
Version: 1.0
Author Dawid Czaja
Author URI:
License:
*/


add_action('admin_menu', 'confapp_setup_menu');

/**
 * Add item to backend menu
 */
function confapp_setup_menu()
{
    add_menu_page('ConfApp', 'ConfApp', 'manage_options', 'confapp', 'confapp_init');
}

/**
 * Render config page
 */
function confapp_init()
{
    echo "<h1>ConfApp</h1>";
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
    add_option('confapp_general', array(
        'base_url' => '',
        'api_key' => '',
        'conference' => '',
        'default_language' => ''
    ));
}

function confapp_admin_init()
{
    global $confAppGeneral;
    if (isset($_POST['base_url']) && isset($_POST['api_key'])) {
        $new_values = array(
            'base_url' => htmlentities($_POST['base_url'], ENT_QUOTES),
            'api_key' => htmlentities($_POST['api_key'], ENT_QUOTES),
            'conference' => htmlentities($_POST['conference'], ENT_QUOTES),
            'default_language' => htmlentities($_POST['default_language'], ENT_QUOTES)
        );

        $confAppGeneral = $new_values;
        update_option('confapp_general', $new_values);
    }

    register_setting('confapp_admin', 'confapp_admin', 'confapp_admin_sanitize');

    add_settings_section('confapp_admin_selection', 'General', 'confapp_admin_selection_callback', 'confapp_admin_page');

    add_settings_field('confapp_admin_selection_base_url', 'Base url api', 'confapp_admin_selection_base_url_callback', 'confapp_admin_page', 'confapp_admin_selection');
    add_settings_field('confapp_admin_selection_api_key', 'Api key', 'confapp_admin_selection_api_key_callback', 'confapp_admin_page', 'confapp_admin_selection');
    add_settings_field('confapp_admin_selection_default_language', 'Select default language', 'confapp_admin_selection_default_language_callback', 'confapp_admin_page', 'confapp_admin_selection');
    add_settings_field('confapp_admin_selection_conference', 'Select conference', 'confapp_admin_selection_conference_callback', 'confapp_admin_page', 'confapp_admin_selection');

}

function confapp_admin_selection_callback()
{
}

function confapp_admin_selection_default_language_callback()
{
    global $confAppGeneral;
    global $wpdb;
    $tablenameLanguage = $wpdb->prefix . 'conference_translations';
    $results = $wpdb->get_results("SELECT locale FROM $tablenameLanguage");
    echo '<select name="default_language">';
    foreach ($results as $language) {
        echo '<option ';
        if ($language->locale == $confAppGeneral['default_language']) {
            echo 'selected';
        }
        echo ' value="' . $language->locale . '"/>' . $language->locale . '</option>';
    }
    echo '</select>';

}

function confapp_admin_selection_base_url_callback()
{
    global $confAppGeneral;

    echo '<input type="text" name="base_url" value="';
    if (isset($confAppGeneral['base_url']) && $confAppGeneral['base_url'] !== '') {
        echo $confAppGeneral['base_url'];
    }
    echo '"/>';
}

function confapp_admin_selection_api_key_callback()
{
    global $confAppGeneral;

    echo '<input type="text" name="api_key" value="';
    if (isset($confAppGeneral['api_key']) && $confAppGeneral['api_key'] !== '') {
        echo $confAppGeneral['api_key'];
    }
    echo '"/>';
}

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
            echo ' value="' . $conference["id"] . '"/>' . $conference['name'] . '</option>';
        }
        echo '</select>';
    } else {
        echo 'Wrong base url or api key';
    }
    echo '<p class="submit"><input type="submit" class="button-primary" value="SaveChanges" /></p>';
}

/**
 * Get page url
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
 * Synchronization WP data with confApp
 */
function synchronizeConference()
{
    global $confAppGeneral;

    global $wpdb;

    //synchronize conference
    $conferencesData = array_shift(getDataByCurl('conferences/' . $confAppGeneral['conference'] . '.json'));
    if ($conferencesData) {
        //add conference
        $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix . 'conferences');
        $table = $wpdb->prefix . "conferences";
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
//                'default_language' => json_encode($conferencesData['default_language']),
            )
        );

        //add conference translations
        $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix . 'conference_translations');
        $table = $wpdb->prefix . "conference_translations";
        $conferencesLanguage = getDataByCurl('conferences/' . $conferencesData['id'] . '/translations/conference_translations/' . $conferencesData['id'] . '/conference_id.json');
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
    $daysData = getDataByCurl('conferences/' . $confAppGeneral['conference'] . '/days.json');
    $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix . 'day_translations');
    $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix . 'day');
    if ($daysData) {
        $table = $wpdb->prefix . "day";
        foreach ($daysData as $day) {
            //add day
            $wpdb->insert(
                $table,
                array(
                    'id' => $day['id'],
                    'date' => $day['date'],
                    'conference_id' => $day['conference_id'],
                    'updated_at' => $day['updated_at'],
//                'default_language' => $day['default_language'],
                )
            );
        }

        //add day translations
        $tableTranslation = $wpdb->prefix . "day_translations";
        $dayLanguage = getDataByCurl('conferences/' . $conferencesData['id'] . '/translations/day_translations/' . $day['id'] . '/day_id.json');
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

    //synchronize floor
    $floors = getDataByCurl($confAppGeneral['conference'] . '/maps.json');
    $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix . 'floors');
    $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix . 'floor_translations');
    if ($floors) {
        $table = $wpdb->prefix . "floors";
        foreach ($floors as $floor) {
            $wpdb->insert(
                $table,
                array(
                    'id' => $floor['id'],
                    'avatar' => $floor['url'],
                    'order' => $floor['order'],
                    'conference_id' => $confAppGeneral['conference'],
//                'default_language' => $floor['default_language'],
                )
            );

            //add floor translations
            $tableTranslation = $wpdb->prefix . "floor_translations";
            $floorLanguage = getDataByCurl('conferences/' . $conferencesData['id'] . '/translations/floor_translations/' . $floor['id'] . '/floor_id.json');
            if ($floorLanguage) {
                foreach ($floorLanguage as $translation) {
                    $wpdb->insert(
                        $tableTranslation,
                        array(
                            'id' => $translation['id'],
                            'floor_id' => $translation['floor_id'],
                            'locale' => $translation['locale'],
                            'updated_at' => $translation['updated_at'],
                            'name' => $translation['name'],
                        )
                    );
                }
            }
        }
    }

    //synchronize presentation
    $presentations = getDataByCurl('conferences/' . $confAppGeneral['conference'] . '/presentations.json');
    $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix . 'presentation');
    $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix . 'presentation_translations');
    if ($presentations) {
        $table = $wpdb->prefix . "presentation";
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
            $tableTranslation = $wpdb->prefix . "presentation_translations";
            $presentationLanguage = getDataByCurl('conferences/' . $conferencesData['id'] . '/translations/presentation_translations/' . $presentation['id'] . '/presentation_id.json');
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
    $speeches = getDataByCurl('conferences/' . $confAppGeneral['conference'] . '/speeches.json');
    $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix . 'speaches');
    if ($speeches) {
        $table = $wpdb->prefix . "speaches";
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
    $speakers = getDataByCurl('conferences/' . $confAppGeneral['conference'] . '/speakers.json');
    $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix . 'speaker');
    $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix . 'speaker_translations');
    if ($speakers) {
        $table = $wpdb->prefix . "speaker";
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
            $tableTranslation = $wpdb->prefix . "speaker_translations";
            $speakerLanguage = getDataByCurl('conferences/' . $conferencesData['id'] . '/translations/speaker_translations/' . $speaker['id'] . '/speaker_id.json');
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
    $tracks = getDataByCurl('conferences/' . $confAppGeneral['conference'] . '/tracks.json');
    $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix . 'track');
    $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix . 'track_translations');
    if ($tracks) {
        $table = $wpdb->prefix . "track";
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
            $tableTranslation = $wpdb->prefix . "track_translations";
            $trackLanguage = getDataByCurl('conferences/' . $conferencesData['id'] . '/translations/track_translations/' . $track['id'] . '/track_id.json');
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
 * Get conferences from api
 */
function getConferences()
{
    return getDataByCurl('conferences.json');
}

/**
 * Get data by curl
 */
function getDataByCurl($url)
{
    global $confAppGeneral;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $confAppGeneral['base_url'] . $url . '?key=' . $confAppGeneral['api_key']);
    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}

/**
 * Installer
 */
function confapp_activate()
{
    global $wpdb;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $tableNameConferences = $wpdb->prefix . 'conferences';

    if ($wpdb->get_var('SHOW TABLES LIKE ' . $tableNameConferences) != $tableNameConferences) {
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

    $tableNameConferenceTranslation = $wpdb->prefix . 'conference_translations';

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

    $tableNameDay = $wpdb->prefix . 'day';

    if ($wpdb->get_var('SHOW TABLES LIKE ' . $tableNameDay) != $tableNameDay) {
        $sql = 'CREATE TABLE ' . $tableNameDay . '(
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `date` date DEFAULT NULL,
                  `conference_id` int(11) DEFAULT NULL,
                  `updated_at` datetime DEFAULT NULL,
                  PRIMARY KEY (`id`))';

        dbDelta($sql);
    }

    $tableNameDayTranslations = $wpdb->prefix . 'day_translations';

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

    $tableNamePresentation = $wpdb->prefix . 'presentation';

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

    $tableNamePresentationTranslations = $wpdb->prefix . 'presentation_translations';

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

    $tableNameSpeaker = $wpdb->prefix . 'speaker';

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

    $tableNameSpeakerTranslations = $wpdb->prefix . 'speaker_translations';

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

    $tableNameSpeaches = $wpdb->prefix . 'speaches';

    if ($wpdb->get_var('SHOW TABLES LIKE ' . $tableNameSpeaches) != $tableNameSpeaches) {
        $sql = 'CREATE TABLE ' . $tableNameSpeaches . '(
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `speaker_id` int(11) DEFAULT NULL,
                  `presentation_id` int(11) DEFAULT NULL,
                  `updated_at` datetime DEFAULT NULL,
                  PRIMARY KEY (`id`))';

        dbDelta($sql);
    }

    $tableTrack = $wpdb->prefix . 'track';

    if ($wpdb->get_var('SHOW TABLES LIKE ' . $tableTrack) != $tableTrack) {
        $sql = 'CREATE TABLE ' . $tableTrack . '(
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `color` varchar(11) DEFAULT NULL,
                      `conference_id` int(11) DEFAULT NULL,
                      `updated_at` datetime DEFAULT NULL,
                  PRIMARY KEY (`id`))';

        dbDelta($sql);
    }

    $tableNameTrackTranslations = $wpdb->prefix . 'track_translations';

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

    $tableFloors = $wpdb->prefix . 'floors';

    if ($wpdb->get_var('SHOW TABLES LIKE ' . $tableFloors) != $tableFloors) {
        $sql = 'CREATE TABLE ' . $tableFloors . '(
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `conference_id` int(11) DEFAULT NULL,
                      `avatar` varchar(255) DEFAULT NULL,
                      `order` int(11) DEFAULT NULL,
                      `updated_at` datetime DEFAULT NULL,
                  PRIMARY KEY (`id`))';

        dbDelta($sql);
    }

    $tableNameFloorTranslations = $wpdb->prefix . 'floor_translations';

    if ($wpdb->get_var('SHOW TABLES LIKE ' . $tableNameFloorTranslations) != $tableNameFloorTranslations) {
        $sql = 'CREATE TABLE ' . $tableNameFloorTranslations . '(
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `floor_id` int(11) DEFAULT NULL,
                  `updated_at` datetime DEFAULT NULL,
                  `locale` varchar(10) DEFAULT NULL,
                  `name` varchar(255) DEFAULT NULL,
                  PRIMARY KEY (`id`))';

        dbDelta($sql);
    }
    confapp_theme_addititonal();

    add_option('confapp_database_version', '1.0');
}

/**
 * Add template
 *
 * @return bool
 */
function confapp_theme_addititonal()
{
    $fileToAdd = ABSPATH . "wp-content/plugins/confapp/confappTemplate.php";
    $duplicatedDirFilename = get_template_directory() . "/confapp_pagetemplate.php";
    if (copy($fileToAdd, $duplicatedDirFilename)) {
        $data = file_get_contents($fileToAdd);
        if ($data == false) {
            return false;
        }

        $handle = fopen($duplicatedDirFilename, 'w');
        fwrite($handle, $data);
        fclose($handle);
    }
}

/**
 * Remove template
 */
function confapp_theme_subtractextras()
{
    $fileToDelete = get_template_directory() . "confapp_pagetemplate." . strtolower(trim(get_current_theme())) . ".php";
    $fh = fopen($fileToDelete, 'w') or die('can`t open file');
    fclose($fh);
    unlink($fileToDelete);
}

/**
 * Activate module hook
 */
register_activation_hook(__FILE__, 'confapp_activate');
/**
 * Uninstall module hook
 */
register_uninstall_hook(__FILE__, 'confapp_theme_subtractextras');


function getConfrenceDays()
{
    global $confAppGeneral;
    global $wpdb;
    $tablename = $wpdb->prefix . 'day';
    $tablenameLanguage = $wpdb->prefix . 'day_translations';
    $language = getLang();

    $results = $wpdb->get_results("
      SELECT * FROM $tablename
      LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.day_id
      WHERE  $tablenameLanguage.locale = '$language'
    ");

    if (!$results) {
        $results = $wpdb->get_results("
      SELECT * FROM $tablename
      LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.day_id
      WHERE  $tablenameLanguage.locale = '" . $confAppGeneral['default_language'] . "'
    ");
    }

    return $results;
}

function getConfrenceFloors()
{
    global $confAppGeneral;
    global $wpdb;
    $tablename = $wpdb->prefix . 'floors';
    $tablenameLanguage = $wpdb->prefix . 'floor_translations';
    $language = getLang();

    $results = $wpdb->get_results("
      SELECT * FROM $tablename
      LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.floor_id
      WHERE  $tablenameLanguage.locale = '$language'
    ");

    if (!$results) {
        $results = $wpdb->get_results("
      SELECT * FROM $tablename
      LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.floor_id
      WHERE  $tablenameLanguage.locale = '" . $confAppGeneral['default_language'] . "'
    ");
    }
}

function getConfrencePresentations($day, $track)
{
    global $confAppGeneral;
    global $wpdb;
    $tablename = $wpdb->prefix . 'presentation';
    $tablenameLanguage = $wpdb->prefix . 'presentation_translations';
    $tablenameSpekaer = $wpdb->prefix . 'speaker';
    $tablenameSpekaerTranslation = $wpdb->prefix . 'speaker_translations';
    $tablenameSpeeches = $wpdb->prefix . 'speaches';
    $language = getLang();

    $results = $wpdb->get_results("
      SELECT $tablename.*, $tablenameSpekaerTranslation.name FROM $tablename
      LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.presentation_id
      LEFT JOIN $tablenameSpeeches ON $tablename.id = $tablenameSpeeches.presentation_id
      LEFT JOIN $tablenameSpekaer ON $tablenameSpeeches.speaker_id = $tablenameSpekaer.id
      LEFT JOIN $tablenameSpekaerTranslation ON $tablenameSpekaer.id = $tablenameSpekaerTranslation.speaker_id AND $tablenameSpekaerTranslation.locale ='$language'
      WHERE  $tablenameLanguage.locale = '$language'
      AND $tablename.day_id = $day
      AND $tablename.track_id = $track
    ");

    if (!$results) {
        $results = $wpdb->get_results("
      SELECT $tablename.*, $tablenameSpekaerTranslation.name FROM $tablename
      LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.presentation_id
      LEFT JOIN $tablenameSpeeches ON $tablename.id = $tablenameSpeeches.presentation_id
      LEFT JOIN $tablenameSpekaer ON $tablenameSpeeches.speaker_id = $tablenameSpekaer.id
      LEFT JOIN $tablenameSpekaerTranslation ON $tablenameSpekaer.id = $tablenameSpekaerTranslation.speaker_id AND $tablenameSpekaerTranslation.locale = '" . $confAppGeneral['default_language'] . "'
      WHERE  $tablenameLanguage.locale = '" . $confAppGeneral['default_language'] . "'
      AND $tablename.day_id = $day
      AND $tablename.track_id = $track
    ");
    }

    return $results;
}

function getSpeaker($speakerId)
{
    global $confAppGeneral;
    global $wpdb;
    $tablename = $wpdb->prefix . 'speaker';
    $tablenameLanguage = $wpdb->prefix . 'speaker_translations';
    $language = getLang();

    $results = $wpdb->get_results("
      SELECT * FROM $tablename
      LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.speaker_id
      WHERE  $tablenameLanguage.locale = '$language'
      AND $tablename.id = $speakerId
    ");

    if (!$results) {
        $results = $wpdb->get_results("
      SELECT * FROM $tablename
      LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.speaker_id
      WHERE  $tablenameLanguage.locale = '" . $confAppGeneral['default_language'] . "'
      AND $tablename.id = $speakerId
    ");
    }

    return $results;
}

function getTracks()
{
    global $confAppGeneral;
    global $wpdb;
    $tablename = $wpdb->prefix . 'track';
    $tablenameLanguage = $wpdb->prefix . 'track_translations';
    $language = getLang();

    $results = $wpdb->get_results("
      SELECT * FROM $tablename
      LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.track_id
      WHERE  $tablenameLanguage.locale = '$language'
    ");

    if (!$results) {
        $results = $wpdb->get_results("
      SELECT * FROM $tablename
      LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.track_id
      WHERE  $tablenameLanguage.locale = '" . $confAppGeneral['default_language'] . "'
    ");
    }

    return $results;
}

function getConfrenceData()
{
    global $confAppGeneral;
    global $wpdb;
    $tablename = $wpdb->prefix . 'conferences';
    $tablenameLanguage = $wpdb->prefix . 'conference_translations';
    $language = getLang();

    $results = $wpdb->get_results("
      SELECT * FROM $tablename
      LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.conference_id
      WHERE  $tablenameLanguage.locale = '$language'
      LIMIT 1
    ");

    if (!$results) {
        $results = $wpdb->get_results("
      SELECT * FROM $tablename
      LEFT JOIN $tablenameLanguage ON $tablename.id = $tablenameLanguage.conference_id
      WHERE  $tablenameLanguage.locale = '" . $confAppGeneral['default_language'] . "'
      LIMIT 1
    ");
    }
    return array_shift($results);
}

function getLang()
{
    return substr(get_locale(), 0, 2);
}