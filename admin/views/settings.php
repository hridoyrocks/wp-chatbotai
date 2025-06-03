<?php
/**
 * Admin Settings View - সেটিংস পৃষ্ঠা
 * Comprehensive chatbot configuration interface
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$settings = isset($settings) ? $settings : array();
?>

<div class="wrap biic-admin-wrap">
    
    <!-- Header -->
    <div class="biic-admin-header">
        <div class="biic-header-content">
            <h1 class="biic-page-title">
                <span class="biic-logo">⚙️</span>
                <?php esc_html_e('Chatbot Settings', 'banglay-ielts-chatbot'); ?>
            </h1>
            <div class="biic-header-actions">
                <button type="button" class="button button-secondary" onclick="biicResetSettings()">
                    <span class="dashicons dashicons-undo"></span>
                    <?php esc_html_e('Reset to Defaults', 'banglay-ielts-chatbot'); ?>
                </button>
                <button type="button" class="button button-primary" onclick="biicExportSettings()">
                    <span class="dashicons dashicons-download"></span>
                    <?php esc_html_e('Export Settings', 'banglay-ielts-chatbot'); ?>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Settings Form -->
    <form method="post" action="" id="biic-settings-form">
        <?php wp_nonce_field('biic_save_settings', 'biic_settings_nonce'); ?>
        
        <div class="biic-settings-content">
            
            <!-- Settings Navigation -->
            <div class="biic-settings-nav">
                <ul class="biic-nav-tabs">
                    <li class="biic-nav-item">
                        <a href="#general" class="biic-nav-link active" data-tab="general">
                            <span class="dashicons dashicons-admin-generic"></span>
                            <?php esc_html_e('General', 'banglay-ielts-chatbot'); ?>
                        </a>
                    </li>
                    <li class="biic-nav-item">
                        <a href="#appearance" class="biic-nav-link" data-tab="appearance">
                            <span class="dashicons dashicons-admin-appearance"></span>
                            <?php esc_html_e('Appearance', 'banglay-ielts-chatbot'); ?>
                        </a>
                    </li>
                    <li class="biic-nav-item">
                        <a href="#behavior" class="biic-nav-link" data-tab="behavior">
                            <span class="dashicons dashicons-performance"></span>
                            <?php esc_html_e('Behavior', 'banglay-ielts-chatbot'); ?>
                        </a>
                    </li>
                    <li class="biic-nav-item">
                        <a href="#ai-integration" class="biic-nav-link" data-tab="ai-integration">
                            <span class="dashicons dashicons-superhero"></span>
                            <?php esc_html_e('AI Integration', 'banglay-ielts-chatbot'); ?>
                        </a>
                    </li>
                    <li class="biic-nav-item">
                        <a href="#notifications" class="biic-nav-link" data-tab="notifications">
                            <span class="dashicons dashicons-email"></span>
                            <?php esc_html_e('Notifications', 'banglay-ielts-chatbot'); ?>
                        </a>
                    </li>
                    <li class="biic-nav-item">
                        <a href="#advanced" class="biic-nav-link" data-tab="advanced">
                            <span class="dashicons dashicons-admin-tools"></span>
                            <?php esc_html_e('Advanced', 'banglay-ielts-chatbot'); ?>
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Settings Panels -->
            <div class="biic-settings-panels">
                
                <!-- General Settings -->
                <div id="general" class="biic-settings-panel active">
                    <div class="biic-panel-header">
                        <h2><?php esc_html_e('General Settings', 'banglay-ielts-chatbot'); ?></h2>
                        <p><?php esc_html_e('Basic chatbot configuration and enable/disable options.', 'banglay-ielts-chatbot'); ?></p>
                    </div>
                    
                    <div class="biic-settings-section">
                        <h3><?php esc_html_e('Chatbot Status', 'banglay-ielts-chatbot'); ?></h3>
                        
                        <div class="biic-setting-item">
                            <div class="biic-setting-control">
                                <label class="biic-toggle">
                                    <input type="checkbox" name="chatbot_enabled" value="1" <?php checked($settings['chatbot_enabled'] ?? true); ?>>
                                    <span class="biic-toggle-slider"></span>
                                </label>
                            </div>
                            <div class="biic-setting-info">
                                <h4><?php esc_html_e('Enable Chatbot', 'banglay-ielts-chatbot'); ?></h4>
                                <p><?php esc_html_e('Turn the chatbot on or off across your entire website.', 'banglay-ielts-chatbot'); ?></p>
                            </div>
                        </div>
                        
                        <div class="biic-setting-item">
                            <div class="biic-setting-control">
                                <label class="biic-toggle">
                                    <input type="checkbox" name="auto_greeting" value="1" <?php checked($settings['auto_greeting'] ?? true); ?>>
                                    <span class="biic-toggle-slider"></span>
                                </label>
                            </div>
                            <div class="biic-setting-info">
                                <h4><?php esc_html_e('Auto Greeting', 'banglay-ielts-chatbot'); ?></h4>
                                <p><?php esc_html_e('Automatically show greeting message when users visit the site.', 'banglay-ielts-chatbot'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="biic-settings-section">
                        <h3><?php esc_html_e('Welcome Message', 'banglay-ielts-chatbot'); ?></h3>
                        
                        <div class="biic-setting-item biic-setting-full">
                            <label for="welcome_message"><?php esc_html_e('Greeting Text', 'banglay-ielts-chatbot'); ?></label>
                            <textarea 
                                id="welcome_message" 
                                name="welcome_message" 
                                class="large-text" 
                                rows="3"
                                placeholder="<?php esc_attr_e('আস্সালামু আলাইকুম! IELTS এর ব্যাপারে কিছু জানতে চান?', 'banglay-ielts-chatbot'); ?>"
                            ><?php echo esc_textarea($settings['welcome_message'] ?? 'আস্সালামু আলাইকুম! IELTS এর ব্যাপারে কিছু জানতে চান?'); ?></textarea>
                            <p class="description"><?php esc_html_e('This message will be shown when the chatbot first loads.', 'banglay-ielts-chatbot'); ?></p>
                        </div>
                    </div>
                    
                    <div class="biic-settings-section">
                        <h3><?php esc_html_e('Business Hours', 'banglay-ielts-chatbot'); ?></h3>
                        
                        <div class="biic-setting-item biic-setting-row">
                            <div class="biic-setting-col">
                                <label for="business_hours_start"><?php esc_html_e('Start Time', 'banglay-ielts-chatbot'); ?></label>
                                <input type="time" id="business_hours_start" name="business_hours_start" value="<?php echo esc_attr($settings['business_hours']['start'] ?? '10:00'); ?>">
                            </div>
                            
                            <div class="biic-setting-col">
                                <label for="business_hours_end"><?php esc_html_e('End Time', 'banglay-ielts-chatbot'); ?></label>
                                <input type="time" id="business_hours_end" name="business_hours_end" value="<?php echo esc_attr($settings['business_hours']['end'] ?? '18:00'); ?>">
                            </div>
                        </div>
                        
                        <div class="biic-setting-item biic-setting-full">
                            <label><?php esc_html_e('Working Days', 'banglay-ielts-chatbot'); ?></label>
                            <div class="biic-checkbox-group">
                                <?php 
                                $days = array(
                                    'monday' => __('Monday', 'banglay-ielts-chatbot'),
                                    'tuesday' => __('Tuesday', 'banglay-ielts-chatbot'),
                                    'wednesday' => __('Wednesday', 'banglay-ielts-chatbot'),
                                    'thursday' => __('Thursday', 'banglay-ielts-chatbot'),
                                    'friday' => __('Friday', 'banglay-ielts-chatbot'),
                                    'saturday' => __('Saturday', 'banglay-ielts-chatbot'),
                                    'sunday' => __('Sunday', 'banglay-ielts-chatbot')
                                );
                                $selected_days = $settings['business_hours']['days'] ?? array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
                                
                                foreach ($days as $value => $label): ?>
                                    <label class="biic-checkbox-label">
                                        <input type="checkbox" name="business_days[]" value="<?php echo esc_attr($value); ?>" <?php checked(in_array($value, $selected_days)); ?>>
                                        <?php echo esc_html($label); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Appearance Settings -->
                <div id="appearance" class="biic-settings-panel">
                    <div class="biic-panel-header">
                        <h2><?php esc_html_e('Appearance Settings', 'banglay-ielts-chatbot'); ?></h2>
                        <p><?php esc_html_e('Customize the look and feel of your chatbot.', 'banglay-ielts-chatbot'); ?></p>
                    </div>
                    
                    <div class="biic-settings-section">
                        <h3><?php esc_html_e('Position & Layout', 'banglay-ielts-chatbot'); ?></h3>
                        
                        <div class="biic-setting-item biic-setting-row">
                            <div class="biic-setting-col">
                                <label for="chat_position"><?php esc_html_e('Chat Position', 'banglay-ielts-chatbot'); ?></label>
                                <select id="chat_position" name="chat_position">
                                    <option value="bottom-right" <?php selected($settings['chat_position'] ?? 'bottom-right', 'bottom-right'); ?>><?php esc_html_e('Bottom Right', 'banglay-ielts-chatbot'); ?></option>
                                    <option value="bottom-left" <?php selected($settings['chat_position'] ?? 'bottom-right', 'bottom-left'); ?>><?php esc_html_e('Bottom Left', 'banglay-ielts-chatbot'); ?></option>
                                    <option value="top-right" <?php selected($settings['chat_position'] ?? 'bottom-right', 'top-right'); ?>><?php esc_html_e('Top Right', 'banglay-ielts-chatbot'); ?></option>
                                    <option value="top-left" <?php selected($settings['chat_position'] ?? 'bottom-right', 'top-left'); ?>><?php esc_html_e('Top Left', 'banglay-ielts-chatbot'); ?></option>
                                </select>
                            </div>
                            
                            <div class="biic-setting-col">
                                <label for="chat_theme"><?php esc_html_e('Theme', 'banglay-ielts-chatbot'); ?></label>
                                <select id="chat_theme" name="chat_theme">
                                    <option value="modern" <?php selected($settings['chat_theme'] ?? 'modern', 'modern'); ?>><?php esc_html_e('Modern', 'banglay-ielts-chatbot'); ?></option>
                                    <option value="minimal" <?php selected($settings['chat_theme'] ?? 'modern', 'minimal'); ?>><?php esc_html_e('Minimal', 'banglay-ielts-chatbot'); ?></option>
                                    <option value="dark" <?php selected($settings['chat_theme'] ?? 'modern', 'dark'); ?>><?php esc_html_e('Dark', 'banglay-ielts-chatbot'); ?></option>
                                    <option value="classic" <?php selected($settings['chat_theme'] ?? 'modern', 'classic'); ?>><?php esc_html_e('Classic', 'banglay-ielts-chatbot'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="biic-settings-section">
                        <h3><?php esc_html_e('Colors & Branding', 'banglay-ielts-chatbot'); ?></h3>
                        
                        <div class="biic-setting-item biic-setting-row">
                            <div class="biic-setting-col">
                                <label for="primary_color"><?php esc_html_e('Primary Color', 'banglay-ielts-chatbot'); ?></label>
                                <input type="color" id="primary_color" name="primary_color" value="<?php echo esc_attr($settings['primary_color'] ?? '#E53E3E'); ?>">
                            </div>
                            
                            <div class="biic-setting-col">
                                <label for="font_family"><?php esc_html_e('Font Family', 'banglay-ielts-chatbot'); ?></label>
                                <select id="font_family" name="font_family">
                                    <option value="" <?php selected($settings['font_family'] ?? '', ''); ?>><?php esc_html_e('Default', 'banglay-ielts-chatbot'); ?></option>
                                    <option value="Arial, sans-serif" <?php selected($settings['font_family'] ?? '', 'Arial, sans-serif'); ?>>Arial</option>
                                    <option value="Georgia, serif" <?php selected($settings['font_family'] ?? '', 'Georgia, serif'); ?>>Georgia</option>
                                    <option value="Tahoma, sans-serif" <?php selected($settings['font_family'] ?? '', 'Tahoma, sans-serif'); ?>>Tahoma</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="biic-settings-section">
                        <h3><?php esc_html_e('Animation & Effects', 'banglay-ielts-chatbot'); ?></h3>
                        
                        <div class="biic-setting-item">
                            <div class="biic-setting-control">
                                <label class="biic-toggle">
                                    <input type="checkbox" name="enable_animations" value="1" <?php checked($settings['enable_animations'] ?? true); ?>>
                                    <span class="biic-toggle-slider"></span>
                                </label>
                            </div>
                            <div class="biic-setting-info">
                                <h4><?php esc_html_e('Enable Animations', 'banglay-ielts-chatbot'); ?></h4>
                                <p><?php esc_html_e('Smooth animations for better user experience.', 'banglay-ielts-chatbot'); ?></p>
                            </div>
                        </div>
                        
                        <div class="biic-setting-item">
                            <div class="biic-setting-control">
                                <label class="biic-toggle">
                                    <input type="checkbox" name="enable_sounds" value="1" <?php checked($settings['enable_sounds'] ?? true); ?>>
                                    <span class="biic-toggle-slider"></span>
                                </label>
                            </div>
                            <div class="biic-setting-info">
                                <h4><?php esc_html_e('Sound Notifications', 'banglay-ielts-chatbot'); ?></h4>
                                <p><?php esc_html_e('Play notification sounds for new messages.', 'banglay-ielts-chatbot'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Behavior Settings -->
                <div id="behavior" class="biic-settings-panel">
                    <div class="biic-panel-header">
                        <h2><?php esc_html_e('Behavior Settings', 'banglay-ielts-chatbot'); ?></h2>
                        <p><?php esc_html_e('Configure how the chatbot behaves and responds.', 'banglay-ielts-chatbot'); ?></p>
                    </div>
                    
                    <div class="biic-settings-section">
                        <h3><?php esc_html_e('Response Settings', 'banglay-ielts-chatbot'); ?></h3>
                        
                        <div class="biic-setting-item biic-setting-row">
                            <div class="biic-setting-col">
                                <label for="typing_speed"><?php esc_html_e('Typing Speed (ms)', 'banglay-ielts-chatbot'); ?></label>
                                <input type="number" id="typing_speed" name="typing_speed" min="10" max="500" value="<?php echo esc_attr($settings['typing_speed'] ?? 50); ?>">
                                <p class="description"><?php esc_html_e('Delay between words when typing responses.', 'banglay-ielts-chatbot'); ?></p>
                            </div>
                            
                            <div class="biic-setting-col">
                                <label for="max_message_length"><?php esc_html_e('Max Message Length', 'banglay-ielts-chatbot'); ?></label>
                                <input type="number" id="max_message_length" name="max_message_length" min="100" max="2000" value="<?php echo esc_attr($settings['max_message_length'] ?? 1000); ?>">
                                <p class="description"><?php esc_html_e('Maximum characters per user message.', 'banglay-ielts-chatbot'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="biic-settings-section">
                        <h3><?php esc_html_e('File Upload', 'banglay-ielts-chatbot'); ?></h3>
                        
                        <div class="biic-setting-item">
                            <div class="biic-setting-control">
                                <label class="biic-toggle">
                                    <input type="checkbox" name="allow_file_upload" value="1" <?php checked($settings['allow_file_upload'] ?? false); ?>>
                                    <span class="biic-toggle-slider"></span>
                                </label>
                            </div>
                            <div class="biic-setting-info">
                                <h4><?php esc_html_e('Allow File Upload', 'banglay-ielts-chatbot'); ?></h4>
                                <p><?php esc_html_e('Let users upload files during conversations.', 'banglay-ielts-chatbot'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="biic-settings-section">
                        <h3><?php esc_html_e('Timezone', 'banglay-ielts-chatbot'); ?></h3>
                        
                        <div class="biic-setting-item biic-setting-full">
                            <label for="timezone"><?php esc_html_e('Timezone', 'banglay-ielts-chatbot'); ?></label>
                            <select id="timezone" name="timezone">
                                <option value="Asia/Dhaka" <?php selected($settings['timezone'] ?? 'Asia/Dhaka', 'Asia/Dhaka'); ?>>Asia/Dhaka</option>
                                <option value="UTC" <?php selected($settings['timezone'] ?? 'Asia/Dhaka', 'UTC'); ?>>UTC</option>
                                <option value="America/New_York" <?php selected($settings['timezone'] ?? 'Asia/Dhaka', 'America/New_York'); ?>>America/New_York</option>
                                <option value="Europe/London" <?php selected($settings['timezone'] ?? 'Asia/Dhaka', 'Europe/London'); ?>>Europe/London</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- AI Integration Settings -->
                <div id="ai-integration" class="biic-settings-panel">
                    <div class="biic-panel-header">
                        <h2><?php esc_html_e('AI Integration', 'banglay-ielts-chatbot'); ?></h2>
                        <p><?php esc_html_e('Configure OpenAI integration for intelligent responses.', 'banglay-ielts-chatbot'); ?></p>
                    </div>
                    
                    <div class="biic-settings-section">
                        <h3><?php esc_html_e('OpenAI Configuration', 'banglay-ielts-chatbot'); ?></h3>
                        
                        <div class="biic-setting-item biic-setting-full">
                            <label for="openai_api_key"><?php esc_html_e('OpenAI API Key', 'banglay-ielts-chatbot'); ?></label>
                            <input type="password" id="openai_api_key" name="openai_api_key" class="large-text" value="<?php echo esc_attr($settings['openai_api_key'] ?? ''); ?>" placeholder="sk-...">
                            <p class="description">
                                <?php printf(
                                    esc_html__('Get your API key from %s. Keep this secure!', 'banglay-ielts-chatbot'),
                                    '<a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>'
                                ); ?>
                            </p>
                        </div>
                        
                        <div class="biic-setting-item biic-setting-row">
                            <div class="biic-setting-col">
                                <label for="ai_model"><?php esc_html_e('AI Model', 'banglay-ielts-chatbot'); ?></label>
                                <select id="ai_model" name="ai_model">
                                    <option value="gpt-3.5-turbo" <?php selected($settings['ai_model'] ?? 'gpt-3.5-turbo', 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
                                    <option value="gpt-4" <?php selected($settings['ai_model'] ?? 'gpt-3.5-turbo', 'gpt-4'); ?>>GPT-4</option>
                                    <option value="gpt-4-turbo" <?php selected($settings['ai_model'] ?? 'gpt-3.5-turbo', 'gpt-4-turbo'); ?>>GPT-4 Turbo</option>
                                </select>
                            </div>
                            
                            <div class="biic-setting-col">
                                <label for="ai_temperature"><?php esc_html_e('Creativity (0-1)', 'banglay-ielts-chatbot'); ?></label>
                                <input type="number" id="ai_temperature" name="ai_temperature" min="0" max="1" step="0.1" value="<?php echo esc_attr($settings['ai_temperature'] ?? '0.7'); ?>">
                                <p class="description"><?php esc_html_e('Higher values = more creative responses.', 'banglay-ielts-chatbot'); ?></p>
                            </div>
                        </div>
                        
                        <div class="biic-ai-test-section">
                            <button type="button" class="button button-secondary" onclick="biicTestAI()">
                                <span class="dashicons dashicons-superhero"></span>
                                <?php esc_html_e('Test AI Connection', 'banglay-ielts-chatbot'); ?>
                            </button>
                            <div id="ai-test-result" class="biic-test-result"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Notifications Settings -->
                <div id="notifications" class="biic-settings-panel">
                    <div class="biic-panel-header">
                        <h2><?php esc_html_e('Notification Settings', 'banglay-ielts-chatbot'); ?></h2>
                        <p><?php esc_html_e('Configure email notifications and alerts.', 'banglay-ielts-chatbot'); ?></p>
                    </div>
                    
                    <div class="biic-settings-section">
                        <h3><?php esc_html_e('Lead Notifications', 'banglay-ielts-chatbot'); ?></h3>
                        
                        <div class="biic-setting-item">
                            <div class="biic-setting-control">
                                <label class="biic-toggle">
                                    <input type="checkbox" name="lead_notifications" value="1" <?php checked($settings['lead_notifications'] ?? true); ?>>
                                    <span class="biic-toggle-slider"></span>
                                </label>
                            </div>
                            <div class="biic-setting-info">
                                <h4><?php esc_html_e('New Lead Alerts', 'banglay-ielts-chatbot'); ?></h4>
                                <p><?php esc_html_e('Send email notifications when new leads are captured.', 'banglay-ielts-chatbot'); ?></p>
                            </div>
                        </div>
                        
                        <div class="biic-setting-item biic-setting-full">
                            <label for="notification_email"><?php esc_html_e('Notification Email', 'banglay-ielts-chatbot'); ?></label>
                            <input type="email" id="notification_email" name="notification_email" class="regular-text" value="<?php echo esc_attr($settings['notification_email'] ?? get_option('admin_email')); ?>">
                            <p class="description"><?php esc_html_e('Email address to receive notifications.', 'banglay-ielts-chatbot'); ?></p>
                        </div>
                    </div>
                    
                    <div class="biic-settings-section">
                        <h3><?php esc_html_e('Email Templates', 'banglay-ielts-chatbot'); ?></h3>
                        
                        <div class="biic-setting-item biic-setting-full">
                            <label for="new_lead_email_subject"><?php esc_html_e('New Lead Email Subject', 'banglay-ielts-chatbot'); ?></label>
                            <input type="text" id="new_lead_email_subject" name="new_lead_email_subject" class="large-text" value="<?php echo esc_attr($settings['new_lead_email_subject'] ?? 'New Lead from Banglay IELTS Chatbot'); ?>">
                        </div>
                        
                        <div class="biic-setting-item biic-setting-full">
                            <label for="new_lead_email_template"><?php esc_html_e('New Lead Email Template', 'banglay-ielts-chatbot'); ?></label>
                            <textarea id="new_lead_email_template" name="new_lead_email_template" class="large-text" rows="8"><?php echo esc_textarea($settings['new_lead_email_template'] ?? 'New lead received from chatbot:\n\nName: {name}\nPhone: {phone}\nEmail: {email}\nCourse Interest: {course_interest}'); ?></textarea>
                            <p class="description"><?php esc_html_e('Use placeholders: {name}, {phone}, {email}, {course_interest}', 'banglay-ielts-chatbot'); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Advanced Settings -->
                <div id="advanced" class="biic-settings-panel">
                    <div class="biic-panel-header">
                        <h2><?php esc_html_e('Advanced Settings', 'banglay-ielts-chatbot'); ?></h2>
                        <p><?php esc_html_e('Advanced configuration options for developers.', 'banglay-ielts-chatbot'); ?></p>
                    </div>
                    
                    <div class="biic-settings-section">
                        <h3><?php esc_html_e('Analytics & Data', 'banglay-ielts-chatbot'); ?></h3>
                        
                        <div class="biic-setting-item">
                            <div class="biic-setting-control">
                                <label class="biic-toggle">
                                    <input type="checkbox" name="analytics_enabled" value="1" <?php checked($settings['analytics_enabled'] ?? true); ?>>
                                    <span class="biic-toggle-slider"></span>
                                </label>
                            </div>
                            <div class="biic-setting-info">
                                <h4><?php esc_html_e('Enable Analytics', 'banglay-ielts-chatbot'); ?></h4>
                                <p><?php esc_html_e('Track user interactions and conversation analytics.', 'banglay-ielts-chatbot'); ?></p>
                            </div>
                        </div>
                        
                        <div class="biic-setting-item biic-setting-row">
                            <div class="biic-setting-col">
                                <label for="data_retention_days"><?php esc_html_e('Data Retention (Days)', 'banglay-ielts-chatbot'); ?></label>
                                <input type="number" id="data_retention_days" name="data_retention_days" min="30" max="365" value="<?php echo esc_attr($settings['data_retention_days'] ?? 365); ?>">
                                <p class="description"><?php esc_html_e('How long to keep conversation data.', 'banglay-ielts-chatbot'); ?></p>
                            </div>
                            
                            <div class="biic-setting-col">
                                <label for="api_rate_limit"><?php esc_html_e('API Rate Limit', 'banglay-ielts-chatbot'); ?></label>
                                <input type="number" id="api_rate_limit" name="api_rate_limit" min="10" max="1000" value="<?php echo esc_attr($settings['api_rate_limit'] ?? 60); ?>">
                                <p class="description"><?php esc_html_e('Requests per minute per user.', 'banglay-ielts-chatbot'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="biic-settings-section">
                        <h3><?php esc_html_e('Custom CSS', 'banglay-ielts-chatbot'); ?></h3>
                        
                        <div class="biic-setting-item biic-setting-full">
                            <label for="custom_css"><?php esc_html_e('Custom CSS Code', 'banglay-ielts-chatbot'); ?></label>
                            <textarea id="custom_css" name="custom_css" class="large-text code" rows="10"><?php echo esc_textarea($settings['custom_css'] ?? ''); ?></textarea>
                            <p class="description"><?php esc_html_e('Add custom CSS to override chatbot styles.', 'banglay-ielts-chatbot'); ?></p>
                        </div>
                    </div>
                    
                    <div class="biic-settings-section">
                        <h3><?php esc_html_e('Database Management', 'banglay-ielts-chatbot'); ?></h3>
                        
                        <div class="biic-setting-item">
                            <div class="biic-setting-control">
                                <label class="biic-toggle">
                                    <input type="checkbox" name="remove_data_on_uninstall" value="1" <?php checked($settings['remove_data_on_uninstall'] ?? false); ?>>
                                    <span class="biic-toggle-slider"></span>
                                </label>
                            </div>
                            <div class="biic-setting-info">
                                <h4><?php esc_html_e('Remove Data on Uninstall', 'banglay-ielts-chatbot'); ?></h4>
                                <p class="biic-warning"><?php esc_html_e('⚠️ WARNING: This will permanently delete all chatbot data when plugin is uninstalled.', 'banglay-ielts-chatbot'); ?></p>
                            </div>
                        </div>
                        
                        <div class="biic-database-actions">
                            <button type="button" class="button button-secondary" onclick="biicExportData()">
                                <span class="dashicons dashicons-download"></span>
                                <?php esc_html_e('Export All Data', 'banglay-ielts-chatbot'); ?>
                            </button>
                            
                            <button type="button" class="button button-secondary biic-danger" onclick="biicCleanupData()">
                                <span class="dashicons dashicons-trash"></span>
                                <?php esc_html_e('Cleanup Old Data', 'banglay-ielts-chatbot'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        
        <!-- Save Button -->
        <div class="biic-settings-footer">
            <div class="biic-save-section">
                <button type="submit" class="button button-primary button-large">
                    <span class="dashicons dashicons-yes"></span>
                    <?php esc_html_e('Save Settings', 'banglay-ielts-chatbot'); ?>
                </button>
                
                <span class="biic-save-status" id="save-status"></span>
            </div>
        </div>
        
    </form>
    
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Initialize settings interface
    biicInitSettingsInterface();
});

function biicInitSettingsInterface() {
    var $ = jQuery;
    
    // Tab navigation
    $('.biic-nav-link').on('click', function(e) {
        e.preventDefault();
        
        var targetTab = $(this).data('tab');
        
        // Remove active class from all tabs and panels
        $('.biic-nav-link').removeClass('active');
        $('.biic-settings-panel').removeClass('active');
        
        // Add active class to clicked tab and corresponding panel
        $(this).addClass('active');
        $('#' + targetTab).addClass('active');
        
        // Update URL hash
        window.location.hash = targetTab;
    });
    
    // Load tab from hash on page load
    if (window.location.hash) {
        var hash = window.location.hash.substring(1);
        $('[data-tab="' + hash + '"]').click();
    }
    
    // Form submission with AJAX
    $('#biic-settings-form').on('submit', function(e) {
        e.preventDefault();
        biicSaveSettings();
    });
    
    // Auto-save draft on input change
    $('.biic-settings-panel input, .biic-settings-panel textarea, .biic-settings-panel select').on('change', function() {
        biicSaveDraft();
    });
    
    // Color picker enhancement
    $('#primary_color').on('change', function() {
        biicPreviewColorChange($(this).val());
    });
}

function biicSaveSettings() {
    var $ = jQuery;
    var form = $('#biic-settings-form');
    var submitBtn = form.find('button[type="submit"]');
    var saveStatus = $('#save-status');
    
    submitBtn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Saving...');
    saveStatus.removeClass().addClass('biic-save-status saving').text('Saving settings...');
    
    $.post(ajaxurl, {
        action: 'biic_save_settings',
        settings_data: form.serialize(),
        nonce: biic_admin.nonce
    }, function(response) {
        if (response.success) {
            saveStatus.removeClass().addClass('biic-save-status success').text('✅ Settings saved successfully!');
            
            // Show success notice
            $('<div class="notice notice-success is-dismissible"><p>Settings have been saved successfully!</p></div>')
                .insertAfter('.biic-admin-header').delay(3000).fadeOut();
        } else {
            saveStatus.removeClass().addClass('biic-save-status error').text('❌ Failed to save settings');
        }
    }).fail(function() {
        saveStatus.removeClass().addClass('biic-save-status error').text('❌ Network error occurred');
    }).always(function() {
        submitBtn.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> Save Settings');
        
        setTimeout(function() {
            saveStatus.text('');
        }, 5000);
    });
}

function biicSaveDraft() {
    // Auto-save draft functionality
    localStorage.setItem('biic_settings_draft', $('#biic-settings-form').serialize());
}

function biicTestAI() {
    var $ = jQuery;
    var apiKey = $('#openai_api_key').val();
    var resultDiv = $('#ai-test-result');
    
    if (!apiKey) {
        resultDiv.html('<div class="biic-test-error">❌ Please enter your OpenAI API key first.</div>');
        return;
    }
    
    resultDiv.html('<div class="biic-test-loading">🔄 Testing AI connection...</div>');
    
    $.post(ajaxurl, {
        action: 'biic_test_ai_connection',
        api_key: apiKey,
        nonce: biic_admin.nonce
    }, function(response) {
        if (response.success) {
            resultDiv.html('<div class="biic-test-success">✅ AI connection successful! Model: ' + response.data.model + '</div>');
        } else {
            resultDiv.html('<div class="biic-test-error">❌ Connection failed: ' + response.data.message + '</div>');
        }
    }).fail(function() {
        resultDiv.html('<div class="biic-test-error">❌ Network error occurred</div>');
    });
}

function biicPreviewColorChange(color) {
    // Live preview of color changes
    $('<style id="biic-color-preview">:root { --biic-primary: ' + color + '; }</style>').appendTo('head');
    $('#biic-color-preview').remove();
    $('<style id="biic-color-preview">:root { --biic-primary: ' + color + '; }</style>').appendTo('head');
}

function biicResetSettings() {
    if (confirm('Are you sure you want to reset all settings to defaults? This cannot be undone.')) {
        $.post(ajaxurl, {
            action: 'biic_reset_settings',
            nonce: biic_admin.nonce
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Failed to reset settings.');
            }
        });
    }
}

function biicExportSettings() {
    window.open(ajaxurl + '?action=biic_export_settings&nonce=' + biic_admin.nonce, '_blank');
}

function biicExportData() {
    if (confirm('This will export all chatbot data. Continue?')) {
        window.open(ajaxurl + '?action=biic_export_all_data&nonce=' + biic_admin.nonce, '_blank');
    }
}

function biicCleanupData() {
    if (confirm('This will permanently delete old conversation data. Continue?')) {
        $.post(ajaxurl, {
            action: 'biic_cleanup_old_data',
            nonce: biic_admin.nonce
        }, function(response) {
            if (response.success) {
                alert('Data cleanup completed. ' + response.data.deleted + ' old records removed.');
            } else {
                alert('Cleanup failed: ' + response.data.message);
            }
        });
    }
}
</script>

<style>
/* Settings page specific styles */
.biic-settings-content {
    display: flex;
    gap: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.biic-settings-nav {
    width: 240px;
    background: #f8f9fa;
    border-right: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.biic-nav-tabs {
    list-style: none;
    margin: 0;
    padding: 0;
}

.biic-nav-item {
    border-bottom: 1px solid #e5e7eb;
}

.biic-nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    text-decoration: none;
    color: #6b7280;
    font-weight: 500;
    transition: all 0.2s ease;
}

.biic-nav-link:hover,
.biic-nav-link:focus {
    background: #e5e7eb;
    color: #374151;
    text-decoration: none;
}

.biic-nav-link.active {
    background: var(--biic-admin-primary);
    color: white;
}

.biic-nav-link .dashicons {
    font-size: 16px;
}

.biic-settings-panels {
    flex: 1;
    min-height: 600px;
}

.biic-settings-panel {
    display: none;
    padding: 32px;
    height: 100%;
    overflow-y: auto;
}

.biic-settings-panel.active {
    display: block;
}

.biic-panel-header {
    margin-bottom: 32px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.biic-panel-header h2 {
    margin: 0 0 8px 0;
    font-size: 24px;
    font-weight: 600;
    color: #1f2937;
}

.biic-panel-header p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}

.biic-settings-section {
    margin-bottom: 40px;
}

.biic-settings-section h3 {
    margin: 0 0 20px 0;
    font-size: 18px;
    font-weight: 600;
    color: #374151;
    padding-bottom: 8px;
    border-bottom: 1px solid #f3f4f6;
}

.biic-setting-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 24px;
    padding: 20px;
    border: 1px solid #f3f4f6;
    border-radius: 8px;
    transition: border-color 0.2s ease;
}

