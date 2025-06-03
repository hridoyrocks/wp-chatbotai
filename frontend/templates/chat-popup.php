<?php
/**
 * Chat Popup Component
 * frontend/chat-popup.php
 * Separate popup window for advanced chat features
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get chat settings
$chat_settings = array(
    'enabled' => get_option('biic_chatbot_enabled', true),
    'position' => get_option('biic_chat_position', 'bottom-right'),
    'theme' => get_option('biic_chat_theme', 'modern'),
    'welcome_message' => get_option('biic_welcome_message', 'আস্সালামু আলাইকুম! IELTS এর ব্যাপারে কিছু জানতে চান?'),
    'business_hours' => get_option('biic_business_hours', array()),
    'primary_color' => get_option('biic_primary_color', '#E53E3E')
);

// Check if should show popup
if (!$chat_settings['enabled']) {
    return;
}

// Get session data if exists
$session_id = isset($_COOKIE['biic_session_id']) ? sanitize_text_field($_COOKIE['biic_session_id']) : '';
$is_returning_visitor = !empty($session_id);

// Check business hours
$is_business_hours = $this->is_within_business_hours($chat_settings['business_hours']);
?>

<!-- Chat Popup Modal -->
<div id="biic-chat-popup" class="biic-popup-overlay" style="display: none;">
    <div class="biic-popup-container biic-theme-<?php echo esc_attr($chat_settings['theme']); ?>">
        
        <!-- Popup Header -->
        <div class="biic-popup-header">
            <div class="biic-popup-header-content">
                <div class="biic-popup-avatar">
                    <img src="<?php echo BIIC_PLUGIN_ASSETS_URL; ?>images/bot-avatar.png" alt="Chatbot" onerror="this.innerHTML='🤖'">
                </div>
                <div class="biic-popup-header-text">
                    <h3><?php esc_html_e('Banglay IELTS Assistant', 'banglay-ielts-chatbot'); ?></h3>
                    <p class="biic-popup-status">
                        <span class="biic-status-indicator <?php echo $is_business_hours ? 'online' : 'offline'; ?>"></span>
                        <?php 
                        if ($is_business_hours) {
                            esc_html_e('অনলাইনে আছি - সাথে সাথে উত্তর পাবেন', 'banglay-ielts-chatbot');
                        } else {
                            esc_html_e('অফিস সময়ের বাইরে - শীঘ্রই উত্তর দেব', 'banglay-ielts-chatbot');
                        }
                        ?>
                    </p>
                </div>
            </div>
            <button class="biic-popup-close" aria-label="<?php esc_attr_e('Close Chat', 'banglay-ielts-chatbot'); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>
            </button>
        </div>

        <!-- Chat Content Area -->
        <div class="biic-popup-content">
            
            <!-- Welcome Section -->
            <div class="biic-welcome-section">
                <div class="biic-welcome-message">
                    <div class="biic-welcome-icon">👋</div>
                    <h4><?php esc_html_e('স্বাগতম!', 'banglay-ielts-chatbot'); ?></h4>
                    <p><?php echo esc_html($chat_settings['welcome_message']); ?></p>
                </div>

                <!-- Quick Start Options -->
                <div class="biic-quick-start">
                    <h5><?php esc_html_e('আমি আপনাকে কীভাবে সাহায্য করতে পারি?', 'banglay-ielts-chatbot'); ?></h5>
                    <div class="biic-quick-options">
                        <button class="biic-quick-option" data-message="IELTS কোর্স সম্পর্কে জানতে চাই">
                            <span class="biic-option-icon">📚</span>
                            <span class="biic-option-text"><?php esc_html_e('IELTS কোর্স', 'banglay-ielts-chatbot'); ?></span>
                        </button>
                        
                        <button class="biic-quick-option" data-message="কোর্সের ফি কত?">
                            <span class="biic-option-icon">💰</span>
                            <span class="biic-option-text"><?php esc_html_e('কোর্সের ফি', 'banglay-ielts-chatbot'); ?></span>
                        </button>
                        
                        <button class="biic-quick-option" data-message="ক্লাসের সময়সূচী জানতে চাই">
                            <span class="biic-option-icon">🕒</span>
                            <span class="biic-option-text"><?php esc_html_e('ক্লাসের সময়', 'banglay-ielts-chatbot'); ?></span>
                        </button>
                        
                        <button class="biic-quick-option" data-message="আপনাদের লোকেশন কোথায়?">
                            <span class="biic-option-icon">📍</span>
                            <span class="biic-option-text"><?php esc_html_e('লোকেশন', 'banglay-ielts-chatbot'); ?></span>
                        </button>
                        
                        <button class="biic-quick-option" data-message="ফ্রি কাউন্সেলিং বুক করতে চাই">
                            <span class="biic-option-icon">📞</span>
                            <span class="biic-option-text"><?php esc_html_e('ফ্রি কাউন্সেলিং', 'banglay-ielts-chatbot'); ?></span>
                        </button>
                        
                        <button class="biic-quick-option" data-message="অনলাইন কোর্স আছে কি?">
                            <span class="biic-option-icon">💻</span>
                            <span class="biic-option-text"><?php esc_html_e('অনলাইন কোর্স', 'banglay-ielts-chatbot'); ?></span>
                        </button>
                    </div>
                </div>

                <!-- Special Offers Section -->
                <?php if (get_option('biic_show_offers', true)): ?>
                <div class="biic-special-offers">
                    <div class="biic-offer-banner">
                        <div class="biic-offer-icon">🎉</div>
                        <div class="biic-offer-content">
                            <h6><?php esc_html_e('বিশেষ অফার!', 'banglay-ielts-chatbot'); ?></h6>
                            <p><?php esc_html_e('এই মাসে ভর্তি হলে ২০% ছাড়! আজই যোগাযোগ করুন।', 'banglay-ielts-chatbot'); ?></p>
                        </div>
                        <button class="biic-offer-btn" data-message="বিশেষ অফার সম্পর্কে জানতে চাই">
                            <?php esc_html_e('জানুন', 'banglay-ielts-chatbot'); ?>
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Contact Info -->
                <div class="biic-contact-info">
                    <div class="biic-contact-item">
                        <span class="biic-contact-icon">📞</span>
                        <div class="biic-contact-details">
                            <span class="biic-contact-label"><?php esc_html_e('হটলাইন', 'banglay-ielts-chatbot'); ?></span>
                            <a href="tel:+8809613820821" class="biic-contact-value">+880 961 382 0821</a>
                        </div>
                    </div>
                    
                    <div class="biic-contact-item">
                        <span class="biic-contact-icon">📧</span>
                        <div class="biic-contact-details">
                            <span class="biic-contact-label"><?php esc_html_e('ইমেইল', 'banglay-ielts-chatbot'); ?></span>
                            <a href="mailto:info@banglayelts.com" class="biic-contact-value">info@banglayelts.com</a>
                        </div>
                    </div>
                    
                    <div class="biic-contact-item">
                        <span class="biic-contact-icon">🌐</span>
                        <div class="biic-contact-details">
                            <span class="biic-contact-label"><?php esc_html_e('ওয়েবসাইট', 'banglay-ielts-chatbot'); ?></span>
                            <a href="https://banglayelts.com" target="_blank" class="biic-contact-value">banglayelts.com</a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Chat Messages Area (Initially Hidden) -->
            <div class="biic-chat-area" style="display: none;">
                <div class="biic-chat-messages" id="biic-popup-messages">
                    <!-- Messages will be dynamically added here -->
                </div>
                
                <!-- Chat Input -->
                <div class="biic-chat-input-area">
                    <div class="biic-input-container">
                        <textarea 
                            class="biic-message-input" 
                            placeholder="<?php esc_attr_e('আপনার প্রশ্ন লিখুন...', 'banglay-ielts-chatbot'); ?>"
                            rows="1"
                            maxlength="<?php echo esc_attr(get_option('biic_max_message_length', 1000)); ?>"
                        ></textarea>
                        <button class="biic-send-button" type="button">
                            <svg class="biic-send-icon" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Popup Footer -->
        <div class="biic-popup-footer">
            <div class="biic-social-links">
                <a href="https://facebook.com/banglayelts" target="_blank" class="biic-social-link" aria-label="Facebook">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </a>
                <a href="https://youtube.com/@banglayelts" target="_blank" class="biic-social-link" aria-label="YouTube">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                    </svg>
                </a>
                <a href="https://instagram.com/banglayelts" target="_blank" class="biic-social-link" aria-label="Instagram">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                </a>
            </div>
            
            <div class="biic-popup-copyright">
                <span><?php esc_html_e('Made with', 'banglay-ielts-chatbot'); ?> ❤️ <?php esc_html_e('by', 'banglay-ielts-chatbot'); ?> 
                <a href="https://banglayelts.com" target="_blank">Love Rocks</a></span>
            </div>
        </div>

    </div>
</div>

<!-- Popup Trigger Button (if main widget is disabled) -->
<?php if (get_option('biic_show_popup_trigger', false)): ?>
<div class="biic-popup-trigger">
    <button class="biic-trigger-btn" onclick="biicOpenChatPopup()">
        <span class="biic-trigger-icon">💬</span>
        <span class="biic-trigger-text"><?php esc_html_e('চ্যাট করুন', 'banglay-ielts-chatbot'); ?></span>
    </button>
</div>
<?php endif; ?>

<style>
/* Chat Popup Styles */
.biic-popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.biic-popup-overlay.active {
    opacity: 1;
    visibility: visible;
}

