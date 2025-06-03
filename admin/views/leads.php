<?php
/**
 * Admin Leads Management View
 * Professional lead management interface
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get leads data
$leads = isset($leads) ? $leads : array();
$lead_stats = isset($lead_stats) ? $lead_stats : array();
?>

<div class="wrap biic-admin-wrap">
    
    <!-- Header -->
    <div class="biic-admin-header">
        <div class="biic-header-content">
            <h1 class="biic-page-title">
                <span class="biic-logo">🎯</span>
                <?php esc_html_e('Lead Management', 'banglay-ielts-chatbot'); ?>
            </h1>
            <div class="biic-header-actions">
                <button type="button" class="button button-secondary" onclick="biicExportLeads()">
                    <span class="dashicons dashicons-download"></span>
                    <?php esc_html_e('Export Leads', 'banglay-ielts-chatbot'); ?>
                </button>
                <button type="button" class="button button-primary" onclick="biicBulkActions()">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <?php esc_html_e('Bulk Actions', 'banglay-ielts-chatbot'); ?>
                </button>
            </div>
        </div>
        
        <!-- Lead Statistics -->
        <div class="biic-leads-stats">
            <div class="biic-stat-item">
                <span class="biic-stat-icon">📊</span>
                <div class="biic-stat-content">
                    <span class="biic-stat-label"><?php esc_html_e('Total Leads', 'banglay-ielts-chatbot'); ?></span>
                    <span class="biic-stat-value"><?php echo esc_html($lead_stats['total_leads'] ?? 0); ?></span>
                </div>
            </div>
            
            <div class="biic-stat-item hot">
                <span class="biic-stat-icon">🔥</span>
                <div class="biic-stat-content">
                    <span class="biic-stat-label"><?php esc_html_e('Hot Leads', 'banglay-ielts-chatbot'); ?></span>
                    <span class="biic-stat-value"><?php echo esc_html($this->count_leads_by_status($lead_stats, 'hot')); ?></span>
                </div>
            </div>
            
            <div class="biic-stat-item warm">
                <span class="biic-stat-icon">🌡️</span>
                <div class="biic-stat-content">
                    <span class="biic-stat-label"><?php esc_html_e('Warm Leads', 'banglay-ielts-chatbot'); ?></span>
                    <span class="biic-stat-value"><?php echo esc_html($this->count_leads_by_status($lead_stats, 'warm')); ?></span>
                </div>
            </div>
            
            <div class="biic-stat-item converted">
                <span class="biic-stat-icon">✅</span>
                <div class="biic-stat-content">
                    <span class="biic-stat-label"><?php esc_html_e('Converted', 'banglay-ielts-chatbot'); ?></span>
                    <span class="biic-stat-value"><?php echo esc_html($lead_stats['converted_leads_month'] ?? 0); ?></span>
                </div>
            </div>
            
            <div class="biic-stat-item conversion">
                <span class="biic-stat-icon">📈</span>
                <div class="biic-stat-content">
                    <span class="biic-stat-label"><?php esc_html_e('Conversion Rate', 'banglay-ielts-chatbot'); ?></span>
                    <span class="biic-stat-value"><?php echo esc_html($lead_stats['conversion_rate'] ?? 0); ?>%</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters and Search -->
    <div class="biic-leads-filters">
        <div class="biic-filters-row">
            <div class="biic-filter-group">
                <label for="lead-status-filter"><?php esc_html_e('Status:', 'banglay-ielts-chatbot'); ?></label>
                <select id="lead-status-filter" name="status">
                    <option value=""><?php esc_html_e('All Statuses', 'banglay-ielts-chatbot'); ?></option>
                    <option value="new" <?php selected($_GET['status'] ?? '', 'new'); ?>><?php esc_html_e('New', 'banglay-ielts-chatbot'); ?></option>
                    <option value="contacted" <?php selected($_GET['status'] ?? '', 'contacted'); ?>><?php esc_html_e('Contacted', 'banglay-ielts-chatbot'); ?></option>
                    <option value="qualified" <?php selected($_GET['status'] ?? '', 'qualified'); ?>><?php esc_html_e('Qualified', 'banglay-ielts-chatbot'); ?></option>
                    <option value="converted" <?php selected($_GET['status'] ?? '', 'converted'); ?>><?php esc_html_e('Converted', 'banglay-ielts-chatbot'); ?></option>
                    <option value="lost" <?php selected($_GET['status'] ?? '', 'lost'); ?>><?php esc_html_e('Lost', 'banglay-ielts-chatbot'); ?></option>
                </select>
            </div>
            
            <div class="biic-filter-group">
                <label for="course-filter"><?php esc_html_e('Course Interest:', 'banglay-ielts-chatbot'); ?></label>
                <select id="course-filter" name="course_interest">
                    <option value=""><?php esc_html_e('All Courses', 'banglay-ielts-chatbot'); ?></option>
                    <option value="ielts_comprehensive" <?php selected($_GET['course_interest'] ?? '', 'ielts_comprehensive'); ?>><?php esc_html_e('IELTS Comprehensive', 'banglay-ielts-chatbot'); ?></option>
                    <option value="ielts_focus" <?php selected($_GET['course_interest'] ?? '', 'ielts_focus'); ?>><?php esc_html_e('IELTS Focus', 'banglay-ielts-chatbot'); ?></option>
                    <option value="ielts_crash" <?php selected($_GET['course_interest'] ?? '', 'ielts_crash'); ?>><?php esc_html_e('IELTS Crash', 'banglay-ielts-chatbot'); ?></option>
                    <option value="online_course" <?php selected($_GET['course_interest'] ?? '', 'online_course'); ?>><?php esc_html_e('Online Course', 'banglay-ielts-chatbot'); ?></option>
                    <option value="study_abroad" <?php selected($_GET['course_interest'] ?? '', 'study_abroad'); ?>><?php esc_html_e('Study Abroad', 'banglay-ielts-chatbot'); ?></option>
                </select>
            </div>
            
            <div class="biic-filter-group">
                <label for="date-from"><?php esc_html_e('Date From:', 'banglay-ielts-chatbot'); ?></label>
                <input type="date" id="date-from" name="date_from" value="<?php echo esc_attr($_GET['date_from'] ?? ''); ?>">
            </div>
            
            <div class="biic-filter-group">
                <label for="date-to"><?php esc_html_e('Date To:', 'banglay-ielts-chatbot'); ?></label>
                <input type="date" id="date-to" name="date_to" value="<?php echo esc_attr($_GET['date_to'] ?? ''); ?>">
            </div>
            
            <div class="biic-filter-actions">
                <button type="button" class="button button-primary" onclick="biicApplyFilters()">
                    <span class="dashicons dashicons-filter"></span>
                    <?php esc_html_e('Apply Filters', 'banglay-ielts-chatbot'); ?>
                </button>
                <button type="button" class="button button-secondary" onclick="biicClearFilters()">
                    <?php esc_html_e('Clear', 'banglay-ielts-chatbot'); ?>
                </button>
            </div>
        </div>
        
        <div class="biic-search-row">
            <div class="biic-search-box">
                <input type="text" id="leads-search" placeholder="<?php esc_attr_e('Search by name, phone, or email...', 'banglay-ielts-chatbot'); ?>" value="<?php echo esc_attr($_GET['search'] ?? ''); ?>">
                <button type="button" class="biic-search-btn" onclick="biicSearchLeads()">
                    <span class="dashicons dashicons-search"></span>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Leads Table -->
    <div class="biic-leads-table-container">
        <table class="biic-leads-table wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th class="check-column">
                        <input type="checkbox" id="select-all-leads">
                    </th>
                    <th class="biic-col-score" data-sort="lead_score">
                        <?php esc_html_e('Score', 'banglay-ielts-chatbot'); ?>
                        <span class="biic-sort-indicator"></span>
                    </th>
                    <th class="biic-col-contact" data-sort="name">
                        <?php esc_html_e('Contact Info', 'banglay-ielts-chatbot'); ?>
                        <span class="biic-sort-indicator"></span>
                    </th>
                    <th class="biic-col-course" data-sort="course_interest">
                        <?php esc_html_e('Course Interest', 'banglay-ielts-chatbot'); ?>
                        <span class="biic-sort-indicator"></span>
                    </th>
                    <th class="biic-col-status" data-sort="lead_status">
                        <?php esc_html_e('Status', 'banglay-ielts-chatbot'); ?>
                        <span class="biic-sort-indicator"></span>
                    </th>
                    <th class="biic-col-source" data-sort="lead_source">
                        <?php esc_html_e('Source', 'banglay-ielts-chatbot'); ?>
                        <span class="biic-sort-indicator"></span>
                    </th>
                    <th class="biic-col-date" data-sort="created_at">
                        <?php esc_html_e('Created', 'banglay-ielts-chatbot'); ?>
                        <span class="biic-sort-indicator"></span>
                    </th>
                    <th class="biic-col-actions">
                        <?php esc_html_e('Actions', 'banglay-ielts-chatbot'); ?>
                    </th>
                </tr>
            </thead>
            <tbody id="leads-table-body">
                <?php if (!empty($leads)): ?>
                    <?php foreach ($leads as $lead): ?>
                        <tr class="biic-lead-row" data-lead-id="<?php echo esc_attr($lead->id); ?>">
                            <td class="check-column">
                                <input type="checkbox" class="lead-checkbox" value="<?php echo esc_attr($lead->id); ?>">
                            </td>
                            
                            <!-- Lead Score -->
                            <td class="biic-col-score">
                                <div class="biic-score-container">
                                    <div class="biic-score-circle <?php echo $this->get_score_class($lead->lead_score); ?>">
                                        <span class="biic-score-number"><?php echo esc_html($lead->lead_score ?? 0); ?></span>
                                    </div>
                                    <div class="biic-score-tier">
                                        <?php echo esc_html($this->get_lead_tier($lead->lead_score)); ?>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Contact Information -->
                            <td class="biic-col-contact">
                                <div class="biic-contact-info">
                                    <div class="biic-contact-name">
                                        <strong><?php echo esc_html($lead->name ?: 'N/A'); ?></strong>
                                    </div>
                                    <div class="biic-contact-details">
                                        <?php if ($lead->phone): ?>
                                            <a href="tel:<?php echo esc_attr($lead->phone); ?>" class="biic-phone-link">
                                                <span class="dashicons dashicons-phone"></span>
                                                <?php echo esc_html($lead->phone); ?>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($lead->email): ?>
                                            <a href="mailto:<?php echo esc_attr($lead->email); ?>" class="biic-email-link">
                                                <span class="dashicons dashicons-email"></span>
                                                <?php echo esc_html($lead->email); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($lead->location): ?>
                                        <div class="biic-contact-location">
                                            <span class="dashicons dashicons-location"></span>
                                            <?php echo esc_html($lead->location); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <!-- Course Interest -->
                            <td class="biic-col-course">
                                <?php if ($lead->course_interest): ?>
                                    <div class="biic-course-badge <?php echo esc_attr($lead->course_interest); ?>">
                                        <?php echo esc_html(ucfirst(str_replace('_', ' ', $lead->course_interest))); ?>
                                    </div>
                                <?php else: ?>
                                    <span class="biic-no-data">—</span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Lead Status -->
                            <td class="biic-col-status">
                                <div class="biic-status-container">
                                    <select class="biic-status-select" data-lead-id="<?php echo esc_attr($lead->id); ?>" data-current-status="<?php echo esc_attr($lead->lead_status); ?>">
                                        <option value="new" <?php selected($lead->lead_status, 'new'); ?>><?php esc_html_e('New', 'banglay-ielts-chatbot'); ?></option>
                                        <option value="contacted" <?php selected($lead->lead_status, 'contacted'); ?>><?php esc_html_e('Contacted', 'banglay-ielts-chatbot'); ?></option>
                                        <option value="qualified" <?php selected($lead->lead_status, 'qualified'); ?>><?php esc_html_e('Qualified', 'banglay-ielts-chatbot'); ?></option>
                                        <option value="converted" <?php selected($lead->lead_status, 'converted'); ?>><?php esc_html_e('Converted', 'banglay-ielts-chatbot'); ?></option>
                                        <option value="lost" <?php selected($lead->lead_status, 'lost'); ?>><?php esc_html_e('Lost', 'banglay-ielts-chatbot'); ?></option>
                                    </select>
                                    <div class="biic-status-badge biic-status-<?php echo esc_attr($lead->lead_status); ?>">
                                        <?php echo esc_html(ucfirst($lead->lead_status)); ?>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Lead Source -->
                            <td class="biic-col-source">
                                <div class="biic-source-info">
                                    <span class="biic-source-badge">
                                        <?php echo esc_html(ucfirst($lead->lead_source ?? 'chatbot')); ?>
                                    </span>
                                    <?php if ($lead->device_type): ?>
                                        <span class="biic-device-info" title="<?php esc_attr_e('Device Type', 'banglay-ielts-chatbot'); ?>">
                                            <?php echo $lead->device_type === 'mobile' ? '📱' : ($lead->device_type === 'tablet' ? '📟' : '💻'); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <!-- Created Date -->
                            <td class="biic-col-date">
                                <div class="biic-date-info">
                                    <div class="biic-date-primary">
                                        <?php echo esc_html(date('M j, Y', strtotime($lead->created_at))); ?>
                                    </div>
                                    <div class="biic-date-time">
                                        <?php echo esc_html(date('g:i A', strtotime($lead->created_at))); ?>
                                    </div>
                                    <div class="biic-date-relative" title="<?php echo esc_attr($lead->created_at); ?>">
                                        <?php echo esc_html(human_time_diff(strtotime($lead->created_at), current_time('timestamp'))); ?> ago
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Actions -->
                            <td class="biic-col-actions">
                                <div class="biic-actions-container">
                                    <button type="button" class="button button-small biic-view-lead" data-lead-id="<?php echo esc_attr($lead->id); ?>" title="<?php esc_attr_e('View Details', 'banglay-ielts-chatbot'); ?>">
                                        <span class="dashicons dashicons-visibility"></span>
                                    </button>
                                    
                                    <button type="button" class="button button-small biic-edit-lead" data-lead-id="<?php echo esc_attr($lead->id); ?>" title="<?php esc_attr_e('Edit Lead', 'banglay-ielts-chatbot'); ?>">
                                        <span class="dashicons dashicons-edit"></span>
                                    </button>
                                    
                                    <?php if ($lead->phone): ?>
                                        <a href="tel:<?php echo esc_attr($lead->phone); ?>" class="button button-small biic-call-lead" title="<?php esc_attr_e('Call Lead', 'banglay-ielts-chatbot'); ?>">
                                            <span class="dashicons dashicons-phone"></span>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($lead->email): ?>
                                        <a href="mailto:<?php echo esc_attr($lead->email); ?>" class="button button-small biic-email-lead" title="<?php esc_attr_e('Send Email', 'banglay-ielts-chatbot'); ?>">
                                            <span class="dashicons dashicons-email-alt"></span>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <div class="biic-actions-more">
                                        <button type="button" class="button button-small biic-more-actions" data-lead-id="<?php echo esc_attr($lead->id); ?>">
                                            <span class="dashicons dashicons-ellipsis"></span>
                                        </button>
                                        <div class="biic-actions-dropdown">
                                            <a href="#" class="biic-add-note" data-lead-id="<?php echo esc_attr($lead->id); ?>">
                                                <span class="dashicons dashicons-edit-page"></span>
                                                <?php esc_html_e('Add Note', 'banglay-ielts-chatbot'); ?>
                                            </a>
                                            <a href="#" class="biic-schedule-follow-up" data-lead-id="<?php echo esc_attr($lead->id); ?>">
                                                <span class="dashicons dashicons-calendar-alt"></span>
                                                <?php esc_html_e('Schedule Follow-up', 'banglay-ielts-chatbot'); ?>
                                            </a>
                                            <a href="#" class="biic-view-conversation" data-session-id="<?php echo esc_attr($lead->session_id); ?>">
                                                <span class="dashicons dashicons-format-chat"></span>
                                                <?php esc_html_e('View Conversation', 'banglay-ielts-chatbot'); ?>
                                            </a>
                                            <hr>
                                            <a href="#" class="biic-delete-lead biic-text-danger" data-lead-id="<?php echo esc_attr($lead->id); ?>">
                                                <span class="dashicons dashicons-trash"></span>
                                                <?php esc_html_e('Delete Lead', 'banglay-ielts-chatbot'); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="biic-no-leads">
                        <td colspan="8" class="biic-empty-state">
                            <div class="biic-empty-content">
                                <span class="biic-empty-icon">🎯</span>
                                <h3><?php esc_html_e('No leads found', 'banglay-ielts-chatbot'); ?></h3>
                                <p><?php esc_html_e('Leads will appear here as users interact with your chatbot.', 'banglay-ielts-chatbot'); ?></p>
                                <a href="<?php echo admin_url('admin.php?page=biic-settings'); ?>" class="button button-primary">
                                    <?php esc_html_e('Configure Chatbot', 'banglay-ielts-chatbot'); ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if (!empty($leads) && count($leads) >= 20): ?>
        <div class="biic-pagination">
            <div class="biic-pagination-info">
                <?php printf(esc_html__('Showing %d leads', 'banglay-ielts-chatbot'), count($leads)); ?>
            </div>
            <div class="biic-pagination-controls">
                <button type="button" class="button" onclick="biicLoadMore()" id="load-more-leads">
                    <?php esc_html_e('Load More', 'banglay-ielts-chatbot'); ?>
                </button>
            </div>
        </div>
    <?php endif; ?>
    
</div>

<!-- Lead Details Modal -->
<div id="biic-lead-modal" class="biic-modal" style="display: none;">
    <div class="biic-modal-content">
        <div class="biic-modal-header">
            <h2><?php esc_html_e('Lead Details', 'banglay-ielts-chatbot'); ?></h2>
            <button type="button" class="biic-modal-close">&times;</button>
        </div>
        <div class="biic-modal-body" id="lead-modal-content">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div id="biic-note-modal" class="biic-modal" style="display: none;">
    <div class="biic-modal-content biic-modal-small">
        <div class="biic-modal-header">
            <h3><?php esc_html_e('Add Note', 'banglay-ielts-chatbot'); ?></h3>
            <button type="button" class="biic-modal-close">&times;</button>
        </div>
        <div class="biic-modal-body">
            <form id="add-note-form">
                <input type="hidden" id="note-lead-id" name="lead_id">
                <div class="biic-form-group">
                    <label for="lead-note"><?php esc_html_e('Note:', 'banglay-ielts-chatbot'); ?></label>
                    <textarea id="lead-note" name="note" rows="4" class="widefat" placeholder="<?php esc_attr_e('Enter your note here...', 'banglay-ielts-chatbot'); ?>" required></textarea>
                </div>
                <div class="biic-form-actions">
                    <button type="submit" class="button button-primary"><?php esc_html_e('Add Note', 'banglay-ielts-chatbot'); ?></button>
                    <button type="button" class="button button-secondary biic-modal-close"><?php esc_html_e('Cancel', 'banglay-ielts-chatbot'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div id="biic-bulk-modal" class="biic-modal" style="display: none;">
    <div class="biic-modal-content">
        <div class="biic-modal-header">
            <h3><?php esc_html_e('Bulk Actions', 'banglay-ielts-chatbot'); ?></h3>
            <button type="button" class="biic-modal-close">&times;</button>
        </div>
        <div class="biic-modal-body">
            <div class="biic-bulk-selection-info">
                <p><span id="selected-count">0</span> <?php esc_html_e('leads selected', 'banglay-ielts-chatbot'); ?></p>
            </div>
            
            <div class="biic-bulk-actions-list">
                <button type="button" class="biic-bulk-action" data-action="update_status">
                    <span class="dashicons dashicons-update"></span>
                    <?php esc_html_e('Update Status', 'banglay-ielts-chatbot'); ?>
                </button>
                
                <button type="button" class="biic-bulk-action" data-action="assign_counselor">
                    <span class="dashicons dashicons-admin-users"></span>
                    <?php esc_html_e('Assign Counselor', 'banglay-ielts-chatbot'); ?>
                </button>
                
                <button type="button" class="biic-bulk-action" data-action="send_email">
                    <span class="dashicons dashicons-email"></span>
                    <?php esc_html_e('Send Email Campaign', 'banglay-ielts-chatbot'); ?>
                </button>
                
                <button type="button" class="biic-bulk-action" data-action="export">
                    <span class="dashicons dashicons-download"></span>
                    <?php esc_html_e('Export Selected', 'banglay-ielts-chatbot'); ?>
                </button>
                
                <button type="button" class="biic-bulk-action biic-text-danger" data-action="delete">
                    <span class="dashicons dashicons-trash"></span>
                    <?php esc_html_e('Delete Selected', 'banglay-ielts-chatbot'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Initialize leads management
    biicInitLeadsManagement();
});

// Initialize leads management functionality
function biicInitLeadsManagement() {
    var $ = jQuery;
    
    // Status change handling
    $('.biic-status-select').on('change', function() {
        var leadId = $(this).data('lead-id');
        var newStatus = $(this).val();
        var currentStatus = $(this).data('current-status');
        
        if (newStatus !== currentStatus) {
            biicUpdateLeadStatus(leadId, newStatus, $(this));
        }
    });
    
    // View lead details
    $('.biic-view-lead').on('click', function() {
        var leadId = $(this).data('lead-id');
        biicShowLeadDetails(leadId);
    });
    
    // Add note functionality
    $('.biic-add-note').on('click', function(e) {
        e.preventDefault();
        var leadId = $(this).data('lead-id');
        biicShowAddNoteModal(leadId);
    });
    
    // Modal close functionality
    $('.biic-modal-close').on('click', function() {
        $(this).closest('.biic-modal').hide();
    });
    
    // Add note form submission
    $('#add-note-form').on('submit', function(e) {
        e.preventDefault();
        biicSubmitNote();
    });
    
    // Select all checkbox
    $('#select-all-leads').on('change', function() {
        $('.lead-checkbox').prop('checked', $(this).prop('checked'));
        biicUpdateBulkSelection();
    });
    
    // Individual checkboxes
    $('.lead-checkbox').on('change', function() {
        biicUpdateBulkSelection();
    });
    
    // Table sorting
    $('.biic-leads-table th[data-sort]').on('click', function() {
        var sortField = $(this).data('sort');
        biicSortLeads(sortField);
    });
    
    // Actions dropdown
    $('.biic-more-actions').on('click', function() {
        $('.biic-actions-dropdown').hide();
        $(this).siblings('.biic-actions-dropdown').toggle();
    });
    
    // Hide dropdowns when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.biic-actions-more').length) {
            $('.biic-actions-dropdown').hide();
        }
    });
}

// Update lead status
function biicUpdateLeadStatus(leadId, newStatus, selectElement) {
    var $ = jQuery;
    var originalStatus = selectElement.data('current-status');
    
    // Show loading state
    selectElement.prop('disabled', true);
    
    $.post(ajaxurl, {
        action: 'biic_update_lead_status',
        lead_id: leadId,
        status: newStatus,
        nonce: biic_admin.nonce
    }, function(response) {
        if (response.success) {
            // Update the status badge
            var statusBadge = selectElement.siblings('.biic-status-badge');
            statusBadge.removeClass().addClass('biic-status-badge biic-status-' + newStatus);
            statusBadge.text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
            
            // Update data attribute
            selectElement.data('current-status', newStatus);
            
            // Show success message
            biicShowNotice('Lead status updated successfully', 'success');
        } else {
            // Revert to original status
            selectElement.val(originalStatus);
            biicShowNotice('Failed to update lead status', 'error');
        }
    }).fail(function() {
        // Revert to original status
        selectElement.val(originalStatus);
        biicShowNotice('Network error occurred', 'error');
    }).always(function() {
        selectElement.prop('disabled', false);
    });
}

// Show lead details modal
function biicShowLeadDetails(leadId) {
    var $ = jQuery;
    var modal = $('#biic-lead-modal');
    var content = $('#lead-modal-content');
    
    content.html('<div class="biic-loading"><span class="biic-spinner"></span> Loading...</div>');
    modal.show();
    
    $.post(ajaxurl, {
        action: 'biic_get_lead_details',
        lead_id: leadId,
        nonce: biic_admin.nonce
    }, function(response) {
        if (response.success) {
            content.html(response.data.html);
        } else {
            content.html('<div class="biic-error">Failed to load lead details</div>');
        }
    }).fail(function() {
        content.html('<div class="biic-error">Network error occurred</div>');
    });
}

// Show add note modal
function biicShowAddNoteModal(leadId) {
    var $ = jQuery;
    $('#note-lead-id').val(leadId);
    $('#lead-note').val('');
    $('#biic-note-modal').show();
    $('#lead-note').focus();
}

// Submit note
function biicSubmitNote() {
    var $ = jQuery;
    var form = $('#add-note-form');
    var submitBtn = form.find('button[type="submit"]');
    var originalText = submitBtn.text();
    
    submitBtn.prop('disabled', true).text('Adding...');
    
    $.post(ajaxurl, {
        action: 'biic_add_lead_note',
        lead_id: $('#note-lead-id').val(),
        note: $('#lead-note').val(),
        nonce: biic_admin.nonce
    }, function(response) {
        if (response.success) {
            $('#biic-note-modal').hide();
            biicShowNotice('Note added successfully', 'success');
        } else {
            biicShowNotice('Failed to add note', 'error');
        }
    }).fail(function() {
        biicShowNotice('Network error occurred', 'error');
    }).always(function() {
        submitBtn.prop('disabled', false).text(originalText);
    });
}

// Apply filters
function biicApplyFilters() {
    var params = new URLSearchParams();
    
    var status = document.getElementById('lead-status-filter').value;
    var course = document.getElementById('course-filter').value;
    var dateFrom = document.getElementById('date-from').value;
    var dateTo = document.getElementById('date-to').value;
    var search = document.getElementById('leads-search').value;
    
    if (status) params.append('status', status);
    if (course) params.append('course_interest', course);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    if (search) params.append('search', search);
    
    var newUrl = window.location.pathname + '?page=biic-leads&' + params.toString();
    window.location.href = newUrl;
}

// Clear filters
function biicClearFilters() {
    window.location.href = window.location.pathname + '?page=biic-leads';
}

// Search leads
function biicSearchLeads() {
    biicApplyFilters();
}

// Export leads
function biicExportLeads() {
    var params = new URLSearchParams(window.location.search);
    params.append('export', 'csv');
    window.open(window.location.pathname + '?' + params.toString(), '_blank');
}

// Update bulk selection
function biicUpdateBulkSelection() {
    var selectedCount = jQuery('.lead-checkbox:checked').length;
    jQuery('#selected-count').text(selectedCount);
    
    if (selectedCount > 0) {
        jQuery('.biic-header-actions button[onclick="biicBulkActions()"]').removeClass('button-secondary').addClass('button-primary');
    } else {
        jQuery('.biic-header-actions button[onclick="biicBulkActions()"]').removeClass('button-primary').addClass('button-secondary');
    }
}

// Show bulk actions modal
function biicBulkActions() {
    var selectedCount = jQuery('.lead-checkbox:checked').length;
    
    if (selectedCount === 0) {
        biicShowNotice('Please select leads first', 'warning');
        return;
    }
    
    jQuery('#biic-bulk-modal').show();
}

// Show notification
function biicShowNotice(message, type) {
    // Implementation for showing admin notices
    var noticeClass = 'notice notice-' + type;
    var notice = jQuery('<div class="' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
    
    jQuery('.biic-admin-wrap').prepend(notice);
    
    setTimeout(function() {
        notice.fadeOut();
    }, 5000);
}

// Sort leads
function biicSortLeads(field) {
    var currentUrl = new URL(window.location);
    var currentSort = currentUrl.searchParams.get('orderby');
    var currentOrder = currentUrl.searchParams.get('order');
    
    if (currentSort === field && currentOrder === 'asc') {
        currentUrl.searchParams.set('order', 'desc');
    } else {
        currentUrl.searchParams.set('order', 'asc');
    }
    
    currentUrl.searchParams.set('orderby', field);
    window.location.href = currentUrl.toString();
}
</script>

<?php
/**
 * Helper functions for the view
 */

function count_leads_by_status($lead_stats, $status) {
    if (!isset($lead_stats['leads_by_status'])) return 0;
    
    foreach ($lead_stats['leads_by_status'] as $stat) {
        if ($stat->lead_status === $status) {
            return $stat->count;
        }
    }
    return 0;
}

function get_score_class($score) {
    if ($score >= 80) return 'hot';
    if ($score >= 60) return 'warm';
    if ($score >= 40) return 'medium';
    return 'cold';
}

function get_lead_tier($score) {
    if ($score >= 80) return 'Hot';
    if ($score >= 60) return 'Warm';
    if ($score >= 40) return 'Medium';
    return 'Cold';
}
?>