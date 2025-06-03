<?php
/**
 * Initial Data Migration
 * database/migrations/initial-data.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Insert initial configuration and training data
 */
function biic_insert_initial_configuration() {
    // Set default plugin options
    $default_options = array(
        'biic_chatbot_enabled' => true,
        'biic_auto_greeting' => true,
        'biic_welcome_message' => 'আস্সালামু আলাইকুম! 🌟 আমি Banglay IELTS এর AI সহায়ক। IELTS সম্পর্কে কিছু জানতে চান?',
        'biic_chat_position' => 'bottom-right',
        'biic_chat_theme' => 'modern',
        'biic_max_message_length' => 1000,
        'biic_typing_speed' => 50,
        'biic_enable_sounds' => true,
        'biic_enable_animations' => true,
        'biic_business_hours' => array(
            'start' => '10:00',
            'end' => '18:00',
            'days' => array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday')
        ),
        'biic_lead_notifications' => true,
        'biic_notification_email' => get_option('admin_email'),
        'biic_analytics_enabled' => true,
        'biic_data_retention_days' => 365,
        'biic_allow_file_upload' => false,
        'biic_timezone' => 'Asia/Dhaka',
        'biic_rate_limit' => 10,
        'biic_auto_responses' => true,
        'biic_lead_scoring_enabled' => true,
        'biic_webhook_notifications' => false,
        'biic_seo_enabled' => true,
        'biic_structured_data_enabled' => true,
        'biic_db_version' => '1.0.0'
    );
    
    foreach ($default_options as $option_name => $option_value) {
        add_option($option_name, $option_value);
    }
    
    return true;
}

/**
 * Insert comprehensive training data for the chatbot
 */
