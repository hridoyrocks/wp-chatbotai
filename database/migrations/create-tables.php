<?php
/**
 * Database Tables Creation Migration
 * database/migrations/create-tables.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create all required database tables for Banglay IELTS Chatbot
 */
function biic_create_database_tables() {
    global $wpdb;
    
    // Get the WordPress database prefix
    $table_prefix = $wpdb->prefix . 'biic_';
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Chat Sessions Table
    $sql_sessions = "CREATE TABLE {$table_prefix}chat_sessions (
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
        started_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        ended_at datetime NULL,
        last_activity datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        total_messages int(11) DEFAULT 0,
        conversation_duration int(11) DEFAULT 0,
        lead_score int(11) DEFAULT 0,
        lead_status enum('hot','warm','cold','converted') DEFAULT 'cold',
        is_active tinyint(1) DEFAULT 1,
        utm_source varchar(100) NULL,
        utm_medium varchar(100) NULL,
        utm_campaign varchar(100) NULL,
        utm_term varchar(100) NULL,
        utm_content varchar(100) NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY session_id (session_id),
        KEY user_id (user_id),
        KEY ip_address (ip_address),
        KEY started_at (started_at),
        KEY last_activity (last_activity),
        KEY lead_status (lead_status),
        KEY lead_score (lead_score),
        KEY is_active (is_active),
        KEY device_type (device_type),
        KEY country (country),
        KEY utm_source (utm_source)
    ) $charset_collate;";
    
    // Chat Messages Table
    $sql_messages = "CREATE TABLE {$table_prefix}chat_messages (
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
        KEY sentiment (sentiment),
        FULLTEXT KEY content (content),
        CONSTRAINT fk_messages_session FOREIGN KEY (session_id) REFERENCES {$table_prefix}chat_sessions(session_id) ON DELETE CASCADE
    ) $charset_collate;";
    
    // User Interactions Table
    $sql_interactions = "CREATE TABLE {$table_prefix}user_interactions (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        session_id varchar(255) NOT NULL,
        interaction_type varchar(100) NOT NULL,
        interaction_data json NULL,
        page_url varchar(500) NULL,
        element_id varchar(100) NULL,
        element_text varchar(255) NULL,
        element_class varchar(255) NULL,
        scroll_depth int(11) NULL,
        time_on_page int(11) NULL,
        mouse_x int(11) NULL,
        mouse_y int(11) NULL,
        viewport_width int(11) NULL,
        viewport_height int(11) NULL,
        timestamp datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY session_id (session_id),
        KEY interaction_type (interaction_type),
        KEY timestamp (timestamp),
        KEY page_url (page_url),
        CONSTRAINT fk_interactions_session FOREIGN KEY (session_id) REFERENCES {$table_prefix}chat_sessions(session_id) ON DELETE CASCADE
    ) $charset_collate;";
    
    // Leads Table
    $sql_leads = "CREATE TABLE {$table_prefix}leads (
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
        custom_fields json NULL,
        lead_origin varchar(100) NULL,
        referral_source varchar(255) NULL,
        budget_range varchar(50) NULL,
        timeline varchar(50) NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY session_id (session_id),
        UNIQUE KEY unique_session_lead (session_id),
        KEY email (email),
        KEY phone (phone),
        KEY lead_status (lead_status),
        KEY lead_score (lead_score),
        KEY course_interest (course_interest),
        KEY assigned_to (assigned_to),
        KEY follow_up_date (follow_up_date),
        KEY conversion_date (conversion_date),
        KEY created_at (created_at),
        KEY priority (priority),
        CONSTRAINT fk_leads_session FOREIGN KEY (session_id) REFERENCES {$table_prefix}chat_sessions(session_id) ON DELETE CASCADE
    ) $charset_collate;";
    
    // Analytics Table
    $sql_analytics = "CREATE TABLE {$table_prefix}analytics (
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
        KEY metric_type (metric_type),
        KEY date (date),
        KEY period_type (period_type)
    ) $charset_collate;";
    
    // Lead Notes Table (for detailed note tracking)
    $sql_lead_notes = "CREATE TABLE {$table_prefix}lead_notes (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        lead_id bigint(20) NOT NULL,
        user_id bigint(20) NOT NULL,
        note_type enum('general','call','email','meeting','follow_up','status_change') DEFAULT 'general',
        note_content text NOT NULL,
        is_private tinyint(1) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY lead_id (lead_id),
        KEY user_id (user_id),
        KEY note_type (note_type),
        KEY created_at (created_at),
        CONSTRAINT fk_notes_lead FOREIGN KEY (lead_id) REFERENCES {$table_prefix}leads(id) ON DELETE CASCADE
    ) $charset_collate;";
    
    // Chatbot Training Data Table
    $sql_training = "CREATE TABLE {$table_prefix}training_data (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        intent varchar(100) NOT NULL,
        example_input text NOT NULL,
        expected_response text NOT NULL,
        response_type varchar(50) DEFAULT 'text',
        confidence_threshold decimal(3,2) DEFAULT 0.80,
        entities json NULL,
        context_required json NULL,
        is_active tinyint(1) DEFAULT 1,
        language varchar(10) DEFAULT 'bn',
        created_by bigint(20) NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY intent (intent),
        KEY language (language),
        KEY is_active (is_active),
        KEY created_by (created_by),
        FULLTEXT KEY training_content (example_input, expected_response)
    ) $charset_collate;";
    
    // Email Templates Table
    $sql_email_templates = "CREATE TABLE {$table_prefix}email_templates (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        template_name varchar(100) NOT NULL,
        template_subject varchar(255) NOT NULL,
        template_body text NOT NULL,
        template_type enum('lead_notification','follow_up','welcome','conversion','nurture') NOT NULL,
        placeholders json NULL,
        is_active tinyint(1) DEFAULT 1,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY template_type (template_type),
        KEY template_name (template_name),
        KEY is_active (is_active)
    ) $charset_collate;";
    
    // API Keys and Integrations Table
    $sql_integrations = "CREATE TABLE {$table_prefix}integrations (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        integration_name varchar(100) NOT NULL,
        integration_type varchar(50) NOT NULL,
        api_key varchar(500) NULL,
        api_secret varchar(500) NULL,
        endpoint_url varchar(500) NULL,
        configuration json NULL,
        is_active tinyint(1) DEFAULT 0,
        last_sync datetime NULL,
        sync_status enum('success','failed','pending','disabled') DEFAULT 'pending',
        error_log text NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_integration (integration_name, integration_type),
        KEY integration_type (integration_type),
        KEY is_active (is_active),
        KEY sync_status (sync_status)
    ) $charset_collate;";
    
    // Webhook Events Table
    $sql_webhooks = "CREATE TABLE {$table_prefix}webhook_events (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        event_type varchar(100) NOT NULL,
        payload json NOT NULL,
        webhook_url varchar(500) NOT NULL,
        status enum('pending','sent','failed','retry') DEFAULT 'pending',
        attempts int(11) DEFAULT 0,
        max_attempts int(11) DEFAULT 3,
        response_code int(11) NULL,
        response_body text NULL,
        error_message text NULL,
        scheduled_at datetime DEFAULT CURRENT_TIMESTAMP,
        sent_at datetime NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY event_type (event_type),
        KEY status (status),
        KEY scheduled_at (scheduled_at),
        KEY attempts (attempts)
    ) $charset_collate;";
    
    // Execute table creation
    $tables = array(
        'chat_sessions' => $sql_sessions,
        'chat_messages' => $sql_messages,
        'user_interactions' => $sql_interactions,
        'leads' => $sql_leads,
        'analytics' => $sql_analytics,
        'lead_notes' => $sql_lead_notes,
        'training_data' => $sql_training,
        'email_templates' => $sql_email_templates,
        'integrations' => $sql_integrations,
        'webhook_events' => $sql_webhooks
    );
    
    $created_tables = array();
    $errors = array();
    
    foreach ($tables as $table_name => $sql) {
        $result = dbDelta($sql);
        
        if ($result) {
            $created_tables[] = $table_name;
            error_log("BIIC: Created table {$table_prefix}{$table_name}");
        } else {
            $errors[] = "Failed to create table {$table_prefix}{$table_name}";
            error_log("BIIC: Failed to create table {$table_prefix}{$table_name}");
        }
    }
    
    // Create indexes for better performance
    biic_create_database_indexes($table_prefix);
    
    // Insert initial data
    biic_insert_initial_data($table_prefix);
    
    return array(
        'created_tables' => $created_tables,
        'errors' => $errors,
        'success' => empty($errors)
    );
}

