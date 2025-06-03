<?php
/**
 * Lead Management System for Banglay IELTS Chatbot
 * Advanced lead scoring, automated follow-up, and conversion tracking
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BIIC_Lead_Management {
    
    /**
     * Lead scoring rules
     */
    private $scoring_rules;
    
    /**
     * Follow-up intervals (in days)
     */
    private $follow_up_intervals = array(1, 3, 7, 14, 30);
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_scoring_rules();
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // AJAX handlers
        add_action('wp_ajax_biic_update_lead_status', array($this, 'ajax_update_lead_status'));
        add_action('wp_ajax_biic_add_lead_note', array($this, 'ajax_add_lead_note'));
        add_action('wp_ajax_biic_schedule_follow_up', array($this, 'ajax_schedule_follow_up'));
        add_action('wp_ajax_biic_bulk_lead_action', array($this, 'ajax_bulk_lead_action'));
        
        // Scheduled tasks
        add_action('biic_process_follow_ups', array($this, 'process_scheduled_follow_ups'));
        add_action('biic_lead_scoring_update', array($this, 'update_all_lead_scores'));
        
        // Schedule events if not already scheduled
        if (!wp_next_scheduled('biic_process_follow_ups')) {
            wp_schedule_event(time(), 'hourly', 'biic_process_follow_ups');
        }
        
        if (!wp_next_scheduled('biic_lead_scoring_update')) {
            wp_schedule_event(time(), 'daily', 'biic_lead_scoring_update');
        }
        
        // Email notifications
        add_action('biic_new_lead_created', array($this, 'send_new_lead_notification'));
        add_action('biic_lead_status_changed', array($this, 'send_status_change_notification'), 10, 3);
    }
    
    /**
     * Initialize lead scoring rules
     */
    private function init_scoring_rules() {
        $this->scoring_rules = array(
            // Interaction-based scoring
            'actions' => array(
                'message_sent' => 2,
                'phone_clicked' => 25,
                'email_clicked' => 15,
                'form_submitted' => 30,
                'file_downloaded' => 20,
                'page_time_5min' => 10,
                'multiple_sessions' => 15,
                'returned_visitor' => 20
            ),
            
            // Intent-based scoring
            'intents' => array(
                'course_fee' => 25,
                'admission' => 30,
                'contact_info' => 20,
                'study_abroad' => 25,
                'course_inquiry' => 15,
                'schedule' => 10,
                'branch_location' => 8,
                'greeting' => 2
            ),
            
            // Course interest scoring
            'courses' => array(
                'ielts_comprehensive' => 20,
                'study_abroad' => 25,
                'ielts_focus' => 15,
                'ielts_crash' => 12,
                'online_course' => 10
            ),
            
            // Demographics scoring
            'demographics' => array(
                'has_name' => 10,
                'has_email' => 10,
                'has_phone' => 15,
                'dhaka_location' => 5,
                'student_age' => 10
            ),
            
            // Engagement scoring
            'engagement' => array(
                'session_duration_5min' => 10,
                'session_duration_10min' => 15,
                'messages_count_5' => 8,
                'messages_count_10' => 12,
                'quick_replies_used' => 5,
                'file_shared' => 15
            )
        );
    }
    
    /**
     * Create a new lead
     */
    public function create_lead($session_id, $lead_data) {
        $database = BIIC()->database;
        
        // Calculate initial lead score
        $lead_score = $this->calculate_lead_score($session_id, $lead_data);
        $lead_data['lead_score'] = $lead_score;
        
        // Determine lead priority based on score
        $lead_data['priority'] = $this->determine_priority($lead_score);
        
        // Set lead source if not provided
        if (!isset($lead_data['lead_source'])) {
            $lead_data['lead_source'] = 'chatbot';
        }
        
        // Insert lead into database
        $lead_id = $database->upsert_lead($lead_data);
        
        if ($lead_id) {
            // Trigger new lead action
            do_action('biic_new_lead_created', $lead_id, $lead_data);
            
            // Schedule initial follow-up
            $this->schedule_follow_up($lead_id, 1); // Follow up in 1 day
            
            // Update session with lead information
            $this->update_session_lead_status($session_id, $lead_score);
        }
        
        return $lead_id;
    }
    
    /**
     * Calculate comprehensive lead score
     */
    public function calculate_lead_score($session_id, $lead_data = array()) {
        global $wpdb;
        $database = BIIC()->database;
        
        $score = 0;
        
        // Get session data
        $session = $database->get_chat_session($session_id);
        if (!$session) {
            return 0;
        }
        
        // Get messages for this session
        $messages = $database->get_chat_messages($session_id);
        
        // Get user interactions
        $interactions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$database->get_table('user_interactions')} WHERE session_id = %s",
            $session_id
        ));
        
        // Demographics scoring
        if (!empty($lead_data['name'])) {
            $score += $this->scoring_rules['demographics']['has_name'];
        }
        if (!empty($lead_data['email'])) {
            $score += $this->scoring_rules['demographics']['has_email'];
        }
        if (!empty($lead_data['phone'])) {
            $score += $this->scoring_rules['demographics']['has_phone'];
        }
        if (strpos(strtolower($session->location ?? ''), 'dhaka') !== false) {
            $score += $this->scoring_rules['demographics']['dhaka_location'];
        }
        
        // Course interest scoring
        if (!empty($lead_data['course_interest']) && isset($this->scoring_rules['courses'][$lead_data['course_interest']])) {
            $score += $this->scoring_rules['courses'][$lead_data['course_interest']];
        }
        
        // Intent-based scoring from messages
        $intents_found = array();
        foreach ($messages as $message) {
            if ($message->detected_intent && isset($this->scoring_rules['intents'][$message->detected_intent])) {
                if (!in_array($message->detected_intent, $intents_found)) {
                    $score += $this->scoring_rules['intents'][$message->detected_intent];
                    $intents_found[] = $message->detected_intent;
                }
            }
        }
        
        // Engagement scoring
        $session_duration = $this->calculate_session_duration($session);
        if ($session_duration >= 600) { // 10 minutes
            $score += $this->scoring_rules['engagement']['session_duration_10min'];
        } elseif ($session_duration >= 300) { // 5 minutes
            $score += $this->scoring_rules['engagement']['session_duration_5min'];
        }
        
        $message_count = count($messages);
        if ($message_count >= 10) {
            $score += $this->scoring_rules['engagement']['messages_count_10'];
        } elseif ($message_count >= 5) {
            $score += $this->scoring_rules['engagement']['messages_count_5'];
        }
        
        // Interaction-based scoring
        foreach ($interactions as $interaction) {
            $interaction_data = json_decode($interaction->interaction_data, true);
            
            switch ($interaction->interaction_type) {
                case 'phone_click':
                    $score += $this->scoring_rules['actions']['phone_clicked'];
                    break;
                case 'email_click':
                    $score += $this->scoring_rules['actions']['email_clicked'];
                    break;
                case 'file_download':
                    $score += $this->scoring_rules['actions']['file_downloaded'];
                    break;
                case 'quick_reply_used':
                    $score += $this->scoring_rules['engagement']['quick_replies_used'];
                    break;
            }
        }
        
        // Device and location bonus
        if ($session->device_type === 'mobile') {
            $score += 5; // Mobile users are more engaged
        }
        
        // Time-based scoring (business hours)
        $session_hour = (int) date('H', strtotime($session->started_at));
        if ($session_hour >= 10 && $session_hour <= 18) {
            $score += 5; // Sessions during business hours
        }
        
        // Return visitor bonus
        $previous_sessions = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$database->get_table('chat_sessions')} 
             WHERE ip_address = %s AND id != %d",
            $session->ip_address,
            $session->id
        ));
        
        if ($previous_sessions > 0) {
            $score += $this->scoring_rules['actions']['returned_visitor'];
        }
        
        // Cap the score at 100
        return min(100, $score);
    }
    
    /**
     * Update lead status
     */
    public function update_lead_status($lead_id, $new_status, $notes = '') {
        global $wpdb;
        $database = BIIC()->database;
        
        // Get current lead data
        $lead = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$database->get_table('leads')} WHERE id = %d",
            $lead_id
        ));
        
        if (!$lead) {
            return false;
        }
        
        $old_status = $lead->lead_status;
        
        $update_data = array(
            'lead_status' => $new_status,
            'updated_at' => current_time('mysql')
        );
        
        // Add notes if provided
        if (!empty($notes)) {
            $existing_notes = $lead->notes ? $lead->notes . "\n\n" : '';
            $update_data['notes'] = $existing_notes . '[' . current_time('Y-m-d H:i:s') . '] ' . $notes;
        }
        
        // Set conversion date if converting
        if ($new_status === 'converted' && $old_status !== 'converted') {
            $update_data['conversion_date'] = current_time('mysql');
            
            // Recalculate conversion value based on course interest
            $conversion_value = $this->estimate_conversion_value($lead->course_interest);
            $update_data['conversion_value'] = $conversion_value;
        }
        
        // Update last contact date
        $update_data['last_contact_date'] = current_time('mysql');
        
        $result = $wpdb->update(
            $database->get_table('leads'),
            $update_data,
            array('id' => $lead_id),
            array('%s', '%s', '%s', '%s', '%f', '%s'),
            array('%d')
        );
        
        if ($result !== false) {
            // Trigger status change action
            do_action('biic_lead_status_changed', $lead_id, $old_status, $new_status);
            
            // Schedule appropriate follow-up
            $this->schedule_status_based_follow_up($lead_id, $new_status);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Add note to lead
     */
    public function add_lead_note($lead_id, $note, $user_id = null) {
        global $wpdb;
        $database = BIIC()->database;
        
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $user = get_user_by('id', $user_id);
        $author = $user ? $user->display_name : 'System';
        
        $formatted_note = '[' . current_time('Y-m-d H:i:s') . ' - ' . $author . '] ' . $note;
        
        // Get existing notes
        $existing_notes = $wpdb->get_var($wpdb->prepare(
            "SELECT notes FROM {$database->get_table('leads')} WHERE id = %d",
            $lead_id
        ));
        
        $new_notes = $existing_notes ? $existing_notes . "\n\n" . $formatted_note : $formatted_note;
        
        return $wpdb->update(
            $database->get_table('leads'),
            array(
                'notes' => $new_notes,
                'updated_at' => current_time('mysql')
            ),
            array('id' => $lead_id),
            array('%s', '%s'),
            array('%d')
        );
    }
    
    /**
     * Schedule follow-up
     */
    public function schedule_follow_up($lead_id, $days_from_now, $notes = '') {
        global $wpdb;
        $database = BIIC()->database;
        
        $follow_up_date = date('Y-m-d', strtotime("+{$days_from_now} days"));
        
        $result = $wpdb->update(
            $database->get_table('leads'),
            array(
                'follow_up_date' => $follow_up_date,
                'follow_up_notes' => $notes,
                'updated_at' => current_time('mysql')
            ),
            array('id' => $lead_id),
            array('%s', '%s', '%s'),
            array('%d')
        );
        
        // Schedule WordPress cron event
        wp_schedule_single_event(
            strtotime($follow_up_date . ' 10:00:00'),
            'biic_lead_follow_up',
            array($lead_id)
        );
        
        return $result !== false;
    }
    
    /**
     * Process scheduled follow-ups
     */
    public function process_scheduled_follow_ups() {
        global $wpdb;
        $database = BIIC()->database;
        
        $today = current_time('Y-m-d');
        
        // Get leads due for follow-up
        $leads_due = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$database->get_table('leads')} 
             WHERE follow_up_date <= %s 
             AND lead_status NOT IN ('converted', 'lost')
             ORDER BY follow_up_date ASC",
            $today
        ));
        
        foreach ($leads_due as $lead) {
            // Send follow-up notification
            $this->send_follow_up_notification($lead);
            
            // Update follow-up date to next interval
            $this->schedule_next_follow_up($lead->id, $lead->lead_status);
        }
        
        // Log the processing
        error_log("BIIC: Processed " . count($leads_due) . " follow-up leads");
    }
    
    /**
     * Send follow-up notification
     */
    private function send_follow_up_notification($lead) {
        $notification_email = get_option('biic_notification_email', get_option('admin_email'));
        
        $subject = sprintf('Follow-up Required: %s', $lead->name ?: 'Lead #' . $lead->id);
        
        $message = "Follow-up required for lead:\n\n";
        $message .= "Name: " . ($lead->name ?: 'Not provided') . "\n";
        $message .= "Phone: " . ($lead->phone ?: 'Not provided') . "\n";
        $message .= "Email: " . ($lead->email ?: 'Not provided') . "\n";
        $message .= "Course Interest: " . ($lead->course_interest ?: 'Not specified') . "\n";
        $message .= "Lead Score: " . $lead->lead_score . "/100\n";
        $message .= "Status: " . ucfirst($lead->lead_status) . "\n";
        $message .= "Created: " . $lead->created_at . "\n";
        
        if ($lead->follow_up_notes) {
            $message .= "\nFollow-up Notes:\n" . $lead->follow_up_notes . "\n";
        }
        
        if ($lead->notes) {
            $message .= "\nPrevious Notes:\n" . $lead->notes . "\n";
        }
        
        $message .= "\nView lead details in admin panel.";
        
        wp_mail($notification_email, $subject, $message);
    }
    
    /**
     * Update all lead scores (daily task)
     */
    public function update_all_lead_scores() {
        global $wpdb;
        $database = BIIC()->database;
        
        // Get all active leads
        $leads = $wpdb->get_results(
            "SELECT id, session_id FROM {$database->get_table('leads')} 
             WHERE lead_status NOT IN ('converted', 'lost')"
        );
        
        $updated_count = 0;
        
        foreach ($leads as $lead) {
            $new_score = $this->calculate_lead_score($lead->session_id);
            
            $result = $wpdb->update(
                $database->get_table('leads'),
                array('lead_score' => $new_score),
                array('id' => $lead->id),
                array('%d'),
                array('%d')
            );
            
            if ($result !== false) {
                $updated_count++;
            }
        }
        
        error_log("BIIC: Updated lead scores for {$updated_count} leads");
    }
    
    /**
     * AJAX: Update lead status
     */
    public function ajax_update_lead_status() {
        check_ajax_referer('biic_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'banglay-ielts-chatbot'));
        }
        
        $lead_id = intval($_POST['lead_id']);
        $status = sanitize_text_field($_POST['status']);
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');
        
        if ($this->update_lead_status($lead_id, $status, $notes)) {
            wp_send_json_success(__('Lead status updated successfully', 'banglay-ielts-chatbot'));
        } else {
            wp_send_json_error(__('Failed to update lead status', 'banglay-ielts-chatbot'));
        }
    }
    
    /**
     * AJAX: Add lead note
     */
    public function ajax_add_lead_note() {
        check_ajax_referer('biic_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'banglay-ielts-chatbot'));
        }
        
        $lead_id = intval($_POST['lead_id']);
        $note = sanitize_textarea_field($_POST['note']);
        
        if ($this->add_lead_note($lead_id, $note)) {
            wp_send_json_success(__('Note added successfully', 'banglay-ielts-chatbot'));
        } else {
            wp_send_json_error(__('Failed to add note', 'banglay-ielts-chatbot'));
        }
    }
    
    /**
     * AJAX: Schedule follow-up
     */
    public function ajax_schedule_follow_up() {
        check_ajax_referer('biic_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'banglay-ielts-chatbot'));
        }
        
        $lead_id = intval($_POST['lead_id']);
        $days = intval($_POST['days']);
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');
        
        if ($this->schedule_follow_up($lead_id, $days, $notes)) {
            wp_send_json_success(__('Follow-up scheduled successfully', 'banglay-ielts-chatbot'));
        } else {
            wp_send_json_error(__('Failed to schedule follow-up', 'banglay-ielts-chatbot'));
        }
    }
    
    /**
     * Send new lead notification
     */
    public function send_new_lead_notification($lead_id, $lead_data) {
        if (!get_option('biic_lead_notifications', true)) {
            return;
        }
        
        $notification_email = get_option('biic_notification_email', get_option('admin_email'));
        $subject = get_option('biic_new_lead_email_subject', 'New Lead from Banglay IELTS Chatbot');
        
        // Get email template
        $template = get_option('biic_new_lead_email_template', 
            'New lead received from chatbot:\n\nName: {name}\nPhone: {phone}\nEmail: {email}\nCourse Interest: {course_interest}\nLead Score: {lead_score}/100'
        );
        
        // Replace placeholders
        $replacements = array(
            '{name}' => $lead_data['name'] ?? 'Not provided',
            '{phone}' => $lead_data['phone'] ?? 'Not provided',
            '{email}' => $lead_data['email'] ?? 'Not provided',
            '{course_interest}' => $lead_data['course_interest'] ?? 'Not specified',
            '{lead_score}' => $lead_data['lead_score'] ?? 0
        );
        
        $message = str_replace(array_keys($replacements), array_values($replacements), $template);
        $message .= "\n\nTime: " . current_time('mysql');
        $message .= "\nView details in admin panel.";
        
        wp_mail($notification_email, $subject, $message);
    }
    
    /**
     * Send status change notification
     */
    public function send_status_change_notification($lead_id, $old_status, $new_status) {
        // Only send for important status changes
        $important_changes = array(
            'qualified' => 'Lead qualified for conversion',
            'converted' => 'Lead successfully converted! 🎉',
            'lost' => 'Lead marked as lost'
        );
        
        if (!isset($important_changes[$new_status])) {
            return;
        }
        
        global $wpdb;
        $database = BIIC()->database;
        
        $lead = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$database->get_table('leads')} WHERE id = %d",
            $lead_id
        ));
        
        if (!$lead) {
            return;
        }
        
        $notification_email = get_option('biic_notification_email', get_option('admin_email'));
        $subject = sprintf('Lead Status Update: %s', $lead->name ?: 'Lead #' . $lead_id);
        
        $message = $important_changes[$new_status] . "\n\n";
        $message .= "Lead Details:\n";
        $message .= "Name: " . ($lead->name ?: 'Not provided') . "\n";
        $message .= "Phone: " . ($lead->phone ?: 'Not provided') . "\n";
        $message .= "Email: " . ($lead->email ?: 'Not provided') . "\n";
        $message .= "Course Interest: " . ($lead->course_interest ?: 'Not specified') . "\n";
        $message .= "Lead Score: " . $lead->lead_score . "/100\n";
        $message .= "Previous Status: " . ucfirst($old_status) . "\n";
        $message .= "New Status: " . ucfirst($new_status) . "\n";
        
        if ($new_status === 'converted' && $lead->conversion_value) {
            $message .= "Conversion Value: $" . number_format($lead->conversion_value, 2) . "\n";
        }
        
        wp_mail($notification_email, $subject, $message);
    }
    
    /**
     * Helper methods
     */
    
    private function calculate_session_duration($session) {
        $start = strtotime($session->started_at);
        $end = $session->last_activity ? strtotime($session->last_activity) : time();
        return $end - $start;
    }
    
    private function determine_priority($score) {
        if ($score >= 80) return 'high';
        if ($score >= 50) return 'medium';
        return 'low';
    }
    
    private function estimate_conversion_value($course_interest) {
        $course_values = array(
            'ielts_comprehensive' => 15000, // BDT
            'study_abroad' => 25000,
            'ielts_focus' => 12000,
            'ielts_crash' => 8000,
            'online_course' => 5000
        );
        
        return $course_values[$course_interest] ?? 10000;
    }
    
    private function update_session_lead_status($session_id, $lead_score) {
        global $wpdb;
        $database = BIIC()->database;
        
        $lead_status = 'cold';
        if ($lead_score >= 80) {
            $lead_status = 'hot';
        } elseif ($lead_score >= 50) {
            $lead_status = 'warm';
        }
        
        $wpdb->update(
            $database->get_table('chat_sessions'),
            array(
                'lead_score' => $lead_score,
                'lead_status' => $lead_status
            ),
            array('session_id' => $session_id),
            array('%d', '%s'),
            array('%s')
        );
    }
    
    private function schedule_status_based_follow_up($lead_id, $status) {
        $follow_up_days = array(
            'new' => 1,
            'contacted' => 3,
            'qualified' => 1,
            'converted' => 30, // Check-in call
            'lost' => 60 // Re-engagement attempt
        );
        
        if (isset($follow_up_days[$status])) {
            $this->schedule_follow_up($lead_id, $follow_up_days[$status]);
        }
    }
    
    private function schedule_next_follow_up($lead_id, $current_status) {
        // Determine next follow-up interval based on current status
        $intervals = array(
            'new' => 3,
            'contacted' => 7,
            'qualified' => 3
        );
        
        if (isset($intervals[$current_status])) {
            $this->schedule_follow_up($lead_id, $intervals[$current_status]);
        }
    }
}