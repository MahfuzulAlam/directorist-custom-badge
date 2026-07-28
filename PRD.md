# PRD — Directorist - Smart Badges

- **Product:** Directorist - Smart Badges (WordPress plugin, Directorist extension)
- **Slug / Text Domain:** `directorist-smart-badges`
- **Plugin URI:** https://wpxplore.com/tools/directorist-smart-badges/
- **Author:** wpWax
- **Current Version:** 3.4.0
- **Status:** Shipped; this PRD documents the current product and its requirements

## 1. Overview

Directory owners running Directorist need a way to visually highlight listings — "Verified", "Top Rated", "Premium", "Open Now" — without writing code. Directorist ships only a few fixed badges (Featured, New, Popular). Smart Badges adds unlimited, admin-managed badges whose visibility is driven by **rules** ("smart" conditions) evaluated per listing, so badges appear automatically on listings that qualify.

## 2. Goals

1. Let a non-developer admin create, style, and manage unlimited listing badges from the WordPress dashboard.
2. Display badges automatically based on listing data (meta fields) and monetization state (pricing plans) — no manual per-listing tagging.
3. Integrate natively with the Directorist directory builder so badges are placeable like any built-in widget (card grid/list views, single listing header).
4. Keep configurations portable (import/export) and safe (validation, capability checks, nonces).

### Non-Goals

- Badges for non-Directorist post types.
- Per-listing manual badge assignment UI (rules only; the builder toggle controls placement, conditions control visibility).
- Scheduled/time-limited badges (possible future enhancement).

## 3. Users

- **Directory owner / site admin** (primary): creates and manages badges; needs `manage_options`.
- **Site visitor** (secondary): sees badges on listing cards and single listing pages; needs clear, fast-rendering visuals.
- **Developer** (tertiary): registers badges programmatically via `new Directorist_Smart_Badge( $atts )`.

## 4. Functional Requirements

### 4.1 Badge Management (Admin)

Located at **Directory Listings → Smart Badges** (`edit.php?post_type=at_biz_dir`, page slug `directorist-smart-badges`). Requires `manage_options`.

- **FR-1 Create/Edit** badges on a dedicated form page (slug `directorist-smart-badges-form`, hidden from menu) with fields:
  - Badge Title (admin-facing, required)
  - Badge ID (required, unique, lowercase letters/numbers/hyphens only)
  - Badge Type: **Custom** (default) or **Tags** (renders listing tags as badges; optional maximum tag count)
  - Display Type: **Label** (text, required label, font size, optional icon, background + text color via WP color picker) or **Image** (media-library image or URL, display width)
  - Optional CSS class
- **FR-2 List view** shows all badges with status, supports:
  - Activate/deactivate toggle (non-destructive)
  - Duplicate (gets a unique suffixed Badge ID)
  - Delete (with confirmation)
  - Drag & drop reordering (persisted order)
- **FR-3 Import/Export** badge configurations as JSON; imported badges get regenerated IDs so they never collide.
- **FR-4** All admin mutations happen over AJAX with nonce + capability verification, with user-facing success/error feedback (i18n-ready strings).

### 4.2 Display Conditions

- **FR-5** A badge may have zero or more conditions. Zero conditions = always shown (where placed). Conditions combine under a badge-level relation: **AND** (all must pass, default) or **OR** (any passes).
- **FR-6 Meta field condition**: compare a listing meta key against a value with operators `=`, `!=`, `>`, `>=`, `<`, `<=`, `LIKE`, `NOT LIKE`, `IN`, `NOT IN`, `EXISTS`, `NOT EXISTS` and type casts `CHAR`, `NUMERIC`, `DECIMAL`, `DATE`, `DATETIME`, `BOOLEAN`. The meta-key input is a native datalist combobox pre-populated with keys discovered from existing listings plus common Directorist keys; custom keys can be typed.
- **FR-7 Pricing plan condition** (requires Directorist Pricing Plans):
  - *User Active Plan* — the listing author holds a completed, non-expired order for the selected plan(s)
  - *Listing Has Plan* — the listing is assigned to the selected plan(s) (`=`, `!=`, `IN`, `NOT IN`)
- **FR-8** Unknown/invalid condition types are skipped without breaking the badge; a badge whose conditions are all unprocessable is hidden.

### 4.3 Frontend Display

- **FR-9** Every active badge registers as a **builder widget** in Directorist's directory builder for grid and list card templates (all standard badge placement zones) and as a toggle in the single listing header layout.
- **FR-10** Badge rendering respects conditions per listing at render time; markup uses Directorist badge classes plus `directorist-smart-badge` and the admin-supplied class, with inline styles for colors/font-size only when configured.
- **FR-11** Tags-type badges render the listing's Directorist tags (capped at Maximum Tags when set) in badge styling.

### 4.4 Developer API

- **FR-12** `new Directorist_Smart_Badge( $atts )` registers a badge from code; simple `meta_key`/`meta_value` fallback matching is supported for backward compatibility.

## 5. Non-Functional Requirements

- **Compatibility:** WordPress ≥ 5.2, PHP ≥ 7.4; requires active Directorist; multisite-aware activation check; plugin no-ops (does not fatal) when Directorist is inactive.
- **Security:** whitelist validation of all enum-like inputs; sanitization of all user input; nonce (`directorist_smart_badges_nonce`) + `manage_options` on every AJAX endpoint; escaped output in templates; WordPress DB APIs only.
- **Performance:** single autoload-off option (`directorist_smart_badges`) for all badge configs; meta-key discovery query capped at 500 keys and statically cached per request; assets enqueued only on plugin admin pages / frontend where needed.
- **Admin UI (v3.4.0):** modern card-based design — sticky header with always-visible Save, CSS-only vertical tabs, toggle switches, one accent color (Directorist purple `#6c39f0`), system font stack, inline SVG icons only. Exactly one CSS + one deferred vanilla-JS file (no jQuery, no build step, zero external requests); all saves via AJAX (`fetch` + `FormData`) with toast feedback; visible focus states, RTL-safe logical properties, `prefers-reduced-motion` respected.
- **i18n:** every user-facing string translatable under text domain `directorist-smart-badges` (Domain Path `/languages`).
- **Data continuity:** upgrading from "Directorist - Custom Badges" (≤ 3.2.0) must preserve badges — a one-time migration copies the legacy `directorist_custom_badges` option to `directorist_smart_badges` when the new option is absent.

## 6. Data & Storage

| Storage | Purpose |
|---|---|
| Option `directorist_smart_badges` (autoload off) | Array of all badge configurations (see README.md → Data Model) |
| Option `directorist_custom_badges` (legacy) | Read once for migration; left untouched as backup |
| Listing meta (`_fm_plans`, arbitrary keys) | Read-only inputs to condition evaluation |
| `atbdp_orders` / `atbdp_pricing_plans` posts | Read-only inputs to pricing-plan conditions |

## 7. Success Criteria

- Admin can go from zero to a conditional badge visible on qualifying listing cards in under 2 minutes with no code.
- Badges never appear on listings that fail their conditions; no PHP notices/fatals with or without the Pricing Plans extension present.
- Rename release (3.3.0): existing sites keep all badges after upgrade with zero manual steps beyond reactivating the plugin.

## 8. Future Considerations

- Scheduled badges (date-range visibility) using the existing DATE/DATETIME casts.
- Additional condition types (taxonomy terms, review rating, listing age) — the conditions engine already skips unknown types safely, so new types are additive.
- Badge preview in the admin form.
- Export/import via file download/upload rather than JSON text.
