/**
 * MS Installment Plugin Admin JavaScript
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize admin functionality
        initAdminFunctionality();
    });

    /**
     * Initialize admin functionality
     */
    function initAdminFunctionality() {
        // View application modal
        initApplicationModal();
        
        // Status update buttons
        initStatusUpdates();
        
        // Admin notes
        initAdminNotes();
        
        // Bulk actions
        initBulkActions();
    }

    /**
     * Initialize application modal
     */
    function initApplicationModal() {
        const $modal = $('#ms-application-modal');
        const $modalContent = $('.ms-modal-content');
        const $closeBtn = $('.ms-modal-close');
        const $detailsContainer = $('#ms-application-details');

        // Open modal on view button click
        $(document).on('click', '.ms-view-application', function() {
            const applicationId = $(this).data('id');
            loadApplicationDetails(applicationId);
            $modal.show();
        });

        // Close modal on X button
        $closeBtn.on('click', function() {
            $modal.hide();
        });

        // Close modal on outside click
        $modal.on('click', function(e) {
            if (e.target === this) {
                $modal.hide();
            }
        });

        // Close modal on escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $modal.is(':visible')) {
                $modal.hide();
            }
        });
    }

    /**
     * Load application details
     */
    function loadApplicationDetails(applicationId) {
        const $detailsContainer = $('#ms-application-details');
        
        console.log('Loading application details for ID:', applicationId);
        
        // Show loading
        $detailsContainer.html('<div class="ms-loading">Loading application details...</div>');
        
        // Get application details via AJAX
        $.ajax({
            url: ms_installment_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'ms_get_application_details',
                application_id: applicationId,
                nonce: ms_installment_admin.nonce
            },
            success: function(response) {
                console.log('AJAX Response:', response);
                if (response.success) {
                    $detailsContainer.html(response.data.html);
                    console.log('Modal content loaded successfully');
                } else {
                    $detailsContainer.html('<div class="ms-notice error">Error loading application details: ' + (response.data || 'Unknown error') + '</div>');
                    console.log('Error loading application details:', response.data);
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', xhr, status, error);
                $detailsContainer.html('<div class="ms-notice error">Network error. Please try again.</div>');
            }
        });
    }

    /**
     * Initialize status updates
     */
    function initStatusUpdates() {
        $(document).on('click', '.ms-status-btn', function() {
            const $btn = $(this);
            const applicationId = $btn.data('id');
            const newStatus = $btn.data('status');
            
            console.log('Status button clicked:', {
                applicationId: applicationId,
                newStatus: newStatus,
                buttonText: $btn.text(),
                buttonClass: $btn.attr('class')
            });
            
            if (!confirm('Are you sure you want to change the status to "' + newStatus + '"?')) {
                return;
            }
            
            // Show loading
            $btn.prop('disabled', true).text('Updating...');
            
            // Update status via AJAX
            $.ajax({
                url: ms_installment_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'ms_update_application_status',
                    application_id: applicationId,
                    status: newStatus,
                    nonce: ms_installment_admin.nonce
                },
                success: function(response) {
                    console.log('Status update response:', response);
                    if (response.success) {
                        // Update status display
                        updateStatusDisplay(applicationId, newStatus);
                        showNotice('Status updated successfully.', 'success');
                    } else {
                        showNotice('Error updating status: ' + (response.data || 'Unknown error'), 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Status update error:', xhr, status, error);
                    showNotice('Network error. Please try again.', 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                    updateStatusButtonText($btn, newStatus);
                }
            });
        });
    }

    /**
     * Update status display
     */
    function updateStatusDisplay(applicationId, newStatus) {
        const statusLabels = {
            'pending': 'Pending',
            'approved': 'Approved',
            'rejected': 'Rejected'
        };
        
        const statusClasses = {
            'pending': 'status-pending',
            'approved': 'status-approved',
            'rejected': 'status-rejected'
        };
        
        // Update status in table - find the row with the matching application ID
        const $row = $('.application-checkbox[value="' + applicationId + '"]').closest('tr');
        const $statusCell = $row.find('.ms-installment-status');
        
        if ($statusCell.length) {
            $statusCell
                .removeClass('status-pending status-approved status-rejected')
                .addClass(statusClasses[newStatus])
                .text(statusLabels[newStatus]);
        }
        
        // Update status in modal if it's open
        const $modalStatus = $('.ms-application-details .ms-installment-status');
        if ($modalStatus.length) {
            $modalStatus
                .removeClass('status-pending status-approved status-rejected')
                .addClass(statusClasses[newStatus])
                .text(statusLabels[newStatus]);
        }
    }

    /**
     * Update status button text
     */
    function updateStatusButtonText($btn, status) {
        const buttonTexts = {
            'pending': 'Set Pending',
            'approved': 'Approve',
            'rejected': 'Reject'
        };
        
        // Update the button text based on the status it represents
        $btn.text(buttonTexts[status]);
        
        // Update button styling based on current status
        $btn.removeClass('ms-status-pending ms-status-approve ms-status-reject');
        $btn.addClass('ms-status-' + status);
    }

    /**
     * Initialize admin notes
     */
    function initAdminNotes() {
        $(document).on('click', '.ms-save-notes', function() {
            const $btn = $(this);
            const applicationId = $btn.data('id');
            const notes = $btn.closest('.ms-admin-notes').find('textarea').val();
            
            // Show loading
            $btn.prop('disabled', true).text('Saving...');
            
            // Save notes via AJAX
            $.ajax({
                url: ms_installment_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'ms_save_admin_notes',
                    application_id: applicationId,
                    notes: notes,
                    nonce: ms_installment_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showNotice('Notes saved successfully.', 'success');
                    } else {
                        showNotice('Error saving notes.', 'error');
                    }
                },
                error: function() {
                    showNotice('Network error. Please try again.', 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Save Notes');
                }
            });
        });
    }

    /**
     * Show notice
     */
    function showNotice(message, type) {
        const $notice = $('<div class="ms-notice ' + type + '">' + message + '</div>');
        
        // Remove existing notices
        $('.ms-notice').remove();
        
        // Add new notice
        $('.wrap h1').after($notice);
        
        // Auto-hide after 3 seconds
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

    /**
     * Format currency
     */
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-PK', {
            style: 'currency',
            currency: 'PKR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }

    /**
     * Format date
     */
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    /**
     * Generate application details HTML
     */
    function generateApplicationDetailsHTML(application) {
        const statusLabels = {
            'pending': 'Pending',
            'approved': 'Approved',
            'rejected': 'Rejected'
        };
        
        const statusClasses = {
            'pending': 'status-pending',
            'approved': 'status-approved',
            'rejected': 'status-rejected'
        };
        
        return `
            <div class="ms-application-details">
                <div class="ms-application-section">
                    <h4>Product Information</h4>
                    <div class="ms-application-field">
                        <label>Product Name</label>
                        <div class="value">${application.product_name}</div>
                    </div>
                    <div class="ms-application-field">
                        <label>Product Price</label>
                        <div class="value">${formatCurrency(application.product_price)}</div>
                    </div>
                    <div class="ms-application-field">
                        <label>Processing Fee</label>
                        <div class="value">${formatCurrency(application.processing_fee)}</div>
                    </div>
                    <div class="ms-application-field">
                        <label>Total Amount</label>
                        <div class="value">${formatCurrency(application.total_amount)}</div>
                    </div>
                    <div class="ms-application-field">
                        <label>Monthly Installment (3 months)</label>
                        <div class="value">${formatCurrency(application.installment_amount)}</div>
                    </div>
                </div>
                
                <div class="ms-application-section">
                    <h4>Customer Information</h4>
                    <div class="ms-application-field">
                        <label>Full Name</label>
                        <div class="value">${application.customer_name}</div>
                    </div>
                    <div class="ms-application-field">
                        <label>Email Address</label>
                        <div class="value">${application.customer_email}</div>
                    </div>
                    <div class="ms-application-field">
                        <label>Phone Number</label>
                        <div class="value">${application.customer_phone}</div>
                    </div>
                    <div class="ms-application-field">
                        <label>Address</label>
                        <div class="value">${application.customer_address}</div>
                    </div>
                </div>
                
                <div class="ms-application-section">
                    <h4>Eligibility Information</h4>
                    <div class="ms-application-field">
                        <label>Lahore Resident</label>
                        <div class="value ${application.is_lahore_resident}">${application.is_lahore_resident === 'yes' ? 'Yes' : 'No'}</div>
                    </div>
                    <div class="ms-application-field">
                        <label>Residence Type</label>
                        <div class="value">${application.residence === 'own' ? 'Own' : 'Rented'}</div>
                    </div>
                    <div class="ms-application-field">
                        <label>Profession</label>
                        <div class="value">${application.profession === 'job' ? 'Job' : 'Business'}</div>
                    </div>
                    <div class="ms-application-field">
                        <label>Bank Account</label>
                        <div class="value ${application.has_bank_account}">${application.has_bank_account === 'yes' ? 'Yes' : 'No'}</div>
                    </div>
                    <div class="ms-application-field">
                        <label>Can Provide Guarantor</label>
                        <div class="value ${application.can_provide_guarantor}">${application.can_provide_guarantor === 'yes' ? 'Yes' : 'No'}</div>
                    </div>
                    <div class="ms-application-field">
                        <label>Has CNIC</label>
                        <div class="value ${application.has_cnic}">${application.has_cnic === 'yes' ? 'Yes' : 'No'}</div>
                    </div>
                </div>
                
                <div class="ms-application-actions">
                    <h4>Application Actions</h4>
                    <div class="ms-application-field">
                        <label>Current Status</label>
                        <div class="value">
                            <span class="ms-installment-status ${statusClasses[application.status]}">${statusLabels[application.status]}</span>
                        </div>
                    </div>
                    
                    <div class="ms-status-actions">
                        <button type="button" class="ms-status-btn ms-status-pending" data-id="${application.id}" data-status="pending">Set Pending</button>
                        <button type="button" class="ms-status-btn ms-status-approve" data-id="${application.id}" data-status="approved">Approve</button>
                        <button type="button" class="ms-status-btn ms-status-reject" data-id="${application.id}" data-status="rejected">Reject</button>
                    </div>
                    
                    <div class="ms-admin-notes">
                        <h4>Admin Notes</h4>
                        <textarea placeholder="Add notes about this application...">${application.admin_notes || ''}</textarea>
                        <button type="button" class="ms-save-notes" data-id="${application.id}">Save Notes</button>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Initialize bulk actions
     */
    function initBulkActions() {
        // Select all functionality
        $('#select-all-applications').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.application-checkbox').prop('checked', isChecked);
            updateBulkActionButton();
        });
        
        // Individual checkbox change
        $(document).on('change', '.application-checkbox', function() {
            updateSelectAllCheckbox();
            updateBulkActionButton();
        });
        
        // Bulk action button
        $('#do-bulk-action').on('click', function() {
            const action = $('#bulk_action').val();
            const selectedIds = getSelectedApplicationIds();
            
            if (!action) {
                showNotice('Please select a bulk action.', 'error');
                return;
            }
            
            if (selectedIds.length === 0) {
                showNotice('Please select at least one application.', 'error');
                return;
            }
            
            if (action === 'delete') {
                performBulkDelete(selectedIds);
            }
        });
    }
    
    /**
     * Update select all checkbox
     */
    function updateSelectAllCheckbox() {
        const totalCheckboxes = $('.application-checkbox').length;
        const checkedCheckboxes = $('.application-checkbox:checked').length;
        
        if (checkedCheckboxes === 0) {
            $('#select-all-applications').prop('indeterminate', false).prop('checked', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            $('#select-all-applications').prop('indeterminate', false).prop('checked', true);
        } else {
            $('#select-all-applications').prop('indeterminate', true);
        }
    }
    
    /**
     * Update bulk action button
     */
    function updateBulkActionButton() {
        const selectedCount = $('.application-checkbox:checked').length;
        const $button = $('#do-bulk-action');
        
        if (selectedCount > 0) {
            $button.prop('disabled', false);
        } else {
            $button.prop('disabled', true);
        }
    }
    
    /**
     * Get selected application IDs
     */
    function getSelectedApplicationIds() {
        const ids = [];
        $('.application-checkbox:checked').each(function() {
            ids.push($(this).val());
        });
        return ids;
    }
    
    /**
     * Perform bulk delete
     */
    function performBulkDelete(applicationIds) {
        if (!confirm('Are you sure you want to delete ' + applicationIds.length + ' application(s)? This action cannot be undone.')) {
            return;
        }
        
        const $button = $('#do-bulk-action');
        $button.prop('disabled', true).text('Deleting...');
        
        $.ajax({
            url: ms_installment_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'ms_bulk_delete_applications',
                application_ids: applicationIds,
                nonce: ms_installment_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice(response.data, 'success');
                    // Reload the page to refresh the table
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showNotice(response.data || 'Error deleting applications.', 'error');
                }
            },
            error: function() {
                showNotice('Network error. Please try again.', 'error');
            },
            complete: function() {
                $button.prop('disabled', false).text('Apply');
            }
        });
    }

})(jQuery); 