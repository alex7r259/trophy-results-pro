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

        if (!$season_id || !$category_id) {
            return '<p>' . esc_html__('Season and category are required.', 'trp') . '</p>';
        }

        $standings = TRP_Scoring::get_standings($season_id, $category_id);
        if (empty($standings)) {
            return '<p>' . esc_html__('No standings available yet.', 'trp') . '</p>';
        }

        global $wpdb;
        $participants_table = TRP_DB::table('participants');

        ob_start();
        ?>
        <table class="trp-standings-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Place', 'trp'); ?></th>
                    <th><?php esc_html_e('Pilot', 'trp'); ?></th>
                    <th><?php esc_html_e('Starts', 'trp'); ?></th>
                    <th><?php esc_html_e('Wins', 'trp'); ?></th>
                    <th><?php esc_html_e('Points', 'trp'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($standings as $index => $row) :
                    $pilot = $wpdb->get_row($wpdb->prepare(
                        "SELECT first_name, last_name FROM {$participants_table} WHERE id = %d",
                        $row['pilot_id']
                    ));
                    $pilot_name = $pilot ? trim($pilot->last_name . ' ' . $pilot->first_name) : ('#' . $row['pilot_id']);
                    ?>
                    <tr>
                        <td><?php echo esc_html($index + 1); ?></td>
                        <td><?php echo esc_html($pilot_name); ?></td>
                        <td><?php echo esc_html($row['starts']); ?></td>
                        <td><?php echo esc_html($row['wins']); ?></td>
                        <td><?php echo esc_html($row['total_points']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php

        return ob_get_clean();
    }
}
