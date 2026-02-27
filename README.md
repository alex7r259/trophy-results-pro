# Trophy Results Pro

WordPress plugin for trophy-racing series management with dedicated database tables and judge-focused admin UI.

## Features

- Dedicated entities in DB tables: seasons, events, categories, participants, points table, season settings, and event results.
- Participants are independent records and **not** WordPress users.
- Pilot-centered standings with per-category separation.
- Scoring modes: `all` events or `best_n`.
- Tie-breakers: wins, second places, last event place.
- Admin tabs for managing seasons, events, participants, categories, points, season settings, and results (including delete actions in each table).
- Judges can edit result status inline in the results table (Finish/DNF/DSQ/DNS) with automatic points recalculation.
- Judges can also edit season status and event status inline from their list tables.
- Only one season can be active at a time.
- Frontend shortcode: `[trophy_standings season="1" category="2"]`.

## Quick start

1. Place plugin in `wp-content/plugins/trophy-results-pro`.
2. Activate plugin in WP admin.
3. Give user role `judge` (or capability `trp_manage_data`).
4. Open **WP Admin → Trophy**.
5. Add data in this order:
   - **Seasons** tab → create season.
   - **Participants** tab → add pilots and co-drivers.
   - **Events** tab → add season events and set stage coefficient (e.g. 1.0 / 1.5).
   - **Categories** tab → add categories for each season.
   - **Points table** tab → define place-to-points mapping (e.g. 1→100, 2→88...).
   - **Season settings** tab → set `all` or `best_n` and `best_events_count`.
   - **Results** tab → enter event results (including start number and car).

## Shortcode output

Standings table includes:

- Place
- Start number
- Pilot/Co-driver surname
- Car
- Every season event is split into two columns: `Place` and `Points` (`base(weighted)` where weighted = base × coefficient).
- Counted stages for `best_n` are highlighted in green
- Total weighted points (sum of counted stages)

If `season` is omitted, shortcode uses current active season.
Table is responsive via horizontal scroll on mobile.

## Notes

- Unique index `(event_id, pilot_id)` prevents duplicate pilot entries in the same event.
- Result points are assigned automatically from the season points table.
- Each admin list has a **Delete** action with nonce protection.
