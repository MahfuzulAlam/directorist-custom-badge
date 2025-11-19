# Directorist - Custom Badges

A powerful WordPress plugin extension for Directorist that allows you to create and manage custom badges for your listings with advanced condition-based display rules.

## Description

Directorist Custom Badges extends the Directorist plugin functionality by providing a comprehensive badge management system. Create custom badges that display on listings based on configurable conditions such as meta fields, pricing plans, and more.

## Features

- **Custom Badge Creation**: Create unlimited custom badges with custom labels, icons, colors, and CSS classes
- **Condition-Based Display**: Display badges based on:
  - Meta field conditions (with various comparison operators)
  - Pricing plan conditions (user active plans, listing plans)
  - Multiple conditions with AND/OR logic
- **Template Integration**: Seamlessly integrates with Directorist templates
- **Admin Interface**: User-friendly admin interface for managing badges
- **Import/Export**: Import and export badge configurations
- **Active/Inactive Toggle**: Enable or disable badges without deleting them
- **Drag & Drop Reordering**: Reorder badges with drag and drop functionality

## Requirements

- WordPress 5.2 or higher
- Directorist plugin (active)
- PHP 7.4 or higher

## Installation

1. Upload the `directorist-custom-badge` folder to `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to Directorist > Custom Badges to start creating badges

## Usage

### Creating a Badge via Admin

1. Go to **Directorist > Custom Badges** in your WordPress admin
2. Click **Add New Badge**
3. Fill in the badge details:
   - Badge Title
   - Badge ID (lowercase with hyphens only)
   - Badge Label (display text)
   - Icon (optional)
   - CSS Class (optional)
   - Color (optional)
4. Configure conditions (meta fields, pricing plans, etc.)
5. Set condition relation (AND/OR)
6. Save the badge

### Programmatic Usage

You can also create badges programmatically:

```php
add_action('init', function() {
    $my_badge_atts = array(
        'id'         => 'my-badge',
        'label'      => 'Badge',
        'icon'       => 'las la-check-circle',
        'hook'       => 'atbdp-my-badge',
        'title'      => 'My Badge',
        'meta_key'   => '_custom-select',
        'meta_value' => 'Free',
        'class'      => 'my-custom-badge'
    );
    new Directorist_Custom_Badge($my_badge_atts);
});
```

## Condition Types

### Meta Field Conditions

Display badges based on custom field values with operators:
- `=` (equals)
- `!=` (not equals)
- `>` (greater than)
- `>=` (greater than or equal)
- `<` (less than)
- `<=` (less than or equal)
- `LIKE` (contains)
- `NOT LIKE` (does not contain)
- `IN` (in list)
- `NOT IN` (not in list)
- `EXISTS` (field exists)
- `NOT EXISTS` (field does not exist)

Type casting options:
- `CHAR` (string)
- `NUMERIC` (numbers)
- `DECIMAL` (decimal numbers)
- `DATE` (dates)
- `DATETIME` (date and time)
- `BOOLEAN` (true/false)

### Pricing Plan Conditions

- **User Active Plan**: Check if the listing owner has an active pricing plan
- **Listing Has Plan**: Check if the listing is assigned to a specific pricing plan

## Security

This plugin follows WordPress security best practices:
- All user inputs are sanitized and validated
- Nonce verification for AJAX requests
- Capability checks for admin functions
- Proper data escaping for output
- SQL injection prevention through WordPress APIs

## Coding Standards

The plugin follows WordPress Coding Standards:
- PSR-2 compatible code structure
- Proper escaping and sanitization
- Consistent naming conventions
- Comprehensive inline documentation

## Support

For support, feature requests, or bug reports, please visit:
- Plugin URI: https://wpxplore.com/tools/directorist-custom-badges/

## Changelog

### 3.0.0
- Complete rewrite with improved architecture
- Added condition-based badge display
- Enhanced admin interface
- Improved security and code quality
- Added import/export functionality
- Better integration with Directorist templates

## License

GPL v2 or later

## Author

wpWax - https://wpxplore.com
