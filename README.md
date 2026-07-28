# Directorist - Smart Badges

A WordPress plugin extension for [Directorist](https://directorist.com) that lets you create and manage smart badges for listings with advanced condition-based display rules.

- **Plugin URI:** https://wpxplore.com/tools/directorist-smart-badges/
- **Version:** 3.4.0
- **Author:** wpWax
- **Text Domain:** `directorist-smart-badges`
- **Requires:** WordPress 5.2+, PHP 7.4+, Directorist (active)

> **Note:** This plugin was formerly named **Directorist - Custom Badges** (`directorist-custom-badge`). Version 3.3.0 renamed the plugin, folder, text domain, and all internal identifiers. Badges saved under the old `directorist_custom_badges` option are migrated automatically on first load.

## Features

- **Smart Badge Creation** — unlimited badges with custom labels, icons, colors, image display, and CSS classes
- **Badge Types** — `custom` (label or image badge) and `tags` (renders the listing's tags as badges, with an optional maximum count)
- **Condition-Based Display** — show badges only when rules match:
  - Meta field conditions (12 comparison operators, 6 type casts)
  - Pricing plan conditions (user has an active plan / listing is on a plan)
  - Multiple conditions combined with AND/OR logic
- **Admin UI** — list page with drag & drop reordering, active/inactive toggle, duplicate, and import/export; separate add/edit form page
- **Template Integration** — badges register as Directorist builder widgets for listing cards (grid/list) and the single listing header

## File Structure

```
directorist-smart-badges/
├── directorist-smart-badges.php   # Bootstrap: constants, includes, enqueues, legacy option migration
├── inc/
│   ├── class-admin.php            # Directorist_Smart_Badges_Admin — admin pages, CRUD, AJAX
│   ├── class-badge.php            # Directorist_Smart_Badge — widget registration + frontend rendering
│   ├── class-conditions.php       # Directorist_Smart_Badges_Conditions — condition evaluation engine
│   ├── class-helper.php           # Directorist_Smart_Badges_Helper — sanitization, pricing-plan lookups, utils
│   ├── class-single.php           # Directorist_Smart_Single_Listing_Badge — single listing template override
│   └── functions.php              # Misc filters (field key toggle, badge init on `init`)
├── templates/
│   ├── admin-page.php             # Badge list table
│   ├── admin-form-page.php        # Add/edit badge form
│   ├── condition-item.php         # One condition row (JS template)
│   ├── condition-meta-fields.php  # Meta condition inputs
│   ├── condition-pricing-plan-fields.php
│   └── single/fields/badges.php   # Single listing badges template override
└── assets/
    ├── css/  (main.css, admin.css)
    └── js/   (main.js, admin.js)
```

## Architecture

### Bootstrapping

`directorist-smart-badges.php` defines `DIRECTORIST_SMART_BADGE_VERSION`, `DIRECTORIST_SMART_BADGE_URI`, `DIRECTORIST_SMART_BADGE_DIR`, migrates the legacy option, includes all classes, and only initializes when `directorist/directorist-base.php` is an active plugin.

On `init` (see `inc/functions.php`), `Directorist_Smart_Badge::init_badges_from_options()` instantiates one `Directorist_Smart_Badge` per active saved badge. Each instance:

1. Registers itself as a `badge`-type widget in the Directorist directory builder via the `atbdp_listing_type_settings_field_list` filter (grid + list card templates, all badge placements).
2. Adds a toggle to the single listing header layout via `directorist_listing_header_layout`.
3. Renders on listing cards via the `atbdp_all_listings_badge_template` action — the badge outputs only when `Directorist_Smart_Badges_Conditions::check_conditions()` passes for the current listing.

Single listing pages are handled by `Directorist_Smart_Single_Listing_Badge`, which intercepts the `directorist_template` filter for `single/fields/badges` and renders `templates/single/fields/badges.php`.

### Data Model

Badges are stored as an array of associative arrays in a single autoload-off option: **`directorist_smart_badges`**.

| Key | Type | Notes |
|---|---|---|
| `id` | string | Internal unique ID (`time()-rand`) |
| `badge_type` | string | `custom` (default) or `tags` |
| `display_type` | string | `label` (default) or `image` |
| `badge_title` | string | Admin-facing title |
| `badge_id` | string | Unique slug (lowercase + hyphens); becomes the widget key and element `id` |
| `badge_label` | string | Front-end text |
| `badge_label_font_size` | int | px, default 14 |
| `badge_icon` | string | Directorist icon class (e.g. `las la-check-circle`) |
| `badge_class` | string | Extra CSS class(es) |
| `badge_color` / `badge_text_color` | hex | Background / text color |
| `badge_image_id` / `badge_image_url` / `badge_image_width` | int/url/int | For `display_type: image` |
| `maximum_tags` | int | For `badge_type: tags`; 0 = unlimited |
| `conditions` | array | See below |
| `condition_relation` | string | `AND` (default) or `OR` |
| `is_active` | bool | Toggle without deleting |
| `order` | int | Sort position |

### Conditions

Evaluated by `Directorist_Smart_Badges_Conditions::check_conditions( $badge_data, $listing_id )`. A badge with no conditions always shows. Unknown condition types are skipped; if no condition is processable, the badge is hidden.

**Meta condition** (`type: meta`): `meta_key`, `meta_value`, `compare`, `type_cast`.
- Operators: `=`, `!=`, `>`, `>=`, `<`, `<=`, `LIKE`, `NOT LIKE`, `IN`, `NOT IN`, `EXISTS`, `NOT EXISTS`
- Type casts: `CHAR`, `NUMERIC`, `DECIMAL`, `DATE`, `DATETIME`, `BOOLEAN`
- `EXISTS`/`NOT EXISTS` use `metadata_exists()` so an empty-but-present key is distinguished from an absent key. Array (serialized) meta is supported by `LIKE`/`IN` style operators via `in_array()`.

**Pricing plan condition** (`type: pricing_plan`, requires the Directorist Pricing Plans extension / `ATBDP_Pricing_Plans`):
- `plan_status_condition: user_active_plan` — the listing author has a non-expired, completed `atbdp_orders` order for the plan(s)
- `plan_status_condition: listing_has_plan` (default) — the listing's `_fm_plans` meta matches `plan_id` under `compare` (`=`, `!=`, `IN`, `NOT IN`)

### Admin

`Directorist_Smart_Badges_Admin` registers two submenu pages under the Directorist CPT menu (`edit.php?post_type=at_biz_dir`):

- `directorist-smart-badges` — list page (`templates/admin-page.php`)
- `directorist-smart-badges-form` — add/edit form (`templates/admin-form-page.php`), hidden from the menu with CSS

All mutations run over AJAX. Every endpoint checks the `directorist_smart_badges_nonce` nonce and the `manage_options` capability:

| Action | Method |
|---|---|
| `dsb_get_badge` | `ajax_get_badge()` |
| `dsb_save_badge` | `ajax_save_badge()` |
| `dsb_delete_badge` | `ajax_delete_badge()` |
| `dsb_toggle_badge` | `ajax_toggle_badge()` |
| `dsb_reorder_badges` | `ajax_reorder_badges()` |
| `dsb_duplicate_badge` | `ajax_duplicate_badge()` |
| `dsb_export_badges` | `ajax_export_badges()` |
| `dsb_import_badges` | `ajax_import_badges()` |

The admin UI (v3.4.0+) is a card-based design with a sticky header (always-visible Save button on the form page), CSS-only vertical tabs (General / Appearance / Conditions), toggle switches, and inline SVG icons. It ships exactly two assets — `assets/css/admin.css` and `assets/js/admin.js` — enqueued only on the two plugin screens (`$hook_suffix` check), with the JS loaded deferred. The JS is vanilla (zero jQuery): all endpoints are called with `fetch()` + `FormData`, saves show a toast and a button spinner, and drag-reorder uses native HTML5 drag & drop. The meta-key combobox is a native `<input list>` + `<datalist>` (server-rendered from `get_listing_meta_keys()`), colors use native `<input type="color">` swatches synced to hex text inputs, and images use the core `wp.media` frame. No external requests, no icon fonts, no Google Fonts; styles use CSS custom-property tokens, logical properties (RTL-safe), visible focus states, and respect `prefers-reduced-motion`. Admin JS is localized as `dsbAdmin` (ajaxUrl, nonce, i18n strings).

### Naming Conventions

- Classes: `Directorist_Smart_*`
- Constants: `DIRECTORIST_SMART_BADGE_*`
- Options/nonces/functions: `directorist_smart_badge(s)_*`
- AJAX actions, JS globals, DOM ids/classes in admin: `dsb` prefix
- Frontend badge markup class: `directorist-smart-badge` (alongside Directorist's own `directorist-badge` classes)
- Text domain: `directorist-smart-badges`

## Programmatic Usage

Badges can also be registered in code:

```php
add_action('init', function() {
    new Directorist_Smart_Badge(array(
        'id'         => 'my-badge',
        'label'      => 'Badge',
        'icon'       => 'las la-check-circle',
        'hook'       => 'atbdp-my-badge',
        'title'      => 'My Badge',
        'meta_key'   => '_custom-select',
        'meta_value' => 'Free',
        'class'      => 'my-custom-badge',
    ));
});
```

When `badge_data` is not supplied, the badge falls back to a simple `meta_key == meta_value` check.

## Security

- All inputs sanitized (`sanitize_key`, `sanitize_text_field`, `sanitize_hex_color`, `esc_url_raw`, `absint`) and whitelist-validated (badge/display types, operators, type casts, relations)
- Nonce verification + `manage_options` capability checks on every AJAX endpoint
- Output escaping (`esc_attr`, `esc_html`, `esc_url`) in all templates
- DB access through WordPress APIs with prepared statements

## Development

- Lint: `php -l` every changed file (on Local, PHP is not in PATH — use Local's bundled binary)
- No build step: plain PHP/JS/CSS, edit and reload
- Keep `README.md` (this file), `PRD.md`, and `DOCUMENTATION.md` in sync with code changes

## Changelog

### 3.4.0
- Redesigned admin UI: card layout, Directorist-purple accent, sticky header with always-visible Save, CSS-only vertical tabs (General / Appearance / Conditions), toggle switches, inline SVG icons, RTL-safe logical properties, reduced-motion support
- Admin JS rewritten in vanilla JS (`fetch()` + `FormData`, native HTML5 drag & drop, toasts, save spinner) — jQuery, jQuery UI Sortable, wp-color-picker, and Select2 (incl. its CDN fallback) removed; zero external requests
- Meta-key combobox now a native `<datalist>`; colors use native color inputs synced with hex fields
- Fixed leftover `DCBAdmin` JS global from the 3.3.0 rename (now `DSBAdmin`)
- Admin assets shrunk from ~55KB to ~38KB raw (~9.6KB gzipped), JS loaded with `defer`

### 3.3.0
- Renamed plugin from "Directorist - Custom Badges" to **"Directorist - Smart Badges"** (new folder, main file, text domain `directorist-smart-badges`, `Directorist_Smart_*` classes, `DIRECTORIST_SMART_BADGE_*` constants, `dsb_*` AJAX actions, admin page slugs)
- Automatic one-time migration of badges from the legacy `directorist_custom_badges` option

### 3.2.0
- Badge admin settings updates, tags-as-badge type, meta-search option in condition editor

### 3.0.0
- Complete rewrite with condition-based badge display, new admin interface, import/export, improved security

## License

GPL v2 or later — https://www.gnu.org/licenses/gpl-2.0.html
