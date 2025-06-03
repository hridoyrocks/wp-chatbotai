<?php
/**
 * Admin Sidebar Partial
 * admin/views/partials/sidebar.php
 */
?>

<div class="biic-admin-sidebar">
    <div class="biic-sidebar-content">
        
        <!-- Quick Actions -->
        <div class="biic-sidebar-section">
            <h3 class="biic-sidebar-title">
                <span class="dashicons dashicons-performance"></span>
                <?php esc_html_e('দ্রুত কাজ', 'banglay-ielts-chatbot'); ?>
            </h3>
            
            <div class="biic-quick-actions">
                <a href="<?php echo admin_url('admin.php?page=biic-conversations'); ?>" class="biic-quick-action">
                    <span class="biic-action-icon">💬</span>
                    <span class="biic-action-text"><?php esc_html_e('নতুন কথোপকথন', 'banglay-ielts-chatbot'); ?></span>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=biic-leads'); ?>" class="biic-quick-action">
                    <span class="biic-action-icon">🎯</span>
                    <span class="biic-action-text"><?php esc_html_e('লিড পরিচালনা', 'banglay-ielts-chatbot'); ?></span>
                </a>
                
                <button type="button" class="biic-quick-action" onclick="biicTestChatbot()">
                    <span class="biic-action-icon">🧪</span>
                    <span class="biic-action-text"><?php esc_html_e('চ্যাটবট টেস্ট', 'banglay-ielts-chatbot'); ?></span>
                </button>
                
                <button type="button" class="biic-quick-action" onclick="biicExportData()">
                    <span class="biic-action-icon">📥</span>
                    <span class="biic-action-text"><?php esc_html_e('ডেটা এক্সপোর্ট', 'banglay-ielts-chatbot'); ?></span>
                </button>
            </div>
        </div>
        
        <!-- System Status -->
        <div class="biic-sidebar-section">
            <h3 class="biic-sidebar-title">
                <span class="dashicons dashicons-admin-tools"></span>
                <?php esc_html_e('সিস্টেম স্ট্যাটাস', 'banglay-ielts-chatbot'); ?>
            </h3>
            
            <div class="biic-system-status">
                <div class="biic-status-item">
                    <span class="biic-status-indicator active"></span>
                    <span class="biic-status-label"><?php esc_html_e('চ্যাটবট', 'banglay-ielts-chatbot'); ?></span>
                    <span class="biic-status-value"><?php echo get_option('biic_chatbot_enabled', true) ? 'সক্রিয়' : 'নিষ্ক্রিয়'; ?></span>
                </div>
                
                <div class="biic-status-item">
                    <span class="biic-status-indicator <?php echo !empty(get_option('biic_openai_api_key')) ? 'active' : 'inactive'; ?>"></span>
                    <span class="biic-status-label"><?php esc_html_e('AI', 'banglay-ielts-chatbot'); ?></span>
                    <span class="biic-status-value"><?php echo !empty(get_option('biic_openai_api_key')) ? 'সংযুক্ত' : 'বিচ্ছিন্ন'; ?></span>
                </div>
                
                <div class="biic-status-item">
                    <span class="biic-status-indicator active"></span>
                    <span class="biic-status-label"><?php esc_html_e('ডাটাবেস', 'banglay-ielts-chatbot'); ?></span>
                    <span class="biic-status-value">সংযুক্ত</span>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="biic-sidebar-section">
            <h3 class="biic-sidebar-title">
                <span class="dashicons dashicons-clock"></span>
                <?php esc_html_e('সাম্প্রতিক কার্যকলাপ', 'banglay-ielts-chatbot'); ?>
            </h3>
            
            <div class="biic-recent-activity" id="sidebar-recent-activity">
                <div class="biic-activity-loading">
                    <span class="biic-spinner"></span>
                    <?php esc_html_e('লোড হচ্ছে...', 'banglay-ielts-chatbot'); ?>
                </div>
            </div>
        </div>
        
        <!-- Help & Support -->
        <div class="biic-sidebar-section">
            <h3 class="biic-sidebar-title">
                <span class="dashicons dashicons-sos"></span>
                <?php esc_html_e('সাহায্য ও সাপোর্ট', 'banglay-ielts-chatbot'); ?>
            </h3>
            
            <div class="biic-help-links">
                <a href="https://banglayelts.com/support" target="_blank" class="biic-help-link">
                    <span class="dashicons dashicons-book"></span>
                    <?php esc_html_e('ডকুমেন্টেশন', 'banglay-ielts-chatbot'); ?>
                </a>
                
                <a href="mailto:support@banglayelts.com" class="biic-help-link">
                    <span class="dashicons dashicons-email"></span>
                    <?php esc_html_e('ইমেইল সাপোর্ট', 'banglay-ielts-chatbot'); ?>
                </a>
                
                <a href="https://banglayelts.com" target="_blank" class="biic-help-link">
                    <span class="dashicons dashicons-external"></span>
                    <?php esc_html_e('ওয়েবসাইট', 'banglay-ielts-chatbot'); ?>
                </a>
            </div>
        </div>
        
    </div>
