<?php
/**
 * Admin Footer Partial
 * admin/views/partials/footer.php
 */
?>

<div class="biic-admin-footer">
    <div class="biic-footer-content">
        <div class="biic-footer-left">
            <div class="biic-footer-branding">
                <span class="biic-footer-logo">🤖</span>
                <span class="biic-footer-text">Banglay IELTS Chatbot</span>
                <span class="biic-footer-version">v<?php echo BIIC_VERSION; ?></span>
            </div>
            
            <div class="biic-footer-stats">
                <div class="biic-footer-stat">
                    <span class="biic-stat-label"><?php esc_html_e('মোট কথোপকথন:', 'banglay-ielts-chatbot'); ?></span>
                    <span class="biic-stat-value" id="footer-total-conversations">0</span>
                </div>
                <div class="biic-footer-stat">
                    <span class="biic-stat-label"><?php esc_html_e('মোট লিড:', 'banglay-ielts-chatbot'); ?></span>
                    <span class="biic-stat-value" id="footer-total-leads">0</span>
                </div>
            </div>
        </div>
        
        <div class="biic-footer-center">
            <div class="biic-footer-made-with">
                Made with <span class="biic-footer-heart">❤️</span> by 
                <a href="https://banglayelts.com" target="_blank" class="biic-footer-link">Love Rocks</a>
            </div>
            
            <div class="biic-footer-copyright">
                © <?php echo date('Y'); ?> Banglay IELTS. <?php esc_html_e('সর্বস্বত্ব সংরক্ষিত।', 'banglay-ielts-chatbot'); ?>
            </div>
        </div>
        
        <div class="biic-footer-right">
            <div class="biic-footer-actions">
                <button type="button" class="biic-footer-btn" onclick="biicCheckForUpdates()">
                    <span class="dashicons dashicons-update"></span>
                    <?php esc_html_e('আপডেট চেক', 'banglay-ielts-chatbot'); ?>
                </button>
                
                <a href="https://banglayelts.com/support" target="_blank" class="biic-footer-btn">
                    <span class="dashicons dashicons-sos"></span>
                    <?php esc_html_e('সাহায্য', 'banglay-ielts-chatbot'); ?>
                </a>
            </div>
            
            <div class="biic-footer-social">
                <a href="https://facebook.com/banglayelts" target="_blank" class="biic-social-link" title="Facebook">
                    📘
                </a>
                <a href="https://youtube.com/banglayelts" target="_blank" class="biic-social-link" title="YouTube">
                    📹
                </a>
                <a href="mailto:support@banglayelts.com" class="biic-social-link" title="Email">
                    📧
                </a>
            </div>
        </div>
    </div>
    
    <!-- System Status Bar -->
    <div class="biic-status-bar">
        <div class="biic-status-indicator">
            <span class="biic-status-dot active"></span>
            <span class="biic-status-text"><?php esc_html_e('সিস্টেম সচল', 'banglay-ielts-chatbot'); ?></span>
        </div>
        
        <div class="biic-status-info">
            <span class="biic-status-item">
                <?php esc_html_e('শেষ আপডেট:', 'banglay-ielts-chatbot'); ?>
                <span id="last-update-time"><?php echo current_time('H:i'); ?></span>
            </span>
            
            <span class="biic-status-item">
                <?php esc_html_e('সার্ভার সময়:', 'banglay-ielts-chatbot'); ?>
                <span id="server-time"><?php echo current_time('H:i:s'); ?></span>
            </span>
        </div>
    </div>
</div>

<!-- Notifications Container -->
<div class="biic-notifications-container" id="biic-notifications"></div>

<!-- Loading Overlay -->
<div class="biic-loading-overlay" id="biic-loading-overlay" style="display: none;">
    <div class="biic-loading-content">
        <div class="biic-loading-spinner"></div>
        <div class="biic-loading-text"><?php esc_html_e('লোড হচ্ছে...', 'banglay-ielts-chatbot'); ?></div>
    </div>
</div>

<style>
.biic-admin-footer {
    background: #ffffff;
    border-top: 1px solid #e5e7eb;
    margin-top: 40px;
    position: relative;
}

.biic-footer-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 32px;
    flex-wrap: wrap;
    gap: 20px;
}

