<?php
/**
 * Plugin Name: Banglay IELTS - AI Chatbot
 * Plugin URI: https://banglayelts.com
 * Description: Our Newly Chatbot
 * Version: 1.0.0
 * Author: Mr Rocks
 * Author URI: https://banglayelts.com
 * License: GPL v2 or later
 * Text Domain: banglay-ielts-chatbot
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

// Define plugin constants
define('BIIC_VERSION', '1.0.0');
define('BIIC_PLUGIN_FILE', __FILE__);
define('BIIC_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('BIIC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('BIIC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BIIC_PLUGIN_ASSETS_URL', BIIC_PLUGIN_URL . 'assets/');
define('BIIC_INCLUDES_PATH', BIIC_PLUGIN_PATH . 'includes/');
define('BIIC_ADMIN_PATH', BIIC_PLUGIN_PATH . 'admin/');
define('BIIC_FRONTEND_PATH', BIIC_PLUGIN_PATH . 'frontend/');
define('BIIC_API_PATH', BIIC_PLUGIN_PATH . 'api/');

/**
 * Main Plugin Class
 */
class BanglayIELTSChatbot {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Plugin components
     */
    public $database;
    public $admin;
    public $frontend;
    public $chatbot;
    public $ai_integration;
    public $user_tracking;
    public $lead_management;
    public $analytics;
    public $api;
    
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
     * Initialize plugin
     */
    private function init() {
        // Load text domain
        add_action('init', array($this, 'load_textdomain'));
        
        // Include required files
        $this->includes();
        
        // Initialize components
        $this->init_components();
        
        // Setup hooks
        $this->setup_hooks();
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        register_uninstall_hook(__FILE__, array('BanglayIELTSChatbot', 'uninstall'));
    }
    
    /**
     * Include required files
     */
    private function includes() {
        // Core classes
        require_once BIIC_INCLUDES_PATH . 'class-database.php';
        require_once BIIC_INCLUDES_PATH . 'class-chatbot.php';
        require_once BIIC_INCLUDES_PATH . 'class-ai-integration.php';
        require_once BIIC_INCLUDES_PATH . 'class-user-tracking.php';
        require_once BIIC_INCLUDES_PATH . 'class-lead-management.php';
        require_once BIIC_INCLUDES_PATH . 'class-analytics.php';
        
        // Admin classes
        if (is_admin()) {
            require_once BIIC_ADMIN_PATH . 'class-admin.php';
        }
        
        // Frontend classes
        if (!is_admin()) {
            require_once BIIC_FRONTEND_PATH . 'class-frontend.php';
        }
        
        // API classes
        require_once BIIC_API_PATH . 'class-api.php';
    }
    
    /**
     * Initialize components
     */
    private function init_components() {
        // Initialize database
        $this->database = new BIIC_Database();
        
        // Initialize chatbot core
        $this->chatbot = new BIIC_Chatbot();
        
        // Initialize AI integration
        $this->ai_integration = new BIIC_AI_Integration();
        
        // Initialize user tracking
        $this->user_tracking = new BIIC_User_Tracking();
        
        // Initialize lead management
        $this->lead_management = new BIIC_Lead_Management();
        
        // Initialize analytics
        $this->analytics = new BIIC_Analytics();
        
        // Initialize admin (if in admin)
        if (is_admin()) {
            $this->admin = new BIIC_Admin();
        }
        
        // Initialize frontend (if not in admin)
        if (!is_admin()) {
            $this->frontend = new BIIC_Frontend();
        }
        
        // Initialize API
        $this->api = new BIIC_API();
    }
    