.biic-popup-container {
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    background: var(--biic-white);
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    transform: scale(0.95) translateY(20px);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.biic-popup-overlay.active .biic-popup-container {
    transform: scale(1) translateY(0);
}

.biic-popup-header {
    background: linear-gradient(135deg, var(--biic-primary) 0%, var(--biic-primary-dark) 100%);
    color: white;
    padding: 20px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.biic-popup-header-content {
    display: flex;
    align-items: center;
    gap: 16px;
}

.biic-popup-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    overflow: hidden;
}

.biic-popup-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.biic-popup-header-text h3 {
    margin: 0 0 4px 0;
    font-size: 18px;
    font-weight: 600;
}

.biic-popup-status {
    margin: 0;
    font-size: 13px;
    opacity: 0.9;
    display: flex;
    align-items: center;
    gap: 8px;
}

.biic-status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--biic-success);
    animation: biic-pulse 2s infinite;
}

.biic-status-indicator.offline {
    background: var(--biic-gray-400);
    animation: none;
}

.biic-popup-close {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: white;
    padding: 8px;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.biic-popup-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

.biic-popup-content {
    max-height: calc(90vh - 140px);
    overflow-y: auto;
}

.biic-welcome-section {
    padding: 24px;
}

.biic-welcome-message {
    text-align: center;
    margin-bottom: 24px;
}

.biic-welcome-icon {
    font-size: 48px;
    margin-bottom: 12px;
}

.biic-welcome-message h4 {
    margin: 0 0 8px 0;
    font-size: 20px;
    font-weight: 600;
    color: var(--biic-gray-900);
}

.biic-welcome-message p {
    margin: 0;
    color: var(--biic-gray-600);
    line-height: 1.5;
}

.biic-quick-start h5 {
    margin: 0 0 16px 0;
    font-size: 16px;
    font-weight: 600;
    color: var(--biic-gray-900);
    text-align: center;
}

.biic-quick-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 12px;
    margin-bottom: 24px;
}

