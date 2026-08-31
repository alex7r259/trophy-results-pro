<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap trp-admin">
    <h1><?php esc_html_e('Trophy Results', 'trp'); ?></h1>

    <?php if (isset($_GET['saved'])) : ?>
        <div class="notice notice-success"><p><?php esc_html_e('Result saved.', 'trp'); ?></p></div>
    <?php endif; ?>

    <h2><?php esc_html_e('Add result', 'trp'); ?></h2>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="trp_save_result">
        <?php wp_nonce_field('trp_save_result'); ?>
        <table class="form-table" role="presentation">
            <tr>
                <th><label for="season_id"><?php esc_html_e('Season', 'trp'); ?></label></th>
                <td><select name="season_id" id="season_id" required><?php foreach ($seasons as $season) : ?><option value="<?php echo esc_attr($season->id); ?>"><?php echo esc_html($season->name); ?></option><?php endforeach; ?></select></td>
            </tr>
            <tr>
                <th><label for="event_id"><?php esc_html_e('Event', 'trp'); ?></label></th>
                <td><select name="event_id" id="event_id" required><?php foreach ($events as $event) : ?><option value="<?php echo esc_attr($event->id); ?>"><?php echo esc_html($event->name); ?></option><?php endforeach; ?></select></td>
            </tr>
            <tr>
                <th><label for="category_id"><?php esc_html_e('Category', 'trp'); ?></label></th>
                <td><select name="category_id" id="category_id" required><?php foreach ($categories as $category) : ?><option value="<?php echo esc_attr($category->id); ?>"><?php echo esc_html($category->name); ?></option><?php endforeach; ?></select></td>
            </tr>
            <tr>
                <th><label for="pilot_id"><?php esc_html_e('Pilot', 'trp'); ?></label></th>
                <td><select name="pilot_id" id="pilot_id" required><?php foreach ($participants as $participant) : ?><option value="<?php echo esc_attr($participant->id); ?>"><?php echo esc_html($participant->last_name . ' ' . $participant->first_name); ?></option><?php endforeach; ?></select></td>
            </tr>
            <tr>
                <th><label for="co_driver_id"><?php esc_html_e('Co-driver', 'trp'); ?></label></th>
                <td><select name="co_driver_id" id="co_driver_id"><option value="0">—</option><?php foreach ($participants as $participant) : ?><option value="<?php echo esc_attr($participant->id); ?>"><?php echo esc_html($participant->last_name . ' ' . $participant->first_name); ?></option><?php endforeach; ?></select></td>
            </tr>
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
</div>
