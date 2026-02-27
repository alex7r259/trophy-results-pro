<?php

if (!defined('ABSPATH')) {
    exit;
}

class TRP_Scoring
{
    public static function get_points_for_place($season_id, $place_number)
    {
        global $wpdb;

        $points_table = TRP_DB::table('points');
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT points FROM {$points_table} WHERE season_id = %d AND place_number = %d",
            $season_id,
            $place_number
        ));

        return $value !== null ? (int) $value : 0;
    }

    public static function get_standings($season_id, $category_id)
    {
        global $wpdb;

        $results_table = TRP_DB::table('results');
        $settings_table = TRP_DB::table('season_settings');

        $settings = $wpdb->get_row($wpdb->prepare(
            "SELECT scoring_type, best_events_count FROM {$settings_table} WHERE season_id = %d",
            $season_id
        ));

        $events_table = TRP_DB::table('events');
        $points_table = TRP_DB::table('points');
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT r.pilot_id, r.place_number, COALESCE(pt.points,0) AS base_points, r.event_id, e.coefficient
             FROM {$results_table} r
             LEFT JOIN {$events_table} e ON e.id = r.event_id
             LEFT JOIN {$points_table} pt ON pt.season_id = r.season_id AND pt.place_number = r.place_number
             WHERE r.season_id = %d
               AND r.category_id = %d
               AND r.status = 'finish'",
            $season_id,
            $category_id
        ));

        $by_pilot = [];
        foreach ($rows as $row) {
            $pilot_id = (int) $row->pilot_id;
            if (!isset($by_pilot[$pilot_id])) {
                $by_pilot[$pilot_id] = [];
            }

            $by_pilot[$pilot_id][] = [
                'points' => (int) round(((int) $row->base_points) * (float) ($row->coefficient ?: 1)),
                'place'  => (int) $row->place_number,
                'event'  => (int) $row->event_id,
            ];
        }

        $standings = [];
        foreach ($by_pilot as $pilot_id => $results) {
            usort($results, static function ($a, $b) {
                return $b['points'] <=> $a['points'];
            });

            $counted = $results;
            if ($settings && $settings->scoring_type === 'best_n' && (int) $settings->best_events_count > 0) {
                $counted = array_slice($results, 0, (int) $settings->best_events_count);
            }

            $wins = 0;
            $seconds = 0;
            $total_points = 0;

            foreach ($counted as $res) {
                $total_points += $res['points'];
                if ($res['place'] === 1) {
                    $wins++;
                }
                if ($res['place'] === 2) {
                    $seconds++;
                }
            }

            $last_event_place = null;
            if (!empty($results)) {
                usort($results, static function ($a, $b) {
                    return $b['event'] <=> $a['event'];
                });
                $last_event_place = $results[0]['place'];
            }

            $standings[] = [
                'pilot_id'         => $pilot_id,
                'starts'           => count($results),
                'wins'             => $wins,
                'seconds'          => $seconds,
                'total_points'     => $total_points,
                'last_event_place' => $last_event_place,
            ];
        }

        usort($standings, static function ($a, $b) {
            if ($a['total_points'] !== $b['total_points']) {
                return $b['total_points'] <=> $a['total_points'];
            }
            if ($a['wins'] !== $b['wins']) {
                return $b['wins'] <=> $a['wins'];
            }
            if ($a['seconds'] !== $b['seconds']) {
                return $b['seconds'] <=> $a['seconds'];
            }

            return ($a['last_event_place'] ?? PHP_INT_MAX) <=> ($b['last_event_place'] ?? PHP_INT_MAX);
        });

        return $standings;
    }
}
