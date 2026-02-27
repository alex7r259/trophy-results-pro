<?php

if (!defined('ABSPATH')) {
    exit;
}

class TRP_Shortcode
{
    public static function init()
    {
        add_shortcode('trophy_standings', [__CLASS__, 'render_standings']);
    }

    public static function render_standings($atts)
    {
        $atts = shortcode_atts([
            'season' => 0,
            'category' => 0,
        ], $atts);

        $season_id = absint($atts['season']);
        $category_id = absint($atts['category']);

        global $wpdb;

        if (!$season_id) {
            $season_id = (int) $wpdb->get_var("SELECT id FROM " . TRP_DB::table('seasons') . " WHERE status = 'active' ORDER BY id DESC LIMIT 1");
        }

        if (!$season_id || !$category_id) {
            return '<p>' . esc_html__('Season (or active season) and category are required.', 'trp') . '</p>';
        }

        $events = $wpdb->get_results($wpdb->prepare(
            "SELECT id, name, stage_number, coefficient FROM " . TRP_DB::table('events') . " WHERE season_id = %d ORDER BY stage_number ASC",
            $season_id
        ));

        if (empty($events)) {
            return '<p>' . esc_html__('No events found for this season.', 'trp') . '</p>';
        }

        $event_coefficients = [];
        foreach ($events as $event) {
            $event_coefficients[(int) $event->id] = isset($event->coefficient) ? (float) $event->coefficient : 1.0;
        }

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT r.*, p.last_name AS pilot_last_name, c.last_name AS codriver_last_name
             FROM " . TRP_DB::table('results') . " r
             LEFT JOIN " . TRP_DB::table('participants') . " p ON p.id = r.pilot_id
             LEFT JOIN " . TRP_DB::table('participants') . " c ON c.id = r.co_driver_id
             WHERE r.season_id = %d AND r.category_id = %d",
            $season_id,
            $category_id
        ));

        if (empty($results)) {
            return '<p>' . esc_html__('No standings available yet.', 'trp') . '</p>';
        }

        $settings = $wpdb->get_row($wpdb->prepare(
            "SELECT scoring_type, best_events_count FROM " . TRP_DB::table('season_settings') . " WHERE season_id = %d",
            $season_id
        ));

        $rows = [];
        foreach ($results as $res) {
            $pilot_id = (int) $res->pilot_id;

            if (!isset($rows[$pilot_id])) {
                $rows[$pilot_id] = [
                    'pilot_id' => $pilot_id,
                    'pilot_last_name' => $res->pilot_last_name ?: ('#' . $pilot_id),
                    'codriver_last_name' => $res->codriver_last_name ?: '—',
                    'start_number' => $res->start_number ?: '—',
                    'car_name' => $res->car_name ?: '—',
                    'results_by_event' => [],
                    'finish_results' => [],
                    'total' => 0,
                    'counted_event_ids' => [],
                ];
            }

            $event_id = (int) $res->event_id;
            $coefficient = isset($event_coefficients[$event_id]) ? $event_coefficients[$event_id] : 1.0;
            $weighted_points = (int) round(((int) $res->points) * $coefficient);

            $rows[$pilot_id]['results_by_event'][$event_id] = [
                'place' => (int) $res->place_number,
                'points' => $weighted_points,
                'weighted_points' => $weighted_points,
                'coefficient' => $coefficient,
                'status' => $res->status,
            ];

            if (!empty($res->start_number)) {
                $rows[$pilot_id]['start_number'] = $res->start_number;
            }
            if (!empty($res->car_name)) {
                $rows[$pilot_id]['car_name'] = $res->car_name;
            }
            if (!empty($res->codriver_last_name) && $res->codriver_last_name !== '—') {
                $rows[$pilot_id]['codriver_last_name'] = $res->codriver_last_name;
            }

            if ($res->status === 'finish') {
                $rows[$pilot_id]['finish_results'][] = [
                    'event_id' => (int) $res->event_id,
                    'points' => $weighted_points,
                ];
            }
        }

        foreach ($rows as &$row) {
            $counted = $row['finish_results'];
            usort($counted, static function ($a, $b) {
                return $b['points'] <=> $a['points'];
            });

            if ($settings && $settings->scoring_type === 'best_n' && (int) $settings->best_events_count > 0) {
                $counted = array_slice($counted, 0, (int) $settings->best_events_count);
            }

            $row['counted_event_ids'] = array_map(static function ($r) {
                return (int) $r['event_id'];
            }, $counted);
            $row['total'] = array_sum(array_map(static function ($r) {
                return (int) $r['points'];
            }, $counted));
        }
        unset($row);

        usort($rows, static function ($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        ob_start();
        ?>
        <style>
            .trp-standings-table{width:100%;border-collapse:collapse}
            .trp-standings-table th,.trp-standings-table td{border:1px solid #ddd;padding:6px}
            .trp-counted-stage{background:#e8f5e9;font-weight:600}
        </style>
        <table class="trp-standings-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Место', 'trp'); ?></th>
                    <th><?php esc_html_e('Стартовый номер', 'trp'); ?></th>
                    <th><?php esc_html_e('Фамилия Пилот/Штурман', 'trp'); ?></th>
                    <th><?php esc_html_e('Автомобиль', 'trp'); ?></th>
                    <?php foreach ($events as $event) : ?>
                        <th><?php echo esc_html($event->stage_number . ' этап x' . (isset($event->coefficient) ? (float) $event->coefficient : 1)); ?></th>
                    <?php endforeach; ?>
                    <th><?php esc_html_e('Итог', 'trp'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $index => $row) : ?>
                    <tr>
                        <td><?php echo esc_html($index + 1); ?></td>
                        <td><?php echo esc_html($row['start_number']); ?></td>
                        <td><?php echo esc_html($row['pilot_last_name'] . ' / ' . $row['codriver_last_name']); ?></td>
                        <td><?php echo esc_html($row['car_name']); ?></td>
                        <?php foreach ($events as $event) :
                            $event_id = (int) $event->id;
                            $event_result = isset($row['results_by_event'][$event_id]) ? $row['results_by_event'][$event_id] : null;
                            $is_counted = in_array($event_id, $row['counted_event_ids'], true);
                            ?>
                            <td class="<?php echo $is_counted ? 'trp-counted-stage' : ''; ?>">
                                <?php if ($event_result) : ?>
                                    <?php echo esc_html($event_result['place'] . ' / ' . $event_result['weighted_points']); ?>
                                <?php else : ?>
                                    —
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                        <td><strong><?php echo esc_html($row['total']); ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php

        return ob_get_clean();
    }
}
