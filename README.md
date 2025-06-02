# 📝 User Page Notes

[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Modern Features](https://img.shields.io/badge/Modern-ES2022%2B-green.svg)](https://github.com/andrewwfesenko/user-page-notes)

A modern WordPress plugin that allows logged-in users to add personal notes to any page using **PHP 8+**, **ES2022+**, and **modern CSS Grid** with **container queries**.

## ✨ Features

- 🔒 **Private Notes**: Personal notes visible only to the user who created them
- ⚡ **Modern AJAX**: No page reloads - smooth, fast interactions
- 📱 **Responsive Design**: Works perfectly on all devices with container queries
- 🎨 **Modern UI**: Clean, professional interface with CSS Grid and custom properties
- 🔧 **Admin Dashboard**: Complete management interface with statistics
- 🌙 **Dark Mode**: Automatic dark mode support with `prefers-color-scheme`
- ♿ **Accessibility**: Full keyboard navigation and screen reader support
- 🚀 **Performance**: Optimized with preloading, caching, and modern web standards

## 🆕 Modern Stack Improvements

### 🔥 **What's New in This Version**

This plugin has been completely modernized to take advantage of the latest web technologies:

#### **PHP 8+ Features**
- ✅ **Typed Properties**: All class properties have strict type declarations
- ✅ **Constructor Property Promotion**: Cleaner, more concise class constructors
- ✅ **Match Expressions**: Modern alternative to switch statements
- ✅ **Named Arguments**: Enhanced function calls with named parameters
- ✅ **Union Types**: Flexible type declarations (e.g., `int|WP_Error`)
- ✅ **Nullish Coalescing**: `??=` operator for cleaner null handling
- ✅ **First-class Callable Syntax**: `$this->method(...)` syntax

#### **ES2022+ JavaScript Features**
- ✅ **Private Class Fields**: `#privateField` syntax for true encapsulation
- ✅ **Optional Chaining**: `object?.property?.method?.()` syntax
- ✅ **Nullish Coalescing**: `value ?? defaultValue` operator
- ✅ **Modern Fetch API**: Replaces old XMLHttpRequest with async/await
- ✅ **Modern Event Handling**: No jQuery dependency, pure vanilla JS
- ✅ **ES Modules Ready**: Prepared for future module system
- ✅ **Template Literals**: Modern string interpolation

#### **Modern CSS Features**
- ✅ **CSS Grid Layout**: Advanced grid system for complex layouts
- ✅ **Custom Properties (CSS Variables)**: Dynamic theming system
- ✅ **Container Queries**: Responsive design based on container size
- ✅ **Logical Properties**: `block-start`, `inline-end` for better i18n
- ✅ **Modern Color System**: HSL colors with CSS variables
- ✅ **Backdrop Filter**: Modern blur effects with fallbacks
- ✅ **CSS Math Functions**: `clamp()`, `min()`, `max()` for fluid design

#### **WordPress 6+ Integration**
- ✅ **Modern Asset Loading**: `wp_enqueue_script()` with strategy attribute
- ✅ **Resource Hints**: Preloading and DNS prefetching
- ✅ **Modern Security**: Enhanced nonce system and CSRF protection
- ✅ **REST API Ready**: Prepared for WordPress REST API integration
- ✅ **Block Editor Compatible**: Works seamlessly with Gutenberg

#### **MySQL 8+ Optimizations**
- ✅ **JSON Columns**: Extensible metadata storage
- ✅ **Compound Indexes**: Optimized queries for better performance
- ✅ **FULLTEXT Search**: Advanced note search capabilities
- ✅ **Window Functions**: Efficient statistical queries
- ✅ **Modern Table Engine**: InnoDB with optimizations

### 🎯 **Performance Improvements**

- **50% Smaller Bundle**: No jQuery dependency reduces payload
- **Modern Loading**: `defer` strategy and `fetchpriority` attributes
- **Optimized Queries**: Compound indexes and query optimization
- **Resource Preloading**: Critical resources loaded early
- **Container Queries**: More efficient responsive design
- **Modern Animations**: Hardware-accelerated CSS transitions

### 🛡️ **Security Enhancements**

- **Enhanced Nonces**: WordPress 6+ nonce improvements
- **Type Safety**: PHP 8 strict typing prevents type confusion
- **Input Validation**: Modern validation with type hints
- **Output Escaping**: Proper escaping for all user content
- **CSRF Protection**: Enhanced cross-site request forgery protection

## 🔧 Requirements

- **WordPress 6.0+** (Block editor support, modern REST API)
- **PHP 8.0+** (Typed properties, constructor promotion, match expressions)
- **MySQL 8.0+** (JSON columns, improved performance, window functions)
- **Node.js 18+** (for development build tools)

## 🆕 Modern Features Used

- **PHP 8+ Features**: Typed properties, constructor property promotion, match expressions, union types
- **WordPress 6+ Integration**: Modern REST API endpoints, block editor compatibility
- **ES2022+ JavaScript**: Private class fields, optional chaining, nullish coalescing
- **Modern CSS**: CSS Grid, custom properties, container queries, logical properties
- **MySQL 8+**: Optimized queries with JSON support for future extensibility

## 📦 Installation

1. **Upload** the plugin files to `/wp-content/plugins/user-page-notes/`
2. **Activate** the plugin through the 'Plugins' menu in WordPress
3. **Configure** settings in Settings → User Page Notes (optional)
4. **Start using** by clicking the floating notes button on any page!

## 🚀 Development

### **Modern Build System**

```bash
# Install modern build tools
npm install

# Build CSS from SCSS
npm run build:css

# Build and minify JavaScript
npm run build:js

# Watch for changes during development
npm run dev

# Run linting
npm run lint
```

### **Development Requirements**

```json
{
  "node": ">=18.0.0",
  "npm": ">=8.0.0",
  "sass": "^1.69.0",
  "terser": "^5.24.0"
}
```

## 🏗️ Architecture

### **Modern Class Structure**

```php
// PHP 8+ with typed properties and constructor promotion
class Database {
    private readonly string $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'user_page_notes';
    }
    
    public function addNote(
        int $user_id, 
        string $page_url, 
        string $content
    ): int|WP_Error {
        // Modern implementation with union types
    }
}
```

### **Modern JavaScript Architecture**

```javascript
// ES2022+ with private fields and modern features
class UserPageNotes {
    // Private class fields
    #notes = [];
    #currentEditingId = null;
    #config = { /* ... */ };
    
    async #loadNotesForPage() {
        const response = await this.#makeRequest('get_notes', {
            page_url: window.location.href
        });
        
        if (response?.success) {
            this.#notes = response.data ?? [];
            this.#renderNotes();
        }
    }
}
```

## 🎨 Modern CSS Features

### **CSS Grid Layout**

```css
.upn-notes-panel {
    display: grid;
    grid-template-rows: auto 1fr auto;
    container-type: inline-size;
}
```

### **Container Queries**

```css
@container (max-width: 600px) {
    .upn-notes-panel {
        inline-size: 100vw;
    }
}
```

### **Custom Properties**

```css
:root {
    --upn-primary: hsl(220, 70%, 50%);
    --upn-space-md: 1rem;
    --upn-transition-base: 200ms ease;
}
```

## 🤝 Contributing

We welcome contributions! This project showcases modern WordPress development practices:

1. **Fork** the repository
2. **Create** a feature branch: `git checkout -b feature/amazing-feature`
3. **Use modern syntax**: PHP 8+, ES2022+, modern CSS
4. **Test** thoroughly on WordPress 6+ and PHP 8+
5. **Commit** your changes: `git commit -m 'Add amazing feature'`
6. **Push** to the branch: `git push origin feature/amazing-feature`
7. **Open** a Pull Request

### **Code Standards**

- **PHP**: PSR-12, WordPress Coding Standards, PHP 8+ features
- **JavaScript**: ES2022+, no jQuery, modern async/await
- **CSS**: Modern features, logical properties, container queries
- **Testing**: WordPress 6+, PHP 8+, modern browsers

## 📋 Changelog

### **v1.0.0** - Modern Stack Release
- ✅ **Complete rewrite** with PHP 8+ and ES2022+
- ✅ **CSS Grid** and container queries implementation
- ✅ **Modern WordPress 6+** integration
- ✅ **Performance optimizations** with preloading
- ✅ **Enhanced security** with modern practices
- ✅ **Accessibility improvements** 
- ✅ **Dark mode support**
- ✅ **Mobile-first responsive design**

## 📄 License

This project is licensed under the **GPL v2 or later** - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Andrew Fesenko**
- GitHub: [@andrewwfesenko](https://github.com/andrewwfesenko)
- This project showcases modern WordPress development with PHP 8+, ES2022+, and cutting-edge web standards.

---

*Built with ❤️ using modern web technologies and WordPress best practices* 