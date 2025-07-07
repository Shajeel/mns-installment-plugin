<?php
/**
 * Settings page template for MS Installment Plugin
 */

// Ensure we have access to WordPress globals
global $wpdb;

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Check permissions
if (!MS_Installment_Permissions::user_can_manage_settings()) {
    wp_die(__('You do not have permission to access this page.', 'ms-installment'));
}

// Load email class
require_once MS_INSTALLMENT_PLUGIN_PATH . 'includes/class-ms-installment-email.php';

// Handle form submission
if (isset($_POST['ms_installment_settings_nonce']) && wp_verify_nonce($_POST['ms_installment_settings_nonce'], 'ms_installment_settings')) {
    $processing_fee = floatval($_POST['processing_fee']);
    
    // Validate processing fee
    if ($processing_fee < 0 || $processing_fee > 100) {
        echo '<div class="notice notice-error"><p>' . __('Processing fee must be between 0% and 100%.', 'ms-installment') . '</p></div>';
    } else {
        $show_all_products = sanitize_text_field($_POST['show_all_products']);
        $calculator_page = intval($_POST['calculator_page']);
        
        // Email settings
        $email_settings = array(
            'admin_new_application' => isset($_POST['admin_new_application']) ? 'yes' : 'no',
            'customer_application_submitted' => isset($_POST['customer_application_submitted']) ? 'yes' : 'no',
            'customer_application_approved' => isset($_POST['customer_application_approved']) ? 'yes' : 'no',
            'customer_application_rejected' => isset($_POST['customer_application_rejected']) ? 'yes' : 'no',
            'admin_email' => sanitize_email($_POST['admin_email']),
            'contact_email' => sanitize_email($_POST['contact_email']),
            'contact_phone' => sanitize_text_field($_POST['contact_phone'])
        );
        
        // Role permissions
        $enabled_roles = isset($_POST['enabled_roles']) ? array_map('sanitize_text_field', $_POST['enabled_roles']) : array('administrator');
        
        update_option('ms_installment_processing_fee', $processing_fee);
        update_option('ms_installment_show_all_products', $show_all_products);
        update_option('ms_installment_calculator_page', $calculator_page);
        update_option('ms_installment_email_settings', $email_settings);
        
        // Save role permissions
        MS_Installment_Permissions::save_enabled_roles($enabled_roles);
        
        echo '<div class="notice notice-success"><p>' . __('Settings saved successfully.', 'ms-installment') . '</p></div>';
    }
}

// Handle test email
if (isset($_POST['ms_installment_test_email_nonce']) && wp_verify_nonce($_POST['ms_installment_test_email_nonce'], 'ms_installment_test_email')) {
    $test_email = sanitize_email($_POST['test_email']);
    if (!empty($test_email)) {
        require_once MS_INSTALLMENT_PLUGIN_PATH . 'includes/class-ms-installment-email.php';
        $result = MS_Installment_Email::test_email($test_email);
        if ($result) {
            echo '<div class="notice notice-success"><p>' . __('Test email sent successfully!', 'ms-installment') . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . __('Failed to send test email. Please check your email configuration.', 'ms-installment') . '</p></div>';
        }
    }
}

// Handle reset numbering
if (isset($_POST['ms_installment_reset_nonce']) && wp_verify_nonce($_POST['ms_installment_reset_nonce'], 'ms_installment_reset_numbering')) {
    if (isset($_POST['reset_numbering'])) {
        // Call the migration function
        global $ms_installment_plugin;
        if (method_exists($ms_installment_plugin, 'migrate_application_ids')) {
            $ms_installment_plugin->migrate_application_ids();
            echo '<div class="notice notice-success"><p>' . __('Application numbering has been reset successfully.', 'ms-installment') . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . __('Error: Migration function not found.', 'ms-installment') . '</p></div>';
        }
    }
}

// Get current settings
$processing_fee = get_option('ms_installment_processing_fee', 5);

// Fix processing fee if it's unreasonable
if ($processing_fee > 100 || $processing_fee < 0) {
    update_option('ms_installment_processing_fee', 5);
    $processing_fee = 5;
    echo '<div class="notice notice-warning"><p>' . __('Processing fee was automatically corrected to 5%.', 'ms-installment') . '</p></div>';
}

