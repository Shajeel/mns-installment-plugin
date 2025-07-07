<?php
/**
 * Admin applications page template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check permissions
if (!MS_Installment_Permissions::user_can_view_applications()) {
    wp_die(MS_Installment_Permissions::get_permission_error_message());
}

// Get filters
$filters = array();
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $filters['status'] = sanitize_text_field($_GET['status']);
}
if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $filters['date_from'] = sanitize_text_field($_GET['date_from']);
}
if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $filters['date_to'] = sanitize_text_field($_GET['date_to']);
}
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filters['search'] = sanitize_text_field($_GET['search']);
}
if (isset($_GET['paged']) && !empty($_GET['paged'])) {
    $filters['page'] = intval($_GET['paged']);
}

// Get applications with error handling
try {
    $applications_data = MS_Installment_Admin::get_applications($filters);
    $applications = $applications_data['applications'];
    $total = $applications_data['total'];
    $current_page = $applications_data['current_page'];
    $total_pages = $applications_data['total_pages'];
} catch (Exception $e) {
    error_log('MS Installment Plugin Error loading applications: ' . $e->getMessage());
    $applications = array();
    $total = 0;
    $current_page = 1;
    $total_pages = 0;
}
?>

<div class="wrap">
    <h1><?php _e('M&S Installment Applications', 'ms-installment'); ?></h1>
    
    <!-- Filters -->
    <div class="ms-installment-filters">
        <form method="get" action="">
            <input type="hidden" name="page" value="ms-installment-applications">
            
            <div class="ms-installment-filter-row">
                <div class="ms-installment-filter-group">
                    <label for="status"><?php _e('Status:', 'ms-installment'); ?></label>
                    <select name="status" id="status">
                        <option value=""><?php _e('All Statuses', 'ms-installment'); ?></option>
                        <option value="pending" <?php selected($filters['status'] ?? '', 'pending'); ?>><?php _e('Pending', 'ms-installment'); ?></option>
                        <option value="approved" <?php selected($filters['status'] ?? '', 'approved'); ?>><?php _e('Approved', 'ms-installment'); ?></option>
                        <option value="rejected" <?php selected($filters['status'] ?? '', 'rejected'); ?>><?php _e('Rejected', 'ms-installment'); ?></option>
                    </select>
                </div>
                
                <div class="ms-installment-filter-group">
                    <label for="date_from"><?php _e('Date From:', 'ms-installment'); ?></label>
                    <input type="date" name="date_from" id="date_from" value="<?php echo esc_attr($filters['date_from'] ?? ''); ?>">
                </div>
                
                <div class="ms-installment-filter-group">
                    <label for="date_to"><?php _e('Date To:', 'ms-installment'); ?></label>
                    <input type="date" name="date_to" id="date_to" value="<?php echo esc_attr($filters['date_to'] ?? ''); ?>">
                </div>
                
                <div class="ms-installment-filter-group">
                    <label for="search"><?php _e('Search:', 'ms-installment'); ?></label>
                    <input type="text" name="search" id="search" value="<?php echo esc_attr($filters['search'] ?? ''); ?>" placeholder="<?php _e('Product name, customer name, email, phone', 'ms-installment'); ?>">
                </div>
                
                <div class="ms-installment-filter-group">
                    <button type="submit" class="button"><?php _e('Filter', 'ms-installment'); ?></button>
                    <a href="?page=ms-installment-applications" class="button"><?php _e('Clear', 'ms-installment'); ?></a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Applications Table -->
    <div class="ms-installment-applications-table">
        <?php if (empty($applications)): ?>
            <p><?php _e('No applications found.', 'ms-installment'); ?></p>
        <?php else: ?>
            <form id="ms-bulk-actions-form" method="post">
                <div class="ms-bulk-actions">
                    <select name="bulk_action" id="bulk_action">
                        <option value=""><?php _e('Bulk Actions', 'ms-installment'); ?></option>
                        <option value="delete"><?php _e('Delete Selected', 'ms-installment'); ?></option>
                    </select>
                    <button type="button" id="do-bulk-action" class="button"><?php _e('Apply', 'ms-installment'); ?></button>
                </div>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th class="check-column">
                                <input type="checkbox" id="select-all-applications">
                            </th>
                            <th><?php _e('ID', 'ms-installment'); ?></th>
                            <th><?php _e('Product', 'ms-installment'); ?></th>
                            <th><?php _e('Customer', 'ms-installment'); ?></th>
                            <th><?php _e('Total Amount', 'ms-installment'); ?></th>
                            <th><?php _e('Status', 'ms-installment'); ?></th>
                            <th><?php _e('Date', 'ms-installment'); ?></th>
                            <th><?php _e('Actions', 'ms-installment'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $application): ?>
                            <tr>
                                <td class="check-column">
                                    <input type="checkbox" name="application_ids[]" value="<?php echo esc_attr($application->id); ?>" class="application-checkbox">
                                </td>
                                <td><?php echo esc_html($application->id); ?></td>
                                <td>
                                    <strong><?php echo esc_html($application->product_name); ?></strong><br>
                                    <small><?php echo MS_Installment_Frontend::format_currency($application->product_price); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo esc_html($application->customer_name); ?></strong><br>
                                    <small><?php echo esc_html($application->customer_email); ?><br>
                                    <?php echo esc_html($application->customer_phone); ?></small>
                                </td>
                                <td>
                                    <?php echo MS_Installment_Frontend::format_currency($application->total_amount); ?><br>
                                    <small><?php _e('3 x', 'ms-installment'); ?> <?php echo MS_Installment_Frontend::format_currency($application->installment_amount); ?></small>
                                </td>
                                <td>
                                    <span class="ms-installment-status <?php echo MS_Installment_Frontend::get_status_class($application->status); ?>">
                                        <?php echo MS_Installment_Frontend::get_status_label($application->status); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo date_i18n(get_option('date_format'), strtotime($application->created_at)); ?><br>
                                    <small><?php echo date_i18n(get_option('time_format'), strtotime($application->created_at)); ?></small>
                                </td>
                                <td>
                                    <button type="button" class="button button-small ms-view-application" data-id="<?php echo esc_attr($application->id); ?>">
                                        <?php _e('View', 'ms-installment'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="ms-installment-pagination">
                    <?php
                    echo paginate_links(array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => __('&laquo; Previous'),
                        'next_text' => __('Next &raquo;'),
                        'total' => $total_pages,
                        'current' => $current_page
                    ));
                    ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Application Modal -->
<div id="ms-application-modal" class="ms-modal" style="display: none;">
    <div class="ms-modal-content">
        <div class="ms-modal-header">
            <h2><?php _e('Application Details', 'ms-installment'); ?></h2>
            <span class="ms-modal-close">&times;</span>
        </div>
        <div class="ms-modal-body">
            <div id="ms-application-details"></div>
        </div>
    </div>
</div> 