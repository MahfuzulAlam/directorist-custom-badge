# Directorist - Smart Badges — User Guide

This guide is for site owners and administrators. For technical/developer details, see [README.md](README.md).

Smart Badges lets you create eye-catching badges — "Verified", "Premium", "Top Rated", and more — that appear **automatically** on the Directorist listings that match rules you define. No code required.

---

## 1. Requirements & Installation

You need:

- WordPress 5.2 or newer
- The **Directorist** plugin installed and active
- (Optional) the **Directorist Pricing Plans** extension, if you want plan-based badge rules

To install:

1. Upload the `directorist-smart-badges` folder to `/wp-content/plugins/`.
2. Activate **Directorist - Smart Badges** from the **Plugins** menu.
3. Go to **Directory Listings → Smart Badges** to start creating badges.

> **Upgrading from "Directorist - Custom Badges"?** Your existing badges are migrated automatically the first time the new version loads. Because the plugin folder changed, WordPress deactivates the old entry — just activate "Directorist - Smart Badges" once and everything carries over.

## 2. Creating Your First Badge

The badge editor is organized into three tabs — **General**, **Appearance**, and **Conditions** — with the **Save Badge** button and the **Active** switch always visible in the bar at the top of the page.

1. Go to **Directory Listings → Smart Badges** and click **Add New Badge**.
2. On the **General** tab, fill in the basics:
   - **Badge Title** — the name you'll see in the admin list (e.g. "Verified Business").
   - **Badge ID** — a unique identifier in lowercase letters, numbers, and hyphens (e.g. `verified-business`). This is also what the badge is called in the directory builder.
3. Still on **General**, choose a **Badge Type**:
   - **Custom** — a normal badge showing a label or an image (most common).
   - **Tags** — displays the listing's own tags as small badges. Use **Maximum Tags** to cap how many show (0 = show all).
4. On the **Appearance** tab, for a Custom badge choose a **Display Type**:
   - **Label** — text badge. Set the **Badge Label** (the text visitors see), optionally a font size, an **Icon**, a **Background Color**, and a **Text Color** (use the color swatch or type a hex value like `#7a45e5`; **Clear** removes the color).
   - **Image** — pick an image from the Media Library (or paste a URL) and set its display width.
5. Optionally add a **CSS Class** if you want to style the badge with your own CSS.
6. On the **Conditions** tab, add display **Conditions** (see next section) — or leave empty to always show the badge wherever it's placed.
7. Click **Save Badge** in the top bar. Your changes are saved instantly without a page reload, and a small confirmation message appears.

## 3. Conditions — Making Badges "Smart"

Conditions decide **which listings** get the badge. Add as many as you like, then choose how they combine:

- **AND** — the listing must match *every* condition (default).
- **OR** — the listing must match *at least one* condition.

### Meta Field Condition

Checks a listing's custom field (meta) value. Choose:

- **Meta Key** — pick from the dropdown (it lists keys found on your listings, e.g. `_featured`, `_price`, `phone`, `website`) or type your own.
- **Operator** — how to compare:

  | Operator | Meaning |
  |---|---|
  | `=` / `!=` | equals / does not equal |
  | `>` `>=` `<` `<=` | greater/less than (use with Numeric or Date types) |
  | `LIKE` / `NOT LIKE` | contains / does not contain |
  | `IN` / `NOT IN` | value is / isn't in the stored list |
  | `EXISTS` / `NOT EXISTS` | the field is present / absent (no value needed) |

- **Value** — what to compare against (not needed for EXISTS / NOT EXISTS).
- **Type** — how to interpret the values: **Text** (CHAR), **Numeric**, **Decimal**, **Date**, **Date & Time**, or **Boolean** (understands yes/no, true/false, 1/0, on/off).

**Examples**

| Goal | Setup |
|---|---|
| Badge for featured listings | Meta key `_featured`, operator `=`, value `1`, type Boolean |
| Badge for listings over $100 | Meta key `_price`, operator `>`, value `100`, type Numeric |
| Badge for listings with a website | Meta key `website`, operator `EXISTS` |

### Pricing Plan Condition

*(Requires the Directorist Pricing Plans extension.)*

- **User Active Plan** — the badge shows when the listing's **owner** currently has an active (paid, unexpired) subscription to the plan(s) you select. Great for "Premium Member" badges.
- **Listing Has Plan** — the badge shows when the **listing itself** was submitted under the selected plan(s). You can also use "is not" (`!=` / `NOT IN`) to badge everything *except* certain plans.

## 4. Placing Badges on Your Directory

Creating a badge makes it **available**; the Directorist builder controls **where** it appears:

1. Go to **Directory Listings → Directory Builder** and edit your directory type.
2. Open **All Listings → Card Layout** (grid and list views). Your badge appears in the widget list under its Badge ID — drag it into a placement zone (thumbnail corners, body, footer, etc.).
3. For the **single listing page**, open the **Single Listing Header** section and switch on the toggle for your badge.
4. Save the directory type.

The badge now renders in those spots — but **only** on listings that pass its conditions.

## 5. Managing Badges

On the **Smart Badges** list page you can:

- **Activate / Deactivate** — temporarily hide a badge everywhere without deleting it.
- **Edit** — change any setting; updates apply immediately.
- **Duplicate** — copy a badge (the copy gets a new unique ID) — handy for creating variations.
- **Delete** — remove a badge permanently (you'll be asked to confirm).
- **Reorder** — drag rows to change badge order.

## 6. Import & Export

- **Export** downloads your badge configurations so you can back them up or move them to another site.
- **Import** loads exported badge data. Imported badges receive fresh IDs, so they never overwrite or conflict with existing badges.

## 7. Styling Tips

- The quickest styling is the built-in **Background Color** and **Text Color** pickers — the plugin automatically keeps text readable.
- For advanced styling, give the badge a **CSS Class** and target it in **Appearance → Customize → Additional CSS**. Every badge also carries the `directorist-smart-badge` class:

```css
.my-special-badge {
    border-radius: 12px;
    text-transform: uppercase;
}
```

- Icons use Directorist's icon set (e.g. Line Awesome classes like `las la-check-circle`).

## 8. Troubleshooting / FAQ

**My badge doesn't appear anywhere.**
Check, in order: the badge is **Active**; it has been **placed** in the directory builder (card layout or single listing header); the listing actually **passes the conditions**; and your directory type is the one you edited in the builder.

**The badge shows on every listing.**
The badge has no conditions (that's "always show"), or the conditions use OR and one of them matches everything.

**Pricing plan conditions don't work.**
They require the Directorist Pricing Plans extension to be installed and active. "User Active Plan" only counts **completed, unexpired** orders.

**A number comparison behaves strangely.**
Make sure the condition **Type** is set to Numeric or Decimal — with the default Text type, `"9"` is greater than `"10"` alphabetically.

**I renamed/updated the plugin and my badges are gone.**
Activate the new "Directorist - Smart Badges" plugin entry; badges migrate automatically on the first load. Your old data is kept as a backup under the previous settings entry.

## 9. Support

- Plugin page: https://wpxplore.com/tools/directorist-smart-badges/
- Author: wpWax — https://wpxplore.com
