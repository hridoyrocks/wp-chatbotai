<?php
/**
 * Admin Dashboard View - Complete Implementation
 * admin/views/dashboard.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get dashboard data
$stats = isset($stats) ? $stats : array();
?>

<div class="wrap biic-admin-wrap">
    
    <!-- Header -->
    <div class="biic-admin-header">
        <div class="biic-header-content">
            <h1 class="biic-page-title">
                <span class="biic-logo">📊</span>
                <?php esc_html_e('Dashboard', 'banglay-ielts-chatbot'); ?>
            </h1>
            <div class="biic-header-actions">
                <button type="button" class="button button-secondary biic-refresh-dashboard">
                    <span class="dashicons dashicons-update"></span>
                    <?php esc_html_e('Refresh', 'banglay-ielts-chatbot'); ?>
                </button>
                <a href="<?php echo admin_url('admin.php?page=biic-analytics'); ?>" class="button button-primary">
                    <span class="dashicons dashicons-chart-area"></span>
                    <?php esc_html_e('View Analytics', 'banglay-ielts-chatbot'); ?>
                </a>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="biic-quick-stats">
            <div class="biic-stat-item" data-stat-type="conversations_today">
                <span class="biic-stat-icon">💬</span>
                <div class="biic-stat-content">
                    <span class="biic-stat-label"><?php esc_html_e('আজকের কথোপকথন', 'banglay-ielts-chatbot'); ?></span>
                    <span class="biic-stat-value biic-highlight" data-stat="conversations_today">
                        <?php echo esc_html($stats['conversations_today'] ?? 0); ?>
                    </span>
                </div>
            </div>
            
            <div class="biic-stat-item" data-stat-type="messages_today">
                <span class="biic-stat-icon">📝</span>
                <div class="biic-stat-content">
                    <span class="biic-stat-label"><?php esc_html_e('আজকের বার্তা', 'banglay-ielts-chatbot'); ?></span>
                    <span class="biic-stat-value" data-stat="messages_today">
                        <?php echo esc_html($stats['messages_today'] ?? 0); ?>
                    </span>
                </div>
            </div>
            
            <div class="biic-stat-item" data-stat-type="leads_today">
                <span class="biic-stat-icon">🎯</span>
                <div class="biic-stat-content">
                    <span class="biic-stat-label"><?php esc_html_e('আজকের লিড', 'banglay-ielts-chatbot'); ?></span>
                    <span class="biic-stat-value" data-stat="leads_today">
                        <?php echo esc_html($stats['leads_today'] ?? 0); ?>
                    </span>
                </div>
            </div>
            
            <div class="biic-stat-item" data-stat-type="active_sessions">
                <span class="biic-stat-icon">🟢</span>
                <div class="biic-stat-content">
                    <span class="biic-stat-label"><?php esc_html_e('সক্রিয় সেশন', 'banglay-ielts-chatbot'); ?></span>
                    <span class="biic-stat-value" data-stat="active_sessions">
                        <?php echo esc_html($stats['active_sessions'] ?? 0); ?>
                    </span>
                </div>
            </div>
            
            <div class="biic-stat-item">
                <span class="biic-stat-icon">📈</span>
                <div class="biic-stat-content">
                    <span class="biic-stat-label"><?php esc_html_e('রূপান্তর হার', 'banglay-ielts-chatbot'); ?></span>
                    <span class="biic-stat-value">
                        <?php echo esc_html($stats['conversion_rate'] ?? 0); ?>%
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Dashboard Content -->
    <div class="biic-dashboard-content">
        
        <!-- First Row -->
        <div class="biic-dashboard-row">
            
            <!-- Overview Metrics -->
            <div class="biic-dashboard-card">
                <div class="biic-card-header">
                    <h3 class="biic-card-title">
                        <span class="biic-card-icon">📊</span>
                        <?php esc_html_e('আজকের পরিসংখ্যান', 'banglay-ielts-chatbot'); ?>
                    </h3>
                    <div class="biic-card-actions">
                        <select class="biic-select-small" id="stats-period">
                            <option value="today"><?php esc_html_e('আজ', 'banglay-ielts-chatbot'); ?></option>
                            <option value="7days"><?php esc_html_e('৭ দিন', 'banglay-ielts-chatbot'); ?></option>
                            <option value="30days"><?php esc_html_e('৩০ দিন', 'banglay-ielts-chatbot'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="biic-card-content">
                    <div class="biic-metric-display">
                        <div class="biic-metric-number biic-highlight">
                            <?php echo esc_html($stats['conversations_today'] ?? 0); ?>
                        </div>
                        <div class="biic-metric-change positive">
                            <span class="dashicons dashicons-arrow-up-alt"></span>
                            <?php echo esc_html($stats['conversations_change'] ?? 0); ?>%
                            <small><?php esc_html_e('গতকাল থেকে', 'banglay-ielts-chatbot'); ?></small>
                        </div>
                    </div>
                    <div class="biic-metric-subtitle">
                        <?php esc_html_e('নতুন কথোপকথন', 'banglay-ielts-chatbot'); ?>
                    </div>
                    
                    <div class="biic-performance-indicator">
                        <span class="biic-indicator excellent">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php esc_html_e('চমৎকার কর্মক্ষমতা', 'banglay-ielts-chatbot'); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Response Time -->
            <div class="biic-dashboard-card">
                <div class="biic-card-header">
                    <h3 class="biic-card-title">
                        <span class="biic-card-icon">⚡</span>
                        <?php esc_html_e('প্রতিক্রিয়ার সময়', 'banglay-ielts-chatbot'); ?>
                    </h3>
                </div>
                <div class="biic-card-content">
                    <div class="biic-metric-display">
                        <div class="biic-metric-number">
                            <?php echo esc_html($stats['avg_response_time'] ?? 1.2); ?>s
                        </div>
                    </div>
                    <div class="biic-metric-subtitle">
                        <?php esc_html_e('গড় প্রতিক্রিয়ার সময়', 'banglay-ielts-chatbot'); ?>
                    </div>
                    
                    <div class="biic-performance-indicator">
                        <span class="biic-indicator excellent">
                            <span class="dashicons dashicons-clock"></span>
                            <?php esc_html_e('দ্রুত প্রতিক্রিয়া', 'banglay-ielts-chatbot'); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Lead Conversion -->
            <div class="biic-dashboard-card">
                <div class="biic-card-header">
                    <h3 class="biic-card-title">
                        <span class="biic-card-icon">🎯</span>
                        <?php esc_html_e('লিড রূপান্তর', 'banglay-ielts-chatbot'); ?>
                    </h3>
                </div>
                <div class="biic-card-content">
                    <div class="biic-metric-display">
                        <div class="biic-metric-number biic-highlight">
                            <?php echo esc_html($stats['leads_today'] ?? 0); ?>
                        </div>
                        <div class="biic-metric-change positive">
                            <span class="dashicons dashicons-arrow-up-alt"></span>
                            <?php echo esc_html($stats['leads_change'] ?? 0); ?>%
                        </div>
                    </div>
                    <div class="biic-metric-subtitle">
                        <?php esc_html_e('আজকের নতুন লিড', 'banglay-ielts-chatbot'); ?>
                    </div>
                    
                    <div class="biic-progress-bar">
                        <div class="biic-progress-fill" style="width: <?php echo esc_attr($stats['conversion_rate'] ?? 15); ?>%"></div>
                    </div>
                    <small><?php echo esc_html($stats['conversion_rate'] ?? 15); ?>% রূপান্তর হার</small>
                </div>
            </div>
            
        </div>
        
        <!-- Second Row -->
        <div class="biic-dashboard-row">
            
            <!-- Popular Intents -->
            <div class="biic-dashboard-card biic-card-large">
                <div class="biic-card-header">
                    <h3 class="biic-card-title">
                        <span class="biic-card-icon">🔥</span>
                        <?php esc_html_e('জনপ্রিয় বিষয়সমূহ', 'banglay-ielts-chatbot'); ?>
                    </h3>
                    <a href="<?php echo admin_url('admin.php?page=biic-analytics'); ?>" class="biic-card-link">
                        <?php esc_html_e('সব দেখুন', 'banglay-ielts-chatbot'); ?>
                    </a>
                </div>
                <div class="biic-card-content">
                    <div class="biic-intents-list">
                        <?php 
                        $top_intents = $stats['top_intents'] ?? array();
                        $max_count = !empty($top_intents) ? $top_intents[0]->count : 1;
                        
                        foreach (array_slice($top_intents, 0, 5) as $index => $intent): 
                            $percentage = round(($intent->count / $max_count) * 100);
                        ?>
                            <div class="biic-intent-item">
                                <div class="biic-intent-rank"><?php echo $index + 1; ?></div>
                                <div class="biic-intent-info">
                                    <span class="biic-intent-name">
                                        <?php echo esc_html($this->format_intent_name($intent->detected_intent)); ?>
                                    </span>
                                    <div class="biic-intent-bar">
                                        <div class="biic-intent-progress" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                </div>
                                <div class="biic-intent-count"><?php echo esc_html($intent->count); ?></div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($top_intents)): ?>
                            <div class="biic-no-data">
                                <span class="dashicons dashicons-info"></span>
                                <p><?php esc_html_e('এখনও কোনো ডেটা পাওয়া যায়নি', 'banglay-ielts-chatbot'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Course Interests -->
            <div class="biic-dashboard-card">
                <div class="biic-card-header">
                    <h3 class="biic-card-title">
                        <span class="biic-card-icon">📚</span>
                        <?php esc_html_e('কোর্সের আগ্রহ', 'banglay-ielts-chatbot'); ?>
                    </h3>
                </div>
                <div class="biic-card-content">
                    <div class="biic-course-stats">
                        <?php 
                        $course_interests = $stats['course_interests'] ?? array();
                        $total_interests = array_sum(array_column($course_interests, 'count'));
                        
                        foreach ($course_interests as $course):
                            $percentage = $total_interests > 0 ? round(($course->count / $total_interests) * 100) : 0;
                            $course_name = $this->format_course_name($course->course_interest);
                            $color = $this->get_course_color($course->course_interest);
                        ?>
                            <div class="biic-course-item">
                                <div class="biic-course-header">
                                    <div class="biic-course-dot" style="background-color: <?php echo $color; ?>"></div>
                                    <div class="biic-course-name"><?php echo esc_html($course_name); ?></div>
                                    <div class="biic-course-percentage"><?php echo $percentage; ?>%</div>
                                </div>
                                <div class="biic-course-bar">
                                    <div class="biic-course-progress" style="width: <?php echo $percentage; ?>%; background-color: <?php echo $color; ?>"></div>
                                </div>
                                <div class="biic-course-count"><?php echo esc_html($course->count); ?> জন আগ্রহী</div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($course_interests)): ?>
                            <div class="biic-no-data">
                                <span class="dashicons dashicons-book-alt"></span>
                                <p><?php esc_html_e('এখনও কোনো কোর্সের আগ্রহ নেই', 'banglay-ielts-chatbot'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Third Row -->
        <div class="biic-dashboard-row">
            
            <!-- Recent Conversations -->
            <div class="biic-dashboard-card biic-card-large">
                <div class="biic-card-header">
                    <h3 class="biic-card-title">
                        <span class="biic-card-icon">💬</span>
                        <?php esc_html_e('সাম্প্রতিক কথোপকথন', 'banglay-ielts-chatbot'); ?>
                    </h3>
                    <a href="<?php echo admin_url('admin.php?page=biic-conversations'); ?>" class="biic-card-link">
                        <?php esc_html_e('সব দেখুন', 'banglay-ielts-chatbot'); ?>
                    </a>
                </div>
                <div class="biic-card-content">
                    <div class="biic-recent-conversations">
                        <?php 
                        // Get recent conversations
                        $database = BIIC()->database;
                        $recent_conversations = $database->get_recent_conversations(5);
                        
                        foreach ($recent_conversations as $conversation): 
                            $lead_badge = $this->get_lead_badge($conversation->lead_score ?? 0);
                            $time_ago = human_time_diff(strtotime($conversation->started_at), current_time('timestamp'));
                        ?>
                            <div class="biic-conversation-item" data-session-id="<?php echo esc_attr($conversation->session_id); ?>">
                                <div class="biic-conversation-avatar">
                                    <?php 
                                    if (!empty($conversation->lead_name)) {
                                        echo esc_html(strtoupper(substr($conversation->lead_name, 0, 2)));
                                    } else {
                                        echo '👤';
                                    }
                                    ?>
                                </div>
                                <div class="biic-conversation-content">
                                    <div class="biic-conversation-header">
                                        <span class="biic-conversation-name">
                                            <?php echo esc_html($conversation->lead_name ?: 'বেনামী ব্যবহারকারী'); ?>
                                        </span>
                                        <?php echo $lead_badge; ?>
                                    </div>
                                    <div class="biic-conversation-meta">
                                        <span><i class="dashicons dashicons-clock"></i> <?php echo $time_ago; ?> আগে</span>
                                        <span><i class="dashicons dashicons-format-chat"></i> <?php echo esc_html($conversation->total_messages ?? 0); ?> বার্তা</span>
                                        <span>
                                            <?php 
                                            $device_icon = $conversation->device_type === 'mobile' ? '📱' : 
                                                          ($conversation->device_type === 'tablet' ? '📟' : '💻');
                                            echo $device_icon;
                                            ?>
                                            <?php echo esc_html($conversation->device_type ?: 'অজানা'); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="biic-conversation-actions">
                                    <button type="button" class="button button-small biic-view-conversation" 
                                            data-session-id="<?php echo esc_attr($conversation->session_id); ?>" 
                                            title="কথোপকথন দেখুন">
                                        <span class="dashicons dashicons-visibility"></span>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($recent_conversations)): ?>
                            <div class="biic-no-data">
                                <span class="dashicons dashicons-format-chat"></span>
                                <p><?php esc_html_e('এখনও কোনো কথোপকথন নেই', 'banglay-ielts-chatbot'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- System Status -->
            <div class="biic-dashboard-card">
                <div class="biic-card-header">
                    <h3 class="biic-card-title">
                        <span class="biic-card-icon">⚙️</span>
                        <?php esc_html_e('সিস্টেম স্ট্যাটাস', 'banglay-ielts-chatbot'); ?>
                    </h3>
                </div>
                <div class="biic-card-content">
                    <div class="biic-status-items">
                        
                        <div class="biic-status-item">
                            <span class="biic-status-indicator active"></span>
                            <div class="biic-status-content">
                                <span class="biic-status-label"><?php esc_html_e('চ্যাটবট', 'banglay-ielts-chatbot'); ?></span>
                                <span class="biic-status-value">
                                    <?php echo get_option('biic_chatbot_enabled', true) ? 'সক্রিয়' : 'নিষ্ক্রিয়'; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="biic-status-item">
                            <span class="biic-status-indicator <?php echo !empty(get_option('biic_openai_api_key')) ? 'active' : 'inactive'; ?>"></span>
                            <div class="biic-status-content">
                                <span class="biic-status-label"><?php esc_html_e('AI ইন্টিগ্রেশন', 'banglay-ielts-chatbot'); ?></span>
                                <span class="biic-status-value">
                                    <?php echo !empty(get_option('biic_openai_api_key')) ? 'কনফিগার করা' : 'অনুপস্থিত'; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="biic-status-item">
                            <span class="biic-status-indicator active"></span>
                            <div class="biic-status-content">
                                <span class="biic-status-label"><?php esc_html_e('ডাটাবেস', 'banglay-ielts-chatbot'); ?></span>
                                <span class="biic-status-value">সংযুক্ত</span>
                            </div>
                        </div>
                        
                        <div class="biic-status-item">
                            <span class="biic-status-indicator <?php echo get_option('biic_analytics_enabled', true) ? 'active' : 'inactive'; ?>"></span>
                            <div class="biic-status-content">
                                <span class="biic-status-label"><?php esc_html_e('অ্যানালিটিক্স', 'banglay-ielts-chatbot'); ?></span>
                                <span class="biic-status-value">
                                    <?php echo get_option('biic_analytics_enabled', true) ? 'সক্রিয়' : 'নিষ্ক্রিয়'; ?>
                                </span>
                            </div>
                        </div>
                        
                    </div>
                    
                    <!-- Overall Health Score -->
                    <div class="biic-health-score">
                        <div class="biic-health-header">
                            <span class="biic-health-label"><?php esc_html_e('সামগ্রিক স্বাস্থ্য', 'banglay-ielts-chatbot'); ?></span>
                            <span class="biic-health-percentage excellent">85%</span>
                        </div>
                        <div class="biic-health-bar">
                            <div class="biic-health-progress excellent" style="width: 85%"></div>
                        </div>
                        <div class="biic-health-status excellent"><?php esc_html_e('চমৎকার', 'banglay-ielts-chatbot'); ?></div>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Fourth Row - Quick Actions -->
        <div class="biic-dashboard-row">
            <div class="biic-dashboard-card biic-card-large">
                <div class="biic-card-header">
                    <h3 class="biic-card-title">
                        <span class="biic-card-icon">⚡</span>
                        <?php esc_html_e('দ্রুত কাজ', 'banglay-ielts-chatbot'); ?>
                    </h3>
                </div>
                <div class="biic-card-content">
                    <div class="biic-actions-grid">
                        
                        <a href="<?php echo admin_url('admin.php?page=biic-conversations'); ?>" class="biic-action-item">
                            <span class="biic-action-icon">💬</span>
                            <div class="biic-action-content">
                                <span class="biic-action-title"><?php esc_html_e('কথোপকথন দেখুন', 'banglay-ielts-chatbot'); ?></span>
                                <span class="biic-action-desc"><?php esc_html_e('সব কথোপকথন দেখুন ও পরিচালনা করুন', 'banglay-ielts-chatbot'); ?></span>
                            </div>
                        </a>
                        
                        <a href="<?php echo admin_url('admin.php?page=biic-leads'); ?>" class="biic-action-item">
                            <span class="biic-action-icon">🎯</span>
                            <div class="biic-action-content">
                                <span class="biic-action-title"><?php esc_html_e('লিড পরিচালনা', 'banglay-ielts-chatbot'); ?></span>
                                <span class="biic-action-desc"><?php esc_html_e('লিড দেখুন ও ফলো-আপ করুন', 'banglay-ielts-chatbot'); ?></span>
                            </div>
                        </a>
                        
                        <a href="<?php echo admin_url('admin.php?page=biic-analytics'); ?>" class="biic-action-item">
                            <span class="biic-action-icon">📊</span>
                            <div class="biic-action-content">
                                <span class="biic-action-title"><?php esc_html_e('অ্যানালিটিক্স', 'banglay-ielts-chatbot'); ?></span>
                                <span class="biic-action-desc"><?php esc_html_e('বিস্তারিত রিপোর্ট ও পরিসংখ্যান', 'banglay-ielts-chatbot'); ?></span>
                            </div>
                        </a>
                        
                        <a href="<?php echo admin_url('admin.php?page=biic-settings'); ?>" class="biic-action-item">
                            <span class="biic-action-icon">⚙️</span>
                            <div class="biic-action-content">
                                <span class="biic-action-title"><?php esc_html_e('সেটিংস', 'banglay-ielts-chatbot'); ?></span>
                                <span class="biic-action-desc"><?php esc_html_e('চ্যাটবট কনফিগার করুন', 'banglay-ielts-chatbot'); ?></span>
                            </div>
                        </a>
                        
                        <div class="biic-action-item" onclick="biicTestChatbot()">
                            <span class="biic-action-icon">🧪</span>
                            <div class="biic-action-content">
                                <span class="biic-action-title"><?php esc_html_e('চ্যাটবট টেস্ট', 'banglay-ielts-chatbot'); ?></span>
                                <span class="biic-action-desc"><?php esc_html_e('চ্যাটবট কার্যক্ষমতা পরীক্ষা করুন', 'banglay-ielts-chatbot'); ?></span>
                            </div>
                        </div>
                        
                        <div class="biic-action-item" onclick="biicExportData()">
                            <span class="biic-action-icon">📥</span>
                            <div class="biic-action-content">
                                <span class="biic-action-title"><?php esc_html_e('ডেটা এক্সপোর্ট', 'banglay-ielts-chatbot'); ?></span>
                                <span class="biic-action-desc"><?php esc_html_e('সব ডেটা ডাউনলোড করুন', 'banglay-ielts-chatbot'); ?></span>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- Dashboard Footer -->
    <div class="biic-dashboard-footer">
        <div class="biic-footer-info">
            <span class="biic-footer-text">Banglay IELTS Chatbot</span>
            <span class="biic-version">v<?php echo BIIC_VERSION; ?></span>
        </div>
        <div class="biic-footer-made-with">
            Made with <span class="biic-footer-heart">❤️</span> by Love Rocks
        </div>
        <div class="biic-footer-actions">
            <a href="https://banglayelts.com" target="_blank" class="button button-small">
                <span class="dashicons dashicons-external"></span>
                <?php esc_html_e('ওয়েবসাইট', 'banglay-ielts-chatbot'); ?>
            </a>
            <a href="mailto:support@banglayelts.com" class="button button-small">
                <span class="dashicons dashicons-email"></span>
                <?php esc_html_e('সাপোর্ট', 'banglay-ielts-chatbot'); ?>
            </a>
        </div>
    </div>
    
</div>

<script type="text/javascript">
// Dashboard specific JavaScript
function biicTestChatbot() {
    alert('চ্যাটবট টেস্ট ফিচার শীঘ্রই আসছে!');
}

function biicExportData() {
    if (confirm('সব ডেটা এক্সপোর্ট করতে চান?')) {
        window.location.href = '<?php echo admin_url("admin-ajax.php?action=biic_export_all_data&nonce=" . wp_create_nonce("biic_export_nonce")); ?>';
    }
}
</script>

<?php
/**
 * Helper functions for dashboard
 */