.biic-quick-option {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 16px 12px;
    background: var(--biic-gray-50);
    border: 2px solid var(--biic-gray-200);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
}

.biic-quick-option:hover {
    border-color: var(--biic-primary);
    background: rgba(229, 62, 62, 0.05);
    transform: translateY(-2px);
}

.biic-option-icon {
    font-size: 24px;
}

.biic-option-text {
    font-size: 12px;
    font-weight: 500;
    color: var(--biic-gray-700);
}

.biic-special-offers {
    margin-bottom: 24px;
}

.biic-offer-banner {
    background: linear-gradient(135deg, #FFF7ED 0%, #FFEDD5 100%);
    border: 2px solid var(--biic-warning);
    border-radius: 12px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.biic-offer-icon {
    font-size: 24px;
}

.biic-offer-content {
    flex: 1;
}

.biic-offer-content h6 {
    margin: 0 0 4px 0;
    font-size: 14px;
    font-weight: 600;
    color: var(--biic-gray-900);
}

.biic-offer-content p {
    margin: 0;
    font-size: 12px;
    color: var(--biic-gray-600);
}

.biic-offer-btn {
    background: var(--biic-warning);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}

.biic-contact-info {
    border-top: 1px solid var(--biic-gray-200);
    padding-top: 20px;
}

.biic-contact-item {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.biic-contact-icon {
    font-size: 16px;
    width: 20px;
    text-align: center;
}

.biic-contact-details {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.biic-contact-label {
    font-size: 11px;
    color: var(--biic-gray-500);
    font-weight: 500;
}

.biic-contact-value {
    font-size: 13px;
    color: var(--biic-primary);
    text-decoration: none;
    font-weight: 500;
}

.biic-contact-value:hover {
    text-decoration: underline;
}

.biic-chat-area {
    padding: 0;
    display: flex;
    flex-direction: column;
    height: 400px;
}

.biic-chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: var(--biic-gray-50);
}

.biic-chat-input-area {
    padding: 16px 20px;
    border-top: 1px solid var(--biic-gray-200);
    background: white;
}

.biic-popup-footer {
    background: var(--biic-gray-50);
    border-top: 1px solid var(--biic-gray-200);
    padding: 16px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.biic-social-links {
    display: flex;
    gap: 12px;
}

.biic-social-link {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--biic-gray-200);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--biic-gray-600);
    text-decoration: none;
    transition: all 0.2s ease;
}

.biic-social-link:hover {
    background: var(--biic-primary);
    color: white;
    transform: translateY(-2px);
}

.biic-popup-copyright {
    font-size: 11px;
    color: var(--biic-gray-500);
}

.biic-popup-copyright a {
    color: var(--biic-primary);
    text-decoration: none;
    font-weight: 600;
}

/* Popup Trigger Button */
.biic-popup-trigger {
    position: fixed;
    bottom: 20px;
    left: 20px;
    z-index: 999998;
}

.biic-trigger-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--biic-primary);
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 25px;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(229, 62, 62, 0.3);
    transition: all 0.3s ease;
}

