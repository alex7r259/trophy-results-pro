<?php

if (!defined('ABSPATH')) {
    exit;
}

class TRP_Admin
{
    public static function init()
    {
        add_action('admin_menu', [__CLASS__, 'register_menu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);

        add_action('admin_post_trp_save_result', [__CLASS__, 'save_result']);
        add_action('admin_post_trp_save_season', [__CLASS__, 'save_season']);
        add_action('admin_post_trp_save_event', [__CLASS__, 'save_event']);
        add_action('admin_post_trp_save_participant', [__CLASS__, 'save_participant']);
    }

    public static function enqueue_assets($hook)
    {
        if ($hook !== 'toplevel_page_trp-dashboard') {
            return;
        }

        wp_enqueue_style('trp-admin', TRP_PLUGIN_URL . 'assets/admin.css', [], TRP_PLUGIN_VERSION);
    }

    public static function register_menu()
    {
        add_menu_page(
            __('Trophy', 'trp'),
            __('Trophy', 'trp'),
            'trp_manage_data',
            'trp-dashboard',
            [__CLASS__, 'render_page'],
            'dashicons-awards',
            25
        );
    }

    public static function render_page()
    {
        if (!current_user_can('trp_manage_data')) {
            wp_die(__('Insufficient permissions', 'trp'));
        }

        $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'results';
        $allowed_tabs = ['results', 'seasons', 'events', 'participants'];
        if (!in_array($tab, $allowed_tabs, true)) {
            $tab = 'results';
        }

        global $wpdb;

        $seasons = $wpdb->get_results('SELECT * FROM ' . TRP_DB::table('seasons') . ' ORDER BY season_year DESC, id DESC');
        $events = $wpdb->get_results('SELECT * FROM ' . TRP_DB::table('events') . ' ORDER BY id DESC');
        $participants = $wpdb->get_results('SELECT * FROM ' . TRP_DB::table('participants') . ' ORDER BY last_name ASC, first_name ASC');
        $categories = $wpdb->get_results('SELECT id, name FROM ' . TRP_DB::table('categories') . ' ORDER BY name ASC');
        $results = $wpdb->get_results('SELECT * FROM ' . TRP_DB::table('results') . ' ORDER BY id DESC LIMIT 100');

        include TRP_PLUGIN_PATH . 'includes/views/admin-page.php';
    }

    public static function save_season()
    {
        if (!current_user_can('trp_manage_data')) {
            wp_die(__('Insufficient permissions', 'trp'));
        }
        check_admin_referer('trp_save_season');

        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $season_year = isset($_POST['season_year']) ? absint($_POST['season_year']) : 0;
        $status = isset($_POST['status']) ? sanitize_key($_POST['status']) : 'draft';

        if ($name && $season_year) {
            global $wpdb;
            $wpdb->insert(
                TRP_DB::table('seasons'),
                [
                    'name' => $name,
                    'season_year' => $season_year,
                    'status' => in_array($status, ['draft', 'active', 'completed'], true) ? $status : 'draft',
                ],
                ['%s', '%d', '%s']
            );
        }

        wp_safe_redirect(admin_url('admin.php?page=trp-dashboard&tab=seasons&saved=1'));
        exit;
    }

    public static function save_event()
    {
        if (!current_user_can('trp_manage_data')) {
            wp_die(__('Insufficient permissions', 'trp'));
        }
        check_admin_referer('trp_save_event');

        $season_id = isset($_POST['season_id']) ? absint($_POST['season_id']) : 0;
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $stage_number = isset($_POST['stage_number']) ? absint($_POST['stage_number']) : 0;
        $date_start = isset($_POST['date_start']) ? sanitize_text_field($_POST['date_start']) : '';
        $date_end = isset($_POST['date_end']) ? sanitize_text_field($_POST['date_end']) : '';
        $status = isset($_POST['status']) ? sanitize_key($_POST['status']) : 'draft';

        if ($season_id && $name && $stage_number) {
            global $wpdb;
            $wpdb->insert(
                TRP_DB::table('events'),
                [
                    'season_id' => $season_id,
                    'name' => $name,
                    'stage_number' => $stage_number,
                    'date_start' => $date_start ?: null,
                    'date_end' => $date_end ?: null,
                    'status' => in_array($status, ['draft', 'published', 'closed'], true) ? $status : 'draft',
                ],
                ['%d', '%s', '%d', '%s', '%s', '%s']
            );
        }

        wp_safe_redirect(admin_url('admin.php?page=trp-dashboard&tab=events&saved=1'));
        exit;
    }

    public static function save_participant()
    {
        if (!current_user_can('trp_manage_data')) {
            wp_die(__('Insufficient permissions', 'trp'));
        }
        check_admin_referer('trp_save_participant');

        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $city = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';
        $license_number = isset($_POST['license_number']) ? sanitize_text_field($_POST['license_number']) : '';

        if ($first_name && $last_name) {
            global $wpdb;
            $wpdb->insert(
                TRP_DB::table('participants'),
                [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'city' => $city,
                    'license_number' => $license_number,
                ],
                ['%s', '%s', '%s', '%s']
            );
        }

        wp_safe_redirect(admin_url('admin.php?page=trp-dashboard&tab=participants&saved=1'));
        exit;
    }

    public static function save_result()
    {
        if (!current_user_can('trp_manage_data')) {
            wp_die(__('Insufficient permissions', 'trp'));
        }

        check_admin_referer('trp_save_result');

        $season_id = isset($_POST['season_id']) ? absint($_POST['season_id']) : 0;
        $event_id = isset($_POST['event_id']) ? absint($_POST['event_id']) : 0;
        $category_id = isset($_POST['category_id']) ? absint($_POST['category_id']) : 0;
        $pilot_id = isset($_POST['pilot_id']) ? absint($_POST['pilot_id']) : 0;
        $co_driver_id = isset($_POST['co_driver_id']) ? absint($_POST['co_driver_id']) : 0;
        $place_number = isset($_POST['place_number']) ? absint($_POST['place_number']) : 0;
        $status = isset($_POST['status']) ? sanitize_key($_POST['status']) : 'finish';
        $time_seconds = isset($_POST['time_seconds']) ? absint($_POST['time_seconds']) : 0;
        $penalty_seconds = isset($_POST['penalty_seconds']) ? absint($_POST['penalty_seconds']) : 0;
        $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';

        $allowed_statuses = ['finish', 'dnf', 'dsq', 'dns'];
        if (!in_array($status, $allowed_statuses, true)) {
            $status = 'finish';
        }

        $points = ($status === 'finish') ? TRP_Scoring::get_points_for_place($season_id, $place_number) : 0;

        global $wpdb;
        $wpdb->insert(
            TRP_DB::table('results'),
            [
                'season_id'       => $season_id,
                'event_id'        => $event_id,
                'category_id'     => $category_id,
                'pilot_id'        => $pilot_id,
                'co_driver_id'    => $co_driver_id ?: null,
                'place_number'    => $place_number,
                'points'          => $points,
                'status'          => $status,
                'time_seconds'    => $time_seconds ?: null,
                'penalty_seconds' => $penalty_seconds,
                'notes'           => $notes,
            ],
            ['%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%d', '%d', '%s']
        );

        wp_safe_redirect(admin_url('admin.php?page=trp-dashboard&tab=results&saved=1'));
        exit;
    }
}