/**
 * Create additional database indexes for performance
 */
function biic_create_database_indexes($table_prefix) {
    global $wpdb;
    
    $indexes = array(
        // Composite indexes for common queries
        "CREATE INDEX idx_session_activity ON {$table_prefix}chat_sessions (is_active, last_activity)",
        "CREATE INDEX idx_session_lead ON {$table_prefix}chat_sessions (lead_status, lead_score)",
        "CREATE INDEX idx_message_intent ON {$table_prefix}chat_messages (detected_intent, intent_confidence)",
        "CREATE INDEX idx_message_timestamp ON {$table_prefix}chat_messages (timestamp, message_type)",
        "CREATE INDEX idx_lead_status_date ON {$table_prefix}leads (lead_status, created_at)",
        "CREATE INDEX idx_lead_follow_up ON {$table_prefix}leads (follow_up_date, lead_status)",
        "CREATE INDEX idx_analytics_date_metric ON {$table_prefix}analytics (date, metric_name, period_type)",
        "CREATE INDEX idx_interaction_page ON {$table_prefix}user_interactions (page_url, interaction_type)"
    );
    
    foreach ($indexes as $index_sql) {
        $wpdb->query($index_sql);
    }
}

/**
 * Insert initial data and default settings
 */
function biic_insert_initial_data($table_prefix) {
    global $wpdb;
    
    // Insert default email templates
    $email_templates = array(
        array(
            'template_name' => 'new_lead_notification',
            'template_subject' => 'New Lead from Banglay IELTS Chatbot',
            'template_body' => 'New lead received:\n\nName: {name}\nPhone: {phone}\nEmail: {email}\nCourse Interest: {course_interest}\nLead Score: {lead_score}/100\n\nTime: {created_at}',
            'template_type' => 'lead_notification',
            'placeholders' => json_encode(['name', 'phone', 'email', 'course_interest', 'lead_score', 'created_at'])
        ),
        array(
            'template_name' => 'follow_up_reminder',
            'template_subject' => 'Follow-up Required: {name}',
            'template_body' => 'Follow-up required for lead:\n\nName: {name}\nPhone: {phone}\nLead Score: {lead_score}\nLast Contact: {last_contact_date}\n\nNotes: {follow_up_notes}',
            'template_type' => 'follow_up',
            'placeholders' => json_encode(['name', 'phone', 'lead_score', 'last_contact_date', 'follow_up_notes'])
        ),
        array(
            'template_name' => 'lead_converted',
            'template_subject' => 'Lead Converted: {name} 🎉',
            'template_body' => 'Great news! Lead has been converted:\n\nName: {name}\nPhone: {phone}\nCourse: {course_interest}\nConversion Value: {conversion_value}\n\nCongratulations!',
            'template_type' => 'conversion',
            'placeholders' => json_encode(['name', 'phone', 'course_interest', 'conversion_value'])
        )
    );
    
    foreach ($email_templates as $template) {
        $wpdb->insert(
            $table_prefix . 'email_templates',
            $template,
            array('%s', '%s', '%s', '%s', '%s')
        );
    }
    
    // Insert default training data
    $training_data = array(
        array(
            'intent' => 'greeting',
            'example_input' => 'আস্সালামু আলাইকুম',
            'expected_response' => 'ওয়ালাইকুম আস্সালাম! আমি Banglay IELTS এর AI সহায়ক। কিভাবে সাহায্য করতে পারি?',
            'language' => 'bn'
        ),
        array(
            'intent' => 'course_inquiry',
            'example_input' => 'IELTS কোর্স সম্পর্কে জানতে চাই',
            'expected_response' => 'আমাদের ৪টি IELTS কোর্স আছে: Comprehensive, Focus, Crash এবং Online। কোনটি সম্পর্কে জানতে চান?',
            'language' => 'bn'
        ),
        array(
            'intent' => 'course_fee',
            'example_input' => 'কোর্স ফি কত',
            'expected_response' => 'কোর্স ফি জানতে +880 961 382 0821 নম্বরে কল করুন অথবা admission.banglayelts.com ভিজিট করুন।',
            'language' => 'bn'
        )
    );
    
    foreach ($training_data as $data) {
        $wpdb->insert(
            $table_prefix . 'training_data',
            $data,
            array('%s', '%s', '%s', '%s')
        );
    }
    
    // Insert sample analytics data for current month
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
            $table_prefix . 'analytics',
            $data,
            array('%s', '%s', '%f', '%s')
        );
    }
}