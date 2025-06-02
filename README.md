# User Page Notes WordPress Plugin

A modern WordPress plugin that allows logged-in users to add personal notes to any page on your website. Built with modern PHP OOP principles, SCSS styling, and AJAX functionality.

## Features

- ✅ **Personal Notes**: Each user can add private notes visible only to them
- ✅ **Any Page Support**: Works on any WordPress page, post, or custom post type
- ✅ **AJAX Interface**: Add, edit, and delete notes without page reload
- ✅ **Modern UI**: Beautiful, responsive interface with smooth animations
- ✅ **Mobile Friendly**: Fully responsive design that works on all devices
- ✅ **Secure**: Proper nonce verification and input sanitization
- ✅ **OOP Architecture**: Clean, maintainable code following WordPress best practices
- ✅ **Admin Dashboard**: View statistics and manage plugin settings

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Installation

1. Upload the `user-page-notes` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The plugin will automatically create the necessary database table
4. Configure settings under Settings → User Page Notes

## Usage

### For Users

Once activated, logged-in users will see a floating notes button on the right side of every page. Users can:

1. **View Notes**: Click the notes button to see all notes for the current page
2. **Add Notes**: Click the "+" button to add a new note
3. **Edit Notes**: Click the edit icon on any existing note
4. **Delete Notes**: Click the delete icon and confirm deletion

### For Administrators

Administrators can:

1. **Enable/Disable**: Toggle the notes feature on/off
2. **View Statistics**: See total notes, active users, and daily activity
3. **Monitor Usage**: Track how the feature is being used across the site

## File Structure

```
user-page-notes/
├── user-page-notes.php          # Main plugin file
├── src/                         # PHP source files
│   ├── Core/
│   │   ├── Database.php         # Database operations
│   │   ├── Assets.php           # Asset management
│   │   └── Ajax.php             # AJAX handlers
│   ├── Frontend/
│   │   └── NotesDisplay.php     # Frontend interface
│   └── Admin/
│       └── Settings.php         # Admin settings
├── assets/                      # Frontend assets
│   ├── css/
│   │   ├── frontend.css         # Compiled frontend styles
│   │   └── admin.css            # Admin styles
│   ├── scss/
│   │   └── frontend.scss        # Source SCSS file
│   └── js/
│       ├── frontend.js          # Frontend JavaScript
│       └── admin.js             # Admin JavaScript
└── README.md                    # This file
```

## Database Schema

The plugin creates a single table `wp_user_page_notes` with the following structure:

```sql
CREATE TABLE wp_user_page_notes (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    user_id bigint(20) unsigned NOT NULL,
    page_url varchar(500) NOT NULL,
    page_title varchar(500) DEFAULT '',
    note_content longtext NOT NULL,
    note_position_x int(11) DEFAULT 0,
    note_position_y int(11) DEFAULT 0,
    is_private tinyint(1) DEFAULT 1,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY page_url (page_url(191)),
    KEY created_at (created_at)
);
```

## AJAX Endpoints

The plugin registers the following AJAX actions:

- `upn_add_note` - Add a new note
- `upn_update_note` - Update an existing note
- `upn_delete_note` - Delete a note
- `upn_get_notes` - Get notes for a page

All endpoints require proper nonce verification and user authentication.

## Security Features

- **Nonce Verification**: All AJAX requests use WordPress nonces
- **User Authentication**: Only logged-in users can access the functionality
- **Input Sanitization**: All user inputs are properly sanitized
- **SQL Injection Protection**: Uses WordPress prepared statements
- **XSS Protection**: Output is properly escaped

## Styling

The plugin uses SCSS for styling which is compiled to CSS. Key styling features:

- **CSS Variables**: For easy customization
- **Responsive Design**: Mobile-first approach
- **Modern Animations**: Smooth transitions and hover effects
- **WordPress Integration**: Follows WordPress admin design patterns

## JavaScript Architecture

The frontend JavaScript is organized using ES6+ features:

- **Class-based Structure**: Clean, maintainable code
- **Event Delegation**: Efficient event handling
- **Promise-based AJAX**: Modern async handling
- **Utility Methods**: Debouncing and throttling for performance

## Customization

### Styling

You can customize the appearance by overriding the CSS classes:

```css
/* Change the primary color */
.upn-toggle-btn {
    background: #your-color !important;
}

/* Customize the panel */
.upn-notes-panel {
    width: 400px !important;
}
```

### Hooks and Filters

The plugin provides several hooks for customization:

```php
// Modify note data before saving
add_filter('upn_before_save_note', function($note_data) {
    // Your customization
    return $note_data;
});
```

## Performance

The plugin is optimized for performance:

- **Conditional Loading**: Assets only load for logged-in users
- **Lazy Loading**: Notes are loaded only when needed
- **Efficient Queries**: Optimized database queries with proper indexing
- **Caching Ready**: Compatible with WordPress caching plugins

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Changelog

### Version 1.0.0
- Initial release
- Core functionality for adding, editing, and deleting notes
- Modern responsive UI
- Admin settings panel
- Security features

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support questions, please contact the plugin author or submit an issue on the project repository. 