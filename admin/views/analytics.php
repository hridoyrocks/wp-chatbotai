<?php
/**
 * Admin Analytics View 

 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get analytics data
$analytics_data = isset($analytics_data) ? $analytics_data : array();
$date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : date('Y-m-d', strtotime('-30 days'));
$date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : current_time('Y-m-d');
?>

<div class="wrap biic-admin-wrap">
    
    <!-- Header -->
    <div class="biic-admin-header">
        <div class="biic-header-content">
            <h1 class="biic-page-title">
                <span class="biic-logo">📊</span>
                <?php esc_html_e('Analytics & Reports', 'banglay-ielts-chatbot'); ?>
            </h1>
            <div class="biic-header-actions">
                <button type="button" class="button button-secondary" onclick="biicExportAnalytics()">
                    <span class="dashicons dashicons-download"></span>
                    <?php esc_html_e('Export Report', 'banglay-ielts-chatbot'); ?>
                </button>
                <button type="button" class="button button-primary" onclick="biicRefreshAnalytics()">
                    <span class="dashicons dashicons-update"></span>
                    <?php esc_html_e('Refresh Data', 'banglay-ielts-chatbot'); ?>
                </button>
            </div>
        </div>
        
        <!-- Date Range Filter -->
        <div class="biic-analytics-filters">
            <div class="biic-filter-row">
                <div class="biic-filter-group">
                    <label for="analytics-date-from"><?php esc_html_e('From:', 'banglay-ielts-chatbot'); ?></label>
                    <input type="date" id="analytics-date-from" name="date_from" value="<?php echo esc_attr($date_from); ?>">
                </div>
                
                <div class="biic-filter-group">
                    <label for="analytics-date-to"><?php esc_html_e('To:', 'banglay-ielts-chatbot'); ?></label>
                    <input type="date" id="analytics-date-to" name="date_to" value="<?php echo esc_attr($date_to); ?>">
                </div>
                
                <div class="biic-filter-group">
                    <label for="analytics-granularity"><?php esc_html_e('View By:', 'banglay-ielts-chatbot'); ?></label>
                    <select id="analytics-granularity" name="granularity">
                        <option value="daily"><?php esc_html_e('Daily', 'banglay-ielts-chatbot'); ?></option>
                        <option value="weekly"><?php esc_html_e('Weekly', 'banglay-ielts-chatbot'); ?></option>
                        <option value="monthly"><?php esc_html_e('Monthly', 'banglay-ielts-chatbot'); ?></option>
                    </select>
                </div>
                
                <div class="biic-filter-actions">
                    <button type="button" class="button button-primary" onclick="biicApplyAnalyticsFilters()">
                        <?php esc_html_e('Apply Filters', 'banglay-ielts-chatbot'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Analytics Content -->
    <div class="biic-analytics-content">
        
        <!-- Overview Cards -->
        <div class="biic-analytics-overview">
            <div class="biic-overview-card">
                <div class="biic-card-icon">💬</div>
                <div class="biic-card-content">
                    <div class="biic-card-number"><?php echo number_format($analytics_data['total_conversations'] ?? 0); ?></div>
                    <div class="biic-card-label"><?php esc_html_e('Total Conversations', 'banglay-ielts-chatbot'); ?></div>
                    <div class="biic-card-change positive">+12% থেকে গত মাস</div>
                </div>
            </div>
            
            <div class="biic-overview-card">
                <div class="biic-card-icon">📝</div>
                <div class="biic-card-content">
                    <div class="biic-card-number"><?php echo number_format($analytics_data['total_messages'] ?? 0); ?></div>
                    <div class="biic-card-label"><?php esc_html_e('Total Messages', 'banglay-ielts-chatbot'); ?></div>
                    <div class="biic-card-change positive">+8% থেকে গত মাস</div>
                </div>
            </div>
            
            <div class="biic-overview-card">
                <div class="biic-card-icon">🎯</div>
                <div class="biic-card-content">
                    <div class="biic-card-number"><?php echo number_format($analytics_data['total_leads'] ?? 0); ?></div>
                    <div class="biic-card-label"><?php esc_html_e('Generated Leads', 'banglay-ielts-chatbot'); ?></div>
                    <div class="biic-card-change positive">+25% থেকে গত মাস</div>
                </div>
            </div>
            
            <div class="biic-overview-card">
                <div class="biic-card-icon">📈</div>
                <div class="biic-card-content">
                    <div class="biic-card-number"><?php echo number_format($analytics_data['conversion_rate'] ?? 0, 2); ?>%</div>
                    <div class="biic-card-label"><?php esc_html_e('Conversion Rate', 'banglay-ielts-chatbot'); ?></div>
                    <div class="biic-card-change positive">+3.2% থেকে গত মাস</div>
                </div>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="biic-analytics-charts">
            <div class="biic-chart-row">
                
                <!-- Conversations Over Time -->
                <div class="biic-chart-container">
                    <div class="biic-chart-header">
                        <h3><?php esc_html_e('Conversations Over Time', 'banglay-ielts-chatbot'); ?></h3>
                        <div class="biic-chart-controls">
                            <select id="conversations-chart-type">
                                <option value="line">Line Chart</option>
                                <option value="bar">Bar Chart</option>
                            </select>
                        </div>
                    </div>
                    <div class="biic-chart-canvas">
                        <canvas id="conversationsChart" width="400" height="200"></canvas>
                    </div>
                </div>
                
                <!-- User Engagement -->
                <div class="biic-chart-container">
                    <div class="biic-chart-header">
                        <h3><?php esc_html_e('User Engagement Metrics', 'banglay-ielts-chatbot'); ?></h3>
                    </div>
                    <div class="biic-chart-canvas">
                        <canvas id="engagementChart" width="400" height="200"></canvas>
                    </div>
                </div>
                
            </div>
            
            <div class="biic-chart-row">
                
                <!-- Intent Distribution -->
                <div class="biic-chart-container">
                    <div class="biic-chart-header">
                        <h3><?php esc_html_e('Top User Intents', 'banglay-ielts-chatbot'); ?></h3>
                    </div>
                    <div class="biic-chart-canvas">
                        <canvas id="intentsChart" width="400" height="200"></canvas>
                    </div>
                </div>
                
                <!-- Device & Location -->
                <div class="biic-chart-container">
                    <div class="biic-chart-header">
                        <h3><?php esc_html_e('Device Types & Locations', 'banglay-ielts-chatbot'); ?></h3>
                    </div>
                    <div class="biic-device-location-stats">
                        <div class="biic-device-stats">
                            <h4>Device Types</h4>
                            <div class="biic-device-list">
                                <div class="biic-device-item">
                                    <span class="biic-device-icon">📱</span>
                                    <span class="biic-device-name">Mobile</span>
                                    <span class="biic-device-percent">65%</span>
                                </div>
                                <div class="biic-device-item">
                                    <span class="biic-device-icon">💻</span>
                                    <span class="biic-device-name">Desktop</span>
                                    <span class="biic-device-percent">30%</span>
                                </div>
                                <div class="biic-device-item">
                                    <span class="biic-device-icon">📟</span>
                                    <span class="biic-device-name">Tablet</span>
                                    <span class="biic-device-percent">5%</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="biic-location-stats">
                            <h4>Top Locations</h4>
                            <div class="biic-location-list">
                                <div class="biic-location-item">
                                    <span class="biic-location-flag">🇧🇩</span>
                                    <span class="biic-location-name">Dhaka</span>
                                    <span class="biic-location-percent">45%</span>
                                </div>
                                <div class="biic-location-item">
                                    <span class="biic-location-flag">🇧🇩</span>
                                    <span class="biic-location-name">Chattogram</span>
                                    <span class="biic-location-percent">20%</span>
                                </div>
                                <div class="biic-location-item">
                                    <span class="biic-location-flag">🇧🇩</span>
                                    <span class="biic-location-name">Sylhet</span>
                                    <span class="biic-location-percent">15%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        
        <!-- Performance Metrics -->
        <div class="biic-performance-section">
            <h3><?php esc_html_e('Performance Metrics', 'banglay-ielts-chatbot'); ?></h3>
            
            <div class="biic-metrics-grid">
                
                <div class="biic-metric-card">
                    <div class="biic-metric-header">
                        <h4><?php esc_html_e('Response Time', 'banglay-ielts-chatbot'); ?></h4>
                        <span class="biic-metric-icon">⚡</span>
                    </div>
                    <div class="biic-metric-value">1.2s</div>
                    <div class="biic-metric-trend positive">
                        <span class="biic-trend-arrow">↓</span>
                        <span class="biic-trend-text">15% faster</span>
                    </div>
                </div>
                
                <div class="biic-metric-card">
                    <div class="biic-metric-header">
                        <h4><?php esc_html_e('User Satisfaction', 'banglay-ielts-chatbot'); ?></h4>
                        <span class="biic-metric-icon">😊</span>
                    </div>
                    <div class="biic-metric-value">4.7/5</div>
                    <div class="biic-metric-trend positive">
                        <span class="biic-trend-arrow">↑</span>
                        <span class="biic-trend-text">+0.3 points</span>
                    </div>
                </div>
                
                <div class="biic-metric-card">
                    <div class="biic-metric-header">
                        <h4><?php esc_html_e('Resolution Rate', 'banglay-ielts-chatbot'); ?></h4>
                        <span class="biic-metric-icon">✅</span>
                    </div>
                    <div class="biic-metric-value">82%</div>
                    <div class="biic-metric-trend positive">
                        <span class="biic-trend-arrow">↑</span>
                        <span class="biic-trend-text">+5% higher</span>
                    </div>
                </div>
                
                <div class="biic-metric-card">
                    <div class="biic-metric-header">
                        <h4><?php esc_html_e('Peak Hours', 'banglay-ielts-chatbot'); ?></h4>
                        <span class="biic-metric-icon">🕒</span>
                    </div>
                    <div class="biic-metric-value">2-6 PM</div>
                    <div class="biic-metric-trend neutral">
                        <span class="biic-trend-text">Most active time</span>
                    </div>
                </div>
                
            </div>
        </div>
        
        <!-- Detailed Reports -->
        <div class="biic-reports-section">
            <h3><?php esc_html_e('Detailed Reports', 'banglay-ielts-chatbot'); ?></h3>
            
            <div class="biic-reports-tabs">
                <button class="biic-tab-button active" data-tab="conversation-flow">
                    <?php esc_html_e('Conversation Flow', 'banglay-ielts-chatbot'); ?>
                </button>
                <button class="biic-tab-button" data-tab="user-journey">
                    <?php esc_html_e('User Journey', 'banglay-ielts-chatbot'); ?>
                </button>
                <button class="biic-tab-button" data-tab="lead-funnel">
                    <?php esc_html_e('Lead Funnel', 'banglay-ielts-chatbot'); ?>
                </button>
                <button class="biic-tab-button" data-tab="ai-insights">
                    <?php esc_html_e('AI Insights', 'banglay-ielts-chatbot'); ?>
                </button>
            </div>
            
            <div class="biic-tab-content">
                
                <!-- Conversation Flow -->
                <div id="conversation-flow" class="biic-tab-panel active">
                    <div class="biic-flow-chart">
                        <h4><?php esc_html_e('Most Common Conversation Paths', 'banglay-ielts-chatbot'); ?></h4>
                        <div class="biic-flow-diagram">
                            <div class="biic-flow-step">
                                <div class="biic-step-box start">Greeting</div>
                                <div class="biic-step-count">100%</div>
                            </div>
                            
                            <div class="biic-flow-arrow">→</div>
                            
                            <div class="biic-flow-step">
                                <div class="biic-step-box">Course Inquiry</div>
                                <div class="biic-step-count">65%</div>
                            </div>
                            
                            <div class="biic-flow-arrow">→</div>
                            
                            <div class="biic-flow-step">
                                <div class="biic-step-box">Fee Question</div>
                                <div class="biic-step-count">45%</div>
                            </div>
                            
                            <div class="biic-flow-arrow">→</div>
                            
                            <div class="biic-flow-step">
                                <div class="biic-step-box end">Lead Form</div>
                                <div class="biic-step-count">25%</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- User Journey -->
                <div id="user-journey" class="biic-tab-panel">
                    <div class="biic-journey-map">
                        <h4><?php esc_html_e('Average User Journey', 'banglay-ielts-chatbot'); ?></h4>
                        <div class="biic-journey-timeline">
                            <div class="biic-journey-point">
                                <div class="biic-point-time">0:00</div>
                                <div class="biic-point-action">Page Visit</div>
                                <div class="biic-point-detail">User lands on website</div>
                            </div>
                            
                            <div class="biic-journey-point">
                                <div class="biic-point-time">0:30</div>
                                <div class="biic-point-action">Chat Opens</div>
                                <div class="biic-point-detail">Auto-greeting triggered</div>
                            </div>
                            
                            <div class="biic-journey-point">
                                <div class="biic-point-time">1:45</div>
                                <div class="biic-point-action">First Message</div>
                                <div class="biic-point-detail">User asks about courses</div>
                            </div>
                            
                            <div class="biic-journey-point">
                                <div class="biic-point-time">4:20</div>
                                <div class="biic-point-action">Lead Capture</div>
                                <div class="biic-point-detail">Contact form submitted</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Lead Funnel -->
                <div id="lead-funnel" class="biic-tab-panel">
                    <div class="biic-funnel-chart">
                        <h4><?php esc_html_e('Lead Conversion Funnel', 'banglay-ielts-chatbot'); ?></h4>
                        <div class="biic-funnel-stages">
                            <div class="biic-funnel-stage" style="width: 100%;">
                                <div class="biic-stage-label">Website Visitors</div>
                                <div class="biic-stage-count">10,000</div>
                            </div>
                            
                            <div class="biic-funnel-stage" style="width: 45%;">
                                <div class="biic-stage-label">Chat Engaged</div>
                                <div class="biic-stage-count">4,500</div>
                            </div>
                            
                            <div class="biic-funnel-stage" style="width: 25%;">
                                <div class="biic-stage-label">Information Shared</div>
                                <div class="biic-stage-count">2,500</div>
                            </div>
                            
                            <div class="biic-funnel-stage" style="width: 15%;">
                                <div class="biic-stage-label">Leads Generated</div>
                                <div class="biic-stage-count">1,500</div>
                            </div>
                            
                            <div class="biic-funnel-stage" style="width: 8%;">
                                <div class="biic-stage-label">Converted</div>
                                <div class="biic-stage-count">800</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- AI Insights -->
                <div id="ai-insights" class="biic-tab-panel">
                    <div class="biic-insights-grid">
                        <div class="biic-insight-card">
                            <div class="biic-insight-icon">🎯</div>
                            <div class="biic-insight-content">
                                <h5><?php esc_html_e('Key Insight', 'banglay-ielts-chatbot'); ?></h5>
                                <p>Users who ask about fees are 3x more likely to convert. Consider showing pricing earlier in conversations.</p>
                            </div>
                        </div>
                        
                        <div class="biic-insight-card">
                            <div class="biic-insight-icon">💡</div>
                            <div class="biic-insight-content">
                                <h5><?php esc_html_e('Optimization Tip', 'banglay-ielts-chatbot'); ?></h5>
                                <p>Peak traffic is 2-6 PM. Ensure quick response times during these hours for better conversion.</p>
                            </div>
                        </div>
                        
                        <div class="biic-insight-card">
                            <div class="biic-insight-icon">📱</div>
                            <div class="biic-insight-content">
                                <h5><?php esc_html_e('Mobile Focus', 'banglay-ielts-chatbot'); ?></h5>
                                <p>65% users are on mobile. Mobile lead forms convert 20% better than desktop versions.</p>
                            </div>
                        </div>
                        
                        <div class="biic-insight-card">
                            <div class="biic-insight-icon">🔄</div>
                            <div class="biic-insight-content">
                                <h5><?php esc_html_e('Returning Users', 'banglay-ielts-chatbot'); ?></h5>
                                <p>30% users return within 7 days. Follow-up messaging could improve conversion rates.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        
    </div>
    
</div>

<!-- Chart.js Integration -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeAnalyticsCharts();
    initializeAnalyticsTabs();
});

function initializeAnalyticsCharts() {
    // Conversations Chart
    const conversationsCtx = document.getElementById('conversationsChart').getContext('2d');
    new Chart(conversationsCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($analytics_data['daily_conversations'] ?? []); ?>,
            datasets: [{
                label: 'Conversations',
                data: <?php echo json_encode(array_column($analytics_data['daily_conversations'] ?? [], 'count')); ?>,
                borderColor: '#E53E3E',
                backgroundColor: 'rgba(229, 62, 62, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Engagement Chart
    const engagementCtx = document.getElementById('engagementChart').getContext('2d');
    new Chart(engagementCtx, {
        type: 'doughnut',
        data: {
            labels: ['New Users', 'Returning Users', 'Converted Users'],
            datasets: [{
                data: [60, 30, 10],
                backgroundColor: [
                    '#E53E3E',
                    '#38A169',
                    '#3182CE'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Intents Chart
    const intentsCtx = document.getElementById('intentsChart').getContext('2d');
    new Chart(intentsCtx, {
        type: 'bar',
        data: {
            labels: ['Course Fee', 'Admission', 'Location', 'Study Abroad', 'Contact'],
            datasets: [{
                label: 'Intent Frequency',
                data: [450, 320, 280, 200, 150],
                backgroundColor: '#E53E3E'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function initializeAnalyticsTabs() {
    const tabButtons = document.querySelectorAll('.biic-tab-button');
    const tabPanels = document.querySelectorAll('.biic-tab-panel');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            
            // Remove active class from all buttons and panels
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanels.forEach(panel => panel.classList.remove('active'));
            
            // Add active class to clicked button and corresponding panel
            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });
}

function biicApplyAnalyticsFilters() {
    const dateFrom = document.getElementById('analytics-date-from').value;
    const dateTo = document.getElementById('analytics-date-to').value;
    const granularity = document.getElementById('analytics-granularity').value;
    
    const params = new URLSearchParams();
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    if (granularity) params.append('granularity', granularity);
    
    window.location.href = window.location.pathname + '?page=biic-analytics&' + params.toString();
}

function biicExportAnalytics() {
    const dateFrom = document.getElementById('analytics-date-from').value;
    const dateTo = document.getElementById('analytics-date-to').value;
    
    const params = new URLSearchParams();
    params.append('export', 'analytics');
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    
    window.open(window.location.pathname + '?' + params.toString(), '_blank');
}

function biicRefreshAnalytics() {
    location.reload();
}
</script>

<style>
/* Analytics specific styles */
.biic-analytics-filters {
    padding: 20px 32px;
    background: #f8f9fa;
    border-top: 1px solid #e5e7eb;
}

