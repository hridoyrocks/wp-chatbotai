/**
 * Banglay IELTS Chatbot Admin Dashboard JavaScript
 * Professional admin interface interactions
 */

(function ($) {
    'use strict';

    // Global admin object
    window.BiicAdmin = {
        init: function () {
            this.bindEvents();
            this.initComponents();
            this.loadDashboardData();
        },

        bindEvents: function () {
            // Dashboard refresh
            $('.biic-refresh-dashboard').on('click', this.refreshDashboard);

            // Quick stats refresh
            $('.biic-quick-stats .biic-stat-item').on('click', this.refreshStat);

            // Modal handling
            this.initModals();

            // Form validation
            this.initFormValidation();

            // Data tables
            this.initDataTables();

            // Auto-refresh for real-time data
            this.initAutoRefresh();

            // Keyboard shortcuts
            this.initKeyboardShortcuts();
        },

        initComponents: function () {
            // Initialize tooltips
            this.initTooltips();

            // Initialize date pickers
            this.initDatePickers();

            // Initialize select2 dropdowns
            this.initSelect2();

            // Initialize charts
            this.initCharts();

            // Initialize notifications
            this.initNotifications();
        },

        loadDashboardData: function () {
            this.showLoading('.biic-dashboard-content');

            $.ajax({
                url: biic_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'biic_get_dashboard_stats',
                    nonce: biic_admin.nonce
                },
                success: function (response) {
                    if (response.success) {
                        BiicAdmin.updateDashboardStats(response.data);
                    } else {
                        BiicAdmin.showNotice('Failed to load dashboard data', 'error');
                    }
                },
                error: function () {
                    BiicAdmin.showNotice('Network error occurred', 'error');
                },
                complete: function () {
                    BiicAdmin.hideLoading('.biic-dashboard-content');
                }
            });
        },

        refreshDashboard: function (e) {
            e.preventDefault();
            BiicAdmin.loadDashboardData();
            BiicAdmin.showNotice('Dashboard data refreshed', 'success', 2000);
        },

        refreshStat: function (e) {
            e.preventDefault();
            var $stat = $(this);
            var statType = $stat.data('stat-type');

            if (!statType) return;

            $stat.addClass('loading');

            $.ajax({
                url: biic_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'biic_get_stat_data',
                    stat_type: statType,
                    nonce: biic_admin.nonce
                },
                success: function (response) {
                    if (response.success) {
                        BiicAdmin.updateStat($stat, response.data);
                    }
                },
                complete: function () {
                    $stat.removeClass('loading');
                }
            });
        },

        updateDashboardStats: function (data) {
            // Update quick stats
            $('.biic-stat-value[data-stat="conversations_today"]').text(data.conversations_today || 0);
            $('.biic-stat-value[data-stat="messages_today"]').text(data.messages_today || 0);
            $('.biic-stat-value[data-stat="leads_today"]').text(data.leads_today || 0);
            $('.biic-stat-value[data-stat="active_sessions"]').text(data.active_sessions || 0);

            // Update charts
            this.updateCharts(data);

            // Update top intents list
            this.updateTopIntents(data.top_intents || []);

            // Update course interests
            this.updateCourseInterests(data.course_interests || []);

            // Update recent conversations
            this.updateRecentConversations(data.recent_conversations || []);
        },

        updateCharts: function (data) {
            // Update conversations chart if it exists
            if (window.conversationsChart) {
                // Update chart data
                window.conversationsChart.data.datasets[0].data = data.daily_conversations || [];
                window.conversationsChart.update();
            }

            // Update leads funnel chart
            if (window.leadsFunnelChart) {
                window.leadsFunnelChart.data.datasets[0].data = [
                    data.total_visitors || 0,
                    data.chat_engaged || 0,
                    data.leads_generated || 0,
                    data.leads_converted || 0
                ];
                window.leadsFunnelChart.update();
            }
        },

        updateTopIntents: function (intents) {
            var $container = $('.biic-intents-list');
            if (!$container.length) return;

            $container.empty();

            var maxCount = intents.length > 0 ? intents[0].count : 1;

            intents.forEach(function (intent, index) {
                var percentage = Math.round((intent.count / maxCount) * 100);
                var $item = $(`
                    <div class="biic-intent-item">
                        <div class="biic-intent-rank">${index + 1}</div>
                        <div class="biic-intent-info">
                            <span class="biic-intent-name">${intent.detected_intent}</span>
                            <div class="biic-intent-bar">
                                <div class="biic-intent-progress" style="width: ${percentage}%"></div>
                            </div>
                        </div>
                        <div class="biic-intent-count">${intent.count}</div>
                    </div>
                `);
                $container.append($item);
            });
        },

        updateCourseInterests: function (courses) {
            var $container = $('.biic-course-stats');
            if (!$container.length) return;

            $container.empty();

            var total = courses.reduce((sum, course) => sum + parseInt(course.count), 0);

            courses.forEach(function (course) {
                var percentage = total > 0 ? Math.round((course.count / total) * 100) : 0;
                var color = BiicAdmin.getCourseColor(course.course_interest);

                var $item = $(`
                    <div class="biic-course-item">
                        <div class="biic-course-header">
                            <div class="biic-course-dot" style="background-color: ${color}"></div>
                            <div class="biic-course-name">${BiicAdmin.formatCourseName(course.course_interest)}</div>
                            <div class="biic-course-percentage">${percentage}%</div>
                        </div>
                        <div class="biic-course-bar">
                            <div class="biic-course-progress" style="width: ${percentage}%; background-color: ${color}"></div>
                        </div>
                        <div class="biic-course-count">${course.count} leads</div>
                    </div>
                `);
                $container.append($item);
            });
        },

        updateRecentConversations: function (conversations) {
            var $container = $('.biic-recent-conversations');
            if (!$container.length) return;

            $container.empty();

            conversations.forEach(function (conversation) {
                var leadBadge = BiicAdmin.getLeadBadge(conversation.lead_score);
                var deviceIcon = BiicAdmin.getDeviceIcon(conversation.device_type);
                var timeAgo = BiicAdmin.timeAgo(conversation.started_at);

                var $item = $(`
                    <div class="biic-conversation-item" data-session-id="${conversation.session_id}">
                        <div class="biic-conversation-avatar">
                            ${conversation.lead_name ? conversation.lead_name.charAt(0).toUpperCase() : '👤'}
                        </div>
                        <div class="biic-conversation-content">
                            <div class="biic-conversation-header">
                                <span class="biic-conversation-name">
                                    ${conversation.lead_name || 'Anonymous User'}
                                </span>
                                ${leadBadge}
                            </div>
                            <div class="biic-conversation-meta">
                                <span><i class="dashicons dashicons-clock"></i> ${timeAgo}</span>
                                <span><i class="dashicons dashicons-format-chat"></i> ${conversation.message_count} messages</span>
                                <span>${deviceIcon} ${conversation.device_type || 'Unknown'}</span>
                            </div>
                        </div>
                        <div class="biic-conversation-actions">
                            <button type="button" class="button button-small biic-view-conversation" 
                                    data-session-id="${conversation.session_id}" 
                                    title="View Conversation">
                                <span class="dashicons dashicons-visibility"></span>
                            </button>
                        </div>
                    </div>
                `);
                $container.append($item);
            });
        },

        initModals: function () {
            // Modal open/close handling
            $(document).on('click', '[data-modal]', function (e) {
                e.preventDefault();
                var modalId = $(this).data('modal');
                BiicAdmin.openModal(modalId);
            });

            $(document).on('click', '.biic-modal-close, .biic-modal-backdrop', function (e) {
                e.preventDefault();
                BiicAdmin.closeModal();
            });

            // Prevent modal close when clicking inside modal content
            $(document).on('click', '.biic-modal-content', function (e) {
                e.stopPropagation();
            });

            // ESC key to close modal
            $(document).on('keydown', function (e) {
                if (e.keyCode === 27) { // ESC key
                    BiicAdmin.closeModal();
                }
            });
        },

        openModal: function (modalId) {
            var $modal = $('#' + modalId);
            if ($modal.length) {
                $modal.addClass('active');
                $('body').addClass('biic-modal-open');

                // Focus first input
                setTimeout(function () {
                    $modal.find('input:first').focus();
                }, 100);
            }
        },

        closeModal: function () {
            $('.biic-modal').removeClass('active');
            $('body').removeClass('biic-modal-open');
        },

        initFormValidation: function () {
            // Real-time form validation
            $(document).on('blur', '.biic-form-field[required]', function () {
                BiicAdmin.validateField($(this));
            });

            $(document).on('submit', '.biic-form', function (e) {
                if (!BiicAdmin.validateForm($(this))) {
                    e.preventDefault();
                    return false;
                }
            });
        },

        validateField: function ($field) {
            var value = $field.val().trim();
            var fieldType = $field.attr('type');
            var isValid = true;
            var errorMessage = '';

            // Required field validation
            if ($field.prop('required') && !value) {
                isValid = false;
                errorMessage = 'This field is required';
            }

            // Email validation
            if (fieldType === 'email' && value) {
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    isValid = false;
                    errorMessage = 'Please enter a valid email address';
                }
            }

            // Phone validation (Bangladesh)
            if ($field.hasClass('phone-field') && value) {
                var phoneRegex = /^(\+880|880|01)[0-9]{8,9}$/;
                if (!phoneRegex.test(value)) {
                    isValid = false;
                    errorMessage = 'Please enter a valid phone number';
                }
            }

            // Update field state
            $field.toggleClass('error', !isValid);
            var $error = $field.siblings('.field-error');

            if (!isValid) {
                if (!$error.length) {
                    $error = $('<div class="field-error"></div>');
                    $field.after($error);
                }
                $error.text(errorMessage);
            } else {
                $error.remove();
            }

            return isValid;
        },

        validateForm: function ($form) {
            var isValid = true;

            $form.find('.biic-form-field[required]').each(function () {
                if (!BiicAdmin.validateField($(this))) {
                    isValid = false;
                }
            });

            return isValid;
        },

        initDataTables: function () {
            // Initialize sortable tables
            $('.biic-data-table').each(function () {
                BiicAdmin.initSortableTable($(this));
            });

            // Search functionality
            $('.biic-table-search').on('input', function () {
                var query = $(this).val().toLowerCase();
                var $table = $(this).closest('.biic-table-container').find('.biic-data-table');

                $table.find('tbody tr').each(function () {
                    var text = $(this).text().toLowerCase();
                    $(this).toggle(text.indexOf(query) > -1);
                });
            });
        },

        initSortableTable: function ($table) {
            $table.find('th[data-sort]').addClass('sortable').on('click', function () {
                var $th = $(this);
                var column = $th.data('sort');
                var order = $th.hasClass('asc') ? 'desc' : 'asc';

                // Remove sort classes from all headers
                $table.find('th').removeClass('asc desc');

                // Add sort class to current header
                $th.addClass(order);

                // Sort table rows
                BiicAdmin.sortTable($table, column, order);
            });
        },

        sortTable: function ($table, column, order) {
            var $tbody = $table.find('tbody');
            var rows = $tbody.find('tr').toArray();

            rows.sort(function (a, b) {
                var aVal = $(a).find('[data-sort-value]').data('sort-value') || $(a).find('td').eq(column).text();
                var bVal = $(b).find('[data-sort-value]').data('sort-value') || $(b).find('td').eq(column).text();

                // Try to parse as numbers
                var aNum = parseFloat(aVal);
                var bNum = parseFloat(bVal);

                if (!isNaN(aNum) && !isNaN(bNum)) {
                    return order === 'asc' ? aNum - bNum : bNum - aNum;
                }

                // String comparison
                aVal = aVal.toString().toLowerCase();
                bVal = bVal.toString().toLowerCase();

                if (order === 'asc') {
                    return aVal.localeCompare(bVal);
                } else {
                    return bVal.localeCompare(aVal);
                }
            });

            $tbody.empty().append(rows);
        },

        initAutoRefresh: function () {
            // Auto-refresh active sessions count every 30 seconds
            setInterval(function () {
                BiicAdmin.refreshActiveSessions();
            }, 30000);

            // Auto-refresh dashboard every 5 minutes
            setInterval(function () {
                BiicAdmin.loadDashboardData();
            }, 300000);
        },

        refreshActiveSessions: function () {
            $.ajax({
                url: biic_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'biic_get_active_sessions',
                    nonce: biic_admin.nonce
                },
                success: function (response) {
                    if (response.success) {
                        $('.biic-stat-value[data-stat="active_sessions"]').text(response.data.count);
                    }
                }
            });
        },

        initKeyboardShortcuts: function () {
            $(document).on('keydown', function (e) {
                // Ctrl/Cmd + R: Refresh dashboard
                if ((e.ctrlKey || e.metaKey) && e.keyCode === 82) {
                    e.preventDefault();
                    BiicAdmin.refreshDashboard(e);
                }

                // Ctrl/Cmd + K: Open search modal
                if ((e.ctrlKey || e.metaKey) && e.keyCode === 75) {
                    e.preventDefault();
                    BiicAdmin.openModal('search-modal');
                }

                // Ctrl/Cmd + N: New lead modal
                if ((e.ctrlKey || e.metaKey) && e.keyCode === 78) {
                    e.preventDefault();
                    BiicAdmin.openModal('new-lead-modal');
                }
            });
        },

        initTooltips: function () {
            // Initialize tooltips
            $('[data-tooltip]').each(function () {
                var $element = $(this);
                var text = $element.data('tooltip');

                $element.on('mouseenter', function () {
                    BiicAdmin.showTooltip($element, text);
                }).on('mouseleave', function () {
                    BiicAdmin.hideTooltip();
                });
            });
        },

        showTooltip: function ($element, text) {
            var $tooltip = $('<div class="biic-tooltip">' + text + '</div>');
            $('body').append($tooltip);

            var elementRect = $element[0].getBoundingClientRect();
            var tooltipWidth = $tooltip.outerWidth();

            $tooltip.css({
                position: 'fixed',
                top: elementRect.top - $tooltip.outerHeight() - 8,
                left: elementRect.left + (elementRect.width / 2) - (tooltipWidth / 2),
                zIndex: 9999
            });
        },

        hideTooltip: function () {
            $('.biic-tooltip').remove();
        },

        initDatePickers: function () {
            // Initialize jQuery UI datepickers
            $('.biic-datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true
            });
        },

        initSelect2: function () {
            // Initialize Select2 dropdowns if available
            if ($.fn.select2) {
                $('.biic-select2').select2({
                    theme: 'default',
                    width: '100%'
                });
            }
        },

        initCharts: function () {
            // Chart initialization will be handled by individual chart functions
            // This is just a placeholder for common chart settings
            if (typeof Chart !== 'undefined') {
                Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
                Chart.defaults.plugins.legend.display = false;
            }
        },

        initNotifications: function () {
            // Check for browser notifications permission
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        },

        showNotification: function (message, type, duration) {
            type = type || 'info';
            duration = duration || 5000;

            var $notification = $(`
                <div class="biic-notification biic-notification-${type}">
                    <div class="biic-notification-content">
                        <span class="biic-notification-message">${message}</span>
                        <button type="button" class="biic-notification-close">&times;</button>
                    </div>
                </div>
            `);

            $('.biic-notifications-container').append($notification);

            // Auto-hide after duration
            setTimeout(function () {
                $notification.fadeOut(function () {
                    $(this).remove();
                });
            }, duration);

            // Manual close
            $notification.find('.biic-notification-close').on('click', function () {
                $notification.fadeOut(function () {
                    $(this).remove();
                });
            });
        },

        showLoading: function (selector) {
            var $container = $(selector);
            if (!$container.find('.biic-loading-overlay').length) {
                $container.append('<div class="biic-loading-overlay"><div class="biic-spinner"></div></div>');
            }
        },

        hideLoading: function (selector) {
            $(selector).find('.biic-loading-overlay').remove();
        },

        // Utility functions
        timeAgo: function (datetime) {
            var date = new Date(datetime);
            var now = new Date();
            var diff = Math.floor((now - date) / 1000);

            if (diff < 60) return 'Just now';
            if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
            if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
            if (diff < 2592000) return Math.floor(diff / 86400) + ' days ago';

            return date.toLocaleDateString();
        },

        formatNumber: function (num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },

        getCourseColor: function (course) {
            var colors = {
                'ielts_comprehensive': '#E53E3E',
                'ielts_focus': '#38A169',
                'ielts_crash': '#D69E2E',
                'online_course': '#3182CE',
                'study_abroad': '#805AD5'
            };
            return colors[course] || '#6B7280';
        },

        formatCourseName: function (course) {
            var names = {
                'ielts_comprehensive': 'IELTS Comprehensive',
                'ielts_focus': 'IELTS Focus',
                'ielts_crash': 'IELTS Crash',
                'online_course': 'Online Course',
                'study_abroad': 'Study Abroad'
            };
            return names[course] || course;
        },

        getLeadBadge: function (score) {
            if (score >= 80) {
                return '<span class="biic-lead-badge biic-lead-hot">🔥 Hot</span>';
            } else if (score >= 50) {
                return '<span class="biic-lead-badge biic-lead-warm">🌡️ Warm</span>';
            } else {
                return '<span class="biic-lead-badge biic-lead-cold">❄️ Cold</span>';
            }
        },

        getDeviceIcon: function (device) {
            var icons = {
                'mobile': '📱',
                'tablet': '📟',
                'desktop': '💻'
            };
            return icons[device] || '📱';
        }
    };

    // Initialize when document is ready
    $(document).ready(function () {
        BiicAdmin.init();
    });

    // Export to global scope
    window.BiicAdmin = BiicAdmin;

})(jQuery);