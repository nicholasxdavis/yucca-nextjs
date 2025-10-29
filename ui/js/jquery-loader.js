/**
 * jQuery Loader with Fallback
 * Ensures jQuery loads from CDN with fallback, then loads enhancements
 * Graceful degradation if jQuery fails to load
 */

(function() {
    'use strict';
    
    // Check if jQuery is already loaded
    if (typeof jQuery !== 'undefined') {
        console.log('jQuery already loaded, version:', jQuery.fn.jquery);
        loadEnhancements();
        return;
    }
    
    // jQuery CDN URLs (multiple fallbacks)
    const jQueryCDNs = [
        'https://code.jquery.com/jquery-3.7.1.min.js',
        'https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js'
    ];
    
    let currentCDNIndex = 0;
    let loadAttempts = 0;
    const maxAttempts = 3;
    
    /**
     * Load jQuery from CDN
     */
    function loadjQuery(url) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = url;
            script.async = true;
            
            // Set integrity and crossorigin for security (if using official CDN)
            if (url.includes('code.jquery.com')) {
                script.integrity = 'sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=';
                script.crossOrigin = 'anonymous';
            }
            
            script.onload = function() {
                if (typeof jQuery !== 'undefined') {
                    console.log('✅ jQuery loaded successfully from:', url);
                    resolve();
                } else {
                    reject(new Error('jQuery loaded but not available'));
                }
            };
            
            script.onerror = function() {
                console.warn('⚠️ Failed to load jQuery from:', url);
                reject(new Error('Script load error'));
            };
            
            document.head.appendChild(script);
            
            // Timeout after 5 seconds
            setTimeout(() => {
                if (typeof jQuery === 'undefined') {
                    reject(new Error('jQuery load timeout'));
                }
            }, 5000);
        });
    }
    
    /**
     * Try loading jQuery with fallbacks
     */
    function tryLoadjQuery() {
        loadAttempts++;
        
        if (loadAttempts > maxAttempts) {
            console.warn('❌ jQuery failed to load after', maxAttempts, 'attempts');
            console.log('⚡ Core functionality will work without jQuery');
            // Dispatch event for logging/monitoring
            document.dispatchEvent(new CustomEvent('jQueryLoadFailed'));
            return;
        }
        
        const url = jQueryCDNs[currentCDNIndex];
        
        loadjQuery(url)
            .then(() => {
                // jQuery loaded successfully
                loadEnhancements();
            })
            .catch((error) => {
                console.error('jQuery load error:', error.message);
                
                // Try next CDN
                currentCDNIndex++;
                if (currentCDNIndex < jQueryCDNs.length) {
                    console.log('🔄 Trying next CDN...');
                    setTimeout(tryLoadjQuery, 500);
                } else {
                    // Reset and try again (up to maxAttempts)
                    currentCDNIndex = 0;
                    setTimeout(tryLoadjQuery, 1000);
                }
            });
    }
    
    /**
     * Load enhancements after jQuery is ready
     */
    function loadEnhancements() {
        // Verify jQuery is actually available
        if (typeof jQuery === 'undefined') {
            console.warn('Cannot load enhancements: jQuery not available');
            return;
        }
        
        // Load enhancements.js
        const script = document.createElement('script');
        script.src = getBasePath() + 'ui/js/enhancements.js';
        script.async = true;
        
        script.onload = function() {
            console.log('✨ UI Enhancements loaded');
            document.dispatchEvent(new CustomEvent('yuccaClubEnhanced'));
        };
        
        script.onerror = function() {
            console.warn('⚠️ Failed to load enhancements.js (not critical)');
        };
        
        document.head.appendChild(script);
    }
    
    /**
     * Get base path for assets
     */
    function getBasePath() {
        const scripts = document.getElementsByTagName('script');
        const currentScript = scripts[scripts.length - 1];
        const scriptPath = currentScript.src;
        
        // Extract base path
        if (scriptPath.includes('/ui/js/')) {
            return scriptPath.split('/ui/js/')[0] + '/';
        }
        
        // Fallback: try to detect from current page
        const path = window.location.pathname;
        const depth = (path.match(/\//g) || []).length - 1;
        return depth > 0 ? '../'.repeat(depth) : '';
    }
    
    /**
     * Initialize
     */
    function init() {
        // Only load if document is ready or loading
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', tryLoadjQuery);
        } else {
            tryLoadjQuery();
        }
    }
    
    // Start loading
    init();
    
})();



