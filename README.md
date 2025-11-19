# Roleplay

A WordPress user role editor plugin.

## Features

- View all roles with name, slug, and capability summary
- Add new custom roles
- Edit existing role names and capabilities
- Delete roles with warning about affected users
- Mobile-friendly responsive design with card view
- HTML dialog confirmations

## Installation

1. Build the plugin (see below)
2. Upload `roleplay-x.x.x.zip` via WordPress admin: Plugins → Add New → Upload Plugin
3. Activate the plugin
4. Navigate to Users → Roles

## Development

### Prerequisites

- [Wordsmith](https://github.com/abrayall/wordsmith) - WordPress plugin build tool

### Building

```bash
wordsmith build
```

This creates `build/roleplay-x.x.x.zip` ready for installation.

### Versioning

The plugin version is automatically derived from git tags. To release a new version:

```bash
git tag v1.0.0
git push origin v1.0.0
wordsmith build
```

Wordsmith reads the latest `vX.X.X` tag and uses it as the plugin version. If no tags exist, it defaults to `0.1.0`.

### Project Structure

```
roleplay/
├── plugin.properties      # Plugin metadata for wordsmith
├── roleplay.php           # Main plugin file
├── includes/
│   ├── class-roleplay-admin.php    # Admin page & AJAX handlers
│   └── class-roleplay-manager.php  # Role/capability CRUD operations
└── assets/
    ├── css/admin.css      # Admin styles
    └── js/admin.js        # Admin JavaScript
```

### How It Works

- Roles are stored in `wp_options` table under `wp_user_roles`
- Uses WordPress `WP_Roles` class and `add_role()`, `remove_role()` functions
- Admin page registered under Users menu via `add_users_page()`
- All operations via AJAX with nonce verification
- Capabilities collected from all existing roles

## Known Issues

- WordPress admin CSS aggressively overrides form elements, especially on mobile
- When deleting a role, users assigned to it lose capabilities (they become role-less)

## Future Ideas / Roadmap

- [ ] **Clone role** - Duplicate an existing role as starting point
- [ ] **User count per role** - Show how many users assigned to each role
- [ ] **Bulk capability actions** - Select all, clear all, group by type
- [ ] **Export/Import** - Backup roles as JSON, restore on another site
- [ ] **Capability search** - Filter checkbox list when 60+ capabilities
- [ ] **Role comparison** - Side-by-side diff of two roles
- [ ] **Custom capabilities** - Add new capabilities for plugin developers

## License

GPL-2.0+
