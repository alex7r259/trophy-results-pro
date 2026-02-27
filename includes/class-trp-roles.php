<?php

if (!defined('ABSPATH')) {
    exit;
}

class TRP_Roles
{
    public static function add_roles()
    {
        add_role('judge', 'Judge', [
            'read'              => true,
            'trp_manage_data'   => true,
        ]);

        $admin = get_role('administrator');
        if ($admin && !$admin->has_cap('trp_manage_data')) {
            $admin->add_cap('trp_manage_data');
        }
    }

    public static function remove_roles()
    {
        remove_role('judge');
    }
}
