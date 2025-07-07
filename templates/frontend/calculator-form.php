<?php
/**
 * Frontend calculator form template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get product details from shortcode attributes or URL parameters
$product_id = !empty($atts['product_id']) ? intval($atts['product_id']) : (isset($_GET['product_id']) ? intval($_GET['product_id']) : '');
$product_name = !empty($atts['product_name']) ? sanitize_text_field($atts['product_name']) : (isset($_GET['product_name']) ? sanitize_text_field($_GET['product_name']) : '');
$product_price = !empty($atts['product_price']) ? floatval($atts['product_price']) : (isset($_GET['product_price']) ? floatval($_GET['product_price']) : 0);

// Calculate installment details
$installment_details = MS_Installment_Frontend::calculate_installment_details($product_price);
?>

<div class="ms-installment-calculator">
    <div class="ms-installment-header">
        <h2><?php _e('M&S Installment Calculator', 'ms-installment'); ?></h2>
        <p><?php _e('Calculate your installment plan and apply for financing.', 'ms-installment'); ?></p>
    </div>

    <div class="ms-installment-calculator-section">
        <h3><?php _e('Product Information', 'ms-installment'); ?></h3>
        
        <div class="ms-installment-form-row">
            <div class="ms-installment-form-group">
                <label for="product_name"><?php _e('Product Name', 'ms-installment'); ?> *</label>
                <input type="text" id="product_name" name="product_name" value="<?php echo esc_attr($atts['product_name']); ?>" required>
            </div>
            
            <div class="ms-installment-form-group">
                <label for="product_price"><?php _e('Product Price (PKR)', 'ms-installment'); ?> *</label>
                <input type="number" id="product_price" name="product_price" value="<?php echo esc_attr($atts['product_price']); ?>" min="0" step="0.01" required>
            </div>
        </div>
        
        <div class="ms-installment-form-row">
            <div class="ms-installment-form-group">
                <label for="processing_fee"><?php _e('Processing Fee (%)', 'ms-installment'); ?></label>
                <input type="number" id="processing_fee" name="processing_fee" value="<?php echo esc_attr($installment_details['processing_fee_percentage']); ?>" readonly>
                <small><?php _e('This is calculated as a percentage of the product price', 'ms-installment'); ?></small>
            </div>
            
            <div class="ms-installment-form-group">
            <label for="calculate_installment">&nbsp;</label>
                <button type="button" id="calculate_installment" class="button woocommerce-button"><?php _e('Calculate Installment', 'ms-installment'); ?></button>
            </div>
        </div>
    </div>

    <div class="ms-installment-calculation-section" id="calculation_results" style="<?php echo $product_price > 0 ? 'display: block;' : 'display: none;'; ?>">
        <h3><?php _e('Installment Calculation', 'ms-installment'); ?></h3>
        
        <div class="ms-installment-calculation-grid">
            <div class="ms-installment-calculation-item">
                <span class="label"><?php _e('Product Price:', 'ms-installment'); ?></span>
                <span class="value" id="display_product_price"><?php echo $product_price > 0 ? MS_Installment_Frontend::format_currency($installment_details['product_price']) : ''; ?></span>
            </div>
            
            <div class="ms-installment-calculation-item">
                <span class="label"><?php _e('Processing Fee (', 'ms-installment'); ?><?php echo esc_html($installment_details['processing_fee_percentage']); ?>%):</span>
                <span class="value" id="display_processing_fee"><?php echo $product_price > 0 ? MS_Installment_Frontend::format_currency($installment_details['processing_fee']) : ''; ?></span>
            </div>
            
            <div class="ms-installment-calculation-item total">
                <span class="label"><?php _e('Total Amount:', 'ms-installment'); ?></span>
                <span class="value" id="display_total_amount"><?php echo $product_price > 0 ? MS_Installment_Frontend::format_currency($installment_details['total_amount']) : ''; ?></span>
            </div>
            
            <div class="ms-installment-calculation-item mns-installment">
                <span class="label"><?php _e('Monthly Installment (3 months):', 'ms-installment'); ?></span>
                <span class="value" id="display_installment_amount"><?php echo $product_price > 0 ? MS_Installment_Frontend::format_currency($installment_details['installment_amount']) : ''; ?></span>
            </div>
        </div>
    </div>

    <div class="ms-installment-application-section">
        <h3><?php _e('Application Form', 'ms-installment'); ?></h3>
        
        <form id="ms_installment_application_form" class="ms-installment-form">
            <?php wp_nonce_field('ms_installment_frontend_nonce', 'ms_installment_nonce'); ?>
            
            <input type="hidden" name="action" value="ms_submit_installment_application">
            
            <!-- Hidden fields for calculation data -->
            <input type="hidden" name="processing_fee" id="hidden_processing_fee" value="<?php echo esc_attr($installment_details['processing_fee']); ?>">
            <input type="hidden" name="total_amount" id="hidden_total_amount" value="<?php echo $product_price > 0 ? esc_attr($installment_details['total_amount']) : ''; ?>">
            <input type="hidden" name="installment_amount" id="hidden_installment_amount" value="<?php echo $product_price > 0 ? esc_attr($installment_details['installment_amount']) : ''; ?>">
            
            <!-- Hidden fields for product information (will be populated by JavaScript) -->
            <input type="hidden" name="product_name" id="hidden_product_name" value="<?php echo esc_attr($atts['product_name']); ?>">
            <input type="hidden" name="product_price" id="hidden_product_price" value="<?php echo esc_attr($atts['product_price']); ?>">
            <div class="ms-installment-form-section">
                <h4><?php _e('Eligibility Questions', 'ms-installment'); ?></h4>
                
                <div class="ms-installment-form-row">
                    <div class="ms-installment-form-group">
                        <label><?php _e('Are you resident of Lahore?', 'ms-installment'); ?> *</label>
                        <div class="ms-installment-radio-group">
                            <label><input type="radio" name="is_lahore_resident" value="yes" required> <?php _e('Yes', 'ms-installment'); ?></label>
                            <label><input type="radio" name="is_lahore_resident" value="no" required> <?php _e('No', 'ms-installment'); ?></label>
                        </div>
                    </div>
                </div>
                
                <div class="ms-installment-form-row">
                    <div class="ms-installment-form-group">
                        <label><?php _e('Residence:', 'ms-installment'); ?> *</label>
                        <div class="ms-installment-radio-group">
                            <label><input type="radio" name="residence" value="own" required> <?php _e('Own', 'ms-installment'); ?></label>
                            <label><input type="radio" name="residence" value="rented" required> <?php _e('Rented', 'ms-installment'); ?></label>
                        </div>
                    </div>
                    
                    <div class="ms-installment-form-group">
                        <label><?php _e('Profession:', 'ms-installment'); ?> *</label>
                        <div class="ms-installment-radio-group">
                            <label><input type="radio" name="profession" value="job" required> <?php _e('Job', 'ms-installment'); ?></label>
                            <label><input type="radio" name="profession" value="business" required> <?php _e('Business', 'ms-installment'); ?></label>
                        </div>
                    </div>
                </div>
                
                <div class="ms-installment-form-row">
                    <div class="ms-installment-form-group">
                        <label><?php _e('Do you have any bank\'s account?', 'ms-installment'); ?> *</label>
                        <div class="ms-installment-radio-group">
                            <label><input type="radio" name="has_bank_account" value="yes" required> <?php _e('Yes', 'ms-installment'); ?></label>
                            <label><input type="radio" name="has_bank_account" value="no" required> <?php _e('No', 'ms-installment'); ?></label>
                        </div>
                    </div>
                    
                    <div class="ms-installment-form-group">
                        <label><?php _e('Can you provide one guarantor?', 'ms-installment'); ?> *</label>
                        <div class="ms-installment-radio-group">
                            <label><input type="radio" name="can_provide_guarantor" value="yes" required> <?php _e('Yes', 'ms-installment'); ?></label>
                            <label><input type="radio" name="can_provide_guarantor" value="no" required> <?php _e('No', 'ms-installment'); ?></label>
                        </div>
                    </div>
                </div>
                
                <div class="ms-installment-form-row">
                    <div class="ms-installment-form-group">
                        <label><?php _e('Do you have CNIC?', 'ms-installment'); ?> *</label>
                        <div class="ms-installment-radio-group">
                            <label><input type="radio" name="has_cnic" value="yes" required> <?php _e('Yes', 'ms-installment'); ?></label>
                            <label><input type="radio" name="has_cnic" value="no" required> <?php _e('No', 'ms-installment'); ?></label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="ms-installment-form-section">
                <h4><?php _e('Personal Information', 'ms-installment'); ?></h4>
                
                <div class="ms-installment-form-row">
                    <div class="ms-installment-form-group">
                        <label for="customer_name"><?php _e('Full Name', 'ms-installment'); ?> *</label>
                        <input type="text" id="customer_name" name="customer_name" required>
                    </div>
                    
                    <div class="ms-installment-form-group">
                        <label for="customer_email"><?php _e('Email Address', 'ms-installment'); ?> *</label>
                        <input type="email" id="customer_email" name="customer_email" required>
                    </div>
                </div>
                
                <div class="ms-installment-form-row">
                    <div class="ms-installment-form-group">
                        <label for="customer_phone"><?php _e('Phone Number', 'ms-installment'); ?> *</label>
                        <input type="tel" id="customer_phone" name="customer_phone" required>
                    </div>
                </div>
                
                <div class="ms-installment-form-row">
                    <div class="ms-installment-form-group full-width">
                        <label for="customer_address"><?php _e('Address', 'ms-installment'); ?> *</label>
                        <textarea id="customer_address" name="customer_address" rows="3" required></textarea>
                    </div>
                </div>
            </div>
            
            <div class="ms-installment-form-section">
                <h4><?php _e('Terms and Conditions', 'ms-installment'); ?></h4>
                
                <div class="ms-installment-terms">
                    <ul>
                        <li><?php _e('M&S Electronics will have the right to reject the request without reason', 'ms-installment'); ?></li>
                        <li><?php _e('No price discounts possible after agreeing on one price', 'ms-installment'); ?></li>
                        <li><?php _e('In case of non-payment, company has right to take legal action', 'ms-installment'); ?></li>
                    </ul>
                </div>
                
                <div class="ms-installment-form-group">
                    <label class="ms-installment-checkbox-label">
                        <input type="checkbox" name="terms_agreed" value="yes" required>
                        <span class="checkmark"></span>
                        <?php _e('I agree to the mentioned terms', 'ms-installment'); ?> *
                    </label>
                </div>
            </div>
            
            <div class="ms-installment-form-section">
                <h4><?php _e('Security Verification', 'ms-installment'); ?></h4>
                
                <div class="ms-installment-captcha">
                    <div class="ms-installment-form-group">
                        <label for="captcha_question"><?php _e('Security Question', 'ms-installment'); ?> *</label>
                        <div class="captcha-question">
                            <?php 
                            // Generate simple math question
                            $num1 = rand(1, 10);
                            $num2 = rand(1, 10);
                            $captcha_answer = $num1 + $num2;
                            ?>
                            <span class="captcha-text"><?php echo $num1; ?> + <?php echo $num2; ?> = ?</span>
                            <input type="hidden" name="captcha_answer" value="<?php echo $captcha_answer; ?>">
                        </div>
                    </div>
                    
                    <div class="ms-installment-form-group">
                        <label for="captcha_input"><?php _e('Your Answer', 'ms-installment'); ?> *</label>
                        <input type="number" id="captcha_input" name="captcha_input" required min="0" placeholder="<?php _e('Enter your answer', 'ms-installment'); ?>">
                        <small class="captcha-help"><?php _e('Please solve the math question above to verify you are human.', 'ms-installment'); ?></small>
                    </div>
                </div>
            </div>
            
            <div class="ms-installment-form-submit">
                <button type="submit" class="button ms-installment-submit-btn woocommerce-button" disabled>
                    <?php _e('Submit Application', 'ms-installment'); ?>
                </button>
            </div>
        </form>
    </div>
    
    <div id="ms_installment_message" class="ms-installment-message" style="display: none;"></div>
</div> 