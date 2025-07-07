<?php
/**
 * Main plugin class
 */
class MS_Installment_Plugin {
    
    public function __construct() {
        try {
            $this->init_hooks();
            $this->load_dependencies();
        } catch (Exception $e) {
            // Log the error and show admin notice
            error_log('MS Installment Plugin Constructor Error: ' . $e->getMessage());
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error"><p>' . 
                     sprintf(__('MS Installment Plugin: Error during initialization: %s', 'ms-installment'), $e->getMessage()) . 
                     '</p></div>';
            });
        }
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // Frontend hooks
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));
        add_action('woocommerce_after_add_to_cart_button', array($this, 'add_installment_button'));
        add_action('wp_ajax_ms_submit_installment_application', array($this, 'handle_application_submission'));
        add_action('wp_ajax_nopriv_ms_submit_installment_application', array($this, 'handle_application_submission'));
        add_action('wp_ajax_ms_refresh_captcha', array($this, 'handle_captcha_refresh'));
        add_action('wp_ajax_nopriv_ms_refresh_captcha', array($this, 'handle_captcha_refresh'));
        add_action('wp_ajax_ms_calculate_installment', array('MS_Installment_Ajax', 'calculate_installment'));
        add_action('wp_ajax_nopriv_ms_calculate_installment', array('MS_Installment_Ajax', 'calculate_installment'));
        
        // Shortcode
        add_shortcode('ms_installment_calculator', array($this, 'installment_calculator_shortcode'));
        
        // Product meta box
        add_action('add_meta_boxes', array($this, 'add_product_meta_box'));
        add_action('save_post', array($this, 'save_product_meta_box'));
        
        // Plugin deactivation hook
        register_deactivation_hook(MS_INSTALLMENT_PLUGIN_FILE, array($this, 'deactivate_plugin'));
        
        // Plugin activation hook
        register_activation_hook(MS_INSTALLMENT_PLUGIN_FILE, array($this, 'activate_plugin'));
        
        // Initialize database on plugin load
        add_action('init', array($this, 'init_database'));
        
        // Initialize permissions after WordPress is fully loaded
        add_action('init', array($this, 'init_permissions'));
    }
    
    /**
     * Initialize permissions system
     */
    public function init_permissions() {
        try {
            if (class_exists('MS_Installment_Permissions')) {
                MS_Installment_Permissions::init();
            }
        } catch (Exception $e) {
            error_log('MS Installment Plugin Permissions Error: ' . $e->getMessage());
        }
    }

    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        try {
            require_once MS_INSTALLMENT_PLUGIN_PATH . 'includes/class-ms-installment-admin.php';
            require_once MS_INSTALLMENT_PLUGIN_PATH . 'includes/class-ms-installment-frontend.php';
            require_once MS_INSTALLMENT_PLUGIN_PATH . 'includes/class-ms-installment-ajax.php';
            require_once MS_INSTALLMENT_PLUGIN_PATH . 'includes/class-ms-installment-email.php';
            require_once MS_INSTALLMENT_PLUGIN_PATH . 'includes/class-ms-installment-permissions.php';
            
            // Initialize classes
            if (class_exists('MS_Installment_Admin')) {
                new MS_Installment_Admin();
            }
            if (class_exists('MS_Installment_Frontend')) {
                new MS_Installment_Frontend();
            }
            if (class_exists('MS_Installment_Ajax')) {
                new MS_Installment_Ajax();
            }
        } catch (Exception $e) {
            error_log('MS Installment Plugin Dependencies Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Installment Applications', 'ms-installment'),
            __('M&S Installments', 'ms-installment'),
            MS_Installment_Permissions::CAPABILITY,
            'ms-installment-applications',
            array($this, 'admin_applications_page'),
            'dashicons-money-alt',
            56
        );
        
        add_submenu_page(
            'ms-installment-applications',
            __('Applications', 'ms-installment'),
            __('Applications', 'ms-installment'),
            MS_Installment_Permissions::CAPABILITY,
            'ms-installment-applications',
            array($this, 'admin_applications_page')
        );
        
        add_submenu_page(
            'ms-installment-applications',
            __('Settings', 'ms-installment'),
            __('Settings', 'ms-installment'),
            'manage_options', // Only administrators can access settings
            'ms-installment-settings',
            array($this, 'admin_settings_page')
        );
    }
    
    /**
     * Admin applications page
     */
    public function admin_applications_page() {
        require_once MS_INSTALLMENT_PLUGIN_PATH . 'templates/admin/applications-page.php';
    }
    
    /**
     * Admin settings page
     */
    public function admin_settings_page() {
        require_once MS_INSTALLMENT_PLUGIN_PATH . 'templates/admin/settings-page.php';
    }
    
    /**
     * Enqueue admin scripts
     */
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'ms-installment') !== false) {
            wp_enqueue_script('ms-installment-admin', MS_INSTALLMENT_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), MS_INSTALLMENT_PLUGIN_VERSION, true);
            wp_enqueue_style('ms-installment-admin', MS_INSTALLMENT_PLUGIN_URL . 'assets/css/admin.css', array(), MS_INSTALLMENT_PLUGIN_VERSION);
            
            wp_localize_script('ms-installment-admin', 'ms_installment_admin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ms_installment_admin_nonce')
            ));
        }
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function frontend_enqueue_scripts() {
        wp_enqueue_script('ms-installment-frontend', MS_INSTALLMENT_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), MS_INSTALLMENT_PLUGIN_VERSION, true);
        wp_enqueue_style('ms-installment-frontend', MS_INSTALLMENT_PLUGIN_URL . 'assets/css/frontend.css', array(), MS_INSTALLMENT_PLUGIN_VERSION);
        
        wp_localize_script('ms-installment-frontend', 'ms_installment_frontend', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ms_installment_frontend_nonce')
        ));
    }
    
    /**
     * Add installment button to product page
     */
    public function add_installment_button() {
        global $product;
        
        if (!$product) {
            return;
        }
        
        // Check if installment is enabled for this product
        if (!$this->is_installment_enabled_for_product($product->get_id())) {
            return;
        }
        
        // Check if product is purchasable and in stock
        if (!$product->is_purchasable() || !$product->is_in_stock()) {
            return;
        }
        
        $calculator_page_id = get_option('ms_installment_calculator_page');
        if (!$calculator_page_id) {
            return;
        }
        
        $calculator_url = add_query_arg(array(
            'product_id' => $product->get_id(),
            'product_name' => urlencode($product->get_name()),
            'product_price' => $product->get_price()
        ), get_permalink($calculator_page_id));
        
        echo '<a target="_blank" href="' . esc_url($calculator_url) . '" class="button ms-installment-button woocommerce-button">';
        echo __('Easy 3 Installments', 'ms-installment');
        echo '</a>';
    }
    
    /**
     * Check if installment is enabled for a product
     */
    private function is_installment_enabled_for_product($product_id) {
        $show_all = get_option('ms_installment_show_all_products', 'selected');
        
        if ($show_all === 'all') {
            return true;
        }
        
        $enabled = get_post_meta($product_id, '_ms_installment_enabled', true);
        return $enabled === 'yes';
    }
    
    /**
     * Installment calculator shortcode
     */
    public function installment_calculator_shortcode($atts) {
        $atts = shortcode_atts(array(
            'product_id' => '',
            'product_name' => '',
            'product_price' => ''
        ), $atts);
        
        // Get values from URL if not provided in shortcode
        if (empty($atts['product_id']) && isset($_GET['product_id'])) {
            $atts['product_id'] = intval($_GET['product_id']);
        }
        if (empty($atts['product_name']) && isset($_GET['product_name'])) {
            $atts['product_name'] = sanitize_text_field($_GET['product_name']);
        }
        if (empty($atts['product_price']) && isset($_GET['product_price'])) {
            $atts['product_price'] = floatval($_GET['product_price']);
        }
        
        ob_start();
        require MS_INSTALLMENT_PLUGIN_PATH . 'templates/frontend/calculator-form.php';
        return ob_get_clean();
    }
    
    /**
     * Add product meta box
     */
    public function add_product_meta_box() {
        // Only show meta box if user has permission
        if (!MS_Installment_Permissions::user_can_manage_installments()) {
            return;
        }
        
        add_meta_box(
            'ms-installment-product-settings',
            __('Installment Settings', 'ms-installment'),
            array($this, 'product_meta_box_callback'),
            'product',
            'side',
            'default'
        );
    }
    
    /**
     * Product meta box callback
     */
    public function product_meta_box_callback($post) {
        // Check permissions
        if (!MS_Installment_Permissions::user_can_manage_installments()) {
            echo '<p>' . MS_Installment_Permissions::get_permission_error_message() . '</p>';
            return;
        }
        
        wp_nonce_field('ms_installment_product_meta_box', 'ms_installment_product_meta_box_nonce');
        
        $enabled = get_post_meta($post->ID, '_ms_installment_enabled', true);
        
        echo '<p>';
        echo '<label>';
        echo '<input type="checkbox" name="ms_installment_enabled" value="yes" ' . checked($enabled, 'yes', false) . ' />';
        echo __('Enable installment for this product', 'ms-installment');
        echo '</label>';
        echo '</p>';
    }
    
    /**
     * Save product meta box
     */
    public function save_product_meta_box($post_id) {
        // Check permissions
        if (!MS_Installment_Permissions::user_can_manage_installments()) {
            return;
        }
        
        if (!isset($_POST['ms_installment_product_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['ms_installment_product_meta_box_nonce'], 'ms_installment_product_meta_box')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        $enabled = isset($_POST['ms_installment_enabled']) ? 'yes' : 'no';
        update_post_meta($post_id, '_ms_installment_enabled', $enabled);
    }
    
    /**
     * Handle application submission
     */
    public function handle_application_submission() {
        // Check nonce from form field
        if (!wp_verify_nonce($_POST['ms_installment_nonce'], 'ms_installment_frontend_nonce')) {
            wp_send_json(array('success' => false, 'message' => __('Security check failed. Please try again.', 'ms-installment')));
        }
        
        $response = array('success' => false, 'message' => '');
        
        // Validate required fields
        $required_fields = array(
            'product_name', 'product_price', 'is_lahore_resident', 
            'residence', 'profession', 'has_bank_account', 
            'can_provide_guarantor', 'has_cnic', 'customer_name',
            'customer_email', 'customer_phone', 'customer_address', 'terms_agreed',
            'captcha_input', 'captcha_answer'
        );
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $response['message'] = sprintf(__('Please fill in all required fields. Missing: %s', 'ms-installment'), $field);
                wp_send_json($response);
            }
        }
        
        // Validate email format
        if (!is_email($_POST['customer_email'])) {
            $response['message'] = __('Please enter a valid email address.', 'ms-installment');
            wp_send_json($response);
        }
        
        // Validate terms agreement
        if ($_POST['terms_agreed'] !== 'yes') {
            $response['message'] = __('You must agree to the terms and conditions.', 'ms-installment');
            wp_send_json($response);
        }
        
        // Validate CAPTCHA
        if (empty($_POST['captcha_input']) || empty($_POST['captcha_answer'])) {
            $response['message'] = __('Please complete the security verification.', 'ms-installment');
            wp_send_json($response);
        }
        
        if (intval($_POST['captcha_input']) !== intval($_POST['captcha_answer'])) {
            $response['message'] = __('Incorrect security answer. Please try again.', 'ms-installment');
            wp_send_json($response);
        }
        
        // Validate Lahore resident requirement
        if ($_POST['is_lahore_resident'] === 'no') {
            $response['message'] = __('Sorry, this installment service is only available for residents of Lahore.', 'ms-installment');
            wp_send_json($response);
        }
        
        // Save application
        global $wpdb;
        $table_name = $wpdb->prefix . 'ms_installment_applications';
        
        // Get the next application ID starting from 100000
        $next_id = get_option('ms_installment_next_id', 100000);
        
        $data = array(
            'id' => $next_id, // Set the custom ID
            'product_name' => sanitize_text_field($_POST['product_name']),
            'product_price' => floatval($_POST['product_price']),
            'processing_fee_percentage' => floatval(get_option('ms_installment_processing_fee', 5)),
            'processing_fee' => floatval($_POST['processing_fee']),
            'total_amount' => floatval($_POST['total_amount']),
            'installment_amount' => floatval($_POST['installment_amount']),
            'is_lahore_resident' => sanitize_text_field($_POST['is_lahore_resident']),
            'residence' => sanitize_text_field($_POST['residence']),
            'profession' => sanitize_text_field($_POST['profession']),
            'has_bank_account' => sanitize_text_field($_POST['has_bank_account']),
            'can_provide_guarantor' => sanitize_text_field($_POST['can_provide_guarantor']),
            'has_cnic' => sanitize_text_field($_POST['has_cnic']),
            'customer_name' => sanitize_text_field($_POST['customer_name']),
            'customer_email' => sanitize_email($_POST['customer_email']),
            'customer_phone' => sanitize_text_field($_POST['customer_phone']),
            'customer_address' => sanitize_textarea_field($_POST['customer_address']),
            'status' => 'pending',
            'created_at' => current_time('mysql')
        );
        
        $result = $wpdb->insert($table_name, $data);
        
        if ($result) {
            // Increment the next ID for future applications
            update_option('ms_installment_next_id', $next_id + 1);
            
            // Send email notifications
            $application_id = $next_id;
            $application = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                $application_id
            ));
            
            if ($application) {
                // Send admin notification
                MS_Installment_Email::send_admin_new_application_email($application);
                
                // Send customer confirmation
                MS_Installment_Email::send_customer_application_submitted_email($application);
            }
            
            $response['success'] = true;
            $response['message'] = __('Application submitted successfully! If 8 points are ok then we will accept the request and we will deliver the said goods in 72 hours minimum. After verification process', 'ms-installment');
        } else {
            $response['message'] = __('Error submitting application. Please try again.', 'ms-installment');
        }
        
        wp_send_json($response);
    }
    
    /**
     * Handle CAPTCHA refresh
     */
    public function handle_captcha_refresh() {
        check_ajax_referer('ms_installment_frontend_nonce', 'nonce');
        
        // Generate new CAPTCHA question
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $answer = $num1 + $num2;
        $question = $num1 . ' + ' . $num2 . ' = ?';
        
        wp_send_json_success(array(
            'question' => $question,
            'answer' => $answer
        ));
    }

    /**
     * Deactivate plugin hook
     */
    public function deactivate_plugin() {
        // Remove capabilities added by the plugin
        MS_Installment_Permissions::remove_capabilities();
    }
    
    /**
     * Activate plugin hook
     */
    public function activate_plugin() {
        // Initialize database
        $this->init_database();
        
        // Set default options if they don't exist
        if (get_option('ms_installment_processing_fee') === false) {
            add_option('ms_installment_processing_fee', 5);
        }
        
        if (get_option('ms_installment_show_all_products') === false) {
            add_option('ms_installment_show_all_products', 'selected');
        }
        
        if (get_option('ms_installment_email_settings') === false) {
            $default_email_settings = array(
                'admin_new_application' => 'yes',
                'customer_application_submitted' => 'yes',
                'customer_application_approved' => 'yes',
                'customer_application_rejected' => 'yes',
                'admin_email' => get_option('admin_email'),
                'contact_email' => get_option('admin_email'),
                'contact_phone' => ''
            );
            add_option('ms_installment_email_settings', $default_email_settings);
        }
        
        if (get_option('ms_installment_enabled_roles') === false) {
            add_option('ms_installment_enabled_roles', array('administrator'));
        }
    }

    /**
     * Initialize database tables and options
     */
    public function init_database() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ms_installment_applications';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                product_name varchar(255) NOT NULL,
                product_price decimal(10,2) NOT NULL,
                processing_fee_percentage decimal(5,2) NOT NULL,
                processing_fee decimal(10,2) NOT NULL,
                total_amount decimal(10,2) NOT NULL,
                installment_amount decimal(10,2) NOT NULL,
                is_lahore_resident varchar(10) NOT NULL,
                residence varchar(255) NOT NULL,
                profession varchar(255) NOT NULL,
                has_bank_account varchar(10) NOT NULL,
                can_provide_guarantor varchar(10) NOT NULL,
                has_cnic varchar(255) NOT NULL,
                customer_name varchar(255) NOT NULL,
                customer_email varchar(255) NOT NULL,
                customer_phone varchar(255) NOT NULL,
                customer_address text NOT NULL,
                status varchar(50) NOT NULL DEFAULT 'pending',
                admin_notes text,
                created_at datetime NOT NULL,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset_collate;";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            // Set the next ID to 100000 if it doesn't exist
            if (get_option('ms_installment_next_id') === false) {
                add_option('ms_installment_next_id', 100000);
            }
        } else {
            // Check if we need to migrate existing applications
            $this->migrate_existing_applications();
        }
    }
    
    /**
     * Migrate existing applications to use proper ID numbering
     */
    private function migrate_existing_applications() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ms_installment_applications';
        
        // Check if next_id option exists
        if (get_option('ms_installment_next_id') === false) {
            // Get the highest existing ID
            $max_id = $wpdb->get_var("SELECT MAX(id) FROM $table_name");
            
            if ($max_id && $max_id < 100000) {
                // We have existing applications with low IDs, need to migrate
                $this->migrate_application_ids();
            } else {
                // Set next ID to 100000 or max_id + 1, whichever is higher
                $next_id = max(100000, ($max_id ? $max_id + 1 : 100000));
                add_option('ms_installment_next_id', $next_id);
            }
        }
    }
    
    /**
     * Migrate application IDs to start from 100000
     */
    public function migrate_application_ids() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ms_installment_applications';
        
        // Get all applications ordered by creation date
        $applications = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at ASC");
        
        if (!empty($applications)) {
            $new_id = 100000;
            
            foreach ($applications as $application) {
                // Update the application ID
                $wpdb->update(
                    $table_name,
                    array('id' => $new_id),
                    array('id' => $application->id),
                    array('%d'),
                    array('%d')
                );
                $new_id++;
            }
            
            // Set the next ID for future applications
            update_option('ms_installment_next_id', $new_id);
        } else {
            // No applications exist, set to 100000
            add_option('ms_installment_next_id', 100000);
        }
    }
} 