function biic_insert_comprehensive_training_data() {
    global $wpdb;
    $table_prefix = $wpdb->prefix . 'biic_';
    
    $training_data = array(
        // Greetings
        array(
            'intent' => 'greeting',
            'example_input' => 'আস্সালামু আলাইকুম',
            'expected_response' => 'ওয়ালাইকুম আস্সালাম! 🌟 আমি Banglay IELTS এর AI সহায়ক। IELTS সম্পর্কে কিছু জানতে চান?',
            'response_type' => 'text',
            'language' => 'bn'
        ),
        array(
            'intent' => 'greeting',
            'example_input' => 'হাই',
            'expected_response' => 'হ্যালো! 👋 Banglay IELTS তে স্বাগতম। আমি আপনার AI সহায়ক। কিভাবে সাহায্য করতে পারি?',
            'response_type' => 'text',
            'language' => 'bn'
        ),
        array(
            'intent' => 'greeting',
            'example_input' => 'Hello',
            'expected_response' => 'Hello! Welcome to Banglay IELTS. I\'m your AI assistant. How can I help you today?',
            'response_type' => 'text',
            'language' => 'en'
        ),
        
        // Course Inquiries
        array(
            'intent' => 'course_inquiry',
            'example_input' => 'IELTS কোর্স সম্পর্কে জানতে চাই',
            'expected_response' => '📚 **Banglay IELTS কোর্সসমূহ:**\n\n🎯 **IELTS Comprehensive** (4.5 months, 50+ classes) - Beginner level\n🎯 **IELTS Focus** (3 months, 30+ classes) - Intermediate level\n🎯 **IELTS Crash** (1.5 months, 30+ classes) - Intensive\n🎯 **Online Course** (2 months, weekly 3/4 classes) - Live sessions\n\nকোনটি সম্পর্কে বিস্তারিত জানতে চান?',
            'response_type' => 'text_with_options',
            'language' => 'bn',
            'entities' => json_encode(array('course_types' => array('comprehensive', 'focus', 'crash', 'online')))
        ),
        array(
            'intent' => 'course_inquiry',
            'example_input' => 'কোন কোর্স আছে',
            'expected_response' => 'আমাদের ৪টি প্রধান IELTS কোর্স রয়েছে:\n\n1️⃣ **Comprehensive Course** - নতুনদের জন্য সম্পূর্ণ প্রস্তুতি\n2️⃣ **Focus Course** - মধ্যম স্তরের জন্য\n3️⃣ **Crash Course** - দ্রুত প্রস্তুতি\n4️⃣ **Online Course** - লাইভ অনলাইন ক্লাস\n\nকোনটি নিয়ে আরো জানতে চান?',
            'response_type' => 'text',
            'language' => 'bn'
        ),
        
        // Course Fees
        array(
            'intent' => 'course_fee',
            'example_input' => 'কোর্স ফি কত',
            'expected_response' => '💰 **কোর্স ফি জানতে:**\n\n📞 **Direct Call:** +880 961 382 0821 (10 AM - 6 PM)\n🌐 **Online:** admission.banglayelts.com\n\n💡 **বিশেষ সুবিধা:**\n✅ কোর্স ম্যাটেরিয়াল ফ্রি\n✅ Mock tests included\n✅ Expert teacher feedback\n\nএখনই ফ্রি কনসালটেশন নিন! 🎯',
            'response_type' => 'text_with_actions',
            'language' => 'bn'
        ),
        array(
            'intent' => 'course_fee',
            'example_input' => 'ফি কেমন',
            'expected_response' => 'কোর্স ফি সম্পর্কে বিস্তারিত জানতে আমাদের Admission Office এ কল করুন:\n\n📞 **+880 961 382 0821**\n⏰ **সময়:** সকাল ১০টা - সন্ধ্যা ৬টা\n\nঅথবা অনলাইনে আবেদন করুন: admission.banglayelts.com',
            'response_type' => 'text',
            'language' => 'bn'
        ),
        
        // Contact Information
        array(
            'intent' => 'contact_info',
            'example_input' => 'ফোন নম্বর দিন',
            'expected_response' => '📞 **যোগাযোগ করুন:**\n\n🔥 **হটলাইন:** +880 961 382 0821\n📧 **ইমেইল:** info@biic.com.bd\n🌐 **ওয়েবসাইট:** banglayelts.com\n📍 **ঠিকানা:** Rahman Heights, Uttara, Dhaka\n\n⏰ **সময়:** সকাল ১০টা - সন্ধ্যা ৬টা (রবি-শুক্র)',
            'response_type' => 'contact_card',
            'language' => 'bn'
        ),
        array(
            'intent' => 'contact_info',
            'example_input' => 'যোগাযোগ',
            'expected_response' => '☎️ **তাৎক্ষণিক যোগাযোগ:**\n\n📱 **Mobile:** +880 961 382 0821\n💬 **WhatsApp:** +880 961 382 0821\n📧 **Email:** info@biic.com.bd\n\n🏢 **Office:** Rahman Heights, Plot-01, Uttara, Dhaka\n🕙 **অফিস সময়:** ১০:০০ AM - ৬:০০ PM',
            'response_type' => 'text',
            'language' => 'bn'
        ),
        
        // Branch Locations
        array(
            'intent' => 'branch_location',
            'example_input' => 'ঠিকানা চাই',
            'expected_response' => '📍 **Banglay IELTS এর শাখাসমূহ:**\n\n🏢 **ঢাকা (প্রধান কার্যালয়):**\nRahman Heights, Plot-01, Uttara\n(IELTS Test Center + Training)\n\n🏢 **চট্টগ্রাম শাখা:**\nGEC Circle area\n\n🏢 **সিলেট শাখা:**\nAmbarkhana area\n\n📞 **সকল শাখার হটলাইন:** +880 961 382 0821',
            'response_type' => 'location_card',
            'language' => 'bn'
        ),
        array(
            'intent' => 'branch_location',
            'example_input' => 'কোথায় অফিস',
            'expected_response' => '🗺️ **আমাদের অফিস অবস্থান:**\n\n📍 **Main Campus:** Rahman Heights, Uttara, Dhaka\n📍 **Chattogram:** GEC Circle\n📍 **Sylhet:** Ambarkhana\n\nগুগল ম্যাপে "Banglay IELTS" লিখে খুঁজুন 🔍',
            'response_type' => 'text',
            'language' => 'bn'
        ),
        
        // Study Abroad
        array(
            'intent' => 'study_abroad',
            'example_input' => 'বিদেশে পড়তে চাই',
            'expected_response' => '🌍 **Study Abroad with BIIC:**\n\n✈️ **গন্তব্য:** UK, USA, Canada, Australia, Denmark, Finland\n🎓 **সেবা:** University selection, Application, Visa processing\n📈 **সাফল্যের হার:** 95% visa approval\n🏛️ **পার্টনার:** 300+ global universities\n💰 **Scholarship:** সম্পূর্ণ গাইডেন্স\n\n📞 **বিস্তারিত:** +880 961 382 0821',
            'response_type' => 'study_abroad_card',
            'language' => 'bn'
        ),
        array(
            'intent' => 'study_abroad',
            'example_input' => 'ভিসা সাপোর্ট',
            'expected_response' => '🛂 **ভিসা সাপোর্ট সেবা:**\n\n✅ Document preparation\n✅ Visa application guidance\n✅ Interview preparation\n✅ 95% success rate\n✅ Complete support until visa\n\nবিনামূল্যে কনসালটেশন নিন! 📞 +880 961 382 0821',
            'response_type' => 'text',
            'language' => 'bn'
        ),
        
        // Admission Process
        array(
            'intent' => 'admission',
            'example_input' => 'ভর্তি হতে চাই',
            'expected_response' => '📝 **ভর্তি প্রক্রিয়া:**\n\n1️⃣ **ফ্রি কনসালটেশন** নিন\n2️⃣ **Level Assessment** টেস্ট দিন\n3️⃣ **উপযুক্ত কোর্স** নির্বাচন করুন\n4️⃣ **Admission form** পূরণ করুন\n5️⃣ **ফি পেমেন্ট** সম্পন্ন করুন\n\n🌐 **অনলাইন আবেদন:** admission.banglayelts.com\n📞 **সরাসরি:** +880 961 382 0821',
            'response_type' => 'process_card',
            'language' => 'bn'
        ),
        array(
            'intent' => 'admission',
            'example_input' => 'কিভাবে ভর্তি হবো',
            'expected_response' => '🎯 **সহজ ভর্তি প্রক্রিয়া:**\n\n📞 প্রথমে কল করুন: +880 961 382 0821\n🏢 অফিস ভিজিট করুন বা\n💻 অনলাইনে আবেদন করুন\n\n**প্রয়োজনীয় কাগজপত্র:**\n• ছবি (পাসপোর্ট সাইজ)\n• NID/Birth Certificate\n• Academic certificates\n\nআজই শুরু করুন! 🚀',
            'response_type' => 'text',
            'language' => 'bn'
        ),
        
        // Course Duration
        array(
            'intent' => 'course_duration',
            'example_input' => 'কোর্স কতদিনের',
            'expected_response' => '⏰ **কোর্সের সময়কাল:**\n\n📚 **Comprehensive:** 4.5 মাস (50+ ক্লাস)\n🎯 **Focus:** 3 মাস (30+ ক্লাস)\n⚡ **Crash:** 1.5 মাস (30+ ক্লাস)\n💻 **Online:** 2 মাস (সপ্তাহে 3/4 ক্লাস)\n\n**বিশেষ সুবিধা:** \n• Class miss হলে makeup class\n• 1 বছর পর্যন্ত support\n• Unlimited mock tests',
            'response_type' => 'duration_card',
            'language' => 'bn'
        ),
        
        // Schedule Information
        array(
            'intent' => 'schedule',
            'example_input' => 'ক্লাস টাইম কি',
            'expected_response' => '🕐 **ক্লাসের সময়সূচী:**\n\n🌅 **Morning Batch:** 8:00 AM - 10:00 AM\n🌞 **Day Batch:** 10:30 AM - 12:30 PM\n🌆 **Evening Batch:** 5:00 PM - 7:00 PM\n🌙 **Night Batch:** 7:30 PM - 9:30 PM\n\n📅 **সপ্তাহে:** 4-5 দিন ক্লাস\n🎯 **Weekend Special:** শুক্র ও শনিবার\n\nআপনার সুবিধামত সময় বেছে নিন! ⏰',
            'response_type' => 'schedule_card',
            'language' => 'bn'
        ),
        
        // Online Course
        array(
            'intent' => 'online_course',
            'example_input' => 'অনলাইন কোর্স',
            'expected_response' => '💻 **অনলাইন কোর্সের বিশেষত্ব:**\n\n🔴 **Live Interactive Classes**\n📱 **Secret Mobile App** access\n📚 **Unlimited Cambridge Mock Tests**\n👨‍🏫 **Expert teachers থেকে feedback**\n⏰ **1 বছর validity**\n📞 **24/7 support**\n\n**ক্লাস সময়:** সপ্তাহে 3/4 দিন\n**Duration:** 2 মাস\n\nবিস্তারিত জানুন: +880 961 382 0821',
            'response_type' => 'online_course_card',
            'language' => 'bn'
        ),
        
        // IELTS General Information
        array(
            'intent' => 'ielts_general',
            'example_input' => 'IELTS কি',
            'expected_response' => '📖 **IELTS সম্পর্কে:**\n\n🎯 **IELTS** = International English Language Testing System\n🌍 **বিশ্বের** সবচেয়ে জনপ্রিয় English proficiency test\n📊 **4টি section:** Reading, Writing, Listening, Speaking\n⭐ **Band Score:** 0-9 scale\n\n**প্রয়োজন:**\n• Higher education abroad\n• Immigration\n• Professional registration\n• Job opportunities\n\nআমরা আছি আপনার সাফল্যের সাথী! 🤝',
            'response_type' => 'info_card',
            'language' => 'bn'
        ),
        
        // Test Registration
        array(
            'intent' => 'test_registration',
            'example_input' => 'পরীক্ষার রেজিস্ট্রেশন',
            'expected_response' => '📝 **IELTS Test Registration:**\n\n🏢 **Test Center:** Rahman Heights, Uttara\n🎤 **Speaking Test:** 3rd Floor\n📝 **LRW Test:** 4th Floor\n\n**Registration করুন:**\n• IDP Education\n• British Council\n\n**আমাদের সাহায্য:** Registration থেকে Result পর্যন্ত সম্পূর্ণ গাইডেন্স!\n\n📞 **তথ্যের জন্য:** +880 961 382 0821',
            'response_type' => 'registration_card',
            'language' => 'bn'
        ),
        
        // Thank you responses
        array(
            'intent' => 'thank_you',
            'example_input' => 'ধন্যবাদ',
            'expected_response' => 'আপনাকেও ধন্যবাদ! 😊 Banglay IELTS পরিবারে আপনাকে স্বাগতম। আরো কোন সাহায্য দরকার হলে জানাবেন। 🎯\n\n📞 **যেকোনো সময় কল করুন:** +880 961 382 0821\n🌟 **আমরা আছি আপনার IELTS সাফল্যের পথে!**',
            'response_type' => 'text',
            'language' => 'bn'
        ),
        
        // Goodbye responses
        array(
            'intent' => 'goodbye',
            'example_input' => 'বাই',
            'expected_response' => 'আল্লাহ হাফেজ! 👋 IELTS এর যাত্রায় আপনার সাফল্য কামনা করি। 🎯\n\n📞 যেকোনো প্রয়োজনে: +880 961 382 0821\n🌟 Banglay IELTS - আপনার স্বপ্নের সাথী!',
            'response_type' => 'text',
            'language' => 'bn'
        ),
        
        // Default/General responses
        array(
            'intent' => 'general',
            'example_input' => 'আরো জানতে চাই',
            'expected_response' => 'অবশ্যই! আমি আপনাকে সাহায্য করতে পেরে খুশি। 😊\n\nবিস্তারিত জানতে আমাদের Expert Counselor এর সাথে কথা বলুন:\n\n📞 **+880 961 382 0821**\n🌐 **Website:** banglayelts.com\n📧 **Email:** info@biic.com.bd\n\n💡 **মনে রাখবেন:** আমরা Bangladesh এর #1 IELTS Training Center! 🏆',
            'response_type' => 'text',
            'language' => 'bn'
        )
    );
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($training_data as $data) {
        $result = $wpdb->insert(
            $table_prefix . 'training_data',
            $data,
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result) {
            $success_count++;
        } else {
            $error_count++;
            error_log('BIIC: Failed to insert training data: ' . $wpdb->last_error);
        }
    }
    
    error_log("BIIC: Inserted {$success_count} training data entries, {$error_count} failures");
    
    return array(
        'success' => $success_count,
        'errors' => $error_count,
        'total' => count($training_data)
    );
}

