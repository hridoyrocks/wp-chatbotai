<?php
/**
 * Chatbot Widget Template
 * Professional Grade UI with "Made with Love Rocks" branding
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get chatbot settings
$chatbot_enabled = get_option('biic_chatbot_enabled', true);
$chat_position = get_option('biic_chat_position', 'bottom-right');
$chat_theme = get_option('biic_chat_theme', 'modern');
$welcome_message = get_option('biic_welcome_message', 'আস্সালামু আলাইকুম! IELTS এর ব্যাপারে কিছু জানতে চান?');

// Don't show if disabled
if (!$chatbot_enabled) {
    return;
}

// Position classes
$position_class = 'biic-position-' . str_replace('-', '_', $chat_position);
?>

<div class="biic-chatbot-widget <?php echo esc_attr($position_class); ?>" data-theme="<?php echo esc_attr($chat_theme); ?>">
    
    <!-- Floating Action Button -->
    <button class="biic-chat-fab" type="button" aria-label="<?php esc_attr_e('চ্যাট খুলুন', 'banglay-ielts-chatbot'); ?>">
        <svg class="biic-chat-fab-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2C6.48 2 2 6.48 2 12C2 13.54 2.36 14.99 3.01 16.26L2 22L7.74 20.99C9.01 21.64 10.46 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C10.74 20 9.54 19.72 8.5 19.22L8.19 19.05L4.55 20.05L5.55 16.41L5.38 16.1C4.88 15.06 4.6 13.86 4.6 12.6C4.6 7.91 8.31 4.2 13 4.2C17.69 4.2 21.4 7.91 21.4 12.6C21.4 17.29 17.69 21 13 21H12Z" fill="currentColor"/>
            <path d="M16.5 13.5C16.11 13.78 15.68 14 15.2 14.18C14.72 14.36 14.2 14.45 13.66 14.45C12.96 14.45 12.3 14.26 11.68 13.88C11.06 13.5 10.54 12.98 10.12 12.32C9.7 11.66 9.49 10.94 9.49 10.16C9.49 9.62 9.58 9.1 9.76 8.62C9.94 8.14 10.16 7.71 10.42 7.32L10.89 6.66C11.1 6.35 11.35 6.09 11.63 5.88C11.91 5.67 12.21 5.56 12.53 5.56C12.77 5.56 12.99 5.61 13.19 5.71C13.39 5.81 13.55 5.95 13.67 6.13L14.5 7.29C14.58 7.41 14.64 7.54 14.68 7.68C14.72 7.82 14.74 7.95 14.74 8.07C14.74 8.25 14.69 8.42 14.59 8.58C14.49 8.74 14.36 8.88 14.2 9L13.83 9.32C13.75 9.38 13.69 9.45 13.65 9.53C13.61 9.61 13.59 9.7 13.59 9.8C13.59 9.86 13.6 9.91 13.62 9.95C13.64 9.99 13.67 10.03 13.71 10.07L14.5 11.09C14.7 11.37 14.94 11.6 15.22 11.78C15.5 11.96 15.81 12.05 16.15 12.05C16.23 12.05 16.31 12.04 16.38 12.02C16.45 12 16.51 11.97 16.56 11.93L16.83 11.7C16.95 11.6 17.08 11.52 17.22 11.46C17.36 11.4 17.51 11.37 17.67 11.37C17.79 11.37 17.9 11.39 18 11.43C18.1 11.47 18.19 11.53 18.27 11.61L19.43 12.44C19.61 12.56 19.75 12.72 19.85 12.92C19.95 13.12 20 13.34 20 13.58C20 13.9 19.89 14.2 19.67 14.48C19.45 14.76 19.15 15 18.77 15.2C18.39 15.4 17.96 15.5 17.48 15.5C17.15 15.5 16.82 15.43 16.5 15.29V13.5Z" fill="currentColor"/>
        </svg>
        
        <!-- Notification Badge (hidden by default) -->
        <div class="biic-chat-notification" style="display: none;">1</div>
    </button>
    
    <!-- Chat Window -->
    <div class="biic-chat-window">
        
        <!-- Chat Header -->
        <div class="biic-chat-header">
            <div class="biic-chat-header-info">
                <div class="biic-chat-avatar">বট</div>
                <div class="biic-chat-header-text">
                    <h3><?php esc_html_e('Banglay IELTS সহায়ক', 'banglay-ielts-chatbot'); ?></h3>
                    <p>
                        <span class="biic-online-indicator"></span>
                        <?php esc_html_e('অনলাইনে আছি', 'banglay-ielts-chatbot'); ?>
                    </p>
                </div>
            </div>
            <button class="biic-chat-close" type="button" aria-label="<?php esc_attr_e('চ্যাট বন্ধ করুন', 'banglay-ielts-chatbot'); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        
        <!-- Messages Container -->
        <div class="biic-chat-messages">
            <!-- Messages will be dynamically added here -->
        </div>
        
        <!-- Chat Input -->
        <div class="biic-chat-input">
            <div class="biic-input-container">
                <textarea 
                    class="biic-message-input" 
                    placeholder="<?php esc_attr_e('আপনার প্রশ্ন লিখুন...', 'banglay-ielts-chatbot'); ?>"
                    rows="1"
                    maxlength="1000"
                ></textarea>
                
                <!-- File Upload Button (Optional) -->
                <?php if (get_option('biic_allow_file_upload', false)): ?>
                <button class="biic-file-button" type="button" title="<?php esc_attr_e('ফাইল আপলোড', 'banglay-ielts-chatbot'); ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21.44 11.05L12.25 20.24C11.84 20.65 11.35 20.98 10.8 21.21C10.25 21.44 9.66 21.56 9.06 21.56C8.46 21.56 7.87 21.44 7.32 21.21C6.77 20.98 6.28 20.65 5.87 20.24C5.46 19.83 5.13 19.34 4.9 18.79C4.67 18.24 4.55 17.65 4.55 17.05C4.55 16.45 4.67 15.86 4.9 15.31C5.13 14.76 5.46 14.27 5.87 13.86L15.06 4.67C15.84 3.89 16.9 3.45 18 3.45C19.1 3.45 20.16 3.89 20.94 4.67C21.72 5.45 22.16 6.51 22.16 7.61C22.16 8.71 21.72 9.77 20.94 10.55L11.75 19.74C11.36 20.13 10.84 20.35 10.3 20.35C9.76 20.35 9.24 20.13 8.85 19.74C8.46 19.35 8.24 18.83 8.24 18.29C8.24 17.75 8.46 17.23 8.85 16.84L17.34 8.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <?php endif; ?>
                
                <!-- Send Button -->
                <button class="biic-send-button" type="button" aria-label="<?php esc_attr_e('পাঠান', 'banglay-ielts-chatbot'); ?>">
                    <svg class="biic-send-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22 2L11 13M22 2L15 22L11 13M22 2L2 9L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Chat Footer with Copyright -->
        <div class="biic-chat-footer">
            <div class="biic-copyright">
                <?php esc_html_e('Made with', 'banglay-ielts-chatbot'); ?>
                <span class="biic-copyright-heart">❤️</span>
                <a href="#" class="biic-copyright-link" target="_blank">
                    <?php esc_html_e('Rocks', 'banglay-ielts-chatbot'); ?>
                </a>
            </div>
        </div>
        
    </div>
    
</div>

<!-- Pre-load Quick Replies Template (Hidden) -->
<template id="biic-quick-replies-template">
    <div class="biic-quick-replies">
        <!-- Quick reply buttons will be inserted here -->
    </div>
</template>

<!-- Pre-load Lead Form Template (Hidden) -->
<template id="biic-lead-form-template">
    <div class="biic-lead-form">
        <div class="biic-form-title">📚 <?php esc_html_e('আরও তথ্য পেতে যোগাযোগের বিস্তারিত দিন', 'banglay-ielts-chatbot'); ?></div>
        
        <div class="biic-form-group">
            <label class="biic-form-label"><?php esc_html_e('নাম', 'banglay-ielts-chatbot'); ?> *</label>
            <input type="text" class="biic-form-input" name="name" placeholder="<?php esc_attr_e('আপনার নাম লিখুন', 'banglay-ielts-chatbot'); ?>" required>
        </div>
        
        <div class="biic-form-group">
            <label class="biic-form-label"><?php esc_html_e('ফোন নম্বর', 'banglay-ielts-chatbot'); ?> *</label>
            <input type="tel" class="biic-form-input" name="phone" placeholder="+880 1X XX XX XX XX" required>
        </div>
        
        <div class="biic-form-group">
            <label class="biic-form-label"><?php esc_html_e('ইমেইল', 'banglay-ielts-chatbot'); ?></label>
            <input type="email" class="biic-form-input" name="email" placeholder="<?php esc_attr_e('আপনার ইমেইল (ঐচ্ছিক)', 'banglay-ielts-chatbot'); ?>">
        </div>
        
        <div class="biic-form-group">
            <label class="biic-form-label"><?php esc_html_e('কোর্সের আগ্রহ', 'banglay-ielts-chatbot'); ?></label>
            <select class="biic-form-input" name="course_interest">
                <option value=""><?php esc_html_e('কোর্স নির্বাচন করুন', 'banglay-ielts-chatbot'); ?></option>
                <option value="ielts_comprehensive"><?php esc_html_e('IELTS Comprehensive (4.5 months)', 'banglay-ielts-chatbot'); ?></option>
                <option value="ielts_focus"><?php esc_html_e('IELTS Focus (3 months)', 'banglay-ielts-chatbot'); ?></option>
                <option value="ielts_crash"><?php esc_html_e('IELTS Crash (1.5 months)', 'banglay-ielts-chatbot'); ?></option>
                <option value="online_course"><?php esc_html_e('Online Course (2 months)', 'banglay-ielts-chatbot'); ?></option>
                <option value="speaking_only"><?php esc_html_e('Speaking Only', 'banglay-ielts-chatbot'); ?></option>
                <option value="study_abroad"><?php esc_html_e('Study Abroad Consultation', 'banglay-ielts-chatbot'); ?></option>
            </select>
        </div>
        
        <button type="submit" class="biic-form-submit">
            📞 <?php esc_html_e('ফ্রি কনসালটেশন বুক করুন', 'banglay-ielts-chatbot'); ?>
        </button>
    </div>
</template>

<!-- Chat Widget Styles (Inline for better performance) -->
<style>
/* Position classes */
.biic-chatbot-widget.biic-position-bottom_right {
    bottom: 24px;
    right: 24px;
}