.biic-trigger-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(229, 62, 62, 0.4);
}

.biic-trigger-text {
    font-size: 14px;
    font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
    .biic-popup-container {
        width: 95%;
        max-width: none;
        margin: 20px;
    }
    
    .biic-quick-options {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .biic-popup-footer {
        flex-direction: column;
        gap: 12px;
        text-align: center;
    }
}
</style>

<script>
// Chat Popup JavaScript
function biicOpenChatPopup() {
    const popup = document.getElementById('biic-chat-popup');
    if (popup) {
        popup.style.display = 'flex';
        setTimeout(() => popup.classList.add('active'), 10);
        
        // Track popup open
        if (window.BiicTracking) {
            window.BiicTracking.trackEvent('popup_opened');
        }
    }
}

function biicCloseChatPopup() {
    const popup = document.getElementById('biic-chat-popup');
    if (popup) {
        popup.classList.remove('active');
        setTimeout(() => popup.style.display = 'none', 300);
    }
}

function biicStartChat(message) {
    // Hide welcome section and show chat area
    const welcomeSection = document.querySelector('.biic-welcome-section');
    const chatArea = document.querySelector('.biic-chat-area');
    
    if (welcomeSection && chatArea) {
        welcomeSection.style.display = 'none';
        chatArea.style.display = 'flex';
        
        // Send the initial message
        if (message && window.biicChatbot) {
            setTimeout(() => {
                window.biicChatbot.sendMessage(message);
            }, 300);
        }
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Close popup
    const closeBtn = document.querySelector('.biic-popup-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', biicCloseChatPopup);
    }
    
    // Close on overlay click
    const overlay = document.getElementById('biic-chat-popup');
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                biicCloseChatPopup();
            }
        });
    }
    
    // Quick option clicks
    document.querySelectorAll('.biic-quick-option, .biic-offer-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const message = this.dataset.message || this.textContent.trim();
            biicStartChat(message);
        });
    });
    
    // ESC key to close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            biicCloseChatPopup();
        }
    });
});
</script>

<?php
/**
 * Helper function to check business hours
 */
function is_within_business_hours($business_hours) {
    if (empty($business_hours['days']) || empty($business_hours['start']) || empty($business_hours['end'])) {
        return true;
    }
    
    $timezone = get_option('biic_timezone', 'Asia/Dhaka');
    $now = new DateTime('now', new DateTimeZone($timezone));
    
    $day_names = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
    $current_day = $day_names[$now->format('w')];
    
    if (!in_array($current_day, $business_hours['days'])) {
        return false;
    }
    
    $current_time = $now->format('Hi');
    $start_time = str_replace(':', '', $business_hours['start']);
    $end_time = str_replace(':', '', $business_hours['end']);
    
    return ($current_time >= $start_time && $current_time <= $end_time);
}
?>