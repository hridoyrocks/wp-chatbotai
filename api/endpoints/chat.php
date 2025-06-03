<?php
/**
 * Chat API Endpoints
 * api/endpoints/chat.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BIIC_Chat_Endpoints {
    
    /**
     * Register chat-related REST routes
     */
    public function register_routes() {
        // Main chat endpoint
        register_rest_route('biic/v1', '/chat', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_chat_message'),
            'permission_callback' => array($this, 'check_chat_permission'),
            'args' => array(
                'message' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => array($this, 'validate_message')
                ),
                'session_id' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));

        // Chat history endpoint
        register_rest_route('biic/v1', '/chat/history', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_chat_history'),
            'permission_callback' => array($this, 'check_chat_permission'),
            'args' => array(
                'session_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'limit' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 50,
                    'minimum' => 1,
                    'maximum' => 100
                )
            )
        ));

        // Start new session endpoint
        register_rest_route('biic/v1', '/chat/session', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_session'),
            'permission_callback' => array($this, 'check_chat_permission'),
            'args' => array(
                'user_info' => array(
                    'required' => false,
                    'type' => 'object'
                )
            )
        ));

        // End session endpoint
        register_rest_route('biic/v1', '/chat/session/(?P<session_id>[a-zA-Z0-9_]+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'end_session'),
            'permission_callback' => array($this, 'check_chat_permission')
        ));

        // Get session info endpoint
        register_rest_route('biic/v1', '/chat/session/(?P<session_id>[a-zA-Z0-9_]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_session_info'),
            'permission_callback' => array($this, 'check_chat_permission')
        ));

        // Send typing indicator
        register_rest_route('biic/v1', '/chat/typing', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_typing_indicator'),
            'permission_callback' => array($this, 'check_chat_permission'),
            'args' => array(
                'session_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'is_typing' => array(
                    'required' => true,
                    'type' => 'boolean'
                )
            )
        ));

        // Quick reply endpoint
        register_rest_route('biic/v1', '/chat/quick-reply', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_quick_reply'),
            'permission_callback' => array($this, 'check_chat_permission'),
            'args' => array(
                'session_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'reply_text' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));

        // File upload endpoint
        register_rest_route('biic/v1', '/chat/upload', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_file_upload'),
            'permission_callback' => array($this, 'check_file_upload_permission'),
            'args' => array(
                'session_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));

        // Feedback endpoint
        register_rest_route('biic/v1', '/chat/feedback', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_feedback'),
            'permission_callback' => array($this, 'check_chat_permission'),
            'args' => array(
                'message_id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'minimum' => 1
                ),
                'is_helpful' => array(
                    'required' => true,
                    'type' => 'boolean'
                ),
                'feedback_score' => array(
                    'required' => false,
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 5
                )
            )
        ));
    }

    /**
     * Handle chat message
     */
    public function handle_chat_message($request) {
        $message = $request->get_param('message');
        $session_id = $request->get_param('session_id');

        // Rate limiting
        if (!$this->check_rate_limit($session_id)) {
            return new WP_Error('rate_limit_exceeded', 'Rate limit exceeded', array('status' => 429));
        }

        // Create session if not provided
        if (empty($session_id)) {
            $session_id = $this->generate_session_id();
        }

        try {
            // Process message through chatbot
            $chatbot = BIIC()->chatbot;
            $response = $chatbot->process_message($message, $session_id);

            if ($response['success']) {
                // Track the interaction
                $user_tracking = BIIC()->user_tracking;
                $user_tracking->track_message($session_id, $message, $response['data']);

                return rest_ensure_response(array(
                    'success' => true,
                    'session_id' => $session_id,
                    'response' => $response['data'],
                    'timestamp' => current_time('mysql'),
                    'message_id' => $this->get_last_message_id($session_id)
                ));
            } else {
                return new WP_Error('processing_failed', $response['message'], array('status' => 500));
            }

        } catch (Exception $e) {
            error_log('BIIC Chat Error: ' . $e->getMessage());
            return new WP_Error('server_error', 'Internal server error', array('status' => 500));
        }
    }

    /**
     * Get chat history
     */
    public function get_chat_history($request) {
        $session_id = $request->get_param('session_id');
        $limit = $request->get_param('limit');

        $database = BIIC()->database;
        $messages = $database->get_chat_messages($session_id, $limit);

        $formatted_messages = array();
        foreach ($messages as $message) {
            $formatted_messages[] = array(
                'id' => $message->id,
                'type' => $message->message_type,
                'content' => $message->content,
                'timestamp' => $message->timestamp,
                'intent' => $message->detected_intent,
                'confidence' => $message->intent_confidence,
                'metadata' => $message->metadata ? json_decode($message->metadata, true) : null
            );
        }

        return rest_ensure_response(array(
            'success' => true,
            'session_id' => $session_id,
            'messages' => $formatted_messages,
            'total' => count($formatted_messages)
        ));
    }

    /**
     * Create new session
     */
    public function create_session($request) {
        $user_info = $request->get_param('user_info') ?: array();

        $database = BIIC()->database;
        
        // Gather user information
        $session_data = array_merge(array(
            'ip_address' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'referrer' => $_SERVER['HTTP_REFERER'] ?? '',
            'page_url' => $request->get_header('referer') ?: '',
            'utm_source' => $user_info['utm_source'] ?? null,
            'utm_medium' => $user_info['utm_medium'] ?? null,
            'utm_campaign' => $user_info['utm_campaign'] ?? null
        ), $user_info);

        $session_id = $database->insert_chat_session($session_data);

        if ($session_id) {
            // Get the session record
            global $wpdb;
            $session_record = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$database->get_table('chat_sessions')} WHERE id = %d",
                $session_id
            ));

            return rest_ensure_response(array(
                'success' => true,
                'session_id' => $session_record->session_id,
                'session_data' => array(
                    'started_at' => $session_record->started_at,
                    'device_type' => $session_record->device_type,
                    'location' => $session_record->location
                )
            ));
        } else {
            return new WP_Error('session_creation_failed', 'Failed to create session', array('status' => 500));
        }
    }

    /**
     * End session
     */
    public function end_session($request) {
        $session_id = $request->get_param('session_id');

        global $wpdb;
        $database = BIIC()->database;

        $result = $wpdb->update(
            $database->get_table('chat_sessions'),
            array(
                'ended_at' => current_time('mysql'),
                'is_active' => 0
            ),
            array('session_id' => $session_id),
            array('%s', '%d'),
            array('%s')
        );

        if ($result !== false) {
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Session ended successfully'
            ));
        } else {
            return new WP_Error('session_end_failed', 'Failed to end session', array('status' => 500));
        }
    }

    /**
     * Get session info
     */
    public function get_session_info($request) {
        $session_id = $request->get_param('session_id');

        $database = BIIC()->database;
        $session = $database->get_chat_session($session_id);

        if (!$session) {
            return new WP_Error('session_not_found', 'Session not found', array('status' => 404));
        }

        // Get session statistics
        global $wpdb;
        $message_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$database->get_table('chat_messages')} WHERE session_id = %s",
            $session_id
        ));

        $session_duration = 0;
        if ($session->started_at && $session->last_activity) {
            $session_duration = strtotime($session->last_activity) - strtotime($session->started_at);
        }

        return rest_ensure_response(array(
            'success' => true,
            'session_info' => array(
                'session_id' => $session->session_id,
                'started_at' => $session->started_at,
                'last_activity' => $session->last_activity,
                'is_active' => (bool) $session->is_active,
                'message_count' => (int) $message_count,
                'session_duration' => $session_duration,
                'lead_score' => (int) $session->lead_score,
                'lead_status' => $session->lead_status,
                'device_type' => $session->device_type,
                'location' => $session->location
            )
        ));
    }

    /**
     * Handle typing indicator
     */
    public function handle_typing_indicator($request) {
        $session_id = $request->get_param('session_id');
        $is_typing = $request->get_param('is_typing');

        // Track typing event
        $user_tracking = BIIC()->user_tracking;
        $user_tracking->track_event('typing_indicator', array(
            'session_id' => $session_id,
            'is_typing' => $is_typing,
            'timestamp' => current_time('mysql')
        ));

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Typing indicator processed'
        ));
    }

    /**
     * Handle quick reply
     */
    public function handle_quick_reply($request) {
        $session_id = $request->get_param('session_id');
        $reply_text = $request->get_param('reply_text');

        // Track quick reply usage
        $user_tracking = BIIC()->user_tracking;
        $user_tracking->track_event('quick_reply_used', array(
            'session_id' => $session_id,
            'reply_text' => $reply_text,
            'timestamp' => current_time('mysql')
        ));

        // Process as regular message
        $chatbot = BIIC()->chatbot;
        $response = $chatbot->process_message($reply_text, $session_id);

        if ($response['success']) {
            return rest_ensure_response(array(
                'success' => true,
                'session_id' => $session_id,
                'response' => $response['data'],
                'timestamp' => current_time('mysql')
            ));
        } else {
            return new WP_Error('processing_failed', $response['message'], array('status' => 500));
        }
    }

    /**
     * Handle file upload
     */
    public function handle_file_upload($request) {
        $session_id = $request->get_param('session_id');

        // Check if file uploads are enabled
        if (!get_option('biic_allow_file_upload', false)) {
            return new WP_Error('uploads_disabled', 'File uploads are disabled', array('status' => 403));
        }

        $files = $request->get_file_params();
        if (empty($files['file'])) {
            return new WP_Error('no_file', 'No file uploaded', array('status' => 400));
        }

        $file = $files['file'];

        // Validate file
        $validation = $this->validate_uploaded_file($file);
        if (is_wp_error($validation)) {
            return $validation;
        }

        // Upload file
        $upload_result = $this->process_file_upload($file, $session_id);
        if (is_wp_error($upload_result)) {
            return $upload_result;
        }

        // Track file upload
        $user_tracking = BIIC()->user_tracking;
        $user_tracking->track_event('file_uploaded', array(
            'session_id' => $session_id,
            'file_name' => $file['name'],
            'file_size' => $file['size'],
            'file_type' => $file['type'],
            'timestamp' => current_time('mysql')
        ));

        return rest_ensure_response(array(
            'success' => true,
            'file_info' => $upload_result,
            'message' => 'File uploaded successfully'
        ));
    }

    /**
     * Handle feedback
     */
    public function handle_feedback($request) {
        $message_id = $request->get_param('message_id');
        $is_helpful = $request->get_param('is_helpful');
        $feedback_score = $request->get_param('feedback_score');

        global $wpdb;
        $database = BIIC()->database;

        $update_data = array(
            'is_helpful' => $is_helpful ? 1 : 0
        );

        if ($feedback_score !== null) {
            $update_data['feedback_score'] = $feedback_score;
        }

        $result = $wpdb->update(
            $database->get_table('chat_messages'),
            $update_data,
            array('id' => $message_id),
            array('%d', '%d'),
            array('%d')
        );

        if ($result !== false) {
            // Track feedback event
            $user_tracking = BIIC()->user_tracking;
            $user_tracking->track_event('feedback_submitted', array(
                'message_id' => $message_id,
                'is_helpful' => $is_helpful,
                'feedback_score' => $feedback_score,
                'timestamp' => current_time('mysql')
            ));

            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Feedback recorded successfully'
            ));
        } else {
            return new WP_Error('feedback_failed', 'Failed to record feedback', array('status' => 500));
        }
    }

    /**
     * Permission callbacks
     */
    public function check_chat_permission($request) {
        // Allow public access for chat endpoints
        return true;
    }

    public function check_file_upload_permission($request) {
        // Check if file uploads are enabled
        return get_option('biic_allow_file_upload', false);
    }

    /**
     * Validation functions
     */
    public function validate_message($value, $request, $param) {
        if (empty(trim($value))) {
            return new WP_Error('empty_message', 'Message cannot be empty');
        }

        $max_length = get_option('biic_max_message_length', 1000);
        if (strlen($value) > $max_length) {
            return new WP_Error('message_too_long', "Message cannot exceed {$max_length} characters");
        }

        return true;
    }

    /**
     * Helper functions
     */
    private function check_rate_limit($session_id) {
        $rate_limit = get_option('biic_rate_limit', 10); // 10 messages per minute
        $time_window = 60;

        $cache_key = "biic_rate_limit_{$session_id}";
        $requests = get_transient($cache_key);

        if ($requests === false) {
            $requests = 1;
        } else {
            $requests++;
        }

        if ($requests > $rate_limit) {
            return false;
        }

        set_transient($cache_key, $requests, $time_window);
        return true;
    }

    private function generate_session_id() {
        return 'biic_' . uniqid() . '_' . wp_generate_password(8, false);
    }

    private function get_client_ip() {
        $ip_headers = array(
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );

        foreach ($ip_headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    private function get_last_message_id($session_id) {
        global $wpdb;
        $database = BIIC()->database;

        return $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$database->get_table('chat_messages')} 
             WHERE session_id = %s 
             ORDER BY timestamp DESC 
             LIMIT 1",
            $session_id
        ));
    }

    private function validate_uploaded_file($file) {
        // Check file size (5MB max)
        $max_size = 5 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            return new WP_Error('file_too_large', 'File size cannot exceed 5MB');
        }

        // Check file type
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain');
        if (!in_array($file['type'], $allowed_types)) {
            return new WP_Error('invalid_file_type', 'Invalid file type');
        }

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error('upload_error', 'File upload error');
        }

        return true;
    }

    private function process_file_upload($file, $session_id) {
        // Create upload directory
        $upload_dir = wp_upload_dir();
        $biic_dir = $upload_dir['basedir'] . '/biic-chatbot/' . $session_id . '/';

        if (!file_exists($biic_dir)) {
            wp_mkdir_p($biic_dir);
        }

        // Generate unique filename
        $filename = uniqid() . '_' . sanitize_file_name($file['name']);
        $file_path = $biic_dir . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            return array(
                'filename' => $filename,
                'original_name' => $file['name'],
                'file_size' => $file['size'],
                'file_type' => $file['type'],
                'upload_path' => $file_path,
                'url' => $upload_dir['baseurl'] . '/biic-chatbot/' . $session_id . '/' . $filename
            );
        } else {
            return new WP_Error('upload_failed', 'Failed to save uploaded file');
        }
    }
}