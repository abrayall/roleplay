<?php
if (!defined('ABSPATH')) {
    exit;
}

class Roleplay_Manager {

    public function get_roles() {
        global $wp_roles;

        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }

        $roles = array();

        foreach ($wp_roles->roles as $slug => $role) {
            $cap_count = count(array_filter($role['capabilities']));
            $roles[] = array(
                'slug' => $slug,
                'name' => $role['name'],
                'capabilities_count' => $cap_count,
                'capabilities_summary' => $this->get_capabilities_summary($role['capabilities']),
            );
        }

        usort($roles, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return $roles;
    }

    public function get_role($slug) {
        global $wp_roles;

        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }

        if (!isset($wp_roles->roles[$slug])) {
            return null;
        }

        $role = $wp_roles->roles[$slug];

        return array(
            'slug' => $slug,
            'name' => $role['name'],
            'capabilities' => array_keys(array_filter($role['capabilities'])),
        );
    }

    public function save_role($slug, $name, $capabilities) {
        global $wp_roles;

        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }

        // Build capabilities array
        $caps = array();
        foreach ($capabilities as $cap) {
            $caps[$cap] = true;
        }

        // If editing existing role
        if (!empty($slug) && isset($wp_roles->roles[$slug])) {
            // Remove old role
            remove_role($slug);
        }

        // Generate slug from name if new role
        if (empty($slug)) {
            $slug = sanitize_title($name);
        }

        // Add role with capabilities
        $result = add_role($slug, $name, $caps);

        return $result !== null || isset($wp_roles->roles[$slug]);
    }

    public function delete_role($slug) {
        // Prevent deleting administrator role
        if ($slug === 'administrator') {
            return false;
        }

        remove_role($slug);

        global $wp_roles;
        return !isset($wp_roles->roles[$slug]);
    }

    public function get_all_capabilities() {
        global $wp_roles;

        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }

        $capabilities = array();

        // Collect all capabilities from all roles
        foreach ($wp_roles->roles as $role) {
            foreach ($role['capabilities'] as $cap => $granted) {
                if (!in_array($cap, $capabilities)) {
                    $capabilities[] = $cap;
                }
            }
        }

        sort($capabilities);

        return $capabilities;
    }

    private function get_capabilities_summary($capabilities) {
        $enabled = array_keys(array_filter($capabilities));

        if (count($enabled) === 0) {
            return '';
        }

        $display = array_slice($enabled, 0, 3);
        $tags = array_map(function($cap) {
            return '<span class="roleplay-cap">' . esc_html($cap) . '</span>';
        }, $display);

        $result = implode(' ', $tags);

        $remaining = count($enabled) - 3;
        if ($remaining > 0) {
            $result .= ' <em class="roleplay-more">+' . $remaining . ' more (' . count($enabled) . ' total)</em>';
        }

        return $result;
    }
}
