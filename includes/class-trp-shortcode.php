<?php

if (!defined('ABSPATH')) {
    exit;
}

class TRP_Shortcode
{
    public static function init()
    {
        add_shortcode('trophy_standings', [__CLASS__, 'render_standings']);
        add_action('wp_ajax_get_class_results', [__CLASS__, 'ajax_get_class_results']);
        add_action('wp_ajax_nopriv_get_class_results', [__CLASS__, 'ajax_get_class_results']);
    }

    public static function render_standings($atts)
    {
        $atts = shortcode_atts([
            'season' => 0,
            'category' => 0,
        ], $atts);

        global $wpdb;

        $season_id = absint($atts['season']);
        if (!$season_id) {
            $season_id = (int) $wpdb->get_var("SELECT id FROM " . TRP_DB::table('seasons') . " WHERE status = 'active' ORDER BY id DESC LIMIT 1");
        }

        if (!$season_id) {
            return '<p>' . esc_html__('No active season found.', 'trp') . '</p>';
        }

        $categories = $wpdb->get_results($wpdb->prepare(
            "SELECT id, name FROM " . TRP_DB::table('categories') . " WHERE season_id = %d ORDER BY name ASC",
            $season_id
        ));

        if (empty($categories)) {
            return '<p>' . esc_html__('No categories found for active season.', 'trp') . '</p>';
        }

        $category_id = absint($atts['category']);
        if (!$category_id) {
            $category_id = (int) $categories[0]->id;
        }

        $seasons = $wpdb->get_results("SELECT id, name, season_year FROM " . TRP_DB::table('seasons') . " ORDER BY season_year ASC, id ASC");

        wp_enqueue_style('trp-results-style', TRP_PLUGIN_URL . 'assets/results_style.css', [], TRP_PLUGIN_VERSION);

        $nonce = wp_create_nonce('trp_results_ajax_nonce');
        $ajax_url = admin_url('admin-ajax.php');
        $container_id = 'trp-results-' . wp_generate_uuid4();

        ob_start();
        ?>
        <div id="<?php echo esc_attr($container_id); ?>" class="results-container" data-season-id="<?php echo esc_attr($season_id); ?>" data-category-id="<?php echo esc_attr($category_id); ?>" data-nonce="<?php echo esc_attr($nonce); ?>">
            <div class="results-header">
                <div class="season-filters">
                    <?php foreach ($seasons as $season) :
                        $cls = ((int) $season->id === $season_id) ? 'season-filter active' : 'season-filter';
                        ?>
                        <a href="#" data-season="<?php echo esc_attr($season->id); ?>" class="<?php echo esc_attr($cls); ?>"><?php echo esc_html($season->season_year ?: $season->name); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="results-header">
                <div class="class-filters-top3">
                    <?php foreach ($categories as $cat) :
                        $cls = ((int) $cat->id === $category_id) ? 'class-filter-top3 active' : 'class-filter-top3';
                        ?>
                        <a href="#" data-class-id="<?php echo esc_attr($cat->id); ?>" class="<?php echo esc_attr($cls); ?>"><?php echo esc_html($cat->name); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div id="results-table-content">
                <?php echo self::render_results_table($season_id, $category_id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </div>
        <script>
        (function(){
            const root = document.getElementById(<?php echo wp_json_encode($container_id); ?>);
            if (!root) { return; }
            const ajaxUrl = <?php echo wp_json_encode($ajax_url); ?>;
            const nonce = <?php echo wp_json_encode($nonce); ?>;
            const tableContent = root.querySelector('#results-table-content');

            function bindHandlers(){
                root.querySelectorAll('.season-filter').forEach(el => {
                    el.addEventListener('click', function(e){
                        e.preventDefault();
                        root.querySelectorAll('.season-filter').forEach(x=>x.classList.remove('active'));
                        this.classList.add('active');
                        const seasonId = this.dataset.season;
                        loadCategoriesAndTable(seasonId);
                    });
                });

                root.querySelectorAll('.class-filter-top3').forEach(el => {
                    el.addEventListener('click', function(e){
                        e.preventDefault();
                        root.querySelectorAll('.class-filter-top3').forEach(x=>x.classList.remove('active'));
                        this.classList.add('active');
                        const seasonId = root.dataset.seasonId;
                        const categoryId = this.dataset.classId;
                        loadTable(seasonId, categoryId);
                    });
                });
            }

            function postData(payload){
                const fd = new FormData();
                Object.keys(payload).forEach(k=>fd.append(k,payload[k]));
                return fetch(ajaxUrl,{method:'POST',body:fd,credentials:'same-origin'}).then(r=>r.text());
            }

            function loadCategoriesAndTable(seasonId){
                postData({action:'get_class_results', mode:'categories', season_id:seasonId, security:nonce}).then(html=>{
                    root.querySelector('.class-filters-top3').innerHTML = html;
                    root.dataset.seasonId = seasonId;
                    const first = root.querySelector('.class-filter-top3');
                    const categoryId = first ? first.dataset.classId : 0;
                    if (first) first.classList.add('active');
                    if (categoryId) loadTable(seasonId, categoryId);
                    bindHandlers();
                });
            }

            function loadTable(seasonId, categoryId){
                root.dataset.seasonId = seasonId;
                root.dataset.categoryId = categoryId;
                postData({action:'get_class_results', mode:'table', season_id:seasonId, category_id:categoryId, security:nonce}).then(html=>{
                    tableContent.innerHTML = html;
                });
            }

            bindHandlers();
        })();
        </script>
        <?php

        return ob_get_clean();
    }

    public static function ajax_get_class_results()
    {
        // Read-only endpoint: do not hard-fail by nonce to avoid cached-page mismatches.
        $mode = isset($_POST['mode']) ? sanitize_key(wp_unslash($_POST['mode'])) : 'table';
        $season_id = isset($_POST['season_id']) ? absint($_POST['season_id']) : 0;

        if ($mode === 'categories') {
            echo self::render_category_filters($season_id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            wp_die();
        }

        $category_id = isset($_POST['category_id']) ? absint($_POST['category_id']) : 0;
        echo self::render_results_table($season_id, $category_id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        wp_die();
    }

    private static function render_category_filters($season_id)
    {
        global $wpdb;
        $categories = $wpdb->get_results($wpdb->prepare(
            "SELECT id, name FROM " . TRP_DB::table('categories') . " WHERE season_id = %d ORDER BY name ASC",
            $season_id
        ));

        $html = '';
        foreach ($categories as $i => $cat) {
            $cls = $i === 0 ? 'class-filter-top3 active' : 'class-filter-top3';
            $html .= '<a href="#" data-class-id="' . esc_attr($cat->id) . '" class="' . esc_attr($cls) . '">' . esc_html($cat->name) . '</a>';
        }

        return $html;
    }

    private static function render_results_table($season_id, $category_id)
    {
        global $wpdb;

        $events = $wpdb->get_results($wpdb->prepare(
            "SELECT id, stage_number, coefficient FROM " . TRP_DB::table('events') . " WHERE season_id = %d ORDER BY stage_number ASC",
            $season_id
        ));

        if (empty($events) || !$category_id) {
            return '<p class="error">Нет данных для отображения</p>';
        }

        $event_coefficients = [];
        foreach ($events as $event) {
            $event_coefficients[(int) $event->id] = (float) ($event->coefficient ?: 1);
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
            return '<p class="error">Нет результатов для выбранной категории</p>';
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
            $base = (int) $res->base_points_ref;
            $weighted = (int) round($base * ($event_coefficients[$event_id] ?? 1));
            $rows[$pilot_id]['results_by_event'][$event_id] = [
                'place' => (int) $res->place_number,
                'base' => $base,
                'weighted' => $weighted,
            ];
            if ($res->status === 'finish') {
                $rows[$pilot_id]['finish_results'][] = ['event_id' => $event_id, 'points' => $weighted];
            }
        }

        foreach ($rows as &$row) {
            $counted = $row['finish_results'];
            usort($counted, static fn($a,$b) => $b['points'] <=> $a['points']);
            if ($settings && $settings->scoring_type === 'best_n' && (int) $settings->best_events_count > 0) {
                $counted = array_slice($counted, 0, (int) $settings->best_events_count);
            }
            $row['counted_event_ids'] = array_map(static fn($e)=>(int)$e['event_id'],$counted);
            $row['total'] = array_sum(array_map(static fn($e)=>(int)$e['points'],$counted));
        }
        unset($row);

        usort($rows, static fn($a,$b) => $b['total'] <=> $a['total']);

        ob_start();
        ?>
        <div class="table-responsive">
            <figure class="wp-block-table is-style-stripes">
                <table class="delivery results-table responsive-mode">
                    <thead>
                        <tr>
                            <th rowspan="2" class="has-text-align-center" data-align="center">Место</th>
                            <th rowspan="2" class="has-text-align-center" data-align="center">Стартовый<br>номер</th>
                            <th rowspan="2" class="has-text-align-center" data-align="center">Фамилия Имя Пилот/Штурман</th>
                            <th rowspan="2" class="has-text-align-center" data-align="center">Автомобиль</th>
                            <?php foreach ($events as $event) : ?>
                                <th colspan="2" class="has-text-align-center" data-align="center"><?php echo esc_html($event->stage_number . ' этап'); ?></th>
                            <?php endforeach; ?>
                            <th rowspan="2" class="has-text-align-center" data-align="center">Баллы<br>Итог</th>
                        </tr>
                        <tr>
                            <?php foreach ($events as $event) :
                                $coef = rtrim(rtrim((string) ((float) $event->coefficient), '0'), '.'); ?>
                                <th class="has-text-align-center" data-align="center">Место</th>
                                <th class="has-text-align-center" data-align="center"><?php echo esc_html('Баллы (x' . $coef . ')'); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $rank => $row) : ?>
                        <tr>
                            <td class="has-text-align-center results_place" data-align="center"><?php echo esc_html($row['total'] > 0 ? $rank + 1 : '-'); ?></td>
                            <td class="has-text-align-center" data-align="center"><?php echo esc_html($row['start_number']); ?></td>
                            <td class="has-text-align-center" data-align="center"><?php echo esc_html($row['pilot_last_name'] . ', ' . $row['codriver_last_name']); ?></td>
                            <td class="has-text-align-center scores-border" data-align="center"><?php echo esc_html($row['car_name']); ?></td>
                            <?php foreach ($events as $event) :
                                $event_id = (int) $event->id;
                                $r = $row['results_by_event'][$event_id] ?? null;
                                $counted_cls = in_array($event_id, $row['counted_event_ids'], true) ? ' trp-counted-stage' : '';
                                ?>
                                <td class="has-text-align-center results_scores<?php echo esc_attr($counted_cls); ?>" data-align="center"><?php echo esc_html($r ? $r['place'] : '-'); ?></td>
                                <td class="has-text-align-center results_scores scores-border<?php echo esc_attr($counted_cls); ?>" data-align="center"><?php echo esc_html($r ? ($r['base'] . ' (' . $r['weighted'] . ')') : '0 (0)'); ?></td>
                            <?php endforeach; ?>
                            <td class="has-text-align-center results_scores_all" data-align="center"><?php echo esc_html($row['total']); ?></td>
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
