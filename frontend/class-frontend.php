<?php
/**
 * Frontend Controller for Banglay IELTS Chatbot
 * Handles all public-facing functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BIIC_Frontend {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize frontend hooks
     */
    private function init_hooks() {
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // Add chatbot widget to footer
        add_action('wp_footer', array($this, 'render_chatbot_widget'));
        
        // AJAX handlers for public users
        add_action('wp_ajax_biic_chat_message', array($this, 'handle_chat_message'));
        add_action('wp_ajax_nopriv_biic_chat_message', array($this, 'handle_chat_message'));
        
        add_action('wp_ajax_biic_submit_lead', array($this, 'handle_lead_submission'));
        add_action('wp_ajax_nopriv_biic_submit_lead', array($this, 'handle_lead_submission'));
        
        add_action('wp_ajax_biic_track_event', array($this, 'handle_event_tracking'));
        add_action('wp_ajax_nopriv_biic_track_event', array($this, 'handle_event_tracking'));
        
        add_action('wp_ajax_biic_get_chat_history', array($this, 'handle_get_chat_history'));
        add_action('wp_ajax_nopriv_biic_get_chat_history', array($this, 'handle_get_chat_history'));
        
        // Shortcode support
        add_shortcode('biic_chatbot', array($this, 'chatbot_shortcode'));
        add_shortcode('biic_contact_form', array($this, 'contact_form_shortcode'));
        
        // Custom page templates
        add_filter('template_include', array($this, 'custom_page_templates'));
        
        // Add meta tags for SEO
        add_action('wp_head', array($this, 'add_meta_tags'));
        
        // Add structured data
        add_action('wp_footer', array($this, 'add_structured_data'));
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets() {
        // Don't load on admin pages
        if (is_admin()) return;
        
        // Check if chatbot is enabled
        if (!get_option('biic_chatbot_enabled', true)) return;
        
        // CSS
        wp_enqueue_style(
            'biic-chatbot-widget',
            BIIC_PLUGIN_ASSETS_URL . 'css/chatbot-widget.css',
            array(),
            BIIC_VERSION
        );
        
        // JavaScript
        wp_enqueue_script(
            'biic-chatbot-widget',
            BIIC_PLUGIN_ASSETS_URL . 'js/chatbot-widget.js',
            array('jquery'),
            BIIC_VERSION,
            true
        );
        
        wp_enqueue_script(
            'biic-user-tracking',
            BIIC_PLUGIN_ASSETS_URL . 'js/user-tracking.js',
            array('jquery'),
            BIIC_VERSION,
            true
        );
        
        // Localize script with settings
        $this->localize_scripts();
        
        // Add custom CSS if configured
        $this->add_custom_styles();
    }
    
    /**
     * Localize scripts with configuration
     */
    private function localize_scripts() {
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
        
        wp_localize_script('biic-chatbot-widget', 'biicConfig', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('biic_chat_nonce'),
            'restUrl' => rest_url('biic/v1/'),
            'pluginUrl' => BIIC_PLUGIN_URL,
            'isBusinessHours' => $is_business_hours,
            'businessHours' => $business_hours,
            'currentTime' => $current_time,
            'currentDay' => $current_day,
            'timezone' => get_option('biic_timezone', 'Asia/Dhaka'),
            'locale' => get_locale(),
            'settings' => array(
                'welcomeMessage' => get_option('biic_welcome_message', 'আস্সালামু আলাইকুম! IELTS সম্পর্কে কিছু জানতে চান?'),
                'position' => get_option('biic_chat_position', 'bottom-right'),
                'theme' => get_option('biic_chat_theme', 'modern'),
                'enableSounds' => get_option('biic_enable_sounds', true),
                'enableAnimations' => get_option('biic_enable_animations', true),
                'autoGreeting' => get_option('biic_auto_greeting', true),
                'maxMessageLength' => get_option('biic_max_message_length', 1000),
                'typingSpeed' => get_option('biic_typing_speed', 50)
            ),
            'strings' => array(
                'typing' => __('বট টাইপ করছে...', 'banglay-ielts-chatbot'),
                'error' => __('দুঃখিত, একটি সমস্যা হয়েছে।', 'banglay-ielts-chatbot'),
                'offline' => __('আমরা এখন অফলাইনে আছি।', 'banglay-ielts-chatbot'),
                'placeholder' => __('আপনার প্রশ্ন লিখুন...', 'banglay-ielts-chatbot'),
                'send' => __('পাঠান', 'banglay-ielts-chatbot'),
                'retry' => __('আবার চেষ্টা করুন', 'banglay-ielts-chatbot'),
                'copyright' => __('Made with Love Rocks', 'banglay-ielts-chatbot')
            ),
            'courses' => array(
                'ielts_comprehensive' => array(
                    'name' => 'IELTS Comprehensive',
                    'duration' => '4.5 months',
                    'classes' => '50+',
                    'level' => 'Beginner'
                ),
                'ielts_focus' => array(
                    'name' => 'IELTS Focus', 
                    'duration' => '3 months',
                    'classes' => '30+',
                    'level' => 'Intermediate'
                ),
                'ielts_crash' => array(
                    'name' => 'IELTS Crash',
                    'duration' => '1.5 months', 
                    'classes' => '30+',
                    'level' => 'Intensive'
                ),
                'online_course' => array(
                    'name' => 'Online Course',
                    'duration' => '2 months',
                    'classes' => 'Weekly 3/4',
                    'level' => 'All levels'
                )
            ),
            'contact' => array(
                'phone' => '+880 961 382 0821',
                'email' => 'info@biic.com.bd',
                'website' => 'https://banglayelts.com',
                'admission' => 'https://admission.banglayelts.com'
            )
        ));
    }
    
    /**
     * Add custom styles from admin settings
     */
    private function add_custom_styles() {
        $custom_css = get_option('biic_custom_css', '');
        $primary_color = get_option('biic_primary_color', '#E53E3E');
        $font_family = get_option('biic_font_family', '');
        
        if ($custom_css || $primary_color !== '#E53E3E' || $font_family) {
            echo '<style id="biic-custom-styles">';
            
            if ($primary_color !== '#E53E3E') {
                echo ":root { --biic-primary: {$primary_color}; }";
            }
            
            if ($font_family) {
                echo ".biic-chatbot-widget { font-family: {$font_family}; }";
            }
            
            if ($custom_css) {
                echo $custom_css;
            }
            
            echo '</style>';
        }
    }
    
    /**
     * Render chatbot widget
     */
    public function render_chatbot_widget() {
        // Don't show on admin pages
        if (is_admin()) return;
        
        // Check if chatbot is enabled
        if (!get_option('biic_chatbot_enabled', true)) return;
        
        // Check page restrictions
        if (!$this->should_show_chatbot()) return;
        
        // Include widget template
        include BIIC_FRONTEND_PATH . 'templates/chatbot-widget.php';
    }
    
    /**
     * Check if chatbot should be shown on current page
     */
    private function should_show_chatbot() {
        $show_on_pages = get_option('biic_show_on_pages', 'all');
        $exclude_pages = get_option('biic_exclude_pages', array());
        
        // Check exclusions
        if (is_array($exclude_pages) && !empty($exclude_pages)) {
            $current_page_id = get_the_ID();
            if (in_array($current_page_id, $exclude_pages)) {
                return false;
            }
        }
        
        // Check inclusions
        switch ($show_on_pages) {
            case 'homepage':
                return is_front_page();
            case 'posts':
                return is_single();
            case 'pages':
                return is_page();
            case 'custom':
                $include_pages = get_option('biic_include_pages', array());
                return is_array($include_pages) && in_array(get_the_ID(), $include_pages);
            case 'all':
            default:
                return true;
        }
    }
    
    /**
     * Handle chat message AJAX
     */
    public function handle_chat_message() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'biic_chat_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $message = sanitize_text_field($_POST['message']);
        $session_id = sanitize_text_field($_POST['session_id']);
        
        if (empty($message) || empty($session_id)) {
            wp_send_json_error('Missing required parameters');
        }
        
        // Rate limiting
        if (!$this->check_rate_limit($session_id)) {
            wp_send_json_error('Rate limit exceeded. Please wait before sending another message.');
        }
        
        // Process message through chatbot
        $chatbot = BIIC()->chatbot;
        $response = $chatbot->process_message($message, $session_id);
        
        if ($response['success']) {
            wp_send_json_success($response['data']);
        } else {
            wp_send_json_error($response['message'] ?? 'Failed to process message');
        }
    }
    
    /**
     * Handle lead submission
     */
    public function handle_lead_submission() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'biic_chat_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $lead_data = json_decode(stripslashes($_POST['lead_data']), true);
        
        // Validate required fields
        $required_fields = array('session_id', 'phone');
        foreach ($required_fields as $field) {
            if (empty($lead_data[$field])) {
                wp_send_json_error("Required field missing: {$field}");
            }
        }
        
        // Sanitize data
        $sanitized_data = array(
            'session_id' => sanitize_text_field($lead_data['session_id']),
            'name' => sanitize_text_field($lead_data['name'] ?? ''),
            'phone' => sanitize_text_field($lead_data['phone']),
            'email' => sanitize_email($lead_data['email'] ?? ''),
            'course_interest' => sanitize_text_field($lead_data['course_interest'] ?? ''),
            'lead_source' => 'chatbot'
        );
        
        // Validate phone number format
        if (!$this->validate_phone_number($sanitized_data['phone'])) {
            wp_send_json_error('Invalid phone number format');
        }
        
        // Validate email if provided
        if (!empty($sanitized_data['email']) && !is_email($sanitized_data['email'])) {
            wp_send_json_error('Invalid email format');
        }
        
        // Create lead using lead management system
        $lead_manager = BIIC()->lead_management;
        $lead_id = $lead_manager->create_lead($sanitized_data['session_id'], $sanitized_data);
        
        if ($lead_id) {
            wp_send_json_success(array(
                'message' => 'Lead submitted successfully',
                'lead_id' => $lead_id
            ));
        } else {
            wp_send_json_error('Failed to save lead');
        }
    }
    
    /**
     * Handle event tracking
     */
    public function handle_event_tracking() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'biic_chat_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $event_name = sanitize_text_field($_POST['event_name']);
        $event_data = json_decode(stripslashes($_POST['event_data']), true);
        
        // Track event using user tracking system
        $user_tracking = BIIC()->user_tracking;
        $result = $user_tracking->track_event($event_name, $event_data);
        
        if ($result) {
            wp_send_json_success('Event tracked');
        } else {
            wp_send_json_error('Failed to track event');
        }
    }
    
    /**
     * Handle get chat history
     */
    public function handle_get_chat_history() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'biic_chat_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $session_id = sanitize_text_field($_POST['session_id']);
        $limit = intval($_POST['limit'] ?? 50);
        
        if (empty($session_id)) {
            wp_send_json_error('Session ID required');
        }
        
        $database = BIIC()->database;
        $messages = $database->get_chat_messages($session_id, $limit);
        
        // Format messages for frontend
        $formatted_messages = array();
        foreach ($messages as $message) {
            $formatted_messages[] = array(
                'id' => $message->id,
                'type' => $message->message_type,
                'content' => $message->content,
                'timestamp' => $message->timestamp,
                'intent' => $message->detected_intent,
                'confidence' => $message->intent_confidence
            );
        }
        
        wp_send_json_success($formatted_messages);
    }
    
    /**
     * Chatbot shortcode
     */
    public function chatbot_shortcode($atts) {
        $atts = shortcode_atts(array(
            'position' => 'inline',
            'height' => '600px',
            'width' => '100%',
            'theme' => 'modern'
        ), $atts);
        
        // Enqueue assets if not already done
        if (!wp_script_is('biic-chatbot-widget', 'enqueued')) {
            $this->enqueue_assets();
        }
        
        ob_start();
        ?>
        <div class="biic-chatbot-shortcode" 
             data-position="<?php echo esc_attr($atts['position']); ?>"
             data-theme="<?php echo esc_attr($atts['theme']); ?>"
             style="height: <?php echo esc_attr($atts['height']); ?>; width: <?php echo esc_attr($atts['width']); ?>;">
            
            <?php if ($atts['position'] === 'inline'): ?>
                <!-- Inline chatbot interface -->
                <div class="biic-inline-chatbot">
                    <div class="biic-chat-header">
                        <h3>💬 Banglay IELTS সহায়ক</h3>
                        <p>IELTS সম্পর্কে যেকোনো প্রশ্ন করুন</p>
                    </div>
                    <div class="biic-chat-messages" style="height: calc(100% - 120px);"></div>
                    <div class="biic-chat-input">
                        <input type="text" placeholder="আপনার প্রশ্ন লিখুন..." />
                        <button type="button">পাঠান</button>
                    </div>
                </div>
            <?php else: ?>
                <!-- Floating chatbot -->
                <div class="biic-chatbot-widget" data-shortcode="true">
                    <!-- Widget content will be loaded by JavaScript -->
                </div>
            <?php endif; ?>
            
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Initialize shortcode chatbot
            if (typeof window.biicChatbot !== 'undefined') {
                window.biicChatbot.initShortcode($('.biic-chatbot-shortcode'));
            }
        });
        </script>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Contact form shortcode
     */
    public function contact_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'title' => 'ফ্রি কনসালটেশন নিন',
            'button_text' => 'Submit',
            'redirect_url' => '',
            'style' => 'default'
        ), $atts);
        
        ob_start();
        ?>
        <div class="biic-contact-form-wrapper">
            <form class="biic-contact-form" data-style="<?php echo esc_attr($atts['style']); ?>">
                <h3 class="biic-form-title"><?php echo esc_html($atts['title']); ?></h3>
                
                <div class="biic-form-row">
                    <div class="biic-form-group">
                        <label for="biic-name">নাম *</label>
                        <input type="text" id="biic-name" name="name" required>
                    </div>
                    <div class="biic-form-group">
                        <label for="biic-phone">ফোন *</label>
                        <input type="tel" id="biic-phone" name="phone" required>
                    </div>
                </div>
                
                <div class="biic-form-group">
                    <label for="biic-email">ইমেইল</label>
                    <input type="email" id="biic-email" name="email">
                </div>
                
                <div class="biic-form-group">
                    <label for="biic-course">কোর্সের আগ্রহ</label>
                    <select id="biic-course" name="course_interest">
                        <option value="">নির্বাচন করুন</option>
                        <option value="ielts_comprehensive">IELTS Comprehensive</option>
                        <option value="ielts_focus">IELTS Focus</option>
                        <option value="ielts_crash">IELTS Crash</option>
                        <option value="online_course">Online Course</option>
                        <option value="study_abroad">Study Abroad</option>
                    </select>
                </div>
                
                <div class="biic-form-group">
                    <label for="biic-message">বার্তা</label>
                    <textarea id="biic-message" name="message" rows="3"></textarea>
                </div>
                
                <button type="submit" class="biic-submit-btn">
                    <?php echo esc_html($atts['button_text']); ?>
                </button>
                
                <div class="biic-form-status"></div>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('.biic-contact-form').on('submit', function(e) {
                e.preventDefault();
                
                var $form = $(this);
                var $status = $form.find('.biic-form-status');
                var $btn = $form.find('.biic-submit-btn');
                
                // Collect form data
                var formData = {
                    action: 'biic_submit_lead',
                    nonce: biicConfig.nonce,
                    lead_data: JSON.stringify({
                        session_id: 'form_' + Date.now(),
                        name: $form.find('[name="name"]').val(),
                        phone: $form.find('[name="phone"]').val(),
                        email: $form.find('[name="email"]').val(),
                        course_interest: $form.find('[name="course_interest"]').val(),
                        message: $form.find('[name="message"]').val(),
                        source: 'contact_form'
                    })
                };
                
                $btn.prop('disabled', true).text('পাঠানো হচ্ছে...');
                $status.removeClass('success error').text('');
                
                $.post(biicConfig.ajaxUrl, formData, function(response) {
                    if (response.success) {
                        $status.addClass('success').html('✅ ধন্যবাদ! আমরা শীঘ্রই আপনার সাথে যোগাযোগ করব।');
                        $form[0].reset();
                        
                        <?php if ($atts['redirect_url']): ?>
                        setTimeout(function() {
                            window.location.href = '<?php echo esc_url($atts['redirect_url']); ?>';
                        }, 2000);
                        <?php endif; ?>
                    } else {
                        $status.addClass('error').html('❌ দুঃখিত, একটি সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।');
                    }
                }).fail(function() {
                    $status.addClass('error').html('❌ নেটওয়ার্ক সমস্যা। অনুগ্রহ করে আবার চেষ্টা করুন।');
                }).always(function() {
                    $btn.prop('disabled', false).text('<?php echo esc_js($atts['button_text']); ?>');
                });
            });
        });
        </script>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Custom page templates
     */
    public function custom_page_templates($template) {
        // Chat page template
        if (is_page('biic-chat') || get_query_var('biic_page') === 'chat') {
            $custom_template = BIIC_FRONTEND_PATH . 'templates/chat-page.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        return $template;
    }
    
    /**
     * Add meta tags for SEO
     */
    public function add_meta_tags() {
        if (!get_option('biic_seo_enabled', true)) return;
        
        echo '<meta name="description" content="Banglay IELTS - Bangladesh\'s leading IELTS training institute. AI-powered chatbot for instant course information and support.">' . "\n";
        echo '<meta name="keywords" content="IELTS, Bangladesh, training, chatbot, AI, course, admission, study abroad">' . "\n";
        echo '<meta property="og:title" content="Banglay IELTS - AI Chatbot Support">' . "\n";
        echo '<meta property="og:description" content="Get instant answers about IELTS courses, fees, and admission through our AI chatbot.">' . "\n";
        echo '<meta property="og:type" content="website">' . "\n";
        echo '<meta property="og:url" content="' . home_url() . '">' . "\n";
        echo '<meta name="twitter:card" content="summary">' . "\n";
        echo '<meta name="robots" content="index, follow">' . "\n";
    }
    
    /**
     * Add structured data
     */
    public function add_structured_data() {
        if (!get_option('biic_structured_data_enabled', true)) return;
        
        $structured_data = array(
            '@context' => 'https://schema.org',
            '@type' => 'EducationalOrganization',
            'name' => 'Banglay IELTS & Immigration Center',
            'url' => 'https://banglayelts.com',
            'logo' => BIIC_PLUGIN_ASSETS_URL . 'images/biic-logo.png',
            'description' => 'Bangladesh\'s leading IELTS training institute with AI-powered chatbot support',
            'address' => array(
                '@type' => 'PostalAddress',
                'streetAddress' => 'Rahman Heights, Plot-01, Uttara',
                'addressLocality' => 'Dhaka',
                'addressCountry' => 'Bangladesh'
            ),
            'contactPoint' => array(
                '@type' => 'ContactPoint',
                'telephone' => '+880-961-382-0821',
                'contactType' => 'customer service',
                'availableLanguage' => array('Bengali', 'English')
            ),
            'offers' => array(
                '@type' => 'Offer',
                'name' => 'IELTS Training Courses',
                'description' => 'Comprehensive IELTS preparation courses',
                'category' => 'Education'
            )
        );
        
        echo '<script type="application/ld+json">' . json_encode($structured_data, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
    
    /**
     * Rate limiting check
     */
    private function check_rate_limit($session_id) {
        $rate_limit = get_option('biic_rate_limit', 10); // 10 messages per minute
        $time_window = 60; // 1 minute
        
        $cache_key = "biic_rate_limit_{$session_id}";
        $requests = get_transient($cache_key);
        
        if ($requests === false) {
            $requests = 1;
        } else {
            $requests++;
        }
        
        if ($requests > $rate_limit) {
            return false;
        }
        
        set_transient($cache_key, $requests, $time_window);
        return true;
    }
    
    /**
     * Validate phone number
     */
    private function validate_phone_number($phone) {
        // Bangladesh phone number patterns
        $patterns = array(
            '/^(\+880|880|01)[0-9]{8,9}$/', // Standard BD format
            '/^01[3-9][0-9]{8}$/',          // Mobile numbers
            '/^\+8801[3-9][0-9]{8}$/'       // International format
        );
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $phone)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get current user session
     */
    private function get_current_session() {
        $user_tracking = BIIC()->user_tracking;
        return $user_tracking->get_current_session();
    }
    
    /**
     * Log frontend error
     */
    private function log_error($message, $data = array()) {
        error_log("BIIC Frontend Error: {$message} - " . json_encode($data));
    }
}