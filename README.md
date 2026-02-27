# Trophy Results Pro

WordPress plugin for trophy-racing series management with dedicated database tables and judge-focused admin UI.

## Features

- Dedicated entities in DB tables: seasons, events, categories, participants, points table, season settings, and event results.
- Pilot-centered standings with per-category separation.
- Scoring modes: `all` events or `best_n`.
- Tie-breakers: wins, second places, last event place.
- Admin page to submit results with automatic points lookup.
- Frontend shortcode: `[trophy_standings season="1" category="2"]`.

## Installation

1. Place plugin in `wp-content/plugins/trophy-results-pro`.
2. Activate plugin in WP admin.
3. Ensure user has role `judge` or capability `trp_manage_data`.
4. Fill base tables (`trp_seasons`, `trp_events`, `trp_categories`, `trp_participants`, `trp_points`, `trp_season_settings`).
5. Enter event results from **Trophy** admin menu.

## Notes

- Participants are independent records and **not** WordPress users.
- Unique index `(event_id, pilot_id)` prevents duplicate pilot entries in the same event.
