<?php
/**
 * Leads API Endpoints
 * api/endpoints/leads.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BIIC_Leads_Endpoints {
    
    /**
     * Register leads-related REST routes
     */
    public function register_routes() {
        // Get all leads endpoint
        register_rest_route('biic/v1', '/leads', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_leads'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'page' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 1,
                    'minimum' => 1
                ),
                'per_page' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 20,
                    'minimum' => 1,
                    'maximum' => 100
                ),
                'status' => array(
                    'required' => false,
                    'type' => 'string',
                    'enum' => array('new', 'contacted', 'qualified', 'converted', 'lost')
                ),
                'course_interest' => array(
                    'required' => false,
                    'type' => 'string'
                ),
                'date_from' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date'
                ),
                'date_to' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date'
                ),
                'search' => array(
                    'required' => false,
                    'type' => 'string'
                ),
                'orderby' => array(
                    'required' => false,
                    'type' => 'string',
                    'enum' => array('created_at', 'lead_score', 'name', 'updated_at'),
                    'default' => 'created_at'
                ),
                'order' => array(
                    'required' => false,
                    'type' => 'string',
                    'enum' => array('asc', 'desc'),
                    'default' => 'desc'
                )
            )
        ));

        // Create new lead endpoint
        register_rest_route('biic/v1', '/leads', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_lead'),
            'permission_callback' => array($this, 'check_create_permission'),
            'args' => array(
                'session_id' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'name' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'phone' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => array($this, 'validate_phone')
                ),
                'email' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_email'
                ),
                'course_interest' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'lead_source' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'api',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'notes' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field'
                )
            )
        ));

        // Get single lead endpoint
        register_rest_route('biic/v1', '/leads/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_lead'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'include_conversation' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false
                )
            )
        ));

        // Update lead endpoint
        register_rest_route('biic/v1', '/leads/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_lead'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'name' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'phone' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => array($this, 'validate_phone')
                ),
                'email' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_email'
                ),
                'lead_status' => array(
                    'required' => false,
                    'type' => 'string',
                    'enum' => array('new', 'contacted', 'qualified', 'converted', 'lost')
                ),
                'course_interest' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'notes' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field'
                ),
                'lead_score' => array(
                    'required' => false,
                    'type' => 'integer',
                    'minimum' => 0,
                    'maximum' => 100
                )
            )
        ));

        // Delete lead endpoint
        register_rest_route('biic/v1', '/leads/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_lead'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));

        // Lead statistics endpoint
        register_rest_route('biic/v1', '/leads/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_lead_stats'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'date_from' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d', strtotime('-30 days'))
                ),
                'date_to' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d')
                )
            )
        ));

        // Lead conversion funnel endpoint
        register_rest_route('biic/v1', '/leads/funnel', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_conversion_funnel'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'date_from' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d', strtotime('-30 days'))
                ),
                'date_to' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d')
                )
            )
        ));

        // Bulk operations endpoint
        register_rest_route('biic/v1', '/leads/bulk', array(
            'methods' => 'POST',
            'callback' => array($this, 'bulk_operations'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'action' => array(
                    'required' => true,
                    'type' => 'string',
                    'enum' => array('update_status', 'delete', 'export', 'assign_counselor')
                ),
                'lead_ids' => array(
                    'required' => true,
                    'type' => 'array',
                    'items' => array('type' => 'integer')
                ),
                'data' => array(
                    'required' => false,
                    'type' => 'object'
                )
            )
        ));

        // Lead notes endpoint
        register_rest_route('biic/v1', '/leads/(?P<id>\d+)/notes', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_lead_notes'),
                'permission_callback' => array($this, 'check_admin_permission')
            ),
            array(
                'methods' => 'POST',
                'callback' => array($this, 'add_lead_note'),
                'permission_callback' => array($this, 'check_admin_permission'),
                'args' => array(
                    'note' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_textarea_field'
                    ),
                    'note_type' => array(
                        'required' => false,
                        'type' => 'string',
                        'enum' => array('general', 'follow_up', 'conversion', 'important'),
                        'default' => 'general'
                    )
                )
            )
        ));

        // Lead follow-up endpoint
        register_rest_route('biic/v1', '/leads/(?P<id>\d+)/follow-up', array(
            'methods' => 'POST',
            'callback' => array($this, 'schedule_follow_up'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'follow_up_date' => array(
                    'required' => true,
                    'type' => 'string',
                    'format' => 'date-time'
                ),
                'follow_up_type' => array(
                    'required' => true,
                    'type' => 'string',
                    'enum' => array('call', 'email', 'whatsapp', 'sms')
                ),
                'notes' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field'
                )
            )
        ));

        // Export leads endpoint
        register_rest_route('biic/v1', '/leads/export', array(
            'methods' => 'POST',
            'callback' => array($this, 'export_leads'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'format' => array(
                    'required' => true,
                    'type' => 'string',
                    'enum' => array('csv', 'xlsx', 'pdf')
                ),
                'filters' => array(
                    'required' => false,
                    'type' => 'object'
                ),
                'fields' => array(
                    'required' => false,
                    'type' => 'array',
                    'items' => array('type' => 'string')
                )
            )
        ));

        // Lead scoring endpoint
        register_rest_route('biic/v1', '/leads/(?P<id>\d+)/score', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_lead_score'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'score' => array(
                    'required' => true,
                    'type' => 'integer',
                    'minimum' => 0,
                    'maximum' => 100
                ),
                'score_reason' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));
    }

    /**
     * Get leads
     */
    public function get_leads($request) {
        $page = $request->get_param('page');
        $per_page = $request->get_param('per_page');
        $status = $request->get_param('status');
        $course_interest = $request->get_param('course_interest');
        $date_from = $request->get_param('date_from');
        $date_to = $request->get_param('date_to');
        $search = $request->get_param('search');
        $orderby = $request->get_param('orderby');
        $order = $request->get_param('order');

        global $wpdb;
        $database = BIIC()->database;
        $leads_table = $database->get_table('leads');

        // Build WHERE clause
        $where_conditions = array('1=1');
        $where_values = array();

        if ($status) {
            $where_conditions[] = 'lead_status = %s';
            $where_values[] = $status;
        }

        if ($course_interest) {
            $where_conditions[] = 'course_interest = %s';
            $where_values[] = $course_interest;
        }

        if ($date_from) {
            $where_conditions[] = 'DATE(created_at) >= %s';
            $where_values[] = $date_from;
        }

        if ($date_to) {
            $where_conditions[] = 'DATE(created_at) <= %s';
            $where_values[] = $date_to;
        }

        if ($search) {
            $where_conditions[] = '(name LIKE %s OR phone LIKE %s OR email LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }

        $where_clause = implode(' AND ', $where_conditions);

        // Get total count
        $total_query = "SELECT COUNT(*) FROM $leads_table WHERE $where_clause";
        if (!empty($where_values)) {
            $total_query = $wpdb->prepare($total_query, $where_values);
        }
        $total = $wpdb->get_var($total_query);

        // Get leads with pagination
        $offset = ($page - 1) * $per_page;
        $leads_query = "SELECT l.*, s.ip_address, s.location, s.device_type 
                       FROM $leads_table l
                       LEFT JOIN {$database->get_table('chat_sessions')} s ON l.session_id = s.session_id
                       WHERE $where_clause
                       ORDER BY l.$orderby $order
                       LIMIT %d OFFSET %d";

        $query_values = array_merge($where_values, array($per_page, $offset));
        $leads = $wpdb->get_results($wpdb->prepare($leads_query, $query_values));

        return rest_ensure_response(array(
            'success' => true,
            'data' => $leads,
            'pagination' => array(
                'page' => $page,
                'per_page' => $per_page,
                'total' => (int) $total,
                'total_pages' => ceil($total / $per_page)
            )
        ));
    }

    /**
     * Create new lead
     */
    public function create_lead($request) {
        $session_id = $request->get_param('session_id') ?: 'api_' . uniqid();
        $name = $request->get_param('name');
        $phone = $request->get_param('phone');
        $email = $request->get_param('email');
        $course_interest = $request->get_param('course_interest');
        $lead_source = $request->get_param('lead_source');
        $notes = $request->get_param('notes');

        // Check if lead already exists with this phone
        global $wpdb;
        $database = BIIC()->database;
        $existing_lead = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$database->get_table('leads')} WHERE phone = %s",
            $phone
        ));

        if ($existing_lead) {
            return new WP_Error('duplicate_lead', 'A lead with this phone number already exists', array('status' => 409));
        }

        // Calculate initial lead score
        $lead_score = $this->calculate_lead_score(array(
            'name' => $name,
            'email' => $email,
            'course_interest' => $course_interest,
            'lead_source' => $lead_source
        ));

        // Insert lead
        $lead_data = array(
            'session_id' => $session_id,
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'course_interest' => $course_interest,
            'lead_source' => $lead_source,
            'lead_status' => 'new',
            'lead_score' => $lead_score,
            'notes' => $notes,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );

        $result = $wpdb->insert($database->get_table('leads'), $lead_data);

        if ($result === false) {
            return new WP_Error('creation_failed', 'Failed to create lead', array('status' => 500));
        }

        $lead_id = $wpdb->insert_id;

        // Send notification if enabled
        if (get_option('biic_lead_notifications', true)) {
            $this->send_lead_notification($lead_data);
        }

        // Track lead creation
        do_action('biic_lead_created', $lead_id, $lead_data);

        return rest_ensure_response(array(
            'success' => true,
            'lead_id' => $lead_id,
            'message' => 'Lead created successfully',
            'data' => array_merge($lead_data, array('id' => $lead_id))
        ));
    }

    /**
     * Get single lead
     */
    public function get_lead($request) {
        $lead_id = $request->get_param('id');
        $include_conversation = $request->get_param('include_conversation');

        global $wpdb;
        $database = BIIC()->database;

        $lead = $wpdb->get_row($wpdb->prepare(
            "SELECT l.*, s.ip_address, s.location, s.device_type, s.started_at as session_started
             FROM {$database->get_table('leads')} l
             LEFT JOIN {$database->get_table('chat_sessions')} s ON l.session_id = s.session_id
             WHERE l.id = %d",
            $lead_id
        ));

        if (!$lead) {
            return new WP_Error('not_found', 'Lead not found', array('status' => 404));
        }

        $response_data = array(
            'success' => true,
            'data' => $lead
        );

        if ($include_conversation && $lead->session_id) {
            $messages = $database->get_chat_messages($lead->session_id);
            $response_data['conversation'] = $messages;
        }

        return rest_ensure_response($response_data);
    }

    /**
     * Update lead
     */
    public function update_lead($request) {
        $lead_id = $request->get_param('id');

        global $wpdb;
        $database = BIIC()->database;

        // Check if lead exists
        $existing_lead = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$database->get_table('leads')} WHERE id = %d",
            $lead_id
        ));

        if (!$existing_lead) {
            return new WP_Error('not_found', 'Lead not found', array('status' => 404));
        }

        // Prepare update data
        $update_data = array();
        $fields = array('name', 'phone', 'email', 'lead_status', 'course_interest', 'notes', 'lead_score');

        foreach ($fields as $field) {
            $value = $request->get_param($field);
            if ($value !== null) {
                $update_data[$field] = $value;
            }
        }

        if (empty($update_data)) {
            return new WP_Error('no_data', 'No data provided for update', array('status' => 400));
        }

        // Add updated timestamp
        $update_data['updated_at'] = current_time('mysql');

        // Update conversion date if status changed to converted
        if (isset($update_data['lead_status']) && $update_data['lead_status'] === 'converted' && $existing_lead->lead_status !== 'converted') {
            $update_data['conversion_date'] = current_time('mysql');
        }

        $result = $wpdb->update(
            $database->get_table('leads'),
            $update_data,
            array('id' => $lead_id)
        );

        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to update lead', array('status' => 500));
        }

        // Track lead update
        do_action('biic_lead_updated', $lead_id, $update_data, $existing_lead);

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Lead updated successfully',
            'data' => array_merge((array) $existing_lead, $update_data)
        ));
    }

    /**
     * Delete lead
     */
    public function delete_lead($request) {
        $lead_id = $request->get_param('id');

        global $wpdb;
        $database = BIIC()->database;

        // Check if lead exists
        $lead = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$database->get_table('leads')} WHERE id = %d",
            $lead_id
        ));

        if (!$lead) {
            return new WP_Error('not_found', 'Lead not found', array('status' => 404));
        }

        $result = $wpdb->delete(
            $database->get_table('leads'),
            array('id' => $lead_id),
            array('%d')
        );

        if ($result === false) {
            return new WP_Error('deletion_failed', 'Failed to delete lead', array('status' => 500));
        }

        // Track lead deletion
        do_action('biic_lead_deleted', $lead_id, $lead);

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Lead deleted successfully'
        ));
    }

    /**
     * Get lead statistics
     */
    public function get_lead_stats($request) {
        $date_from = $request->get_param('date_from');
        $date_to = $request->get_param('date_to');

        global $wpdb;
        $database = BIIC()->database;
        $leads_table = $database->get_table('leads');

        $stats = array();

        // Total leads
        $stats['total_leads'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $leads_table WHERE DATE(created_at) BETWEEN %s AND %s",
            $date_from, $date_to
        ));

        // Leads by status
        $stats['by_status'] = $wpdb->get_results($wpdb->prepare(
            "SELECT lead_status, COUNT(*) as count 
             FROM $leads_table 
             WHERE DATE(created_at) BETWEEN %s AND %s 
             GROUP BY lead_status",
            $date_from, $date_to
        ));

        // Leads by course interest
        $stats['by_course'] = $wpdb->get_results($wpdb->prepare(
            "SELECT course_interest, COUNT(*) as count 
             FROM $leads_table 
             WHERE DATE(created_at) BETWEEN %s AND %s 
             AND course_interest IS NOT NULL
             GROUP BY course_interest 
             ORDER BY count DESC",
            $date_from, $date_to
        ));

        // Leads by score range
        $stats['by_score'] = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                CASE 
                    WHEN lead_score >= 80 THEN 'hot'
                    WHEN lead_score >= 60 THEN 'warm'
                    WHEN lead_score >= 40 THEN 'medium'
                    ELSE 'cold'
                END as score_range,
                COUNT(*) as count
             FROM $leads_table 
             WHERE DATE(created_at) BETWEEN %s AND %s 
             GROUP BY score_range",
            $date_from, $date_to
        ));

        // Daily leads
        $stats['daily_leads'] = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(created_at) as date, COUNT(*) as count 
             FROM $leads_table 
             WHERE DATE(created_at) BETWEEN %s AND %s 
             GROUP BY DATE(created_at) 
             ORDER BY date ASC",
            $date_from, $date_to
        ));

        // Conversion metrics
        $converted_leads = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $leads_table 
             WHERE lead_status = 'converted' 
             AND DATE(conversion_date) BETWEEN %s AND %s",
            $date_from, $date_to
        ));

        $stats['conversion_rate'] = $stats['total_leads'] > 0 ? 
            round(($converted_leads / $stats['total_leads']) * 100, 2) : 0;

        return rest_ensure_response(array(
            'success' => true,
            'data' => $stats,
            'date_range' => array(
                'from' => $date_from,
                'to' => $date_to
            )
        ));
    }

    /**
     * Get conversion funnel
     */
    public function get_conversion_funnel($request) {
        $date_from = $request->get_param('date_from');
        $date_to = $request->get_param('date_to');

        global $wpdb;
        $database = BIIC()->database;

        // Get funnel data
        $sessions = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$database->get_table('chat_sessions')} 
             WHERE DATE(started_at) BETWEEN %s AND %s",
            $date_from, $date_to
        ));

        $engaged_sessions = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$database->get_table('chat_sessions')} 
             WHERE total_messages >= 3 
             AND DATE(started_at) BETWEEN %s AND %s",
            $date_from, $date_to
        ));

        $leads = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$database->get_table('leads')} 
             WHERE DATE(created_at) BETWEEN %s AND %s",
            $date_from, $date_to
        ));

        $converted = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$database->get_table('leads')} 
             WHERE lead_status = 'converted' 
             AND DATE(conversion_date) BETWEEN %s AND %s",
            $date_from, $date_to
        ));

        $funnel = array(
            'total_sessions' => (int) $sessions,
            'engaged_sessions' => (int) $engaged_sessions,
            'leads_generated' => (int) $leads,
            'leads_converted' => (int) $converted,
            'engagement_rate' => $sessions > 0 ? round(($engaged_sessions / $sessions) * 100, 2) : 0,
            'conversion_rate' => $sessions > 0 ? round(($leads / $sessions) * 100, 2) : 0,
            'close_rate' => $leads > 0 ? round(($converted / $leads) * 100, 2) : 0
        );

        return rest_ensure_response(array(
            'success' => true,
            'data' => $funnel,
            'date_range' => array(
                'from' => $date_from,
                'to' => $date_to
            )
        ));
    }

    /**
     * Bulk operations on leads
     */
    public function bulk_operations($request) {
        $action = $request->get_param('action');
        $lead_ids = $request->get_param('lead_ids');
        $data = $request->get_param('data');

        if (empty($lead_ids)) {
            return new WP_Error('no_leads', 'No leads specified', array('status' => 400));
        }

        global $wpdb;
        $database = BIIC()->database;
        $leads_table = $database->get_table('leads');

        $results = array(
            'success_count' => 0,
            'error_count' => 0,
            'errors' => array()
        );

        switch ($action) {
            case 'update_status':
                if (empty($data['status'])) {
                    return new WP_Error('missing_status', 'Status is required', array('status' => 400));
                }

                $update_data = array(
                    'lead_status' => $data['status'],
                    'updated_at' => current_time('mysql')
                );

                if ($data['status'] === 'converted') {
                    $update_data['conversion_date'] = current_time('mysql');
                }

                foreach ($lead_ids as $lead_id) {
                    $result = $wpdb->update($leads_table, $update_data, array('id' => $lead_id));
                    if ($result !== false) {
                        $results['success_count']++;
                    } else {
                        $results['error_count']++;
                        $results['errors'][] = "Failed to update lead ID: $lead_id";
                    }
                }
                break;

            case 'delete':
                foreach ($lead_ids as $lead_id) {
                    $result = $wpdb->delete($leads_table, array('id' => $lead_id), array('%d'));
                    if ($result !== false) {
                        $results['success_count']++;
                    } else {
                        $results['error_count']++;
                        $results['errors'][] = "Failed to delete lead ID: $lead_id";
                    }
                }
                break;

            case 'assign_counselor':
                if (empty($data['counselor'])) {
                    return new WP_Error('missing_counselor', 'Counselor is required', array('status' => 400));
                }

                foreach ($lead_ids as $lead_id) {
                    $result = $wpdb->update(
                        $leads_table,
                        array(
                            'assigned_counselor' => $data['counselor'],
                            'updated_at' => current_time('mysql')
                        ),
                        array('id' => $lead_id)
                    );
                    if ($result !== false) {
                        $results['success_count']++;
                    } else {
                        $results['error_count']++;
                        $results['errors'][] = "Failed to assign counselor to lead ID: $lead_id";
                    }
                }
                break;

            case 'export':
                return $this->export_selected_leads($lead_ids, $data);

            default:
                return new WP_Error('invalid_action', 'Invalid bulk action', array('status' => 400));
        }

        return rest_ensure_response(array(
            'success' => true,
            'message' => "Bulk operation completed",
            'results' => $results
        ));
    }

    /**
     * Get lead notes
     */
    public function get_lead_notes($request) {
        $lead_id = $request->get_param('id');

        global $wpdb;
        $database = BIIC()->database;

        // Check if lead exists
        $lead = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$database->get_table('leads')} WHERE id = %d",
            $lead_id
        ));

        if (!$lead) {
            return new WP_Error('not_found', 'Lead not found', array('status' => 404));
        }

        // Get notes from lead record and separate notes table if exists
        $lead_data = $wpdb->get_row($wpdb->prepare(
            "SELECT notes FROM {$database->get_table('leads')} WHERE id = %d",
            $lead_id
        ));

        $notes = array();
        if ($lead_data->notes) {
            $notes[] = array(
                'id' => 'main',
                'note' => $lead_data->notes,
                'note_type' => 'general',
                'created_at' => $lead_data->created_at ?? current_time('mysql'),
                'author' => 'System'
            );
        }

        return rest_ensure_response(array(
            'success' => true,
            'data' => $notes
        ));
    }

    /**
     * Add lead note
     */
    public function add_lead_note($request) {
        $lead_id = $request->get_param('id');
        $note = $request->get_param('note');
        $note_type = $request->get_param('note_type');

        global $wpdb;
        $database = BIIC()->database;

        // Check if lead exists
        $lead = $wpdb->get_row($wpdb->prepare(
            "SELECT notes FROM {$database->get_table('leads')} WHERE id = %d",
            $lead_id
        ));

        if (!$lead) {
            return new WP_Error('not_found', 'Lead not found', array('status' => 404));
        }

        // Append note to existing notes
        $existing_notes = $lead->notes ? $lead->notes . "\n\n" : '';
        $timestamp = current_time('mysql');
        $current_user = wp_get_current_user();
        $author = $current_user->display_name ?: 'Admin';

        $new_note = "[$timestamp] [$note_type] [$author]: $note";
        $updated_notes = $existing_notes . $new_note;

        $result = $wpdb->update(
            $database->get_table('leads'),
            array(
                'notes' => $updated_notes,
                'updated_at' => $timestamp
            ),
            array('id' => $lead_id)
        );

        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to add note', array('status' => 500));
        }

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Note added successfully',
            'note' => array(
                'note' => $note,
                'note_type' => $note_type,
                'created_at' => $timestamp,
                'author' => $author
            )
        ));
    }

    /**
     * Schedule follow-up
     */
    public function schedule_follow_up($request) {
        $lead_id = $request->get_param('id');
        $follow_up_date = $request->get_param('follow_up_date');
        $follow_up_type = $request->get_param('follow_up_type');
        $notes = $request->get_param('notes');

        global $wpdb;
        $database = BIIC()->database;

        // Check if lead exists
        $lead = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$database->get_table('leads')} WHERE id = %d",
            $lead_id
        ));

        if (!$lead) {
            return new WP_Error('not_found', 'Lead not found', array('status' => 404));
        }

        // Update lead with follow-up info
        $result = $wpdb->update(
            $database->get_table('leads'),
            array(
                'follow_up_date' => $follow_up_date,
                'follow_up_type' => $follow_up_type,
                'follow_up_notes' => $notes,
                'updated_at' => current_time('mysql')
            ),
            array('id' => $lead_id)
        );

        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to schedule follow-up', array('status' => 500));
        }

        // Schedule WordPress cron job for follow-up reminder
        wp_schedule_single_event(
            strtotime($follow_up_date),
            'biic_follow_up_reminder',
            array($lead_id)
        );

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Follow-up scheduled successfully',
            'follow_up' => array(
                'date' => $follow_up_date,
                'type' => $follow_up_type,
                'notes' => $notes
            )
        ));
    }

    /**
     * Export leads
     */
    public function export_leads($request) {
        $format = $request->get_param('format');
        $filters = $request->get_param('filters') ?: array();
        $fields = $request->get_param('fields') ?: array();

        global $wpdb;
        $database = BIIC()->database;

        // Build query based on filters
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

        if (!empty($filters['date_from'])) {
            $where_conditions[] = 'DATE(created_at) >= %s';
            $where_values[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where_conditions[] = 'DATE(created_at) <= %s';
            $where_values[] = $filters['date_to'];
        }

        $where_clause = implode(' AND ', $where_conditions);

        // Select fields
        $select_fields = empty($fields) ? '*' : implode(', ', array_map('sanitize_sql_orderby', $fields));

        $query = "SELECT $select_fields FROM {$database->get_table('leads')} WHERE $where_clause ORDER BY created_at DESC";
        
        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        }

        $leads = $wpdb->get_results($query, ARRAY_A);

        if (empty($leads)) {
            return new WP_Error('no_data', 'No leads found for export', array('status' => 404));
        }

        // Generate export file
        $filename = 'biic_leads_export_' . date('Y-m-d_H-i-s');
        $file_url = $this->generate_export_file($leads, $format, $filename);

        return rest_ensure_response(array(
            'success' => true,
            'export_url' => $file_url,
            'filename' => $filename . '.' . $format,
            'record_count' => count($leads),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour'))
        ));
    }

    /**
     * Update lead score
     */
    public function update_lead_score($request) {
        $lead_id = $request->get_param('id');
        $score = $request->get_param('score');
        $score_reason = $request->get_param('score_reason');

        global $wpdb;
        $database = BIIC()->database;

        // Check if lead exists
        $lead = $wpdb->get_row($wpdb->prepare(
            "SELECT id, lead_score FROM {$database->get_table('leads')} WHERE id = %d",
            $lead_id
        ));

        if (!$lead) {
            return new WP_Error('not_found', 'Lead not found', array('status' => 404));
        }

        $old_score = $lead->lead_score;

        $result = $wpdb->update(
            $database->get_table('leads'),
            array(
                'lead_score' => $score,
                'score_reason' => $score_reason,
                'updated_at' => current_time('mysql')
            ),
            array('id' => $lead_id)
        );

        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to update lead score', array('status' => 500));
        }

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Lead score updated successfully',
            'data' => array(
                'old_score' => $old_score,
                'new_score' => $score,
                'score_reason' => $score_reason
            )
        ));
    }

    /**
     * Permission callbacks
     */
    public function check_admin_permission($request) {
        return current_user_can('manage_options');
    }

    public function check_create_permission($request) {
        // Allow public lead creation or check for API key
        return true;
    }

    /**
     * Validation callbacks
     */
    public function validate_phone($value, $request, $param) {
        // Validate Bangladesh phone number format
        if (!preg_match('/^(\+880|880|01)[0-9]{8,9}$/', $value)) {
            return new WP_Error('invalid_phone', 'Invalid phone number format');
        }
        return true;
    }

    /**
     * Helper methods
     */
    private function calculate_lead_score($data) {
        $score = 30; // Base score

        // Add points for complete information
        if (!empty($data['name'])) $score += 10;
        if (!empty($data['email'])) $score += 15;
        if (!empty($data['course_interest'])) $score += 20;

        // Add points based on lead source
        $source_scores = array(
            'chatbot' => 25,
            'website' => 20,
            'facebook' => 15,
            'google' => 20,
            'api' => 10
        );

        $score += $source_scores[$data['lead_source']] ?? 10;

        return min(100, $score); // Cap at 100
    }

    private function send_lead_notification($lead_data) {
        $notification_email = get_option('biic_notification_email', get_option('admin_email'));
        $subject = 'নতুন লিড - Banglay IELTS Chatbot';

        $message = "নতুন লিড পাওয়া গেছে:\n\n";
        $message .= "নাম: {$lead_data['name']}\n";
        $message .= "ফোন: {$lead_data['phone']}\n";
        $message .= "ইমেইল: {$lead_data['email']}\n";
        $message .= "কোর্স: {$lead_data['course_interest']}\n";
        $message .= "স্কোর: {$lead_data['lead_score']}/100\n";
        $message .= "সময়: " . current_time('mysql') . "\n\n";
        $message .= "অ্যাডমিন প্যানেলে দেখুন।";

        wp_mail($notification_email, $subject, $message);
    }

    private function export_selected_leads($lead_ids, $data) {
        global $wpdb;
        $database = BIIC()->database;

        $ids_placeholder = implode(',', array_fill(0, count($lead_ids), '%d'));
        $leads = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$database->get_table('leads')} WHERE id IN ($ids_placeholder)",
            $lead_ids
        ), ARRAY_A);

        $format = $data['format'] ?? 'csv';
        $filename = 'biic_selected_leads_' . date('Y-m-d_H-i-s');
        $file_url = $this->generate_export_file($leads, $format, $filename);

        return rest_ensure_response(array(
            'success' => true,
            'export_url' => $file_url,
            'filename' => $filename . '.' . $format,
            'record_count' => count($leads)
        ));
    }

    private function generate_export_file($data, $format, $filename) {
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/biic-exports/';

        if (!file_exists($export_dir)) {
            wp_mkdir_p($export_dir);
        }

        $file_path = $export_dir . $filename . '.' . $format;

        switch ($format) {
            case 'csv':
                $this->generate_csv_file($data, $file_path);
                break;
            case 'xlsx':
                $this->generate_excel_file($data, $file_path);
                break;
            case 'pdf':
                $this->generate_pdf_file($data, $file_path);
                break;
        }

        return $upload_dir['baseurl'] . '/biic-exports/' . $filename . '.' . $format;
    }

    private function generate_csv_file($data, $file_path) {
        $file = fopen($file_path, 'w');

        // Add BOM for UTF-8
        fputs($file, "\xEF\xBB\xBF");

        if (!empty($data)) {
            // Write headers
            fputcsv($file, array_keys($data[0]));

            // Write data
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
        }

        fclose($file);
    }

    private function generate_excel_file($data, $file_path) {
        // For now, fallback to CSV
        // In production, implement with PhpSpreadsheet
        $this->generate_csv_file($data, str_replace('.xlsx', '.csv', $file_path));
    }

    private function generate_pdf_file($data, $file_path) {
        // For now, create a simple text file
        // In production, implement with TCPDF or similar
        $content = "Banglay IELTS Leads Export\n";
        $content .= "Generated: " . current_time('mysql') . "\n\n";
        $content .= "Total Records: " . count($data) . "\n\n";

        foreach ($data as $lead) {
            $content .= "Lead ID: {$lead['id']}\n";
            $content .= "Name: {$lead['name']}\n";
            $content .= "Phone: {$lead['phone']}\n";
            $content .= "Email: {$lead['email']}\n";
            $content .= "Status: {$lead['lead_status']}\n";
            $content .= "Score: {$lead['lead_score']}\n";
            $content .= "Created: {$lead['created_at']}\n";
            $content .= "------------------------\n\n";
        }

        file_put_contents($file_path, $content);
    }
}