/**
 * Banglay IELTS Chatbot Analytics JavaScript
 * assets/js/analytics.js
 */

(function ($) {
    'use strict';

    // Global analytics object
    window.BiicAnalytics = {
        charts: {},

        init: function () {
            this.bindEvents();
            this.initDatePickers();
            this.loadAnalyticsData();
            this.initCharts();
        },

        bindEvents: function () {
            // Date range change
            $('#analytics-date-from, #analytics-date-to').on('change', this.refreshAnalytics);

            // Export buttons
            $('.biic-export-btn').on('click', this.handleExport);

            // Chart type toggles
            $('.biic-chart-toggle').on('click', this.toggleChartType);

            // Refresh button
            $('.biic-refresh-analytics').on('click', this.refreshAnalytics);

            // Period quick select
            $('.biic-period-select').on('click', this.selectPeriod);
        },

        initDatePickers: function () {
            if ($.fn.datepicker) {
                $('#analytics-date-from, #analytics-date-to').datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    maxDate: new Date()
                });
            }
        },

        loadAnalyticsData: function () {
            const dateFrom = $('#analytics-date-from').val() || this.getDefaultDateFrom();
            const dateTo = $('#analytics-date-to').val() || this.getDefaultDateTo();

            this.showLoading();

            $.ajax({
                url: biic_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'biic_get_analytics_data',
                    date_from: dateFrom,
                    date_to: dateTo,
                    nonce: biic_admin.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateAnalyticsDisplay(response.data);
                        this.updateCharts(response.data);
                    } else {
                        this.showError('Failed to load analytics data');
                    }
                },
                error: () => {
                    this.showError('Network error occurred');
                },
                complete: () => {
                    this.hideLoading();
                }
            });
        },

        initCharts: function () {
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js not loaded');
                return;
            }

            // Set default chart options
            Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            Chart.defaults.plugins.legend.display = false;
            Chart.defaults.elements.point.radius = 4;
            Chart.defaults.elements.point.hoverRadius = 6;

            // Initialize conversation trends chart
            this.initConversationChart();

            // Initialize leads funnel chart
            this.initLeadsFunnelChart();

            // Initialize intents distribution chart
            this.initIntentsChart();

            // Initialize device distribution chart
            this.initDeviceChart();

            // Initialize hourly activity chart
            this.initHourlyChart();
        },

        initConversationChart: function () {
            const ctx = document.getElementById('conversationTrendsChart');
            if (!ctx) return;

            this.charts.conversations = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Conversations',
                        data: [],
                        borderColor: '#E53E3E',
                        backgroundColor: 'rgba(229, 62, 62, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }, {
                        label: 'Leads',
                        data: [],
                        borderColor: '#38A169',
                        backgroundColor: 'rgba(56, 161, 105, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#E53E3E',
                            borderWidth: 1
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        },

        initLeadsFunnelChart: function () {
            const ctx = document.getElementById('leadsFunnelChart');
            if (!ctx) return;

            this.charts.leadsFunnel = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Visitors', 'Engaged', 'Leads', 'Converted'],
                    datasets: [{
                        data: [0, 0, 0, 0],
                        backgroundColor: [
                            '#3182CE',
                            '#D69E2E',
                            '#E53E3E',
                            '#38A169'
                        ],
                        borderWidth: 0,
                        cutout: '60%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const label = context.label || '';
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        },

        initIntentsChart: function () {
            const ctx = document.getElementById('intentsChart');
            if (!ctx) return;

            this.charts.intents = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Intent Count',
                        data: [],
                        backgroundColor: [
                            '#E53E3E', '#38A169', '#D69E2E', '#3182CE', '#805AD5',
                            '#DD6B20', '#319795', '#E53E3E', '#38A169', '#D69E2E'
                        ],
                        borderRadius: 4,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                title: function (context) {
                                    return context[0].label.replace('_', ' ').toUpperCase();
                                },
                                label: function (context) {
                                    return `Count: ${context.parsed.x}`;
                                }
                            }
                        }
                    }
                }
            });
        },

        initDeviceChart: function () {
            const ctx = document.getElementById('deviceChart');
            if (!ctx) return;

            this.charts.device = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Mobile', 'Desktop', 'Tablet'],
                    datasets: [{
                        data: [0, 0, 0],
                        backgroundColor: ['#E53E3E', '#38A169', '#D69E2E'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const label = context.label || '';
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${label}: ${percentage}%`;
                                }
                            }
                        }
                    }
                }
            });
        },

        initHourlyChart: function () {
            const ctx = document.getElementById('hourlyChart');
            if (!ctx) return;

            const hours = Array.from({ length: 24 }, (_, i) => i.toString().padStart(2, '0') + ':00');

            this.charts.hourly = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: hours,
                    datasets: [{
                        label: 'Conversations',
                        data: new Array(24).fill(0),
                        backgroundColor: '#E53E3E',
                        borderRadius: 4,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                title: function (context) {
                                    return `Hour: ${context[0].label}`;
                                },
                                label: function (context) {
                                    return `Conversations: ${context.parsed.y}`;
                                }
                            }
                        }
                    }
                }
            });
        },

        updateCharts: function (data) {
            // Update conversation trends
            if (this.charts.conversations && data.conversations) {
                const dailyData = data.conversations.daily_data || [];
                const dailyLeads = data.leads.daily_leads || [];

                this.charts.conversations.data.labels = dailyData.map(d => this.formatDate(d.date));
                this.charts.conversations.data.datasets[0].data = dailyData.map(d => d.count);
                this.charts.conversations.data.datasets[1].data = dailyLeads.map(d => d.count || 0);
                this.charts.conversations.update();
            }

            // Update leads funnel
            if (this.charts.leadsFunnel && data.leads && data.leads.conversion_funnel) {
                const funnel = data.leads.conversion_funnel;
                this.charts.leadsFunnel.data.datasets[0].data = [
                    funnel.total_sessions || 0,
                    funnel.engaged_sessions || 0,
                    funnel.total_leads || 0,
                    funnel.converted_leads || 0
                ];
                this.charts.leadsFunnel.update();
            }

            // Update intents chart
            if (this.charts.intents && data.user_behavior && data.user_behavior.top_intents) {
                const intents = data.user_behavior.top_intents.slice(0, 10);
                this.charts.intents.data.labels = intents.map(i => this.formatIntent(i.detected_intent));
                this.charts.intents.data.datasets[0].data = intents.map(i => i.count);
                this.charts.intents.update();
            }

            // Update device chart
            if (this.charts.device && data.conversations && data.conversations.device_distribution) {
                const devices = data.conversations.device_distribution;
                const deviceData = this.processDeviceData(devices);
                this.charts.device.data.datasets[0].data = [
                    deviceData.mobile || 0,
                    deviceData.desktop || 0,
                    deviceData.tablet || 0
                ];
                this.charts.device.update();
            }

            // Update hourly chart
            if (this.charts.hourly && data.conversations && data.conversations.peak_hours) {
                const hourlyData = new Array(24).fill(0);
                data.conversations.peak_hours.forEach(h => {
                    hourlyData[parseInt(h.hour)] = h.count;
                });
                this.charts.hourly.data.datasets[0].data = hourlyData;
                this.charts.hourly.update();
            }
        },

        updateAnalyticsDisplay: function (data) {
            // Update overview metrics
            if (data.overview) {
                $('.biic-metric-conversations .biic-metric-number').text(this.formatNumber(data.overview.total_conversations));
                $('.biic-metric-messages .biic-metric-number').text(this.formatNumber(data.overview.total_messages));
                $('.biic-metric-leads .biic-metric-number').text(this.formatNumber(data.overview.total_leads));
                $('.biic-metric-conversion .biic-metric-number').text(data.overview.conversion_rate + '%');

                // Update change indicators
                this.updateChangeIndicator('.biic-metric-conversations', data.overview.conversations_change);
                this.updateChangeIndicator('.biic-metric-leads', data.overview.leads_change);
            }

            // Update performance metrics
            if (data.performance) {
                $('.biic-avg-response-time').text(data.performance.avg_response_time + 's');
                $('.biic-resolution-rate').text(data.performance.resolution_rate + '%');
            }

            // Update engagement metrics
            if (data.conversations) {
                $('.biic-avg-duration').text(data.conversations.avg_duration + ' min');
                $('.biic-avg-messages').text(data.conversations.avg_messages);
            }
        },

        updateChangeIndicator: function (selector, change) {
            const $indicator = $(selector + ' .biic-change-indicator');
            const isPositive = change >= 0;

            $indicator
                .removeClass('positive negative')
                .addClass(isPositive ? 'positive' : 'negative')
                .html(`
                    <span class="dashicons dashicons-arrow-${isPositive ? 'up' : 'down'}-alt"></span>
                    ${Math.abs(change)}%
                `);
        },

        refreshAnalytics: function () {
            BiicAnalytics.loadAnalyticsData();
        },

        selectPeriod: function (e) {
            e.preventDefault();
            const period = $(this).data('period');
            const dates = BiicAnalytics.calculateDateRange(period);

            $('#analytics-date-from').val(dates.from);
            $('#analytics-date-to').val(dates.to);

            $('.biic-period-select').removeClass('active');
            $(this).addClass('active');

            BiicAnalytics.refreshAnalytics();
        },

        calculateDateRange: function (period) {
            const today = new Date();
            const to = this.formatDateForInput(today);
            let from;

            switch (period) {
                case '7d':
                    from = this.formatDateForInput(new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000));
                    break;
                case '30d':
                    from = this.formatDateForInput(new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000));
                    break;
                case '90d':
                    from = this.formatDateForInput(new Date(today.getTime() - 90 * 24 * 60 * 60 * 1000));
                    break;
                default:
                    from = this.formatDateForInput(new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000));
            }

            return { from, to };
        },

        handleExport: function (e) {
            e.preventDefault();
            const format = $(this).data('format');
            const dateFrom = $('#analytics-date-from').val();
            const dateTo = $('#analytics-date-to').val();

            BiicAnalytics.exportData(format, dateFrom, dateTo);
        },

        exportData: function (format, dateFrom, dateTo) {
            const exportUrl = biic_admin.ajax_url;
            const params = new URLSearchParams({
                action: 'biic_export_analytics',
                format: format,
                date_from: dateFrom,
                date_to: dateTo,
                nonce: biic_admin.nonce
            });

            // Create temporary form for file download
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = exportUrl;

            params.forEach((value, key) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        },

        toggleChartType: function (e) {
            e.preventDefault();
            const chartId = $(this).data('chart');
            const newType = $(this).data('type');

            if (BiicAnalytics.charts[chartId]) {
                BiicAnalytics.charts[chartId].config.type = newType;
                BiicAnalytics.charts[chartId].update();
            }
        },

        // Utility functions
        formatNumber: function (num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },

        formatDate: function (dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        },

        formatDateForInput: function (date) {
            return date.toISOString().split('T')[0];
        },

        formatIntent: function (intent) {
            return intent.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        },

        processDeviceData: function (devices) {
            const deviceData = { mobile: 0, desktop: 0, tablet: 0 };

            devices.forEach(device => {
                if (device.device_type in deviceData) {
                    deviceData[device.device_type] = device.count;
                }
            });

            return deviceData;
        },

        getDefaultDateFrom: function () {
            return this.formatDateForInput(new Date(Date.now() - 30 * 24 * 60 * 60 * 1000));
        },

        getDefaultDateTo: function () {
            return this.formatDateForInput(new Date());
        },

        showLoading: function () {
            $('.biic-analytics-content').addClass('biic-loading');
        },

        hideLoading: function () {
            $('.biic-analytics-content').removeClass('biic-loading');
        },

        showError: function (message) {
            // Create or update error notice
            let $notice = $('.biic-analytics-error');
            if (!$notice.length) {
                $notice = $('<div class="notice notice-error biic-analytics-error"><p></p></div>');
                $('.biic-admin-wrap').prepend($notice);
            }
            $notice.find('p').text(message);
            $notice.show();

            setTimeout(() => $notice.fadeOut(), 5000);
        }
    };

    // Initialize when document is ready
    $(document).ready(function () {
        if ($('.biic-analytics-page').length) {
            BiicAnalytics.init();
        }
    });

    // Export to global scope
    window.BiicAnalytics = BiicAnalytics;

})(jQuery);