.biic-setting-item:hover {
    border-color: #e5e7eb;
}

.biic-setting-item.biic-setting-full {
    flex-direction: column;
    align-items: stretch;
}

.biic-setting-item.biic-setting-row {
    flex-direction: row;
    align-items: flex-start;
}

.biic-setting-col {
    flex: 1;
}

.biic-setting-control {
    flex-shrink: 0;
}

.biic-setting-info {
    flex: 1;
}

.biic-setting-info h4 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 500;
    color: #1f2937;
}

.biic-setting-info p {
    margin: 0;
    font-size: 13px;
    color: #6b7280;
    line-height: 1.4;
}

/* Toggle Switch */
.biic-toggle {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
}

.biic-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.biic-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #cbd5e1;
    transition: 0.3s;
    border-radius: 24px;
}

.biic-toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.biic-toggle input:checked + .biic-toggle-slider {
    background-color: var(--biic-admin-primary);
}

.biic-toggle input:checked + .biic-toggle-slider:before {
    transform: translateX(20px);
}

/* Form Elements */
.biic-setting-item label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #374151;
    font-size: 14px;
}

.biic-setting-item input[type="text"],
.biic-setting-item input[type="email"],
.biic-setting-item input[type="password"],
.biic-setting-item input[type="number"],
.biic-setting-item input[type="time"],
.biic-setting-item input[type="color"],
.biic-setting-item select,
.biic-setting-item textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s ease;
}