.biic-footer-left {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.biic-footer-branding {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #374151;
}

.biic-footer-logo {
    font-size: 20px;
}

.biic-footer-version {
    background: #f3f4f6;
    color: #6b7280;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 500;
}

.biic-footer-stats {
    display: flex;
    gap: 20px;
    font-size: 12px;
}

.biic-footer-stat {
    display: flex;
    gap: 4px;
}

.biic-stat-label {
    color: #6b7280;
}

.biic-stat-value {
    font-weight: 600;
    color: #374151;
}

.biic-footer-center {
    text-align: center;
    font-size: 12px;
    color: #6b7280;
}

.biic-footer-made-with {
    margin-bottom: 4px;
}

.biic-footer-heart {
    color: #e53e3e;
    animation: biic-heartbeat 1.5s infinite;
}

.biic-footer-link {
    color: #e53e3e;
    text-decoration: none;
    font-weight: 500;
}

.biic-footer-link:hover {
    text-decoration: underline;
}

.biic-footer-right {
    display: flex;
    flex-direction: column;
    gap: 12px;
    align-items: flex-end;
}

.biic-footer-actions {
    display: flex;
    gap: 8px;
}

.biic-footer-btn {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 6px 12px;
    background: none;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    color: #374151;
    text-decoration: none;
    font-size: 12px;
    transition: all 0.2s ease;
    cursor: pointer;
}

.biic-footer-btn:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
    color: #374151;
    text-decoration: none;
}

.biic-footer-social {
    display: flex;
    gap: 8px;
}

.biic-social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    transition: transform 0.2s ease;
}

.biic-social-link:hover {
    transform: scale(1.1);
}

.biic-status-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 32px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
    font-size: 11px;
    color: #6b7280;
}

.biic-status-indicator {
    display: flex;
    align-items: center;
    gap: 6px;
}

.biic-status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #9ca3af;
}

.biic-status-dot.active {
    background: #10b981;
    animation: biic-pulse 2s infinite;
}

.biic-status-info {
    display: flex;
    gap: 16px;
}

.biic-notifications-container {
    position: fixed;
    top: 32px;
    right: 20px;
    z-index: 10000;
    max-width: 400px;
}

.biic-loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.biic-loading-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    padding: 32px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.biic-loading-spinner {
    width: 32px;
    height: 32px;
    border: 3px solid #f3f4f6;
    border-top: 3px solid #e53e3e;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.biic-loading-text {
    color: #374151;
    font-weight: 500;
}

@keyframes biic-heartbeat {
    0%, 100% { transform: scale(1); }
    25% { transform: scale(1.1); }
    50% { transform: scale(1); }
    75% { transform: scale(1.05); }
}

@keyframes biic-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

@media (max-width: 768px) {
    .biic-footer-content {
        flex-direction: column;
        text-align: center;
        gap: 16px;
    }
    
    .biic-footer-right {
        align-items: center;
    }
    
    .biic-status-bar {
        flex-direction: column;
        gap: 8px;
        text-align: center;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Update footer stats
    function updateFooterStats() {
        $.post(ajaxurl, {
            action: 'biic_get_footer_stats',
            nonce: biic_admin.nonce
        }, function(response) {
            if (response.success) {
                $('#footer-total-conversations').text(response.data.conversations);
                $('#footer-total-leads').text(response.data.leads);
            }
        });
    }
    
    // Update server time
    function updateServerTime() {
        const now = new Date();
        $('#server-time').text(now.toLocaleTimeString('bn-BD', {
            hour12: false,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        }));
    }
    
    // Check for updates
    window.biicCheckForUpdates = function() {
        BiicAdmin.showLoading('#biic-loading-overlay');
        
        $.post(ajaxurl, {
            action: 'biic_check_updates',
            nonce: biic_admin.nonce
        }, function(response) {
            if (response.success) {
                if (response.data.has_update) {
                    alert('নতুন আপডেট পাওয়া গেছে: ' + response.data.version);
                } else {
                    alert('আপনার প্লাগইন সর্বশেষ সংস্করণে আছে।');
                }
            } else {
                alert('আপডেট চেক করতে ব্যর্থ।');
            }
        }).always(function() {
            BiicAdmin.hideLoading('#biic-loading-overlay');
        });
    };
    
    // Initialize
    updateFooterStats();
    updateServerTime();
    
    // Update time every second
    setInterval(updateServerTime, 1000);
    
    // Update stats every 5 minutes
    setInterval(updateFooterStats, 300000);
});
</script>