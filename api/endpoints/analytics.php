<?php
/**
 * Analytics API Endpoints
 * api/endpoints/analytics.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BIIC_Analytics_Endpoints {
    
    /**
     * Register analytics-related REST routes
     */
    public function register_routes() {
        // Dashboard analytics endpoint
        register_rest_route('biic/v1', '/analytics/dashboard', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_dashboard_analytics'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'date_from' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d', strtotime('-30 days'))
                ),
                'date_to' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d')
                )
            )
        ));

        // Conversation analytics endpoint
        register_rest_route('biic/v1', '/analytics/conversations', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_conversation_analytics'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'date_from' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d', strtotime('-30 days'))
                ),
                'date_to' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d')
                )
            )
        ));

        // Lead analytics endpoint
        register_rest_route('biic/v1', '/analytics/leads', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_lead_analytics'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'date_from' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d', strtotime('-30 days'))
                ),
                'date_to' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d')
                )
            )
        ));

        // Real-time analytics endpoint
        register_rest_route('biic/v1', '/analytics/realtime', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_realtime_analytics'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));

        // Performance metrics endpoint
        register_rest_route('biic/v1', '/analytics/performance', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_performance_metrics'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'date_from' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d', strtotime('-7 days'))
                ),
                'date_to' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d')
                )
            )
        ));

        // Intent analytics endpoint
        register_rest_route('biic/v1', '/analytics/intents', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_intent_analytics'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'date_from' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d', strtotime('-30 days'))
                ),
                'date_to' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d')
                ),
                'limit' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 10,
                    'minimum' => 1,
                    'maximum' => 50
                )
            )
        ));

        // Export analytics endpoint
        register_rest_route('biic/v1', '/analytics/export', array(
            'methods' => 'POST',
            'callback' => array($this, 'export_analytics'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'format' => array(
                    'required' => true,
                    'type' => 'string',
                    'enum' => array('csv', 'json', 'pdf'),
                    'default' => 'csv'
                ),
                'type' => array(
                    'required' => true,
                    'type' => 'string',
                    'enum' => array('dashboard', 'conversations', 'leads', 'performance')
                ),
                'date_from' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d', strtotime('-30 days'))
                ),
                'date_to' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d')
                )
            )
        ));

        // Analytics insights endpoint
        register_rest_route('biic/v1', '/analytics/insights', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_analytics_insights'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));

        // Custom metrics endpoint
        register_rest_route('biic/v1', '/analytics/metrics', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_custom_metrics'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'metric_names' => array(
                    'required' => true,
                    'type' => 'array',
                    'items' => array('type' => 'string')
                ),
                'date_from' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d', strtotime('-30 days'))
                ),
                'date_to' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d')
                ),
                'period' => array(
                    'required' => false,
                    'type' => 'string',
                    'enum' => array('hourly', 'daily', 'weekly', 'monthly'),
                    'default' => 'daily'
                )
            )
        ));

        // Store custom metric endpoint
        register_rest_route('biic/v1', '/analytics/metrics', array(
            'methods' => 'POST',
            'callback' => array($this, 'store_custom_metric'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'metric_name' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'metric_value' => array(
                    'required' => true,
                    'type' => 'number'
                ),
                'metric_type' => array(
                    'required' => false,
                    'type' => 'string',
                    'enum' => array('counter', 'gauge', 'percentage', 'duration'),
                    'default' => 'counter'
                ),
                'date' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d')
                ),
                'dimensions' => array(
                    'required' => false,
                    'type' => 'object'
                )
            )
        ));
    }

    /**
     * Get dashboard analytics
     */
    public function get_dashboard_analytics($request) {
        $date_from = $request->get_param('date_from');
        $date_to = $request->get_param('date_to');

        $analytics = BIIC()->analytics;
        $data = $analytics->get_dashboard_analytics($date_from, $date_to);

        return rest_ensure_response(array(
            'success' => true,
            'data' => $data,
            'date_range' => array(
                'from' => $date_from,
                'to' => $date_to
            ),
            'generated_at' => current_time('mysql')
        ));
    }

    /**
     * Get conversation analytics
     */
    public function get_conversation_analytics($request) {
        $date_from = $request->get_param('date_from');
        $date_to = $request->get_param('date_to');

        global $wpdb;
        $database = BIIC()->database;

        // Daily conversation data
        $daily_conversations = $wpdb->get_results($wpdb->prepare(
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

        // Location distribution
        $location_distribution = $wpdb->get_results($wpdb->prepare(
            "SELECT country, COUNT(*) as count 
             FROM {$database->get_table('chat_sessions')} 
             WHERE DATE(started_at) BETWEEN %s AND %s 
             AND country IS NOT NULL 
             GROUP BY country 
             ORDER BY count DESC 
             LIMIT 10",
            $date_from, $date_to
        ));

        return rest_ensure_response(array(
            'success' => true,
            'data' => array(
                'daily_conversations' => $daily_conversations,
                'average_metrics' => array(
                    'messages' => round($avg_metrics->avg_messages ?? 0, 1),
                    'duration' => round($avg_metrics->avg_duration ?? 0, 1),
                    'lead_score' => round($avg_metrics->avg_lead_score ?? 0, 1)
                ),
                'peak_hours' => $peak_hours,
                'device_distribution' => $device_distribution,
                'location_distribution' => $location_distribution
            ),
            'date_range' => array(
                'from' => $date_from,
                'to' => $date_to
            )
        ));
    }

    /**
     * Get lead analytics
     */
    public function get_lead_analytics($request) {
        $date_from = $request->get_param('date_from');
        $date_to = $request->get_param('date_to');

        global $wpdb;
        $database = BIIC()->database;

        // Daily leads
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

        $conversion_rate = $total_sessions > 0 ? round(($total_leads / $total_sessions) * 100, 2) : 0;
        $close_rate = $total_leads > 0 ? round(($converted_leads / $total_leads) * 100, 2) : 0;

        return rest_ensure_response(array(
            'success' => true,
            'data' => array(
                'daily_leads' => $daily_leads,
                'status_distribution' => $status_distribution,
                'score_distribution' => $score_distribution,
                'course_interests' => $course_interests,
                'conversion_funnel' => array(
                    'total_sessions' => $total_sessions,
                    'total_leads' => $total_leads,
                    'converted_leads' => $converted_leads,
                    'conversion_rate' => $conversion_rate,
                    'close_rate' => $close_rate
                )
            ),
            'date_range' => array(
                'from' => $date_from,
                'to' => $date_to
            )
        ));
    }

    /**
     * Get real-time analytics
     */
    public function get_realtime_analytics($request) {
        global $wpdb;
        $database = BIIC()->database;

        $last_hour = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $last_5_minutes = date('Y-m-d H:i:s', strtotime('-5 minutes'));

        $data = array(
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
            )),
            'avg_response_time' => $wpdb->get_var($wpdb->prepare(
                "SELECT AVG(response_time) FROM {$database->get_table('chat_messages')} 
                 WHERE timestamp >= %s AND message_type = 'bot' AND response_time IS NOT NULL",
                $last_hour
            ))
        );

        return rest_ensure_response(array(
            'success' => true,
            'data' => $data,
            'timestamp' => current_time('mysql'),
            'last_updated' => $last_5_minutes
        ));
    }

    /**
     * Get performance metrics
     */
    public function get_performance_metrics($request) {
        $date_from = $request->get_param('date_from');
        $date_to = $request->get_param('date_to');

        global $wpdb;
        $database = BIIC()->database;

        // Response time metrics
        $response_metrics = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                AVG(response_time) as avg_response_time,
                MIN(response_time) as min_response_time,
                MAX(response_time) as max_response_time,
                COUNT(*) as total_responses
             FROM {$database->get_table('chat_messages')} 
             WHERE DATE(timestamp) BETWEEN %s AND %s 
             AND message_type = 'bot' 
             AND response_time IS NOT NULL 
             AND response_time > 0",
            $date_from, $date_to
        ));

        // User satisfaction metrics
        $satisfaction_metrics = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                AVG(feedback_score) as avg_feedback_score,
                COUNT(CASE WHEN is_helpful = 1 THEN 1 END) as helpful_count,
                COUNT(CASE WHEN is_helpful = 0 THEN 1 END) as not_helpful_count,
                COUNT(*) as total_feedback
             FROM {$database->get_table('chat_messages')} 
             WHERE DATE(timestamp) BETWEEN %s AND %s 
             AND (is_helpful IS NOT NULL OR feedback_score IS NOT NULL)",
            $date_from, $date_to
        ));

        // Error rate
        $error_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$database->get_table('user_interactions')} 
             WHERE DATE(timestamp) BETWEEN %s AND %s 
             AND interaction_type = 'error'",
            $date_from, $date_to
        ));

        $total_interactions = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$database->get_table('user_interactions')} 
             WHERE DATE(timestamp) BETWEEN %s AND %s",
            $date_from, $date_to
        ));

        $error_rate = $total_interactions > 0 ? round(($error_count / $total_interactions) * 100, 2) : 0;

        // Session completion rate
        $completed_sessions = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$database->get_table('chat_sessions')} 
             WHERE DATE(started_at) BETWEEN %s AND %s 
             AND ended_at IS NOT NULL 
             AND total_messages >= 3",
            $date_from, $date_to
        ));

        $total_sessions = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$database->get_table('chat_sessions')} 
             WHERE DATE(started_at) BETWEEN %s AND %s",
            $date_from, $date_to
        ));

        $completion_rate = $total_sessions > 0 ? round(($completed_sessions / $total_sessions) * 100, 2) : 0;

        return rest_ensure_response(array(
            'success' => true,
            'data' => array(
                'response_time' => array(
                    'average' => round($response_metrics->avg_response_time ?? 0, 2),
                    'minimum' => round($response_metrics->min_response_time ?? 0, 2),
                    'maximum' => round($response_metrics->max_response_time ?? 0, 2),
                    'total_responses' => (int) $response_metrics->total_responses
                ),
                'satisfaction' => array(
                    'average_score' => round($satisfaction_metrics->avg_feedback_score ?? 0, 2),
                    'helpful_percentage' => $satisfaction_metrics->total_feedback > 0 ? 
                        round(($satisfaction_metrics->helpful_count / $satisfaction_metrics->total_feedback) * 100, 2) : 0,
                    'total_feedback' => (int) $satisfaction_metrics->total_feedback
                ),
                'error_rate' => $error_rate,
                'completion_rate' => $completion_rate
            ),
            'date_range' => array(
                'from' => $date_from,
                'to' => $date_to
            )
        ));
    }

    /**
     * Get intent analytics
     */
    public function get_intent_analytics($request) {
        $date_from = $request->get_param('date_from');
        $date_to = $request->get_param('date_to');
        $limit = $request->get_param('limit');

        global $wpdb;
        $database = BIIC()->database;

        // Top intents
        $top_intents = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                detected_intent, 
                COUNT(*) as count,
                AVG(intent_confidence) as avg_confidence
             FROM {$database->get_table('chat_messages')} 
             WHERE DATE(timestamp) BETWEEN %s AND %s 
             AND detected_intent IS NOT NULL 
             GROUP BY detected_intent 
             ORDER BY count DESC 
             LIMIT %d",
            $date_from, $date_to, $limit
        ));

        // Intent trends over time
        $intent_trends = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                DATE(timestamp) as date,
                detected_intent,
                COUNT(*) as count
             FROM {$database->get_table('chat_messages')} 
             WHERE DATE(timestamp) BETWEEN %s AND %s 
             AND detected_intent IS NOT NULL 
             GROUP BY DATE(timestamp), detected_intent 
             ORDER BY date ASC, count DESC",
            $date_from, $date_to
        ));

        // Low confidence intents (need training)
        $low_confidence_intents = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                detected_intent,
                AVG(intent_confidence) as avg_confidence,
                COUNT(*) as count
             FROM {$database->get_table('chat_messages')} 
             WHERE DATE(timestamp) BETWEEN %s AND %s 
             AND detected_intent IS NOT NULL 
             AND intent_confidence < 0.7 
             GROUP BY detected_intent 
             ORDER BY avg_confidence ASC 
             LIMIT 10",
            $date_from, $date_to
        ));

        return rest_ensure_response(array(
            'success' => true,
            'data' => array(
                'top_intents' => $top_intents,
                'intent_trends' => $intent_trends,
                'low_confidence_intents' => $low_confidence_intents
            ),
            'date_range' => array(
                'from' => $date_from,
                'to' => $date_to
            )
        ));
    }

    /**
     * Export analytics
     */
    public function export_analytics($request) {
        $format = $request->get_param('format');
        $type = $request->get_param('type');
        $date_from = $request->get_param('date_from');
        $date_to = $request->get_param('date_to');

        // Get data based on type
        switch ($type) {
            case 'conversations':
                $data = $this->get_conversation_analytics_data($date_from, $date_to);
                break;
            case 'leads':
                $data = $this->get_lead_analytics_data($date_from, $date_to);
                break;
            case 'performance':
                $data = $this->get_performance_analytics_data($date_from, $date_to);
                break;
            default:
                $analytics = BIIC()->analytics;
                $data = $analytics->get_dashboard_analytics($date_from, $date_to);
        }

        // Generate export file
        $filename = "biic_analytics_{$type}_{$date_from}_to_{$date_to}";
        $export_url = $this->generate_export_file($data, $format, $filename);

        return rest_ensure_response(array(
            'success' => true,
            'export_url' => $export_url,
            'filename' => $filename . '.' . $format,
            'format' => $format,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour'))
        ));
    }

    /**
     * Get analytics insights
     */
    public function get_analytics_insights($request) {
        $analytics = BIIC()->analytics;
        $insights = $analytics->get_insights();

        return rest_ensure_response(array(
            'success' => true,
            'insights' => $insights,
            'generated_at' => current_time('mysql')
        ));
    }

    /**
     * Get custom metrics
     */
    public function get_custom_metrics($request) {
        $metric_names = $request->get_param('metric_names');
        $date_from = $request->get_param('date_from');
        $date_to = $request->get_param('date_to');
        $period = $request->get_param('period');

        global $wpdb;
        $database = BIIC()->database;

        $metrics_data = array();

        foreach ($metric_names as $metric_name) {
            $data = $wpdb->get_results($wpdb->prepare(
                "SELECT date, metric_value, dimensions 
                 FROM {$database->get_table('analytics')} 
                 WHERE metric_name = %s 
                 AND date BETWEEN %s AND %s 
                 AND period_type = %s 
                 ORDER BY date ASC",
                $metric_name, $date_from, $date_to, $period
            ));

            $metrics_data[$metric_name] = $data;
        }

        return rest_ensure_response(array(
            'success' => true,
            'metrics' => $metrics_data,
            'period' => $period,
            'date_range' => array(
                'from' => $date_from,
                'to' => $date_to
            )
        ));
    }

    /**
     * Store custom metric
     */
    public function store_custom_metric($request) {
        $metric_name = $request->get_param('metric_name');
        $metric_value = $request->get_param('metric_value');
        $metric_type = $request->get_param('metric_type');
        $date = $request->get_param('date');
        $dimensions = $request->get_param('dimensions');

        $database = BIIC()->database;
        $result = $database->store_analytics_metric(
            $date, 
            $metric_name, 
            $metric_value, 
            $metric_type, 
            $dimensions
        );

        if ($result !== false) {
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Metric stored successfully',
                'metric_name' => $metric_name,
                'metric_value' => $metric_value,
                'date' => $date
            ));
        } else {
            return new WP_Error('storage_failed', 'Failed to store metric', array('status' => 500));
        }
    }

    /**
     * Permission callback
     */
    public function check_admin_permission($request) {
        return current_user_can('manage_options');
    }

    /**
     * Helper methods
     */
    private function get_conversation_analytics_data($date_from, $date_to) {
        // Implementation for getting conversation data for export
        global $wpdb;
        $database = BIIC()->database;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT s.*, COUNT(m.id) as message_count 
             FROM {$database->get_table('chat_sessions')} s
             LEFT JOIN {$database->get_table('chat_messages')} m ON s.session_id = m.session_id
             WHERE DATE(s.started_at) BETWEEN %s AND %s
             GROUP BY s.id
             ORDER BY s.started_at DESC",
            $date_from, $date_to
        ), ARRAY_A);
    }

    private function get_lead_analytics_data($date_from, $date_to) {
        global $wpdb;
        $database = BIIC()->database;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$database->get_table('leads')} 
             WHERE DATE(created_at) BETWEEN %s AND %s 
             ORDER BY created_at DESC",
            $date_from, $date_to
        ), ARRAY_A);
    }

    private function get_performance_analytics_data($date_from, $date_to) {
        global $wpdb;
        $database = BIIC()->database;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT 
                DATE(timestamp) as date,
                AVG(response_time) as avg_response_time,
                COUNT(*) as message_count,
                AVG(feedback_score) as avg_feedback_score
             FROM {$database->get_table('chat_messages')} 
             WHERE DATE(timestamp) BETWEEN %s AND %s 
             AND message_type = 'bot'
             GROUP BY DATE(timestamp)
             ORDER BY date ASC",
            $date_from, $date_to
        ), ARRAY_A);
    }

    private function generate_export_file($data, $format, $filename) {
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/biic-exports/';

        if (!file_exists($export_dir)) {
            wp_mkdir_p($export_dir);
        }

        $file_path = $export_dir . $filename . '.' . $format;

        switch ($format) {
            case 'csv':
                $this->generate_csv_file($data, $file_path);
                break;
            case 'json':
                $this->generate_json_file($data, $file_path);
                break;
            case 'pdf':
                $this->generate_pdf_file($data, $file_path);
                break;
        }

        return $upload_dir['baseurl'] . '/biic-exports/' . $filename . '.' . $format;
    }

    private function generate_csv_file($data, $file_path) {
        $file = fopen($file_path, 'w');
        
        if (!empty($data)) {
            // Write headers
            fputcsv($file, array_keys($data[0]));
            
            // Write data
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
        }
        
        fclose($file);
    }

    private function generate_json_file($data, $file_path) {
        file_put_contents($file_path, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function generate_pdf_file($data, $file_path) {
        // For basic implementation, create a simple text file
        // In production, use a proper PDF library like TCPDF
        $content = "Analytics Report\n";
        $content .= "Generated: " . current_time('mysql') . "\n\n";
        $content .= json_encode($data, JSON_PRETTY_PRINT);
        
        file_put_contents($file_path, $content);
    }
}