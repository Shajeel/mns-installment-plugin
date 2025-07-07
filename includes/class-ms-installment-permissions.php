<?php
/**
 * Permissions and role management class for MS Installment Plugin
 */
class MS_Installment_Permissions {
    
    /**
     * Plugin capability name
     */
    const CAPABILITY = 'manage_ms_installments';
    
    /**
     * Static flag to prevent infinite loops
     */
    private static $checking_permissions = false;
    
    /**
     * Initialize permissions
     */
    public static function init() {
        // Only initialize if WordPress is fully loaded
        if (!did_action('init')) {
            add_action('init', array(__CLASS__, 'init'));
            return;
        }
        
        // Add custom capability to administrator role
        add_action('admin_init', array(__CLASS__, 'add_capabilities'));
        
        // Filter admin menu visibility
        add_filter('user_has_cap', array(__CLASS__, 'filter_user_capabilities'), 10, 4);
        
        // Add role management to settings
        add_action('admin_init', array(__CLASS__, 'handle_role_settings'));
    }
    
    /**
     * Add custom capabilities to roles
     */
    public static function add_capabilities() {
        // Safety check - only run if WordPress roles are available
        if (!function_exists('get_role') || !function_exists('wp_roles')) {
            return;
        }
        
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->add_cap(self::CAPABILITY);
        }
        
        // Add capability to other roles based on settings
        $enabled_roles = get_option('ms_installment_enabled_roles', array('administrator'));
        
        foreach ($enabled_roles as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                $role->add_cap(self::CAPABILITY);
            }
        }
    }
    
    /**
     * Check if user can manage installments
     */
    public static function user_can_manage_installments($user_id = null) {
        // Prevent infinite loops
        if (self::$checking_permissions) {
            return false;
        }
        
        self::$checking_permissions = true;
        
        try {
            if (!$user_id) {
                $user_id = get_current_user_id();
            }
            
            if (!$user_id) {
                return false;
            }
            
            // Safety check - only run if WordPress user functions are available
            if (!function_exists('get_userdata')) {
                return false;
            }
            
            $user = get_userdata($user_id);
            if (!$user) {
                return false;
            }
            
            // Simple administrator check first (most common case)
            if (in_array('administrator', $user->roles)) {
                return true;
            }
            
            // Check if user has the custom capability (but avoid circular dependency)
            if ($user->has_cap(self::CAPABILITY)) {
                return true;
            }
            
            // Check enabled roles from settings
            $enabled_roles = get_option('ms_installment_enabled_roles', array('administrator'));
            foreach ($user->roles as $role) {
                if (in_array($role, $enabled_roles)) {
                    return true;
                }
            }
            
            return false;
        } finally {
            self::$checking_permissions = false;
        }
    }
    
    /**
     * Check if user can view applications
     */
    public static function user_can_view_applications($user_id = null) {
        return self::user_can_manage_installments($user_id);
    }
    
    /**
     * Check if user can edit applications
     */
    public static function user_can_edit_applications($user_id = null) {
        return self::user_can_manage_installments($user_id);
    }
    
    /**
     * Check if user can delete applications
     */
    public static function user_can_delete_applications($user_id = null) {
        return self::user_can_manage_installments($user_id);
    }
    
    /**
     * Check if user can manage settings
     */
    public static function user_can_manage_settings($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }
        
        // Only administrators can manage settings
        return in_array('administrator', $user->roles);
    }
    
    /**
     * Get available roles for the plugin
     */
    public static function get_available_roles() {
        $roles = wp_roles()->get_names();
        
        // Remove roles that shouldn't have access
        $excluded_roles = array('subscriber');
        foreach ($excluded_roles as $role) {
            unset($roles[$role]);
        }
        
        return $roles;
    }
    
    /**
     * Get enabled roles for the plugin
     */
    public static function get_enabled_roles() {
        return get_option('ms_installment_enabled_roles', array('administrator'));
    }
    
    /**
     * Save enabled roles
     */
    public static function save_enabled_roles($roles) {
        // Ensure administrator is always included
        if (!in_array('administrator', $roles)) {
            $roles[] = 'administrator';
        }
        
        update_option('ms_installment_enabled_roles', $roles);
        self::add_capabilities(); // Refresh capabilities
    }
    
    /**
     * Handle role settings form submission
     */
    public static function handle_role_settings() {
        if (isset($_POST['ms_installment_roles_nonce']) && 
            wp_verify_nonce($_POST['ms_installment_roles_nonce'], 'ms_installment_roles')) {
            
            if (!self::user_can_manage_settings()) {
                wp_die(__('You do not have permission to manage these settings.', 'ms-installment'));
            }
            
            $enabled_roles = isset($_POST['enabled_roles']) ? array_map('sanitize_text_field', $_POST['enabled_roles']) : array('administrator');
            self::save_enabled_roles($enabled_roles);
            
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success"><p>' . __('Role permissions updated successfully.', 'ms-installment') . '</p></div>';
            });
        }
    }
    
    /**
     * Filter user capabilities to check for custom capability
     */
    public static function filter_user_capabilities($allcaps, $caps, $args, $user) {
        // Check if the requested capability is our custom one
        if (in_array(self::CAPABILITY, $caps)) {
            // Simple check without calling user_can_manage_installments to avoid circular dependency
            if (in_array('administrator', $user->roles)) {
                $allcaps[self::CAPABILITY] = true;
            } else {
                // Check enabled roles from settings
                $enabled_roles = get_option('ms_installment_enabled_roles', array('administrator'));
                foreach ($user->roles as $role) {
                    if (in_array($role, $enabled_roles)) {
                        $allcaps[self::CAPABILITY] = true;
                        break;
                    }
                }
            }
        }
        
        return $allcaps;
    }
    
    /**
     * Remove capabilities from roles (cleanup)
     */
    public static function remove_capabilities() {
        $roles = wp_roles()->get_names();
        
        foreach ($roles as $role_name => $role_display_name) {
            $role = get_role($role_name);
            if ($role) {
                $role->remove_cap(self::CAPABILITY);
            }
        }
    }
    
    /**
     * Get user's role display name
     */
    public static function get_user_role_display_name($user_id) {
        $user = get_userdata($user_id);
        if (!$user) {
            return '';
        }
        
        $roles = wp_roles()->get_names();
        $user_roles = $user->roles;
        
        if (!empty($user_roles)) {
            $primary_role = $user_roles[0];
            return isset($roles[$primary_role]) ? $roles[$primary_role] : $primary_role;
        }
        
        return '';
    }
    
    /**
     * Check if current user can access admin page
     */
    public static function can_access_admin_page() {
        $current_screen = get_current_screen();
        
        if (!$current_screen) {
            return false;
        }
        
        // Check if this is our plugin page
        if (strpos($current_screen->id, 'ms-installment') !== false) {
            return self::user_can_manage_installments();
        }
        
        return true;
    }
    
    /**
     * Add permission check to AJAX actions
     */
    public static function check_ajax_permissions() {
        if (!self::user_can_manage_installments()) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'ms-installment'));
        }
    }
    
    /**
     * Get permission error message
     */
    public static function get_permission_error_message() {
        return __('You do not have permission to access this feature. Please contact your administrator.', 'ms-installment');
    }
} 