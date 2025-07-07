# MS Installment Plugin - Roles and Permissions

## Overview

The MS Installment Plugin includes a comprehensive role-based access control system that allows you to control which user roles can access and manage installment applications.

## Default Permissions

### Administrator Role
- **Full Access**: Can view, edit, delete applications, and manage all settings
- **Settings Access**: Can configure plugin settings, email notifications, and role permissions
- **Always Enabled**: Administrator role cannot be disabled from the plugin

### Other WordPress Roles
- **Editor**: Can view and manage applications (if enabled)
- **Author**: Can view and manage applications (if enabled)
- **Contributor**: Can view and manage applications (if enabled)
- **Subscriber**: No access (excluded by default)

## Custom Capability

The plugin uses a custom WordPress capability: `manage_ms_installments`

This capability is automatically added to:
- Administrator role (always)
- Any other roles you enable in the settings

## Managing Role Permissions

### Accessing Role Settings

1. Go to **WordPress Admin** → **M&S Installments** → **Settings**
2. Scroll down to the **"Role Permissions"** section
3. Check/uncheck the roles you want to grant access to
4. Click **"Save Settings"**

### Available Actions by Role

| Action | Administrator | Editor | Author | Contributor |
|--------|---------------|--------|--------|-------------|
| View Applications | ✅ | ✅ | ✅ | ✅ |
| Edit Applications | ✅ | ✅ | ✅ | ✅ |
| Delete Applications | ✅ | ✅ | ✅ | ✅ |
| Manage Settings | ✅ | ❌ | ❌ | ❌ |
| Manage Role Permissions | ✅ | ❌ | ❌ | ❌ |

## User Management

### Viewing Current Users

The settings page includes a **"Current Users & Permissions"** section that shows:
- All users with access to the plugin
- Their assigned roles
- Whether they can currently access the plugin
- Their email addresses

### Adding New Users

To give a user access to the plugin:

1. **Create/Edit User**: Go to **Users** → **Add New** or edit an existing user
2. **Assign Role**: Give them a role that has plugin access (Editor, Author, etc.)
3. **Enable Role**: Make sure that role is enabled in the plugin settings
4. **Verify Access**: The user should now see the "M&S Installments" menu

## Security Features

### Permission Checks
- All admin pages check permissions before displaying
- AJAX actions verify user capabilities
- Product meta boxes only show for authorized users
- Settings access restricted to administrators only

### Nonce Verification
- All forms include WordPress nonces for security
- AJAX requests verify nonces before processing
- Prevents unauthorized form submissions

### Capability Filtering
- Custom capability filtering ensures proper access control
- Fallback to administrator role for security
- Automatic capability assignment/removal

## Troubleshooting

### User Can't Access Plugin
1. Check if their role is enabled in plugin settings
2. Verify the user has the correct WordPress role
3. Clear any caching plugins
4. Check if any security plugins are blocking access

### Capabilities Not Working
1. Go to plugin settings and re-save role permissions
2. Deactivate and reactivate the plugin
3. Check if any other plugins are interfering with capabilities

### Settings Not Saving
1. Ensure you're logged in as an administrator
2. Check if you have the `manage_options` capability
3. Verify nonce is not expired (refresh the page)

## Best Practices

### Role Assignment
- **Editor**: Good for staff who need to manage applications
- **Author**: Suitable for junior staff with limited access
- **Contributor**: Minimal access, good for viewing only
- **Administrator**: Full access, use sparingly

### Security Recommendations
- Regularly review which roles have access
- Remove access from users who no longer need it
- Use the principle of least privilege
- Monitor user activity in the applications

### Maintenance
- Review role permissions monthly
- Update user roles when staff changes
- Test access with different user accounts
- Keep the plugin updated for security patches

## API Reference

### Permission Check Functions

```php
// Check if user can manage installments
MS_Installment_Permissions::user_can_manage_installments($user_id);

// Check if user can view applications
MS_Installment_Permissions::user_can_view_applications($user_id);

// Check if user can edit applications
MS_Installment_Permissions::user_can_edit_applications($user_id);

// Check if user can delete applications
MS_Installment_Permissions::user_can_delete_applications($user_id);

// Check if user can manage settings
MS_Installment_Permissions::user_can_manage_settings($user_id);
```

### Role Management Functions

```php
// Get available roles
MS_Installment_Permissions::get_available_roles();

// Get enabled roles
MS_Installment_Permissions::get_enabled_roles();

// Save enabled roles
MS_Installment_Permissions::save_enabled_roles($roles);

// Get user's role display name
MS_Installment_Permissions::get_user_role_display_name($user_id);
```

## Support

If you encounter issues with roles and permissions:

1. Check this documentation first
2. Verify WordPress user roles are working correctly
3. Test with a fresh WordPress installation
4. Contact plugin support with specific error messages 