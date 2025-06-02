<?php

namespace UPN\Core;

/**
 * Assets management class
 * Modern WordPress 6+ asset loading with optimizations
 */
class Assets {
    
    private readonly string $version;
    
    public function __construct() {
        $this->version = UPN_VERSION;
        $this->init();
    }
    
    /**
     * Initialize asset loading
     */
    private function init(): void {
        add_action('wp_enqueue_scripts', $this->enqueueAssets(...));
        add_action('admin_enqueue_scripts', $this->enqueueAdminAssets(...));
        
        // Add modern performance optimizations
        add_filter('script_loader_tag', $this->addModernScriptAttributes(...), 10, 3);
        add_filter('style_loader_tag', $this->addModernStyleAttributes(...), 10, 4);
    }
    
    /**
     * Enqueue frontend assets with modern optimizations
     */
    public function enqueueAssets(): void {
        // Only load for logged-in users
        if (!is_user_logged_in()) {
            return;
        }
        
        // Enqueue modern CSS with container query support detection
        wp_enqueue_style(
            'upn-frontend',
            UPN_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            $this->version,
            'all'
        );
        
        // Add inline CSS for container query fallback
        $this->addContainerQueryFallback();
        
        // Enqueue modern JavaScript as module
        wp_enqueue_script(
            'upn-frontend',
            UPN_PLUGIN_URL . 'assets/js/frontend.js',
            [], // No dependencies - modern vanilla JS
            $this->version,
            ['strategy' => 'defer', 'in_footer' => true]
        );
        
        // Localize script with enhanced security and modern features
        $this->localizeScript();
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueueAdminAssets(string $hook): void {
        // Only load on our admin pages
        if (!str_contains($hook, 'user-page-notes')) {
            return;
        }
        
        wp_enqueue_style(
            'upn-admin',
            UPN_PLUGIN_URL . 'assets/css/admin.css',
            ['wp-admin', 'forms'],
            $this->version
        );
        
        wp_enqueue_script(
            'upn-admin',
            UPN_PLUGIN_URL . 'assets/js/admin.js',
            ['wp-api', 'wp-i18n'],
            $this->version,
            ['strategy' => 'defer', 'in_footer' => true]
        );
        
        // Set up translations for admin JS
        wp_set_script_translations('upn-admin', 'user-page-notes');
    }
    
    /**
     * Localize script with modern data structure
     */
    private function localizeScript(): void {
        $localization_data = [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('upn_nonce'),
            'currentUrl' => $this->getCurrentUrl(),
            'currentTitle' => $this->getCurrentTitle(),
            'restUrl' => rest_url('upn/v1/'),
            'restNonce' => wp_create_nonce('wp_rest'),
            'isRTL' => is_rtl(),
            'locale' => get_locale(),
            'config' => [
                'maxNoteLength' => 5000,
                'debounceDelay' => 500,
                'animationDuration' => 300,
                'autoSaveDelay' => 2000,
                'supportModernFeatures' => $this->checkModernFeatureSupport()
            ],
            'strings' => [
                'addNote' => __('Add Note', 'user-page-notes'),
                'editNote' => __('Edit Note', 'user-page-notes'),
                'saveNote' => __('Save Note', 'user-page-notes'),
                'deleteNote' => __('Delete Note', 'user-page-notes'),
                'confirmDelete' => __('Are you sure you want to delete this note?', 'user-page-notes'),
                'loading' => __('Loading...', 'user-page-notes'),
                'saved' => __('Note saved successfully!', 'user-page-notes'),
                'errorSaving' => __('Error saving note. Please try again.', 'user-page-notes'),
                'errorLoading' => __('Error loading notes. Please refresh the page.', 'user-page-notes'),
                'errorDeleting' => __('Error deleting note. Please try again.', 'user-page-notes'),
                'emptyContent' => __('Please enter some content for your note.', 'user-page-notes'),
                'tooLong' => __('Note is too long. Please shorten it.', 'user-page-notes'),
                'networkError' => __('Network error. Please check your connection.', 'user-page-notes')
            ]
        ];
        
        wp_localize_script('upn-frontend', 'upnAjax', $localization_data);
    }
    
    /**
     * Add modern script attributes for performance
     */
    public function addModernScriptAttributes(string $tag, string $handle, string $src): string {
        if (!str_starts_with($handle, 'upn-')) {
            return $tag;
        }
        
        // Add modern attributes for better performance
        $attributes = [];
        
        // Add fetchpriority for critical scripts
        if ($handle === 'upn-frontend') {
            $attributes[] = 'fetchpriority="high"';
        }
        
        // Add crossorigin for external resources (if any)
        if (str_contains($src, 'cdn.') || !str_contains($src, home_url())) {
            $attributes[] = 'crossorigin="anonymous"';
        }
        
        if (!empty($attributes)) {
            $tag = str_replace('<script ', '<script ' . implode(' ', $attributes) . ' ', $tag);
        }
        
        return $tag;
    }
    
    /**
     * Add modern style attributes
     */
    public function addModernStyleAttributes(string $html, string $handle, string $href, string $media): string {
        if (!str_starts_with($handle, 'upn-')) {
            return $html;
        }
        
        // Add fetchpriority for critical CSS
        if ($handle === 'upn-frontend') {
            $html = str_replace('<link ', '<link fetchpriority="high" ', $html);
        }
        
        return $html;
    }
    
    /**
     * Add container query fallback for older browsers
     */
    private function addContainerQueryFallback(): void {
        $fallback_css = "
            /* Container query fallback for older browsers */
            @supports not (container-type: inline-size) {
                .upn-notes-container {
                    container-type: normal;
                }
                
                @media (max-width: 600px) {
                    .upn-notes-panel {
                        width: 100vw !important;
                    }
                    
                    .upn-modal .upn-modal-content {
                        width: 95vw !important;
                        margin: 0.5rem !important;
                    }
                }
            }
            
            /* Feature detection and progressive enhancement */
            @supports not (backdrop-filter: blur(4px)) {
                .upn-modal {
                    background: rgba(0, 0, 0, 0.75) !important;
                }
            }
            
            /* High contrast mode improvements */
            @media (prefers-contrast: high) {
                .upn-note {
                    border-width: 2px !important;
                }
                
                .upn-toggle-button {
                    outline: 2px solid currentColor !important;
                    outline-offset: 2px !important;
                }
            }
        ";
        
        wp_add_inline_style('upn-frontend', $fallback_css);
    }
    
    /**
     * Check for modern feature support
     */
    private function checkModernFeatureSupport(): array {
        return [
            'containerQueries' => true, // Assume modern browsers
            'gridLayout' => true,
            'customProperties' => true,
            'logicalProperties' => true,
            'backdropFilter' => true,
            'es2022' => true,
            'modules' => true,
            'optionalChaining' => true,
            'nullishCoalescing' => true,
            'privateFields' => true
        ];
    }
    
    /**
     * Get current URL with modern security
     */
    private function getCurrentUrl(): string {
        $url = home_url(add_query_arg([], wp_unslash($_SERVER['REQUEST_URI'] ?? '')));
        return esc_url_raw($url);
    }
    
    /**
     * Get current page title with fallback
     */
    private function getCurrentTitle(): string {
        global $post;
        
        if (is_singular() && $post) {
            return get_the_title($post);
        }
        
        if (is_archive()) {
            return get_the_archive_title();
        }
        
        if (is_search()) {
            return sprintf(__('Search Results for: %s', 'user-page-notes'), get_search_query());
        }
        
        if (is_404()) {
            return __('Page Not Found', 'user-page-notes');
        }
        
        return wp_get_document_title() ?: get_bloginfo('name');
    }
    
    /**
     * Preload critical resources for better performance
     */
    public function preloadCriticalResources(): void {
        if (!is_user_logged_in()) {
            return;
        }
        
        // Preload critical CSS
        echo sprintf(
            '<link rel="preload" href="%s" as="style" fetchpriority="high">',
            esc_url(UPN_PLUGIN_URL . 'assets/css/frontend.css?ver=' . $this->version)
        );
        
        // Preload critical JavaScript
        echo sprintf(
            '<link rel="preload" href="%s" as="script" fetchpriority="high">',
            esc_url(UPN_PLUGIN_URL . 'assets/js/frontend.js?ver=' . $this->version)
        );
        
        // DNS prefetch for AJAX requests
        echo '<link rel="dns-prefetch" href="' . esc_url(admin_url()) . '">';
    }
    
    /**
     * Add resource hints for better performance
     */
    public function addResourceHints(): void {
        add_action('wp_head', $this->preloadCriticalResources(...), 1);
    }
} 