/**
 * Insert default email templates
 */
function biic_insert_email_templates() {
    global $wpdb;
    $table_prefix = $wpdb->prefix . 'biic_';
    
    $templates = array(
        array(
            'template_name' => 'new_lead_notification',
            'template_subject' => '🎯 New Lead Alert - Banglay IELTS Chatbot',
            'template_body' => '<h2>🎯 New Lead Received!</h2>
<p>A new lead has been captured through the chatbot:</p>

<table style="border-collapse: collapse; width: 100%;">
<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Name:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{name}</td></tr>
<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Phone:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{phone}</td></tr>
<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Email:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{email}</td></tr>
<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Course Interest:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{course_interest}</td></tr>
<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Lead Score:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{lead_score}/100</td></tr>
<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Received:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{created_at}</td></tr>
</table>

<p><strong>📞 Take Action:</strong> Contact this lead within 24 hours for best conversion!</p>
<p><a href="' . admin_url('admin.php?page=biic-leads') . '" style="background: #E53E3E; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View in Dashboard</a></p>',
            'template_type' => 'lead_notification',
            'placeholders' => json_encode(['name', 'phone', 'email', 'course_interest', 'lead_score', 'created_at'])
        ),
        array(
            'template_name' => 'follow_up_reminder',
            'template_subject' => '📅 Follow-up Required: {name}',
            'template_body' => '<h2>📅 Follow-up Reminder</h2>
<p>It\'s time to follow up with this lead:</p>

<table style="border-collapse: collapse; width: 100%;">
<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Name:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{name}</td></tr>
<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Phone:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{phone}</td></tr>
<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Email:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{email}</td></tr>
<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Lead Score:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{lead_score}/100</td></tr>
<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Last Contact:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{last_contact_date}</td></tr>
</table>

<p><strong>📝 Notes:</strong> {follow_up_notes}</p>
<p><a href="tel:{phone}" style="background: #38A169; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">📞 Call Now</a></p>',
            'template_type' => 'follow_up',
            'placeholders' => json_encode(['name', 'phone', 'email', 'lead_score', 'last_contact_date', 'follow_up_notes'])
        ),
        array(
            'template_name' => 'lead_converted',
            'template_subject' => '🎉 Success! Lead Converted: {name}',
            'template_body' => '<h2>🎉 Congratulations! Lead Converted!</h2>
<p>Great news! A lead has been successfully converted:</p>

<table style="border-collapse: collapse; width: 100%;">
<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Student Name:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{name}</td></tr>
<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Phone:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{phone}</td></tr>
<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Course:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{course_interest}</td></tr>
<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Conversion Value:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">৳{conversion_value}</td></tr>
<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Converted Date:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{conversion_date}</td></tr>
</table>

<p>🎯 <strong>Keep up the great work!</strong></p>',
            'template_type' => 'conversion',
            'placeholders' => json_encode(['name', 'phone', 'course_interest', 'conversion_value', 'conversion_date'])
        ),
        array(
            'template_name' => 'welcome_lead',
            'template_subject' => '🌟 Welcome to Banglay IELTS Family!',
            'template_body' => '<h2>🌟 Welcome to Banglay IELTS!</h2>
<p>Dear {name},</p>
<p>Thank you for your interest in our IELTS courses! We\'re excited to help you achieve your IELTS goals.</p>

<h3>📚 What\'s Next?</h3>
<ul>
<li>Our counselor will contact you within 24 hours</li>
<li>Free placement test to assess your level</li>
<li>Personalized course recommendation</li>
<li>Course schedule and fee discussion</li>
</ul>

<h3>📞 Need Immediate Help?</h3>
<p>Call us at: <strong>+880 961 382 0821</strong><br>
Office Hours: 10:00 AM - 6:00 PM (Sun-Fri)</p>

<p>🎯 <em>Join thousands of successful students who achieved their IELTS goals with us!</em></p>

<p>Best regards,<br>
<strong>Banglay IELTS Team</strong></p>',
            'template_type' => 'welcome',
            'placeholders' => json_encode(['name'])
        ),
        array(
            'template_name' => 'nurture_sequence_1',
            'template_subject' => '🎯 Your IELTS Journey Starts Here!',
            'template_body' => '<h2>🎯 Ready to Start Your IELTS Journey?</h2>
<p>Hi {name},</p>
<p>We noticed you\'re interested in IELTS preparation. Here\'s how we can help you succeed:</p>

<h3>🏆 Why Choose Banglay IELTS?</h3>
<ul>
<li>573K+ YouTube Community</li>
<li>492K+ Facebook Family</li>
<li>Thousands of successful students</li>
<li>Expert instructors</li>
<li>Proven teaching methodology</li>
</ul>

<h3>🎁 Special Offer for You!</h3>
<p>Book a <strong>FREE consultation</strong> and get:</p>
<ul>
<li>Free IELTS level assessment</li>
<li>Personalized study plan</li>
<li>Course recommendation</li>
<li>Study materials</li>
</ul>

<p><a href="tel:+8809613820821" style="background: #E53E3E; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;">📞 Book Free Consultation</a></p>

<p>Best regards,<br>
<strong>Banglay IELTS Team</strong></p>',
            'template_type' => 'nurture',
            'placeholders' => json_encode(['name'])
        )
    );
    
    $success_count = 0;
    foreach ($templates as $template) {
        $result = $wpdb->insert(
            $table_prefix . 'email_templates',
            $template,
            array('%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result) {
            $success_count++;
        }
    }
    
    return $success_count;
}

/**
 * Create sample analytics data for dashboard
 */
function biic_create_sample_analytics() {
    global $wpdb;
    $table_prefix = $wpdb->prefix . 'biic_';
    
    $analytics_data = array();
    $current_date = current_time('Y-m-d');
    
    // Generate sample data for last 30 days
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        
        // Sample metrics
        $conversations = rand(5, 25);
        $messages = $conversations * rand(3, 8);
        $leads = rand(1, 5);
        $conversion_rate = $conversations > 0 ? ($leads / $conversations) * 100 : 0;
        
        $analytics_data[] = array(
            'date' => $date,
            'metric_name' => 'daily_conversations',
            'metric_value' => $conversations,
            'metric_type' => 'counter'
        );
        
        $analytics_data[] = array(
            'date' => $date,
            'metric_name' => 'daily_messages',
            'metric_value' => $messages,
            'metric_type' => 'counter'
        );
        
        $analytics_data[] = array(
            'date' => $date,
            'metric_name' => 'daily_leads',
            'metric_value' => $leads,
            'metric_type' => 'counter'
        );
        
        $analytics_data[] = array(
            'date' => $date,
            'metric_name' => 'conversion_rate',
            'metric_value' => $conversion_rate,
            'metric_type' => 'percentage'
        );
    }
    
    $success_count = 0;
    foreach ($analytics_data as $data) {
        $result = $wpdb->insert(
            $table_prefix . 'analytics',
            $data,
            array('%s', '%s', '%f', '%s')
        );
        
        if ($result) {
            $success_count++;
        }
    }
    
    return $success_count;
}

/**
 * Run all initial data migrations
 */
function biic_run_initial_data_migration() {
    $results = array();
    
    // Insert configuration
    $results['config'] = biic_insert_initial_configuration();
    
    // Insert training data
    $results['training'] = biic_insert_comprehensive_training_data();
    
    // Insert email templates
    $results['templates'] = biic_insert_email_templates();
    
    // Create sample analytics
    $results['analytics'] = biic_create_sample_analytics();
    
    // Mark migration as complete
    update_option('biic_initial_data_migrated', true);
    update_option('biic_migration_date', current_time('mysql'));
    
    return $results;
}