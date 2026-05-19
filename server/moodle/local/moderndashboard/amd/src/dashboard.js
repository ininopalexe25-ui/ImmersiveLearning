// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * AMD module for Modern Dashboard interactivity.
 *
 * Handles:
 * - Dark mode toggle and persistence
 * - Animated stat counter on scroll
 * - Progress bar entrance animations
 * - Intersection Observer for card animations
 *
 * @module     local_moderndashboard/dashboard
 * @copyright  2024 Modern Moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/log'], function($, Log) {

    'use strict';

    // ---------------------------------------------------------------------------
    // Constants
    // ---------------------------------------------------------------------------
    const DARK_MODE_KEY   = 'moderndashboard_darkmode';
    const ROOT_CLASS      = 'dark-mode';
    const BODY            = document.body;
    const STORAGE         = window.localStorage;

    // ---------------------------------------------------------------------------
    // Dark mode
    // ---------------------------------------------------------------------------

    /**
     * Read saved preference from localStorage.
     * @returns {boolean}
     */
    function isDarkModeSaved() {
        try {
            return STORAGE.getItem(DARK_MODE_KEY) === '1';
        } catch (e) {
            return false;
        }
    }

    /**
     * Save dark mode preference.
     * @param {boolean} enabled
     */
    function saveDarkMode(enabled) {
        try {
            STORAGE.setItem(DARK_MODE_KEY, enabled ? '1' : '0');
        } catch (e) {
            Log.debug('moderndashboard: localStorage unavailable');
        }
    }

    /**
     * Apply or remove dark mode from the document.
     * @param {boolean} enable
     */
    function applyDarkMode(enable) {
        if (enable) {
            BODY.classList.add(ROOT_CLASS);
        } else {
            BODY.classList.remove(ROOT_CLASS);
        }
        updateToggleIcon(enable);
    }

    /**
     * Update the toggle button icon to reflect current state.
     * @param {boolean} isDark
     */
    function updateToggleIcon(isDark) {
        const btn = document.getElementById('md-darkmode-toggle');
        if (!btn) {
            return;
        }
        // Sun icon for dark mode (click to go light), moon for light mode.
        btn.innerHTML = isDark
            ? `<svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                 <circle cx="12" cy="12" r="5"/>
                 <line x1="12" y1="1" x2="12" y2="3" stroke="currentColor" stroke-width="2"/>
                 <line x1="12" y1="21" x2="12" y2="23" stroke="currentColor" stroke-width="2"/>
                 <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" stroke="currentColor" stroke-width="2"/>
                 <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" stroke="currentColor" stroke-width="2"/>
                 <line x1="1" y1="12" x2="3" y2="12" stroke="currentColor" stroke-width="2"/>
                 <line x1="21" y1="12" x2="23" y2="12" stroke="currentColor" stroke-width="2"/>
                 <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" stroke="currentColor" stroke-width="2"/>
                 <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" stroke="currentColor" stroke-width="2"/>
               </svg>`
            : `<svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                 <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
               </svg>`;
        btn.setAttribute('aria-pressed', String(isDark));
    }

    /**
     * Bind click handler on the dark mode toggle button.
     */
    function initDarkMode() {
        // Restore saved preference immediately.
        const saved = isDarkModeSaved();
        applyDarkMode(saved);

        $(document).on('click', '#md-darkmode-toggle', function() {
            const isCurrentlyDark = BODY.classList.contains(ROOT_CLASS);
            applyDarkMode(!isCurrentlyDark);
            saveDarkMode(!isCurrentlyDark);
        });
    }

    // ---------------------------------------------------------------------------
    // Animated stat counters
    // ---------------------------------------------------------------------------

    /**
     * Animate a numeric value from 0 to target.
     * @param {HTMLElement} el - Element whose textContent to animate.
     * @param {number} target  - Target integer value.
     * @param {number} duration - Animation duration in ms.
     */
    function animateCounter(el, target, duration) {
        const start     = performance.now();
        const startVal  = 0;

        function step(now) {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            // Ease-out cubic.
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(startVal + (target - startVal) * eased);
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = target;
            }
        }
        requestAnimationFrame(step);
    }

    /**
     * Initialise stat counters using IntersectionObserver for
     * lazy trigger when the stats section scrolls into view.
     */
    function initStatCounters() {
        const statEls = document.querySelectorAll('[data-md-stat]');
        if (!statEls.length) {
            return;
        }

        if (!('IntersectionObserver' in window)) {
            // Fallback: just display the numbers.
            return;
        }

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const el     = entry.target;
                    const target = parseInt(el.textContent, 10) || 0;
                    el.textContent = '0';
                    animateCounter(el, target, 900);
                    observer.unobserve(el);
                }
            });
        }, {threshold: 0.4});

        statEls.forEach(function(el) {
            observer.observe(el);
        });
    }

    // ---------------------------------------------------------------------------
    // Progress bar animation
    // ---------------------------------------------------------------------------

    /**
     * Animate progress bars from 0 to their data-progress value
     * once they scroll into view.
     */
    function initProgressBars() {
        const bars = document.querySelectorAll('.md-progress-bar__fill[data-progress]');
        if (!bars.length) {
            return;
        }

        // Temporarily set width to 0 so we can animate in.
        bars.forEach(function(bar) {
            bar.style.width = '0%';
        });

        if (!('IntersectionObserver' in window)) {
            bars.forEach(function(bar) {
                bar.style.width = bar.dataset.progress + '%';
            });
            return;
        }

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const bar = entry.target;
                    // Small delay for stagger effect.
                    setTimeout(function() {
                        bar.style.width = (bar.dataset.progress || 0) + '%';
                    }, 100);
                    observer.unobserve(bar);
                }
            });
        }, {threshold: 0.2});

        bars.forEach(function(bar) {
            observer.observe(bar);
        });
    }

    // ---------------------------------------------------------------------------
    // Card entrance animation
    // ---------------------------------------------------------------------------

    /**
     * Fade + slide-up course cards on scroll into view.
     */
    function initCardAnimations() {
        const cards = document.querySelectorAll('.md-course-card, .md-stat-card, .md-activity-item');
        if (!cards.length || !('IntersectionObserver' in window)) {
            return;
        }

        // Set initial hidden state via inline style (avoids FOUC).
        cards.forEach(function(card, i) {
            card.style.opacity    = '0';
            card.style.transform  = 'translateY(16px)';
            card.style.transition = `opacity .4s ease ${i * 0.05}s, transform .4s ease ${i * 0.05}s`;
        });

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.style.opacity   = '1';
                    entry.target.style.transform = 'translateY(0)';
                    observer.unobserve(entry.target);
                }
            });
        }, {threshold: 0.1});

        cards.forEach(function(card) {
            observer.observe(card);
        });
    }

    // ---------------------------------------------------------------------------
    // Inject custom color properties from PHP settings
    // ---------------------------------------------------------------------------

    /**
     * Inject CSS custom properties from plugin settings into :root.
     * (Fallback in case the template-level style block doesn't run first.)
     * @param {object} params
     */
    function injectCSSVars(params) {
        // Values come from Mustache output in the page already,
        // but we set them here too as a belt-and-suspenders approach.
        if (params && params.primarycolor) {
            document.documentElement.style.setProperty('--md-primary', params.primarycolor);
        }
    }

    // ---------------------------------------------------------------------------
    // Public init
    // ---------------------------------------------------------------------------

    return {
        /**
         * Initialise all dashboard enhancements.
         * @param {object} params - {userid, wwwroot}
         */
        init: function(params) {
            Log.debug('local_moderndashboard: init', params);

            // Wait for DOM ready.
            $(function() {
                initDarkMode();
                initStatCounters();
                initProgressBars();
                initCardAnimations();
                injectCSSVars(params);
            });
        }
    };
});
