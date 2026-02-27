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
            "SELECT id, stage_number, coefficient FROM " . TRP_DB::table('events') . " WHERE season_id = %d ORDER BY stage_number ASC",
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
            "SELECT r.*, COALESCE(pt.points,0) AS base_points_ref, p.last_name AS pilot_last_name, c.last_name AS codriver_last_name
             FROM " . TRP_DB::table('results') . " r
             LEFT JOIN " . TRP_DB::table('participants') . " p ON p.id = r.pilot_id
             LEFT JOIN " . TRP_DB::table('participants') . " c ON c.id = r.co_driver_id
             LEFT JOIN " . TRP_DB::table('points') . " pt ON pt.season_id = r.season_id AND pt.place_number = r.place_number
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
            $event_id = (int) $res->event_id;

            if (!isset($rows[$pilot_id])) {
                $rows[$pilot_id] = [
                    'pilot_last_name' => $res->pilot_last_name ?: ('#' . $pilot_id),
                    'codriver_last_name' => $res->codriver_last_name ?: '—',
                    'start_number' => $res->start_number ?: '—',
                    'car_name' => $res->car_name ?: '—',
                    'results_by_event' => [],
                    'finish_results' => [],
                    'counted_event_ids' => [],
                    'total' => 0,
                ];
            }

            $coef = isset($event_coefficients[$event_id]) ? $event_coefficients[$event_id] : 1.0;
            $base_points = (int) $res->base_points_ref;
            $weighted_points = (int) round($base_points * $coef);

            $rows[$pilot_id]['results_by_event'][$event_id] = [
                'place' => (int) $res->place_number,
                'status' => (string) $res->status,
                'base_points' => $base_points,
                'weighted_points' => $weighted_points,
            ];

            if (!empty($res->start_number)) {
                $rows[$pilot_id]['start_number'] = $res->start_number;
            }
            if (!empty($res->car_name)) {
                $rows[$pilot_id]['car_name'] = $res->car_name;
            }
            if (!empty($res->codriver_last_name)) {
                $rows[$pilot_id]['codriver_last_name'] = $res->codriver_last_name;
            }

            if ($res->status === 'finish') {
                $rows[$pilot_id]['finish_results'][] = [
                    'event_id' => $event_id,
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

            $row['counted_event_ids'] = array_map(static function ($e) {
                return (int) $e['event_id'];
            }, $counted);
            $row['total'] = array_sum(array_map(static function ($e) {
                return (int) $e['points'];
            }, $counted));
        }
        unset($row);

        usort($rows, static function ($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        ob_start();
        ?>
        <style>
            .table-responsive{overflow-x:auto;-webkit-overflow-scrolling:touch}
            .results-table{width:100%;min-width:1080px;border-collapse:collapse;background:#fff}
            .results-table th,.results-table td{border:1px solid #d7d7d7;padding:8px 6px}
            .results-table thead th{background:#f2f4f7;font-weight:700}
            .results_place{font-weight:700}
            .scores-border{border-right:2px solid #b5b5b5}
            .results_scores_all{font-weight:700;background:#f9fbfd}
            .trp-counted-stage{background:PaleGreen}
            @media (max-width:768px){.results-table{font-size:12px}.results-table th,.results-table td{padding:6px 4px}}
        </style>
        <div class="table-responsive">
            <figure class="wp-block-table is-style-stripes">
                <table class="delivery results-table responsive-mode">
                    <thead>
                        <tr>
                            <th rowspan="2" class="has-text-align-center" data-align="center"><?php esc_html_e('Место', 'trp'); ?></th>
                            <th rowspan="2" class="has-text-align-center" data-align="center"><?php esc_html_e('Стартовый<br>номер', 'trp'); ?></th>
                            <th rowspan="2" class="has-text-align-center" data-align="center"><?php esc_html_e('Фамилия Имя Пилот/Штурман', 'trp'); ?></th>
                            <th rowspan="2" class="has-text-align-center" data-align="center"><?php esc_html_e('Автомобиль', 'trp'); ?></th>
                            <?php foreach ($events as $event) : ?>
                                <th colspan="2" class="has-text-align-center" data-align="center"><?php echo esc_html($event->stage_number . ' этап'); ?></th>
                            <?php endforeach; ?>
                            <th rowspan="2" class="has-text-align-center" data-align="center"><?php esc_html_e('Баллы<br>Итог', 'trp'); ?></th>
                        </tr>
                        <tr>
                            <?php foreach ($events as $event) :
                                $coef = rtrim(rtrim((string) ((float) $event->coefficient), '0'), '.'); ?>
                                <th class="has-text-align-center" data-align="center"><?php esc_html_e('Место', 'trp'); ?></th>
                                <th class="has-text-align-center" data-align="center"><?php echo esc_html('Баллы (x' . $coef . ')'); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $rank => $row) :
                            $place_label = ($row['total'] > 0) ? (string) ($rank + 1) : '-'; ?>
                            <tr>
                                <td aria-label="Место" class="has-text-align-center results_place" data-align="center"><?php echo esc_html($place_label); ?></td>
                                <td aria-label="Стартовый номер" class="has-text-align-center" data-align="center"><?php echo esc_html($row['start_number']); ?></td>
                                <td aria-label="ФИО Пилот/Штурман" class="has-text-align-center" data-align="center"><?php echo esc_html($row['pilot_last_name'] . ', ' . $row['codriver_last_name']); ?></td>
                                <td aria-label="Автомобиль" class="has-text-align-center scores-border" data-align="center"><?php echo esc_html($row['car_name']); ?></td>
                                <?php foreach ($events as $event) :
                                    $event_id = (int) $event->id;
                                    $result = isset($row['results_by_event'][$event_id]) ? $row['results_by_event'][$event_id] : null;
                                    $is_counted = in_array($event_id, $row['counted_event_ids'], true);
                                    $cell_class = $is_counted ? 'trp-counted-stage' : '';
                                    $place = $result ? (string) $result['place'] : '-';
                                    $points_text = $result ? ($result['base_points'] . ' (' . $result['weighted_points'] . ')') : '0 (0)';
                                    ?>
                                    <td aria-label="Место <?php echo esc_attr($event->stage_number); ?> этап" class="has-text-align-center results_scores <?php echo esc_attr($cell_class); ?>" data-align="center"><?php echo esc_html($place); ?></td>
                                    <td aria-label="Баллы <?php echo esc_attr($event->stage_number); ?> этап" class="has-text-align-center results_scores scores-border <?php echo esc_attr($cell_class); ?>" data-align="center"><?php echo esc_html($points_text); ?></td>
                                <?php endforeach; ?>
                                <td aria-label="Баллы" class="has-text-align-center results_scores_all" data-align="center"><?php echo esc_html($row['total']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </figure>
        </div>
        <?php

        return ob_get_clean();
    }
}