    /**
     * Setup WordPress hooks
     */
    private function setup_hooks() {
        // WordPress init
        add_action('init', array($this, 'init_plugin'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // AJAX hooks
        add_action('wp_ajax_biic_chat_message', array($this, 'handle_chat_message'));
        add_action('wp_ajax_nopriv_biic_chat_message', array($this, 'handle_chat_message'));
        
        // REST API initialization
        add_action('rest_api_init', array($this->api, 'register_routes'));
        
        // Add chatbot to footer
        add_action('wp_footer', array($this, 'add_chatbot_widget'));
        
        // Custom admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Plugin action links
        add_filter('plugin_action_links_' . BIIC_PLUGIN_BASENAME, array($this, 'add_action_links'));
    }
    
    /**
     * Load text domain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'banglay-ielts-chatbot',
            false,
            dirname(BIIC_PLUGIN_BASENAME) . '/languages/'
        );
    }
    
    /**
     * Initialize plugin after WordPress loads
     */
    public function init_plugin() {
        // Set timezone
        if (!get_option('biic_timezone')) {
            update_option('biic_timezone', 'Asia/Dhaka');
        }
        
        // Initialize tracking
        $this->user_tracking->init_tracking();
        
        // Initialize chatbot session
        $this->chatbot->init_session();
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Only load on frontend
        if (is_admin()) return;
        
        // CSS
        wp_enqueue_style(
            'biic-chatbot-widget',
            BIIC_PLUGIN_ASSETS_URL . 'css/chatbot-widget.css',
            array(),
            BIIC_VERSION
        );
        
        // JavaScript
        wp_enqueue_script(
            'biic-chatbot-widget',
            BIIC_PLUGIN_ASSETS_URL . 'js/chatbot-widget.js',
            array('jquery'),
            BIIC_VERSION,
            true
        );
        
        // User tracking script
        wp_enqueue_script(
            'biic-user-tracking',
            BIIC_PLUGIN_ASSETS_URL . 'js/user-tracking.js',
            array('jquery'),
            BIIC_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('biic-chatbot-widget', 'biic_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('biic_chat_nonce'),
            'rest_url' => rest_url('biic/v1/'),
            'plugin_url' => BIIC_PLUGIN_URL,
            'strings' => array(
                'typing' => __('বট টাইপ করছে...', 'banglay-ielts-chatbot'),
                'error' => __('দুঃখিত, একটি সমস্যা হয়েছে।', 'banglay-ielts-chatbot'),
                'offline' => __('আমরা এখন অফলাইনে আছি।', 'banglay-ielts-chatbot'),
                'copyright' => __('Made with Love Rocks', 'banglay-ielts-chatbot')
            )
        ));
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'biic') === false) return;
        
        // CSS
        wp_enqueue_style(
            'biic-admin-style',
            BIIC_PLUGIN_ASSETS_URL . 'css/admin-style.css',
            array(),
            BIIC_VERSION
        );
        
        wp_enqueue_style(
            'biic-dashboard-style',
            BIIC_PLUGIN_ASSETS_URL . 'css/dashboard.css',
            array(),
            BIIC_VERSION
        );
        
        // JavaScript
        wp_enqueue_script(
            'biic-admin-script',
            BIIC_PLUGIN_ASSETS_URL . 'js/admin-script.js',
            array('jquery', 'jquery-ui-datepicker'),
            BIIC_VERSION,
            true
        );
        
        wp_enqueue_script(
            'biic-analytics',
            BIIC_PLUGIN_ASSETS_URL . 'js/analytics.js',
            array('jquery'),
            BIIC_VERSION,
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
        ));
    }
    
    /**
     * Handle AJAX chat messages
     */
    public function handle_chat_message() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'biic_chat_nonce')) {
            wp_die(__('Security check failed', 'banglay-ielts-chatbot'));
        }
        
        $message = sanitize_text_field($_POST['message']);
        $session_id = sanitize_text_field($_POST['session_id']);
        
        // Process message through chatbot
        $response = $this->chatbot->process_message($message, $session_id);
        
        // Track interaction
        $this->user_tracking->track_message($session_id, $message, $response);
        
        wp_send_json_success($response);
    }
    
    /**
     * Add chatbot widget to footer
     */
    public function add_chatbot_widget() {
        if (is_admin()) return;
        
        // Check if chatbot is enabled
        if (!get_option('biic_chatbot_enabled', true)) return;
        
        include BIIC_FRONTEND_PATH . 'templates/chatbot-widget.php';
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Banglay IELTS Chatbot', 'banglay-ielts-chatbot'),
            __('BIIC Chatbot', 'banglay-ielts-chatbot'),
            'manage_options',
            'biic-dashboard',
            array($this->admin, 'dashboard_page'),
            'dashicons-format-chat',
            30
        );
        
        // Submenu pages
        add_submenu_page(
            'biic-dashboard',
            __('Dashboard', 'banglay-ielts-chatbot'),
            __('Dashboard', 'banglay-ielts-chatbot'),
            'manage_options',
            'biic-dashboard',
            array($this->admin, 'dashboard_page')
        );
        
        add_submenu_page(
            'biic-dashboard',
            __('Conversations', 'banglay-ielts-chatbot'),
            __('Conversations', 'banglay-ielts-chatbot'),
            'manage_options',
            'biic-conversations',
            array($this->admin, 'conversations_page')
        );
        
        add_submenu_page(
            'biic-dashboard',
            __('Leads', 'banglay-ielts-chatbot'),
            __('Leads', 'banglay-ielts-chatbot'),
            'manage_options',
            'biic-leads',
            array($this->admin, 'leads_page')
        );
        
        add_submenu_page(
            'biic-dashboard',
            __('Analytics', 'banglay-ielts-chatbot'),
            __('Analytics', 'banglay-ielts-chatbot'),
            'manage_options',
            'biic-analytics',
            array($this->admin, 'analytics_page')
        );
        
        add_submenu_page(
            'biic-dashboard',
            __('Settings', 'banglay-ielts-chatbot'),
            __('Settings', 'banglay-ielts-chatbot'),
            'manage_options',
            'biic-settings',
            array($this->admin, 'settings_page')
        );
    }
    
    /**
     * Add action links to plugin page
     */
    public function add_action_links($links) {
        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page=biic-settings') . '">' . __('Settings', 'banglay-ielts-chatbot') . '</a>',
            '<a href="' . admin_url('admin.php?page=biic-dashboard') . '">' . __('Dashboard', 'banglay-ielts-chatbot') . '</a>',
        );
        
        return array_merge($plugin_links, $links);
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        $this->database->create_tables();
        
        // Set default options
        $this->set_default_options();
        
        // Create upload directory
        $this->create_upload_directory();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Log activation
        error_log('Banglay IELTS Chatbot activated successfully');
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('biic_cleanup_old_sessions');
        wp_clear_scheduled_hook('biic_lead_follow_up');
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Log deactivation
        error_log('Banglay IELTS Chatbot deactivated');
    }
    
    /**
     * Plugin uninstall
     */
    public static function uninstall() {
        // Remove all plugin data if option is set
        if (get_option('biic_remove_data_on_uninstall', false)) {
            // Drop tables
            global $wpdb;
            $tables = array(
                $wpdb->prefix . 'biic_chat_sessions',
                $wpdb->prefix . 'biic_chat_messages', 
                $wpdb->prefix . 'biic_user_interactions',
                $wpdb->prefix . 'biic_leads',
                $wpdb->prefix . 'biic_analytics'
            );
            
            foreach ($tables as $table) {
                $wpdb->query("DROP TABLE IF EXISTS $table");
            }
            
            // Remove options
            delete_option('biic_chatbot_enabled');
            delete_option('biic_openai_api_key');
            delete_option('biic_timezone');
            delete_option('biic_remove_data_on_uninstall');
        }
    }
    
    /**
     * Set default options
     */
    private function set_default_options() {
        // Default settings
        add_option('biic_chatbot_enabled', true);
        add_option('biic_chat_position', 'bottom-right');
        add_option('biic_chat_theme', 'modern');
        add_option('biic_welcome_message', 'আস্সালামু আলাইকুম! IELTS এর ব্যাপারে কিছু জানতে চান?');
        add_option('biic_business_hours', array(
            'start' => '10:00',
            'end' => '18:00',
            'days' => array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday')
        ));
        add_option('biic_auto_responses', true);
        add_option('biic_lead_notifications', true);
        add_option('biic_analytics_enabled', true);
    }
    
    /**
     * Create upload directory
     */
    private function create_upload_directory() {
        $upload_dir = wp_upload_dir();
        $biic_dir = $upload_dir['basedir'] . '/biic-chatbot/';
        
        if (!file_exists($biic_dir)) {
            wp_mkdir_p($biic_dir);
            
            // Create .htaccess for security
            $htaccess_content = "Options -Indexes\nDeny from all";
            file_put_contents($biic_dir . '.htaccess', $htaccess_content);
        }
    }
    
    /**
     * Get plugin instance
     */
    public static function get_instance() {
        return self::getInstance();
    }
}

/**
 * Initialize the plugin
 */
function biic_init() {
    return BanglayIELTSChatbot::getInstance();
}

// Start the plugin
biic_init();

/**
 * Helper function to get plugin instance
 */
function BIIC() {
    return BanglayIELTSChatbot::get_instance();
}