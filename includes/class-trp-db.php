<?php

if (!defined('ABSPATH')) {
    exit;
}

class TRP_DB
{
    public static function table($name)
    {
        global $wpdb;
        return $wpdb->prefix . 'trp_' . $name;
    }

    public static function install()
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = [];

        $sql[] = "CREATE TABLE " . self::table('seasons') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(190) NOT NULL,
            season_year SMALLINT UNSIGNED NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'draft',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY season_year (season_year)
        ) $charset_collate;";

        $sql[] = "CREATE TABLE " . self::table('events') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            season_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(190) NOT NULL,
            stage_number TINYINT UNSIGNED NOT NULL,
            date_start DATE NULL,
            date_end DATE NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'draft',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_stage_in_season (season_id, stage_number),
            KEY season_id (season_id)
        ) $charset_collate;";

        $sql[] = "CREATE TABLE " . self::table('categories') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            season_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(120) NOT NULL,
            slug VARCHAR(120) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_category_in_season (season_id, slug),
            KEY season_id (season_id)
        ) $charset_collate;";

        $sql[] = "CREATE TABLE " . self::table('participants') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            first_name VARCHAR(120) NOT NULL,
            last_name VARCHAR(120) NOT NULL,
            city VARCHAR(120) NULL,
            license_number VARCHAR(100) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY last_name (last_name)
        ) $charset_collate;";

        $sql[] = "CREATE TABLE " . self::table('points') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            season_id BIGINT UNSIGNED NOT NULL,
            place_number INT UNSIGNED NOT NULL,
            points INT NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_place_in_season (season_id, place_number),
            KEY season_id (season_id)
        ) $charset_collate;";

        $sql[] = "CREATE TABLE " . self::table('season_settings') . " (
            season_id BIGINT UNSIGNED NOT NULL,
            scoring_type VARCHAR(20) NOT NULL DEFAULT 'all',
            best_events_count TINYINT UNSIGNED NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (season_id)
        ) $charset_collate;";

        $sql[] = "CREATE TABLE " . self::table('results') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            season_id BIGINT UNSIGNED NOT NULL,
            event_id BIGINT UNSIGNED NOT NULL,
            category_id BIGINT UNSIGNED NOT NULL,
            pilot_id BIGINT UNSIGNED NOT NULL,
            co_driver_id BIGINT UNSIGNED NULL,
            place_number INT UNSIGNED NOT NULL,
            points INT NOT NULL DEFAULT 0,
            status VARCHAR(20) NOT NULL DEFAULT 'finish',
            time_seconds INT UNSIGNED NULL,
            penalty_seconds INT UNSIGNED NOT NULL DEFAULT 0,
            notes TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_pilot_per_event (event_id, pilot_id),
            KEY season_cat_pilot (season_id, category_id, pilot_id),
            KEY event_id (event_id)
        ) $charset_collate;";

        foreach ($sql as $statement) {
            dbDelta($statement);
        }

        add_option('trp_plugin_version', TRP_PLUGIN_VERSION);
    }
}
