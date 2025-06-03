<?php
/**
 * AI Integration for Banglay IELTS Chatbot
 * OpenAI GPT Integration with Custom Training Data
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BIIC_AI_Integration {
    
    /**
     * OpenAI API configuration
     */
    private $api_key;
    private $api_url = 'https://api.openai.com/v1/chat/completions';
    private $model = 'gpt-3.5-turbo';
    
    /**
     * Custom training context
     */
    private $system_context;
    private $conversation_history = array();
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->api_key = get_option('biic_openai_api_key', '');
        $this->load_system_context();
    }
    
    /**
     * Load system context for AI
     */
    private function load_system_context() {
        $this->system_context = "You are a professional AI assistant for Banglay IELTS & Immigration Center (BIIC), Bangladesh's leading IELTS training institute. 

COMPANY INFORMATION:
- Name: Banglay IELTS & Immigration Center (BIIC)
- Founder: Rashed Hossain
- Mission: Making IELTS preparation simple, affordable, and effective
- Community: Bangladesh's largest IELTS community (573K+ YouTube, 492K+ Facebook)

CONTACT INFORMATION:
- Hotline: +880 961 382 0821 (10 AM - 6 PM)
- Email: info@biic.com.bd
- Website: banglayelts.com
- Admission: admission.banglayelts.com

BRANCH LOCATIONS:
1. Dhaka (Main): Rahman Heights, Uttara (Test Center + Training)
2. Chattogram: GEC Circle area
3. Sylhet: Ambarkhana area

COURSES OFFERED:
1. IELTS Comprehensive (4.5 months, 50+ classes) - Beginner level
2. IELTS Focus (3 months, 30+ classes) - Intermediate level, band 6.0-7.0 target
3. IELTS Crash (1.5 months, 30+ classes) - Intensive preparation
4. Online Course (2 months, weekly 3/4 classes, 1 year validity)

COURSE FEATURES:
- No extra charge for course materials
- Mock tests with teacher feedback
- Language Club & Student Lounge
- Secret Mobile App access (online course)
- Unlimited Cambridge Mocks (online)

STUDY ABROAD SERVICES:
- 95% visa success rate
- 300+ global university partnerships
- UK, USA, Canada, Australia, Denmark, Finland
- Scholarship guidance
- Complete application support

IELTS TEST REGISTRATION:
- IDP and British Council authorized center
- Test location: Rahman Heights, Uttara
- Speaking test: 3rd floor, LRW test: 4th floor

RESPONSE GUIDELINES:
1. Always be helpful, encouraging, and professional
2. Use mix of Bengali and English naturally (like 'IELTS course fee koto?')
3. Provide specific information about courses, fees, locations
4. Always include contact information for detailed queries
5. Encourage course enrollment and free consultation
6. For fees, direct to phone call or website
7. Be supportive about IELTS preparation journey
8. Mention success stories and community size when relevant

CONVERSATION STYLE:
- Warm and encouraging tone
- Use emojis appropriately (📚, 🎯, 📞, etc.)
- Mix Bengali-English as natural for Bangladeshi students
- Be specific about course details
- Always provide next steps (call, visit, online application)

LEAD CAPTURE STRATEGY:
- After discussing fees or admission, offer free consultation
- Collect name, phone, email, course interest
- Emphasize limited seats and early bird offers
- Mention 95% visa success rate for study abroad queries

Remember: You represent Bangladesh's most trusted IELTS training institute. Be confident, helpful, and always guide towards enrollment or consultation.";
    }
    
    /**
     * Generate AI response
     */
    public function generate_response($user_message, $session_context = array()) {
        // Check if OpenAI API is configured
        if (empty($this->api_key)) {
            return $this->get_fallback_response($user_message);
        }
        
        try {
            // Prepare conversation context
            $messages = $this->prepare_conversation_context($user_message, $session_context);
            
            // Call OpenAI API
            $response = $this->call_openai_api($messages);
            
            if ($response && isset($response['choices'][0]['message']['content'])) {
                $ai_response = trim($response['choices'][0]['message']['content']);
                
                // Process and enhance response
                $enhanced_response = $this->enhance_response($ai_response, $user_message);
                
                return $enhanced_response;
            }
            
        } catch (Exception $e) {
            error_log('BIIC AI Integration Error: ' . $e->getMessage());
        }
        
        // Fallback to rule-based response
        return $this->get_fallback_response($user_message);
    }
    
    /**
     * Prepare conversation context for OpenAI
     */
    private function prepare_conversation_context($user_message, $session_context = array()) {
        $messages = array();
        
        // System message with training context
        $messages[] = array(
            'role' => 'system',
            'content' => $this->system_context
        );
        
        // Add session context if available
        if (!empty($session_context)) {
            $context_info = "SESSION CONTEXT:\n";
            
            if (isset($session_context['location'])) {
                $context_info .= "User Location: " . $session_context['location'] . "\n";
            }
            
            if (isset($session_context['device_type'])) {
                $context_info .= "Device: " . $session_context['device_type'] . "\n";
            }
            
            if (isset($session_context['previous_intents'])) {
                $context_info .= "Previous Topics: " . implode(', ', $session_context['previous_intents']) . "\n";
            }
            
            if (isset($session_context['lead_score'])) {
                $context_info .= "Lead Score: " . $session_context['lead_score'] . "/100\n";
            }
            
            $messages[] = array(
                'role' => 'system',
                'content' => $context_info
            );
        }
        
        // Add conversation history (last 5 messages)
        $recent_history = array_slice($this->conversation_history, -10); // Last 10 messages
        foreach ($recent_history as $msg) {
            $messages[] = $msg;
        }
        
        // Add current user message
        $messages[] = array(
            'role' => 'user',
            'content' => $user_message
        );
        
        return $messages;
    }
    
    /**
     * Call OpenAI API
     */
    private function call_openai_api($messages) {
        $headers = array(
            'Authorization: Bearer ' . $this->api_key,
            'Content-Type: application/json'
        );
        
        $data = array(
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => 500,
            'temperature' => 0.7,
            'frequency_penalty' => 0.3,
            'presence_penalty' => 0.3
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        if ($http_code !== 200) {
            throw new Exception('API Error: HTTP ' . $http_code . ' - ' . $response);
        }
        
        $decoded_response = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON Decode Error: ' . json_last_error_msg());
        }
        
        return $decoded_response;
    }
    
    /**
     * Enhance AI response with structured data
     */
    private function enhance_response($ai_response, $user_message) {
        $enhanced = array(
            'message' => $ai_response,
            'quick_replies' => array(),
            'show_lead_form' => false,
            'suggested_actions' => array()
        );
        
        // Detect intent from AI response to add appropriate quick replies
        $response_lower = mb_strtolower($ai_response, 'UTF-8');
        
        // Course inquiry responses
        if (strpos($response_lower, 'course') !== false || strpos($response_lower, 'কোর্স') !== false) {
            $enhanced['quick_replies'] = array(
                'Comprehensive Course',
                'Focus Course', 
                'Crash Course',
                'Online Course',
                'ফি জানতে চাই'
            );
        }
        
        // Fee inquiry responses
        if (strpos($response_lower, 'fee') !== false || strpos($response_lower, 'ফি') !== false || 
            strpos($response_lower, 'cost') !== false || strpos($response_lower, 'price') !== false) {
            $enhanced['quick_replies'] = array(
                'এখনই কল করুন',
                'ফ্রি কনসালটেশন',
                'Office Visit করব',
                'Online Apply করব'
            );
            $enhanced['show_lead_form'] = true;
        }
        
        // Contact/admission responses
        if (strpos($response_lower, 'contact') !== false || strpos($response_lower, 'admission') !== false ||
            strpos($response_lower, 'ভর্তি') !== false || strpos($response_lower, 'যোগাযোগ') !== false) {
            $enhanced['quick_replies'] = array(
                '📞 এখনই কল করুন',
                '📍 Office Visit',
                '💻 Online Apply',
                '📧 Email করব'
            );
            $enhanced['show_lead_form'] = true;
        }
        
        // Study abroad responses
        if (strpos($response_lower, 'study abroad') !== false || strpos($response_lower, 'বিদেশ') !== false ||
            strpos($response_lower, 'visa') !== false || strpos($response_lower, 'scholarship') !== false) {
            $enhanced['quick_replies'] = array(
                'UK এ পড়তে চাই',
                'Canada Immigration',
                'Scholarship চাই',
                'Free Consultation'
            );
            $enhanced['show_lead_form'] = true;
        }
        
        // Location/branch responses
        if (strpos($response_lower, 'location') !== false || strpos($response_lower, 'branch') !== false ||
            strpos($response_lower, 'address') !== false || strpos($response_lower, 'ঠিকানা') !== false) {
            $enhanced['quick_replies'] = array(
                'Dhaka Branch',
                'Chattogram Branch',
                'Sylhet Branch',
                'Visit করতে চাই'
            );
        }
        
        // Add default quick replies if none set
        if (empty($enhanced['quick_replies'])) {
            $enhanced['quick_replies'] = array(
                'কোর্স সম্পর্কে জানব',
                'ফি জানতে চাই',
                'ঠিকানা চাই',
                'এখনই কল করব'
            );
        }
        
        // Add suggested actions for high-intent responses
        if ($enhanced['show_lead_form']) {
            $enhanced['suggested_actions'] = array(
                array(
                    'type' => 'phone_call',
                    'text' => 'এখনই কল করুন',
                    'action' => 'tel:+8809613820821'
                ),
                array(
                    'type' => 'website_visit',
                    'text' => 'Online Apply',
                    'action' => 'https://admission.banglayelts.com'
                )
            );
        }
        
        return $enhanced;
    }
    
    /**
     * Get fallback response (rule-based)
     */
    private function get_fallback_response($user_message) {
        $message_lower = mb_strtolower($user_message, 'UTF-8');
        
        // Course fee inquiries
        if (strpos($message_lower, 'ফি') !== false || strpos($message_lower, 'fee') !== false || 
            strpos($message_lower, 'cost') !== false || strpos($message_lower, 'price') !== false) {
            return array(
                'message' => '💰 **কোর্স ফি জানতে:**\n\n📞 **Direct Call:** +880 961 382 0821\n🌐 **Online:** admission.banglayelts.com\n\n💡 **বিশেষ সুবিধা:**\n✅ Course materials free\n✅ Mock tests included\n✅ Expert teacher feedback\n\nএখনই ফ্রি কনসালটেশন নিন! 🎯',
                'quick_replies' => array('এখনই কল করুন', 'ফ্রি কনসালটেশন', 'Office Visit', 'Online Apply'),
                'show_lead_form' => true
            );
        }
        
        // Course inquiries
        if (strpos($message_lower, 'কোর্স') !== false || strpos($message_lower, 'course') !== false) {
            return array(
                'message' => '📚 **Banglay IELTS কোর্সসমূহ:**\n\n🎯 **IELTS Comprehensive** (4.5 months)\n→ Beginner level, complete preparation\n\n🎯 **IELTS Focus** (3 months)\n→ Intermediate level, band 6.0-7.0\n\n🎯 **IELTS Crash** (1.5 months)\n→ Intensive, quick preparation\n\n🎯 **Online Course** (2 months)\n→ Live classes, 1 year validity\n\nকোনটি সম্পর্কে বিস্তারিত জানতে চান? 🤔',
                'quick_replies' => array('Comprehensive', 'Focus', 'Crash', 'Online', 'ফি জানতে চাই')
            );
        }
        
        // Contact inquiries
        if (strpos($message_lower, 'contact') !== false || strpos($message_lower, 'ফোন') !== false || 
            strpos($message_lower, 'phone') !== false || strpos($message_lower, 'যোগাযোগ') !== false) {
            return array(
                'message' => '📞 **যোগাযোগ করুন:**\n\n🔥 **হটলাইন:** +880 961 382 0821\n📧 **ইমেইল:** info@biic.com.bd\n🌐 **ওয়েবসাইট:** banglayelts.com\n\n⏰ **সময়:** সকাল ১০টা - সন্ধ্যা ৬টা\n📍 **অফিস:** Rahman Heights, Uttara, Dhaka\n\nবিশেষ দ্রষ্টব্য: Bangladesh এর সবচেয়ে বড় IELTS community! 🏆',
                'quick_replies' => array('এখনই কল করুন', 'Office Visit', 'ইমেইল করব', 'ফ্রি কনসালটেশন')
            );
        }
        
        // Location inquiries
        if (strpos($message_lower, 'address') !== false || strpos($message_lower, 'location') !== false || 
            strpos($message_lower, 'ঠিকানা') !== false || strpos($message_lower, 'কোথায়') !== false) {
            return array(
                'message' => '📍 **Banglay IELTS এর শাখাসমূহ:**\n\n🏢 **ঢাকা (প্রধান):**\nRahman Heights, Plot-01, Uttara\n(IELTS Test Center ও Training)\n\n🏢 **চট্টগ্রাম:**\nGEC Circle area\n\n🏢 **সিলেট:**\nAmbarkhana area\n\n📞 **হটলাইন:** +880 961 382 0821\n🌐 **Map:** গুগল ম্যাপে "Banglay IELTS" লিখে খুঁজুন',
                'quick_replies' => array('Dhaka Branch', 'Chattogram Branch', 'Sylhet Branch', 'Visit করব')
            );
        }
        
        // Greeting
        if (strpos($message_lower, 'হাই') !== false || strpos($message_lower, 'hello') !== false || 
            strpos($message_lower, 'সালাম') !== false || strpos($message_lower, 'হ্যালো') !== false) {
            return array(
                'message' => 'আস্সালামু আলাইকুম! 🌟\n\nআমি Banglay IELTS এর AI সহায়ক। Bangladesh এর সবচেয়ে বড় IELTS community তে আপনাকে স্বাগতম! 🎓\n\n📊 **আমাদের সাফল্য:**\n👥 573K+ YouTube Family\n👥 492K+ Facebook Community\n🎯 হাজার হাজার সফল student\n\nIELTS নিয়ে কী জানতে চান? 🤔',
                'quick_replies' => array('কোর্স সম্পর্কে', 'ফি জানতে চাই', 'Study Abroad', 'ঠিকানা চাই')
            );
        }
        
        // Study abroad
        if (strpos($message_lower, 'study abroad') !== false || strpos($message_lower, 'বিদেশ') !== false || 
            strpos($message_lower, 'scholarship') !== false || strpos($message_lower, 'visa') !== false) {
            return array(
                'message' => '🌍 **Study Abroad with BIIC:**\n\n✈️ **Destinations:** UK, USA, Canada, Australia, Denmark, Finland\n🎓 **Services:** University selection, Application, Visa processing\n📈 **Success Rate:** 95% visa approval\n🏛️ **Partners:** 300+ global universities\n💰 **Scholarship:** Complete guidance\n\n🔥 **Special:** Complete package from IELTS preparation to visa! 🎯\n\n📞 **Consultation:** +880 961 382 0821',
                'quick_replies' => array('UK Study', 'Canada Immigration', 'USA University', 'Scholarship Info'),
                'show_lead_form' => true
            );
        }
        
        // Default response
        return array(
            'message' => 'ধন্যবাদ আপনার প্রশ্নের জন্য! 😊\n\nআপনার প্রশ্নটি সম্পর্কে বিস্তারিত জানতে আমাদের এক্সপার্ট কাউন্সেলরের সাথে কথা বলুন।\n\n📞 **এখনই কল করুন:** +880 961 382 0821\n🌐 **Visit:** banglayelts.com\n\n💡 **মনে রাখবেন:** আমরা Bangladesh এর #1 IELTS Training Center! 🏆',
            'quick_replies' => array('কোর্স সম্পর্কে', 'ফি জানতে চাই', 'Study Abroad', 'Contact Info')
        );
    }
    
    /**
     * Add message to conversation history
     */
    public function add_to_conversation($role, $message) {
        $this->conversation_history[] = array(
            'role' => $role,
            'content' => $message
        );
        
        // Keep only last 20 messages to manage context length
        if (count($this->conversation_history) > 20) {
            $this->conversation_history = array_slice($this->conversation_history, -20);
        }
    }
    
    /**
     * Clear conversation history
     */
    public function clear_conversation_history() {
        $this->conversation_history = array();
    }
    
    /**
     * Analyze sentiment of message
     */
    public function analyze_sentiment($message) {
        // Simple sentiment analysis
        $positive_words = array('ভালো', 'good', 'great', 'excellent', 'thanks', 'ধন্যবাদ', 'সুন্দর', 'চমৎকার');
        $negative_words = array('খারাপ', 'bad', 'poor', 'terrible', 'সমস্যা', 'problem', 'issue');
        
        $message_lower = mb_strtolower($message, 'UTF-8');
        $positive_count = 0;
        $negative_count = 0;
        
        foreach ($positive_words as $word) {
            if (strpos($message_lower, $word) !== false) {
                $positive_count++;
            }
        }
        
        foreach ($negative_words as $word) {
            if (strpos($message_lower, $word) !== false) {
                $negative_count++;
            }
        }
        
        if ($positive_count > $negative_count) {
            return array('sentiment' => 'positive', 'score' => 0.7);
        } elseif ($negative_count > $positive_count) {
            return array('sentiment' => 'negative', 'score' => 0.7);
        } else {
            return array('sentiment' => 'neutral', 'score' => 0.5);
        }
    }
    
    /**
     * Extract keywords from message
     */
    public function extract_keywords($message) {
        // Remove common Bengali/English stop words
        $stop_words = array(
            'আমি', 'আপনি', 'তুমি', 'তারা', 'আমরা', 'তোমরা',
            'the', 'is', 'at', 'which', 'on', 'and', 'a', 'to', 'are', 'as', 'was', 'with', 'for'
        );
        
        // Split message into words
        $words = preg_split('/\s+/', mb_strtolower($message, 'UTF-8'));
        
        // Filter out stop words and short words
        $keywords = array();
        foreach ($words as $word) {
            $word = trim($word, '.,!?;:"()[]{}');
            if (strlen($word) > 2 && !in_array($word, $stop_words)) {
                $keywords[] = $word;
            }
        }
        
        return array_unique($keywords);
    }
    
    /**
     * Check if OpenAI is available
     */
    public function is_ai_available() {
        return !empty($this->api_key);
    }
    
    /**
     * Test OpenAI connection
     */
    public function test_connection() {
        if (!$this->is_ai_available()) {
            return array(
                'success' => false,
                'message' => 'OpenAI API key not configured'
            );
        }
        
        try {
            $test_messages = array(
                array('role' => 'system', 'content' => 'You are a helpful assistant.'),
                array('role' => 'user', 'content' => 'Hello, this is a test message.')
            );
            
            $response = $this->call_openai_api($test_messages);
            
            if ($response && isset($response['choices'][0]['message']['content'])) {
                return array(
                    'success' => true,
                    'message' => 'OpenAI connection successful'
                );
            } else {
                return array(
                    'success' => false,
                    'message' => 'Invalid response from OpenAI'
                );
            }
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            );
        }
    }
}