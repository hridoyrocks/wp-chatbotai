<?php
/**
 * Plugin Name: Banglay IELTS - AI Chatbot
 * Plugin URI: https://banglayelts.com
 * Description: Our Newly Chatbot - RX
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
 * Check system requirements before activation
 */
function biic_check_requirements() {
    $errors = array();
    
    // Check PHP version
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        $errors[] = 'PHP version 7.4 or higher is required. You are running ' . PHP_VERSION;
    }
    
    // Check WordPress version
    global $wp_version;
    if (version_compare($wp_version, '5.0', '<')) {
        $errors[] = 'WordPress version 5.0 or higher is required. You are running ' . $wp_version;
    }
    
    // Check required PHP extensions
    $required_extensions = array('curl', 'json', 'mbstring');
    foreach ($required_extensions as $extension) {
        if (!extension_loaded($extension)) {
            $errors[] = "PHP extension '{$extension}' is required but not installed.";
        }
    }
    
    return $errors;
}

/**
 * Plugin activation hook
 */
function biic_activate_plugin() {
    error_log('BIIC: Starting plugin activation...');
    
    // Check requirements first
    $errors = biic_check_requirements();
    if (!empty($errors)) {
        $error_message = 'Plugin activation failed due to system requirements:' . PHP_EOL . implode(PHP_EOL, $errors);
        error_log('BIIC: ' . $error_message);
        wp_die($error_message, 'Plugin Activation Error', array('back_link' => true));
        return;
    }
    
    try {
        // Initialize the plugin instance
        $plugin = BanglayIELTSChatbot::getInstance();
        
        // Run activation process
        $plugin->activate();
        
        error_log('BIIC: Plugin activated successfully');
        
    } catch (Exception $e) {
        error_log('BIIC: Activation failed - ' . $e->getMessage());
        wp_die('Plugin activation failed: ' . $e->getMessage(), 'Plugin Activation Error', array('back_link' => true));
    }
}

/**
 * Plugin deactivation hook
 */
function biic_deactivate_plugin() {
    try {
        $plugin = BanglayIELTSChatbot::getInstance();
        $plugin->deactivate();
        error_log('BIIC: Plugin deactivated successfully');
    } catch (Exception $e) {
        error_log('BIIC: Deactivation error - ' . $e->getMessage());
    }
}

