<?php
/**
 * Analytics System for Banglay IELTS Chatbot
 * Advanced analytics, reporting, and insights
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BIIC_Analytics {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // AJAX handlers
        add_action('wp_ajax_biic_get_analytics_data', array($this, 'ajax_get_analytics_data'));
        add_action('wp_ajax_biic_export_analytics', array($this, 'ajax_export_analytics'));
        
        // Scheduled analytics tasks
        add_action('biic_daily_analytics', array($this, 'process_daily_analytics'));
        add_action('biic_weekly_analytics', array($this, 'process_weekly_analytics'));
        add_action('biic_monthly_analytics', array($this, 'process_monthly_analytics'));
        
        // Schedule events if not already scheduled
        if (!wp_next_scheduled('biic_daily_analytics')) {
            wp_schedule_event(time(), 'daily', 'biic_daily_analytics');
        }
        
        if (!wp_next_scheduled('biic_weekly_analytics')) {
            wp_schedule_event(time(), 'weekly', 'biic_weekly_analytics');
        }
        
        if (!wp_next_scheduled('biic_monthly_analytics')) {
            wp_schedule_event(time(), 'monthly', 'biic_monthly_analytics');
        }
    }
    
    /**
     * Get dashboard analytics
     */
    public function get_dashboard_analytics($date_from = null, $date_to = null) {
        if (!$date_from) {
            $date_from = date('Y-m-d', strtotime('-30 days'));
        }
        if (!$date_to) {
            $date_to = current_time('Y-m-d');
        }
        
        return array(
            'overview' => $this->get_overview_metrics($date_from, $date_to),
            'conversations' => $this->get_conversation_analytics($date_from, $date_to),
            'leads' => $this->get_lead_analytics($date_from, $date_to),
            'user_behavior' => $this->get_user_behavior_analytics($date_from, $date_to),
            'performance' => $this->get_performance_metrics($date_from, $date_to),
            'trends' => $this->get_trend_analysis($date_from, $date_to)
        );
    }
    
    /**
     * Get overview metrics
     */
    private function get_overview_metrics($date_from, $date_to) {
        global $wpdb;
        $database = BIIC()->database;
        
        $current_period = array(
            'total_conversations' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$database->get_table('chat_sessions')} 
                 WHERE DATE(started_at) BETWEEN %s AND %s",
                $date_from, $date_to
            )),
            'total_messages' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$database->get_table('chat_messages')} 
                 WHERE DATE(timestamp) BETWEEN %s AND %s",
                $date_from, $date_to
            )),
            'total_leads' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$database->get_table('leads')} 
                 WHERE DATE(created_at) BETWEEN %s AND %s",
                $date_from, $date_to
            )),
            'active_sessions' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$database->get_table('chat_sessions')} 
                 WHERE last_activity >= %s AND is_active = 1",
                date('Y-m-d H:i:s', strtotime('-5 minutes'))
            ))
        );
        
        // Calculate conversion rate
        $current_period['conversion_rate'] = $current_period['total_conversations'] > 0 
            ? round(($current_period['total_leads'] / $current_period['total_conversations']) * 100, 2)
            : 0;
        
        // Get previous period for comparison
        $days_diff = (strtotime($date_to) - strtotime($date_from)) / (24 * 60 * 60);
        $prev_date_to = date('Y-m-d', strtotime($date_from . ' -1 day'));
        $prev_date_from = date('Y-m-d', strtotime($prev_date_to . ' -' . $days_diff . ' days'));
        
        $previous_period = array(
            'total_conversations' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$database->get_table('chat_sessions')} 
                 WHERE DATE(started_at) BETWEEN %s AND %s",
                $prev_date_from, $prev_date_to
            )),
            'total_leads' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$database->get_table('leads')} 
                 WHERE DATE(created_at) BETWEEN %s AND %s",
                $prev_date_from, $prev_date_to
            ))
        );
        
        // Calculate percentage changes
        $current_period['conversations_change'] = $this->calculate_percentage_change(
            $previous_period['total_conversations'], 
            $current_period['total_conversations']
        );
        
        $current_period['leads_change'] = $this->calculate_percentage_change(
            $previous_period['total_leads'], 
            $current_period['total_leads']
        );
        
        return $current_period;
    }
    
    /**
     * Get conversation analytics
     */
    private function get_conversation_analytics($date_from, $date_to) {
        global $wpdb;
        $database = BIIC()->database;
        
        // Daily conversation data
        $daily_data = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(started_at) as date, COUNT(*) as count 
             FROM {$database->get_table('chat_sessions')} 
             WHERE DATE(started_at) BETWEEN %s AND %s 
             GROUP BY DATE(started_at) 
             ORDER BY date ASC",
            $date_from, $date_to
        ));
        
        // Average session metrics
        $avg_metrics = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                AVG(total_messages) as avg_messages,
                AVG(TIMESTAMPDIFF(MINUTE, started_at, last_activity)) as avg_duration,
                AVG(lead_score) as avg_lead_score
             FROM {$database->get_table('chat_sessions')} 
             WHERE DATE(started_at) BETWEEN %s AND %s 
             AND last_activity IS NOT NULL",
            $date_from, $date_to
        ));
        
        // Peak hours analysis
        $peak_hours = $wpdb->get_results($wpdb->prepare(
            "SELECT HOUR(started_at) as hour, COUNT(*) as count 
             FROM {$database->get_table('chat_sessions')} 
             WHERE DATE(started_at) BETWEEN %s AND %s 
             GROUP BY HOUR(started_at) 
             ORDER BY hour ASC",
            $date_from, $date_to
        ));
        
        // Device distribution
        $device_distribution = $wpdb->get_results($wpdb->prepare(
            "SELECT device_type, COUNT(*) as count 
             FROM {$database->get_table('chat_sessions')} 
             WHERE DATE(started_at) BETWEEN %s AND %s 
             GROUP BY device_type 
             ORDER BY count DESC",
            $date_from, $date_to
        ));
        
        // Top locations
        $top_locations = $wpdb->get_results($wpdb->prepare(
            "SELECT location, COUNT(*) as count 
             FROM {$database->get_table('chat_sessions')} 
             WHERE DATE(started_at) BETWEEN %s AND %s 
             AND location IS NOT NULL 
             GROUP BY location 
             ORDER BY count DESC 
             LIMIT 10",
            $date_from, $date_to
        ));
        
        return array(
            'daily_data' => $daily_data,
            'avg_messages' => round($avg_metrics->avg_messages ?? 0, 1),
            'avg_duration' => round($avg_metrics->avg_duration ?? 0, 1),
            'avg_lead_score' => round($avg_metrics->avg_lead_score ?? 0, 1),
            'peak_hours' => $peak_hours,
            'device_distribution' => $device_distribution,
            'top_locations' => $top_locations
        );
    }
    
    /**
     * Get lead analytics
     */
    private function get_lead_analytics($date_from, $date_to) {
        global $wpdb;
        $database = BIIC()->database;
        
        // Lead generation data
        $daily_leads = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(created_at) as date, COUNT(*) as count 
             FROM {$database->get_table('leads')} 
             WHERE DATE(created_at) BETWEEN %s AND %s 
             GROUP BY DATE(created_at) 
             ORDER BY date ASC",
            $date_from, $date_to
        ));
        
        // Lead status distribution
        $status_distribution = $wpdb->get_results($wpdb->prepare(
            "SELECT lead_status, COUNT(*) as count 
             FROM {$database->get_table('leads')} 
             WHERE DATE(created_at) BETWEEN %s AND %s 
             GROUP BY lead_status 
             ORDER BY count DESC",
            $date_from, $date_to
        ));
        
        // Lead score distribution
        $score_distribution = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                CASE 
                    WHEN lead_score >= 80 THEN 'Hot (80-100)'
                    WHEN lead_score >= 60 THEN 'Warm (60-79)'
                    WHEN lead_score >= 40 THEN 'Medium (40-59)'
                    ELSE 'Cold (0-39)'
                END as score_range,
                COUNT(*) as count
             FROM {$database->get_table('leads')} 
             WHERE DATE(created_at) BETWEEN %s AND %s 
             GROUP BY score_range 
             ORDER BY MIN(lead_score) DESC",
            $date_from, $date_to
        ));
        
        // Course interest distribution
        $course_interests = $wpdb->get_results($wpdb->prepare(
            "SELECT course_interest, COUNT(*) as count 
             FROM {$database->get_table('leads')} 
             WHERE DATE(created_at) BETWEEN %s AND %s 
             AND course_interest IS NOT NULL 
             GROUP BY course_interest 
             ORDER BY count DESC",
            $date_from, $date_to
        ));
        
        // Conversion funnel
        $total_sessions = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$database->get_table('chat_sessions')} 
             WHERE DATE(started_at) BETWEEN %s AND %s",
            $date_from, $date_to
        ));
        
        $engaged_sessions = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$database->get_table('chat_sessions')} 
             WHERE DATE(started_at) BETWEEN %s AND %s 
             AND total_messages >= 3",
            $date_from, $date_to
        ));
        
        $high_intent_sessions = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$database->get_table('chat_sessions')} 
             WHERE DATE(started_at) BETWEEN %s AND %s 
             AND lead_score >= 40",
            $date_from, $date_to
        ));
        
        $total_leads = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$database->get_table('leads')} 
             WHERE DATE(created_at) BETWEEN %s AND %s",
            $date_from, $date_to
        ));
        
        $converted_leads = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$database->get_table('leads')} 
             WHERE DATE(created_at) BETWEEN %s AND %s 
             AND lead_status = 'converted'",
            $date_from, $date_to
        ));
        
        return array(
            'daily_leads' => $daily_leads,
            'status_distribution' => $status_distribution,
            'score_distribution' => $score_distribution,
            'course_interests' => $course_interests,
            'conversion_funnel' => array(
                'total_sessions' => $total_sessions,
                'engaged_sessions' => $engaged_sessions,
                'high_intent_sessions' => $high_intent_sessions,
                'total_leads' => $total_leads,
                'converted_leads' => $converted_leads
            )
        );
    }
    
    /**
     * Get user behavior analytics
     */
    private function get_user_behavior_analytics($date_from, $date_to) {
        global $wpdb;
        $database = BIIC()->database;
        
        // Most common user intents
        $top_intents = $wpdb->get_results($wpdb->prepare(
            "SELECT detected_intent, COUNT(*) as count 
             FROM {$database->get_table('chat_messages')} 
             WHERE DATE(timestamp) BETWEEN %s AND %s 
             AND detected_intent IS NOT NULL 
             GROUP BY detected_intent 
             ORDER BY count DESC 
             LIMIT 10",
            $date_from, $date_to
        ));
        
        // User interaction types
        $interaction_types = $wpdb->get_results($wpdb->prepare(
            "SELECT interaction_type, COUNT(*) as count 
             FROM {$database->get_table('user_interactions')} 
             WHERE DATE(timestamp) BETWEEN %s AND %s 
             GROUP BY interaction_type 
             ORDER BY count DESC",
            $date_from, $date_to
        ));
        
        // Session duration distribution
        $duration_distribution = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                CASE 
                    WHEN TIMESTAMPDIFF(MINUTE, started_at, last_activity) < 1 THEN '< 1 min'
                    WHEN TIMESTAMPDIFF(MINUTE, started_at, last_activity) < 5 THEN '1-5 min'
                    WHEN TIMESTAMPDIFF(MINUTE, started_at, last_activity) < 10 THEN '5-10 min'
                    WHEN TIMESTAMPDIFF(MINUTE, started_at, last_activity) < 30 THEN '10-30 min'
                    ELSE '30+ min'
                END as duration_range,
                COUNT(*) as count
             FROM {$database->get_table('chat_sessions')} 
             WHERE DATE(started_at) BETWEEN %s AND %s 
             AND last_activity IS NOT NULL 
             GROUP BY duration_range",
            $date_from, $date_to
        ));
        
        // Message volume distribution
        $message_distribution = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                CASE 
                    WHEN total_messages = 1 THEN '1 message'
                    WHEN total_messages BETWEEN 2 AND 5 THEN '2-5 messages'
                    WHEN total_messages BETWEEN 6 AND 10 THEN '6-10 messages'
                    WHEN total_messages BETWEEN 11 AND 20 THEN '11-20 messages'
                    ELSE '20+ messages'
                END as message_range,
                COUNT(*) as count
             FROM {$database->get_table('chat_sessions')} 
             WHERE DATE(started_at) BETWEEN %s AND %s 
             GROUP BY message_range",
            $date_from, $date_to
        ));
        
        return array(
            'top_intents' => $top_intents,
            'interaction_types' => $interaction_types,
            'duration_distribution' => $duration_distribution,
            'message_distribution' => $message_distribution
        );
    }
    
    /**
     * Get performance metrics
     */
    private function get_performance_metrics($date_from, $date_to) {
        global $wpdb;
        $database = BIIC()->database;
        
        // Response time analysis
        $avg_response_time = $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(response_time) 
             FROM {$database->get_table('chat_messages')} 
             WHERE DATE(timestamp) BETWEEN %s AND %s 
             AND message_type = 'bot' 
             AND response_time IS NOT NULL 
             AND response_time > 0",
            $date_from, $date_to
        ));
        
        // User satisfaction metrics (if available)
        $satisfaction_data = $wpdb->get_results($wpdb->prepare(
            "SELECT feedback_score, COUNT(*) as count 
             FROM {$database->get_table('chat_messages')} 
             WHERE DATE(timestamp) BETWEEN %s AND %s 
             AND feedback_score IS NOT NULL 
             GROUP BY feedback_score 
             ORDER BY feedback_score DESC",
            $date_from, $date_to
        ));
        
        // Resolution rate (conversations with high lead scores)
        $total_conversations = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$database->get_table('chat_sessions')} 
             WHERE DATE(started_at) BETWEEN %s AND %s",
            $date_from, $date_to
        ));
        
        $resolved_conversations = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$database->get_table('chat_sessions')} 
             WHERE DATE(started_at) BETWEEN %s AND %s 
             AND lead_score >= 60",
            $date_from, $date_to
        ));
        
        $resolution_rate = $total_conversations > 0 
            ? round(($resolved_conversations / $total_conversations) * 100, 1) 
            : 0;
        
        return array(
            'avg_response_time' => round($avg_response_time ?? 0, 2),
            'satisfaction_data' => $satisfaction_data,
            'resolution_rate' => $resolution_rate,
            'total_conversations' => $total_conversations,
            'resolved_conversations' => $resolved_conversations
        );
    }
    
    /**
     * Get trend analysis
     */
    private function get_trend_analysis($date_from, $date_to) {
        global $wpdb;
        $database = BIIC()->database;
        
        // Growth trends
        $daily_growth = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                DATE(started_at) as date,
                COUNT(*) as conversations,
                SUM(CASE WHEN lead_score >= 60 THEN 1 ELSE 0 END) as quality_conversations,
                AVG(lead_score) as avg_lead_score
             FROM {$database->get_table('chat_sessions')} 
             WHERE DATE(started_at) BETWEEN %s AND %s 
             GROUP BY DATE(started_at) 
             ORDER BY date ASC",
            $date_from, $date_to
        ));
        
        // Calculate day-over-day growth rates
        $growth_rates = array();
        for ($i = 1; $i < count($daily_growth); $i++) {
            $prev = $daily_growth[$i-1]->conversations;
            $curr = $daily_growth[$i]->conversations;
            $growth_rates[] = array(
                'date' => $daily_growth[$i]->date,
                'growth_rate' => $prev > 0 ? round((($curr - $prev) / $prev) * 100, 1) : 0
            );
        }
        
        return array(
            'daily_growth' => $daily_growth,
            'growth_rates' => $growth_rates
        );
    }
    
    /**
     * Process daily analytics
     */
    public function process_daily_analytics() {
        $date = current_time('Y-m-d');
        $database = BIIC()->database;
        
        // Store daily metrics
        $metrics = $this->get_overview_metrics($date, $date);
        
        $database->store_analytics_metric($date, 'daily_conversations', $metrics['total_conversations'], 'counter');
        $database->store_analytics_metric($date, 'daily_messages', $metrics['total_messages'], 'counter');
        $database->store_analytics_metric($date, 'daily_leads', $metrics['total_leads'], 'counter');
        $database->store_analytics_metric($date, 'daily_conversion_rate', $metrics['conversion_rate'], 'percentage');
        
        // Store performance metrics
        $performance = $this->get_performance_metrics($date, $date);
        $database->store_analytics_metric($date, 'avg_response_time', $performance['avg_response_time'], 'time');
        $database->store_analytics_metric($date, 'resolution_rate', $performance['resolution_rate'], 'percentage');
        
        // Clean up old analytics data (keep last 2 years)
        $cleanup_date = date('Y-m-d', strtotime('-2 years'));
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$database->get_table('analytics')} WHERE date < %s",
            $cleanup_date
        ));
    }
    
    /**
     * Process weekly analytics
     */
    public function process_weekly_analytics() {
        $end_date = current_time('Y-m-d');
        $start_date = date('Y-m-d', strtotime('-7 days'));
        
        $analytics = $this->get_dashboard_analytics($start_date, $end_date);
        
        // Store weekly summary
        $database = BIIC()->database;
        $database->store_analytics_metric($end_date, 'weekly_conversations', $analytics['overview']['total_conversations'], 'counter');
        $database->store_analytics_metric($end_date, 'weekly_leads', $analytics['overview']['total_leads'], 'counter');
        $database->store_analytics_metric($end_date, 'weekly_conversion_rate', $analytics['overview']['conversion_rate'], 'percentage');
    }
    
    /**
     * Process monthly analytics
     */
    public function process_monthly_analytics() {
        $end_date = current_time('Y-m-d');
        $start_date = date('Y-m-01'); // First day of current month
        
        $analytics = $this->get_dashboard_analytics($start_date, $end_date);
        
        // Store monthly summary
        $database = BIIC()->database;
        $database->store_analytics_metric($end_date, 'monthly_conversations', $analytics['overview']['total_conversations'], 'counter');
        $database->store_analytics_metric($end_date, 'monthly_leads', $analytics['overview']['total_leads'], 'counter');
        $database->store_analytics_metric($end_date, 'monthly_conversion_rate', $analytics['overview']['conversion_rate'], 'percentage');
        
        // Generate monthly insights
        $this->generate_monthly_insights($analytics);
    }
    
    /**
     * Generate AI-powered insights
     */
    public function generate_monthly_insights($analytics) {
        $insights = array();
        
        // Conversation insights
        if ($analytics['overview']['conversations_change'] > 20) {
            $insights[] = array(
                'type' => 'growth',
                'title' => 'Exceptional Growth',
                'message' => "Conversations increased by {$analytics['overview']['conversations_change']}% this month! Consider scaling your support team.",
                'priority' => 'high'
            );
        } elseif ($analytics['overview']['conversations_change'] < -10) {
            $insights[] = array(
                'type' => 'decline',
                'title' => 'Traffic Decline',
                'message' => "Conversations decreased by " . abs($analytics['overview']['conversations_change']) . "%. Review marketing efforts and user experience.",
                'priority' => 'high'
            );
        }
        
        // Lead conversion insights
        if ($analytics['overview']['conversion_rate'] > 15) {
            $insights[] = array(
                'type' => 'success',
                'title' => 'High Conversion Rate',
                'message' => "Excellent conversion rate of {$analytics['overview']['conversion_rate']}%! Your chatbot is performing very well.",
                'priority' => 'medium'
            );
        } elseif ($analytics['overview']['conversion_rate'] < 5) {
            $insights[] = array(
                'type' => 'improvement',
                'title' => 'Low Conversion Rate',
                'message' => "Conversion rate is only {$analytics['overview']['conversion_rate']}%. Consider improving lead capture flows.",
                'priority' => 'high'
            );
        }
        
        // Response time insights
        if ($analytics['performance']['avg_response_time'] > 3) {
            $insights[] = array(
                'type' => 'performance',
                'title' => 'Slow Response Time',
                'message' => "Average response time is {$analytics['performance']['avg_response_time']}s. Consider optimizing AI responses.",
                'priority' => 'medium'
            );
        }
        
        // Device usage insights
        $mobile_usage = 0;
        foreach ($analytics['conversations']['device_distribution'] as $device) {
            if ($device->device_type === 'mobile') {
                $total_devices = array_sum(array_column($analytics['conversations']['device_distribution'], 'count'));
                $mobile_usage = round(($device->count / $total_devices) * 100, 1);
                break;
            }
        }
        
        if ($mobile_usage > 70) {
            $insights[] = array(
                'type' => 'mobile',
                'title' => 'Mobile-First Audience',
                'message' => "{$mobile_usage}% of users are on mobile. Ensure your chatbot is mobile-optimized.",
                'priority' => 'medium'
            );
        }
        
        // Peak hours insights
        $peak_hour = 0;
        $max_count = 0;
        foreach ($analytics['conversations']['peak_hours'] as $hour_data) {
            if ($hour_data->count > $max_count) {
                $max_count = $hour_data->count;
                $peak_hour = $hour_data->hour;
            }
        }
        
        $insights[] = array(
            'type' => 'timing',
            'title' => 'Peak Traffic Hour',
            'message' => "Peak traffic occurs at {$peak_hour}:00. Ensure optimal performance during this time.",
            'priority' => 'low'
        );
        
        // Store insights
        update_option('biic_monthly_insights', $insights);
        
        return $insights;
    }
    
    /**
     * AJAX: Get analytics data
     */
    public function ajax_get_analytics_data() {
        check_ajax_referer('biic_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'banglay-ielts-chatbot'));
        }
        
        $date_from = sanitize_text_field($_POST['date_from'] ?? date('Y-m-d', strtotime('-30 days')));
        $date_to = sanitize_text_field($_POST['date_to'] ?? current_time('Y-m-d'));
        $type = sanitize_text_field($_POST['type'] ?? 'overview');
        
        switch ($type) {
            case 'overview':
                $data = $this->get_overview_metrics($date_from, $date_to);
                break;
            case 'conversations':
                $data = $this->get_conversation_analytics($date_from, $date_to);
                break;
            case 'leads':
                $data = $this->get_lead_analytics($date_from, $date_to);
                break;
            case 'behavior':
                $data = $this->get_user_behavior_analytics($date_from, $date_to);
                break;
            case 'performance':
                $data = $this->get_performance_metrics($date_from, $date_to);
                break;
            default:
                $data = $this->get_dashboard_analytics($date_from, $date_to);
        }
        
        wp_send_json_success($data);
    }
    
    /**
     * AJAX: Export analytics
     */
    public function ajax_export_analytics() {
        check_ajax_referer('biic_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'banglay-ielts-chatbot'));
        }
        
        $date_from = sanitize_text_field($_POST['date_from'] ?? date('Y-m-d', strtotime('-30 days')));
        $date_to = sanitize_text_field($_POST['date_to'] ?? current_time('Y-m-d'));
        $format = sanitize_text_field($_POST['format'] ?? 'csv');
        
        $analytics = $this->get_dashboard_analytics($date_from, $date_to);
        
        $this->export_analytics_data($analytics, $format, $date_from, $date_to);
    }
    
    /**
     * Export analytics data
     */
    private function export_analytics_data($analytics, $format, $date_from, $date_to) {
        $filename = 'biic_analytics_' . $date_from . '_to_' . $date_to;
        
        switch ($format) {
            case 'json':
                $this->export_to_json($analytics, $filename);
                break;
            case 'pdf':
                $this->export_to_pdf($analytics, $filename);
                break;
            default:
                $this->export_to_csv($analytics, $filename);
        }
    }
    
    /**
     * Export to CSV
     */
    private function export_to_csv($analytics, $filename) {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fputs($output, "\xEF\xBB\xBF");
        
        // Overview section
        fputcsv($output, array('OVERVIEW METRICS'));
        fputcsv($output, array('Metric', 'Value', 'Change'));
        fputcsv($output, array('Total Conversations', $analytics['overview']['total_conversations'], $analytics['overview']['conversations_change'] . '%'));
        fputcsv($output, array('Total Messages', $analytics['overview']['total_messages']));
        fputcsv($output, array('Total Leads', $analytics['overview']['total_leads'], $analytics['overview']['leads_change'] . '%'));
        fputcsv($output, array('Conversion Rate', $analytics['overview']['conversion_rate'] . '%'));
        fputcsv($output, array('Active Sessions', $analytics['overview']['active_sessions']));
        fputcsv($output, array(''));
        
        // Daily conversations
        fputcsv($output, array('DAILY CONVERSATIONS'));
        fputcsv($output, array('Date', 'Conversations'));
        foreach ($analytics['conversations']['daily_data'] as $day) {
            fputcsv($output, array($day->date, $day->count));
        }
        fputcsv($output, array(''));
        
        // Lead status distribution
        fputcsv($output, array('LEAD STATUS DISTRIBUTION'));
        fputcsv($output, array('Status', 'Count'));
        foreach ($analytics['leads']['status_distribution'] as $status) {
            fputcsv($output, array(ucfirst($status->lead_status), $status->count));
        }
        fputcsv($output, array(''));
        
        // Top intents
        fputcsv($output, array('TOP USER INTENTS'));
        fputcsv($output, array('Intent', 'Count'));
        foreach ($analytics['user_behavior']['top_intents'] as $intent) {
            fputcsv($output, array($intent->detected_intent, $intent->count));
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Export to JSON
     */
    private function export_to_json($analytics, $filename) {
        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.json"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo json_encode($analytics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Export to PDF (simplified)
     */
    private function export_to_pdf($analytics, $filename) {
        // For now, fallback to CSV
        // In production, implement with TCPDF or similar library
        $this->export_to_csv($analytics, $filename);
    }
    
    /**
     * Get real-time analytics
     */
    public function get_realtime_analytics() {
        global $wpdb;
        $database = BIIC()->database;
        
        $last_hour = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $last_5_minutes = date('Y-m-d H:i:s', strtotime('-5 minutes'));
        
        return array(
            'active_sessions' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$database->get_table('chat_sessions')} 
                 WHERE last_activity >= %s AND is_active = 1",
                $last_5_minutes
            )),
            'recent_conversations' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$database->get_table('chat_sessions')} 
                 WHERE started_at >= %s",
                $last_hour
            )),
            'recent_messages' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$database->get_table('chat_messages')} 
                 WHERE timestamp >= %s",
                $last_hour
            )),
            'recent_leads' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$database->get_table('leads')} 
                 WHERE created_at >= %s",
                $last_hour
            ))
        );
    }
    
    /**
     * Get insights and recommendations
     */
    public function get_insights() {
        $insights = get_option('biic_monthly_insights', array());
        
        // Add real-time insights
        $realtime = $this->get_realtime_analytics();
        
        if ($realtime['active_sessions'] > 10) {
            array_unshift($insights, array(
                'type' => 'realtime',
                'title' => 'High Activity',
                'message' => "Currently {$realtime['active_sessions']} active sessions. Peak traffic detected!",
                'priority' => 'medium'
            ));
        }
        
        // Sort by priority
        usort($insights, function($a, $b) {
            $priority_order = array('high' => 3, 'medium' => 2, 'low' => 1);
            $a_priority = $priority_order[$a['priority']] ?? 0;
            $b_priority = $priority_order[$b['priority']] ?? 0;
            return $b_priority - $a_priority;
        });
        
        return array_slice($insights, 0, 5); // Return top 5 insights
    }
    
    /**
     * Helper function to calculate percentage change
     */
    private function calculate_percentage_change($old_value, $new_value) {
        if ($old_value == 0) {
            return $new_value > 0 ? 100 : 0;
        }
        
        return round((($new_value - $old_value) / $old_value) * 100, 1);
    }
    
    /**
     * Get historical analytics for comparison
     */
    public function get_historical_analytics($metric, $days = 30) {
        $database = BIIC()->database;
        $end_date = current_time('Y-m-d');
        $start_date = date('Y-m-d', strtotime("-{$days} days"));
        
        return $database->get_analytics_data($metric, $start_date, $end_date);
    }
    
    /**
     * Generate analytics report
     */
    public function generate_analytics_report($date_from, $date_to, $format = 'html') {
        $analytics = $this->get_dashboard_analytics($date_from, $date_to);
        $insights = $this->get_insights();
        
        switch ($format) {
            case 'pdf':
                return $this->generate_pdf_report($analytics, $insights, $date_from, $date_to);
            case 'email':
                return $this->generate_email_report($analytics, $insights, $date_from, $date_to);
            default:
                return $this->generate_html_report($analytics, $insights, $date_from, $date_to);
        }
    }
    
    /**
     * Generate HTML report
     */
    private function generate_html_report($analytics, $insights, $date_from, $date_to) {
        ob_start();
        ?>
        <div class="biic-analytics-report">
            <div class="report-header">
                <h1>Banglay IELTS Chatbot Analytics Report</h1>
                <p>Period: <?php echo $date_from; ?> to <?php echo $date_to; ?></p>
                <p>Generated: <?php echo current_time('Y-m-d H:i:s'); ?></p>
            </div>
            
            <div class="report-section">
                <h2>Executive Summary</h2>
                <div class="summary-metrics">
                    <div class="metric">
                        <span class="metric-value"><?php echo number_format($analytics['overview']['total_conversations']); ?></span>
                        <span class="metric-label">Total Conversations</span>
                        <span class="metric-change <?php echo $analytics['overview']['conversations_change'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $analytics['overview']['conversations_change']; ?>%
                        </span>
                    </div>
                    
                    <div class="metric">
                        <span class="metric-value"><?php echo number_format($analytics['overview']['total_leads']); ?></span>
                        <span class="metric-label">Generated Leads</span>
                        <span class="metric-change <?php echo $analytics['overview']['leads_change'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $analytics['overview']['leads_change']; ?>%
                        </span>
                    </div>
                    
                    <div class="metric">
                        <span class="metric-value"><?php echo $analytics['overview']['conversion_rate']; ?>%</span>
                        <span class="metric-label">Conversion Rate</span>
                    </div>
                </div>
            </div>
            
            <div class="report-section">
                <h2>Key Insights</h2>
                <div class="insights-list">
                    <?php foreach ($insights as $insight): ?>
                        <div class="insight-item priority-<?php echo $insight['priority']; ?>">
                            <h4><?php echo esc_html($insight['title']); ?></h4>
                            <p><?php echo esc_html($insight['message']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="report-section">
                <h2>Performance Metrics</h2>
                <table class="performance-table">
                    <tr>
                        <td>Average Response Time</td>
                        <td><?php echo $analytics['performance']['avg_response_time']; ?>s</td>
                    </tr>
                    <tr>
                        <td>Resolution Rate</td>
                        <td><?php echo $analytics['performance']['resolution_rate']; ?>%</td>
                    </tr>
                    <tr>
                        <td>Average Messages per Session</td>
                        <td><?php echo $analytics['conversations']['avg_messages']; ?></td>
                    </tr>
                    <tr>
                        <td>Average Session Duration</td>
                        <td><?php echo $analytics['conversations']['avg_duration']; ?> minutes</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <style>
        .biic-analytics-report {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .report-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
        }
        .summary-metrics {
            display: flex;
            gap: 20px;
            margin: 20px 0;
        }
        .metric {
            flex: 1;
            text-align: center;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        .metric-value {
            display: block;
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
        }
        .metric-label {
            display: block;
            font-size: 12px;
            color: #6b7280;
            margin: 5px 0;
        }
        .metric-change {
            font-size: 12px;
            font-weight: bold;
        }
        .metric-change.positive { color: #10b981; }
        .metric-change.negative { color: #ef4444; }
        .insights-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .insight-item {
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #6b7280;
        }
        .insight-item.priority-high {
            border-left-color: #ef4444;
            background: #fef2f2;
        }
        .insight-item.priority-medium {
            border-left-color: #f59e0b;
            background: #fffbeb;
        }
        .insight-item.priority-low {
            border-left-color: #10b981;
            background: #f0fdf4;
        }
        .performance-table {
            width: 100%;
            border-collapse: collapse;
        }
        .performance-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .performance-table td:first-child {
            font-weight: bold;
            color: #374151;
        }
        .performance-table td:last-child {
            text-align: right;
            color: #1f2937;
        }
        .report-section {
            margin-bottom: 40px;
        }
        .report-section h2 {
            color: #1f2937;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
        }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Schedule analytics reports
     */
    public function schedule_analytics_reports() {
        // Weekly report
        if (!wp_next_scheduled('biic_weekly_analytics_report')) {
            wp_schedule_event(time(), 'weekly', 'biic_weekly_analytics_report');
        }
        
        // Monthly report
        if (!wp_next_scheduled('biic_monthly_analytics_report')) {
            wp_schedule_event(time(), 'monthly', 'biic_monthly_analytics_report');
        }
    }
    
    /**
     * Send weekly analytics email
     */
    public function send_weekly_analytics_email() {
        $end_date = current_time('Y-m-d');
        $start_date = date('Y-m-d', strtotime('-7 days'));
        
        $report = $this->generate_analytics_report($start_date, $end_date, 'email');
        
        $email = get_option('biic_notification_email', get_option('admin_email'));
        $subject = 'Weekly Chatbot Analytics Report - ' . get_bloginfo('name');
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($email, $subject, $report, $headers);
    }
}