.biic-filter-row {
    display: flex;
    align-items: center;
    gap: 20px;
}

.biic-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.biic-filter-group label {
    font-size: 12px;
    font-weight: 500;
    color: #6b7280;
}

.biic-filter-group input,
.biic-filter-group select {
    padding: 6px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
}

.biic-analytics-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.biic-overview-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
}

.biic-card-icon {
    font-size: 32px;
    margin-bottom: 12px;
}

.biic-card-number {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
}

.biic-card-label {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 8px;
}

.biic-card-change {
    font-size: 12px;
    padding: 2px 8px;
    border-radius: 4px;
}

.biic-card-change.positive {
    background: rgba(34, 197, 94, 0.1);
    color: #16a34a;
}

.biic-analytics-charts {
    margin-bottom: 40px;
}

.biic-chart-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
    margin-bottom: 30px;
}

.biic-chart-container {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
}

.biic-chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.biic-chart-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
}

.biic-metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.biic-metric-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    border: 1px solid #e5e7eb;
}

.biic-metric-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.biic-metric-value {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 8px;
}

.biic-metric-trend {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
}

.biic-metric-trend.positive {
    color: #16a34a;
}

.biic-reports-tabs {
    display: flex;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 20px;
}

.biic-tab-button {
    background: none;
    border: none;
    padding: 12px 20px;
    font-size: 14px;
    font-weight: 500;
    color: #6b7280;
    cursor: pointer;
    border-bottom: 2px solid transparent;
}

.biic-tab-button.active {
    color: #e53e3e;
    border-bottom-color: #e53e3e;
}

.biic-tab-panel {
    display: none;
}

.biic-tab-panel.active {
    display: block;
}

.biic-flow-diagram {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    background: #f9fafb;
    border-radius: 8px;
}

.biic-flow-step {
    text-align: center;
}

.biic-step-box {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 12px 16px;
    font-weight: 500;
    margin-bottom: 8px;
}

.biic-step-box.start {
    border-color: #16a34a;
    background: #f0fdf4;
}

.biic-step-box.end {
    border-color: #e53e3e;
    background: #fef2f2;
}

.biic-step-count {
    font-size: 12px;
    color: #6b7280;
}

.biic-flow-arrow {
    font-size: 20px;
    color: #6b7280;
}

@media (max-width: 768px) {
    .biic-filter-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .biic-chart-row {
        grid-template-columns: 1fr;
    }
    
    .biic-flow-diagram {
        flex-direction: column;
    }
    
    .biic-flow-arrow {
        transform: rotate(90deg);
    }
}
</style>