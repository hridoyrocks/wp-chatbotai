<?php
/**
 * Admin Dashboard for Banglay IELTS Chatbot

 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BIIC_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', array($this, 'init'));
    }
    
    /**
     * Initialize admin
     */
    public function init() {
        // Add AJAX handlers
        add_action('wp_ajax_biic_get_dashboard_stats', array($this, 'get_dashboard_stats'));
        add_action('wp_ajax_biic_get_conversations_data', array($this, 'get_conversations_data'));
        add_action('wp_ajax_biic_get_leads_data', array($this, 'get_leads_data'));
        add_action('wp_ajax_biic_export_data', array($this, 'export_data'));
        add_action('wp_ajax_biic_update_settings', array($this, 'update_settings'));
        add_action('wp_ajax_biic_delete_conversation', array($this, 'delete_conversation'));
        add_action('wp_ajax_biic_update_lead_status', array($this, 'update_lead_status'));
    }
    
    /**
     * Dashboard page
     */
    public function dashboard_page() {
        $database = BIIC()->database;
        
        // Get today's stats
        $today = current_time('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day', current_time('timestamp')));
        $last_week = date('Y-m-d', strtotime('-7 days', current_time('timestamp')));
        $last_month = date('Y-m-d', strtotime('-30 days', current_time('timestamp')));
        
        // Calculate metrics
        $stats = $this->calculate_dashboard_stats();
        
        include BIIC_ADMIN_PATH . 'views/dashboard.php';
    }
    
    /**
     * Conversations page
     */
    public function conversations_page() {
        $database = BIIC()->database;
        
        // Get filters
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 20;
        $offset = ($current_page - 1) * $per_page;
        
        $filters = array();
        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = sanitize_text_field($_GET['date_from']);
        }
        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = sanitize_text_field($_GET['date_to']);
        }
        if (!empty($_GET['lead_status'])) {
            $filters['lead_status'] = sanitize_text_field($_GET['lead_status']);
        }
        
        // Get conversations
        $conversations = $database->get_recent_conversations($per_page, $offset);
        
        include BIIC_ADMIN_PATH . 'views/conversations.php';
    }
    
    /**
     * Leads page
     */
    public function leads_page() {
        $database = BIIC()->database;
        
        // Get filters
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 20;
        $offset = ($current_page - 1) * $per_page;
        
        $filters = array();
        if (!empty($_GET['status'])) {
            $filters['status'] = sanitize_text_field($_GET['status']);
        }
        if (!empty($_GET['course_interest'])) {
            $filters['course_interest'] = sanitize_text_field($_GET['course_interest']);
        }
        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = sanitize_text_field($_GET['date_from']);
        }
        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = sanitize_text_field($_GET['date_to']);
        }
        
        // Get leads
        $leads = $database->get_leads($filters, $per_page, $offset);
        
        // Get lead statistics
        $lead_stats = $this->calculate_lead_stats();
        
        include BIIC_ADMIN_PATH . 'views/leads.php';
    }
    
    /**
     * Analytics page
     */
    public function analytics_page() {
        $database = BIIC()->database;
        
        // Get date range from request
        $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : date('Y-m-d', strtotime('-30 days'));
        $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : current_time('Y-m-d');
        
        // Get analytics data
        $analytics_data = $this->get_analytics_data($date_from, $date_to);
        
        include BIIC_ADMIN_PATH . 'views/analytics.php';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        // Handle form submission
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['biic_settings_nonce'], 'biic_save_settings')) {
            $this->save_settings();
            echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'banglay-ielts-chatbot') . '</p></div>';
        }
        
        // Get current settings
        $settings = $this->get_current_settings();
        
        include BIIC_ADMIN_PATH . 'views/settings.php';
    }
    
    /**
     * Calculate dashboard statistics
     */
    private function calculate_dashboard_stats() {
        global $wpdb;
        $database = BIIC()->database;
        
        $today = current_time('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day', current_time('timestamp')));
        $last_week = date('Y-m-d', strtotime('-7 days', current_time('timestamp')));
        $last_month = date('Y-m-d', strtotime('-30 days', current_time('timestamp')));
        
        $sessions_table = $database->get_table('chat_sessions');
        $messages_table = $database->get_table('chat_messages');
        $leads_table = $database->get_table('leads');
        
        // Total conversations today
        $conversations_today = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $sessions_table WHERE DATE(started_at) = %s",
            $today
        ));
        
        // Total conversations yesterday
        $conversations_yesterday = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $sessions_table WHERE DATE(started_at) = %s",
            $yesterday
        ));
        
        // Total messages today
        $messages_today = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $messages_table WHERE DATE(timestamp) = %s",
            $today
        ));
        
        // New leads today
        $leads_today = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $leads_table WHERE DATE(created_at) = %s",
            $today
        ));
        
        // New leads yesterday
        $leads_yesterday = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $leads_table WHERE DATE(created_at) = %s",
            $yesterday
        ));
        
        // Active sessions (last 5 minutes)
        $active_threshold = date('Y-m-d H:i:s', strtotime('-5 minutes', current_time('timestamp')));
        $active_sessions = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $sessions_table WHERE last_activity >= %s AND is_active = 1",
            $active_threshold
        ));
        
        // Conversion rate (leads / sessions) for last 30 days
        $sessions_last_month = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $sessions_table WHERE DATE(started_at) >= %s",
            $last_month
        ));
        
        $leads_last_month = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $leads_table WHERE DATE(created_at) >= %s",
            $last_month
        ));
        
        $conversion_rate = $sessions_last_month > 0 ? ($leads_last_month / $sessions_last_month) * 100 : 0;
        
        // Average response time (bot messages only)
        $avg_response_time = $wpdb->get_var(
            "SELECT AVG(response_time) FROM $messages_table 
            WHERE message_type = 'bot' AND response_time IS NOT NULL AND response_time > 0"
        );
        
        // Top intents
        $top_intents = $wpdb->get_results(
            "SELECT detected_intent, COUNT(*) as count 
            FROM $messages_table 
            WHERE detected_intent IS NOT NULL 
            AND DATE(timestamp) >= '$last_week'
            GROUP BY detected_intent 
            ORDER BY count DESC 
            LIMIT 5"
        );
        
        // Course interest distribution
        $course_interests = $wpdb->get_results(
            "SELECT course_interest, COUNT(*) as count 
            FROM $leads_table 
            WHERE course_interest IS NOT NULL 
            AND DATE(created_at) >= '$last_month'
            GROUP BY course_interest 
            ORDER BY count DESC"
        );
        
        // Calculate percentage changes
        $conversations_change = $conversations_yesterday > 0 ? 
            (($conversations_today - $conversations_yesterday) / $conversations_yesterday) * 100 : 
            ($conversations_today > 0 ? 100 : 0);
            
        $leads_change = $leads_yesterday > 0 ? 
            (($leads_today - $leads_yesterday) / $leads_yesterday) * 100 : 
            ($leads_today > 0 ? 100 : 0);
        
        return array(
            'conversations_today' => $conversations_today,
            'conversations_change' => round($conversations_change, 1),
            'messages_today' => $messages_today,
            'leads_today' => $leads_today,
            'leads_change' => round($leads_change, 1),
            'active_sessions' => $active_sessions,
            'conversion_rate' => round($conversion_rate, 2),
            'avg_response_time' => round($avg_response_time, 2),
            'top_intents' => $top_intents,
            'course_interests' => $course_interests
        );
    }
    
    /**
     * Calculate lead statistics
     */
    private function calculate_lead_stats() {
        global $wpdb;
        $database = BIIC()->database;
        $leads_table = $database->get_table('leads');
        
        $today = current_time('Y-m-d');
        $last_month = date('Y-m-d', strtotime('-30 days', current_time('timestamp')));
        
        // Total leads
        $total_leads = $wpdb->get_var("SELECT COUNT(*) FROM $leads_table");
        
        // Leads by status
        $leads_by_status = $wpdb->get_results(
            "SELECT lead_status, COUNT(*) as count 
            FROM $leads_table 
            GROUP BY lead_status 
            ORDER BY count DESC"
        );
        
        // New leads this month
        $new_leads_month = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $leads_table WHERE DATE(created_at) >= %s",
            $last_month
        ));
        
        // Converted leads this month
        $converted_leads_month = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $leads_table 
            WHERE lead_status = 'converted' AND DATE(updated_at) >= %s",
            $last_month
        ));
        
        // Conversion rate
        $conversion_rate = $new_leads_month > 0 ? 
            ($converted_leads_month / $new_leads_month) * 100 : 0;
        
        // Lead score distribution
        $lead_scores = $wpdb->get_results(
            "SELECT 
                CASE 
                    WHEN lead_score >= 80 THEN 'Hot (80-100)'
                    WHEN lead_score >= 60 THEN 'Warm (60-79)'
                    WHEN lead_score >= 40 THEN 'Medium (40-59)'
                    ELSE 'Cold (0-39)'
                END as score_range,
                COUNT(*) as count
            FROM $leads_table 
            GROUP BY score_range 
            ORDER BY MIN(lead_score) DESC"
        );
        
        return array(
            'total_leads' => $total_leads,
            'leads_by_status' => $leads_by_status,
            'new_leads_month' => $new_leads_month,
            'converted_leads_month' => $converted_leads_month,
            'conversion_rate' => round($conversion_rate, 2),
            'lead_scores' => $lead_scores
        );
    }
    
    /**
     * Get analytics data
     */
    private function get_analytics_data($date_from, $date_to) {
        global $wpdb;
        $database = BIIC()->database;
        
        $sessions_table = $database->get_table('chat_sessions');
        $messages_table = $database->get_table('chat_messages');
        $leads_table = $database->get_table('leads');
        $interactions_table = $database->get_table('user_interactions');
        
        // Daily conversations
        $daily_conversations = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(started_at) as date, COUNT(*) as count 
            FROM $sessions_table 
            WHERE DATE(started_at) BETWEEN %s AND %s 
            GROUP BY DATE(started_at) 
            ORDER BY date ASC",
            $date_from,
            $date_to
        ));
        
        // Daily messages
        $daily_messages = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(timestamp) as date, COUNT(*) as count 
            FROM $messages_table 
            WHERE DATE(timestamp) BETWEEN %s AND %s 
            GROUP BY DATE(timestamp) 
            ORDER BY date ASC",
            $date_from,
            $date_to
        ));
        
        // Daily leads
        $daily_leads = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(created_at) as date, COUNT(*) as count 
            FROM $leads_table 
            WHERE DATE(created_at) BETWEEN %s AND %s 
            GROUP BY DATE(created_at) 
            ORDER BY date ASC",
            $date_from,
            $date_to
        ));
        
        // Most common intents
        $common_intents = $wpdb->get_results($wpdb->prepare(
            "SELECT detected_intent, COUNT(*) as count 
            FROM $messages_table 
            WHERE detected_intent IS NOT NULL 
            AND DATE(timestamp) BETWEEN %s AND %s 
            GROUP BY detected_intent 
            ORDER BY count DESC 
            LIMIT 10",
            $date_from,
            $date_to
        ));
        
        // User locations (from sessions)
        $user_locations = $wpdb->get_results($wpdb->prepare(
            "SELECT country, city, COUNT(*) as count 
            FROM $sessions_table 
            WHERE country IS NOT NULL 
            AND DATE(started_at) BETWEEN %s AND %s 
            GROUP BY country, city 
            ORDER BY count DESC 
            LIMIT 20",
            $date_from,
            $date_to
        ));
        
        // Device types
        $device_types = $wpdb->get_results($wpdb->prepare(
            "SELECT device_type, COUNT(*) as count 
            FROM $sessions_table 
            WHERE device_type IS NOT NULL 
            AND DATE(started_at) BETWEEN %s AND %s 
            GROUP BY device_type 
            ORDER BY count DESC",
            $date_from,
            $date_to
        ));
        
        // Peak hours
        $peak_hours = $wpdb->get_results($wpdb->prepare(
            "SELECT HOUR(started_at) as hour, COUNT(*) as count 
            FROM $sessions_table 
            WHERE DATE(started_at) BETWEEN %s AND %s 
            GROUP BY HOUR(started_at) 
            ORDER BY hour ASC",
            $date_from,
            $date_to
        ));
        
        return array(
            'daily_conversations' => $daily_conversations,
            'daily_messages' => $daily_messages,
            'daily_leads' => $daily_leads,
            'common_intents' => $common_intents,
            'user_locations' => $user_locations,
            'device_types' => $device_types,
            'peak_hours' => $peak_hours
        );
    }
    
    /**
     * Get current settings
     */
    private function get_current_settings() {
        return array(
            'chatbot_enabled' => get_option('biic_chatbot_enabled', true),
            'auto_greeting' => get_option('biic_auto_greeting', true),
            'welcome_message' => get_option('biic_welcome_message', 'আস্সালামু আলাইকুম! IELTS এর ব্যাপারে কিছু জানতে চান?'),
            'chat_position' => get_option('biic_chat_position', 'bottom-right'),
            'chat_theme' => get_option('biic_chat_theme', 'modern'),
            'openai_api_key' => get_option('biic_openai_api_key', ''),
            'max_message_length' => get_option('biic_max_message_length', 1000),
            'typing_speed' => get_option('biic_typing_speed', 50),
            'enable_sounds' => get_option('biic_enable_sounds', true),
            'enable_animations' => get_option('biic_enable_animations', true),
            'business_hours' => get_option('biic_business_hours', array(
                'start' => '10:00',
                'end' => '18:00',
                'days' => array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday')
            )),
            'lead_notifications' => get_option('biic_lead_notifications', true),
            'notification_email' => get_option('biic_notification_email', get_option('admin_email')),
            'analytics_enabled' => get_option('biic_analytics_enabled', true),
            'data_retention_days' => get_option('biic_data_retention_days', 365),
            'allow_file_upload' => get_option('biic_allow_file_upload', false),
            'timezone' => get_option('biic_timezone', 'Asia/Dhaka')
        );
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        $settings = array(
            'biic_chatbot_enabled' => isset($_POST['chatbot_enabled']),
            'biic_auto_greeting' => isset($_POST['auto_greeting']),
            'biic_welcome_message' => sanitize_textarea_field($_POST['welcome_message']),
            'biic_chat_position' => sanitize_text_field($_POST['chat_position']),
            'biic_chat_theme' => sanitize_text_field($_POST['chat_theme']),
            'biic_openai_api_key' => sanitize_text_field($_POST['openai_api_key']),
            'biic_max_message_length' => intval($_POST['max_message_length']),
            'biic_typing_speed' => intval($_POST['typing_speed']),
            'biic_enable_sounds' => isset($_POST['enable_sounds']),
            'biic_enable_animations' => isset($_POST['enable_animations']),
            'biic_lead_notifications' => isset($_POST['lead_notifications']),
            'biic_notification_email' => sanitize_email($_POST['notification_email']),
            'biic_analytics_enabled' => isset($_POST['analytics_enabled']),
            'biic_data_retention_days' => intval($_POST['data_retention_days']),
            'biic_allow_file_upload' => isset($_POST['allow_file_upload']),
            'biic_timezone' => sanitize_text_field($_POST['timezone'])
        );
        
        // Business hours
        $business_hours = array(
            'start' => sanitize_text_field($_POST['business_hours_start']),
            'end' => sanitize_text_field($_POST['business_hours_end']),
            'days' => isset($_POST['business_days']) ? array_map('sanitize_text_field', $_POST['business_days']) : array()
        );
        $settings['biic_business_hours'] = $business_hours;
        
        // Save all settings
        foreach ($settings as $key => $value) {
            update_option($key, $value);
        }
    }
    
    /**
     * AJAX: Get dashboard stats
     */
    public function get_dashboard_stats() {
        check_ajax_referer('biic_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'banglay-ielts-chatbot'));
        }
        
        $stats = $this->calculate_dashboard_stats();
        wp_send_json_success($stats);
    }
    
    /**
     * AJAX: Get conversations data
     */
    public function get_conversations_data() {
        check_ajax_referer('biic_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'banglay-ielts-chatbot'));
        }
        
        $page = intval($_POST['page']);
        $per_page = intval($_POST['per_page']);
        $filters = isset($_POST['filters']) ? $_POST['filters'] : array();
        
        $database = BIIC()->database;
        $conversations = $database->get_recent_conversations($per_page, ($page - 1) * $per_page);
        
        wp_send_json_success($conversations);
    }
    
    /**
     * AJAX: Get leads data
     */
    public function get_leads_data() {
        check_ajax_referer('biic_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'banglay-ielts-chatbot'));
        }
        
        $page = intval($_POST['page']);
        $per_page = intval($_POST['per_page']);
        $filters = isset($_POST['filters']) ? $_POST['filters'] : array();
        
        $database = BIIC()->database;
        $leads = $database->get_leads($filters, $per_page, ($page - 1) * $per_page);
        
        wp_send_json_success($leads);
    }
    
    /**
     * AJAX: Export data
     */
    public function export_data() {
        check_ajax_referer('biic_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'banglay-ielts-chatbot'));
        }
        
        $export_type = sanitize_text_field($_POST['export_type']);
        $date_from = sanitize_text_field($_POST['date_from']);
        $date_to = sanitize_text_field($_POST['date_to']);
        $format = sanitize_text_field($_POST['format']); // csv, xlsx, pdf
        
        $this->generate_export($export_type, $date_from, $date_to, $format);
    }
    
    /**
     * AJAX: Delete conversation
     */
    public function delete_conversation() {
        check_ajax_referer('biic_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'banglay-ielts-chatbot'));
        }
        
        $session_id = sanitize_text_field($_POST['session_id']);
        
        global $wpdb;
        $database = BIIC()->database;
        
        // Delete from all related tables
        $wpdb->delete($database->get_table('chat_sessions'), array('session_id' => $session_id));
        $wpdb->delete($database->get_table('chat_messages'), array('session_id' => $session_id));
        $wpdb->delete($database->get_table('user_interactions'), array('session_id' => $session_id));
        $wpdb->delete($database->get_table('leads'), array('session_id' => $session_id));
        
        wp_send_json_success(__('Conversation deleted successfully', 'banglay-ielts-chatbot'));
    }
    
    /**
     * AJAX: Update lead status
     */
    public function update_lead_status() {
        check_ajax_referer('biic_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'banglay-ielts-chatbot'));
        }
        
        $lead_id = intval($_POST['lead_id']);
        $status = sanitize_text_field($_POST['status']);
        $notes = sanitize_textarea_field($_POST['notes']);
        
        global $wpdb;
        $database = BIIC()->database;
        
        $update_data = array(
            'lead_status' => $status,
            'updated_at' => current_time('mysql')
        );
        
        if (!empty($notes)) {
            $update_data['notes'] = $notes;
        }
        
        if ($status === 'converted') {
            $update_data['conversion_date'] = current_time('mysql');
        }
        
        $result = $wpdb->update(
            $database->get_table('leads'),
            $update_data,
            array('id' => $lead_id)
        );
        
        if ($result !== false) {
            wp_send_json_success(__('Lead status updated successfully', 'banglay-ielts-chatbot'));
        } else {
            wp_send_json_error(__('Failed to update lead status', 'banglay-ielts-chatbot'));
        }
    }
    
    /**
     * Generate export file
     */
    private function generate_export($type, $date_from, $date_to, $format) {
        global $wpdb;
        $database = BIIC()->database;
        
        switch ($type) {
            case 'conversations':
                $data = $wpdb->get_results($wpdb->prepare(
                    "SELECT s.session_id, s.started_at, s.ended_at, s.total_messages, 
                            s.ip_address, s.location, s.device_type, s.lead_score,
                            l.name, l.email, l.phone, l.course_interest
                    FROM {$database->get_table('chat_sessions')} s
                    LEFT JOIN {$database->get_table('leads')} l ON s.session_id = l.session_id
                    WHERE DATE(s.started_at) BETWEEN %s AND %s
                    ORDER BY s.started_at DESC",
                    $date_from,
                    $date_to
                ), ARRAY_A);
                $filename = 'biic_conversations_' . $date_from . '_to_' . $date_to;
                break;
                
            case 'leads':
                $data = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM {$database->get_table('leads')}
                    WHERE DATE(created_at) BETWEEN %s AND %s
                    ORDER BY created_at DESC",
                    $date_from,
                    $date_to
                ), ARRAY_A);
                $filename = 'biic_leads_' . $date_from . '_to_' . $date_to;
                break;
                
            case 'messages':
                $data = $wpdb->get_results($wpdb->prepare(
                    "SELECT m.*, s.ip_address, s.location 
                    FROM {$database->get_table('chat_messages')} m
                    LEFT JOIN {$database->get_table('chat_sessions')} s ON m.session_id = s.session_id
                    WHERE DATE(m.timestamp) BETWEEN %s AND %s
                    ORDER BY m.timestamp DESC",
                    $date_from,
                    $date_to
                ), ARRAY_A);
                $filename = 'biic_messages_' . $date_from . '_to_' . $date_to;
                break;
                
            default:
                wp_send_json_error(__('Invalid export type', 'banglay-ielts-chatbot'));
                return;
        }
        
        if (empty($data)) {
            wp_send_json_error(__('No data found for the selected date range', 'banglay-ielts-chatbot'));
            return;
        }
        
        // Generate file based on format
        switch ($format) {
            case 'csv':
                $this->export_to_csv($data, $filename);
                break;
            case 'xlsx':
                $this->export_to_xlsx($data, $filename);
                break;
            case 'pdf':
                $this->export_to_pdf($data, $filename);
                break;
            default:
                $this->export_to_csv($data, $filename);
        }
    }
    
    /**
     * Export to CSV
     */
    private function export_to_csv($data, $filename) {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fputs($output, "\xEF\xBB\xBF");
        
        // Add headers
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            
            // Add data rows
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Export to XLSX (simplified - would need PhpSpreadsheet for full implementation)
     */
    private function export_to_xlsx($data, $filename) {
        // For now, fallback to CSV
        // In production, implement with PhpSpreadsheet library
        $this->export_to_csv($data, $filename);
    }
    
    /**
     * Export to PDF (simplified - would need TCPDF or similar for full implementation)
     */
    private function export_to_pdf($data, $filename) {
        // For now, fallback to CSV
        // In production, implement with TCPDF or similar library
        $this->export_to_csv($data, $filename);
    }
}