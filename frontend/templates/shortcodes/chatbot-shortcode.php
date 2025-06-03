<?php
/**
 * Chatbot Shortcode Template
 * Advanced inline chatbot with full functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get shortcode attributes
$atts = shortcode_atts(array(
    'position' => 'inline',
    'height' => '600px',
    'width' => '100%',
    'theme' => 'modern',
    'show_header' => 'true',
    'auto_greeting' => 'true',
    'welcome_message' => '',
    'course_filter' => '',
    'language' => 'mixed' // bangla, english, mixed
), $atts);

// Generate unique ID for this shortcode instance
$shortcode_id = 'biic-shortcode-' . uniqid();

// Get welcome message
$welcome_message = !empty($atts['welcome_message']) 
    ? $atts['welcome_message'] 
    : get_option('biic_welcome_message', 'আস্সালামু আলাইকুম! IELTS সম্পর্কে কিছু জানতে চান?');
?>

<div id="<?php echo esc_attr($shortcode_id); ?>" 
     class="biic-chatbot-shortcode biic-theme-<?php echo esc_attr($atts['theme']); ?>" 
     data-position="<?php echo esc_attr($atts['position']); ?>"
     data-theme="<?php echo esc_attr($atts['theme']); ?>"
     data-language="<?php echo esc_attr($atts['language']); ?>"
     style="height: <?php echo esc_attr($atts['height']); ?>; width: <?php echo esc_attr($atts['width']); ?>;">
    
    <?php if ($atts['position'] === 'inline'): ?>
        
        <!-- Inline Chatbot Interface -->
        <div class="biic-inline-chatbot">
            
            <?php if ($atts['show_header'] === 'true'): ?>
                <!-- Chat Header -->
                <div class="biic-shortcode-header">
                    <div class="biic-header-avatar">
                        <div class="biic-avatar-circle">
                            <span class="biic-avatar-text">বট</span>
                        </div>
                        <div class="biic-online-indicator"></div>
                    </div>
                    
                    <div class="biic-header-info">
                        <h3 class="biic-header-title">
                            <?php esc_html_e('Banglay IELTS সহায়ক', 'banglay-ielts-chatbot'); ?>
                        </h3>
                        <p class="biic-header-subtitle">
                            <span class="biic-status-dot"></span>
                            <?php esc_html_e('IELTS সম্পর্কে যেকোনো প্রশ্ন করুন', 'banglay-ielts-chatbot'); ?>
                        </p>
                    </div>
                    
                    <div class="biic-header-actions">
                        <button type="button" class="biic-header-btn biic-minimize-btn" title="<?php esc_attr_e('Minimize', 'banglay-ielts-chatbot'); ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 12L18 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                        
                        <button type="button" class="biic-header-btn biic-fullscreen-btn" title="<?php esc_attr_e('Fullscreen', 'banglay-ielts-chatbot'); ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8 3H5C3.89543 3 3 3.89543 3 5V8M21 8V5C21 3.89543 20.1046 3 19 3H16M16 21H19C20.1046 21 21 20.1046 21 19V16M3 16V19C3 20.1046 3.89543 21 5 21H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Messages Container -->
            <div class="biic-shortcode-messages" id="messages-<?php echo esc_attr($shortcode_id); ?>">
                
                <!-- Initial Welcome Message -->
                <?php if ($atts['auto_greeting'] === 'true'): ?>
                    <div class="biic-message biic-message-bot">
                        <div class="biic-message-avatar">
                            <span>🤖</span>
                        </div>
                        <div class="biic-message-content">
                            <div class="biic-message-bubble">
                                <?php echo wp_kses_post(nl2br($welcome_message)); ?>
                            </div>
                            <div class="biic-message-time">
                                <?php echo current_time('g:i A'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Initial Quick Replies -->
                    <div class="biic-quick-replies-container">
                        <div class="biic-quick-replies">
                            <button type="button" class="biic-quick-reply" data-text="কোর্স সম্পর্কে জানতে চাই">
                                📚 কোর্স সম্পর্কে
                            </button>
                            <button type="button" class="biic-quick-reply" data-text="ফি জানতে চাই">
                                💰 ফি জানতে চাই
                            </button>
                            <button type="button" class="biic-quick-reply" data-text="ঠিকানা চাই">
                                📍 ঠিকানা চাই
                            </button>
                            <button type="button" class="biic-quick-reply" data-text="এখনই কল করুন">
                                📞 কল করুন
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
            
            <!-- Typing Indicator -->
            <div class="biic-typing-indicator" style="display: none;">
                <div class="biic-typing-message">
                    <div class="biic-message-avatar">
                        <span>🤖</span>
                    </div>
                    <div class="biic-typing-content">
                        <div class="biic-typing-bubble">
                            <div class="biic-typing-dots">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Input Container -->
            <div class="biic-shortcode-input">
                <div class="biic-input-wrapper">
                    
                    <!-- Emoji Button -->
                    <button type="button" class="biic-emoji-btn" title="<?php esc_attr_e('Add Emoji', 'banglay-ielts-chatbot'); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                            <path d="M8 14S9.5 16 12 16S16 14 16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="9" cy="9" r="1" fill="currentColor"/>
                            <circle cx="15" cy="9" r="1" fill="currentColor"/>
                        </svg>
                    </button>
                    
                    <!-- Message Input -->
                    <div class="biic-input-container">
                        <textarea 
                            class="biic-message-input" 
                            placeholder="<?php esc_attr_e('আপনার প্রশ্ন লিখুন...', 'banglay-ielts-chatbot'); ?>"
                            rows="1"
                            maxlength="1000"
                        ></textarea>
                        
                        <!-- Character Counter -->
                        <div class="biic-char-counter">
                            <span class="biic-char-current">0</span>/<span class="biic-char-max">1000</span>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="biic-input-actions">
                        
                        <!-- Voice Recording Button -->
                        <button type="button" class="biic-voice-btn" title="<?php esc_attr_e('Voice Message', 'banglay-ielts-chatbot'); ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 1C10.34 1 9 2.34 9 4V12C9 13.66 10.34 15 12 15S15 13.66 15 12V4C15 2.34 13.66 1 12 1Z" stroke="currentColor" stroke-width="2"/>
                                <path d="M19 10V12C19 16.42 15.42 20 11 20H13C17.42 20 21 16.42 21 12V10" stroke="currentColor" stroke-width="2"/>
                                <line x1="12" y1="20" x2="12" y2="23" stroke="currentColor" stroke-width="2"/>
                                <line x1="8" y1="23" x2="16" y2="23" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </button>
                        
                        <!-- File Upload Button -->
                        <?php if (get_option('biic_allow_file_upload', false)): ?>
                        <button type="button" class="biic-file-btn" title="<?php esc_attr_e('File Upload', 'banglay-ielts-chatbot'); ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M21.44 11.05L12.25 20.24C11.84 20.65 11.35 20.98 10.8 21.21C10.25 21.44 9.66 21.56 9.06 21.56C8.46 21.56 7.87 21.44 7.32 21.21C6.77 20.98 6.28 20.65 5.87 20.24C5.46 19.83 5.13 19.34 4.9 18.79C4.67 18.24 4.55 17.65 4.55 17.05C4.55 16.45 4.67 15.86 4.9 15.31C5.13 14.76 5.46 14.27 5.87 13.86L15.06 4.67C15.84 3.89 16.9 3.45 18 3.45C19.1 3.45 20.16 3.89 20.94 4.67C21.72 5.45 22.16 6.51 22.16 7.61C22.16 8.71 21.72 9.77 20.94 10.55L11.75 19.74C11.36 20.13 10.84 20.35 10.3 20.35C9.76 20.35 9.24 20.13 8.85 19.74C8.46 19.35 8.24 18.83 8.24 18.29C8.24 17.75 8.46 17.23 8.85 16.84L17.34 8.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <input type="file" class="biic-file-input" accept=".pdf,.doc,.docx,.jpg,.png,.gif" style="display: none;">
                        </button>
                        <?php endif; ?>
                        
                        <!-- Send Button -->
                        <button type="button" class="biic-send-btn" title="<?php esc_attr_e('পাঠান', 'banglay-ielts-chatbot'); ?>">
                            <svg class="biic-send-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M22 2L11 13M22 2L15 22L11 13M22 2L2 9L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        
                    </div>
                </div>
                
                <!-- Suggestions Bar -->
                <div class="biic-suggestions-bar" style="display: none;">
                    <div class="biic-suggestions-title">💡 Suggestions:</div>
                    <div class="biic-suggestions-list">
                        <!-- Dynamic suggestions will be added here -->
                    </div>
                </div>
            </div>
            
        </div>
        
    <?php else: ?>
        
        <!-- Floating Chatbot Button -->
        <div class="biic-floating-chatbot">
            <button type="button" class="biic-float-trigger" data-shortcode-id="<?php echo esc_attr($shortcode_id); ?>">
                <svg class="biic-chat-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C6.48 2 2 6.48 2 12C2 13.54 2.36 14.99 3.01 16.26L2 22L7.74 20.99C9.01 21.64 10.46 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2Z" fill="currentColor"/>
                </svg>
                <span class="biic-float-text"><?php esc_html_e('Chat with us', 'banglay-ielts-chatbot'); ?></span>
            </button>
        </div>
        
    <?php endif; ?>
    
    <!-- Lead Form Modal (Hidden by default) -->
    <div class="biic-lead-modal" id="lead-modal-<?php echo esc_attr($shortcode_id); ?>" style="display: none;">
        <div class="biic-modal-overlay"></div>
        <div class="biic-modal-content">
            <div class="biic-modal-header">
                <h3>📚 <?php esc_html_e('ফ্রি কনসালটেশন বুক করুন', 'banglay-ielts-chatbot'); ?></h3>
                <button type="button" class="biic-modal-close">×</button>
            </div>
            
            <form class="biic-lead-form" id="lead-form-<?php echo esc_attr($shortcode_id); ?>">
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
                        <option value="study_abroad"><?php esc_html_e('Study Abroad Consultation', 'banglay-ielts-chatbot'); ?></option>
                    </select>
                </div>
                
                <button type="submit" class="biic-form-submit">
                    📞 <?php esc_html_e('ফ্রি কনসালটেশন বুক করুন', 'banglay-ielts-chatbot'); ?>
                </button>
                
                <div class="biic-form-status"></div>
            </form>
        </div>
    </div>
    
</div>

<!-- Emoji Picker (Hidden by default) -->
<div class="biic-emoji-picker" id="emoji-picker-<?php echo esc_attr($shortcode_id); ?>" style="display: none;">
    <div class="biic-emoji-categories">
        <button type="button" class="biic-emoji-category active" data-category="smileys">😊</button>
        <button type="button" class="biic-emoji-category" data-category="objects">📚</button>
        <button type="button" class="biic-emoji-category" data-category="symbols">💰</button>
        <button type="button" class="biic-emoji-category" data-category="flags">🇧🇩</button>
    </div>
    
    <div class="biic-emoji-grid">
        <div class="biic-emoji-section" data-category="smileys">
            <span class="biic-emoji" data-emoji="😊">😊</span>
            <span class="biic-emoji" data-emoji="😍">😍</span>
            <span class="biic-emoji" data-emoji="🤔">🤔</span>
            <span class="biic-emoji" data-emoji="😅">😅</span>
            <span class="biic-emoji" data-emoji="👍">👍</span>
            <span class="biic-emoji" data-emoji="👏">👏</span>
            <span class="biic-emoji" data-emoji="🙏">🙏</span>
            <span class="biic-emoji" data-emoji="❤️">❤️</span>
        </div>
        
        <div class="biic-emoji-section" data-category="objects" style="display: none;">
            <span class="biic-emoji" data-emoji="📚">📚</span>
            <span class="biic-emoji" data-emoji="📝">📝</span>
            <span class="biic-emoji" data-emoji="📞">📞</span>
            <span class="biic-emoji" data-emoji="📧">📧</span>
            <span class="biic-emoji" data-emoji="🎓">🎓</span>
            <span class="biic-emoji" data-emoji="🏫">🏫</span>
            <span class="biic-emoji" data-emoji="💻">💻</span>
            <span class="biic-emoji" data-emoji="📱">📱</span>
        </div>
        
        <div class="biic-emoji-section" data-category="symbols" style="display: none;">
            <span class="biic-emoji" data-emoji="💰">💰</span>
            <span class="biic-emoji" data-emoji="💵">💵</span>
            <span class="biic-emoji" data-emoji="⭐">⭐</span>
            <span class="biic-emoji" data-emoji="✅">✅</span>
            <span class="biic-emoji" data-emoji="❌">❌</span>
            <span class="biic-emoji" data-emoji="⚡">⚡</span>
            <span class="biic-emoji" data-emoji="🔥">🔥</span>
            <span class="biic-emoji" data-emoji="🎯">🎯</span>
        </div>
        
        <div class="biic-emoji-section" data-category="flags" style="display: none;">
            <span class="biic-emoji" data-emoji="🇧🇩">🇧🇩</span>
            <span class="biic-emoji" data-emoji="🇺🇸">🇺🇸</span>
            <span class="biic-emoji" data-emoji="🇬🇧">🇬🇧</span>
            <span class="biic-emoji" data-emoji="🇨🇦">🇨🇦</span>
            <span class="biic-emoji" data-emoji="🇦🇺">🇦🇺</span>
            <span class="biic-emoji" data-emoji="🇩🇰">🇩🇰</span>
            <span class="biic-emoji" data-emoji="🇫🇮">🇫🇮</span>
            <span class="biic-emoji" data-emoji="🌍">🌍</span>
        </div>
    </div>
</div>

<style>
/* Shortcode Specific Styles */
.biic-chatbot-shortcode {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    position: relative;
    background: #ffffff;
    border: 1px solid #e5e7eb;
}

