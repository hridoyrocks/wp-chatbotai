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