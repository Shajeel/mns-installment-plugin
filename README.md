# M&S Installment Plugin for WooCommerce

A comprehensive WordPress plugin for WooCommerce that allows customers to buy products on installments with a complete application management system.

## Features

### Frontend Features
- **Installment Button**: "Buy on Installments" button positioned next to the "Add to Cart" button on product pages
- **Installment Calculator**: Real-time calculation of 3-month installment plans
- **Application Form**: Comprehensive eligibility and personal information collection
- **CAPTCHA Protection**: Math-based CAPTCHA to prevent spam and automated submissions
- **AJAX Submission**: Smooth form submission without page reload
- **Responsive Design**: Mobile-friendly interface with proper button layout

### Admin Features
- **Application Management**: View, filter, and manage all applications
- **Status Management**: Approve, reject, or set applications as pending
- **Admin Notes**: Add internal notes for each application
- **Settings Page**: Configure processing fees and product selection
- **Product Meta Box**: Enable/disable installment for individual products

## Installation

1. **Upload the Plugin**:
   - Upload the plugin folder to `/wp-content/plugins/ms-installment-plugin/`
   - Or zip the folder and upload via WordPress admin

2. **Activate the Plugin**:
   - Go to WordPress Admin → Plugins
   - Find "M&S Installment Plugin" and click "Activate"

3. **Configure Settings**:
   - Go to WordPress Admin → Installments → Settings
   - Set the processing fee (default: PKR 500)
   - Choose whether to show installment button on all products or selected products
   - Select the page where the calculator shortcode will be placed

## Setup Instructions

### 1. Create Calculator Page
1. Create a new page in WordPress
2. Add the shortcode `[ms_installment_calculator]` to the page content
3. Go to Installments → Settings and select this page as the "Calculator Page"

### 2. Configure Products
**Option A: Enable for All Products**
- Go to Installments → Settings
- Select "On all products" under "Show Installment Button"

**Option B: Enable for Selected Products**
- Go to Installments → Settings
- Select "Only on selected products"
- Edit individual products and check "Enable installment for this product" in the sidebar

### 3. Test the Flow
1. Visit a product page with installment enabled
2. Click "Buy on Installments" button
3. Fill out the application form
4. Submit and check the admin panel for the application

## Usage

### Shortcode Usage
```
[ms_installment_calculator]
```

**With Parameters**:
```
[ms_installment_calculator product_name="Product Name" product_price="10000"]
```

### User Flow
1. **Customer visits product page** → Sees "Buy on Installments" button next to "Add to Cart" button
2. **Clicks installment button** → Redirected to calculator page with product info pre-filled
3. **Fills application form** → Submits via AJAX
4. **Admin receives application** → Can view, approve, reject, or add notes
5. **Status updates** → Admin can change application status and add notes

### Admin Management
- **View Applications**: Installments → Applications
- **Filter Applications**: By status, date range, or search terms
- **Update Status**: Click "View" → Use status buttons in modal
- **Add Notes**: Use the notes section in the application modal
- **Configure Settings**: Installments → Settings

## Database Structure

The plugin creates a custom table `wp_ms_installment_applications` with the following fields:

- `id` - Application ID
- `product_name` - Product name
- `product_price` - Product price
- `processing_fee` - Processing fee
- `total_amount` - Total amount (price + fee)
- `installment_amount` - Monthly installment amount
- `is_lahore_resident` - Lahore residence (yes/no)
- `residence` - Residence type (own/rented)
- `profession` - Profession (job/business)
- `has_bank_account` - Bank account (yes/no)
- `can_provide_guarantor` - Guarantor availability (yes/no)
- `has_cnic` - CNIC availability (yes/no)
- `customer_name` - Customer full name
- `customer_email` - Customer email
- `customer_phone` - Customer phone
- `customer_address` - Customer address
- `status` - Application status (pending/approved/rejected)
- `admin_notes` - Admin notes
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

## Configuration Options

### Plugin Settings
- **Processing Fee**: Amount added to product price for installment calculation
- **Show Installment Button**: Choose between "all products" or "selected products"
- **Calculator Page**: Select the page where the shortcode is placed

### Product Settings
- **Enable Installment**: Checkbox in product edit page to enable/disable installment for individual products

## Styling

The plugin includes comprehensive CSS styling for both frontend and admin interfaces:

- **Frontend**: Modern, responsive design with gradient buttons and clean forms
- **Admin**: WordPress admin-style interface with status indicators and modals
- **Mobile**: Fully responsive design that works on all devices

## Security Features

- **Nonce Verification**: All AJAX requests are protected with WordPress nonces
- **Capability Checks**: Admin functions require `manage_options` capability
- **Data Sanitization**: All user inputs are properly sanitized
- **SQL Prepared Statements**: Database queries use prepared statements to prevent SQL injection
- **CAPTCHA Protection**: Math-based CAPTCHA prevents spam and automated form submissions

## Translation Ready

The plugin is fully translation-ready with:
- Text domain: `ms-installment`
- All user-facing strings wrapped in `__()` and `_e()` functions
- Language files can be added to `/languages/` directory

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Internet Explorer 11+

## Requirements

- WordPress 5.0 or higher
- WooCommerce 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Troubleshooting

### Common Issues

1. **Button not showing on product pages**:
   - Check if WooCommerce is active
   - Verify calculator page is set in settings
   - Ensure product has installment enabled (if using "selected products" mode)

2. **Form not submitting**:
   - Check browser console for JavaScript errors
   - Verify AJAX URL is correct
   - Check if nonce is being generated properly

3. **Database table not created**:
   - Deactivate and reactivate the plugin
   - Check WordPress error logs
   - Verify database permissions

### Debug Mode

Enable WordPress debug mode to see detailed error messages:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Support

For support and feature requests, please contact the development team.

## Changelog

### Version 1.0.2
- Added bulk delete functionality with checkboxes
- Added "Select All" checkbox for bulk operations
- Improved application status management in modal
- Fixed status update display in table and modal
- Enhanced admin interface with better bulk actions
- Added confirmation dialogs for delete operations
- Improved table styling with hover effects

### Version 1.0.1
- Fixed form validation issue with product fields outside the form
- Added hidden fields inside form for proper validation
- Improved JavaScript field synchronization
- Added debug logging for troubleshooting
- Enhanced form submission reliability

### Version 1.0.0
- Initial release
- Complete installment calculator and application system
- Admin management interface
- Product meta box integration
- Responsive design
- AJAX form submission
- Status management system

## License

This plugin is proprietary software developed for M&S Electronics.

## Credits

Developed for M&S Electronics - A comprehensive WooCommerce installment solution. 