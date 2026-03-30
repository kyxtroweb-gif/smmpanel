<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Category;
use App\Models\Service;
use App\Models\Provider;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\TicketReply;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== ADMIN USER =====
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@kyxtro.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'balance' => 1000,
            'total_deposited' => 1000,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        UserProfile::create([
            'user_id' => $admin->id,
            'timezone' => 'UTC',
            'language' => 'en',
        ]);

        // ===== DEMO USER =====
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'user@example.com',
            'password' => Hash::make('user123'),
            'role' => 'user',
            'balance' => 50,
            'total_deposited' => 50,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        UserProfile::create([
            'user_id' => $user->id,
            'timezone' => 'UTC',
            'language' => 'en',
        ]);

        // ===== PAYMENT METHODS (Perfect Panel style) =====
        $paymentMethods = [
            [
                'name' => 'PayTM / UPI QR',
                'slug' => 'paytm-upi',
                'type' => 'manual',
                'description' => 'Scan QR code with any UPI app (PayTM, PhonePe, GPay, etc.)',
                'is_automatic' => false,
                'requires_admin_approval' => true,
                'min_amount' => 10,
                'max_amount' => 50000,
                'bonus_percent' => 0,
                'instructions' => "1. Open any UPI app (PayTM, PhonePe, Google Pay)\n2. Scan the QR code shown\n3. Enter the EXACT amount\n4. Complete payment\n5. Come back here and submit your Transaction ID + paid amount",
                'fields' => json_encode([
                    ['name' => 'user_txn_id', 'label' => 'Transaction ID', 'type' => 'text', 'required' => true, 'placeholder' => 'Enter your UPI Transaction ID'],
                    ['name' => 'user_amount', 'label' => 'Amount Paid', 'type' => 'number', 'required' => true, 'placeholder' => 'Enter exact amount paid'],
                ]),
                'sort_order' => 1,
            ],
            [
                'name' => 'Google Pay',
                'slug' => 'gpay',
                'type' => 'manual',
                'description' => 'Send payment via Google Pay',
                'is_automatic' => false,
                'requires_admin_approval' => true,
                'min_amount' => 10,
                'max_amount' => 50000,
                'bonus_percent' => 0,
                'instructions' => "1. Open Google Pay\n2. Send to the UPI ID shown\n3. Enter the EXACT amount\n4. Submit your Transaction ID + paid amount below",
                'fields' => json_encode([
                    ['name' => 'user_txn_id', 'label' => 'Transaction ID', 'type' => 'text', 'required' => true, 'placeholder' => 'Enter your GPay Transaction ID'],
                    ['name' => 'user_amount', 'label' => 'Amount Paid', 'type' => 'number', 'required' => true, 'placeholder' => 'Enter exact amount'],
                ]),
                'sort_order' => 2,
            ],
            [
                'name' => 'PhonePe',
                'slug' => 'phonepe',
                'type' => 'manual',
                'description' => 'Pay via PhonePe app',
                'is_automatic' => false,
                'requires_admin_approval' => true,
                'min_amount' => 10,
                'max_amount' => 50000,
                'bonus_percent' => 0,
                'instructions' => "1. Open PhonePe\n2. Use the QR code or UPI ID to pay\n3. Submit your Transaction ID and exact amount",
                'fields' => json_encode([
                    ['name' => 'user_txn_id', 'label' => 'Transaction ID', 'type' => 'text', 'required' => true],
                    ['name' => 'user_amount', 'label' => 'Amount Paid', 'type' => 'number', 'required' => true],
                ]),
                'sort_order' => 3,
            ],
            [
                'name' => 'Bank Transfer',
                'slug' => 'bank-transfer',
                'type' => 'manual',
                'description' => 'Direct bank transfer (IMPS/NEFT/RTGS)',
                'is_automatic' => false,
                'requires_admin_approval' => true,
                'min_amount' => 100,
                'max_amount' => 100000,
                'bonus_percent' => 0,
                'instructions' => "Account Name: KYXTRO SOLUTIONS\nAccount Number: XXXXXXXXXX\nIFSC Code: XXXXXXXXXX\nBank: XYZ Bank\n\nAfter transferring, submit your UTR/Transaction Number + amount.",
                'fields' => json_encode([
                    ['name' => 'user_txn_id', 'label' => 'UTR / Reference Number', 'type' => 'text', 'required' => true, 'placeholder' => 'Enter UTR number'],
                    ['name' => 'user_amount', 'label' => 'Amount Transferred', 'type' => 'number', 'required' => true],
                ]),
                'sort_order' => 4,
            ],
            [
                'name' => 'Stripe (Card Payment)',
                'slug' => 'stripe',
                'type' => 'automatic',
                'description' => 'Pay with Credit/Debit Card via Stripe',
                'is_automatic' => true,
                'requires_admin_approval' => false,
                'min_amount' => 5,
                'max_amount' => 10000,
                'bonus_percent' => 0,
                'fields' => json_encode([]),
                'credentials' => json_encode(['publishable_key' => '', 'secret_key' => '']),
                'sort_order' => 10,
            ],
            [
                'name' => 'Crypto (USDT/TRC20)',
                'slug' => 'crypto-usdt',
                'type' => 'automatic',
                'description' => 'Pay with USDT on TRC20 network',
                'is_automatic' => true,
                'requires_admin_approval' => false,
                'min_amount' => 5,
                'max_amount' => 100000,
                'bonus_percent' => 5,
                'bonus_threshold' => 50,
                'instructions' => 'Send USDT (TRC20) to the address shown after selecting this method.',
                'fields' => json_encode([]),
                'credentials' => json_encode(['wallet_address' => 'TXaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'currency' => 'USDT']),
                'sort_order' => 20,
            ],
        ];

        foreach ($paymentMethods as $pm) {
            PaymentMethod::create(array_merge($pm, ['is_active' => true]));
        }

        // ===== CATEGORIES =====
        $categories = [
            ['name' => 'Instagram Followers', 'slug' => 'instagram-followers', 'icon' => 'fab fa-instagram', 'sort_order' => 1],
            ['name' => 'Instagram Likes', 'slug' => 'instagram-likes', 'icon' => 'fab fa-instagram', 'sort_order' => 2],
            ['name' => 'Instagram Views', 'slug' => 'instagram-views', 'icon' => 'fab fa-instagram', 'sort_order' => 3],
            ['name' => 'Instagram Comments', 'slug' => 'instagram-comments', 'icon' => 'fab fa-instagram', 'sort_order' => 4],
            ['name' => 'TikTok Followers', 'slug' => 'tiktok-followers', 'icon' => 'fab fa-tiktok', 'sort_order' => 5],
            ['name' => 'TikTok Likes', 'slug' => 'tiktok-likes', 'icon' => 'fab fa-tiktok', 'sort_order' => 6],
            ['name' => 'TikTok Views', 'slug' => 'tiktok-views', 'icon' => 'fab fa-tiktok', 'sort_order' => 7],
            ['name' => 'YouTube Subscribers', 'slug' => 'youtube-subscribers', 'icon' => 'fab fa-youtube', 'sort_order' => 8],
            ['name' => 'YouTube Views', 'slug' => 'youtube-views', 'icon' => 'fab fa-youtube', 'sort_order' => 9],
            ['name' => 'YouTube Likes', 'slug' => 'youtube-likes', 'icon' => 'fab fa-youtube', 'sort_order' => 10],
            ['name' => 'Twitter/X Followers', 'slug' => 'twitter-followers', 'icon' => 'fab fa-x-twitter', 'sort_order' => 11],
            ['name' => 'Telegram Members', 'slug' => 'telegram-members', 'icon' => 'fab fa-telegram', 'sort_order' => 12],
            ['name' => 'Facebook Followers', 'slug' => 'facebook-followers', 'icon' => 'fab fa-facebook', 'sort_order' => 13],
            ['name' => 'Spotify Followers', 'slug' => 'spotify-followers', 'icon' => 'fab fa-spotify', 'sort_order' => 14],
        ];

        $categoryModels = [];
        foreach ($categories as $cat) {
            $categoryModels[$cat['slug']] = Category::create(array_merge($cat, ['is_active' => true]));
        }

        // ===== PROVIDER (demo) =====
        $provider = Provider::create([
            'name' => 'Demo Provider',
            'api_url' => 'https://api.example.com/v2',
            'api_key' => 'demo_api_key_replace_me',
            'is_active' => true,
            'balance' => 0,
        ]);

        // ===== SERVICES (demo) =====
        $services = [
            // Instagram Followers
            ['category' => 'instagram-followers', 'name' => 'Instagram Followers - Real Looking', 'price' => 1.20, 'cost' => 0.80, 'min' => 10, 'max' => 50000, 'avg' => '24 hours', 'refill' => true, 'cancel' => true],
            ['category' => 'instagram-followers', 'name' => 'Instagram Followers - Fast', 'price' => 0.90, 'cost' => 0.60, 'min' => 10, 'max' => 100000, 'avg' => '0-6 hours', 'refill' => false, 'cancel' => true],
            ['category' => 'instagram-followers', 'name' => 'Instagram Followers - Premium', 'price' => 2.50, 'cost' => 1.80, 'min' => 50, 'max' => 20000, 'avg' => '12-24 hours', 'refill' => true, 'cancel' => false],
            // Instagram Likes
            ['category' => 'instagram-likes', 'name' => 'Instagram Likes - Instant', 'price' => 0.15, 'cost' => 0.08, 'min' => 10, 'max' => 100000, 'avg' => '0-1 hour', 'refill' => true, 'cancel' => true],
            ['category' => 'instagram-likes', 'name' => 'Instagram Likes - Real', 'price' => 0.35, 'cost' => 0.20, 'min' => 50, 'max' => 50000, 'avg' => '0-12 hours', 'refill' => true, 'cancel' => false],
            ['category' => 'instagram-likes', 'name' => 'Instagram Likes - Female', 'price' => 0.45, 'cost' => 0.28, 'min' => 100, 'max' => 30000, 'avg' => '12 hours', 'refill' => false, 'cancel' => false],
            // Instagram Views
            ['category' => 'instagram-views', 'name' => 'Instagram Video Views', 'price' => 0.05, 'cost' => 0.02, 'min' => 100, 'max' => 10000000, 'avg' => '0-6 hours', 'refill' => false, 'cancel' => true],
            ['category' => 'instagram-views', 'name' => 'Instagram Reel Views', 'price' => 0.08, 'cost' => 0.04, 'min' => 100, 'max' => 5000000, 'avg' => '0-3 hours', 'refill' => false, 'cancel' => true],
            // Instagram Comments
            ['category' => 'instagram-comments', 'name' => 'Instagram Random Comments', 'price' => 3.00, 'cost' => 2.00, 'min' => 10, 'max' => 10000, 'avg' => '24 hours', 'refill' => false, 'cancel' => false],
            ['category' => 'instagram-comments', 'name' => 'Instagram Custom Comments', 'price' => 5.00, 'cost' => 3.50, 'min' => 5, 'max' => 5000, 'avg' => '24 hours', 'refill' => false, 'cancel' => false],
            // TikTok
            ['category' => 'tiktok-followers', 'name' => 'TikTok Followers', 'price' => 1.50, 'cost' => 1.00, 'min' => 10, 'max' => 50000, 'avg' => '12 hours', 'refill' => true, 'cancel' => true],
            ['category' => 'tiktok-likes', 'name' => 'TikTok Likes', 'price' => 0.20, 'cost' => 0.12, 'min' => 10, 'max' => 100000, 'avg' => '0-6 hours', 'refill' => true, 'cancel' => true],
            ['category' => 'tiktok-views', 'name' => 'TikTok Views', 'price' => 0.03, 'cost' => 0.015, 'min' => 100, 'max' => 10000000, 'avg' => '0-1 hour', 'refill' => false, 'cancel' => true],
            // YouTube
            ['category' => 'youtube-subscribers', 'name' => 'YouTube Subscribers', 'price' => 4.00, 'cost' => 2.80, 'min' => 10, 'max' => 10000, 'avg' => '24-48 hours', 'refill' => true, 'cancel' => false],
            ['category' => 'youtube-views', 'name' => 'YouTube Views - Retention 1min', 'price' => 0.50, 'cost' => 0.30, 'min' => 100, 'max' => 1000000, 'avg' => '24 hours', 'refill' => true, 'cancel' => true],
            ['category' => 'youtube-likes', 'name' => 'YouTube Likes', 'price' => 1.00, 'cost' => 0.60, 'min' => 10, 'max' => 50000, 'avg' => '12 hours', 'refill' => true, 'cancel' => true],
            // Twitter
            ['category' => 'twitter-followers', 'name' => 'Twitter Followers', 'price' => 1.80, 'cost' => 1.20, 'min' => 10, 'max' => 50000, 'avg' => '24 hours', 'refill' => true, 'cancel' => true],
            // Telegram
            ['category' => 'telegram-members', 'name' => 'Telegram Channel Members', 'price' => 1.20, 'cost' => 0.80, 'min' => 10, 'max' => 50000, 'avg' => '12-24 hours', 'refill' => true, 'cancel' => false],
            ['category' => 'telegram-members', 'name' => 'Telegram Group Members', 'price' => 1.50, 'cost' => 1.00, 'min' => 10, 'max' => 20000, 'avg' => '24 hours', 'refill' => true, 'cancel' => false],
            // Facebook
            ['category' => 'facebook-followers', 'name' => 'Facebook Profile Followers', 'price' => 1.50, 'cost' => 1.00, 'min' => 10, 'max' => 50000, 'avg' => '24 hours', 'refill' => true, 'cancel' => false],
            // Spotify
            ['category' => 'spotify-followers', 'name' => 'Spotify Followers', 'price' => 1.80, 'cost' => 1.20, 'min' => 10, 'max' => 50000, 'avg' => '12 hours', 'refill' => true, 'cancel' => false],
        ];

        foreach ($services as $svc) {
            $cat = $categoryModels[$svc['category']];
            Service::create([
                'category_id' => $cat->id,
                'provider_id' => $provider->id,
                'name' => $svc['name'],
                'description' => "High quality {$svc['name']}. Fast delivery, real-looking engagement.",
                'provider_service_id' => 'PROV-' . strtoupper(Str::random(6)),
                'price_per_1k' => $svc['price'],
                'cost_per_1k' => $svc['cost'],
                'min_order' => $svc['min'],
                'max_order' => $svc['max'],
                'average_time' => $svc['avg'],
                'dripfeed' => $svc['min'] >= 1000,
                'refill' => $svc['refill'],
                'cancel' => $svc['cancel'],
                'is_active' => true,
                'is_featured' => $svc['price'] < 1.00,
            ]);
        }

        // ===== SITE SETTINGS =====
        $settings = [
            'site_name' => 'KYXTRO SMM',
            'site_tagline' => 'Best SMM Panel for Social Media Growth',
            'contact_email' => 'support@kyxtro.com',
            'maintenance_mode' => '0',
            'currency' => 'USD',
            'currency_symbol' => '$',
            'timezone' => 'UTC',
            'min_order_value' => '0.01',
            'refill_days' => '365',
            'seo_title' => 'KYXTRO SMM - Buy Social Media Services',
            'seo_description' => 'The best SMM panel for Instagram, TikTok, YouTube, Twitter followers, likes, views and more. Fast delivery, affordable prices.',
            'seo_keywords' => 'SMM panel, buy followers, buy likes, social media marketing',
        ];

        foreach ($settings as $key => $value) {
            Setting::create([
                'key' => $key,
                'value' => $value,
                'type' => is_numeric($value) ? 'int' : 'string',
            ]);
        }

        // ===== SAVED TICKET REPLIES =====
        $replies = [
            ['title' => 'Order Not Delivered', 'message' => 'Thank you for contacting us. We have checked your order and found that it is still in progress. Please wait for the delivery to complete. Most orders complete within the stated time. If it has exceeded the average time, please provide your Order ID.'],
            ['title' => 'Refund Policy', 'message' => 'Thank you for reaching out. Refunds are processed for cancelled orders and failed deliveries. Please note that we can only refund orders that have not been delivered. Please provide your Order ID and we will review it within 24 hours.'],
            ['title' => 'Payment Not Credited', 'message' => 'We apologize for the inconvenience. Please submit your payment proof (screenshot/transaction ID) and we will process your deposit manually within 1-24 hours.'],
            ['title' => 'Service Unavailable', 'message' => 'We apologize, the service you ordered is currently unavailable. Our team is working to restore it. Please try again in a few hours or place a new order for a similar service.'],
        ];

        foreach ($replies as $reply) {
            \App\Models\TicketReply::create($reply);
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin login: admin@kyxtro.com / admin123');
        $this->command->info('Demo user: user@example.com / user123');
    }
}