</div>

<style>
.biic-admin-sidebar {
    width: 280px;
    background: #f8f9fa;
    border-right: 1px solid #e5e7eb;
    height: 100vh;
    overflow-y: auto;
    position: fixed;
    left: 0;
    top: 60px;
    z-index: 999;
}

.biic-sidebar-content {
    padding: 20px;
}

.biic-sidebar-section {
    margin-bottom: 30px;
}

.biic-sidebar-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e5e7eb;
}

.biic-quick-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.biic-quick-action {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    color: #374151;
    text-decoration: none;
    font-size: 13px;
    transition: all 0.2s ease;
    cursor: pointer;
}

.biic-quick-action:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
    color: #374151;
    text-decoration: none;
    transform: translateY(-1px);
}

.biic-action-icon {
    font-size: 16px;
}

.biic-system-status {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.biic-status-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px;
    background: white;
    border-radius: 6px;
    font-size: 12px;
}

.biic-status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #9ca3af;
}

.biic-status-indicator.active {
    background: #10b981;
}

.biic-status-indicator.inactive {
    background: #ef4444;
}

.biic-status-label {
    flex: 1;
    font-weight: 500;
}

.biic-status-value {
    color: #6b7280;
}

.biic-recent-activity {
    max-height: 200px;
    overflow-y: auto;
}

.biic-activity-loading {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 16px;
    color: #6b7280;
    font-size: 12px;
}

.biic-help-links {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.biic-help-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    color: #374151;
    text-decoration: none;
    font-size: 12px;
    border-radius: 6px;
    transition: background 0.2s ease;
}

.biic-help-link:hover {
    background: #e5e7eb;
    color: #374151;
    text-decoration: none;
}

.biic-spinner {
    width: 12px;
    height: 12px;
    border: 2px solid #e5e7eb;
    border-top: 2px solid #e53e3e;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Load recent activity
    function loadRecentActivity() {
        $.post(ajaxurl, {
            action: 'biic_get_recent_activity',
            nonce: biic_admin.nonce
        }, function(response) {
            if (response.success) {
                const activities = response.data;
                let html = '';
                
                activities.forEach(function(activity) {
                    html += `
                        <div class="biic-activity-item">
                            <span class="biic-activity-icon">${activity.icon}</span>
                            <div class="biic-activity-content">
                                <div class="biic-activity-text">${activity.text}</div>
                                <div class="biic-activity-time">${activity.time}</div>
                            </div>
                        </div>
                    `;
                });
                
                $('#sidebar-recent-activity').html(html || '<div class="biic-no-activity">কোনো সাম্প্রতিক কার্যকলাপ নেই</div>');
            }
        });
    }
    
    loadRecentActivity();
    setInterval(loadRecentActivity, 60000); // Update every minute
});
</script>