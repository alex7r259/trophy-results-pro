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
        add_action('admin_post_trp_save_category', [__CLASS__, 'save_category']);
        add_action('admin_post_trp_save_points', [__CLASS__, 'save_points']);
        add_action('admin_post_trp_save_season_settings', [__CLASS__, 'save_season_settings']);
        add_action('admin_post_trp_delete_row', [__CLASS__, 'delete_row']);
        add_action('admin_post_trp_update_result_status', [__CLASS__, 'update_result_status']);
        add_action('admin_post_trp_update_season_status', [__CLASS__, 'update_season_status']);
        add_action('admin_post_trp_update_event_status', [__CLASS__, 'update_event_status']);
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
        $allowed_tabs = ['results', 'seasons', 'events', 'participants', 'categories', 'points', 'settings'];
        if (!in_array($tab, $allowed_tabs, true)) {
            $tab = 'results';
        }

        global $wpdb;

        $seasons = $wpdb->get_results('SELECT * FROM ' . TRP_DB::table('seasons') . ' ORDER BY season_year DESC, id DESC');
        $events = $wpdb->get_results('SELECT * FROM ' . TRP_DB::table('events') . ' ORDER BY id DESC');
        $participants = $wpdb->get_results('SELECT * FROM ' . TRP_DB::table('participants') . ' ORDER BY last_name ASC, first_name ASC');
        $categories = $wpdb->get_results('SELECT * FROM ' . TRP_DB::table('categories') . ' ORDER BY season_id DESC, name ASC');
        $points_rows = $wpdb->get_results('SELECT * FROM ' . TRP_DB::table('points') . ' ORDER BY season_id DESC, place_number ASC');
        $season_settings = $wpdb->get_results('SELECT * FROM ' . TRP_DB::table('season_settings') . ' ORDER BY season_id DESC');
        $results = $wpdb->get_results('SELECT * FROM ' . TRP_DB::table('results') . ' ORDER BY id DESC LIMIT 100');

        include TRP_PLUGIN_PATH . 'includes/views/admin-page.php';
    }

    private static function delete_map()
    {
        return [
            'seasons' => ['table' => TRP_DB::table('seasons'), 'pk' => 'id', 'tab' => 'seasons'],
            'events' => ['table' => TRP_DB::table('events'), 'pk' => 'id', 'tab' => 'events'],
            'participants' => ['table' => TRP_DB::table('participants'), 'pk' => 'id', 'tab' => 'participants'],
            'categories' => ['table' => TRP_DB::table('categories'), 'pk' => 'id', 'tab' => 'categories'],
            'points' => ['table' => TRP_DB::table('points'), 'pk' => 'id', 'tab' => 'points'],
            'settings' => ['table' => TRP_DB::table('season_settings'), 'pk' => 'season_id', 'tab' => 'settings'],
            'results' => ['table' => TRP_DB::table('results'), 'pk' => 'id', 'tab' => 'results'],
        ];
    }

    public static function delete_row()
    {
        if (!current_user_can('trp_manage_data')) {
            wp_die(__('Insufficient permissions', 'trp'));
        }

        check_admin_referer('trp_delete_row');

        $entity = isset($_GET['entity']) ? sanitize_key($_GET['entity']) : '';
        $id = isset($_GET['id']) ? absint($_GET['id']) : 0;

        $map = self::delete_map();
        if (!isset($map[$entity]) || !$id) {
            wp_safe_redirect(admin_url('admin.php?page=trp-dashboard'));
            exit;
        }

        global $wpdb;
        $wpdb->delete(
            $map[$entity]['table'],
            [$map[$entity]['pk'] => $id],
            ['%d']
        );

        wp_safe_redirect(admin_url('admin.php?page=trp-dashboard&tab=' . $map[$entity]['tab'] . '&deleted=1'));
        exit;
    }


    public static function update_season_status()
    {
        if (!current_user_can('trp_manage_data')) {
            wp_die(__('Insufficient permissions', 'trp'));
        }

        check_admin_referer('trp_update_season_status');

        $season_id = isset($_POST['season_id']) ? absint($_POST['season_id']) : 0;
        $status = isset($_POST['status']) ? sanitize_key($_POST['status']) : 'draft';
        $allowed = ['draft', 'active', 'completed'];

        if ($season_id && in_array($status, $allowed, true)) {
            global $wpdb;

            if ($status === 'active') {
                $wpdb->query("UPDATE " . TRP_DB::table('seasons') . " SET status = 'draft' WHERE status = 'active' AND id != " . (int) $season_id);
            }

            $wpdb->update(
                TRP_DB::table('seasons'),
                ['status' => $status],
                ['id' => $season_id],
                ['%s'],
                ['%d']
            );
        }

        wp_safe_redirect(admin_url('admin.php?page=trp-dashboard&tab=seasons&saved=1'));
        exit;
    }

    public static function update_event_status()
    {
        if (!current_user_can('trp_manage_data')) {
            wp_die(__('Insufficient permissions', 'trp'));
        }

        check_admin_referer('trp_update_event_status');

        $event_id = isset($_POST['event_id']) ? absint($_POST['event_id']) : 0;
        $status = isset($_POST['status']) ? sanitize_key($_POST['status']) : 'draft';
        $allowed = ['draft', 'published', 'closed'];

        if ($event_id && in_array($status, $allowed, true)) {
            global $wpdb;
            $wpdb->update(
                TRP_DB::table('events'),
                ['status' => $status],
                ['id' => $event_id],
                ['%s'],
                ['%d']
            );
        }

        wp_safe_redirect(admin_url('admin.php?page=trp-dashboard&tab=events&saved=1'));
        exit;
    }


    public static function update_result_status()
    {
        if (!current_user_can('trp_manage_data')) {
            wp_die(__('Insufficient permissions', 'trp'));
        }

        check_admin_referer('trp_update_result_status');

        $result_id = isset($_POST['result_id']) ? absint($_POST['result_id']) : 0;
        $status = isset($_POST['status']) ? sanitize_key($_POST['status']) : 'finish';
        $allowed_statuses = ['finish', 'dnf', 'dsq', 'dns'];

        if ($result_id && in_array($status, $allowed_statuses, true)) {
            global $wpdb;
            $results_table = TRP_DB::table('results');
            $result = $wpdb->get_row($wpdb->prepare(
                "SELECT id, season_id, place_number FROM {$results_table} WHERE id = %d",
                $result_id
            ));

            if ($result) {
                $points = ($status === 'finish')
                    ? TRP_Scoring::get_points_for_place((int) $result->season_id, (int) $result->place_number)
                    : 0;

                $wpdb->update(
                    $results_table,
                    [
                        'status' => $status,
                        'points' => $points,
                    ],
                    ['id' => $result_id],
                    ['%s', '%d'],
                    ['%d']
                );
            }
        }

        wp_safe_redirect(admin_url('admin.php?page=trp-dashboard&tab=results&saved=1'));
        exit;
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
            $status = in_array($status, ['draft', 'active', 'completed'], true) ? $status : 'draft';

            if ($status === 'active') {
                $wpdb->query("UPDATE " . TRP_DB::table('seasons') . " SET status = 'draft' WHERE status = 'active'");
            }

            $wpdb->insert(
                TRP_DB::table('seasons'),
                [
                    'name' => $name,
                    'season_year' => $season_year,
                    'status' => $status,
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
        $coefficient = isset($_POST['coefficient']) ? (float) $_POST['coefficient'] : 1.0;
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
                    'coefficient' => max(0.1, $coefficient),
                    'status' => in_array($status, ['draft', 'published', 'closed'], true) ? $status : 'draft',
                ],
                ['%d', '%s', '%d', '%s', '%s', '%f', '%s']
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

    public static function save_category()
    {
        if (!current_user_can('trp_manage_data')) {
            wp_die(__('Insufficient permissions', 'trp'));
        }
        check_admin_referer('trp_save_category');

        $season_id = isset($_POST['season_id']) ? absint($_POST['season_id']) : 0;
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';

        if ($season_id && $name) {
            global $wpdb;
            $slug = sanitize_title($name);
            $wpdb->insert(
                TRP_DB::table('categories'),
                [
                    'season_id' => $season_id,
                    'name' => $name,
                    'slug' => $slug,
                ],
                ['%d', '%s', '%s']
            );
        }

        wp_safe_redirect(admin_url('admin.php?page=trp-dashboard&tab=categories&saved=1'));
        exit;
    }

    public static function save_points()
    {
        if (!current_user_can('trp_manage_data')) {
            wp_die(__('Insufficient permissions', 'trp'));
        }
        check_admin_referer('trp_save_points');

        $season_id = isset($_POST['season_id']) ? absint($_POST['season_id']) : 0;
        $place_number = isset($_POST['place_number']) ? absint($_POST['place_number']) : 0;
        $points = isset($_POST['points']) ? intval($_POST['points']) : 0;

        if ($season_id && $place_number) {
            global $wpdb;
            $table = TRP_DB::table('points');
            $existing_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table} WHERE season_id = %d AND place_number = %d",
                $season_id,
                $place_number
            ));

            if ($existing_id) {
                $wpdb->update(
                    $table,
                    ['points' => $points],
                    ['id' => absint($existing_id)],
                    ['%d'],
                    ['%d']
                );
            } else {
                $wpdb->insert(
                    $table,
                    [
                        'season_id' => $season_id,
                        'place_number' => $place_number,
                        'points' => $points,
                    ],
                    ['%d', '%d', '%d']
                );
            }
        }

        wp_safe_redirect(admin_url('admin.php?page=trp-dashboard&tab=points&saved=1'));
        exit;
    }

    public static function save_season_settings()
    {
        if (!current_user_can('trp_manage_data')) {
            wp_die(__('Insufficient permissions', 'trp'));
        }
        check_admin_referer('trp_save_season_settings');

        $season_id = isset($_POST['season_id']) ? absint($_POST['season_id']) : 0;
        $scoring_type = isset($_POST['scoring_type']) ? sanitize_key($_POST['scoring_type']) : 'all';
        $best_events_count = isset($_POST['best_events_count']) ? absint($_POST['best_events_count']) : 0;

        if ($season_id) {
            if (!in_array($scoring_type, ['all', 'best_n'], true)) {
                $scoring_type = 'all';
            }

            global $wpdb;
            $table = TRP_DB::table('season_settings');
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT season_id FROM {$table} WHERE season_id = %d",
                $season_id
            ));

            if ($existing) {
                $wpdb->update(
                    $table,
                    [
                        'scoring_type' => $scoring_type,
                        'best_events_count' => $best_events_count,
                    ],
                    ['season_id' => $season_id],
                    ['%s', '%d'],
                    ['%d']
                );
            } else {
                $wpdb->insert(
                    $table,
                    [
                        'season_id' => $season_id,
                        'scoring_type' => $scoring_type,
                        'best_events_count' => $best_events_count,
                    ],
                    ['%d', '%s', '%d']
                );
            }
        }

        wp_safe_redirect(admin_url('admin.php?page=trp-dashboard&tab=settings&saved=1'));
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
        $start_number = isset($_POST['start_number']) ? sanitize_text_field($_POST['start_number']) : '';
        $car_name = isset($_POST['car_name']) ? sanitize_text_field($_POST['car_name']) : '';

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
                'start_number'    => $start_number,
                'car_name'        => $car_name,
            ],
            ['%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%d', '%d', '%s', '%s', '%s']
        );

        wp_safe_redirect(admin_url('admin.php?page=trp-dashboard&tab=results&saved=1'));
        exit;
    }
}
