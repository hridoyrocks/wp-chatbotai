<?php
/**
 * Admin Conversations Management View
 
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get conversations data
$conversations = isset($conversations) ? $conversations : array();
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;
?>

<div class="wrap biic-admin-wrap">
    
    <!-- Header -->
    <div class="biic-admin-header">
        <div class="biic-header-content">
            <h1 class="biic-page-title">
                <span class="biic-logo">💬</span>
                <?php esc_html_e('Conversations Management', 'banglay-ielts-chatbot'); ?>
            </h1>
            <div class="biic-header-actions">
                <button type="button" class="button button-secondary" onclick="biicExportConversations()">
                    <span class="dashicons dashicons-download"></span>
                    <?php esc_html_e('Export Conversations', 'banglay-ielts-chatbot'); ?>
                </button>
                <button type="button" class="button button-primary" onclick="biicBulkConversationActions()">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <?php esc_html_e('Bulk Actions', 'banglay-ielts-chatbot'); ?>
                </button>
            </div>
        </div>
        
        <!-- Conversation Statistics -->
        <div class="biic-conversations-stats">
            <div class="biic-stat-item">
                <span class="biic-stat-icon">💬</span>
                <div class="biic-stat-content">
                    <span class="biic-stat-label"><?php esc_html_e('Total Conversations', 'banglay-ielts-chatbot'); ?></span>
                    <span class="biic-stat-value"><?php echo esc_html(count($conversations)); ?></span>
                </div>
            </div>
            
            <div class="biic-stat-item active">
                <span class="biic-stat-icon">🟢</span>
                <div class="biic-stat-content">
                    <span class="biic-stat-label"><?php esc_html_e('Active Sessions', 'banglay-ielts-chatbot'); ?></span>
                    <span class="biic-stat-value" id="active-sessions-count">0</span>
                </div>
            </div>
            
            <div class="biic-stat-item average">
                <span class="biic-stat-icon">⏱️</span>
                <div class="biic-stat-content">
                    <span class="biic-stat-label"><?php esc_html_e('Avg. Duration', 'banglay-ielts-chatbot'); ?></span>
                    <span class="biic-stat-value">4.2 min</span>
                </div>
            </div>
            
            <div class="biic-stat-item engagement">
                <span class="biic-stat-icon">📊</span>
                <div class="biic-stat-content">
                    <span class="biic-stat-label"><?php esc_html_e('Avg. Messages', 'banglay-ielts-chatbot'); ?></span>
                    <span class="biic-stat-value">8.5</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters and Search -->
    <div class="biic-conversations-filters">
        <div class="biic-filters-row">
            <div class="biic-filter-group">
                <label for="conversation-status-filter"><?php esc_html_e('Status:', 'banglay-ielts-chatbot'); ?></label>
                <select id="conversation-status-filter" name="status">
                    <option value=""><?php esc_html_e('All Status', 'banglay-ielts-chatbot'); ?></option>
                    <option value="active" <?php selected($_GET['status'] ?? '', 'active'); ?>><?php esc_html_e('Active', 'banglay-ielts-chatbot'); ?></option>
                    <option value="completed" <?php selected($_GET['status'] ?? '', 'completed'); ?>><?php esc_html_e('Completed', 'banglay-ielts-chatbot'); ?></option>
                    <option value="abandoned" <?php selected($_GET['status'] ?? '', 'abandoned'); ?>><?php esc_html_e('Abandoned', 'banglay-ielts-chatbot'); ?></option>
                </select>
            </div>
            
            <div class="biic-filter-group">
                <label for="lead-status-filter"><?php esc_html_e('Lead Status:', 'banglay-ielts-chatbot'); ?></label>
                <select id="lead-status-filter" name="lead_status">
                    <option value=""><?php esc_html_e('All Leads', 'banglay-ielts-chatbot'); ?></option>
                    <option value="hot" <?php selected($_GET['lead_status'] ?? '', 'hot'); ?>><?php esc_html_e('Hot', 'banglay-ielts-chatbot'); ?></option>
                    <option value="warm" <?php selected($_GET['lead_status'] ?? '', 'warm'); ?>><?php esc_html_e('Warm', 'banglay-ielts-chatbot'); ?></option>
                    <option value="cold" <?php selected($_GET['lead_status'] ?? '', 'cold'); ?>><?php esc_html_e('Cold', 'banglay-ielts-chatbot'); ?></option>
                    <option value="converted" <?php selected($_GET['lead_status'] ?? '', 'converted'); ?>><?php esc_html_e('Converted', 'banglay-ielts-chatbot'); ?></option>
                </select>
            </div>
            
            <div class="biic-filter-group">
                <label for="device-filter"><?php esc_html_e('Device:', 'banglay-ielts-chatbot'); ?></label>
                <select id="device-filter" name="device_type">
                    <option value=""><?php esc_html_e('All Devices', 'banglay-ielts-chatbot'); ?></option>
                    <option value="mobile" <?php selected($_GET['device_type'] ?? '', 'mobile'); ?>><?php esc_html_e('Mobile', 'banglay-ielts-chatbot'); ?></option>
                    <option value="desktop" <?php selected($_GET['device_type'] ?? '', 'desktop'); ?>><?php esc_html_e('Desktop', 'banglay-ielts-chatbot'); ?></option>
                    <option value="tablet" <?php selected($_GET['device_type'] ?? '', 'tablet'); ?>><?php esc_html_e('Tablet', 'banglay-ielts-chatbot'); ?></option>
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
                <button type="button" class="button button-primary" onclick="biicApplyConversationFilters()">
                    <span class="dashicons dashicons-filter"></span>
                    <?php esc_html_e('Apply Filters', 'banglay-ielts-chatbot'); ?>
                </button>
                <button type="button" class="button button-secondary" onclick="biicClearConversationFilters()">
                    <?php esc_html_e('Clear', 'banglay-ielts-chatbot'); ?>
                </button>
            </div>
        </div>
        
        <div class="biic-search-row">
            <div class="biic-search-box">
                <input type="text" id="conversations-search" placeholder="<?php esc_attr_e('Search by session ID, message content, or user info...', 'banglay-ielts-chatbot'); ?>" value="<?php echo esc_attr($_GET['search'] ?? ''); ?>">
                <button type="button" class="biic-search-btn" onclick="biicSearchConversations()">
                    <span class="dashicons dashicons-search"></span>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Conversations List -->
    <div class="biic-conversations-list">
        <?php if (!empty($conversations)): ?>
            <?php foreach ($conversations as $conversation): ?>
                <div class="biic-conversation-item" data-session-id="<?php echo esc_attr($conversation->session_id); ?>">
                    
                    <div class="biic-conversation-header">
                        <div class="biic-conversation-main-info">
                            <div class="biic-conversation-avatar">
                                <?php if ($conversation->lead_name): ?>
                                    <?php echo esc_html(strtoupper(substr($conversation->lead_name, 0, 2))); ?>
                                <?php else: ?>
                                    👤
                                <?php endif; ?>
                            </div>
                            
                            <div class="biic-conversation-details">
                                <div class="biic-conversation-title">
                                    <h4>
                                        <?php if ($conversation->lead_name): ?>
                                            <?php echo esc_html($conversation->lead_name); ?>
                                        <?php else: ?>
                                            <?php esc_html_e('Anonymous User', 'banglay-ielts-chatbot'); ?>
                                        <?php endif; ?>
                                    </h4>
                                    
                                    <div class="biic-conversation-badges">
                                        <span class="biic-badge biic-status-<?php echo esc_attr($conversation->is_active ? 'active' : 'completed'); ?>">
                                            <?php echo $conversation->is_active ? esc_html__('Active', 'banglay-ielts-chatbot') : esc_html__('Completed', 'banglay-ielts-chatbot'); ?>
                                        </span>
                                        
                                        <?php if ($conversation->lead_score >= 80): ?>
                                            <span class="biic-badge biic-lead-hot">🔥 Hot Lead</span>
                                        <?php elseif ($conversation->lead_score >= 50): ?>
                                            <span class="biic-badge biic-lead-warm">🌡️ Warm Lead</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="biic-conversation-meta">
                                    <span class="biic-meta-item">
                                        <span class="dashicons dashicons-clock"></span>
                                        <?php echo esc_html(human_time_diff(strtotime($conversation->started_at), current_time('timestamp'))); ?> ago
                                    </span>
                                    
                                    <span class="biic-meta-item">
                                        <span class="dashicons dashicons-format-chat"></span>
                                        <?php echo esc_html($conversation->total_messages); ?> messages
                                    </span>
                                    
                                    <span class="biic-meta-item">
                                        <span class="dashicons dashicons-location"></span>
                                        <?php echo esc_html($conversation->location ?: 'Unknown'); ?>
                                    </span>
                                    
                                    <span class="biic-meta-item">
                                        <?php 
                                        $device_icon = $conversation->device_type === 'mobile' ? '📱' : 
                                                      ($conversation->device_type === 'tablet' ? '📟' : '💻');
                                        echo $device_icon;
                                        ?>
                                        <?php echo esc_html(ucfirst($conversation->device_type ?: 'unknown')); ?>
                                    </span>
                                </div>
                                
                                <?php if ($conversation->lead_phone || $conversation->lead_email): ?>
                                    <div class="biic-conversation-contact">
                                        <?php if ($conversation->lead_phone): ?>
                                            <a href="tel:<?php echo esc_attr($conversation->lead_phone); ?>" class="biic-contact-link">
                                                <span class="dashicons dashicons-phone"></span>
                                                <?php echo esc_html($conversation->lead_phone); ?>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($conversation->lead_email): ?>
                                            <a href="mailto:<?php echo esc_attr($conversation->lead_email); ?>" class="biic-contact-link">
                                                <span class="dashicons dashicons-email"></span>
                                                <?php echo esc_html($conversation->lead_email); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="biic-conversation-score">
                            <div class="biic-score-circle <?php echo $this->get_score_class($conversation->lead_score); ?>">
                                <span class="biic-score-number"><?php echo esc_html($conversation->lead_score ?? 0); ?></span>
                            </div>
                            <div class="biic-score-label">Lead Score</div>
                        </div>
                        
                        <div class="biic-conversation-actions">
                            <button type="button" class="button button-small biic-view-conversation" data-session-id="<?php echo esc_attr($conversation->session_id); ?>" title="<?php esc_attr_e('View Conversation', 'banglay-ielts-chatbot'); ?>">
                                <span class="dashicons dashicons-visibility"></span>
                            </button>
                            
                            <button type="button" class="button button-small biic-export-conversation" data-session-id="<?php echo esc_attr($conversation->session_id); ?>" title="<?php esc_attr_e('Export', 'banglay-ielts-chatbot'); ?>">
                                <span class="dashicons dashicons-download"></span>
                            </button>
                            
                            <?php if ($conversation->lead_phone): ?>
                                <a href="tel:<?php echo esc_attr($conversation->lead_phone); ?>" class="button button-small biic-call-lead" title="<?php esc_attr_e('Call Lead', 'banglay-ielts-chatbot'); ?>">
                                    <span class="dashicons dashicons-phone"></span>
                                </a>
                            <?php endif; ?>
                            
                            <div class="biic-actions-more">
                                <button type="button" class="button button-small biic-more-actions" data-session-id="<?php echo esc_attr($conversation->session_id); ?>">
                                    <span class="dashicons dashicons-ellipsis"></span>
                                </button>
                                <div class="biic-actions-dropdown">
                                    <a href="#" class="biic-add-note" data-session-id="<?php echo esc_attr($conversation->session_id); ?>">
                                        <span class="dashicons dashicons-edit-page"></span>
                                        <?php esc_html_e('Add Note', 'banglay-ielts-chatbot'); ?>
                                    </a>
                                    <a href="#" class="biic-create-lead" data-session-id="<?php echo esc_attr($conversation->session_id); ?>">
                                        <span class="dashicons dashicons-admin-users"></span>
                                        <?php esc_html_e('Create Lead', 'banglay-ielts-chatbot'); ?>
                                    </a>
                                    <a href="#" class="biic-replay-conversation" data-session-id="<?php echo esc_attr($conversation->session_id); ?>">
                                        <span class="dashicons dashicons-controls-play"></span>
                                        <?php esc_html_e('Replay Conversation', 'banglay-ielts-chatbot'); ?>
                                    </a>
                                    <hr>
                                    <a href="#" class="biic-delete-conversation biic-text-danger" data-session-id="<?php echo esc_attr($conversation->session_id); ?>">
                                        <span class="dashicons dashicons-trash"></span>
                                        <?php esc_html_e('Delete Conversation', 'banglay-ielts-chatbot'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Collapsible conversation preview -->
                    <div class="biic-conversation-preview" id="preview-<?php echo esc_attr($conversation->session_id); ?>" style="display: none;">
                        <div class="biic-preview-loading">
                            <span class="biic-spinner"></span>
                            <?php esc_html_e('Loading conversation...', 'banglay-ielts-chatbot'); ?>
                        </div>
                    </div>
                    
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="biic-no-conversations">
                <div class="biic-empty-state">
                    <span class="biic-empty-icon">💬</span>
                    <h3><?php esc_html_e('No conversations found', 'banglay-ielts-chatbot'); ?></h3>
                    <p><?php esc_html_e('Conversations will appear here as users interact with your chatbot.', 'banglay-ielts-chatbot'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=biic-settings'); ?>" class="button button-primary">
                        <?php esc_html_e('Configure Chatbot', 'banglay-ielts-chatbot'); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if (!empty($conversations) && count($conversations) >= $per_page): ?>
        <div class="biic-pagination">
            <div class="biic-pagination-info">
                <?php printf(esc_html__('Showing %d conversations', 'banglay-ielts-chatbot'), count($conversations)); ?>
            </div>
            <div class="biic-pagination-controls">
                <?php if ($current_page > 1): ?>
                    <a href="<?php echo add_query_arg('paged', $current_page - 1); ?>" class="button">
                        <?php esc_html_e('Previous', 'banglay-ielts-chatbot'); ?>
                    </a>
                <?php endif; ?>
                
                <span class="biic-page-info">
                    <?php printf(esc_html__('Page %d', 'banglay-ielts-chatbot'), $current_page); ?>
                </span>
                
                <?php if (count($conversations) >= $per_page): ?>
                    <a href="<?php echo add_query_arg('paged', $current_page + 1); ?>" class="button">
                        <?php esc_html_e('Next', 'banglay-ielts-chatbot'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
</div>

<!-- Conversation Detail Modal -->
<div id="biic-conversation-modal" class="biic-modal" style="display: none;">
    <div class="biic-modal-content biic-modal-large">
        <div class="biic-modal-header">
            <h2><?php esc_html_e('Conversation Details', 'banglay-ielts-chatbot'); ?></h2>
            <button type="button" class="biic-modal-close">&times;</button>
        </div>
        <div class="biic-modal-body" id="conversation-modal-content">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div id="biic-conversation-note-modal" class="biic-modal" style="display: none;">
    <div class="biic-modal-content biic-modal-small">
        <div class="biic-modal-header">
            <h3><?php esc_html_e('Add Conversation Note', 'banglay-ielts-chatbot'); ?></h3>
            <button type="button" class="biic-modal-close">&times;</button>
        </div>
        <div class="biic-modal-body">
            <form id="add-conversation-note-form">
                <input type="hidden" id="note-session-id" name="session_id">
                <div class="biic-form-group">
                    <label for="conversation-note"><?php esc_html_e('Note:', 'banglay-ielts-chatbot'); ?></label>
                    <textarea id="conversation-note" name="note" rows="4" class="widefat" placeholder="<?php esc_attr_e('Add your note about this conversation...', 'banglay-ielts-chatbot'); ?>" required></textarea>
                </div>
                <div class="biic-form-actions">
                    <button type="submit" class="button button-primary"><?php esc_html_e('Add Note', 'banglay-ielts-chatbot'); ?></button>
                    <button type="button" class="button button-secondary biic-modal-close"><?php esc_html_e('Cancel', 'banglay-ielts-chatbot'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Initialize conversations management
    biicInitConversationsManagement();
    
    // Auto-refresh active sessions count
    setInterval(biicUpdateActiveSessionsCount, 30000); // Update every 30 seconds
});

// Initialize conversations management functionality
function biicInitConversationsManagement() {
    var $ = jQuery;
    
    // View conversation details
    $('.biic-view-conversation').on('click', function() {
        var sessionId = $(this).data('session-id');
        biicShowConversationDetails(sessionId);
    });
    
    // Export single conversation
    $('.biic-export-conversation').on('click', function() {
        var sessionId = $(this).data('session-id');
        biicExportSingleConversation(sessionId);
    });
    
    // Add conversation note
    $('.biic-add-note').on('click', function(e) {
        e.preventDefault();
        var sessionId = $(this).data('session-id');
        biicShowAddConversationNoteModal(sessionId);
    });
    
    // Delete conversation
    $('.biic-delete-conversation').on('click', function(e) {
        e.preventDefault();
        var sessionId = $(this).data('session-id');
        biicDeleteConversation(sessionId);
    });
    
    // Toggle conversation preview
    $('.biic-conversation-item').on('click', '.biic-conversation-header', function(e) {
        if ($(e.target).closest('.biic-conversation-actions').length) return;
        
        var sessionId = $(this).closest('.biic-conversation-item').data('session-id');
        var preview = $('#preview-' + sessionId);
        
        if (preview.is(':visible')) {
            preview.slideUp();
        } else {
            biicLoadConversationPreview(sessionId);
            preview.slideDown();
        }
    });
    
    // Modal close functionality
    $('.biic-modal-close').on('click', function() {
        $(this).closest('.biic-modal').hide();
    });
    
    // Add note form submission
    $('#add-conversation-note-form').on('submit', function(e) {
        e.preventDefault();
        biicSubmitConversationNote();
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

// Show conversation details modal
function biicShowConversationDetails(sessionId) {
    var $ = jQuery;
    var modal = $('#biic-conversation-modal');
    var content = $('#conversation-modal-content');
    
    content.html('<div class="biic-loading"><span class="biic-spinner"></span> Loading conversation details...</div>');
    modal.show();
    
    $.post(ajaxurl, {
        action: 'biic_get_conversation_details',
        session_id: sessionId,
        nonce: biic_admin.nonce
    }, function(response) {
        if (response.success) {
            content.html(response.data.html);
        } else {
            content.html('<div class="biic-error">Failed to load conversation details</div>');
        }
    }).fail(function() {
        content.html('<div class="biic-error">Network error occurred</div>');
    });
}

// Load conversation preview
function biicLoadConversationPreview(sessionId) {
    var $ = jQuery;
    var preview = $('#preview-' + sessionId);
    
    $.post(ajaxurl, {
        action: 'biic_get_conversation_preview',
        session_id: sessionId,
        nonce: biic_admin.nonce
    }, function(response) {
        if (response.success) {
            preview.html(response.data.html);
        } else {
            preview.html('<div class="biic-error">Failed to load preview</div>');
        }
    }).fail(function() {
        preview.html('<div class="biic-error">Network error occurred</div>');
    });
}

// Show add conversation note modal
function biicShowAddConversationNoteModal(sessionId) {
    var $ = jQuery;
    $('#note-session-id').val(sessionId);
    $('#conversation-note').val('');
    $('#biic-conversation-note-modal').show();
    $('#conversation-note').focus();
}

// Submit conversation note
function biicSubmitConversationNote() {
    var $ = jQuery;
    var form = $('#add-conversation-note-form');
    var submitBtn = form.find('button[type="submit"]');
    var originalText = submitBtn.text();
    
    submitBtn.prop('disabled', true).text('Adding...');
    
    $.post(ajaxurl, {
        action: 'biic_add_conversation_note',
        session_id: $('#note-session-id').val(),
        note: $('#conversation-note').val(),
        nonce: biic_admin.nonce
    }, function(response) {
        if (response.success) {
            $('#biic-conversation-note-modal').hide();
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

// Delete conversation
function biicDeleteConversation(sessionId) {
    var $ = jQuery;
    
    if (!confirm('Are you sure you want to delete this conversation? This action cannot be undone.')) {
        return;
    }
    
    $.post(ajaxurl, {
        action: 'biic_delete_conversation',
        session_id: sessionId,
        nonce: biic_admin.nonce
    }, function(response) {
        if (response.success) {
            $('[data-session-id="' + sessionId + '"]').fadeOut(function() {
                $(this).remove();
            });
            biicShowNotice('Conversation deleted successfully', 'success');
        } else {
            biicShowNotice('Failed to delete conversation', 'error');
        }
    }).fail(function() {
        biicShowNotice('Network error occurred', 'error');
    });
}

// Export single conversation
function biicExportSingleConversation(sessionId) {
    var params = new URLSearchParams();
    params.append('export', 'conversation');
    params.append('session_id', sessionId);
    window.open(window.location.pathname + '?' + params.toString(), '_blank');
}

// Apply conversation filters
function biicApplyConversationFilters() {
    var params = new URLSearchParams();
    
    var status = document.getElementById('conversation-status-filter').value;
    var leadStatus = document.getElementById('lead-status-filter').value;
    var deviceType = document.getElementById('device-filter').value;
    var dateFrom = document.getElementById('date-from').value;
    var dateTo = document.getElementById('date-to').value;
    var search = document.getElementById('conversations-search').value;
    
    if (status) params.append('status', status);
    if (leadStatus) params.append('lead_status', leadStatus);
    if (deviceType) params.append('device_type', deviceType);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    if (search) params.append('search', search);
    
    var newUrl = window.location.pathname + '?page=biic-conversations&' + params.toString();
    window.location.href = newUrl;
}

// Clear conversation filters
function biicClearConversationFilters() {
    window.location.href = window.location.pathname + '?page=biic-conversations';
}

// Search conversations
function biicSearchConversations() {
    biicApplyConversationFilters();
}

// Export all conversations
function biicExportConversations() {
    var params = new URLSearchParams(window.location.search);
    params.append('export', 'conversations');
    window.open(window.location.pathname + '?' + params.toString(), '_blank');
}

// Update active sessions count
function biicUpdateActiveSessionsCount() {
    var $ = jQuery;
    
    $.post(ajaxurl, {
        action: 'biic_get_active_sessions_count',
        nonce: biic_admin.nonce
    }, function(response) {
        if (response.success) {
            $('#active-sessions-count').text(response.data.count);
        }
    });
}

// Show notification
function biicShowNotice(message, type) {
    var noticeClass = 'notice notice-' + type;
    var notice = jQuery('<div class="' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
    
    jQuery('.biic-admin-wrap').prepend(notice);
    
    setTimeout(function() {
        notice.fadeOut();
    }, 5000);
}

// Bulk conversation actions (placeholder)
function biicBulkConversationActions() {
    alert('Bulk actions feature coming soon!');
}
</script>

<style>
/* Conversations specific styles */
.biic-conversations-stats {
    padding: 20px 32px;
    background: linear-gradient(135deg, var(--biic-admin-gray-50) 0%, var(--biic-admin-white) 100%);
    display: flex;
    gap: 32px;
}

