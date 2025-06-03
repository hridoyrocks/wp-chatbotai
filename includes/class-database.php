<?php
/**
 * Database
 * 
 * @package BanglayIELTSChatbot
 * @subpackage Database
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BIIC_Database {
    
    /**
     * Database version
     */
    private $db_version = '1.0.0';
    
    /**
     * WordPress database object
     */
    private $wpdb;
    
    /**
     * Table names
     */
    private $tables = array();
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        
        // Define table names
        $this->tables = array(
            'chat_sessions' => $wpdb->prefix . 'biic_chat_sessions',
            'chat_messages' => $wpdb->prefix . 'biic_chat_messages',
            'user_interactions' => $wpdb->prefix . 'biic_user_interactions',
            'leads' => $wpdb->prefix . 'biic_leads',
            'analytics' => $wpdb->prefix . 'biic_analytics'
        );
    }
    
    /**
     * Create all database tables
     */
    public function create_tables() {
        error_log('BIIC Database: Starting table creation...');
        
        // Require WordPress upgrade functions
        if (!function_exists('dbDelta')) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        }
        
        try {
            $this->create_chat_sessions_table();
            $this->create_chat_messages_table();
            $this->create_user_interactions_table();
            $this->create_leads_table();
            $this->create_analytics_table();
            
            // Update database version
            update_option('biic_db_version', $this->db_version);
            error_log('BIIC Database: All tables created successfully');
            
        } catch (Exception $e) {
            error_log('BIIC Database: Table creation failed - ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create chat sessions table - FIXED VERSION
     */
    private function create_chat_sessions_table() {
        $table_name = $this->tables['chat_sessions'];
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id varchar(255) NOT NULL,
            user_id bigint(20) NULL,
            ip_address varchar(45) NOT NULL,
            user_agent text NOT NULL,
            location varchar(255) NULL,
            country varchar(100) NULL,
            city varchar(100) NULL,
            device_type varchar(50) NULL,
            browser varchar(100) NULL,
            referrer text NULL,
            page_url varchar(500) NULL,
            started_at datetime NOT NULL,
            ended_at datetime NULL,
            last_activity datetime NOT NULL,
            total_messages int(11) DEFAULT 0,
            conversation_duration int(11) DEFAULT 0,
            lead_score int(11) DEFAULT 0,
            lead_status enum('hot','warm','cold','converted') DEFAULT 'cold',
            is_active tinyint(1) DEFAULT 1,
            utm_source varchar(100) NULL,
            utm_medium varchar(100) NULL,
            utm_campaign varchar(100) NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY session_id (session_id),
            KEY user_id (user_id),
            KEY started_at (started_at),
            KEY lead_status (lead_status),
            KEY is_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $result = dbDelta($sql);
        error_log('BIIC Database: Chat sessions table creation result: ' . print_r($result, true));
    }
    
    /**
     * Create chat messages table - FIXED VERSION (removed foreign key)
     */
    private function create_chat_messages_table() {
        $table_name = $this->tables['chat_messages'];
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id varchar(255) NOT NULL,
            message_type enum('user','bot','system') NOT NULL,
            content text NOT NULL,
            content_type varchar(50) DEFAULT 'text',
            detected_intent varchar(100) NULL,
            intent_confidence decimal(5,4) NULL,
            sentiment varchar(20) NULL,
            sentiment_score decimal(5,4) NULL,
            response_time decimal(8,3) NULL,
            is_helpful tinyint(1) NULL,
            feedback_score int(11) NULL,
            metadata json NULL,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY message_type (message_type),
            KEY detected_intent (detected_intent),
            KEY timestamp (timestamp)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $result = dbDelta($sql);
        error_log('BIIC Database: Chat messages table creation result: ' . print_r($result, true));
    }
            
            // User Interactions Table
            $sql_interactions = "CREATE TABLE {$this->table_prefix}user_interactions (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                session_id varchar(255) NOT NULL,
                interaction_type varchar(100) NOT NULL,
                interaction_data json NULL,
                page_url varchar(500) NULL,
                element_id varchar(100) NULL,
                element_text varchar(255) NULL,
                scroll_depth int(11) NULL,
                time_on_page int(11) NULL,
                timestamp datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY session_id (session_id),
                KEY interaction_type (interaction_type),
                KEY timestamp (timestamp)
            ) $charset_collate;";
            
            // Leads Table
            $sql_leads = "CREATE TABLE {$this->table_prefix}leads (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                session_id varchar(255) NOT NULL,
                name varchar(255) NULL,
                email varchar(255) NULL,
                phone varchar(20) NULL,
                course_interest varchar(100) NULL,
                lead_score int(11) DEFAULT 0,
                lead_status enum('new','contacted','qualified','converted','lost','nurturing') DEFAULT 'new',
                lead_source varchar(100) NULL,
                priority enum('high','medium','low') DEFAULT 'medium',
                assigned_to bigint(20) NULL,
                follow_up_date date NULL,
                follow_up_notes text NULL,
                last_contact_date datetime NULL,
                conversion_date datetime NULL,
                conversion_value decimal(10,2) NULL,
                notes text NULL,
                tags varchar(500) NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY session_id (session_id),
                KEY email (email),
                KEY phone (phone),
                KEY lead_status (lead_status),
                KEY lead_score (lead_score),
                KEY course_interest (course_interest),
                KEY created_at (created_at)
            ) $charset_collate;";
            
            // Analytics Table
            $sql_analytics = "CREATE TABLE {$this->table_prefix}analytics (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                date date NOT NULL,
                metric_name varchar(100) NOT NULL,
                metric_value decimal(15,4) NOT NULL,
                metric_type varchar(50) NOT NULL,
                dimensions json NULL,
                period_type enum('hourly','daily','weekly','monthly') DEFAULT 'daily',
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY unique_date_metric (date, metric_name, period_type),
                KEY metric_name (metric_name),
                KEY date (date)
            ) $charset_collate;";
            
            // Execute table creation
            $tables = array(
                'chat_sessions' => $sql_sessions,
                'chat_messages' => $sql_messages,
                'user_interactions' => $sql_interactions,
                'leads' => $sql_leads,
                'analytics' => $sql_analytics
            );
            
            $created_tables = array();
            $errors = array();
            
            foreach ($tables as $table_name => $sql) {
                $result = dbDelta($sql);
                
                // Check if table was created successfully
                $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_prefix}{$table_name}'");
                
                if ($table_exists) {
                    $created_tables[] = $table_name;
                    error_log("BIIC: Successfully created table {$this->table_prefix}{$table_name}");
                } else {
                    $errors[] = "Failed to create table {$this->table_prefix}{$table_name}";
                    error_log("BIIC: Failed to create table {$this->table_prefix}{$table_name}");
                }
            }
            
            // Insert initial data
            $this->insert_initial_data();
            
            // Update database version
            update_option('biic_db_version', $this->db_version);
            
            return array(
                'success' => empty($errors),
                'created_tables' => $created_tables,
                'errors' => $errors
            );
            
        } catch (Exception $e) {
            error_log('BIIC Database Creation Error: ' . $e->getMessage());
            return array(
                'success' => false,
                'errors' => array($e->getMessage())
            );
        }
    }
    
    /**
     * Insert initial data
     */
    private function insert_initial_data() {
        global $wpdb;
        
        try {
            // Insert sample analytics data
            $current_date = current_time('Y-m-d');
            $analytics_data = array(
                array(
                    'date' => $current_date,
                    'metric_name' => 'daily_conversations',
                    'metric_value' => 0,
                    'metric_type' => 'counter'
                ),
                array(
                    'date' => $current_date,
                    'metric_name' => 'daily_leads',
                    'metric_value' => 0,
                    'metric_type' => 'counter'
                ),
                array(
                    'date' => $current_date,
                    'metric_name' => 'conversion_rate',
                    'metric_value' => 0,
                    'metric_type' => 'percentage'
                )
            );
            
            foreach ($analytics_data as $data) {
                $wpdb->insert(
                    $this->table_prefix . 'analytics',
                    $data,
                    array('%s', '%s', '%f', '%s')
                );
            }
            
        } catch (Exception $e) {
            error_log('BIIC Initial Data Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Get table name with prefix
     */
    public function get_table($table_name) {
        return $this->table_prefix . $table_name;
    }
    
    /**
     * Check if tables exist
     */
    public function tables_exist() {
        global $wpdb;
        
        $required_tables = array('chat_sessions', 'chat_messages', 'leads');
        
        foreach ($required_tables as $table) {
            $table_name = $this->table_prefix . $table;
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
            
            if (!$exists) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Insert chat session
     */
    public function insert_chat_session($session_data) {
        global $wpdb;
        
        $default_data = array(
            'session_id' => uniqid('biic_'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'started_at' => current_time('mysql'),
            'last_activity' => current_time('mysql'),
            'is_active' => 1
        );
        
        $data = array_merge($default_data, $session_data);
        
        $result = $wpdb->insert(
            $this->table_prefix . 'chat_sessions',
            $data
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Insert chat message
     */
    public function insert_chat_message($session_id, $message_type, $content, $additional_data = array()) {
        global $wpdb;
        
        $default_data = array(
            'session_id' => $session_id,
            'message_type' => $message_type,
            'content' => $content,
            'timestamp' => current_time('mysql')
        );
        
        $data = array_merge($default_data, $additional_data);
        
        $result = $wpdb->insert(
            $this->table_prefix . 'chat_messages',
            $data
        );
        
        // Update session message count
        if ($result) {
            $wpdb->query($wpdb->prepare(
                "UPDATE {$this->table_prefix}chat_sessions 
                 SET total_messages = total_messages + 1, last_activity = %s 
                 WHERE session_id = %s",
                current_time('mysql'),
                $session_id
            ));
        }
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Insert lead
     */
    public function insert_lead($lead_data) {
        global $wpdb;
        
        $default_data = array(
            'lead_status' => 'new',
            'lead_score' => 0,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
        
        $data = array_merge($default_data, $lead_data);
        
        $result = $wpdb->insert(
            $this->table_prefix . 'leads',
            $data
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Get recent conversations
     */
    public function get_recent_conversations($limit = 20, $offset = 0) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT s.*, l.name as lead_name, l.phone as lead_phone, l.email as lead_email 
             FROM {$this->table_prefix}chat_sessions s
             LEFT JOIN {$this->table_prefix}leads l ON s.session_id = l.session_id
             ORDER BY s.started_at DESC
             LIMIT %d OFFSET %d",
            $limit, $offset
        ));
    }
    
    /**
     * Get leads
     */
    public function get_leads($filters = array(), $limit = 20, $offset = 0) {
        global $wpdb;
        
        $where_conditions = array('1=1');
        $where_values = array();
        
        if (!empty($filters['status'])) {
            $where_conditions[] = 'lead_status = %s';
            $where_values[] = $filters['status'];
        }
        
        if (!empty($filters['course_interest'])) {
            $where_conditions[] = 'course_interest = %s';
            $where_values[] = $filters['course_interest'];
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $query = "SELECT * FROM {$this->table_prefix}leads 
                  WHERE $where_clause 
                  ORDER BY created_at DESC 
                  LIMIT %d OFFSET %d";
        
        $query_values = array_merge($where_values, array($limit, $offset));
        
        return $wpdb->get_results($wpdb->prepare($query, $query_values));
    }
    
    /**
     * Drop all tables (for uninstall)
     */
    public function drop_tables() {
        global $wpdb;
        
        $tables = array(
            'analytics',
            'leads', 
            'user_interactions',
            'chat_messages',
            'chat_sessions'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$this->table_prefix}{$table}");
        }
    }
}