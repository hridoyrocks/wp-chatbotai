<?php
/**
 * Admin Header Partial
 * admin/views/partials/header.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="biic-admin-header-bar">
    <div class="biic-header-brand">
        <div class="biic-brand-logo">
            <span class="biic-logo-icon">🤖</span>
            <span class="biic-brand-text">Banglay IELTS Chatbot</span>
        </div>
        <div class="biic-brand-version">
            <span class="biic-version-badge">v<?php echo BIIC_VERSION; ?></span>
        </div>
    </div>
    
    <div class="biic-header-nav">
        <nav class="biic-nav-menu">
            <a href="<?php echo admin_url('admin.php?page=biic-dashboard'); ?>" 
               class="biic-nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'biic-dashboard') ? 'active' : ''; ?>">
                <span class="dashicons dashicons-dashboard"></span>
                <?php esc_html_e('ড্যাশবোর্ড', 'banglay-ielts-chatbot'); ?>
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=biic-conversations'); ?>" 
               class="biic-nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'biic-conversations') ? 'active' : ''; ?>">
                <span class="dashicons dashicons-format-chat"></span>
                <?php esc_html_e('কথোপকথন', 'banglay-ielts-chatbot'); ?>
                <span class="biic-nav-count" id="conversations-count">0</span>
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=biic-leads'); ?>" 
               class="biic-nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'biic-leads') ? 'active' : ''; ?>">
                <span class="dashicons dashicons-admin-users"></span>
                <?php esc_html_e('লিডস', 'banglay-ielts-chatbot'); ?>
                <span class="biic-nav-count" id="leads-count">0</span>
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=biic-analytics'); ?>" 
               class="biic-nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'biic-analytics') ? 'active' : ''; ?>">
                <span class="dashicons dashicons-chart-area"></span>
                <?php esc_html_e('অ্যানালিটিক্স', 'banglay-ielts-chatbot'); ?>
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=biic-settings'); ?>" 
               class="biic-nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'biic-settings') ? 'active' : ''; ?>">
                <span class="dashicons dashicons-admin-generic"></span>
                <?php esc_html_e('সেটিংস', 'banglay-ielts-chatbot'); ?>
            </a>
        </nav>
    </div>
    
    <div class="biic-header-actions">
        <div class="biic-status-indicator">
            <span class="biic-status-dot active" id="system-status"></span>
            <span class="biic-status-text"><?php esc_html_e('সিস্টেম সক্রিয়', 'banglay-ielts-chatbot'); ?></span>
        </div>
        
        <div class="biic-quick-stats">
            <div class="biic-quick-stat">
                <span class="biic-stat-icon">🟢</span>
                <span class="biic-stat-number" id="active-sessions">0</span>
                <span class="biic-stat-label"><?php esc_html_e('সক্রিয়', 'banglay-ielts-chatbot'); ?></span>
            </div>
        </div>
        
        <div class="biic-header-dropdown">
            <button type="button" class="biic-dropdown-toggle">
                <span class="dashicons dashicons-menu"></span>
            </button>
            <div class="biic-dropdown-menu">
                <a href="<?php echo admin_url('admin.php?page=biic-settings'); ?>" class="biic-dropdown-item">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <?php esc_html_e('সেটিংস', 'banglay-ielts-chatbot'); ?>
                </a>
                <a href="#" class="biic-dropdown-item" onclick="biicExportData()">
                    <span class="dashicons dashicons-download"></span>
                    <?php esc_html_e('ডেটা এক্সপোর্ট', 'banglay-ielts-chatbot'); ?>
                </a>
                <a href="https://banglayelts.com/support" target="_blank" class="biic-dropdown-item">
                    <span class="dashicons dashicons-sos"></span>
                    <?php esc_html_e('সাহায্য', 'banglay-ielts-chatbot'); ?>
                </a>
                <div class="biic-dropdown-divider"></div>
                <a href="https://banglayelts.com" target="_blank" class="biic-dropdown-item">
                    <span class="dashicons dashicons-external"></span>
                    <?php esc_html_e('ওয়েবসাইট', 'banglay-ielts-chatbot'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.biic-admin-header-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    background: linear-gradient(135deg, #E53E3E 0%, #C53030 100%);
    color: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 1000;
}

.biic-header-brand {
    display: flex;
    align-items: center;
    gap: 12px;
}

.biic-brand-logo {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 18px;
}

.biic-logo-icon {
    font-size: 24px;
    filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.2));
}

.biic-version-badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.biic-nav-menu {
    display: flex;
    gap: 8px;
}

.biic-nav-link {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.2s ease;
    font-size: 14px;
    font-weight: 500;
    position: relative;
}

.biic-nav-link:hover,
.biic-nav-link:focus {
    color: white;
    background: rgba(255, 255, 255, 0.1);
    text-decoration: none;
}

.biic-nav-link.active {
    color: white;
    background: rgba(255, 255, 255, 0.15);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.biic-nav-count {
    background: #D69E2E;
    color: white;
    font-size: 10px;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 18px;
    text-align: center;
    line-height: 1.2;
}

.biic-header-actions {
    display: flex;
    align-items: center;
    gap: 20px;
}

.biic-status-indicator {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
}

.biic-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #9CA3AF;
}

.biic-status-dot.active {
    background: #10B981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
    animation: biic-pulse 2s infinite;
}

.biic-quick-stats {
    display: flex;
    gap: 16px;
}

.biic-quick-stat {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
}

.biic-stat-number {
    font-weight: 600;
    color: #FED7AA;
}

.biic-header-dropdown {
    position: relative;
}

.biic-dropdown-toggle {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: white;
    padding: 8px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s ease;
}

.biic-dropdown-toggle:hover {
    background: rgba(255, 255, 255, 0.2);
}

.biic-dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    min-width: 200px;
    z-index: 1001;
    display: none;
    margin-top: 8px;
}

.biic-dropdown-menu.show {
    display: block;
}

.biic-dropdown-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    color: #374151;
    text-decoration: none;
    font-size: 14px;
    transition: background 0.2s ease;
}

.biic-dropdown-item:hover {
    background: #F3F4F6;
    color: #374151;
    text-decoration: none;
}

.biic-dropdown-divider {
    height: 1px;
    background: #E5E7EB;
    margin: 4px 0;
}

@keyframes biic-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

@media (max-width: 768px) {
    .biic-admin-header-bar {
        flex-direction: column;
        gap: 12px;
        padding: 16px;
    }
    
    .biic-nav-menu {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .biic-header-actions {
        flex-wrap: wrap;
        justify-content: center;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Dropdown toggle
    $('.biic-dropdown-toggle').on('click', function(e) {
        e.stopPropagation();
        $('.biic-dropdown-menu').toggleClass('show');
    });
    
    // Close dropdown when clicking outside
    $(document).on('click', function() {
        $('.biic-dropdown-menu').removeClass('show');
    });
    
    // Update nav counts
    function updateNavCounts() {
        $.post(ajaxurl, {
            action: 'biic_get_nav_counts',
            nonce: biic_admin.nonce
        }, function(response) {
            if (response.success) {
                $('#conversations-count').text(response.data.conversations);
                $('#leads-count').text(response.data.leads);
                $('#active-sessions').text(response.data.active_sessions);
            }
        });
    }
    
    // Update counts every 30 seconds
    updateNavCounts();
    setInterval(updateNavCounts, 30000);
});
</script>