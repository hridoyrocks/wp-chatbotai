/**
 * Banglay IELTS Professional AI Chatbot Widget
 * Made with Love Rocks - Professional Grade JavaScript
 */

class BIICChatbot {
    constructor() {
        this.isOpen = false;
        this.sessionId = null;
        this.messageQueue = [];
        this.isTyping = false;
        this.userTypingTimer = null;
        this.settings = {
            autoGreeting: true,
            greetingDelay: 2000,
            typingSpeed: 50,
            maxRetries: 3,
            enableSounds: true,
            enableAnimations: true,
            theme: 'modern'
        };

        this.init();
    }

    /**
     * Initialize chatbot
     */
    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    /**
     * Setup chatbot components
     */
    setup() {
        this.createSessionId();
        this.bindEvents();
        this.initializeTracking();

        // Auto greeting after delay
        if (this.settings.autoGreeting) {
            setTimeout(() => this.showAutoGreeting(), this.settings.greetingDelay);
        }

        // Keyboard shortcuts
        this.setupKeyboardShortcuts();

        // Visibility change handling
        this.handleVisibilityChange();
    }

    /**
     * Create unique session ID
     */
    createSessionId() {
        this.sessionId = 'biic_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

        // Store in localStorage for session persistence
        localStorage.setItem('biic_session_id', this.sessionId);

        // Track session start
        this.trackEvent('session_start', {
            sessionId: this.sessionId,
            timestamp: new Date().toISOString(),
            userAgent: navigator.userAgent,
            referrer: document.referrer,
            pageUrl: window.location.href
        });
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        const fab = document.querySelector('.biic-chat-fab');
        const closeBtn = document.querySelector('.biic-chat-close');
        const sendBtn = document.querySelector('.biic-send-button');
        const messageInput = document.querySelector('.biic-message-input');

        // FAB click
        fab?.addEventListener('click', () => this.toggleChat());

        // Close button
        closeBtn?.addEventListener('click', () => this.closeChat());

        // Send button
        sendBtn?.addEventListener('click', () => this.sendMessage());

        // Message input
        messageInput?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });

        // Input typing indicator
        messageInput?.addEventListener('input', () => this.handleUserTyping());

        // Quick reply buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('biic-quick-reply')) {
                this.sendQuickReply(e.target.textContent);
            }
        });

        // Outside click to close
        document.addEventListener('click', (e) => {
            const chatWidget = document.querySelector('.biic-chatbot-widget');
            if (this.isOpen && !chatWidget.contains(e.target)) {
                this.closeChat();
            }
        });

        // Escape key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.closeChat();
            }
        });
    }

    /**
     * Toggle chat window
     */
    toggleChat() {
        if (this.isOpen) {
            this.closeChat();
        } else {
            this.openChat();
        }
    }

    /**
     * Open chat window
     */
    openChat() {
        const chatWindow = document.querySelector('.biic-chat-window');
        const fab = document.querySelector('.biic-chat-fab');

        chatWindow.classList.add('open');
        fab.classList.add('open');
        this.isOpen = true;

        // Focus message input
        setTimeout(() => {
            const messageInput = document.querySelector('.biic-message-input');
            messageInput?.focus();
        }, 300);

        // Track open event
        this.trackEvent('chat_opened');

        // Remove notification badge
        this.clearNotification();

        // Load chat history
        this.loadChatHistory();
    }

    /**
     * Close chat window
     */
    closeChat() {
        const chatWindow = document.querySelector('.biic-chat-window');
        const fab = document.querySelector('.biic-chat-fab');

        chatWindow.classList.remove('open');
        fab.classList.remove('open');
        this.isOpen = false;

        // Track close event
        this.trackEvent('chat_closed');
    }

    /**
     * Send message
     */
    async sendMessage() {
        const messageInput = document.querySelector('.biic-message-input');
        const message = messageInput.value.trim();

        if (!message) return;

        // Clear input
        messageInput.value = '';

        // Add user message to chat
        this.addMessage('user', message);

        // Show typing indicator
        this.showTypingIndicator();

        // Track user message
        this.trackEvent('message_sent', {
            message: message,
            timestamp: new Date().toISOString()
        });

        try {
            // Send to backend
            const response = await this.sendToBackend(message);

            // Hide typing indicator
            this.hideTypingIndicator();

            // Add bot response
            if (response.success) {
                await this.addBotMessage(response.data);
            } else {
                this.addMessage('bot', 'দুঃখিত, একটি সমস্যা হয়েছে। আবার চেষ্টা করুন।');
            }

        } catch (error) {
            console.error('Chat error:', error);
            this.hideTypingIndicator();
            this.addMessage('bot', 'দুঃখিত, আমি এখন উপলব্ধ নই। অনুগ্রহ করে পরে আবার চেষ্টা করুন।');
        }
    }

    /**
     * Send quick reply
     */
    sendQuickReply(message) {
        // Remove quick replies
        const quickReplies = document.querySelector('.biic-quick-replies');
        if (quickReplies) {
            quickReplies.remove();
        }

        // Send as regular message
        const messageInput = document.querySelector('.biic-message-input');
        messageInput.value = message;
        this.sendMessage();
    }

    /**
     * Add message to chat
     */
    addMessage(type, content, options = {}) {
        const messagesContainer = document.querySelector('.biic-chat-messages');
        const messageElement = this.createMessageElement(type, content, options);

        messagesContainer.appendChild(messageElement);
        this.scrollToBottom();

        // Play notification sound
        if (type === 'bot' && this.settings.enableSounds) {
            this.playNotificationSound();
        }
    }

    /**
     * Add bot message with typing effect
     */
    async addBotMessage(response) {
        const content = response.message || response;

        // Create message element
        const messageElement = this.createMessageElement('bot', '', {
            timestamp: new Date()
        });

        const messagesContainer = document.querySelector('.biic-chat-messages');
        messagesContainer.appendChild(messageElement);

        // Type message with effect
        await this.typeMessage(messageElement.querySelector('.biic-message-bubble'), content);

        // Add quick replies if available
        if (response.quickReplies && response.quickReplies.length > 0) {
            this.addQuickReplies(messageElement, response.quickReplies);
        }

        // Check for lead capture
        if (response.showLeadForm) {
            this.showLeadCaptureForm();
        }

        this.scrollToBottom();
    }

    /**
     * Create message element
     */
    createMessageElement(type, content, options = {}) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `biic-message ${type}`;

        const avatar = document.createElement('div');
        avatar.className = 'biic-message-avatar';
        avatar.textContent = type === 'user' ? 'আপ' : 'বট';

        const contentDiv = document.createElement('div');
        contentDiv.className = 'biic-message-content';

        const bubble = document.createElement('div');
        bubble.className = 'biic-message-bubble';
        bubble.innerHTML = this.formatMessage(content);

        const time = document.createElement('div');
        time.className = 'biic-message-time';
        time.textContent = this.formatTime(options.timestamp || new Date());

        contentDiv.appendChild(bubble);
        contentDiv.appendChild(time);
        messageDiv.appendChild(avatar);
        messageDiv.appendChild(contentDiv);

        return messageDiv;
    }

    /**
     * Type message with realistic effect
     */
    async typeMessage(element, text) {
        let i = 0;
        const words = text.split(' ');

        return new Promise((resolve) => {
            const typeInterval = setInterval(() => {
                if (i < words.length) {
                    element.innerHTML = words.slice(0, i + 1).join(' ');
                    i++;
                    this.scrollToBottom();
                } else {
                    clearInterval(typeInterval);
                    resolve();
                }
            }, this.settings.typingSpeed);
        });
    }

    /**
     * Format message content
     */
    formatMessage(content) {
        if (!content) return '';

        // Convert line breaks
        content = content.replace(/\n/g, '<br>');

        // Convert URLs to links
        content = content.replace(
            /(https?:\/\/[^\s]+)/g,
            '<a href="$1" target="_blank" rel="noopener">$1</a>'
        );

        // Convert phone numbers to clickable links
        content = content.replace(
            /(\+880\s?\d{10}|\+880\s?\d{4}\s?\d{6})/g,
            '<a href="tel:$1">$1</a>'
        );

        // Highlight course names
        const courses = ['IELTS', 'Comprehensive', 'Focus', 'Crash'];
        courses.forEach(course => {
            const regex = new RegExp(`\\b${course}\\b`, 'gi');
            content = content.replace(regex, `<strong>$&</strong>`);
        });

        return content;
    }

    /**
     * Add quick replies
     */
    addQuickReplies(messageElement, replies) {
        const quickRepliesDiv = document.createElement('div');
        quickRepliesDiv.className = 'biic-quick-replies';

        replies.forEach(reply => {
            const button = document.createElement('button');
            button.className = 'biic-quick-reply';
            button.textContent = reply;
            quickRepliesDiv.appendChild(button);
        });

        messageElement.querySelector('.biic-message-content').appendChild(quickRepliesDiv);
    }

    /**
     * Show typing indicator
     */
    showTypingIndicator() {
        if (this.isTyping) return;

        this.isTyping = true;
        const messagesContainer = document.querySelector('.biic-chat-messages');

        const typingDiv = document.createElement('div');
        typingDiv.className = 'biic-message bot typing';
        typingDiv.innerHTML = `
            <div class="biic-message-avatar">বট</div>
            <div class="biic-message-content">
                <div class="biic-typing-indicator">
                    <div class="biic-typing-dots">
                        <div class="biic-typing-dot"></div>
                        <div class="biic-typing-dot"></div>
                        <div class="biic-typing-dot"></div>
                    </div>
                </div>
            </div>
        `;

        messagesContainer.appendChild(typingDiv);
        this.scrollToBottom();
    }

    /**
     * Hide typing indicator
     */
    hideTypingIndicator() {
        const typingIndicator = document.querySelector('.biic-message.typing');
        if (typingIndicator) {
            typingIndicator.remove();
        }
        this.isTyping = false;
    }

    /**
     * Handle user typing
     */
    handleUserTyping() {
        // Clear existing timer
        if (this.userTypingTimer) {
            clearTimeout(this.userTypingTimer);
        }

        // Track typing start
        this.trackEvent('user_typing_start');

        // Set timer to track typing end
        this.userTypingTimer = setTimeout(() => {
            this.trackEvent('user_typing_end');
        }, 2000);
    }

    /**
     * Show auto greeting
     */
    showAutoGreeting() {
        if (this.isOpen) return;

        // Show notification badge
        this.showNotification();

        // Auto open after another delay
        setTimeout(() => {
            if (!this.isOpen) {
                this.openChat();
                setTimeout(() => {
                    this.addMessage('bot', 'আস্সালামু আলাইকুম!  আমি Banglay IELTS এর AI - Chatbot । IELTS সম্পর্কে কিছু জানতে চান?', {
                        timestamp: new Date()
                    });
                }, 500);
            }
        }, 3000);
    }

    /**
     * Show notification badge
     */
    showNotification(count = 1) {
        let badge = document.querySelector('.biic-chat-notification');
        if (!badge) {
            badge = document.createElement('div');
            badge.className = 'biic-chat-notification';
            document.querySelector('.biic-chat-fab').appendChild(badge);
        }
        badge.textContent = count;
        badge.style.display = 'flex';
    }

    /**
     * Clear notification badge
     */
    clearNotification() {
        const badge = document.querySelector('.biic-chat-notification');
        if (badge) {
            badge.style.display = 'none';
        }
    }

    /**
     * Scroll to bottom
     */
    scrollToBottom() {
        const messagesContainer = document.querySelector('.biic-chat-messages');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }

    /**
     * Format time
     */
    formatTime(date) {
        return new Intl.DateTimeFormat('bn-BD', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        }).format(date);
    }

    /**
     * Send message to backend
     */
    async sendToBackend(message) {
        const formData = new FormData();
        formData.append('action', 'biic_chat_message');
        formData.append('message', message);
        formData.append('session_id', this.sessionId);
        formData.append('nonce', biic_ajax.nonce);

        const response = await fetch(biic_ajax.ajax_url, {
            method: 'POST',
            body: formData
        });

        return await response.json();
    }

    /**
     * Track events
     */
    trackEvent(eventName, data = {}) {
        // Track via AJAX
        const formData = new FormData();
        formData.append('action', 'biic_track_event');
        formData.append('event_name', eventName);
        formData.append('event_data', JSON.stringify({
            ...data,
            sessionId: this.sessionId,
            timestamp: new Date().toISOString(),
            pageUrl: window.location.href
        }));
        formData.append('nonce', biic_ajax.nonce);

        fetch(biic_ajax.ajax_url, {
            method: 'POST',
            body: formData
        }).catch(error => {
            console.error('Tracking error:', error);
        });

        // Also track in localStorage for offline support
        const events = JSON.parse(localStorage.getItem('biic_events') || '[]');
        events.push({
            eventName,
            data,
            timestamp: new Date().toISOString()
        });

        // Keep only last 100 events
        if (events.length > 100) {
            events.splice(0, events.length - 100);
        }

        localStorage.setItem('biic_events', JSON.stringify(events));
    }

    /**
     * Initialize tracking
     */
    initializeTracking() {
        // Track page view
        this.trackEvent('page_view', {
            url: window.location.href,
            title: document.title,
            referrer: document.referrer
        });

        // Track scroll depth
        let maxScroll = 0;
        window.addEventListener('scroll', () => {
            const scrollPercent = Math.round(
                (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100
            );

            if (scrollPercent > maxScroll) {
                maxScroll = scrollPercent;

                // Track milestones
                if ([25, 50, 75, 100].includes(scrollPercent)) {
                    this.trackEvent('scroll_depth', { percent: scrollPercent });
                }
            }
        });

        // Track time on page
        const startTime = Date.now();
        window.addEventListener('beforeunload', () => {
            const timeOnPage = Math.round((Date.now() - startTime) / 1000);
            this.trackEvent('time_on_page', { seconds: timeOnPage });
        });

        // Track clicks on important elements
        document.addEventListener('click', (e) => {
            if (e.target.closest('a[href*="tel:"]')) {
                this.trackEvent('phone_click', {
                    phone: e.target.textContent,
                    element: e.target.outerHTML
                });
            }

            if (e.target.closest('a[href*="mailto:"]')) {
                this.trackEvent('email_click', {
                    email: e.target.textContent,
                    element: e.target.outerHTML
                });
            }
        });
    }

    /**
     * Setup keyboard shortcuts
     */
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + K to open chat
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                if (!this.isOpen) {
                    this.openChat();
                }
            }
        });
    }

    /**
     * Handle visibility change
     */
    handleVisibilityChange() {
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.trackEvent('page_hidden');
            } else {
                this.trackEvent('page_visible');
            }
        });
    }

    /**
     * Show lead capture form
     */
    showLeadCaptureForm() {
        const messagesContainer = document.querySelector('.biic-chat-messages');

        const formHtml = `
            <div class="biic-message bot">
                <div class="biic-message-avatar">বট</div>
                <div class="biic-message-content">
                    <div class="biic-lead-form">
                        <div class="biic-form-title">📚 আরও তথ্য পেতে যোগাযোগের বিস্তারিত দিন</div>
                        <div class="biic-form-group">
                            <label class="biic-form-label">নাম *</label>
                            <input type="text" class="biic-form-input" name="name" placeholder="আপনার নাম লিখুন" required>
                        </div>
                        <div class="biic-form-group">
                            <label class="biic-form-label">ফোন নম্বর *</label>
                            <input type="tel" class="biic-form-input" name="phone" placeholder="+880 1X XX XX XX XX" required>
                        </div>
                        <div class="biic-form-group">
                            <label class="biic-form-label">ইমেইল</label>
                            <input type="email" class="biic-form-input" name="email" placeholder="আপনার ইমেইল">
                        </div>
                        <button type="submit" class="biic-form-submit">📞 ফ্রি কনসালটেশন বুক করুন</button>
                    </div>
                </div>
            </div>
        `;

        messagesContainer.insertAdjacentHTML('beforeend', formHtml);

        // Bind form submission
        const form = messagesContainer.querySelector('.biic-lead-form');
        const submitBtn = form.querySelector('.biic-form-submit');

        submitBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            await this.submitLeadForm(form);
        });

        this.scrollToBottom();
    }

    /**
     * Submit lead form
     */
    async submitLeadForm(form) {
        const formData = new FormData();
        const inputs = form.querySelectorAll('.biic-form-input');

        let isValid = true;
        const leadData = { session_id: this.sessionId };

        inputs.forEach(input => {
            if (input.required && !input.value.trim()) {
                isValid = false;
                input.style.borderColor = 'var(--biic-error)';
            } else {
                input.style.borderColor = '';
                leadData[input.name] = input.value.trim();
            }
        });

        if (!isValid) {
            this.addMessage('bot', '❌ অনুগ্রহ করে সব প্রয়োজনীয় তথ্য পূরণ করুন।');
            return;
        }

        // Submit to backend
        formData.append('action', 'biic_submit_lead');
        formData.append('lead_data', JSON.stringify(leadData));
        formData.append('nonce', biic_ajax.nonce);

        try {
            const response = await fetch(biic_ajax.ajax_url, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Replace form with success message
                form.innerHTML = `
                    <div class="biic-success-message">
                        ✅ ধন্যবাদ! আমাদের কাউন্সেলর শীঘ্রই আপনার সাথে যোগাযোগ করবেন।
                    </div>
                `;

                // Track lead submission
                this.trackEvent('lead_submitted', leadData);

                // Show follow-up message
                setTimeout(() => {
                    this.addMessage('bot', '🎯 এই সময়ে আমাদের হটলাইনে কল করতে পারেন: +880 961 382 0821');
                }, 2000);

            } else {
                this.addMessage('bot', '❌ দুঃখিত, একটি সমস্যা হয়েছে। অনুগ্রহ করে সরাসরি কল করুন +880 961 382 0821');
            }

        } catch (error) {
            console.error('Lead submission error:', error);
            this.addMessage('bot', '❌ নেটওয়ার্ক সমস্যা। অনুগ্রহ করে আবার চেষ্টা করুন।');
        }
    }

    /**
     * Play notification sound
     */
    playNotificationSound() {
        if (!this.settings.enableSounds) return;

        // Create audio context for notification sound
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
        oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1);

        gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);

        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.2);
    }

    /**
     * Load chat history
     */
    async loadChatHistory() {
        try {
            const formData = new FormData();
            formData.append('action', 'biic_get_chat_history');
            formData.append('session_id', this.sessionId);
            formData.append('nonce', biic_ajax.nonce);

            const response = await fetch(biic_ajax.ajax_url, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success && result.data.length > 0) {
                const messagesContainer = document.querySelector('.biic-chat-messages');
                messagesContainer.innerHTML = ''; // Clear existing messages

                result.data.forEach(message => {
                    this.addMessage(
                        message.message_type,
                        message.content,
                        { timestamp: new Date(message.timestamp) }
                    );
                });
            }

        } catch (error) {
            console.error('Error loading chat history:', error);
        }
    }
}

// Initialize chatbot when page loads
document.addEventListener('DOMContentLoaded', () => {
    window.biicChatbot = new BIICChatbot();
});

// Global functions for external access
window.BIICChatbot = {
    open: () => window.biicChatbot?.openChat(),
    close: () => window.biicChatbot?.closeChat(),
    toggle: () => window.biicChatbot?.toggleChat(),
    sendMessage: (message) => {
        if (window.biicChatbot) {
            const input = document.querySelector('.biic-message-input');
            input.value = message;
            window.biicChatbot.sendMessage();
        }
    },
    trackEvent: (event, data) => window.biicChatbot?.trackEvent(event, data)
};