<?php
/**
 * Banglay IELTS Chatbot Core Logic
 * Professional AI-powered conversation engine
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BIIC_Chatbot {
    
    /**
     * Current session
     */
    private $session_id;
    private $session_data;
    
    /**
     * AI Integration
     */
    private $ai_integration;
    
    /**
     * Training data
     */
    private $training_data;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->ai_integration = BIIC()->ai_integration;
        $this->load_training_data();
    }
    
    /**
     * Initialize session
     */
    public function init_session() {
        if (!session_id()) {
            session_start();
        }
        
        // Get or create session ID
        if (isset($_SESSION['biic_session_id'])) {
            $this->session_id = $_SESSION['biic_session_id'];
        } else {
            $this->session_id = $this->create_new_session();
            $_SESSION['biic_session_id'] = $this->session_id;
        }
        
        // Load session data
        $this->load_session_data();
    }
    
    /**
     * Create new session
     */
    private function create_new_session() {
        $database = BIIC()->database;
        
        // Detect user location and device
        $user_info = $this->detect_user_info();
        
        $session_data = array(
            'ip_address' => $user_info['ip'],
            'user_agent' => $user_info['user_agent'],
            'location' => $user_info['location'],
            'country' => $user_info['country'],
            'city' => $user_info['city'],
            'device_type' => $user_info['device_type'],
            'browser' => $user_info['browser'],
            'referrer' => $user_info['referrer'],
            'page_url' => $user_info['page_url'],
            'utm_source' => $user_info['utm_source'],
            'utm_medium' => $user_info['utm_medium'],
            'utm_campaign' => $user_info['utm_campaign']
        );
        
        $session_id = $database->insert_chat_session($session_data);
        
        if ($session_id) {
            // Get the actual session_id string from database
            global $wpdb;
            $session_record = $wpdb->get_row($wpdb->prepare(
                "SELECT session_id FROM {$database->get_table('chat_sessions')} WHERE id = %d",
                $session_id
            ));
            
            return $session_record ? $session_record->session_id : null;
        }
        
        return null;
    }
    
    /**
     * Load session data
     */
    private function load_session_data() {
        if (!$this->session_id) return;
        
        $database = BIIC()->database;
        $this->session_data = $database->get_chat_session($this->session_id);
    }
    
    /**
     * Detect user information
     */
    private function detect_user_info() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip = $this->get_user_ip();
        
        // Detect device type
        $device_type = 'desktop';
        if (wp_is_mobile()) {
            $device_type = 'mobile';
        } elseif (preg_match('/tablet|ipad/i', $user_agent)) {
            $device_type = 'tablet';
        }
        
        // Detect browser
        $browser = 'unknown';
        if (strpos($user_agent, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (strpos($user_agent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (strpos($user_agent, 'Safari') !== false) {
            $browser = 'Safari';
        } elseif (strpos($user_agent, 'Edge') !== false) {
            $browser = 'Edge';
        }
        
        // Get location from IP (basic implementation)
        $location_data = $this->get_location_from_ip($ip);
        
        // Parse UTM parameters
        $utm_source = isset($_GET['utm_source']) ? sanitize_text_field($_GET['utm_source']) : null;
        $utm_medium = isset($_GET['utm_medium']) ? sanitize_text_field($_GET['utm_medium']) : null;
        $utm_campaign = isset($_GET['utm_campaign']) ? sanitize_text_field($_GET['utm_campaign']) : null;
        
        return array(
            'ip' => $ip,
            'user_agent' => $user_agent,
            'device_type' => $device_type,
            'browser' => $browser,
            'location' => $location_data['location'],
            'country' => $location_data['country'],
            'city' => $location_data['city'],
            'referrer' => $_SERVER['HTTP_REFERER'] ?? '',
            'page_url' => $_SERVER['REQUEST_URI'] ?? '',
            'utm_source' => $utm_source,
            'utm_medium' => $utm_medium,
            'utm_campaign' => $utm_campaign
        );
    }
    
    /**
     * Get user IP address
     */
    private function get_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        }
    }
    
    /**
     * Get location from IP
     */
    private function get_location_from_ip($ip) {
        // Default Bangladesh for local IPs
        if ($ip === '127.0.0.1' || strpos($ip, '192.168.') === 0) {
            return array(
                'location' => 'Dhaka, Bangladesh',
                'country' => 'Bangladesh',
                'city' => 'Dhaka'
            );
        }
        
        // For production, integrate with IP geolocation service
        // For now, return default
        return array(
            'location' => 'Bangladesh',
            'country' => 'Bangladesh',
            'city' => 'Unknown'
        );
    }
    
    /**
     * Process incoming message
     */
    public function process_message($message, $session_id = null) {
        if ($session_id) {
            $this->session_id = $session_id;
        }
        
        if (!$this->session_id) {
            return array(
                'success' => false,
                'message' => 'Session not found'
            );
        }
        
        // Store user message
        $this->store_message('user', $message);
        
        // Detect intent and analyze message
        $intent_data = $this->analyze_message($message);
        
        // Generate response
        $response = $this->generate_response($message, $intent_data);
        
        // Store bot response
        $this->store_message('bot', $response['message'], array(
            'intent' => $intent_data['intent'],
            'confidence' => $intent_data['confidence'],
            'quick_replies' => $response['quick_replies'] ?? null,
            'show_lead_form' => $response['show_lead_form'] ?? false
        ));
        
        // Update lead score
        $this->update_lead_score($intent_data);
        
        // Check for lead capture opportunity
        if ($this->should_capture_lead($intent_data)) {
            $response['show_lead_form'] = true;
        }
        
        return array(
            'success' => true,
            'data' => $response
        );
    }
    
    /**
     * Analyze message for intent and entities
     */
    private function analyze_message($message) {
        $message_lower = mb_strtolower($message, 'UTF-8');
        
        // Intent patterns for Banglay IELTS
        $intent_patterns = array(
            'course_inquiry' => array(
                'patterns' => ['কোর্স', 'ক্লাস', 'course', 'class', 'ব্যাচ', 'batch'],
                'confidence' => 0.8
            ),
            'course_fee' => array(
                'patterns' => ['ফি', 'fee', 'দাম', 'price', 'cost', 'টাকা', 'টাক', 'কত'],
                'confidence' => 0.9
            ),
            'course_duration' => array(
                'patterns' => ['কতদিন', 'duration', 'কত মাস', 'কত সপ্তাহ', 'সময়', 'time'],
                'confidence' => 0.8
            ),
            'branch_location' => array(
                'patterns' => ['ঠিকানা', 'location', 'address', 'কোথায়', 'শাখা', 'branch'],
                'confidence' => 0.9
            ),
            'contact_info' => array(
                'patterns' => ['ফোন', 'phone', 'নম্বর', 'number', 'contact', 'যোগাযোগ'],
                'confidence' => 0.9
            ),
            'ielts_general' => array(
                'patterns' => ['ielts', 'আইএলটিএস', 'speaking', 'writing', 'reading', 'listening'],
                'confidence' => 0.7
            ),
            'study_abroad' => array(
                'patterns' => ['বিদেশ', 'abroad', 'study abroad', 'scholarship', 'visa', 'immigration'],
                'confidence' => 0.8
            ),
            'greeting' => array(
                'patterns' => ['হাই', 'হ্যালো', 'আসসালামু', 'hello', 'hi', 'সালাম'],
                'confidence' => 0.9
            ),
            'admission' => array(
                'patterns' => ['ভর্তি', 'admission', 'enroll', 'registration', 'রেজিস্ট্রেশন'],
                'confidence' => 0.8
            ),
            'schedule' => array(
                'patterns' => ['সময়সূচী', 'schedule', 'routine', 'টাইম টেবিল', 'timing'],
                'confidence' => 0.8
            ),
            'online_course' => array(
                'patterns' => ['অনলাইন', 'online', 'ভার্চুয়াল', 'virtual', 'zoom'],
                'confidence' => 0.8
            )
        );
        
        $detected_intent = 'general';
        $max_confidence = 0;
        
        foreach ($intent_patterns as $intent => $data) {
            $pattern_matches = 0;
            foreach ($data['patterns'] as $pattern) {
                if (strpos($message_lower, $pattern) !== false) {
                    $pattern_matches++;
                }
            }
            
            if ($pattern_matches > 0) {
                $confidence = ($pattern_matches / count($data['patterns'])) * $data['confidence'];
                if ($confidence > $max_confidence) {
                    $max_confidence = $confidence;
                    $detected_intent = $intent;
                }
            }
        }
        
        // Extract entities (course names, numbers, etc.)
        $entities = $this->extract_entities($message);
        
        return array(
            'intent' => $detected_intent,
            'confidence' => $max_confidence,
            'entities' => $entities,
            'original_message' => $message
        );
    }
    
    /**
     * Extract entities from message
     */
    private function extract_entities($message) {
        $entities = array();
        $message_lower = mb_strtolower($message, 'UTF-8');
        
        // Course types
        $course_patterns = array(
            'comprehensive' => ['comprehensive', 'কমপ্রিহেনসিভ', 'সম্পূর্ণ'],
            'focus' => ['focus', 'ফোকাস'],
            'crash' => ['crash', 'ক্র্যাশ', 'দ্রুত'],
            'online' => ['online', 'অনলাইন']
        );
        
        foreach ($course_patterns as $course => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($message_lower, $pattern) !== false) {
                    $entities['course_type'] = $course;
                    break 2;
                }
            }
        }
        
        // Extract numbers (for fees, duration, etc.)
        if (preg_match_all('/\d+/', $message, $matches)) {
            $entities['numbers'] = $matches[0];
        }
        
        // Extract phone numbers
        if (preg_match('/(\+880|880|01)\s?[0-9\s-]{9,}/', $message, $matches)) {
            $entities['phone'] = $matches[0];
        }
        
        // Extract email
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $message, $matches)) {
            $entities['email'] = $matches[0];
        }
        
        return $entities;
    }
    
    /**
     * Generate response based on intent
     */
    private function generate_response($message, $intent_data) {
        $intent = $intent_data['intent'];
        $entities = $intent_data['entities'];
        
        // Get response from training data
        $training_response = $this->get_training_response($intent, $entities);
        
        if ($training_response) {
            return $training_response;
        }
        
        // Fallback to predefined responses
        return $this->get_predefined_response($intent, $entities);
    }
    
    /**
     * Get response from training data
     */
    private function get_training_response($intent, $entities) {
        if (!isset($this->training_data[$intent])) {
            return null;
        }
        
        $responses = $this->training_data[$intent];
        
        // Filter responses based on entities
        $filtered_responses = array();
        
        foreach ($responses as $response) {
            $match_score = 0;
            
            // Check entity matches
            if (isset($response['entities'])) {
                foreach ($response['entities'] as $entity_type => $entity_values) {
                    if (isset($entities[$entity_type]) && in_array($entities[$entity_type], $entity_values)) {
                        $match_score++;
                    }
                }
            }
            
            $response['match_score'] = $match_score;
            $filtered_responses[] = $response;
        }
        
        // Sort by match score and get best response
        usort($filtered_responses, function($a, $b) {
            return $b['match_score'] - $a['match_score'];
        });
        
        return $filtered_responses[0] ?? null;
    }
    
    /**
     * Get predefined response
     */
    private function get_predefined_response($intent, $entities) {
        $responses = array(
            'greeting' => array(
                'message' => 'আস্সালামু আলাইকুম! 🌟 আমি Banglay IELTS এর AI সহায়ক। IELTS সম্পর্কে কিছু জানতে চান?',
                'quick_replies' => ['কোর্স সম্পর্কে জানতে চাই', 'ফি জানতে চাই', 'ঠিকানা চাই']
            ),
            'course_inquiry' => array(
                'message' => '📚 Banglay IELTS এ আমাদের ৪টি কোর্স আছে:\n\n🎯 **IELTS Comprehensive** (4.5 months) - Beginner level\n🎯 **IELTS Focus** (3 months) - Intermediate level  \n🎯 **IELTS Crash** (1.5 months) - Intensive\n🎯 **Online Course** (2 months) - Live classes\n\nকোনটি সম্পর্কে বিস্তারিত জানতে চান?',
                'quick_replies' => ['Comprehensive Course', 'Focus Course', 'Crash Course', 'Online Course']
            ),
            'course_fee' => array(
                'message' => '💰 কোর্স ফি জানতে সরাসরি কল করুন:\n📞 **+880 961 382 0821**\n\nঅথবা আমাদের ওয়েবসাইট দেখুন: admission.banglayelts.com\n\nকোন কোর্সের ফি জানতে চান?',
                'quick_replies' => ['Comprehensive ফি', 'Focus ফি', 'Crash ফি', 'Online ফি'],
                'show_lead_form' => true
            ),
            'branch_location' => array(
                'message' => '📍 **Banglay IELTS এর শাখাসমূহ:**\n\n🏢 **ঢাকা (প্রধান):** Rahman Heights, Uttara\n🏢 **চট্টগ্রাম:** GEC Circle area\n🏢 **সিলেট:** Ambarkhana area\n\n📞 হটলাইন: +880 961 382 0821',
                'quick_replies' => ['ঢাকা শাখা', 'চট্টগ্রাম শাখা', 'সিলেট শাখা']
            ),
            'contact_info' => array(
                'message' => '📞 **যোগাযোগ করুন:**\n\n🔥 **হটলাইন:** +880 961 382 0821\n📧 **ইমেইল:** info@biic.com.bd\n🌐 **ওয়েবসাইট:** banglayelts.com\n\n⏰ **সময়:** দুপুর ১০টা - সন্ধ্যা ৬টা',
                'quick_replies' => ['এখনই কল করুন', 'ভিজিট করব', 'অনলাইনে আবেদন']
            ),
            'study_abroad' => array(
                'message' => '🌍 **Study Abroad Services:**\n\n✈️ UK, USA, Canada, Australia\n🎓 University selection & application\n📋 Visa processing (95% success rate)\n💰 Scholarship guidance\n\n📞 বিস্তারিত জানতে: +880 961 382 0821',
                'quick_replies' => ['UK এ পড়তে চাই', 'Canada এ পড়তে চাই', 'Scholarship চাই']
            ),
            'admission' => array(
                'message' => '📝 **ভর্তি প্রক্রিয়া:**\n\n1️⃣ ফ্রি কনসালটেশন নিন\n2️⃣ কোর্স নির্বাচন করুন\n3️⃣ Admission form পূরণ করুন\n4️⃣ ফি প্রদান করুন\n\n🌐 **অনলাইন আবেদন:** admission.banglayelts.com',
                'quick_replies' => ['ফ্রি কনসালটেশন', 'অনলাইন আবেদন', 'অফিস ভিজিট'],
                'show_lead_form' => true
            ),
            'general' => array(
                'message' => 'আপনার প্রশ্নটি সম্পর্কে বিস্তারিত জানতে আমাদের এক্সপার্ট কাউন্সেলরের সাথে কথা বলুন।\n\n📞 **এখনই কল করুন:** +880 961 382 0821\n\nঅথবা আরো কিছু জানতে চান?',
                'quick_replies' => ['কোর্স সম্পর্কে', 'ফি সম্পর্কে', 'ঠিকানা চাই', 'কল করব']
            )
        );
        
        return $responses[$intent] ?? $responses['general'];
    }
    
    /**
     * Store message in database
     */
    private function store_message($type, $content, $metadata = array()) {
        $database = BIIC()->database;
        
        $message_data = array(
            'session_id' => $this->session_id,
            'message_type' => $type,
            'content' => $content,
            'detected_intent' => $metadata['intent'] ?? null,
            'intent_confidence' => $metadata['confidence'] ?? null,
            'metadata' => !empty($metadata) ? json_encode($metadata) : null
        );
        
        return $database->insert_chat_message($message_data);
    }
    
    /**
     * Update lead score based on interaction
     */
    private function update_lead_score($intent_data) {
        $intent = $intent_data['intent'];
        $confidence = $intent_data['confidence'];
        
        // Score mapping for different intents
        $score_mapping = array(
            'course_fee' => 25,
            'admission' => 30,
            'contact_info' => 20,
            'course_inquiry' => 15,
            'study_abroad' => 20,
            'schedule' => 10,
            'branch_location' => 10,
            'greeting' => 5,
            'general' => 2
        );
        
        $score_increment = isset($score_mapping[$intent]) ? $score_mapping[$intent] : 1;
        
        // Adjust by confidence
        $score_increment = round($score_increment * $confidence);
        
        // Update session lead score
        if ($this->session_data) {
            global $wpdb;
            $database = BIIC()->database;
            
            $new_score = min(100, ($this->session_data->lead_score ?? 0) + $score_increment);
            
            $wpdb->update(
                $database->get_table('chat_sessions'),
                array('lead_score' => $new_score),
                array('session_id' => $this->session_id),
                array('%d'),
                array('%s')
            );
            
            // Update lead status based on score
            $lead_status = 'cold';
            if ($new_score >= 80) {
                $lead_status = 'hot';
            } elseif ($new_score >= 50) {
                $lead_status = 'warm';
            }
            
            $wpdb->update(
                $database->get_table('chat_sessions'),
                array('lead_status' => $lead_status),
                array('session_id' => $this->session_id),
                array('%s'),
                array('%s')
            );
        }
    }
    
    /**
     * Check if should capture lead
     */
    private function should_capture_lead($intent_data) {
        $intent = $intent_data['intent'];
        $confidence = $intent_data['confidence'];
        
        // High-intent actions that should trigger lead capture
        $high_intent_actions = array(
            'course_fee',
            'admission', 
            'contact_info',
            'study_abroad'
        );
        
        // Check if session already has lead
        global $wpdb;
        $database = BIIC()->database;
        
        $existing_lead = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$database->get_table('leads')} WHERE session_id = %s",
            $this->session_id
        ));
        
        if ($existing_lead > 0) {
            return false; // Already captured
        }
        
        // Check message count (capture after 3+ high-intent messages)
        $message_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$database->get_table('chat_messages')} WHERE session_id = %s",
            $this->session_id
        ));
        
        return (in_array($intent, $high_intent_actions) && $confidence > 0.7) || $message_count >= 5;
    }
    
    /**
     * Load training data
     */
    private function load_training_data() {
        // This would typically load from a file or database
        // For now, using the comprehensive training data we created earlier
        
        $this->training_data = array(
            'course_inquiry' => array(
                array(
                    'message' => '📚 **Banglay IELTS কোর্সসমূহ:**\n\n🎯 **IELTS Comprehensive** (4.5 months, 50+ classes)\n→ Beginner level, complete preparation\n\n🎯 **IELTS Focus** (3 months, 30+ classes)\n→ Intermediate level, band 6.0-7.0 target\n\n🎯 **IELTS Crash** (1.5 months, 30+ classes)\n→ Intensive, quick preparation\n\n🎯 **Online Course** (2 months, weekly 3/4 classes)\n→ Live interactive sessions\n\nকোনটি সম্পর্কে বিস্তারিত জানতে চান?',
                    'quick_replies' => ['Comprehensive', 'Focus', 'Crash', 'Online'],
                    'entities' => array()
                )
            ),
            'course_fee' => array(
                array(
                    'message' => '💰 **কোর্স ফি জানতে:**\n\n📞 **Direct Call:** +880 961 382 0821\n🌐 **Online:** admission.banglayelts.com\n\n💡 **বিশেষ সুবিধা:**\n✅ No extra charge for materials\n✅ Mock tests included\n✅ 1 year validity (online)\n\nএখনই ফ্রি কনসালটেশন নিন!',
                    'quick_replies' => ['এখনই কল করুন', 'ফ্রি কনসালটেশন', 'Visit Office'],
                    'show_lead_form' => true
                )
            )
        );
    }
    
    /**
     * Get session ID
     */
    public function get_session_id() {
        return $this->session_id;
    }
    
    /**
     * Get session data
     */
    public function get_session_data() {
        return $this->session_data;
    }
}