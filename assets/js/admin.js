/**
 * User Page Notes Admin JavaScript
 * Handles admin interface interactions
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Add any admin-specific JavaScript functionality here
        
        // For now, just add some visual enhancements
        $('.upn-stats-widget .upn-stat-number').each(function() {
            const $this = $(this);
            const finalValue = parseInt($this.text());
            
            // Simple counter animation
            $({ counter: 0 }).animate({ counter: finalValue }, {
                duration: 1000,
                easing: 'swing',
                step: function() {
                    $this.text(Math.ceil(this.counter));
                },
                complete: function() {
                    $this.text(finalValue);
                }
            });
        });
    });

})(jQuery); 