<?php
/**
 * Main Plugin Class for Banglay IELTS Chatbot
 * Core plugin functionality and component coordination
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BIIC_Main {
    
    /**
     * Plugin version
     */
    public $version = '1.0.0';
    
    /**
     * Plugin components
     */
    public $database;
    public $chatbot;
    public $ai_integration;
    public $user_tracking;
    public $lead_management;
    public $analytics;
    
    /**
     * Plugin settings
     */
    private $settings;
    
    /**
     * Error handler
     */
    private $errors = array();
    
    /**
     * Single instance
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize the plugin
     */
    private function init() {
        // Load plugin settings
        $this->load_settings();
        
        // Initialize error handling
        $this->init_error_handling();
        
        // Initialize components
        $this->init_components();
        
        // Setup WordPress hooks
        $this->setup_hooks();
        
        // Initialize frontend/admin specific features
        if (is_admin()) {
            $this->init_admin();
        } else {
            $this->init_frontend();
        }
        
        // Initialize REST API
        $this->init_rest_api();
    }
    
    /**
     * Load plugin settings
     */
    private function load_settings() {
        $default_settings = array(
            'chatbot_enabled' => true,
            'chat_position' => 'bottom-right',
            'chat_theme' => 'modern',
            'welcome_message' => 'আস্সালামু আলাইকুম! IELTS এর ব্যাপারে কিছু জানতে চান?',
            'auto_greeting' => true,
            'typing_speed' => 50,
            'max_message_length' => 1000,
            'enable_sounds' => true,
            'enable_animations' => true,
            'analytics_enabled' => true,
            'lead_notifications' => true,
            'notification_email' => get_option('admin_email'),
            'data_retention_days' => 365,
            'timezone' => 'Asia/Dhaka',
            'business_hours' => array(
                'start' => '10:00',
                'end' => '18:00',
                'days' => array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday')
            )
        );
        
        $this->settings = array();
        foreach ($default_settings as $key => $default_value) {
            $this->settings[$key] = get_option('biic_' . $key, $default_value);
        }
    }
    
    /**
     * Initialize error handling
     */
    private function init_error_handling() {
        // Custom error handler for plugin
        set_error_handler(array($this, 'handle_php_error'));
        
        // WordPress error logging
        add_action('wp_loaded', array($this, 'check_system_requirements'));
        
        // AJAX error handling
        add_action('wp_ajax_biic_report_error', array($this, 'handle_ajax_error'));
        add_action('wp_ajax_nopriv_biic_report_error', array($this, 'handle_ajax_error'));
    }
    
    /**
     * Initialize plugin components
     */
    private function init_components() {
        try {
            // Initialize database first
            $this->database = new BIIC_Database();
            
            // Initialize AI integration
            $this->ai_integration = new BIIC_AI_Integration();
            
            // Initialize chatbot core
            $this->chatbot = new BIIC_Chatbot();
            
            // Initialize user tracking
            $this->user_tracking = new BIIC_User_Tracking();
            
            // Initialize lead management
            $this->lead_management = new BIIC_Lead_Management();
            
            // Initialize analytics
            $this->analytics = new BIIC_Analytics();
            
        } catch (Exception $e) {
            $this->log_error('Component initialization failed: ' . $e->getMessage());
            add_action('admin_notices', array($this, 'show_initialization_error'));
        }
    }
    
    /**
     * Setup WordPress hooks
     */
    private function setup_hooks() {
        // Core WordPress hooks
        add_action('init', array($this, 'on_wp_init'));
        add_action('wp_loaded', array($this, 'on_wp_loaded'));
        add_action('wp_head', array($this, 'add_meta_tags'));
        add_action('wp_footer', array($this, 'add_chatbot_widget'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // AJAX handlers
        add_action('wp_ajax_biic_chat_message', array($this, 'handle_chat_message'));
        add_action('wp_ajax_nopriv_biic_chat_message', array($this, 'handle_chat_message'));
        add_action('wp_ajax_biic_get_chat_history', array($this, 'handle_get_chat_history'));
        add_action('wp_ajax_nopriv_biic_get_chat_history', array($this, 'handle_get_chat_history'));
        
        // Cron jobs
        add_action('biic_daily_tasks', array($this, 'run_daily_tasks'));
        add_action('biic_hourly_tasks', array($this, 'run_hourly_tasks'));
        
        // Plugin lifecycle hooks
        register_activation_hook(BIIC_PLUGIN_FILE, array($this, 'on_activation'));
        register_deactivation_hook(BIIC_PLUGIN_FILE, array($this, 'on_deactivation'));
        
        // Shortcodes
        add_shortcode('biic_chatbot', array($this, 'chatbot_shortcode'));
        add_shortcode('biic_stats', array($this, 'stats_shortcode'));
        
        // Widget
        add_action('widgets_init', array($this, 'register_widgets'));
    }
    
    /**
     * Initialize admin features
     */
    private function init_admin() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_notices', array($this, 'show_admin_notices'));
        add_filter('plugin_action_links_' . BIIC_PLUGIN_BASENAME, array($this, 'add_action_links'));
        add_filter('plugin_row_meta', array($this, 'add_row_meta'), 10, 2);
    }
    
    /**
     * Initialize frontend features
     */
    private function init_frontend() {
        // Only load if chatbot is enabled
        if (!$this->get_setting('chatbot_enabled')) {
            return;
        }
        
        // Initialize user session
        add_action('wp', array($this, 'init_user_session'));
        
        // Add body classes
        add_filter('body_class', array($this, 'add_body_classes'));
        
        // Preconnect to external resources
        add_action('wp_head', array($this, 'add_resource_hints'));
    }
    
    /**
     * Initialize REST API
     */
    private function init_rest_api() {
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }
    
    /**
     * WordPress init hook
     */
    public function on_wp_init() {
        // Set timezone
        $timezone = $this->get_setting('timezone');
        if ($timezone) {
            date_default_timezone_set($timezone);
        }
        
        // Load text domain
        load_plugin_textdomain(
            'banglay-ielts-chatbot',
            false,
            dirname(BIIC_PLUGIN_BASENAME) . '/languages/'
        );
        
        // Initialize user tracking
        if ($this->user_tracking) {
            $this->user_tracking->init_tracking();
        }
        
        // Schedule cron jobs if not scheduled
        $this->schedule_cron_jobs();
    }
    
    /**
     * WordPress loaded hook
     */
    public function on_wp_loaded() {
        // Check for plugin updates
        $this->check_for_updates();
        
        // Initialize chatbot session
        if ($this->chatbot && !is_admin()) {
            $this->chatbot->init_session();
        }
    }
    
    /**
     * Add meta tags to head
     */
    public function add_meta_tags() {
        if (!$this->get_setting('chatbot_enabled')) {
            return;
        }
        
        echo '<meta name="biic-chatbot-enabled" content="true">' . "\n";
        echo '<meta name="biic-version" content="' . esc_attr($this->version) . '">' . "\n";
        
        // Add structured data for chatbot
        $structured_data = array(
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => 'Banglay IELTS Chatbot',
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web Browser'
        );
        
        echo '<script type="application/ld+json">' . json_encode($structured_data) . '</script>' . "\n";
    }
    
    /**
     * Add chatbot widget to footer
     */
    public function add_chatbot_widget() {
        if (!$this->get_setting('chatbot_enabled') || is_admin()) {
            return;
        }
        
        // Check business hours
        if (!$this->is_within_business_hours()) {
            return;
        }
        
        // Load widget template
        $this->load_template('chatbot-widget.php', array(
            'settings' => $this->settings,
            'session_id' => $this->chatbot ? $this->chatbot->get_session_id() : null
        ));
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        if (!$this->get_setting('chatbot_enabled') || is_admin()) {
            return;
        }
        
        // CSS
        wp_enqueue_style(
            'biic-chatbot-widget',
            BIIC_PLUGIN_ASSETS_URL . 'css/chatbot-widget.css',
            array(),
            $this->version
        );
        
        // Add custom CSS if available
        $custom_css = $this->get_setting('custom_css');
        if ($custom_css) {
            wp_add_inline_style('biic-chatbot-widget', $custom_css);
        }
        
        // JavaScript
        wp_enqueue_script(
            'biic-chatbot-widget',
            BIIC_PLUGIN_ASSETS_URL . 'js/chatbot-widget.js',
            array('jquery'),
            $this->version,
            true
        );
        
        // User tracking script
        if ($this->get_setting('analytics_enabled')) {
            wp_enqueue_script(
                'biic-user-tracking',
                BIIC_PLUGIN_ASSETS_URL . 'js/user-tracking.js',
                array('jquery'),
                $this->version,
                true
            );
        }
        
        // Localize scripts
        wp_localize_script('biic-chatbot-widget', 'biic_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('biic_chat_nonce'),
            'rest_url' => rest_url('biic/v1/'),
            'plugin_url' => BIIC_PLUGIN_URL,
            'settings' => array(
                'typing_speed' => $this->get_setting('typing_speed'),
                'enable_sounds' => $this->get_setting('enable_sounds'),
                'enable_animations' => $this->get_setting('enable_animations'),
                'position' => $this->get_setting('chat_position'),
                'theme' => $this->get_setting('chat_theme')
            ),
            'strings' => array(
                'typing' => __('বট টাইপ করছে...', 'banglay-ielts-chatbot'),
                'error' => __('দুঃখিত, একটি সমস্যা হয়েছে।', 'banglay-ielts-chatbot'),
                'offline' => __('আমরা এখন অফলাইনে আছি।', 'banglay-ielts-chatbot'),
                'send' => __('পাঠান', 'banglay-ielts-chatbot'),
                'placeholder' => __('আপনার প্রশ্ন লিখুন...', 'banglay-ielts-chatbot')
            )
        ));
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'biic') === false) {
            return;
        }
        
        // CSS
        wp_enqueue_style(
            'biic-admin-style',
            BIIC_PLUGIN_ASSETS_URL . 'css/admin-style.css',
            array(),
            $this->version
        );
        
        wp_enqueue_style(
            'biic-dashboard-style',
            BIIC_PLUGIN_ASSETS_URL . 'css/dashboard.css',
            array(),
            $this->version
        );
        
        // JavaScript
        wp_enqueue_script(
            'biic-admin-script',
            BIIC_PLUGIN_ASSETS_URL . 'js/admin-script.js',
            array('jquery', 'jquery-ui-datepicker'),
            $this->version,
            true
        );
        
        wp_enqueue_script(
            'biic-analytics',
            BIIC_PLUGIN_ASSETS_URL . 'js/analytics.js',
            array('jquery'),
            $this->version,
            true
        );
        
        // Chart.js for analytics
        wp_enqueue_script(
            'chart-js',
            'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js',
            array(),
            '3.9.1',
            true
        );
        
        // Localize admin script
        wp_localize_script('biic-admin-script', 'biic_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('biic_admin_nonce'),
            'rest_url' => rest_url('biic/v1/'),
            'plugin_version' => $this->version
        ));
    }
    
    /**
     * Handle chat message AJAX
     */
    public function handle_chat_message() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'biic_chat_nonce')) {
            wp_die(__('Security check failed', 'banglay-ielts-chatbot'));
        }
        
        $message = sanitize_text_field($_POST['message']);
        $session_id = sanitize_text_field($_POST['session_id']);
        
        if (empty($message)) {
            wp_send_json_error('Message cannot be empty');
        }
        
        // Rate limiting
        if (!$this->check_rate_limit($session_id)) {
            wp_send_json_error('Too many requests. Please wait.');
        }
        
        try {
            // Process message through chatbot
            $response = $this->chatbot->process_message($message, $session_id);
            
            // Track the interaction
            if ($this->user_tracking) {
                $this->user_tracking->track_message($session_id, $message, $response);
            }
            
            wp_send_json_success($response['data']);
            
        } catch (Exception $e) {
            $this->log_error('Chat message processing failed: ' . $e->getMessage());
            wp_send_json_error('Unable to process message');
        }
    }
    
    /**
     * Handle get chat history AJAX
     */
    public function handle_get_chat_history() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'biic_chat_nonce')) {
            wp_die(__('Security check failed', 'banglay-ielts-chatbot'));
        }
        
        $session_id = sanitize_text_field($_POST['session_id']);
        
        if (empty($session_id)) {
            wp_send_json_error('Session ID required');
        }
        
        try {
            $messages = $this->database->get_chat_messages($session_id);
            
            // Format messages for frontend
            $formatted_messages = array();
            foreach ($messages as $message) {
                $formatted_messages[] = array(
                    'type' => $message->message_type,
                    'content' => $message->content,
                    'timestamp' => $message->timestamp
                );
            }
            
            wp_send_json_success($formatted_messages);
            
        } catch (Exception $e) {
            $this->log_error('Chat history retrieval failed: ' . $e->getMessage());
            wp_send_json_error('Unable to retrieve chat history');
        }
    }
    
    /**
     * Initialize user session
     */
    public function init_user_session() {
        if ($this->chatbot) {
            $this->chatbot->init_session();
        }
    }
    
    /**
     * Add body classes
     */
    public function add_body_classes($classes) {
        if ($this->get_setting('chatbot_enabled')) {
            $classes[] = 'biic-chatbot-enabled';
            $classes[] = 'biic-theme-' . $this->get_setting('chat_theme');
            $classes[] = 'biic-position-' . str_replace('-', '_', $this->get_setting('chat_position'));
        }
        
        return $classes;
    }
    
    /**
     * Add resource hints
     */
    public function add_resource_hints() {
        // Preconnect to OpenAI API if configured
        if ($this->ai_integration && $this->ai_integration->is_ai_available()) {
            echo '<link rel="preconnect" href="https://api.openai.com">' . "\n";
        }
        
        // Preconnect to CDN
        echo '<link rel="preconnect" href="https://cdnjs.cloudflare.com">' . "\n";
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('biic/v1', '/chat', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_chat_endpoint'),
            'permission_callback' => '__return_true',
            'args' => array(
                'message' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'session_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));
        
        register_rest_route('biic/v1', '/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_stats_endpoint'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
    }
    
    /**
     * REST API chat endpoint
     */
    public function rest_chat_endpoint($request) {
        $message = $request->get_param('message');
        $session_id = $request->get_param('session_id');
        
        try {
            $response = $this->chatbot->process_message($message, $session_id);
            return rest_ensure_response($response);
        } catch (Exception $e) {
            return new WP_Error('chat_error', $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API stats endpoint
     */
    public function rest_stats_endpoint($request) {
        try {
            $stats = $this->analytics->get_dashboard_analytics();
            return rest_ensure_response($stats);
        } catch (Exception $e) {
            return new WP_Error('stats_error', $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Check admin permission for REST API
     */
    public function check_admin_permission() {
        return current_user_can('manage_options');
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Banglay IELTS Chatbot', 'banglay-ielts-chatbot'),
            __('BIIC Chatbot', 'banglay-ielts-chatbot'),
            'manage_options',
            'biic-dashboard',
            array($this, 'admin_dashboard_page'),
            'dashicons-format-chat',
            30
        );
    }
    
    /**
     * Admin dashboard page
     */
    public function admin_dashboard_page() {
        if (class_exists('BIIC_Admin')) {
            $admin = new BIIC_Admin();
            $admin->dashboard_page();
        }
    }
    
    /**
     * Add plugin action links
     */
    public function add_action_links($links) {
        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page=biic-dashboard') . '">' . __('Dashboard', 'banglay-ielts-chatbot') . '</a>',
            '<a href="' . admin_url('admin.php?page=biic-settings') . '">' . __('Settings', 'banglay-ielts-chatbot') . '</a>',
        );
        
        return array_merge($plugin_links, $links);
    }
    
    /**
     * Add plugin row meta
     */
    public function add_row_meta($links, $file) {
        if (BIIC_PLUGIN_BASENAME === $file) {
            $row_meta = array(
                'docs' => '<a href="https://banglayelts.com/docs" target="_blank">' . __('Documentation', 'banglay-ielts-chatbot') . '</a>',
                'support' => '<a href="https://banglayelts.com/support" target="_blank">' . __('Support', 'banglay-ielts-chatbot') . '</a>',
            );
            
            return array_merge($links, $row_meta);
        }
        
        return $links;
    }
    
    /**
     * Plugin activation
     */
    public function on_activation() {
        // Create database tables
        if ($this->database) {
            $this->database->create_tables();
        }
        
        // Set default options
        $this->set_default_options();
        
        // Schedule cron jobs
        $this->schedule_cron_jobs();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set activation timestamp
        update_option('biic_activated_at', current_time('mysql'));
        
        // Log activation
        error_log('Banglay IELTS Chatbot activated successfully');
    }
    
    /**
     * Plugin deactivation
     */
    public function on_deactivation() {
        // Clear scheduled events
        wp_clear_scheduled_hook('biic_daily_tasks');
        wp_clear_scheduled_hook('biic_hourly_tasks');
        wp_clear_scheduled_hook('biic_cleanup_old_sessions');
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Log deactivation
        error_log('Banglay IELTS Chatbot deactivated');
    }
    
    /**
     * Shortcode for chatbot
     */
    public function chatbot_shortcode($atts) {
        $atts = shortcode_atts(array(
            'theme' => $this->get_setting('chat_theme'),
            'position' => 'inline',
            'width' => '100%',
            'height' => '400px'
        ), $atts);
        
        ob_start();
        $this->load_template('chatbot-shortcode.php', array(
            'atts' => $atts,
            'settings' => $this->settings
        ));
        return ob_get_clean();
    }
    
    /**
     * Shortcode for stats
     */
    public function stats_shortcode($atts) {
        if (!current_user_can('manage_options')) {
            return '<p>' . __('Unauthorized access', 'banglay-ielts-chatbot') . '</p>';
        }
        
        $atts = shortcode_atts(array(
            'metric' => 'conversations',
            'period' => '30'
        ), $atts);
        
        // Get stats from analytics
        $stats = $this->analytics ? $this->analytics->get_dashboard_analytics() : array();
        
        ob_start();
        $this->load_template('stats-shortcode.php', array(
            'atts' => $atts,
            'stats' => $stats
        ));
        return ob_get_clean();
    }
    
    /**
     * Register widgets
     */
    public function register_widgets() {
        // Register chatbot widget if class exists
        if (class_exists('BIIC_Chatbot_Widget')) {
            register_widget('BIIC_Chatbot_Widget');
        }
    }
    
    /**
     * Utility methods
     */
    
    /**
     * Get plugin setting
     */
    public function get_setting($key, $default = null) {
        return isset($this->settings[$key]) ? $this->settings[$key] : $default;
    }
    
    /**
     * Update plugin setting
     */
    public function update_setting($key, $value) {
        $this->settings[$key] = $value;
        update_option('biic_' . $key, $value);
    }
    
    /**
     * Check if within business hours
     */
    private function is_within_business_hours() {
        $business_hours = $this->get_setting('business_hours');
        
        if (!$business_hours) {
            return true; // Always available if not set
        }
        
        $current_day = strtolower(date('l'));
        $current_time = date('H:i');
        
        // Check if current day is in business days
        if (!in_array($current_day, $business_hours['days'])) {
            return false;
        }
        
        // Check if current time is within business hours
        return ($current_time >= $business_hours['start'] && $current_time <= $business_hours['end']);
    }
    
    /**
     * Rate limiting
     */
    private function check_rate_limit($session_id) {
        $rate_limit = $this->get_setting('api_rate_limit', 60);
        $cache_key = 'biic_rate_limit_' . md5($session_id);
        
        $requests = get_transient($cache_key);
        if ($requests === false) {
            set_transient($cache_key, 1, MINUTE_IN_SECONDS);
            return true;
        }
        
        if ($requests >= $rate_limit) {
            return false;
        }
        
        set_transient($cache_key, $requests + 1, MINUTE_IN_SECONDS);
        return true;
    }
    
    /**
     * Load template file
     */
    private function load_template($template, $vars = array()) {
        $template_path = BIIC_PLUGIN_PATH . 'frontend/templates/' . $template;
        
        if (file_exists($template_path)) {
            extract($vars);
            include $template_path;
        }
    }
    
    /**
     * Schedule cron jobs
     */
    private function schedule_cron_jobs() {
        if (!wp_next_scheduled('biic_daily_tasks')) {
            wp_schedule_event(time(), 'daily', 'biic_daily_tasks');
        }
        
        if (!wp_next_scheduled('biic_hourly_tasks')) {
            wp_schedule_event(time(), 'hourly', 'biic_hourly_tasks');
        }
    }
    
    /**
     * Run daily tasks
     */
    public function run_daily_tasks() {
        // Update lead scores
        if ($this->lead_management) {
            $this->lead_management->update_all_lead_scores();
        }
        
        // Process analytics
        if ($this->analytics) {
            $this->analytics->process_daily_analytics();
        }
        
        // Cleanup old sessions
        if ($this->user_tracking) {
            $this->user_tracking->cleanup_old_sessions();
        }
    }
    
    /**
     * Run hourly tasks
     */
    public function run_hourly_tasks() {
        // Process follow-ups
        if ($this->lead_management) {
            $this->lead_management->process_scheduled_follow_ups();
        }
    }
    
    /**
     * Set default options
     */
    private function set_default_options() {
        foreach ($this->settings as $key => $value) {
            add_option('biic_' . $key, $value);
        }
    }
    
    /**
     * Check system requirements
     */
    public function check_system_requirements() {
        $requirements = array(
            'php_version' => '7.4',
            'wp_version' => '5.0',
            'extensions' => array('curl', 'json', 'mbstring')
        );
        
        // Check PHP version
        if (version_compare(PHP_VERSION, $requirements['php_version'], '<')) {
            $this->add_error('PHP version ' . $requirements['php_version'] . ' or higher is required.');
        }
        
        // Check WordPress version
        global $wp_version;
        if (version_compare($wp_version, $requirements['wp_version'], '<')) {
            $this->add_error('WordPress version ' . $requirements['wp_version'] . ' or higher is required.');
        }
        
        // Check extensions
        foreach ($requirements['extensions'] as $extension) {
            if (!extension_loaded($extension)) {
                $this->add_error('PHP extension "' . $extension . '" is required.');
            }
        }
    }
    
    /**
     * Check for plugin updates
     */
    private function check_for_updates() {
        $last_check = get_option('biic_last_update_check', 0);
        
        if ((time() - $last_check) > DAY_IN_SECONDS) {
            // Check for updates (placeholder for future update server)
            update_option('biic_last_update_check', time());
        }
    }
    
    /**
     * Error handling
     */
    
    /**
     * PHP error handler
     */
    public function handle_php_error($severity, $message, $file, $line) {
        if (strpos($file, 'banglay-ielts-chatbot') !== false) {
            $this->log_error("PHP Error: $message in $file on line $line");
        }
    }
    
    /**
     * AJAX error handler
     */
    public function handle_ajax_error() {
        if (!wp_verify_nonce($_POST['nonce'], 'biic_chat_nonce')) {
            wp_die();
        }
        
        $error = sanitize_text_field($_POST['error']);
        $context = sanitize_text_field($_POST['context']);
        
        $this->log_error("Frontend Error: $error (Context: $context)");
        
        wp_send_json_success();
    }
    
    /**
     * Log error
     */
    private function log_error($message) {
        $this->errors[] = $message;
        error_log('BIIC Plugin Error: ' . $message);
    }
    
    /**
     * Add error message
     */
    private function add_error($message) {
        $this->errors[] = $message;
    }
    
    /**
     * Show admin notices
     */
    public function show_admin_notices() {
        foreach ($this->errors as $error) {
            echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
        }
    }
    
    /**
     * Show initialization error
     */
    public function show_initialization_error() {
        echo '<div class="notice notice-error"><p>';
        echo __('Banglay IELTS Chatbot failed to initialize properly. Please check the error log.', 'banglay-ielts-chatbot');
        echo '</p></div>';
    }
    
    /**
     * Get plugin info
     */
    public function get_plugin_info() {
        return array(
            'version' => $this->version,
            'name' => 'Banglay IELTS Chatbot',
            'author' => 'Mr Rocks',
            'url' => 'https://banglayelts.com',
            'components' => array(
                'database' => is_object($this->database),
                'chatbot' => is_object($this->chatbot),
                'ai_integration' => is_object($this->ai_integration),
                'user_tracking' => is_object($this->user_tracking),
                'lead_management' => is_object($this->lead_management),
                'analytics' => is_object($this->analytics)
            ),
            'settings' => $this->settings
        );
    }
    
    /**
     * Debug information
     */
    public function get_debug_info() {
        return array(
            'plugin_info' => $this->get_plugin_info(),
            'system_info' => array(
                'php_version' => PHP_VERSION,
                'wp_version' => get_bloginfo('version'),
                'extensions' => get_loaded_extensions(),
                'memory_limit' => ini_get('memory_limit'),
                'time_limit' => ini_get('max_execution_time')
            ),
            'database_stats' => $this->database ? $this->database->get_database_stats() : array(),
            'errors' => $this->errors
        );
    }
}