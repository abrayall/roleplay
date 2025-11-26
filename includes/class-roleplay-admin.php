<?php
if (!defined('ABSPATH')) {
    exit;
}

class Roleplay_Admin {

    private $manager;

    public function __construct() {
        $this->manager = new Roleplay_Manager();
    }

    public function init() {
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_roleplay_get_roles', array($this, 'ajax_get_roles'));
        add_action('wp_ajax_roleplay_get_role', array($this, 'ajax_get_role'));
        add_action('wp_ajax_roleplay_save_role', array($this, 'ajax_save_role'));
        add_action('wp_ajax_roleplay_delete_role', array($this, 'ajax_delete_role'));
        add_action('wp_ajax_roleplay_get_capabilities', array($this, 'ajax_get_capabilities'));
    }

    public function add_menu() {
        add_users_page(
            __('Roles', 'roleplay'),
            __('Roles', 'roleplay'),
            'manage_options',
            'roleplay',
            array($this, 'render_page')
        );
    }

    public function enqueue_scripts($hook) {
        if ($hook !== 'users_page_roleplay') {
            return;
        }

        wp_enqueue_style(
            'roleplay-admin',
            ROLEPLAY_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            ROLEPLAY_VERSION
        );

        wp_enqueue_script(
            'roleplay-admin',
            ROLEPLAY_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            ROLEPLAY_VERSION,
            true
        );

        wp_localize_script('roleplay-admin', 'roleplay', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('roleplay_nonce'),
            'strings' => array(
                'confirm_delete' => __('Are you sure you want to delete this role?', 'roleplay'),
                'error' => __('An error occurred. Please try again.', 'roleplay'),
            )
        ));
    }

    public function render_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('User Roles', 'roleplay'); ?></h1>

            <div class="roleplay-toolbar">
                <button type="button" class="button button-primary" id="roleplay-add-role">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php _e('Role', 'roleplay'); ?>
                </button>
            </div>

            <table class="wp-list-table widefat fixed striped" id="roleplay-table">
                <thead>
                    <tr>
                        <th class="column-name"><?php _e('Name', 'roleplay'); ?></th>
                        <th class="column-slug"><?php _e('Slug', 'roleplay'); ?></th>
                        <th class="column-permissions"><?php _e('Permissions', 'roleplay'); ?></th>
                        <th class="column-actions"><?php _e('Actions', 'roleplay'); ?></th>
                    </tr>
                </thead>
                <tbody id="roleplay-roles-list">
                    <tr>
                        <td colspan="4"><?php _e('Loading...', 'roleplay'); ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="roleplay-cards" id="roleplay-cards"></div>

            <div class="roleplay-total" id="roleplay-total"></div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <dialog id="roleplay-delete-dialog" class="roleplay-dialog">
            <h3><?php _e('Delete Role', 'roleplay'); ?></h3>
            <p><?php _e('Are you sure you want to delete this role?', 'roleplay'); ?></p>
            <p class="roleplay-dialog-warning"><?php _e('Users assigned to this role will lose their permissions. Consider reassigning them first.', 'roleplay'); ?></p>
            <div class="roleplay-dialog-actions">
                <button type="button" class="button button-primary" id="roleplay-confirm-delete"><?php _e('Delete', 'roleplay'); ?></button>
                <button type="button" class="button" id="roleplay-cancel-delete"><?php _e('Cancel', 'roleplay'); ?></button>
            </div>
        </dialog>

        <!-- Modal -->
        <div id="roleplay-modal" class="roleplay-modal" style="display:none;">
            <div class="roleplay-modal-content">
                <div class="roleplay-modal-header">
                    <h2 id="roleplay-modal-title"><?php _e('Add Role', 'roleplay'); ?></h2>
                    <button type="button" class="roleplay-modal-close">&times;</button>
                </div>
                <div class="roleplay-modal-body">
                    <form id="roleplay-role-form">
                        <input type="hidden" id="roleplay-role-slug" name="slug" value="">

                        <p>
                            <label for="roleplay-role-name"><?php _e('Name', 'roleplay'); ?></label>
                            <input type="text" id="roleplay-role-name" name="name" class="regular-text" required>
                        </p>

                        <div class="roleplay-permissions">
                            <label><?php _e('Permissions', 'roleplay'); ?></label>
                            <input type="text" id="roleplay-permissions-filter" class="regular-text" placeholder="<?php esc_attr_e('Filter...', 'roleplay'); ?>">
                            <div id="roleplay-permissions-list" class="roleplay-permissions-grid">
                                <!-- Permissions loaded via AJAX -->
                            </div>
                        </div>
                    </form>
                </div>
                <div class="roleplay-modal-footer">
                    <button type="button" class="button button-primary" id="roleplay-modal-save"><?php _e('Save', 'roleplay'); ?></button>
                    <button type="button" class="button" id="roleplay-modal-cancel"><?php _e('Cancel', 'roleplay'); ?></button>
                </div>
            </div>
        </div>
        <?php
    }

    public function ajax_get_roles() {
        check_ajax_referer('roleplay_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $roles = $this->manager->get_roles();
        wp_send_json_success($roles);
    }

    public function ajax_get_role() {
        check_ajax_referer('roleplay_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $slug = sanitize_text_field($_POST['slug']);
        $role = $this->manager->get_role($slug);

        if ($role) {
            wp_send_json_success($role);
        } else {
            wp_send_json_error('Role not found');
        }
    }

    public function ajax_save_role() {
        check_ajax_referer('roleplay_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $slug = sanitize_text_field($_POST['slug']);
        $name = sanitize_text_field($_POST['name']);
        $capabilities = isset($_POST['capabilities']) ? array_map('sanitize_text_field', $_POST['capabilities']) : array();

        $result = $this->manager->save_role($slug, $name, $capabilities);

        if ($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Failed to save role');
        }
    }

    public function ajax_delete_role() {
        check_ajax_referer('roleplay_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $slug = sanitize_text_field($_POST['slug']);
        $result = $this->manager->delete_role($slug);

        if ($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Failed to delete role');
        }
    }

    public function ajax_get_capabilities() {
        check_ajax_referer('roleplay_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $capabilities = $this->manager->get_all_capabilities();
        wp_send_json_success($capabilities);
    }
}
