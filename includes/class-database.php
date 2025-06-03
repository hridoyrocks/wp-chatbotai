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
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $this->create_chat_sessions_table();
        $this->create_chat_messages_table();
        $this->create_user_interactions_table();
        $this->create_leads_table();
        $this->create_analytics_table();
        
        // Update database version
        update_option('biic_db_version', $this->db_version);
    }
    
    /**
     * Create chat sessions table
     */
    private function create_chat_sessions_table() {
        $table_name = $this->tables['chat_sessions'];
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id varchar(255) NOT NULL UNIQUE,
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
            KEY session_id (session_id),
            KEY user_id (user_id),
            KEY started_at (started_at),
            KEY lead_status (lead_status),
            KEY is_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        dbDelta($sql);
    }
    
    /**
     * Create chat messages table
     */
    private function create_chat_messages_table() {
        $table_name = $this->tables['chat_messages'];
        
        $sql = "CREATE TABLE $table_name (
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
            KEY timestamp (timestamp),
            FOREIGN KEY (session_id) REFERENCES {$this->tables['chat_sessions']}(session_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        dbDelta($sql);
    }
    
    /**
     * Create user interactions table
     */
    private function create_user_interactions_table() {
        $table_name = $this->tables['user_interactions'];
        
        $sql = "CREATE TABLE $table_name (
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
            KEY timestamp (timestamp),
            FOREIGN KEY (session_id) REFERENCES {$this->tables['chat_sessions']}(session_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        dbDelta($sql);
    }
    
    /**
     * Create leads table
     */
    private function create_leads_table() {
        $table_name = $this->tables['leads'];
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id varchar(255) NOT NULL,
            name varchar(255) NULL,
            email varchar(255) NULL,
            phone varchar(20) NULL,
            course_interest varchar(100) NULL,
            lead_score int(11) DEFAULT 0,
            lead_status enum('new','contacted','qualified','converted','lost') DEFAULT 'new',
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
            custom_fields json NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY email (email),
            KEY phone (phone),
            KEY lead_status (lead_status),
            KEY course_interest (course_interest),
            KEY assigned_to (assigned_to),
            KEY follow_up_date (follow_up_date),
            KEY created_at (created_at),
            FOREIGN KEY (session_id) REFERENCES {$this->tables['chat_sessions']}(session_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        dbDelta($sql);
    }
    
    /**
     * Create analytics table
     */
    private function create_analytics_table() {
        $table_name = $this->tables['analytics'];
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            date date NOT NULL,
            metric_name varchar(100) NOT NULL,
            metric_value decimal(15,4) NOT NULL,
            metric_type varchar(50) NOT NULL,
            dimensions json NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY date_metric (date, metric_name),
            KEY metric_name (metric_name),
            KEY metric_type (metric_type),
            KEY date (date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        dbDelta($sql);
    }
    
    /**
     * Get table name
     */
    public function get_table($table_key) {
        return isset($this->tables[$table_key]) ? $this->tables[$table_key] : false;
    }
    
    /**
     * Insert chat session
     */
    public function insert_chat_session($data) {
        $defaults = array(
            'session_id' => $this->generate_session_id(),
            'ip_address' => $this->get_user_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'started_at' => current_time('mysql'),
            'last_activity' => current_time('mysql'),
            'page_url' => $_SERVER['HTTP_REFERER'] ?? '',
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $result = $this->wpdb->insert(
            $this->tables['chat_sessions'],
            $data,
            array(
                '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', 
                '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%d',
                '%s', '%s', '%s'
            )
        );
        
        return $result ? $this->wpdb->insert_id : false;
    }
    
    /**
     * Insert chat message
     */
    public function insert_chat_message($data) {
        $defaults = array(
            'timestamp' => current_time('mysql'),
            'content_type' => 'text',
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $result = $this->wpdb->insert(
            $this->tables['chat_messages'],
            $data,
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%d', '%d', '%s')
        );
        
        if ($result) {
            // Update session last activity and message count
            $this->update_session_activity($data['session_id']);
        }
        
        return $result ? $this->wpdb->insert_id : false;
    }
    
    /**
     * Insert user interaction
     */
    public function insert_user_interaction($data) {
        $defaults = array(
            'timestamp' => current_time('mysql'),
        );
        
        $data = wp_parse_args($data, $defaults);
        
        return $this->wpdb->insert(
            $this->tables['user_interactions'],
            $data,
            array('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d')
        );
    }
    
    /**
     * Insert or update lead
     */
    public function upsert_lead($data) {
        // Check if lead exists
        $existing_lead = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT id FROM {$this->tables['leads']} WHERE session_id = %s OR email = %s",
                $data['session_id'],
                $data['email'] ?? ''
            )
        );
        
        if ($existing_lead) {
            // Update existing lead
            unset($data['created_at']);
            $result = $this->wpdb->update(
                $this->tables['leads'],
                $data,
                array('id' => $existing_lead->id),
                array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s'),
                array('%d')
            );
            return $existing_lead->id;
        } else {
            // Insert new lead
            $defaults = array(
                'created_at' => current_time('mysql'),
                'lead_status' => 'new',
                'priority' => 'medium',
            );
            
            $data = wp_parse_args($data, $defaults);
            
            $result = $this->wpdb->insert(
                $this->tables['leads'],
                $data,
                array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s')
            );
            
            return $result ? $this->wpdb->insert_id : false;
        }
    }
    
    /**
     * Update session activity
     */
    public function update_session_activity($session_id) {
        return $this->wpdb->update(
            $this->tables['chat_sessions'],
            array(
                'last_activity' => current_time('mysql'),
                'total_messages' => new WP_Query("SELECT COUNT(*) FROM {$this->tables['chat_messages']} WHERE session_id = '$session_id'")
            ),
            array('session_id' => $session_id),
            array('%s', '%d'),
            array('%s')
        );
    }
    
    /**
     * Get chat session
     */
    public function get_chat_session($session_id) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->tables['chat_sessions']} WHERE session_id = %s",
                $session_id
            )
        );
    }
    
    /**
     * Get chat messages
     */
    public function get_chat_messages($session_id, $limit = 50) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->tables['chat_messages']} 
                WHERE session_id = %s 
                ORDER BY timestamp ASC 
                LIMIT %d",
                $session_id,
                $limit
            )
        );
    }
    
    /**
     * Get recent conversations
     */
    public function get_recent_conversations($limit = 20, $offset = 0) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT s.*, 
                       COUNT(m.id) as message_count,
                       MAX(m.timestamp) as last_message_time,
                       l.name as lead_name,
                       l.email as lead_email,
                       l.phone as lead_phone
                FROM {$this->tables['chat_sessions']} s
                LEFT JOIN {$this->tables['chat_messages']} m ON s.session_id = m.session_id
                LEFT JOIN {$this->tables['leads']} l ON s.session_id = l.session_id
                GROUP BY s.id
                ORDER BY s.last_activity DESC
                LIMIT %d OFFSET %d",
                $limit,
                $offset
            )
        );
    }
    
    /**
     * Get leads
     */
    public function get_leads($filters = array(), $limit = 20, $offset = 0) {
        $where_clauses = array('1=1');
        $values = array();
        
        if (!empty($filters['status'])) {
            $where_clauses[] = 'lead_status = %s';
            $values[] = $filters['status'];
        }
        
        if (!empty($filters['course_interest'])) {
            $where_clauses[] = 'course_interest = %s';
            $values[] = $filters['course_interest'];
        }
        
        if (!empty($filters['date_from'])) {
            $where_clauses[] = 'DATE(created_at) >= %s';
            $values[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where_clauses[] = 'DATE(created_at) <= %s';
            $values[] = $filters['date_to'];
        }
        
        $where_clause = implode(' AND ', $where_clauses);
        $values[] = $limit;
        $values[] = $offset;
        
        $sql = "SELECT l.*, 
                       s.ip_address, s.location, s.device_type,
                       COUNT(m.id) as total_messages
                FROM {$this->tables['leads']} l
                LEFT JOIN {$this->tables['chat_sessions']} s ON l.session_id = s.session_id
                LEFT JOIN {$this->tables['chat_messages']} m ON l.session_id = m.session_id
                WHERE $where_clause
                GROUP BY l.id
                ORDER BY l.created_at DESC
                LIMIT %d OFFSET %d";
        
        return $this->wpdb->get_results(
            $this->wpdb->prepare($sql, ...$values)
        );
    }
    
    /**
     * Get analytics data
     */
    public function get_analytics_data($metric_name, $date_from, $date_to) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->tables['analytics']} 
                WHERE metric_name = %s 
                AND date BETWEEN %s AND %s 
                ORDER BY date ASC",
                $metric_name,
                $date_from,
                $date_to
            )
        );
    }
    
    /**
     * Store analytics metric
     */
    public function store_analytics_metric($date, $metric_name, $metric_value, $metric_type = 'counter', $dimensions = null) {
        return $this->wpdb->replace(
            $this->tables['analytics'],
            array(
                'date' => $date,
                'metric_name' => $metric_name,
                'metric_value' => $metric_value,
                'metric_type' => $metric_type,
                'dimensions' => $dimensions ? json_encode($dimensions) : null,
            ),
            array('%s', '%s', '%f', '%s', '%s')
        );
    }
    
    /**
     * Generate unique session ID
     */
    private function generate_session_id() {
        return 'biic_' . uniqid() . '_' . wp_generate_password(8, false);
    }
    
    /**
     * Get user IP address
     */
    private function get_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        }
    }
    
    /**
     * Clean up old sessions
     */
    public function cleanup_old_sessions($days = 30) {
        $date_threshold = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        // Delete old sessions and related data
        $this->wpdb->query(
            $this->wpdb->prepare(
                "DELETE FROM {$this->tables['chat_sessions']} 
                WHERE last_activity < %s AND is_active = 0",
                $date_threshold
            )
        );
    }
    
    /**
     * Get database statistics
     */
    public function get_database_stats() {
        $stats = array();
        
        foreach ($this->tables as $key => $table) {
            $count = $this->wpdb->get_var("SELECT COUNT(*) FROM $table");
            $stats[$key] = (int) $count;
        }
        
        return $stats;
    }
}