<?php
if (!defined('ABSPATH')) {
    exit;
}

$tabs = [
    'results' => __('Results', 'trp'),
    'seasons' => __('Seasons', 'trp'),
    'events' => __('Events', 'trp'),
    'participants' => __('Participants', 'trp'),
];
?>
<div class="wrap trp-admin">
    <h1><?php esc_html_e('Trophy Management', 'trp'); ?></h1>

    <?php if (isset($_GET['saved'])) : ?>
        <div class="notice notice-success"><p><?php esc_html_e('Saved successfully.', 'trp'); ?></p></div>
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
        <table class="widefat striped"><thead><tr><th>ID</th><th><?php esc_html_e('Name', 'trp'); ?></th><th><?php esc_html_e('Year', 'trp'); ?></th><th><?php esc_html_e('Status', 'trp'); ?></th></tr></thead><tbody>
        <?php foreach ($seasons as $season) : ?><tr><td><?php echo esc_html($season->id); ?></td><td><?php echo esc_html($season->name); ?></td><td><?php echo esc_html($season->season_year); ?></td><td><?php echo esc_html($season->status); ?></td></tr><?php endforeach; ?>
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
        <table class="widefat striped"><thead><tr><th>ID</th><th><?php esc_html_e('Season ID', 'trp'); ?></th><th><?php esc_html_e('Name', 'trp'); ?></th><th><?php esc_html_e('Stage', 'trp'); ?></th><th><?php esc_html_e('Status', 'trp'); ?></th></tr></thead><tbody>
        <?php foreach ($events as $event) : ?><tr><td><?php echo esc_html($event->id); ?></td><td><?php echo esc_html($event->season_id); ?></td><td><?php echo esc_html($event->name); ?></td><td><?php echo esc_html($event->stage_number); ?></td><td><?php echo esc_html($event->status); ?></td></tr><?php endforeach; ?>
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
        <table class="widefat striped"><thead><tr><th>ID</th><th><?php esc_html_e('Last name', 'trp'); ?></th><th><?php esc_html_e('First name', 'trp'); ?></th><th><?php esc_html_e('City', 'trp'); ?></th><th><?php esc_html_e('License', 'trp'); ?></th></tr></thead><tbody>
        <?php foreach ($participants as $participant) : ?><tr><td><?php echo esc_html($participant->id); ?></td><td><?php echo esc_html($participant->last_name); ?></td><td><?php echo esc_html($participant->first_name); ?></td><td><?php echo esc_html($participant->city); ?></td><td><?php echo esc_html($participant->license_number); ?></td></tr><?php endforeach; ?>
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
            <thead><tr><th>ID</th><th>Season</th><th>Event</th><th>Category</th><th>Pilot</th><th>Co-driver</th><th>Place</th><th>Points</th><th>Status</th></tr></thead>
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
                    <td><?php echo esc_html($row->status); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