.biic-chatbot-widget.biic-position-bottom_left {
    bottom: 24px;
    left: 24px;
}

.biic-chatbot-widget.biic-position-top_right {
    top: 24px;
    right: 24px;
}

.biic-chatbot-widget.biic-position-top_left {
    top: 24px;
    left: 24px;
}

/* Theme variations */
.biic-chatbot-widget[data-theme="dark"] .biic-chat-window {
    background: #1a202c;
    color: #f7fafc;
}

.biic-chatbot-widget[data-theme="dark"] .biic-chat-messages {
    background: #2d3748;
}

.biic-chatbot-widget[data-theme="dark"] .biic-message-bubble {
    background: #4a5568;
    color: #f7fafc;
}

.biic-chatbot-widget[data-theme="minimal"] .biic-chat-window {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border: 1px solid #e2e8f0;
}

.biic-chatbot-widget[data-theme="minimal"] .biic-chat-header {
    background: #f8f9fa;
    color: #2d3748;
}

/* Business hours indicator */
.biic-business-hours {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.8);
    margin-top: 2px;
}

.biic-business-hours.closed {
    color: #fbb6ce;
}

.biic-business-hours.closed::before {
    content: "⏰ ";
}

/* Accessibility improvements */
.biic-chat-fab:focus,
.biic-send-button:focus,
.biic-chat-close:focus {
    outline: 2px solid #4299e1;
    outline-offset: 2px;
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .biic-chatbot-widget *,
    .biic-chatbot-widget *::before,
    .biic-chatbot-widget *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }
}