// Register activation/deactivation hooks
register_activation_hook(__FILE__, 'biic_activate_plugin');
register_deactivation_hook(__FILE__, 'biic_deactivate_plugin');

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
     * Initialization errors
     */
    private $init_errors = array();
    
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
        // Don't initialize during activation to avoid conflicts
        if (!defined('WP_INSTALLING') || !WP_INSTALLING) {
            add_action('plugins_loaded', array($this, 'init'), 10);
        }
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        try {
            // Load text domain
            $this->load_textdomain();
            
            // Include required files
            $this->includes();
            
            // Initialize components
            $this->init_components();
            
            // Setup hooks
            $this->setup_hooks();
            
        } catch (Exception $e) {
            $this->init_errors[] = $e->getMessage();
            error_log('BIIC: Initialization error - ' . $e->getMessage());
            add_action('admin_notices', array($this, 'show_init_errors'));
        }
    }
    
    /**
     * Show initialization errors
     */
    public function show_init_errors() {
        if (!empty($this->init_errors)) {
            foreach ($this->init_errors as $error) {
                echo '<div class="notice notice-error"><p><strong>Banglay IELTS Chatbot:</strong> ' . esc_html($error) . '</p></div>';
            }
        }
    }
    
    /**
     * Include required files
     */
    private function includes() {
        $required_files = array(
            BIIC_INCLUDES_PATH . 'class-database.php',
            BIIC_INCLUDES_PATH . 'class-chatbot.php',
            BIIC_INCLUDES_PATH . 'class-ai-integration.php',
            BIIC_INCLUDES_PATH . 'class-user-tracking.php',
            BIIC_INCLUDES_PATH . 'class-lead-management.php',
            BIIC_INCLUDES_PATH . 'class-analytics.php',
            BIIC_API_PATH . 'class-api.php'
        );
        
        foreach ($required_files as $file) {
            if (!file_exists($file)) {
                throw new Exception("Required file missing: " . basename($file));
            }
            require_once $file;
        }
        
        // Admin classes
        if (is_admin() && file_exists(BIIC_ADMIN_PATH . 'class-admin.php')) {
            require_once BIIC_ADMIN_PATH . 'class-admin.php';
        }
        
        // Frontend classes
        if (!is_admin() && file_exists(BIIC_FRONTEND_PATH . 'class-frontend.php')) {
            require_once BIIC_FRONTEND_PATH . 'class-frontend.php';
        }
    }
    
    /**
     * Initialize components
     */
    private function init_components() {
        try {
            // Initialize database first
            if (class_exists('BIIC_Database')) {
                $this->database = new BIIC_Database();
            }
            
            // Initialize other components only if their classes exist
            if (class_exists('BIIC_Chatbot')) {
                $this->chatbot = new BIIC_Chatbot();
            }
            
            if (class_exists('BIIC_AI_Integration')) {
                $this->ai_integration = new BIIC_AI_Integration();
            }
            
            if (class_exists('BIIC_User_Tracking')) {
                $this->user_tracking = new BIIC_User_Tracking();
            }
            
            if (class_exists('BIIC_Lead_Management')) {
                $this->lead_management = new BIIC_Lead_Management();
            }
            
            if (class_exists('BIIC_Analytics')) {
                $this->analytics = new BIIC_Analytics();
            }
            
            if (class_exists('BIIC_API')) {
                $this->api = new BIIC_API();
            }
            
            // Initialize admin (if in admin)
            if (is_admin() && class_exists('BIIC_Admin')) {
                $this->admin = new BIIC_Admin();
            }
            
            // Initialize frontend (if not in admin)
            if (!is_admin() && class_exists('BIIC_Frontend')) {
                $this->frontend = new BIIC_Frontend();
            }
            
        } catch (Exception $e) {
            throw new Exception('Component initialization failed: ' . $e->getMessage());
        }
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
        if ($this->api) {
            add_action('rest_api_init', array($this->api, 'register_routes'));
        }
        
        // Add chatbot to footer
        add_action('wp_footer', array($this, 'add_chatbot_widget'));
        
        // Custom admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Plugin action links
        add_filter('plugin_action_links_' . BIIC_PLUGIN_BASENAME, array($this, 'add_action_links'));
    }
    
    // ... [Rest of your existing methods remain the same] ...
    
    /**
     * Plugin activation - IMPROVED VERSION
     */
    public function activate() {
        error_log('BIIC: Running activation process...');
        
        try {
            // Create database tables first
            if ($this->database) {
                error_log('BIIC: Creating database tables...');
                $this->database->create_tables();
                error_log('BIIC: Database tables created successfully');
            } else {
                throw new Exception('Database component not initialized');
            }
            
            // Set default options
            error_log('BIIC: Setting default options...');
            $this->set_default_options();
            
            // Create upload directory
            error_log('BIIC: Creating upload directory...');
            $this->create_upload_directory();
            
            // Flush rewrite rules
            flush_rewrite_rules();
            
            // Set activation flag
            update_option('biic_plugin_activated', true);
            update_option('biic_activation_time', current_time('mysql'));
            
            error_log('BIIC: Activation completed successfully');
            
        } catch (Exception $e) {
            error_log('BIIC: Activation failed - ' . $e->getMessage());
            throw $e;
        }
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
        if ($this->user_tracking) {
            $this->user_tracking->init_tracking();
        }
        
        // Initialize chatbot session
        if ($this->chatbot) {
            $this->chatbot->init_session();
        }
    }
    
    /**
     * Set default options - SAFE VERSION
     */
    private function set_default_options() {
        $default_options = array(
            'biic_chatbot_enabled' => true,
            'biic_chat_position' => 'bottom-right',
            'biic_chat_theme' => 'modern',
            'biic_welcome_message' => 'আস্সালামু আলাইকুম! IELTS এর ব্যাপারে কিছু জানতে চান?',
            'biic_auto_responses' => true,
            'biic_lead_notifications' => true,
            'biic_analytics_enabled' => true,
            'biic_timezone' => 'Asia/Dhaka'
        );
        
        foreach ($default_options as $option_name => $default_value) {
            if (get_option($option_name) === false) {
                add_option($option_name, $default_value);
            }
        }
    }
    
    /**
     * Create upload directory - SAFE VERSION
     */
    private function create_upload_directory() {
        $upload_dir = wp_upload_dir();
        
        if ($upload_dir['error']) {
            throw new Exception('WordPress upload directory error: ' . $upload_dir['error']);
        }
        
        $biic_dir = $upload_dir['basedir'] . '/biic-chatbot/';
        
        if (!file_exists($biic_dir)) {
            $created = wp_mkdir_p($biic_dir);
            if (!$created) {
                throw new Exception('Failed to create upload directory: ' . $biic_dir);
            }
            
            // Create .htaccess for security
            $htaccess_content = "Options -Indexes\nDeny from all";
            $htaccess_result = file_put_contents($biic_dir . '.htaccess', $htaccess_content);
            
            if ($htaccess_result === false) {
                error_log('BIIC: Warning - Could not create .htaccess file in upload directory');
            }
        }
    }
    
    // Add all your other existing methods here...
    // (enqueue_frontend_assets, handle_chat_message, add_chatbot_widget, etc.)
    
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
add_action('plugins_loaded', 'biic_init', 5);

/**
 * Helper function to get plugin instance
 */
function BIIC() {
    return BanglayIELTSChatbot::get_instance();
}