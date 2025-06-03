<?php
/**
 * User Tracking System for Banglay IELTS Chatbot
 * Advanced user behavior analytics and session management
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BIIC_User_Tracking {
    
    /**
     * Current session data
     */
    private $current_session;
    private $tracking_enabled;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->tracking_enabled = get_option('biic_analytics_enabled', true);
        
        if ($this->tracking_enabled) {
            $this->init_hooks();
        }
    }
    
    /**
     * Initialize tracking hooks
     */
    private function init_hooks() {
        // AJAX handlers for tracking
        add_action('wp_ajax_biic_track_event', array($this, 'handle_track_event'));
        add_action('wp_ajax_nopriv_biic_track_event', array($this, 'handle_track_event'));
        
        add_action('wp_ajax_biic_track_page_view', array($this, 'handle_page_view'));
        add_action('wp_ajax_nopriv_biic_track_page_view', array($this, 'handle_page_view'));
        
        add_action('wp_ajax_biic_submit_lead', array($this, 'handle_lead_submission'));
        add_action('wp_ajax_nopriv_biic_submit_lead', array($this, 'handle_lead_submission'));
        
        // Scheduled cleanup
        add_action('biic_cleanup_old_sessions', array($this, 'cleanup_old_sessions'));
        
        if (!wp_next_scheduled('biic_cleanup_old_sessions')) {
            wp_schedule_event(time(), 'daily', 'biic_cleanup_old_sessions');
        }
    }
    
    /**
     * Initialize tracking for current session
     */
    public function init_tracking() {
        if (!$this->tracking_enabled) return;
        
        // Start session if not already started
        if (!session_id()) {
            session_start();
        }
        
        // Track page view
        $this->track_page_view();
        
        // Initialize user session tracking
        $this->init_user_session();
    }
    
    /**
     * Initialize user session
     */
    private function init_user_session() {
        $session_id = isset($_SESSION['biic_session_id']) ? $_SESSION['biic_session_id'] : null;
        
        if (!$session_id) {
            // Create new session
            $this->current_session = $this->create_new_tracking_session();
            $_SESSION['biic_session_id'] = $this->current_session['session_id'];
        } else {
            // Load existing session
            $database = BIIC()->database;
            $this->current_session = $database->get_chat_session($session_id);
            
            if ($this->current_session) {
                // Update last activity
                $this->update_session_activity($session_id);
            }
        }
    }
    
    /**
     * Create new tracking session
     */
    private function create_new_tracking_session() {
        $database = BIIC()->database;
        
        // Gather user information
        $user_info = $this->gather_user_info();
        
        $session_data = array(
            'ip_address' => $user_info['ip_address'],
            'user_agent' => $user_info['user_agent'],
            'location' => $user_info['location'],
            'country' => $user_info['country'],
            'city' => $user_info['city'],
            'device_type' => $user_info['device_type'],
            'browser' => $user_info['browser'],
            'referrer' => $user_info['referrer'],
            'page_url' => $user_info['current_url'],
            'utm_source' => $user_info['utm_source'],
            'utm_medium' => $user_info['utm_medium'],
            'utm_campaign' => $user_info['utm_campaign']
        );
        
        $session_id = $database->insert_chat_session($session_data);
        
        if ($session_id) {
            // Get session ID string
            global $wpdb;
            $session_record = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$database->get_table('chat_sessions')} WHERE id = %d",
                $session_id
            ));
            
            return (array) $session_record;
        }
        
        return null;
    }
    
    /**
     * Gather comprehensive user information
     */
    private function gather_user_info() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip_address = $this->get_real_ip_address();
        
        // Device detection
        $device_info = $this->detect_device($user_agent);
        
        // Location detection (enhanced)
        $location_info = $this->get_location_info($ip_address);
        
        // UTM parameters
        $utm_info = $this->extract_utm_parameters();
        
        // Browser and OS detection
        $browser_info = $this->detect_browser_and_os($user_agent);
        
        return array_merge(
            array(
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
                'referrer' => $_SERVER['HTTP_REFERER'] ?? '',
                'current_url' => $this->get_current_url()
            ),
            $device_info,
            $location_info,
            $utm_info,
            $browser_info
        );
    }
    
    /**
     * Get real IP address (handle proxies, CloudFlare, etc.)
     */
    private function get_real_ip_address() {
        $ip_headers = array(
            'HTTP_CF_CONNECTING_IP',     // CloudFlare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Default
        );
        
        foreach ($ip_headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                
                // Handle multiple IPs (take first one)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    
    /**
     * Advanced device detection
     */
    private function detect_device($user_agent) {
        $device_type = 'desktop';
        $device_model = '';
        $screen_size = '';
        
        // Mobile detection
        $mobile_patterns = array(
            'iPhone' => 'iPhone',
            'iPad' => 'iPad',
            'Android' => 'Android',
            'BlackBerry' => 'BlackBerry',
            'Windows Phone' => 'Windows Phone'
        );
        
        foreach ($mobile_patterns as $pattern => $device) {
            if (stripos($user_agent, $pattern) !== false) {
                $device_type = strpos($pattern, 'iPad') !== false ? 'tablet' : 'mobile';
                $device_model = $device;
                break;
            }
        }
        
        // Tablet detection
        $tablet_patterns = array('iPad', 'tablet', 'Kindle', 'Silk', 'PlayBook');
        foreach ($tablet_patterns as $pattern) {
            if (stripos($user_agent, $pattern) !== false) {
                $device_type = 'tablet';
                break;
            }
        }
        
        return array(
            'device_type' => $device_type,
            'device_model' => $device_model
        );
    }
    
    /**
     * Enhanced location detection
     */
    private function get_location_info($ip_address) {
        // Default for local/private IPs
        if ($ip_address === '127.0.0.1' || 
            strpos($ip_address, '192.168.') === 0 || 
            strpos($ip_address, '10.') === 0 ||
            strpos($ip_address, '172.') === 0) {
            return array(
                'location' => 'Dhaka, Bangladesh',
                'country' => 'Bangladesh',
                'city' => 'Dhaka'
            );
        }
        
        // Try to get location from IP (you can integrate with services like IPinfo, MaxMind, etc.)
        $location_data = $this->get_location_from_api($ip_address);
        
        if ($location_data) {
            return $location_data;
        }
        
        // Fallback to Bangladesh (most users)
        return array(
            'location' => 'Bangladesh',
            'country' => 'Bangladesh', 
            'city' => 'Unknown'
        );
    }
    
    /**
     * Get location from IP API (placeholder for real implementation)
     */
    private function get_location_from_api($ip_address) {
        // For production, integrate with:
        // - IPinfo.io
        // - MaxMind GeoIP2
        // - ipapi.co
        // - ip-api.com
        
        // Example with ip-api.com (free tier)
        try {
            $response = wp_remote_get("http://ip-api.com/json/{$ip_address}");
            
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);
                
                if ($data && $data['status'] === 'success') {
                    return array(
                        'location' => $data['city'] . ', ' . $data['country'],
                        'country' => $data['country'],
                        'city' => $data['city']
                    );
                }
            }
        } catch (Exception $e) {
            error_log('BIIC Location API Error: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Extract UTM parameters and campaign data
     */
    private function extract_utm_parameters() {
        return array(
            'utm_source' => isset($_GET['utm_source']) ? sanitize_text_field($_GET['utm_source']) : null,
            'utm_medium' => isset($_GET['utm_medium']) ? sanitize_text_field($_GET['utm_medium']) : null,
            'utm_campaign' => isset($_GET['utm_campaign']) ? sanitize_text_field($_GET['utm_campaign']) : null,
            'utm_term' => isset($_GET['utm_term']) ? sanitize_text_field($_GET['utm_term']) : null,
            'utm_content' => isset($_GET['utm_content']) ? sanitize_text_field($_GET['utm_content']) : null
        );
    }
    
    /**
     * Advanced browser and OS detection
     */
    private function detect_browser_and_os($user_agent) {
        // Browser detection
        $browsers = array(
            'Edge' => 'Edg/',
            'Chrome' => 'Chrome/',
            'Firefox' => 'Firefox/',
            'Safari' => 'Safari/',
            'Opera' => 'Opera/',
            'Internet Explorer' => 'MSIE'
        );
        
        $browser = 'Unknown';
        $browser_version = '';
        
        foreach ($browsers as $name => $pattern) {
            if (strpos($user_agent, $pattern) !== false) {
                $browser = $name;
                
                // Extract version
                if (preg_match("/{$pattern}([0-9.]+)/", $user_agent, $matches)) {
                    $browser_version = $matches[1];
                }
                break;
            }
        }
        
        // OS detection
        $os_patterns = array(
            'Windows 10' => 'Windows NT 10.0',
            'Windows 8.1' => 'Windows NT 6.3',
            'Windows 8' => 'Windows NT 6.2',
            'Windows 7' => 'Windows NT 6.1',
            'Mac OS X' => 'Mac OS X',
            'Linux' => 'Linux',
            'Android' => 'Android',
            'iOS' => '(iPhone|iPad)'
        );
        
        $os = 'Unknown';
        foreach ($os_patterns as $name => $pattern) {
            if (preg_match("/{$pattern}/", $user_agent)) {
                $os = $name;
                break;
            }
        }
        
        return array(
            'browser' => $browser,
            'browser_version' => $browser_version,
            'os' => $os
        );
    }
    
    /**
     * Get current URL
     */
    private function get_current_url() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        return $protocol . '://' . $host . $uri;
    }
    
    /**
     * Track page view
     */
    private function track_page_view() {
        if (!$this->current_session) return;
        
        $database = BIIC()->database;
        
        $interaction_data = array(
            'session_id' => $this->current_session['session_id'],
            'interaction_type' => 'page_view',
            'page_url' => $this->get_current_url(),
            'interaction_data' => json_encode(array(
                'title' => get_the_title(),
                'timestamp' => current_time('mysql')
            ))
        );
        
        $database->insert_user_interaction($interaction_data);
    }
    
    /**
     * Track user event
     */
    public function track_event($event_type, $event_data = array()) {
        if (!$this->tracking_enabled || !$this->current_session) return;
        
        $database = BIIC()->database;
        
        $interaction_data = array(
            'session_id' => $this->current_session['session_id'],
            'interaction_type' => $event_type,
            'page_url' => $this->get_current_url(),
            'interaction_data' => json_encode($event_data)
        );
        
        return $database->insert_user_interaction($interaction_data);
    }
    
    /**
     * Track message interaction
     */
    public function track_message($session_id, $user_message, $bot_response) {
        if (!$this->tracking_enabled) return;
        
        // Track message exchange as interaction
        $this->track_event('message_exchange', array(
            'user_message' => $user_message,
            'bot_response' => is_array($bot_response) ? $bot_response['message'] : $bot_response,
            'intent' => is_array($bot_response) && isset($bot_response['intent']) ? $bot_response['intent'] : null,
            'timestamp' => current_time('mysql')
        ));
        
        // Update session activity
        $this->update_session_activity($session_id);
    }
    
    /**
     * Update session activity
     */
    private function update_session_activity($session_id) {
        global $wpdb;
        $database = BIIC()->database;
        
        // Update last activity time
        $wpdb->update(
            $database->get_table('chat_sessions'),
            array(
                'last_activity' => current_time('mysql')
            ),
            array('session_id' => $session_id),
            array('%s'),
            array('%s')
        );
        
        // Update message count
        $message_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$database->get_table('chat_messages')} WHERE session_id = %s",
            $session_id
        ));
        
        $wpdb->update(
            $database->get_table('chat_sessions'),
            array('total_messages' => $message_count),
            array('session_id' => $session_id),
            array('%d'),
            array('%s')
        );
    }
    
    /**
     * AJAX: Handle event tracking
     */
    public function handle_track_event() {
        if (!wp_verify_nonce($_POST['nonce'], 'biic_chat_nonce')) {
            wp_die(__('Security check failed', 'banglay-ielts-chatbot'));
        }
        
        $event_name = sanitize_text_field($_POST['event_name']);
        $event_data = json_decode(stripslashes($_POST['event_data']), true);
        
        $this->track_event($event_name, $event_data);
        
        wp_send_json_success('Event tracked');
    }
    
    /**
     * AJAX: Handle page view tracking
     */
    public function handle_page_view() {
        if (!wp_verify_nonce($_POST['nonce'], 'biic_chat_nonce')) {
            wp_die(__('Security check failed', 'banglay-ielts-chatbot'));
        }
        
        $page_data = array(
            'url' => sanitize_url($_POST['url']),
            'title' => sanitize_text_field($_POST['title']),
            'referrer' => sanitize_url($_POST['referrer'])
        );
        
        $this->track_event('page_view', $page_data);
        
        wp_send_json_success('Page view tracked');
    }
    
    /**
     * AJAX: Handle lead submission
     */
    public function handle_lead_submission() {
        if (!wp_verify_nonce($_POST['nonce'], 'biic_chat_nonce')) {
            wp_die(__('Security check failed', 'banglay-ielts-chatbot'));
        }
        
        $lead_data = json_decode(stripslashes($_POST['lead_data']), true);
        
        // Validate required fields
        if (empty($lead_data['session_id']) || empty($lead_data['phone'])) {
            wp_send_json_error('Required fields missing');
        }
        
        // Sanitize data
        $sanitized_data = array(
            'session_id' => sanitize_text_field($lead_data['session_id']),
            'name' => sanitize_text_field($lead_data['name'] ?? ''),
            'phone' => sanitize_text_field($lead_data['phone']),
            'email' => sanitize_email($lead_data['email'] ?? ''),
            'course_interest' => sanitize_text_field($lead_data['course_interest'] ?? ''),
            'lead_source' => 'chatbot',
            'lead_status' => 'new'
        );
        
        // Calculate initial lead score
        $lead_score = $this->calculate_initial_lead_score($sanitized_data);
        $sanitized_data['lead_score'] = $lead_score;
        
        // Save lead
        $database = BIIC()->database;
        $lead_id = $database->upsert_lead($sanitized_data);
        
        if ($lead_id) {
            // Track lead submission event
            $this->track_event('lead_submitted', $sanitized_data);
            
            // Send notification if enabled
            if (get_option('biic_lead_notifications', true)) {
                $this->send_lead_notification($sanitized_data);
            }
            
            wp_send_json_success(array(
                'message' => 'Lead submitted successfully',
                'lead_id' => $lead_id
            ));
        } else {
            wp_send_json_error('Failed to save lead');
        }
    }
    
    /**
     * Calculate initial lead score
     */
    private function calculate_initial_lead_score($lead_data) {
        $score = 30; // Base score for lead submission
        
        // Add points for complete information
        if (!empty($lead_data['name'])) $score += 10;
        if (!empty($lead_data['email'])) $score += 10;
        if (!empty($lead_data['course_interest'])) $score += 15;
        
        // Add points based on course interest
        $high_value_courses = array('ielts_comprehensive', 'study_abroad');
        if (in_array($lead_data['course_interest'], $high_value_courses)) {
            $score += 20;
        }
        
        // Check session activity for additional scoring
        global $wpdb;
        $database = BIIC()->database;
        
        $session_data = $wpdb->get_row($wpdb->prepare(
            "SELECT lead_score, total_messages FROM {$database->get_table('chat_sessions')} WHERE session_id = %s",
            $lead_data['session_id']
        ));
        
        if ($session_data) {
            $score += min(20, $session_data->total_messages * 2); // Up to 20 points for engagement
            $score += $session_data->lead_score; // Add session lead score
        }
        
        return min(100, $score); // Cap at 100
    }
    
    /**
     * Send lead notification
     */
    private function send_lead_notification($lead_data) {
        $notification_email = get_option('biic_notification_email', get_option('admin_email'));
        
        $subject = 'New Lead from Banglay IELTS Chatbot';
        
        $message = "New lead submitted through chatbot:\n\n";
        $message .= "Name: {$lead_data['name']}\n";
        $message .= "Phone: {$lead_data['phone']}\n";
        $message .= "Email: {$lead_data['email']}\n";
        $message .= "Course Interest: {$lead_data['course_interest']}\n";
        $message .= "Lead Score: {$lead_data['lead_score']}/100\n";
        $message .= "Time: " . current_time('mysql') . "\n\n";
        $message .= "View details in admin panel.";
        
        wp_mail($notification_email, $subject, $message);
    }
    
    /**
     * Get user analytics
     */
    public function get_user_analytics($session_id) {
        global $wpdb;
        $database = BIIC()->database;
        
        // Get session data
        $session = $database->get_chat_session($session_id);
        
        // Get interactions
        $interactions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$database->get_table('user_interactions')} 
            WHERE session_id = %s 
            ORDER BY timestamp ASC",
            $session_id
        ));
        
        // Get messages
        $messages = $database->get_chat_messages($session_id);
        
        return array(
            'session' => $session,
            'interactions' => $interactions,
            'messages' => $messages,
            'analytics' => $this->calculate_session_analytics($session, $interactions, $messages)
        );
    }
    
    /**
     * Calculate session analytics
     */
    private function calculate_session_analytics($session, $interactions, $messages) {
        $analytics = array();
        
        if ($session) {
            // Session duration
            $start_time = strtotime($session->started_at);
            $end_time = $session->ended_at ? strtotime($session->ended_at) : time();
            $analytics['duration_minutes'] = round(($end_time - $start_time) / 60, 2);
            
            // Message stats
            $analytics['total_messages'] = count($messages);
            $analytics['user_messages'] = count(array_filter($messages, function($msg) {
                return $msg->message_type === 'user';
            }));
            $analytics['bot_messages'] = count(array_filter($messages, function($msg) {
                return $msg->message_type === 'bot';
            }));
            
            // Interaction stats
            $analytics['total_interactions'] = count($interactions);
            $analytics['unique_pages'] = count(array_unique(array_column($interactions, 'page_url')));
            
            // Lead scoring
            $analytics['lead_score'] = $session->lead_score ?? 0;
            $analytics['lead_status'] = $session->lead_status ?? 'cold';
            
            // Engagement metrics
            $analytics['messages_per_minute'] = $analytics['duration_minutes'] > 0 ? 
                round($analytics['total_messages'] / $analytics['duration_minutes'], 2) : 0;
        }
        
        return $analytics;
    }
    
    /**
     * Cleanup old sessions
     */
    public function cleanup_old_sessions() {
        $retention_days = get_option('biic_data_retention_days', 365);
        
        $database = BIIC()->database;
        $database->cleanup_old_sessions($retention_days);
        
        error_log("BIIC: Cleaned up sessions older than {$retention_days} days");
    }
    
    /**
     * Get current session
     */
    public function get_current_session() {
        return $this->current_session;
    }
    
    /**
     * Check if tracking is enabled
     */
    public function is_tracking_enabled() {
        return $this->tracking_enabled;
    }
}