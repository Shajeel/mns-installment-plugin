<?php
/**
 * Admin functionality class
 */
class MS_Installment_Admin {
    
    public function __construct() {
        add_action('wp_ajax_ms_update_application_status', array($this, 'update_application_status'));
        add_action('wp_ajax_ms_save_admin_notes', array($this, 'save_admin_notes'));
        add_action('wp_ajax_ms_get_application_details', array($this, 'get_application_details'));
        add_action('wp_ajax_ms_bulk_delete_applications', array($this, 'bulk_delete_applications'));
    }
    
    /**
     * Update application status
     */
    public function update_application_status() {
        check_ajax_referer('ms_installment_admin_nonce', 'nonce');
        
        if (!MS_Installment_Permissions::user_can_edit_applications()) {
            wp_send_json_error(MS_Installment_Permissions::get_permission_error_message());
        }
        
        $application_id = intval($_POST['application_id']);
        $status = sanitize_text_field($_POST['status']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ms_installment_applications';
        
        // Get current application data
        $current_application = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $application_id
        ));
        
        if (!$current_application) {
            wp_send_json_error('Application not found');
        }
        
        $result = $wpdb->update(
            $table_name,
            array(
                'status' => $status,
                'updated_at' => current_time('mysql')
            ),
            array('id' => $application_id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($result !== false) {
            // Send email notification to customer based on status
            $updated_application = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                $application_id
            ));
            
            if ($updated_application) {
                switch ($status) {
                    case 'approved':
                        MS_Installment_Email::send_customer_application_approved_email($updated_application);
                        break;
                    case 'rejected':
                        MS_Installment_Email::send_customer_application_rejected_email($updated_application);
                        break;
                }
            }
            
            wp_send_json_success('Status updated successfully');
        } else {
            wp_send_json_error('Error updating status');
        }
    }
    
    /**
     * Save admin notes
     */
    public function save_admin_notes() {
        check_ajax_referer('ms_installment_admin_nonce', 'nonce');
        
        if (!MS_Installment_Permissions::user_can_edit_applications()) {
            wp_send_json_error(MS_Installment_Permissions::get_permission_error_message());
        }
        
        $application_id = intval($_POST['application_id']);
        $notes = sanitize_textarea_field($_POST['notes']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ms_installment_applications';
        
        $result = $wpdb->update(
            $table_name,
            array(
                'admin_notes' => $notes,
                'updated_at' => current_time('mysql')
            ),
            array('id' => $application_id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success('Notes saved successfully');
        } else {
            wp_send_json_error('Error saving notes');
        }
    }
    
    /**
     * Bulk delete applications
     */
    public function bulk_delete_applications() {
        check_ajax_referer('ms_installment_admin_nonce', 'nonce');
        
        if (!MS_Installment_Permissions::user_can_delete_applications()) {
            wp_send_json_error(MS_Installment_Permissions::get_permission_error_message());
        }
        
        if (empty($_POST['application_ids']) || !is_array($_POST['application_ids'])) {
            wp_send_json_error('No applications selected');
        }
        
        $application_ids = array_map('intval', $_POST['application_ids']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ms_installment_applications';
        
        // Build the WHERE clause for multiple IDs
        $placeholders = array_fill(0, count($application_ids), '%d');
        $where_clause = 'id IN (' . implode(', ', $placeholders) . ')';
        
        $result = $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name WHERE $where_clause",
            $application_ids
        ));
        
        if ($result !== false) {
            $deleted_count = count($application_ids);
            wp_send_json_success("Successfully deleted $deleted_count application(s)");
        } else {
            wp_send_json_error('Error deleting applications');
        }
    }
    
    /**
     * Get application details
     */
    public function get_application_details() {
        check_ajax_referer('ms_installment_admin_nonce', 'nonce');
        
        if (!MS_Installment_Permissions::user_can_edit_applications()) {
            wp_send_json_error(MS_Installment_Permissions::get_permission_error_message());
        }
        
        $application_id = intval($_POST['application_id']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ms_installment_applications';
        
        $application = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $application_id
        ));
        
        if (!$application) {
            wp_send_json_error('Application not found');
        }
        
        // Generate HTML for application details
        $html = $this->generate_application_details_html($application);
        
        wp_send_json_success(array('html' => $html));
    }
    
    /**
     * Generate application details HTML
     */
    private function generate_application_details_html($application) {
        $status_labels = array(
            'pending' => __('Pending', 'ms-installment'),
            'approved' => __('Approved', 'ms-installment'),
            'rejected' => __('Rejected', 'ms-installment')
        );
        
        $status_classes = array(
            'pending' => 'status-pending',
            'approved' => 'status-approved',
            'rejected' => 'status-rejected'
        );
        
        ob_start();
        ?>
        <div class="ms-application-details">
            <div class="ms-application-section">
                <h4><?php _e('Product Information', 'ms-installment'); ?></h4>
                <div class="ms-application-field">
                    <label><?php _e('Product Name', 'ms-installment'); ?></label>
                    <div class="value"><?php echo esc_html($application->product_name); ?></div>
                </div>
                <div class="ms-application-field">
                    <label><?php _e('Product Price', 'ms-installment'); ?></label>
                    <div class="value"><?php echo MS_Installment_Frontend::format_currency($application->product_price); ?></div>
                </div>
                <div class="ms-application-field">
                    <label><?php _e('Processing Fee', 'ms-installment'); ?></label>
                    <div class="value"><?php echo MS_Installment_Frontend::format_currency($application->processing_fee); ?> (<?php echo esc_html($application->processing_fee_percentage ?? 5); ?>%)</div>
                </div>
                <div class="ms-application-field">
                    <label><?php _e('Total Amount', 'ms-installment'); ?></label>
                    <div class="value"><?php echo MS_Installment_Frontend::format_currency($application->total_amount); ?></div>
                </div>
                <div class="ms-application-field">
                    <label><?php _e('Monthly Installment (3 months)', 'ms-installment'); ?></label>
                    <div class="value"><?php echo MS_Installment_Frontend::format_currency($application->installment_amount); ?></div>
                </div>
            </div>
            
            <div class="ms-application-section">
                <h4><?php _e('Customer Information', 'ms-installment'); ?></h4>
                <div class="ms-application-field">
                    <label><?php _e('Full Name', 'ms-installment'); ?></label>
                    <div class="value"><?php echo esc_html($application->customer_name); ?></div>
                </div>
                <div class="ms-application-field">
                    <label><?php _e('Email Address', 'ms-installment'); ?></label>
                    <div class="value"><?php echo esc_html($application->customer_email); ?></div>
                </div>
                <div class="ms-application-field">
                    <label><?php _e('Phone Number', 'ms-installment'); ?></label>
                    <div class="value"><?php echo esc_html($application->customer_phone); ?></div>
                </div>
                <div class="ms-application-field">
                    <label><?php _e('Address', 'ms-installment'); ?></label>
                    <div class="value"><?php echo esc_html($application->customer_address); ?></div>
                </div>
            </div>
            
            <div class="ms-application-section">
                <h4><?php _e('Eligibility Information', 'ms-installment'); ?></h4>
                <div class="ms-application-field">
                    <label><?php _e('Lahore Resident', 'ms-installment'); ?></label>
                    <div class="value <?php echo $application->is_lahore_resident; ?>"><?php echo $application->is_lahore_resident === 'yes' ? __('Yes', 'ms-installment') : __('No', 'ms-installment'); ?></div>
                </div>
                <div class="ms-application-field">
                    <label><?php _e('Residence Type', 'ms-installment'); ?></label>
                    <div class="value"><?php echo $application->residence === 'own' ? __('Own', 'ms-installment') : __('Rented', 'ms-installment'); ?></div>
                </div>
                <div class="ms-application-field">
                    <label><?php _e('Profession', 'ms-installment'); ?></label>
                    <div class="value"><?php echo $application->profession === 'job' ? __('Job', 'ms-installment') : __('Business', 'ms-installment'); ?></div>
                </div>
                <div class="ms-application-field">
                    <label><?php _e('Bank Account', 'ms-installment'); ?></label>
                    <div class="value <?php echo $application->has_bank_account; ?>"><?php echo $application->has_bank_account === 'yes' ? __('Yes', 'ms-installment') : __('No', 'ms-installment'); ?></div>
                </div>
                <div class="ms-application-field">
                    <label><?php _e('Can Provide Guarantor', 'ms-installment'); ?></label>
                    <div class="value <?php echo $application->can_provide_guarantor; ?>"><?php echo $application->can_provide_guarantor === 'yes' ? __('Yes', 'ms-installment') : __('No', 'ms-installment'); ?></div>
                </div>
                <div class="ms-application-field">
                    <label><?php _e('Has CNIC', 'ms-installment'); ?></label>
                    <div class="value <?php echo $application->has_cnic; ?>"><?php echo $application->has_cnic === 'yes' ? __('Yes', 'ms-installment') : __('No', 'ms-installment'); ?></div>
                </div>
            </div>
            
            <div class="ms-application-actions">
                <h4><?php _e('Application Actions', 'ms-installment'); ?></h4>
                <div class="ms-application-field">
                    <label><?php _e('Current Status', 'ms-installment'); ?></label>
                    <div class="value">
                        <span class="ms-installment-status <?php echo $status_classes[$application->status]; ?>"><?php echo $status_labels[$application->status]; ?></span>
                    </div>
                </div>
                
                <div class="ms-status-actions">
                    <button type="button" class="ms-status-btn ms-status-pending" data-id="<?php echo esc_attr($application->id); ?>" data-status="pending"><?php _e('Set Pending', 'ms-installment'); ?></button>
                    <button type="button" class="ms-status-btn ms-status-approve" data-id="<?php echo esc_attr($application->id); ?>" data-status="approved"><?php _e('Approve', 'ms-installment'); ?></button>
                    <button type="button" class="ms-status-btn ms-status-reject" data-id="<?php echo esc_attr($application->id); ?>" data-status="rejected"><?php _e('Reject', 'ms-installment'); ?></button>
                </div>
                
                <div class="ms-admin-notes">
                    <h4><?php _e('Admin Notes', 'ms-installment'); ?></h4>
                    <textarea placeholder="<?php _e('Add notes about this application...', 'ms-installment'); ?>"><?php echo esc_textarea($application->admin_notes); ?></textarea>
                    <button type="button" class="ms-save-notes" data-id="<?php echo esc_attr($application->id); ?>"><?php _e('Save Notes', 'ms-installment'); ?></button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get applications with filters
     */
    public static function get_applications($filters = array()) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ms_installment_applications';
        
        $where_clauses = array();
        $where_values = array();
        
        // Status filter
        if (!empty($filters['status'])) {
            $where_clauses[] = 'status = %s';
            $where_values[] = $filters['status'];
        }
        
        // Date range filter
        if (!empty($filters['date_from'])) {
            $where_clauses[] = 'created_at >= %s';
            $where_values[] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $where_clauses[] = 'created_at <= %s';
            $where_values[] = $filters['date_to'] . ' 23:59:59';
        }
        
        // Search filter
        if (!empty($filters['search'])) {
            $search_term = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where_clauses[] = '(product_name LIKE %s OR customer_name LIKE %s OR customer_email LIKE %s OR customer_phone LIKE %s)';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        }
        
        // Pagination
        $per_page = 20;
        $page = isset($filters['page']) ? max(1, intval($filters['page'])) : 1;
        $offset = ($page - 1) * $per_page;
        
        try {
            // Get total count
            $count_sql = "SELECT COUNT(*) FROM $table_name $where_sql";
            if (!empty($where_values)) {
                $count_sql = $wpdb->prepare($count_sql, $where_values);
            }
            $total = $wpdb->get_var($count_sql);
            
            // Get applications
            $sql = "SELECT * FROM $table_name $where_sql ORDER BY created_at DESC LIMIT %d OFFSET %d";
            $where_values[] = $per_page;
            $where_values[] = $offset;
            
            $applications = $wpdb->get_results($wpdb->prepare($sql, $where_values));
            
            return array(
                'applications' => $applications ? $applications : array(),
                'total' => $total ? intval($total) : 0,
                'per_page' => $per_page,
                'current_page' => $page,
                'total_pages' => ceil(($total ? intval($total) : 0) / $per_page)
            );
        } catch (Exception $e) {
            error_log('MS Installment Plugin Error in get_applications: ' . $e->getMessage());
            return array(
                'applications' => array(),
                'total' => 0,
                'per_page' => $per_page,
                'current_page' => 1,
                'total_pages' => 0
            );
        }
    }
} 