.biic-setting-item input:focus,
.biic-setting-item select:focus,
.biic-setting-item textarea:focus {
    outline: none;
    border-color: var(--biic-admin-primary);
    box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.1);
}

.biic-setting-item .description {
    margin-top: 6px;
    font-size: 12px;
    color: #6b7280;
    font-style: italic;
}

/* Checkbox Group */
.biic-checkbox-group {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 12px;
}

.biic-checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    cursor: pointer;
}

.biic-checkbox-label input[type="checkbox"] {
    width: auto;
}

/* Test Results */
.biic-test-result {
    margin-top: 12px;
    padding: 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
}

.biic-test-loading {
    background: #f0f9ff;
    color: #0369a1;
    border: 1px solid #bae6fd;
}

.biic-test-success {
    background: #f0fdf4;
    color: #15803d;
    border: 1px solid #bbf7d0;
}

.biic-test-error {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

/* Database Actions */
.biic-database-actions {
    display: flex;
    gap: 12px;
    margin-top: 16px;
}

.biic-database-actions .button.biic-danger {
    background: #dc2626;
    border-color: #dc2626;
    color: white;
}

.biic-database-actions .button.biic-danger:hover {
    background: #b91c1c;
    border-color: #b91c1c;
}

/* AI Test Section */
.biic-ai-test-section {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #f3f4f6;
}

/* Warning Text */
.biic-warning {
    color: #d97706 !important;
    font-weight: 500;
}

/* Settings Footer */
.biic-settings-footer {
    padding: 24px 32px;
    background: #f8f9fa;
    border-top: 1px solid #e5e7eb;
    margin-top: 24px;
}

.biic-save-section {
    display: flex;
    align-items: center;
    gap: 16px;
}

.biic-save-status {
    font-size: 14px;
    font-weight: 500;
}

.biic-save-status.saving {
    color: #0369a1;
}

.biic-save-status.success {
    color: #15803d;
}

.biic-save-status.error {
    color: #dc2626;
}

/* Animations */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.spin {
    animation: spin 1s linear infinite;
}

/* Responsive Design */
@media (max-width: 768px) {
    .biic-settings-content {
        flex-direction: column;
    }
    
    .biic-settings-nav {
        width: 100%;
    }
    
    .biic-nav-tabs {
        display: flex;
        overflow-x: auto;
        white-space: nowrap;
    }
    
    .biic-nav-item {
        border-bottom: none;
        border-right: 1px solid #e5e7eb;
        flex-shrink: 0;
    }
    
    .biic-nav-item:last-child {
        border-right: none;
    }
    
    .biic-settings-panel {
        padding: 20px;
    }
    
    .biic-setting-item.biic-setting-row {
        flex-direction: column;
    }
    
    .biic-checkbox-group {
        grid-template-columns: 1fr;
    }
    
    .biic-database-actions {
        flex-direction: column;
    }
    
    .biic-save-section {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .biic-panel-header h2 {
        font-size: 20px;
    }
    
    .biic-settings-section h3 {
        font-size: 16px;
    }
    
    .biic-setting-item {
        padding: 16px;
    }
}