.biic-conversations-filters {
    padding: 20px 32px;
    background: #f8f9fa;
    border-top: 1px solid #e5e7eb;
}

.biic-filters-row {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 16px;
}

.biic-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.biic-filter-group label {
    font-size: 12px;
    font-weight: 500;
    color: #6b7280;
}

.biic-filter-group input,
.biic-filter-group select {
    padding: 6px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
}

.biic-search-row {
    display: flex;
    justify-content: center;
}

.biic-search-box {
    display: flex;
    max-width: 500px;
    width: 100%;
}

.biic-search-box input {
    flex: 1;
    padding: 8px 16px;
    border: 1px solid #d1d5db;
    border-right: none;
    border-radius: 8px 0 0 8px;
    font-size: 14px;
}

.biic-search-btn {
    padding: 8px 16px;
    background: var(--biic-admin-primary);
    color: white;
    border: 1px solid var(--biic-admin-primary);
    border-radius: 0 8px 8px 0;
    cursor: pointer;
}

.biic-conversations-list {
    padding: 24px;
}

.biic-conversation-item {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    margin-bottom: 16px;
    overflow: hidden;
    transition: all 0.2s ease;
}

.biic-conversation-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-color: var(--biic-admin-primary);
}

.biic-conversation-header {
    padding: 20px 24px;
    display: flex;
    align-items: center;
    gap: 20px;
    cursor: pointer;
}