// Format intent names for display
function format_intent_name($intent) {
    $intent_names = array(
        'course_fee' => 'কোর্সের ফি',
        'admission_process' => 'ভর্তি প্রক্রিয়া',
        'course_duration' => 'কোর্সের সময়কাল',
        'location_info' => 'অবস্থানের তথ্য',
        'contact_info' => 'যোগাযোগের তথ্য',
        'study_abroad' => 'বিদেশে পড়াশোনা',
        'ielts_preparation' => 'IELTS প্রস্তুতি'
    );
    
    return $intent_names[$intent] ?? ucfirst(str_replace('_', ' ', $intent));
}

// Format course names
function format_course_name($course) {
    $course_names = array(
        'ielts_comprehensive' => 'IELTS কমপ্রিহেনসিভ',
        'ielts_focus' => 'IELTS ফোকাস',
        'ielts_crash' => 'IELTS ক্র্যাশ',
        'online_course' => 'অনলাইন কোর্স',
        'study_abroad' => 'বিদেশে পড়াশোনা'
    );
    
    return $course_names[$course] ?? ucfirst(str_replace('_', ' ', $course));
}

// Get course colors
function get_course_color($course) {
    $colors = array(
        'ielts_comprehensive' => '#E53E3E',
        'ielts_focus' => '#38A169',
        'ielts_crash' => '#D69E2E',
        'online_course' => '#3182CE',
        'study_abroad' => '#805AD5'
    );
    
    return $colors[$course] ?? '#6B7280';
}

// Get lead badge
function get_lead_badge($score) {
    if ($score >= 80) {
        return '<span class="biic-lead-badge biic-lead-hot">🔥 হট</span>';
    } elseif ($score >= 50) {
        return '<span class="biic-lead-badge biic-lead-warm">🌡️ ওয়ার্ম</span>';
    } else {
        return '<span class="biic-lead-badge biic-lead-cold">❄️ কোল্ড</span>';
    }
}
?>