/* High contrast support */
@media (prefers-contrast: high) {
    .biic-chat-window {
        border: 2px solid #000;
    }
    
    .biic-message-bubble {
        border: 1px solid #000;
    }
}

/* Print styles */
@media print {
    .biic-chatbot-widget {
        display: none !important;
    }
}
</style>

<!-- Schema.org markup for better SEO -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "ChatBot",
    "name": "Banglay IELTS AI Assistant",
    "description": "Professional AI-powered chatbot for IELTS course inquiries and study abroad consultation",
    "provider": {
        "@type": "Organization",
        "name": "Banglay IELTS & Immigration Center",
        "url": "https://banglayelts.com"
    },
    "applicationCategory": "Educational Technology",
    "operatingSystem": "Web Browser",
    "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "BDT",
        "description": "Free IELTS consultation and course guidance"
    }
}
</script>

<?php
// Add business hours check
$business_hours = get_option('biic_business_hours', array(
    'start' => '10:00',
    'end' => '18:00',
    'days' => array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday')
));

$current_time = current_time('H:i');
$current_day = strtolower(current_time('l'));
$is_business_hours = in_array($current_day, $business_hours['days']) && 
                    $current_time >= $business_hours['start'] && 
                    $current_time <= $business_hours['end'];

// Pass data to JavaScript
?>
<script>
window.biicChatbotConfig = {
    isBusinessHours: <?php echo json_encode($is_business_hours); ?>,
    businessHours: <?php echo json_encode($business_hours); ?>,
    welcomeMessage: <?php echo json_encode($welcome_message); ?>,
    currentTime: '<?php echo current_time('H:i'); ?>',
    currentDay: '<?php echo $current_day; ?>',
    timezone: '<?php echo get_option('biic_timezone', 'Asia/Dhaka'); ?>',
    locale: '<?php echo get_locale(); ?>',
    ajax: {
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        nonce: '<?php echo wp_create_nonce('biic_chat_nonce'); ?>'
    },
    settings: {
        enableSounds: <?php echo json_encode(get_option('biic_enable_sounds', true)); ?>,
        enableAnimations: <?php echo json_encode(get_option('biic_enable_animations', true)); ?>,
        maxMessageLength: <?php echo intval(get_option('biic_max_message_length', 1000)); ?>,
        typingSpeed: <?php echo intval(get_option('biic_typing_speed', 50)); ?>,
        autoGreeting: <?php echo json_encode(get_option('biic_auto_greeting', true)); ?>
    }
};
</script>