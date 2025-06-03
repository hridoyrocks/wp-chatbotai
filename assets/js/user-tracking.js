/**
 * User Tracking System JavaScript
 * assets/js/user-tracking.js
 */

(function ($) {
    'use strict';

    // User tracking object
    window.BiicTracking = {
        sessionId: null,
        trackingEnabled: true,
        events: [],
        maxEvents: 100,

        init: function () {
            if (!this.trackingEnabled) return;

            this.initSession();
            this.bindEvents();
            this.startTracking();
            this.setupPeriodicSync();
        },

        initSession: function () {
            // Get or create session ID
            this.sessionId = this.getSessionId();

            // Track page load
            this.trackEvent('page_load', {
                url: window.location.href,
                title: document.title,
                referrer: document.referrer,
                timestamp: new Date().toISOString()
            });
        },

        getSessionId: function () {
            // Try to get from localStorage first
            let sessionId = localStorage.getItem('biic_session_id');

            if (!sessionId) {
                // Generate new session ID
                sessionId = 'biic_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                localStorage.setItem('biic_session_id', sessionId);
            }

            return sessionId;
        },

        bindEvents: function () {
            // Track clicks
            $(document).on('click', 'a, button', this.handleClick.bind(this));

            // Track form submissions
            $(document).on('submit', 'form', this.handleFormSubmit.bind(this));

            // Track scroll depth
            let maxScroll = 0;
            $(window).on('scroll', () => {
                const scrollPercent = Math.round(
                    ($(window).scrollTop() / ($(document).height() - $(window).height())) * 100
                );

                if (scrollPercent > maxScroll) {
                    maxScroll = scrollPercent;

                    // Track milestones
                    if ([25, 50, 75, 100].includes(scrollPercent)) {
                        this.trackEvent('scroll_milestone', {
                            percent: scrollPercent,
                            timestamp: new Date().toISOString()
                        });
                    }
                }
            });

            // Track page visibility changes
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.trackEvent('page_hidden', {
                        timestamp: new Date().toISOString()
                    });
                } else {
                    this.trackEvent('page_visible', {
                        timestamp: new Date().toISOString()
                    });
                }
            });

            // Track before page unload
            window.addEventListener('beforeunload', () => {
                this.trackEvent('page_unload', {
                    timestamp: new Date().toISOString()
                });
                this.syncEvents(); // Sync remaining events
            });
        },

        startTracking: function () {
            // Track session start
            this.trackEvent('session_start', {
                sessionId: this.sessionId,
                userAgent: navigator.userAgent,
                screenResolution: screen.width + 'x' + screen.height,
                viewport: window.innerWidth + 'x' + window.innerHeight,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                language: navigator.language,
                timestamp: new Date().toISOString()
            });

            // Track device info
            this.trackDeviceInfo();

            // Track performance metrics
            this.trackPerformance();
        },

        trackDeviceInfo: function () {
            const deviceInfo = {
                type: this.detectDeviceType(),
                browser: this.detectBrowser(),
                os: this.detectOS(),
                connection: this.getConnectionInfo(),
                timestamp: new Date().toISOString()
            };

            this.trackEvent('device_info', deviceInfo);
        },

        detectDeviceType: function () {
            const userAgent = navigator.userAgent;

            if (/tablet|ipad|playbook|silk/i.test(userAgent)) {
                return 'tablet';
            }
            if (/mobile|iphone|ipod|android|blackberry|opera|mini|windows\sce|palm|smartphone|iemobile/i.test(userAgent)) {
                return 'mobile';
            }
            return 'desktop';
        },

        detectBrowser: function () {
            const userAgent = navigator.userAgent;

            if (userAgent.indexOf('Chrome') > -1) return 'Chrome';
            if (userAgent.indexOf('Firefox') > -1) return 'Firefox';
            if (userAgent.indexOf('Safari') > -1) return 'Safari';
            if (userAgent.indexOf('Edge') > -1) return 'Edge';
            if (userAgent.indexOf('Opera') > -1) return 'Opera';

            return 'Unknown';
        },

        detectOS: function () {
            const userAgent = navigator.userAgent;

            if (userAgent.indexOf('Windows') > -1) return 'Windows';
            if (userAgent.indexOf('Mac') > -1) return 'macOS';
            if (userAgent.indexOf('Linux') > -1) return 'Linux';
            if (userAgent.indexOf('Android') > -1) return 'Android';
            if (userAgent.indexOf('iOS') > -1) return 'iOS';

            return 'Unknown';
        },

        getConnectionInfo: function () {
            if ('connection' in navigator) {
                const connection = navigator.connection;
                return {
                    effectiveType: connection.effectiveType,
                    downlink: connection.downlink,
                    rtt: connection.rtt
                };
            }
            return null;
        },

        trackPerformance: function () {
            if ('performance' in window) {
                window.addEventListener('load', () => {
                    setTimeout(() => {
                        const timing = performance.timing;
                        const navigation = performance.navigation;

                        const performanceData = {
                            loadTime: timing.loadEventEnd - timing.navigationStart,
                            domReady: timing.domContentLoadedEventEnd - timing.navigationStart,
                            firstPaint: this.getFirstPaint(),
                            navigationType: navigation.type,
                            timestamp: new Date().toISOString()
                        };

                        this.trackEvent('performance', performanceData);
                    }, 1000);
                });
            }
        },

        getFirstPaint: function () {
            if ('getEntriesByType' in performance) {
                const paintEntries = performance.getEntriesByType('paint');
                const firstPaint = paintEntries.find(entry => entry.name === 'first-paint');
                return firstPaint ? firstPaint.startTime : null;
            }
            return null;
        },

        handleClick: function (e) {
            const $target = $(e.target);
            const tagName = $target.prop('tagName').toLowerCase();

            // Track important clicks
            const trackableSelectors = [
                'a[href*="tel:"]',
                'a[href*="mailto:"]',
                'a[href*="banglayelts.com"]',
                '.biic-quick-reply',
                '.biic-send-button',
                '.biic-chat-fab'
            ];

            let shouldTrack = false;
            let clickType = 'click';

            trackableSelectors.forEach(selector => {
                if ($target.is(selector) || $target.closest(selector).length) {
                    shouldTrack = true;

                    if (selector.includes('tel:')) clickType = 'phone_click';
                    else if (selector.includes('mailto:')) clickType = 'email_click';
                    else if (selector.includes('biic-')) clickType = 'chatbot_interaction';
                }
            });

            if (shouldTrack) {
                this.trackEvent(clickType, {
                    element: tagName,
                    text: $target.text().trim().substring(0, 100),
                    href: $target.attr('href') || $target.closest('a').attr('href'),
                    classes: $target.attr('class'),
                    timestamp: new Date().toISOString()
                });
            }
        },

        handleFormSubmit: function (e) {
            const $form = $(e.target);
            const formId = $form.attr('id');
            const formClass = $form.attr('class');

            // Track form submission
            this.trackEvent('form_submit', {
                formId: formId,
                formClass: formClass,
                action: $form.attr('action'),
                method: $form.attr('method') || 'GET',
                fieldCount: $form.find('input, select, textarea').length,
                timestamp: new Date().toISOString()
            });
        },

        trackEvent: function (eventType, eventData) {
            const event = {
                type: eventType,
                data: eventData,
                sessionId: this.sessionId,
                timestamp: new Date().toISOString(),
                url: window.location.href
            };

            // Add to events array
            this.events.push(event);

            // Keep only recent events
            if (this.events.length > this.maxEvents) {
                this.events = this.events.slice(-this.maxEvents);
            }

            // Auto-sync if events queue is getting full
            if (this.events.length >= 10) {
                this.syncEvents();
            }
        },

        syncEvents: function () {
            if (this.events.length === 0) return;

            const eventsToSync = [...this.events];
            this.events = []; // Clear events array

            // Send events to server
            $.ajax({
                url: biicConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'biic_track_event',
                    events: JSON.stringify(eventsToSync),
                    nonce: biicConfig.nonce
                },
                success: (response) => {
                    // Events synced successfully
                    console.log('Events synced:', eventsToSync.length);
                },
                error: (xhr, status, error) => {
                    // Put events back if sync failed
                    this.events = eventsToSync.concat(this.events);
                    console.warn('Failed to sync events:', error);
                }
            });
        },

        setupPeriodicSync: function () {
            // Sync events every 30 seconds
            setInterval(() => {
                this.syncEvents();
            }, 30000);

            // Also sync when page becomes visible (user returns to tab)
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) {
                    this.syncEvents();
                }
            });
        },

        // Chatbot specific tracking
        trackChatEvent: function (eventType, eventData) {
            this.trackEvent('chatbot_' + eventType, {
                ...eventData,
                chatSessionId: window.biicChatbot?.sessionId,
                timestamp: new Date().toISOString()
            });
        },

        trackMessageSent: function (message) {
            this.trackChatEvent('message_sent', {
                messageLength: message.length,
                messageWords: message.split(' ').length,
                timestamp: new Date().toISOString()
            });
        },

        trackMessageReceived: function (response) {
            this.trackChatEvent('message_received', {
                responseLength: response.length,
                hasQuickReplies: response.includes('quick_replies'),
                hasLeadForm: response.includes('lead_form'),
                timestamp: new Date().toISOString()
            });
        },

        trackChatOpened: function () {
            this.trackChatEvent('chat_opened', {
                timestamp: new Date().toISOString()
            });
        },

        trackChatClosed: function () {
            this.trackChatEvent('chat_closed', {
                timestamp: new Date().toISOString()
            });
        },

        trackLeadSubmitted: function (leadData) {
            this.trackChatEvent('lead_submitted', {
                hasName: !!leadData.name,
                hasEmail: !!leadData.email,
                hasCourseInterest: !!leadData.course_interest,
                timestamp: new Date().toISOString()
            });
        },

        // Heatmap tracking
        trackMouseMovement: function () {
            let mouseEvents = [];

            $(document).on('mousemove', (e) => {
                mouseEvents.push({
                    x: e.pageX,
                    y: e.pageY,
                    timestamp: Date.now()
                });

                // Keep only recent movements
                if (mouseEvents.length > 100) {
                    mouseEvents = mouseEvents.slice(-50);
                }
            });

            // Send heatmap data periodically
            setInterval(() => {
                if (mouseEvents.length > 10) {
                    this.trackEvent('mouse_heatmap', {
                        movements: mouseEvents,
                        viewport: {
                            width: window.innerWidth,
                            height: window.innerHeight
                        }
                    });
                    mouseEvents = [];
                }
            }, 60000); // Every minute
        },

        // A/B testing support
        trackExperiment: function (experimentName, variant) {
            this.trackEvent('experiment', {
                experiment: experimentName,
                variant: variant,
                timestamp: new Date().toISOString()
            });

            // Store for session consistency
            sessionStorage.setItem(`biic_experiment_${experimentName}`, variant);
        },

        getExperimentVariant: function (experimentName) {
            return sessionStorage.getItem(`biic_experiment_${experimentName}`);
        },

        // Conversion tracking
        trackConversion: function (conversionType, value = null) {
            this.trackEvent('conversion', {
                type: conversionType,
                value: value,
                timestamp: new Date().toISOString()
            });
        },

        // Error tracking
        trackError: function (error, context = '') {
            this.trackEvent('error', {
                message: error.message,
                stack: error.stack,
                context: context,
                url: window.location.href,
                timestamp: new Date().toISOString()
            });
        },

        // Time tracking
        trackTimeOnPage: function () {
            const startTime = Date.now();

            window.addEventListener('beforeunload', () => {
                const timeOnPage = Date.now() - startTime;
                this.trackEvent('time_on_page', {
                    duration: timeOnPage,
                    durationMinutes: Math.round(timeOnPage / 60000),
                    timestamp: new Date().toISOString()
                });
            });
        },

        // Utility methods
        enableTracking: function () {
            this.trackingEnabled = true;
        },

        disableTracking: function () {
            this.trackingEnabled = false;
            this.syncEvents(); // Sync remaining events before disabling
        },

        getSessionData: function () {
            return {
                sessionId: this.sessionId,
                eventsCount: this.events.length,
                trackingEnabled: this.trackingEnabled
            };
        }
    };

    // Initialize tracking when document is ready
    $(document).ready(function () {
        // Check if tracking is enabled and user has consented
        const trackingEnabled = !localStorage.getItem('biic_tracking_disabled');

        if (trackingEnabled) {
            BiicTracking.init();

            // Enable additional tracking features
            BiicTracking.trackTimeOnPage();

            // Optional: Enable mouse movement tracking for heatmaps
            if (window.biicConfig && window.biicConfig.enableHeatmaps) {
                BiicTracking.trackMouseMovement();
            }
        }
    });

    // Handle tracking consent
    window.addEventListener('biic-consent-given', function () {
        BiicTracking.enableTracking();
        BiicTracking.init();
    });

    window.addEventListener('biic-consent-revoked', function () {
        BiicTracking.disableTracking();
        localStorage.setItem('biic_tracking_disabled', 'true');
    });

    // Global error handler
    window.addEventListener('error', function (e) {
        if (BiicTracking.trackingEnabled) {
            BiicTracking.trackError(e.error, 'global_error_handler');
        }
    });

    // Export to global scope
    window.BiicTracking = BiicTracking;

})(jQuery);