.biic-conversation-main-info {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 16px;
}

.biic-conversation-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--biic-admin-primary) 0%, var(--biic-admin-primary-dark) 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 16px;
    flex-shrink: 0;
}

.biic-conversation-details {
    flex: 1;
}

.biic-conversation-title {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
}

.biic-conversation-title h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
}

.biic-conversation-badges {
    display: flex;
    gap: 8px;
}

.biic-badge {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.biic-status-active {
    background: rgba(34, 197, 94, 0.1);
    color: #16a34a;
}

.biic-status-completed {
    background: rgba(107, 114, 128, 0.1);
    color: #6b7280;
}

.biic-lead-hot {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
}

.biic-lead-warm {
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
}

.biic-conversation-meta {
    display: flex;
    gap: 16px;
    margin-bottom: 8px;
}

.biic-meta-item {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: #6b7280;
}

.biic-conversation-contact {
    display: flex;
    gap: 12px;
}

.biic-contact-link {
    display: flex;
    align-items: center;
    gap: 4px;
    text-decoration: none;
    font-size: 12px;
    color: var(--biic-admin-primary);
    padding: 4px 8px;
    border-radius: 4px;
    background: rgba(229, 62, 62, 0.1);
}

.biic-contact-link:hover {
    background: rgba(229, 62, 62, 0.2);
    text-decoration: none;
}

.biic-conversation-score {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.biic-score-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
    color: white;
}

.biic-score-circle.hot {
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
}

.biic-score-circle.warm {
    background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
}

.biic-score-circle.medium {
    background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
}

.biic-score-circle.cold {
    background: linear-gradient(135deg, #6b7280 0%, #9ca3af 100%);
}

.biic-score-label {
    font-size: 10px;
    color: #6b7280;
    text-align: center;
}

.biic-conversation-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.biic-conversation-actions .button {
    padding: 6px 10px;
    min-height: auto;
}

.biic-actions-more {
    position: relative;
}

.biic-actions-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    z-index: 10;
    min-width: 180px;
    display: none;
}

.biic-actions-dropdown a {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    text-decoration: none;
    color: #374151;
    font-size: 13px;
}

.biic-actions-dropdown a:hover {
    background: #f3f4f6;
}

.biic-actions-dropdown hr {
    margin: 4px 0;
    border: none;
    border-top: 1px solid #e5e7eb;
}

.biic-text-danger {
    color: #dc2626 !important;
}

.biic-conversation-preview {
    border-top: 1px solid #e5e7eb;
    padding: 20px 24px;
    background: #f9fafb;
}

.biic-preview-loading {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #6b7280;
}

.biic-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid #e5e7eb;
    border-top: 2px solid var(--biic-admin-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.biic-no-conversations {
    text-align: center;
    padding: 60px 20px;
}

.biic-empty-state {
    max-width: 400px;
    margin: 0 auto;
}

.biic-empty-icon {
    font-size: 64px;
    margin-bottom: 16px;
    display: block;
}

.biic-empty-state h3 {
    margin-bottom: 8px;
    color: #374151;
}

.biic-empty-state p {
    color: #6b7280;
    margin-bottom: 24px;
}

.biic-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-top: 1px solid #e5e7eb;
}

.biic-pagination-controls {
    display: flex;
    gap: 8px;
    align-items: center;
}

.biic-page-info {
    padding: 0 16px;
    color: #6b7280;
    font-size: 14px;
}

/* Modal styles */
.biic-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.biic-modal-content {
    background: white;
    border-radius: 12px;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.biic-modal-content.biic-modal-large {
    max-width: 800px;
}

.biic-modal-content.biic-modal-small {
    max-width: 400px;
}

.biic-modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.biic-modal-header h2,
.biic-modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
}

.biic-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #6b7280;
    cursor: pointer;
    padding: 4px;
    line-height: 1;
}

.biic-modal-close:hover {
    color: #374151;
}

.biic-modal-body {
    padding: 24px;
    max-height: 60vh;
    overflow-y: auto;
}

.biic-form-group {
    margin-bottom: 16px;
}

.biic-form-group label {
    display: block;
    margin-bottom: 4px;
    font-weight: 500;
    color: #374151;
}

.biic-form-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    margin-top: 20px;
}

/* Responsive design */
@media (max-width: 768px) {
    .biic-filters-row {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }
    
    .biic-conversation-header {
        flex-direction: column;
        align-items: stretch;
        gap: 16px;
    }
    
    .biic-conversation-main-info {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .biic-conversation-meta {
        flex-wrap: wrap;
    }
    
    .biic-pagination {
        flex-direction: column;
        gap: 16px;
    }
}
</style>

<?php
/**
 * Helper functions for the conversations view
 */

function get_score_class($score) {
    if ($score >= 80) return 'hot';
    if ($score >= 60) return 'warm';
    if ($score >= 40) return 'medium';
    return 'cold';
}
?>