$show_all_products = get_option('ms_installment_show_all_products', 'selected');
$calculator_page = get_option('ms_installment_calculator_page', '');
$email_settings = MS_Installment_Email::get_email_settings();

// Get pages for dropdown
$pages = get_pages(array('sort_column' => 'post_title'));
?>

<div class="wrap">
    <h1><?php _e('M&S Installment Plugin Settings', 'ms-installment'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('ms_installment_settings', 'ms_installment_settings_nonce'); ?>
        
        <h2><?php _e('General Settings', 'ms-installment'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="processing_fee"><?php _e('Processing Fee (%)', 'ms-installment'); ?></label>
                </th>
                <td>
                    <input type="number" id="processing_fee" name="processing_fee" value="<?php echo esc_attr($processing_fee); ?>" min="0" max="100" step="0.1" class="regular-text">
                    <p class="description"><?php _e('The processing fee percentage that will be added to the product price for installment calculations. For example, 5% means a 5% fee will be added to the product price.', 'ms-installment'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="show_all_products"><?php _e('Show Installment Button', 'ms-installment'); ?></label>
                </th>
                <td>
                    <select id="show_all_products" name="show_all_products">
                        <option value="all" <?php selected($show_all_products, 'all'); ?>><?php _e('On all products', 'ms-installment'); ?></option>
                        <option value="selected" <?php selected($show_all_products, 'selected'); ?>><?php _e('Only on selected products', 'ms-installment'); ?></option>
                    </select>
                    <p class="description">
                        <?php _e('Choose whether to show the "Buy on Installments" button on all products or only on products where you enable it individually.', 'ms-installment'); ?>
                        <?php if ($show_all_products === 'selected'): ?>
                            <br><strong><?php _e('Note:', 'ms-installment'); ?></strong> <?php _e('You can enable installment for individual products in the product edit page.', 'ms-installment'); ?>
                        <?php endif; ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="calculator_page"><?php _e('Calculator Page', 'ms-installment'); ?></label>
                </th>
                <td>
                    <select id="calculator_page" name="calculator_page">
                        <option value=""><?php _e('-- Select a page --', 'ms-installment'); ?></option>
                        <?php foreach ($pages as $page): ?>
                            <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($calculator_page, $page->ID); ?>>
                                <?php echo esc_html($page->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">
                        <?php _e('Select the page where the installment calculator shortcode [ms_installment_calculator] is placed.', 'ms-installment'); ?>
                        <?php if ($calculator_page): ?>
                            <br><a href="<?php echo get_edit_post_link($calculator_page); ?>" target="_blank"><?php _e('Edit this page', 'ms-installment'); ?></a>
                        <?php endif; ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <h2><?php _e('Email Settings', 'ms-installment'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Email Notifications', 'ms-installment'); ?></th>
                <td>
                    <fieldset>
                        <label>
                            <input type="checkbox" name="admin_new_application" value="yes" <?php checked($email_settings['admin_new_application'], 'yes'); ?>>
                            <?php _e('Send email to admin when new application is submitted', 'ms-installment'); ?>
                        </label><br>
                        
                        <label>
                            <input type="checkbox" name="customer_application_submitted" value="yes" <?php checked($email_settings['customer_application_submitted'], 'yes'); ?>>
                            <?php _e('Send confirmation email to customer when application is submitted', 'ms-installment'); ?>
                        </label><br>
                        
                        <label>
                            <input type="checkbox" name="customer_application_approved" value="yes" <?php checked($email_settings['customer_application_approved'], 'yes'); ?>>
                            <?php _e('Send email to customer when application is approved', 'ms-installment'); ?>
                        </label><br>
                        
                        <label>
                            <input type="checkbox" name="customer_application_rejected" value="yes" <?php checked($email_settings['customer_application_rejected'], 'yes'); ?>>
                            <?php _e('Send email to customer when application is rejected', 'ms-installment'); ?>
                        </label>
                    </fieldset>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="admin_email"><?php _e('Admin Email', 'ms-installment'); ?></label>
                </th>
                <td>
                    <input type="email" id="admin_email" name="admin_email" value="<?php echo esc_attr($email_settings['admin_email']); ?>" class="regular-text">
                    <p class="description"><?php _e('Email address where admin notifications will be sent.', 'ms-installment'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="contact_email"><?php _e('Contact Email', 'ms-installment'); ?></label>
                </th>
                <td>
                    <input type="email" id="contact_email" name="contact_email" value="<?php echo esc_attr($email_settings['contact_email']); ?>" class="regular-text">
                    <p class="description"><?php _e('Email address that will be shown to customers in email templates.', 'ms-installment'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="contact_phone"><?php _e('Contact Phone', 'ms-installment'); ?></label>
                </th>
                <td>
                    <input type="text" id="contact_phone" name="contact_phone" value="<?php echo esc_attr($email_settings['contact_phone']); ?>" class="regular-text">
                    <p class="description"><?php _e('Phone number that will be shown to customers in email templates.', 'ms-installment'); ?></p>
                </td>
            </tr>
        </table>
        
        <h2><?php _e('Role Permissions', 'ms-installment'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Access Control', 'ms-installment'); ?></th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><?php _e('Role Permissions', 'ms-installment'); ?></legend>
                        <p><?php _e('Select which user roles can access the installment applications and manage them:', 'ms-installment'); ?></p>
                        
                        <?php 
                        $available_roles = MS_Installment_Permissions::get_available_roles();
                        $enabled_roles = MS_Installment_Permissions::get_enabled_roles();
                        ?>
                        
                        <?php foreach ($available_roles as $role_name => $role_display_name): ?>
                            <label>
                                <input type="checkbox" name="enabled_roles[]" value="<?php echo esc_attr($role_name); ?>" 
                                       <?php checked(in_array($role_name, $enabled_roles)); ?>>
                                <strong><?php echo esc_html($role_display_name); ?></strong>
                                <?php if ($role_name === 'administrator'): ?>
                                    <em>(<?php _e('Always enabled', 'ms-installment'); ?>)</em>
                                <?php endif; ?>
                            </label><br>
                        <?php endforeach; ?>
                        
                        <p class="description">
                            <?php _e('Users with these roles will be able to view and manage installment applications.', 'ms-installment'); ?>
                            <br>
                            <strong><?php _e('Note:', 'ms-installment'); ?></strong> <?php _e('Only administrators can access plugin settings.', 'ms-installment'); ?>
                        </p>
                    </fieldset>
                </td>
            </tr>
        </table>
        
        <h2><?php _e('Current Users & Permissions', 'ms-installment'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Users with Access', 'ms-installment'); ?></th>
                <td>
                    <?php 
                    $enabled_roles = MS_Installment_Permissions::get_enabled_roles();
                    $users_with_access = get_users(array(
                        'role__in' => $enabled_roles,
                        'orderby' => 'display_name'
                    ));
                    ?>
                    
                    <?php if (!empty($users_with_access)): ?>
                        <div class="ms-users-list">
                            <table class="widefat">
                                <thead>
                                    <tr>
                                        <th><?php _e('User', 'ms-installment'); ?></th>
                                        <th><?php _e('Role', 'ms-installment'); ?></th>
                                        <th><?php _e('Email', 'ms-installment'); ?></th>
                                        <th><?php _e('Can Access', 'ms-installment'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users_with_access as $user): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo esc_html($user->display_name); ?></strong>
                                                <br>
                                                <small><?php echo esc_html($user->user_login); ?></small>
                                            </td>
                                            <td>
                                                <?php 
                                                $role_name = MS_Installment_Permissions::get_user_role_display_name($user->ID);
                                                echo esc_html($role_name);
                                                ?>
                                            </td>
                                            <td><?php echo esc_html($user->user_email); ?></td>
                                            <td>
                                                <?php if (MS_Installment_Permissions::user_can_manage_installments($user->ID)): ?>
                                                    <span class="dashicons dashicons-yes-alt" style="color: #28a745;"></span>
                                                    <?php _e('Yes', 'ms-installment'); ?>
                                                <?php else: ?>
                                                    <span class="dashicons dashicons-no-alt" style="color: #dc3545;"></span>
                                                    <?php _e('No', 'ms-installment'); ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p><?php _e('No users found with access to the installment plugin.', 'ms-installment'); ?></p>
                    <?php endif; ?>
                    
                    <p class="description">
                        <?php _e('This table shows all users who have access to the installment plugin based on their roles.', 'ms-installment'); ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <h2><?php _e('Database Management', 'ms-installment'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Application Numbering', 'ms-installment'); ?></th>
                <td>
                    <?php 
                    $next_id = get_option('ms_installment_next_id', 100000);
                    $total_applications = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ms_installment_applications");
                    ?>
                    
                    <p><strong><?php _e('Next Application ID:', 'ms-installment'); ?></strong> <?php echo esc_html($next_id); ?></p>
                    <p><strong><?php _e('Total Applications:', 'ms-installment'); ?></strong> <?php echo esc_html($total_applications); ?></p>
                    
                    <form method="post" action="" style="margin-top: 15px;">
                        <?php wp_nonce_field('ms_installment_reset_numbering', 'ms_installment_reset_nonce'); ?>
                        <p class="description">
                            <?php _e('If you need to reset the application numbering to start from 100000, use this option. This will renumber all existing applications.', 'ms-installment'); ?>
                        </p>
                        <input type="submit" name="reset_numbering" class="button button-secondary" value="<?php _e('Reset Application Numbering', 'ms-installment'); ?>" 
                               onclick="return confirm('<?php _e('Are you sure? This will renumber all existing applications starting from 100000.', 'ms-installment'); ?>')">
                    </form>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Settings', 'ms-installment'); ?>">
        </p>
    </form>
    
    <hr>
    
    <h2><?php _e('Test Email Functionality', 'ms-installment'); ?></h2>
    <form method="post" action="">
        <?php wp_nonce_field('ms_installment_test_email', 'ms_installment_test_email_nonce'); ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="test_email"><?php _e('Test Email Address', 'ms-installment'); ?></label>
                </th>
                <td>
                    <input type="email" id="test_email" name="test_email" value="<?php echo esc_attr(get_option('admin_email')); ?>" class="regular-text">
                    <p class="description"><?php _e('Enter an email address to test the email functionality.', 'ms-installment'); ?></p>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="test_email_submit" class="button" value="<?php _e('Send Test Email', 'ms-installment'); ?>">
        </p>
    </form>
    
    <hr>
    
    <h2><?php _e('Shortcode Usage', 'ms-installment'); ?></h2>
    <p><?php _e('Use the following shortcode to display the installment calculator on any page or post:', 'ms-installment'); ?></p>
    <code>[ms_installment_calculator]</code>
    
    <h3><?php _e('Shortcode Parameters', 'ms-installment'); ?></h3>
    <p><?php _e('You can also pre-fill the calculator with product information:', 'ms-installment'); ?></p>
    <code>[ms_installment_calculator product_name="Product Name" product_price="10000"]</code>
    
    <hr>
    
    <h2><?php _e('Product Settings', 'ms-installment'); ?></h2>
    <p><?php _e('To enable installment for individual products:', 'ms-installment'); ?></p>
    <ol>
        <li><?php _e('Go to Products → All Products', 'ms-installment'); ?></li>
        <li><?php _e('Edit a product', 'ms-installment'); ?></li>
        <li><?php _e('Look for the "Installment Settings" meta box in the sidebar', 'ms-installment'); ?></li>
        <li><?php _e('Check "Enable installment for this product"', 'ms-installment'); ?></li>
        <li><?php _e('Update the product', 'ms-installment'); ?></li>
    </ol>
    
    <hr>
    
    <h2><?php _e('Installment Button', 'ms-installment'); ?></h2>
    <p><?php _e('The "Buy on Installments" button will appear on product pages for:', 'ms-installment'); ?></p>
    <ul>
        <?php if ($show_all_products === 'all'): ?>
            <li><?php _e('All products (current setting)', 'ms-installment'); ?></li>
        <?php else: ?>
            <li><?php _e('Only products where you have enabled installment individually', 'ms-installment'); ?></li>
        <?php endif; ?>
    </ul>
    
    <p><?php _e('The button will link to the calculator page with the product information pre-filled.', 'ms-installment'); ?></p>
</div> 