.biic-inline-chatbot {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.biic-shortcode-header {
    display: flex;
    align-items: center;
    padding: 16px 20px;
    background: linear-gradient(135deg, #e53e3e 0%, #dc2626 100%);
    color: white;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.biic-header-avatar {
    position: relative;
    margin-right: 12px;
}

.biic-avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
    backdrop-filter: blur(10px);
}

.biic-online-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 12px;
    height: 12px;
    background: #10b981;
    border-radius: 50%;
    border: 2px solid white;
    animation: pulse 2s infinite;
}

.biic-header-info {
    flex: 1;
}

.biic-header-title {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 600;
    line-height: 1.2;
}

.biic-header-subtitle {
    margin: 0;
    font-size: 12px;
    opacity: 0.9;
    display: flex;
    align-items: center;
    gap: 6px;
}

.biic-status-dot {
    width: 6px;
    height: 6px;
    background: #10b981;
    border-radius: 50%;
    display: inline-block;
}

.biic-header-actions {
    display: flex;
    gap: 8px;
}

.biic-header-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    backdrop-filter: blur(10px);
}

.biic-header-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.05);
}

.biic-shortcode-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: #f8f9fa;
    scroll-behavior: smooth;
}

.biic-message {
    display: flex;
    margin-bottom: 16px;
    animation: slideInUp 0.3s ease;
}

