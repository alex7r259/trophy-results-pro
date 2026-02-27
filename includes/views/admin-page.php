<?php
if (!defined('ABSPATH')) {
    exit;
}

$tabs = [
    'results' => __('Results', 'trp'),
    'seasons' => __('Seasons', 'trp'),
    'events' => __('Events', 'trp'),
    'participants' => __('Participants', 'trp'),
    'categories' => __('Categories', 'trp'),
    'points' => __('Points table', 'trp'),
    'settings' => __('Season settings', 'trp'),
];

$delete_url = static function ($entity, $id) {
    return wp_nonce_url(
        admin_url('admin-post.php?action=trp_delete_row&entity=' . rawurlencode($entity) . '&id=' . absint($id)),
        'trp_delete_row'
    );
};
?>
<div class="wrap trp-admin">
    <h1><?php esc_html_e('Trophy Management', 'trp'); ?></h1>

    <?php if (isset($_GET['saved'])) : ?>
        <div class="notice notice-success"><p><?php esc_html_e('Saved successfully.', 'trp'); ?></p></div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])) : ?>
        <div class="notice notice-success"><p><?php esc_html_e('Deleted successfully.', 'trp'); ?></p></div>
    <?php endif; ?>

    <h2 class="nav-tab-wrapper">
        <?php foreach ($tabs as $key => $label) : ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=trp-dashboard&tab=' . $key)); ?>" class="nav-tab <?php echo $tab === $key ? 'nav-tab-active' : ''; ?>"><?php echo esc_html($label); ?></a>
        <?php endforeach; ?>
    </h2>

    <?php if ($tab === 'seasons') : ?>
        <h2><?php esc_html_e('Add season', 'trp'); ?></h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="trp_save_season">
            <?php wp_nonce_field('trp_save_season'); ?>
            <table class="form-table" role="presentation">
                <tr><th><label for="season_name"><?php esc_html_e('Name', 'trp'); ?></label></th><td><input type="text" id="season_name" name="name" required></td></tr>
                <tr><th><label for="season_year"><?php esc_html_e('Year', 'trp'); ?></label></th><td><input type="number" id="season_year" name="season_year" min="2000" required></td></tr>
                <tr><th><label for="season_status"><?php esc_html_e('Status', 'trp'); ?></label></th><td><select id="season_status" name="status"><option value="draft">Draft</option><option value="active">Active</option><option value="completed">Completed</option></select></td></tr>
            </table>
            <p><button class="button button-primary" type="submit"><?php esc_html_e('Save season', 'trp'); ?></button></p>
        </form>

        <h2><?php esc_html_e('Seasons list', 'trp'); ?></h2>
        <table class="widefat striped"><thead><tr><th>ID</th><th><?php esc_html_e('Name', 'trp'); ?></th><th><?php esc_html_e('Year', 'trp'); ?></th><th><?php esc_html_e('Status', 'trp'); ?></th><th><?php esc_html_e('Actions', 'trp'); ?></th></tr></thead><tbody>
        <?php foreach ($seasons as $season) : ?><tr><td><?php echo esc_html($season->id); ?></td><td><?php echo esc_html($season->name); ?></td><td><?php echo esc_html($season->season_year); ?></td><td><?php echo esc_html($season->status); ?></td><td><a class="button button-small button-link-delete" href="<?php echo esc_url($delete_url('seasons', $season->id)); ?>" onclick="return confirm('Delete season?');"><?php esc_html_e('Delete', 'trp'); ?></a></td></tr><?php endforeach; ?>
        </tbody></table>

    <?php elseif ($tab === 'events') : ?>
        <h2><?php esc_html_e('Add event', 'trp'); ?></h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="trp_save_event">
            <?php wp_nonce_field('trp_save_event'); ?>
            <table class="form-table" role="presentation">
                <tr><th><label for="event_season"><?php esc_html_e('Season', 'trp'); ?></label></th><td><select id="event_season" name="season_id" required><?php foreach ($seasons as $season) : ?><option value="<?php echo esc_attr($season->id); ?>"><?php echo esc_html($season->name); ?></option><?php endforeach; ?></select></td></tr>
                <tr><th><label for="event_name"><?php esc_html_e('Name', 'trp'); ?></label></th><td><input type="text" id="event_name" name="name" required></td></tr>
                <tr><th><label for="event_stage"><?php esc_html_e('Stage number', 'trp'); ?></label></th><td><input type="number" min="1" id="event_stage" name="stage_number" required></td></tr>
                <tr><th><label for="event_start"><?php esc_html_e('Start date', 'trp'); ?></label></th><td><input type="date" id="event_start" name="date_start"></td></tr>
                <tr><th><label for="event_end"><?php esc_html_e('End date', 'trp'); ?></label></th><td><input type="date" id="event_end" name="date_end"></td></tr>
                <tr><th><label for="event_status"><?php esc_html_e('Status', 'trp'); ?></label></th><td><select id="event_status" name="status"><option value="draft">Draft</option><option value="published">Published</option><option value="closed">Closed</option></select></td></tr>
            </table>
            <p><button class="button button-primary" type="submit"><?php esc_html_e('Save event', 'trp'); ?></button></p>
        </form>

        <h2><?php esc_html_e('Events list', 'trp'); ?></h2>
        <table class="widefat striped"><thead><tr><th>ID</th><th><?php esc_html_e('Season ID', 'trp'); ?></th><th><?php esc_html_e('Name', 'trp'); ?></th><th><?php esc_html_e('Stage', 'trp'); ?></th><th><?php esc_html_e('Status', 'trp'); ?></th><th><?php esc_html_e('Actions', 'trp'); ?></th></tr></thead><tbody>
        <?php foreach ($events as $event) : ?><tr><td><?php echo esc_html($event->id); ?></td><td><?php echo esc_html($event->season_id); ?></td><td><?php echo esc_html($event->name); ?></td><td><?php echo esc_html($event->stage_number); ?></td><td><?php echo esc_html($event->status); ?></td><td><a class="button button-small button-link-delete" href="<?php echo esc_url($delete_url('events', $event->id)); ?>" onclick="return confirm('Delete event?');"><?php esc_html_e('Delete', 'trp'); ?></a></td></tr><?php endforeach; ?>
        </tbody></table>

    <?php elseif ($tab === 'participants') : ?>
        <h2><?php esc_html_e('Add participant', 'trp'); ?></h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="trp_save_participant">
            <?php wp_nonce_field('trp_save_participant'); ?>
            <table class="form-table" role="presentation">
                <tr><th><label for="participant_last"><?php esc_html_e('Last name', 'trp'); ?></label></th><td><input type="text" id="participant_last" name="last_name" required></td></tr>
                <tr><th><label for="participant_first"><?php esc_html_e('First name', 'trp'); ?></label></th><td><input type="text" id="participant_first" name="first_name" required></td></tr>
                <tr><th><label for="participant_city"><?php esc_html_e('City', 'trp'); ?></label></th><td><input type="text" id="participant_city" name="city"></td></tr>
                <tr><th><label for="participant_license"><?php esc_html_e('License', 'trp'); ?></label></th><td><input type="text" id="participant_license" name="license_number"></td></tr>
            </table>
            <p><button class="button button-primary" type="submit"><?php esc_html_e('Save participant', 'trp'); ?></button></p>
        </form>

        <h2><?php esc_html_e('Participants list', 'trp'); ?></h2>
        <table class="widefat striped"><thead><tr><th>ID</th><th><?php esc_html_e('Last name', 'trp'); ?></th><th><?php esc_html_e('First name', 'trp'); ?></th><th><?php esc_html_e('City', 'trp'); ?></th><th><?php esc_html_e('License', 'trp'); ?></th><th><?php esc_html_e('Actions', 'trp'); ?></th></tr></thead><tbody>
        <?php foreach ($participants as $participant) : ?><tr><td><?php echo esc_html($participant->id); ?></td><td><?php echo esc_html($participant->last_name); ?></td><td><?php echo esc_html($participant->first_name); ?></td><td><?php echo esc_html($participant->city); ?></td><td><?php echo esc_html($participant->license_number); ?></td><td><a class="button button-small button-link-delete" href="<?php echo esc_url($delete_url('participants', $participant->id)); ?>" onclick="return confirm('Delete participant?');"><?php esc_html_e('Delete', 'trp'); ?></a></td></tr><?php endforeach; ?>
        </tbody></table>

    <?php elseif ($tab === 'categories') : ?>
        <h2><?php esc_html_e('Add category', 'trp'); ?></h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="trp_save_category">
            <?php wp_nonce_field('trp_save_category'); ?>
            <table class="form-table" role="presentation">
                <tr><th><label for="category_season"><?php esc_html_e('Season', 'trp'); ?></label></th><td><select id="category_season" name="season_id" required><?php foreach ($seasons as $season) : ?><option value="<?php echo esc_attr($season->id); ?>"><?php echo esc_html($season->name); ?></option><?php endforeach; ?></select></td></tr>
                <tr><th><label for="category_name"><?php esc_html_e('Category name', 'trp'); ?></label></th><td><input type="text" id="category_name" name="name" required></td></tr>
            </table>
            <p><button class="button button-primary" type="submit"><?php esc_html_e('Save category', 'trp'); ?></button></p>
        </form>

        <h2><?php esc_html_e('Categories list', 'trp'); ?></h2>
        <table class="widefat striped"><thead><tr><th>ID</th><th><?php esc_html_e('Season ID', 'trp'); ?></th><th><?php esc_html_e('Name', 'trp'); ?></th><th><?php esc_html_e('Slug', 'trp'); ?></th><th><?php esc_html_e('Actions', 'trp'); ?></th></tr></thead><tbody>
        <?php foreach ($categories as $category) : ?><tr><td><?php echo esc_html($category->id); ?></td><td><?php echo esc_html($category->season_id); ?></td><td><?php echo esc_html($category->name); ?></td><td><?php echo esc_html($category->slug); ?></td><td><a class="button button-small button-link-delete" href="<?php echo esc_url($delete_url('categories', $category->id)); ?>" onclick="return confirm('Delete category?');"><?php esc_html_e('Delete', 'trp'); ?></a></td></tr><?php endforeach; ?>
        </tbody></table>

    <?php elseif ($tab === 'points') : ?>
        <h2><?php esc_html_e('Add or update points', 'trp'); ?></h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="trp_save_points">
            <?php wp_nonce_field('trp_save_points'); ?>
            <table class="form-table" role="presentation">
                <tr><th><label for="points_season"><?php esc_html_e('Season', 'trp'); ?></label></th><td><select id="points_season" name="season_id" required><?php foreach ($seasons as $season) : ?><option value="<?php echo esc_attr($season->id); ?>"><?php echo esc_html($season->name); ?></option><?php endforeach; ?></select></td></tr>
                <tr><th><label for="place_number"><?php esc_html_e('Place', 'trp'); ?></label></th><td><input type="number" id="place_number" name="place_number" min="1" required></td></tr>
                <tr><th><label for="points"><?php esc_html_e('Points', 'trp'); ?></label></th><td><input type="number" id="points" name="points" required></td></tr>
            </table>
            <p><button class="button button-primary" type="submit"><?php esc_html_e('Save points', 'trp'); ?></button></p>
        </form>

        <h2><?php esc_html_e('Points table', 'trp'); ?></h2>
        <table class="widefat striped"><thead><tr><th>ID</th><th><?php esc_html_e('Season ID', 'trp'); ?></th><th><?php esc_html_e('Place', 'trp'); ?></th><th><?php esc_html_e('Points', 'trp'); ?></th><th><?php esc_html_e('Actions', 'trp'); ?></th></tr></thead><tbody>
        <?php foreach ($points_rows as $point) : ?><tr><td><?php echo esc_html($point->id); ?></td><td><?php echo esc_html($point->season_id); ?></td><td><?php echo esc_html($point->place_number); ?></td><td><?php echo esc_html($point->points); ?></td><td><a class="button button-small button-link-delete" href="<?php echo esc_url($delete_url('points', $point->id)); ?>" onclick="return confirm('Delete points row?');"><?php esc_html_e('Delete', 'trp'); ?></a></td></tr><?php endforeach; ?>
        </tbody></table>

    <?php elseif ($tab === 'settings') : ?>
        <h2><?php esc_html_e('Season scoring settings', 'trp'); ?></h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="trp_save_season_settings">
            <?php wp_nonce_field('trp_save_season_settings'); ?>
            <table class="form-table" role="presentation">
                <tr><th><label for="settings_season"><?php esc_html_e('Season', 'trp'); ?></label></th><td><select id="settings_season" name="season_id" required><?php foreach ($seasons as $season) : ?><option value="<?php echo esc_attr($season->id); ?>"><?php echo esc_html($season->name); ?></option><?php endforeach; ?></select></td></tr>
                <tr><th><label for="scoring_type"><?php esc_html_e('Scoring type', 'trp'); ?></label></th><td><select id="scoring_type" name="scoring_type"><option value="all">All events</option><option value="best_n">Best N events</option></select></td></tr>
                <tr><th><label for="best_events_count"><?php esc_html_e('Best events count (for best_n)', 'trp'); ?></label></th><td><input type="number" id="best_events_count" name="best_events_count" min="0" value="0"></td></tr>
            </table>
            <p><button class="button button-primary" type="submit"><?php esc_html_e('Save settings', 'trp'); ?></button></p>
        </form>

        <h2><?php esc_html_e('Current season settings', 'trp'); ?></h2>
        <table class="widefat striped"><thead><tr><th><?php esc_html_e('Season ID', 'trp'); ?></th><th><?php esc_html_e('Scoring type', 'trp'); ?></th><th><?php esc_html_e('Best events count', 'trp'); ?></th><th><?php esc_html_e('Actions', 'trp'); ?></th></tr></thead><tbody>
        <?php foreach ($season_settings as $setting) : ?><tr><td><?php echo esc_html($setting->season_id); ?></td><td><?php echo esc_html($setting->scoring_type); ?></td><td><?php echo esc_html($setting->best_events_count); ?></td><td><a class="button button-small button-link-delete" href="<?php echo esc_url($delete_url('settings', $setting->season_id)); ?>" onclick="return confirm('Delete settings row?');"><?php esc_html_e('Delete', 'trp'); ?></a></td></tr><?php endforeach; ?>
        </tbody></table>

    <?php else : ?>
        <h2><?php esc_html_e('Add result', 'trp'); ?></h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="trp_save_result">
            <?php wp_nonce_field('trp_save_result'); ?>
            <table class="form-table" role="presentation">
                <tr><th><label for="season_id"><?php esc_html_e('Season', 'trp'); ?></label></th><td><select name="season_id" id="season_id" required><?php foreach ($seasons as $season) : ?><option value="<?php echo esc_attr($season->id); ?>"><?php echo esc_html($season->name); ?></option><?php endforeach; ?></select></td></tr>
                <tr><th><label for="event_id"><?php esc_html_e('Event', 'trp'); ?></label></th><td><select name="event_id" id="event_id" required><?php foreach ($events as $event) : ?><option value="<?php echo esc_attr($event->id); ?>"><?php echo esc_html($event->name); ?></option><?php endforeach; ?></select></td></tr>
                <tr><th><label for="category_id"><?php esc_html_e('Category', 'trp'); ?></label></th><td><select name="category_id" id="category_id" required><?php foreach ($categories as $category) : ?><option value="<?php echo esc_attr($category->id); ?>"><?php echo esc_html($category->name); ?></option><?php endforeach; ?></select></td></tr>
                <tr><th><label for="pilot_id"><?php esc_html_e('Pilot', 'trp'); ?></label></th><td><select name="pilot_id" id="pilot_id" required><?php foreach ($participants as $participant) : ?><option value="<?php echo esc_attr($participant->id); ?>"><?php echo esc_html($participant->last_name . ' ' . $participant->first_name); ?></option><?php endforeach; ?></select></td></tr>
                <tr><th><label for="co_driver_id"><?php esc_html_e('Co-driver', 'trp'); ?></label></th><td><select name="co_driver_id" id="co_driver_id"><option value="0">—</option><?php foreach ($participants as $participant) : ?><option value="<?php echo esc_attr($participant->id); ?>"><?php echo esc_html($participant->last_name . ' ' . $participant->first_name); ?></option><?php endforeach; ?></select></td></tr>
                <tr><th><label for="place_number"><?php esc_html_e('Place', 'trp'); ?></label></th><td><input type="number" min="1" name="place_number" id="place_number" required></td></tr>
                <tr><th><label for="status"><?php esc_html_e('Status', 'trp'); ?></label></th><td><select name="status" id="status"><option value="finish">Finish</option><option value="dnf">DNF</option><option value="dsq">DSQ</option><option value="dns">DNS</option></select></td></tr>
                <tr><th><label for="time_seconds"><?php esc_html_e('Time (sec)', 'trp'); ?></label></th><td><input type="number" min="0" name="time_seconds" id="time_seconds"></td></tr>
                <tr><th><label for="penalty_seconds"><?php esc_html_e('Penalty (sec)', 'trp'); ?></label></th><td><input type="number" min="0" name="penalty_seconds" id="penalty_seconds" value="0"></td></tr>
                <tr><th><label for="notes"><?php esc_html_e('Notes', 'trp'); ?></label></th><td><textarea name="notes" id="notes" rows="3" cols="40"></textarea></td></tr>
            </table>
            <p><button class="button button-primary" type="submit"><?php esc_html_e('Save result', 'trp'); ?></button></p>
        </form>

        <h2><?php esc_html_e('Recent results', 'trp'); ?></h2>
        <table class="widefat striped">
            <thead><tr><th>ID</th><th>Season</th><th>Event</th><th>Category</th><th>Pilot</th><th>Co-driver</th><th>Place</th><th>Points</th><th>Status</th><th><?php esc_html_e('Actions', 'trp'); ?></th></tr></thead>
            <tbody>
            <?php foreach ($results as $row) : ?>
                <tr>
                    <td><?php echo esc_html($row->id); ?></td>
                    <td><?php echo esc_html($row->season_id); ?></td>
                    <td><?php echo esc_html($row->event_id); ?></td>
                    <td><?php echo esc_html($row->category_id); ?></td>
                    <td><?php echo esc_html($row->pilot_id); ?></td>
                    <td><?php echo esc_html($row->co_driver_id); ?></td>
                    <td><?php echo esc_html($row->place_number); ?></td>
                    <td><?php echo esc_html($row->points); ?></td>
                    <td>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:flex;gap:6px;align-items:center;">
                            <input type="hidden" name="action" value="trp_update_result_status">
                            <input type="hidden" name="result_id" value="<?php echo esc_attr($row->id); ?>">
                            <?php wp_nonce_field('trp_update_result_status'); ?>
                            <select name="status">
                                <option value="finish" <?php selected($row->status, 'finish'); ?>>Finish</option>
                                <option value="dnf" <?php selected($row->status, 'dnf'); ?>>DNF</option>
                                <option value="dsq" <?php selected($row->status, 'dsq'); ?>>DSQ</option>
                                <option value="dns" <?php selected($row->status, 'dns'); ?>>DNS</option>
                            </select>
                            <button class="button button-small" type="submit"><?php esc_html_e('Update', 'trp'); ?></button>
                        </form>
                    </td>
                    <td><a class="button button-small button-link-delete" href="<?php echo esc_url($delete_url('results', $row->id)); ?>" onclick="return confirm('Delete result row?');"><?php esc_html_e('Delete', 'trp'); ?></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
