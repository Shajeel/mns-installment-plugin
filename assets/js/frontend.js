/**
 * MS Installment Plugin Frontend JavaScript
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize the installment calculator
        initInstallmentCalculator();
        
        // Initialize the application form
        initApplicationForm();
        
        // Initialize installment button positioning
        initInstallmentButton();
        
        // Initial sync of product fields
        syncProductFields();
    });

    /**
     * Initialize installment calculator
     */
    function initInstallmentCalculator() {
        const $calculator = $('#calculate_installment');
        const $productPrice = $('#product_price');
        const $calculationResults = $('#calculation_results');
        
        // Calculate on button click
        $calculator.on('click', function() {
            calculateInstallment();
        });
        
        // Calculate on price input change
        $productPrice.on('input', function() {
            const price = parseFloat($(this).val());
            if (price > 0) {
                calculateInstallment();
            } else {
                $calculationResults.hide();
            }
        });
        
        // Auto-calculate if price is pre-filled
        if ($productPrice.val() && parseFloat($productPrice.val()) > 0) {
            calculateInstallment();
        }
    }

    /**
     * Calculate installment
     */
    function calculateInstallment() {
        const productPrice = parseFloat($('#product_price').val());
        
        if (!productPrice || productPrice <= 0) {
            showMessage('Please enter a valid product price.', 'error');
            return;
        }
        
        // Show loading state
        $('#calculate_installment').prop('disabled', true).text('Calculating...');
        
        // Get processing fee percentage from the input field
        const processingFeePercentage = parseFloat($('#processing_fee').val()) || 5; // Default 5%
        
        // Calculate processing fee as percentage of product price
        const processingFee = (productPrice * processingFeePercentage) / 100;
        const totalAmount = productPrice + processingFee;
        const installmentAmount = totalAmount / 3;
        
        // Update display
        updateCalculationDisplay({
            product_price: productPrice,
            processing_fee_percentage: processingFeePercentage,
            processing_fee: processingFee,
            total_amount: totalAmount,
            installment_amount: installmentAmount
        });
        
        // Update hidden fields
        $('#hidden_processing_fee').val(processingFee);
        $('#hidden_total_amount').val(totalAmount);
        $('#hidden_installment_amount').val(installmentAmount);
        
        // Show results
        $('#calculation_results').show();
        
        // Sync product fields after calculation
        syncProductFields();
        
        // Reset button
        $('#calculate_installment').prop('disabled', false).text('Calculate Installment');
        
        // Enable submit button if form is valid
        checkFormValidity();
    }

    /**
     * Update calculation display
     */
    function updateCalculationDisplay(calculation) {
        // Format currency values
        const formatCurrency = function(amount) {
            return new Intl.NumberFormat('en-PK', {
                style: 'currency',
                currency: 'PKR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        };
        
        $('#display_product_price').text(formatCurrency(calculation.product_price));
        
        // Update processing fee label to show percentage
        const processingFeeLabel = $('.ms-installment-calculation-item:eq(1) .label');
        processingFeeLabel.text('Processing Fee (' + calculation.processing_fee_percentage + '%):');
        $('#display_processing_fee').text(formatCurrency(calculation.processing_fee));
        
        $('#display_total_amount').text(formatCurrency(calculation.total_amount));
        $('#display_installment_amount').text(formatCurrency(calculation.installment_amount));
    }

    /**
     * Initialize application form
     */
    function initApplicationForm() {
        const $form = $('#ms_installment_application_form');
        const $submitBtn = $('.ms-installment-submit-btn');
        
        // Sync product fields from outside form to hidden fields inside form
        syncProductFields();
        
        // Check form validity on any input change (including fields outside the form)
        $form.on('input change', function() {
            syncProductFields();
            checkFormValidity();
        });
        
        // Also listen for changes in product fields outside the form
        $('#product_name, #product_price').on('input change', function() {
            syncProductFields();
            checkFormValidity();
        });
        
        // Special handling for Lahore resident question
        $form.find('input[name="is_lahore_resident"]').on('change', function() {
            if ($(this).val() === 'no') {
                showMessage('Sorry, this installment service is only available for residents of Lahore.', 'error');
            }
            checkFormValidity();
        });
        
        // Handle form submission
        $form.on('submit', function(e) {
            e.preventDefault();
            submitApplication();
        });
        
        // Add CAPTCHA refresh functionality
        initCaptcha();
        
        // Initial form validity check
        checkFormValidity();
    }

    /**
     * Sync product fields from outside form to hidden fields inside form
     */
    function syncProductFields() {
        const productName = $('#product_name').val();
        const productPrice = $('#product_price').val();
        
        $('#hidden_product_name').val(productName);
        $('#hidden_product_price').val(productPrice);
        
        // Debug logging
        console.log('Syncing product fields:');
        console.log('Product Name:', productName);
        console.log('Product Price:', productPrice);
        console.log('Hidden Product Name:', $('#hidden_product_name').val());
        console.log('Hidden Product Price:', $('#hidden_product_price').val());
    }

    /**
     * Initialize CAPTCHA functionality
     */
    function initCaptcha() {
        // Add refresh button to CAPTCHA
        const $captchaQuestion = $('.captcha-question');
        if ($captchaQuestion.length) {
            $captchaQuestion.append('<button type="button" class="captcha-refresh" title="Refresh CAPTCHA">↻</button>');
            
            // Handle refresh button click
            $(document).on('click', '.captcha-refresh', function(e) {
                e.preventDefault();
                refreshCaptcha();
            });
        }
    }

    /**
     * Refresh CAPTCHA with new question
     */
    function refreshCaptcha() {
        // Generate new CAPTCHA via AJAX
        $.ajax({
            url: ms_installment_frontend.ajax_url,
            type: 'POST',
            data: {
                action: 'ms_refresh_captcha',
                nonce: ms_installment_frontend.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.captcha-text').text(response.data.question);
                    $('input[name="captcha_answer"]').val(response.data.answer);
                    $('#captcha_input').val('').focus();
                }
            },
            error: function() {
                // Fallback: generate simple CAPTCHA client-side
                generateSimpleCaptcha();
            }
        });
    }

    /**
     * Generate simple CAPTCHA client-side as fallback
     */
    function generateSimpleCaptcha() {
        const num1 = Math.floor(Math.random() * 10) + 1;
        const num2 = Math.floor(Math.random() * 10) + 1;
        const answer = num1 + num2;
        
        $('.captcha-text').text(num1 + ' + ' + num2 + ' = ?');
        $('input[name="captcha_answer"]').val(answer);
        $('#captcha_input').val('').focus();
    }

    /**
     * Check form validity
     */
    function checkFormValidity() {
        const $form = $('#ms_installment_application_form');
        const $submitBtn = $('.ms-installment-submit-btn');
        
        // Check if calculation is done
        const hasCalculation = $('#calculation_results').is(':visible');
        
        // Check if all required fields are filled
        const requiredFields = [
            'product_name', 'product_price', 'is_lahore_resident', 
            'residence', 'profession', 'has_bank_account', 
            'can_provide_guarantor', 'has_cnic', 'customer_name',
            'customer_email', 'customer_phone', 'customer_address', 'terms_agreed',
            'captcha_input'
        ];
        
        let isValid = hasCalculation;
        
        requiredFields.forEach(function(fieldName) {
            let $field;
            
            // Check if field is inside the form
            $field = $form.find('[name="' + fieldName + '"]');
            
            if ($field.length) {
                if ($field.attr('type') === 'radio') {
                    if (!$field.is(':checked')) {
                        isValid = false;
                        console.log('Radio field not checked:', fieldName);
                    }
                } else if ($field.attr('type') === 'checkbox') {
                    if (!$field.is(':checked')) {
                        isValid = false;
                        console.log('Checkbox not checked:', fieldName);
                    }
                } else {
                    if (!$field.val().trim()) {
                        isValid = false;
                        console.log('Field empty:', fieldName, 'Value:', $field.val());
                    }
                }
            } else {
                console.log('Field not found:', fieldName);
                isValid = false;
            }
        });
        
        // Special validation for Lahore resident
        const $lahoreResident = $form.find('input[name="is_lahore_resident"]:checked');
        if ($lahoreResident.length && $lahoreResident.val() === 'no') {
            isValid = false;
            // Show error message
            showMessage('Sorry, this installment service is only available for residents of Lahore.', 'error');
        }
        
        // Enable/disable submit button
        $submitBtn.prop('disabled', !isValid);
    }

    /**
     * Submit application
     */
    function submitApplication() {
        const $form = $('#ms_installment_application_form');
        const $submitBtn = $('.ms-installment-submit-btn');
        
        // Validate all required fields before submission
        const requiredFields = [
            'product_name', 'product_price', 'is_lahore_resident', 
            'residence', 'profession', 'has_bank_account', 
            'can_provide_guarantor', 'has_cnic', 'customer_name',
            'customer_email', 'customer_phone', 'customer_address', 'terms_agreed',
            'captcha_input'
        ];
        
        const missingFields = [];
        
        requiredFields.forEach(function(fieldName) {
            let $field = $form.find('[name="' + fieldName + '"]');
            
            if ($field.length) {
                if ($field.attr('type') === 'radio') {
                    if (!$field.is(':checked')) {
                        missingFields.push(fieldName);
                        console.log('Radio field not checked during submission:', fieldName);
                    }
                } else if ($field.attr('type') === 'checkbox') {
                    if (!$field.is(':checked')) {
                        missingFields.push(fieldName);
                        console.log('Checkbox not checked during submission:', fieldName);
                    }
                } else {
                    if (!$field.val().trim()) {
                        missingFields.push(fieldName);
                        console.log('Field empty during submission:', fieldName, 'Value:', $field.val());
                    }
                }
            } else {
                missingFields.push(fieldName);
                console.log('Field not found during submission:', fieldName);
            }
        });
        
        if (missingFields.length > 0) {
            showMessage('Please fill in all required fields. Missing: ' + missingFields.join(', '), 'error');
            return;
        }
        
        // Special validation for Lahore resident
        const $lahoreResident = $form.find('input[name="is_lahore_resident"]:checked');
        if ($lahoreResident.length && $lahoreResident.val() === 'no') {
            showMessage('Sorry, this installment service is only available for residents of Lahore.', 'error');
            return;
        }
        
        // Show loading state
        $submitBtn.prop('disabled', true).text('Submitting...');
        $form.addClass('ms-installment-loading');
        
        // Collect form data
        const formData = new FormData($form[0]);
        
        // Sync product fields before submission
        syncProductFields();
        
        formData.append('action', 'ms_submit_installment_application');
        formData.append('nonce', ms_installment_frontend.nonce);
        
        // Also add the nonce from the form field
        const formNonce = $('input[name="ms_installment_nonce"]').val();
        if (formNonce) {
            formData.append('ms_installment_nonce', formNonce);
        }
        
        // Debug: Log form data
        console.log('Form data being sent:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        // Submit via AJAX
        $.ajax({
            url: ms_installment_frontend.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Response:', response);
                if (response.success) {
                    showMessage(response.message, 'success');
                    $form[0].reset();
                    $('#calculation_results').hide();
                    checkFormValidity();
                } else {
                    showMessage(response.message || 'Error submitting application.', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', xhr, status, error);
                showMessage('Network error. Please try again.', 'error');
            },
            complete: function() {
                $submitBtn.prop('disabled', false).text('Submit Application');
                $form.removeClass('ms-installment-loading');
            }
        });
    }

    /**
     * Show message
     */
    function showMessage(message, type) {
        const $messageContainer = $('#ms_installment_message');
        
        $messageContainer
            .removeClass('success error')
            .addClass(type)
            .text(message)
            .show();
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $messageContainer.fadeOut();
        }, 5000);
        
        // Scroll to message
        $('html, body').animate({
            scrollTop: $messageContainer.offset().top - 100
        }, 500);
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
     * Validate email
     */
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Validate phone number (basic validation for Pakistan)
     */
    function isValidPhone(phone) {
        const phoneRegex = /^(\+92|0)?[0-9]{10}$/;
        return phoneRegex.test(phone.replace(/\s/g, ''));
    }

    /**
     * Initialize installment button positioning
     */
    function initInstallmentButton() {
        // Ensure proper button alignment
        $('.ms-installment-button').each(function() {
            const $button = $(this);
            const $addToCartButton = $button.prev('.single_add_to_cart_button');
            
            if ($addToCartButton.length) {
                // Match the height of the add to cart button
                const addToCartHeight = $addToCartButton.outerHeight();
                $button.css('min-height', addToCartHeight + 'px');
            }
        });
        
        // Handle dynamic content (like variations)
        $(document.body).on('found_variation', function() {
            setTimeout(function() {
                $('.ms-installment-button').each(function() {
                    const $button = $(this);
                    const $addToCartButton = $button.prev('.single_add_to_cart_button');
                    
                    if ($addToCartButton.length) {
                        const addToCartHeight = $addToCartButton.outerHeight();
                        $button.css('min-height', addToCartHeight + 'px');
                    }
                });
            }, 100);
        });
    }

})(jQuery); 