.biic-message-bot {
    align-self: flex-start;
}

.biic-message-user {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.biic-message-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
    margin: 0 8px;
}

.biic-message-bot .biic-message-avatar {
    background: linear-gradient(135deg, #e53e3e 0%, #dc2626 100%);
    color: white;
}

.biic-message-user .biic-message-avatar {
    background: #6b7280;
    color: white;
}

.biic-message-content {
    max-width: 70%;
}

.biic-message-bubble {
    padding: 12px 16px;
    border-radius: 18px;
    font-size: 14px;
    line-height: 1.4;
    word-wrap: break-word;
    position: relative;
}

.biic-message-bot .biic-message-bubble {
    background: white;
    color: #374151;
    border-bottom-left-radius: 6px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.biic-message-user .biic-message-bubble {
    background: linear-gradient(135deg, #e53e3e 0%, #dc2626 100%);
    color: white;
    border-bottom-right-radius: 6px;
}

.biic-message-time {
    font-size: 11px;
    color: #9ca3af;
    margin-top: 4px;
    text-align: right;
}

.biic-message-user .biic-message-time {
    text-align: left;
}

.biic-quick-replies-container {
    margin: 16px 0;
}

.biic-quick-replies {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-left: 40px;
}

.biic-quick-reply {
    background: white;
    border: 1px solid #e5e7eb;
    color: #374151;
    padding: 8px 12px;
    border-radius: 16px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.biic-quick-reply:hover {
    background: #e53e3e;
    color: white;
    border-color: #e53e3e;
    transform: translateY(-1px);
}

.biic-typing-indicator {
    margin-bottom: 16px;
}

.biic-typing-bubble {
    background: white;
    padding: 16px;
    border-radius: 18px;
    border-bottom-left-radius: 6px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    width: fit-content;
}

.biic-typing-dots {
    display: flex;
    gap: 4px;
}

.biic-typing-dots span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #9ca3af;
    animation: typing 1.4s infinite ease-in-out;
}

.biic-typing-dots span:nth-child(2) {
    animation-delay: 0.2s;
}

.biic-typing-dots span:nth-child(3) {
    animation-delay: 0.4s;
}

.biic-shortcode-input {
    border-top: 1px solid #e5e7eb;
    background: white;
    padding: 16px 20px;
}

.biic-input-wrapper {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    background: #f8f9fa;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    padding: 8px 16px;
    transition: all 0.2s ease;
}

.biic-input-wrapper:focus-within {
    border-color: #e53e3e;
    box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.1);
}

.biic-emoji-btn,
.biic-voice-btn,
.biic-file-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: none;
    color: #6b7280;
    cursor: pointer;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.biic-emoji-btn:hover,
.biic-voice-btn:hover,
.biic-file-btn:hover {
    background: #e5e7eb;
    color: #374151;
}

.biic-input-container {
    flex: 1;
    position: relative;
}

.biic-message-input {
    width: 100%;
    border: none;
    background: none;
    resize: none;
    outline: none;
    font-size: 14px;
    line-height: 1.4;
    padding: 8px 0;
    max-height: 100px;
    min-height: 20px;
    font-family: inherit;
}

.biic-char-counter {
    position: absolute;
    bottom: -16px;
    right: 0;
    font-size: 10px;
    color: #9ca3af;
}

.biic-input-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.biic-send-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: linear-gradient(135deg, #e53e3e 0%, #dc2626 100%);
    color: white;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.biic-send-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(229, 62, 62, 0.3);
}

.biic-send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.biic-send-icon {
    width: 16px;
    height: 16px;
}

.biic-suggestions-bar {
    margin-top: 12px;
    padding: 12px;
    background: #f1f5f9;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.biic-suggestions-title {
    font-size: 12px;
    font-weight: 500;
    color: #64748b;
    margin-bottom: 8px;
}

.biic-suggestions-list {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.biic-suggestion {
    background: white;
    border: 1px solid #e2e8f0;
    color: #475569;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.biic-suggestion:hover {
    background: #e53e3e;
    color: white;
    border-color: #e53e3e;
}

/* Lead Form Modal */
.biic-lead-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease;
}

.biic-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.biic-modal-content {
    background: white;
    border-radius: 12px;
    padding: 0;
    max-width: 400px;
    width: 90%;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 1;
}

.biic-modal-header {
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(135deg, #e53e3e 0%, #dc2626 100%);
    color: white;
}

.biic-modal-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.biic-modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.biic-modal-close:hover {
    background: rgba(255, 255, 255, 0.1);
}

.biic-lead-form {
    padding: 24px;
}

.biic-form-group {
    margin-bottom: 16px;
}

.biic-form-label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #374151;
    font-size: 14px;
}

.biic-form-input {
    width: 100%;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.biic-form-input:focus {
    outline: none;
    border-color: #e53e3e;
    box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.1);
}

.biic-form-submit {
    width: 100%;
    background: linear-gradient(135deg, #e53e3e 0%, #dc2626 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-top: 8px;
}

.biic-form-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(229, 62, 62, 0.3);
}

.biic-form-status {
    margin-top: 12px;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 13px;
    text-align: center;
    display: none;
}

.biic-form-status.success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.biic-form-status.error {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

/* Emoji Picker */
.biic-emoji-picker {
    position: absolute;
    bottom: 100%;
    left: 0;
    width: 280px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    overflow: hidden;
}

.biic-emoji-categories {
    display: flex;
    border-bottom: 1px solid #e5e7eb;
    background: #f8f9fa;
}

.biic-emoji-category {
    flex: 1;
    padding: 12px;
    border: none;
    background: none;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.biic-emoji-category:hover,
.biic-emoji-category.active {
    background: #e53e3e;
    color: white;
}

.biic-emoji-grid {
    padding: 12px;
    max-height: 200px;
    overflow-y: auto;
}

.biic-emoji-section {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 4px;
}

.biic-emoji {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border-radius: 4px;
    font-size: 16px;
    transition: all 0.2s ease;
}

.biic-emoji:hover {
    background: #f3f4f6;
    transform: scale(1.2);
}

/* Theme Variations */
.biic-theme-dark {
    background: #1f2937;
    color: #f9fafb;
}

.biic-theme-dark .biic-shortcode-messages {
    background: #111827;
}

.biic-theme-dark .biic-message-bot .biic-message-bubble {
    background: #374151;
    color: #f9fafb;
}

.biic-theme-dark .biic-shortcode-input {
    background: #1f2937;
    border-color: #374151;
}

.biic-theme-dark .biic-input-wrapper {
    background: #374151;
    border-color: #4b5563;
}

.biic-theme-minimal {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 2px solid #e5e7eb;
}

.biic-theme-minimal .biic-shortcode-header {
    background: #f8f9fa;
    color: #374151;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

@keyframes typing {
    0%, 60%, 100% {
        transform: translateY(0);
    }
    30% {
        transform: translateY(-10px);
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .biic-shortcode-header {
        padding: 12px 16px;
    }
    
    .biic-header-title {
        font-size: 14px;
    }
    
    .biic-header-subtitle {
        font-size: 11px;
    }
    
    .biic-shortcode-messages {
        padding: 16px;
    }
    
    .biic-message-content {
        max-width: 85%;
    }
    
    .biic-quick-replies {
        margin-left: 32px;
    }
    
    .biic-quick-reply {
        font-size: 12px;
        padding: 6px 10px;
    }
    
    .biic-shortcode-input {
        padding: 12px 16px;
    }
    
    .biic-modal-content {
        width: 95%;
        margin: 20px;
    }
    
    .biic-emoji-picker {
        width: 260px;
    }
}

/* Scrollbar Styling */
.biic-shortcode-messages::-webkit-scrollbar {
    width: 6px;
}

.biic-shortcode-messages::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.biic-shortcode-messages::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.biic-shortcode-messages::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Print Styles */
@media print {
    .biic-chatbot-shortcode {
        display: none !important;
    }
}
</style>

<script>
// Initialize shortcode chatbot
document.addEventListener('DOMContentLoaded', function() {
    const shortcodeId = '<?php echo esc_js($shortcode_id); ?>';
    const container = document.getElementById(shortcodeId);
    
    if (container) {
        new BiicShortcodeChatbot(container, {
            shortcodeId: shortcodeId,
            theme: '<?php echo esc_js($atts['theme']); ?>',
            language: '<?php echo esc_js($atts['language']); ?>',
            autoGreeting: <?php echo json_encode($atts['auto_greeting'] === 'true'); ?>,
            welcomeMessage: <?php echo json_encode($welcome_message); ?>
        });
    }
});

// Shortcode Chatbot Class
class BiicShortcodeChatbot {
    constructor(container, options) {
        this.container = container;
        this.options = options;
        this.sessionId = 'shortcode_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        this.isTyping = false;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.setupAutoResize();
        this.loadPreviousMessages();
    }
    
    bindEvents() {
        const messageInput = this.container.querySelector('.biic-message-input');
        const sendBtn = this.container.querySelector('.biic-send-btn');
        const emojiBtn = this.container.querySelector('.biic-emoji-btn');
        const voiceBtn = this.container.querySelector('.biic-voice-btn');
        const fileBtn = this.container.querySelector('.biic-file-btn');
        
        // Send message events
        if (sendBtn) {
            sendBtn.addEventListener('click', () => this.sendMessage());
        }
        
        if (messageInput) {
            messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
            
            messageInput.addEventListener('input', () => {
                this.updateCharCounter();
                this.autoResize(messageInput);
                this.updateSuggestions();
            });
        }
        
        // Quick reply events
        this.container.addEventListener('click', (e) => {
            if (e.target.classList.contains('biic-quick-reply')) {
                const text = e.target.getAttribute('data-text');
                if (text) {
                    messageInput.value = text;
                    this.sendMessage();
                }
            }
        });
        
        // Emoji picker events
        if (emojiBtn) {
            emojiBtn.addEventListener('click', () => this.toggleEmojiPicker());
        }
        
        this.setupEmojiPicker();
        
        // Voice recording (placeholder)
        if (voiceBtn) {
            voiceBtn.addEventListener('click', () => this.startVoiceRecording());
        }
        
        // File upload
        if (fileBtn) {
            const fileInput = fileBtn.querySelector('.biic-file-input');
            fileBtn.addEventListener('click', () => fileInput?.click());
            if (fileInput) {
                fileInput.addEventListener('change', (e) => this.handleFileUpload(e));
            }
        }
        
        // Lead form events
        this.setupLeadForm();
        
        // Header button events
        this.setupHeaderButtons();
    }
    
    sendMessage() {
        const messageInput = this.container.querySelector('.biic-message-input');
        const message = messageInput.value.trim();
        
        if (!message || this.isTyping) return;
        
        // Add user message to chat
        this.addMessage('user', message);
        
        // Clear input
        messageInput.value = '';
        this.updateCharCounter();
        this.autoResize(messageInput);
        
        // Show typing indicator
        this.showTypingIndicator();
        
        // Send to server
        this.sendToServer(message);
    }
    
    addMessage(type, content, options = {}) {
        const messagesContainer = this.container.querySelector('.biic-shortcode-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `biic-message biic-message-${type}`;
        
        const avatar = type === 'bot' ? '🤖' : '👤';
        const time = new Date().toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit',
            hour12: true 
        });
        
        messageDiv.innerHTML = `
            <div class="biic-message-avatar">
                <span>${avatar}</span>
            </div>
            <div class="biic-message-content">
                <div class="biic-message-bubble">
                    ${this.formatMessage(content)}
                </div>
                <div class="biic-message-time">${time}</div>
            </div>
        `;
        
        messagesContainer.appendChild(messageDiv);
        
        // Add quick replies if provided
        if (options.quickReplies && options.quickReplies.length > 0) {
            const quickRepliesDiv = document.createElement('div');
            quickRepliesDiv.className = 'biic-quick-replies-container';
            quickRepliesDiv.innerHTML = `
                <div class="biic-quick-replies">
                    ${options.quickReplies.map(reply => 
                        `<button type="button" class="biic-quick-reply" data-text="${reply}">${reply}</button>`
                    ).join('')}
                </div>
            `;
            messagesContainer.appendChild(quickRepliesDiv);
        }
        
        // Show lead form if requested
        if (options.showLeadForm) {
            setTimeout(() => this.showLeadForm(), 1000);
        }
        
        this.scrollToBottom();
    }
    
    formatMessage(message) {
        // Convert markdown-style formatting
        return message
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/\n/g, '<br>');
    }
    
    showTypingIndicator() {
        this.isTyping = true;
        const typingIndicator = this.container.querySelector('.biic-typing-indicator');
        if (typingIndicator) {
            typingIndicator.style.display = 'block';
            this.scrollToBottom();
        }
    }
    
    hideTypingIndicator() {
        this.isTyping = false;
        const typingIndicator = this.container.querySelector('.biic-typing-indicator');
        if (typingIndicator) {
            typingIndicator.style.display = 'none';
        }
    }
    
    async sendToServer(message) {
        try {
            const response = await fetch(biicConfig.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'biic_chat_message',
                    message: message,
                    session_id: this.sessionId,
                    nonce: biicConfig.nonce
                })
            });
            
            const data = await response.json();
            
            setTimeout(() => {
                this.hideTypingIndicator();
                
                if (data.success) {
                    this.addMessage('bot', data.data.message, {
                        quickReplies: data.data.quick_replies,
                        showLeadForm: data.data.show_lead_form
                    });
                } else {
                    this.addMessage('bot', 'দুঃখিত, একটি সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।');
                }
            }, 1000 + Math.random() * 1000); // Simulate typing delay
            
        } catch (error) {
            console.error('Chat error:', error);
            this.hideTypingIndicator();
            this.addMessage('bot', 'নেটওয়ার্ক সমস্যা। অনুগ্রহ করে আবার চেষ্টা করুন।');
        }
    }
    
    scrollToBottom() {
        const messagesContainer = this.container.querySelector('.biic-shortcode-messages');
        if (messagesContainer) {
            setTimeout(() => {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }, 100);
        }
    }
    
    updateCharCounter() {
        const messageInput = this.container.querySelector('.biic-message-input');
        const charCurrent = this.container.querySelector('.biic-char-current');
        
        if (messageInput && charCurrent) {
            charCurrent.textContent = messageInput.value.length;
        }
    }
    
    autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 100) + 'px';
    }
    
    setupAutoResize() {
        const messageInput = this.container.querySelector('.biic-message-input');
        if (messageInput) {
            this.autoResize(messageInput);
        }
    }
    
    toggleEmojiPicker() {
        const emojiPicker = document.getElementById(`emoji-picker-${this.options.shortcodeId}`);
        if (emojiPicker) {
            emojiPicker.style.display = emojiPicker.style.display === 'none' ? 'block' : 'none';
        }
    }
    
    setupEmojiPicker() {
        const emojiPicker = document.getElementById(`emoji-picker-${this.options.shortcodeId}`);
        if (!emojiPicker) return;
        
        // Category switching
        emojiPicker.addEventListener('click', (e) => {
            if (e.target.classList.contains('biic-emoji-category')) {
                const category = e.target.getAttribute('data-category');
                
                // Update active category
                emojiPicker.querySelectorAll('.biic-emoji-category').forEach(btn => {
                    btn.classList.remove('active');
                });
                e.target.classList.add('active');
                
                // Show corresponding emoji section
                emojiPicker.querySelectorAll('.biic-emoji-section').forEach(section => {
                    section.style.display = section.getAttribute('data-category') === category ? 'grid' : 'none';
                });
            }
            
            if (e.target.classList.contains('biic-emoji')) {
                const emoji = e.target.getAttribute('data-emoji');
                const messageInput = this.container.querySelector('.biic-message-input');
                if (messageInput) {
                    messageInput.value += emoji;
                    messageInput.focus();
                    this.updateCharCounter();
                    emojiPicker.style.display = 'none';
                }
            }
        });
        
        // Close emoji picker when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.biic-emoji-btn') && !e.target.closest('.biic-emoji-picker')) {
                emojiPicker.style.display = 'none';
            }
        });
    }
    
    startVoiceRecording() {
        // Placeholder for voice recording functionality
        alert('Voice recording feature coming soon! 🎤');
    }
    
    handleFileUpload(event) {
        const files = event.target.files;
        if (files.length > 0) {
            const file = files[0];
            // Placeholder for file upload functionality
            alert(`File selected: ${file.name}\nFile upload feature coming soon! 📎`);
        }
    }
    
    showLeadForm() {
        const leadModal = document.getElementById(`lead-modal-${this.options.shortcodeId}`);
        if (leadModal) {
            leadModal.style.display = 'flex';
        }
    }
    
    hideLeadForm() {
        const leadModal = document.getElementById(`lead-modal-${this.options.shortcodeId}`);
        if (leadModal) {
            leadModal.style.display = 'none';
        }
    }
    
    setupLeadForm() {
        const leadForm = document.getElementById(`lead-form-${this.options.shortcodeId}`);
        const leadModal = document.getElementById(`lead-modal-${this.options.shortcodeId}`);
        
        if (!leadForm || !leadModal) return;
        
        // Form submission
        leadForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(leadForm);
            const leadData = {
                session_id: this.sessionId,
                name: formData.get('name'),
                phone: formData.get('phone'),
                email: formData.get('email'),
                course_interest: formData.get('course_interest')
            };
            
            const submitBtn = leadForm.querySelector('.biic-form-submit');
            const statusDiv = leadForm.querySelector('.biic-form-status');
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'পাঠানো হচ্ছে...';
            
            try {
                const response = await fetch(biicConfig.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'biic_submit_lead',
                        lead_data: JSON.stringify(leadData),
                        nonce: biicConfig.nonce
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    statusDiv.className = 'biic-form-status success';
                    statusDiv.textContent = '✅ ধন্যবাদ! আমরা শীঘ্রই আপনার সাথে যোগাযোগ করব।';
                    statusDiv.style.display = 'block';
                    
                    leadForm.reset();
                    
                    setTimeout(() => {
                        this.hideLeadForm();
                    }, 2000);
                } else {
                    throw new Error(result.data || 'Submission failed');
                }
                
            } catch (error) {
                statusDiv.className = 'biic-form-status error';
                statusDiv.textContent = '❌ দুঃখিত, একটি সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।';
                statusDiv.style.display = 'block';
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = '📞 ফ্রি কনসালটেশন বুক করুন';
            }
        });
        
        // Modal close events
        leadModal.addEventListener('click', (e) => {
            if (e.target.classList.contains('biic-modal-overlay') || 
                e.target.classList.contains('biic-modal-close')) {
                this.hideLeadForm();
            }
        });
    }
    
    setupHeaderButtons() {
        const minimizeBtn = this.container.querySelector('.biic-minimize-btn');
        const fullscreenBtn = this.container.querySelector('.biic-fullscreen-btn');
        
        if (minimizeBtn) {
            minimizeBtn.addEventListener('click', () => {
                const messagesContainer = this.container.querySelector('.biic-shortcode-messages');
                const inputContainer = this.container.querySelector('.biic-shortcode-input');
                
                if (messagesContainer && inputContainer) {
                    const isMinimized = messagesContainer.style.display === 'none';
                    messagesContainer.style.display = isMinimized ? 'block' : 'none';
                    inputContainer.style.display = isMinimized ? 'block' : 'none';
                }
            });
        }
        
        if (fullscreenBtn) {
            fullscreenBtn.addEventListener('click', () => {
                this.container.classList.toggle('biic-fullscreen');
            });
        }
    }
    
    updateSuggestions() {
        const messageInput = this.container.querySelector('.biic-message-input');
        const suggestionsBar = this.container.querySelector('.biic-suggestions-bar');
        const suggestionsList = this.container.querySelector('.biic-suggestions-list');
        
        if (!messageInput || !suggestionsBar || !suggestionsList) return;
        
        const inputValue = messageInput.value.toLowerCase();
        
        if (inputValue.length < 2) {
            suggestionsBar.style.display = 'none';
            return;
        }
        
        const suggestions = this.getSuggestions(inputValue);
        
        if (suggestions.length > 0) {
            suggestionsList.innerHTML = suggestions.map(suggestion => 
                `<span class="biic-suggestion" data-text="${suggestion}">${suggestion}</span>`
            ).join('');
            suggestionsBar.style.display = 'block';
            
            // Add click events to suggestions
            suggestionsList.addEventListener('click', (e) => {
                if (e.target.classList.contains('biic-suggestion')) {
                    messageInput.value = e.target.getAttribute('data-text');
                    suggestionsBar.style.display = 'none';
                    messageInput.focus();
                }
            });
        } else {
            suggestionsBar.style.display = 'none';
        }
    }
    
    getSuggestions(input) {
        const suggestions = [
            'কোর্স ফি কত?',
            'IELTS Comprehensive কোর্স সম্পর্কে জানতে চাই',
            'ভর্তির জন্য কি কি লাগবে?',
            'অফিসের ঠিকানা দিন',
            'অনলাইন কোর্স আছে কি?',
            'ক্লাসের সময়সূচী জানতে চাই',
            'বিদেশে পড়াশোনার জন্য সাহায্য পাব?',
            'মক টেস্ট কিভাবে দেব?',
            'স্পিকিং প্র্যাকটিস কিভাবে করব?',
            'IELTS রেজিস্ট্রেশন কিভাবে করব?'
        ];
        
        return suggestions.filter(suggestion => 
            suggestion.toLowerCase().includes(input)
        ).slice(0, 3);
    }
    
    loadPreviousMessages() {
        // Load previous messages if session exists
        // This could be implemented to restore chat history
    }
}