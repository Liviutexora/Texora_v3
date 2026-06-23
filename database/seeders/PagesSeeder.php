<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PagesSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title'            => 'Privacy Policy',
                'slug'             => 'privacy-policy',
                'meta_description' => 'How we collect, use, and protect your personal information.',
                'sort_order'       => 10,
                'content'          => <<<HTML
<h2>Information We Collect</h2>
<p>We collect information you provide when using our service, including your name, email address, and booking details necessary to confirm appointments.</p>
<h2>How We Use Your Information</h2>
<p>Your information is used to process bookings, send confirmations and reminders, improve our service, and communicate important updates.</p>
<h2>Data Sharing</h2>
<p>We do not sell or share your personal data with third parties except as required to deliver the service (for example, transactional email providers).</p>
<h2>Cookies</h2>
<p>We use essential cookies to keep the platform functioning. Optional cookies may be set by third-party features such as Google reCAPTCHA. You can manage cookie preferences at any time.</p>
<h2>Data Retention</h2>
<p>We retain personal data only as long as necessary to provide the service or as required by applicable law. You may request deletion at any time.</p>
<h2>Your Rights</h2>
<p>You have the right to access, correct, or delete your personal data. To exercise these rights, please contact us using our <a href="/contact">contact form</a>.</p>
<h2>Changes to This Policy</h2>
<p>We may update this policy from time to time. Material changes will be communicated via the platform or by email.</p>
HTML,
            ],
            [
                'title'            => 'Terms & Conditions',
                'slug'             => 'terms-and-conditions',
                'meta_description' => 'The terms governing your use of our booking platform.',
                'sort_order'       => 20,
                'content'          => <<<HTML
<h2>Acceptance of Terms</h2>
<p>By using this booking platform you agree to be bound by these Terms &amp; Conditions. If you do not agree, please do not use the service.</p>
<h2>Use of Service</h2>
<p>This platform is provided for scheduling appointments between clients and service providers. You agree to use it only for lawful purposes and in accordance with these terms.</p>
<h2>Booking Policies</h2>
<p>Bookings are subject to each business's availability. Cancellation and no-show policies are set independently by each service provider — please review them before confirming a booking.</p>
<h2>User Responsibilities</h2>
<p>You agree to provide accurate contact information when making a booking and to honour confirmed appointments or cancel within the business's stated cancellation window.</p>
<h2>Intellectual Property</h2>
<p>All content, trademarks, and software on this platform are owned by or licensed to us and may not be reproduced without prior written permission.</p>
<h2>Limitation of Liability</h2>
<p>To the fullest extent permitted by law, we are not liable for any loss or damages arising from missed or cancelled appointments, inaccurate information provided by third-party businesses, or interruptions to the service.</p>
<h2>Changes to Terms</h2>
<p>We reserve the right to update these terms at any time. Continued use of the service after changes are posted constitutes your acceptance of the revised terms.</p>
<h2>Contact</h2>
<p>Questions about these terms? Please reach us via our <a href="/contact">contact page</a>.</p>
HTML,
            ],
            [
                'title'            => 'Cookie Policy',
                'slug'             => 'cookie-policy',
                'meta_description' => 'How we use cookies and similar technologies on our platform.',
                'sort_order'       => 30,
                'content'          => <<<HTML
<h2>What Are Cookies?</h2>
<p>Cookies are small text files stored on your device when you visit a website. They help the site remember your preferences and function correctly.</p>
<h2>Cookies We Use</h2>
<p><strong>Strictly Necessary Cookies</strong> — These are required for the platform to function (for example, session cookies that keep you logged in). They cannot be disabled.</p>
<p><strong>Functional Cookies</strong> — These remember your preferences such as language and region to improve your experience.</p>
<p><strong>Third-Party Cookies</strong> — If optional features like Google reCAPTCHA are enabled, those services may set their own cookies governed by their own privacy policies.</p>
<h2>Managing Cookies</h2>
<p>You can control or delete cookies through your browser settings. Disabling strictly necessary cookies may affect your ability to use core features of the platform.</p>
<h2>Changes to This Policy</h2>
<p>We may update this Cookie Policy from time to time. Please check back periodically for the latest version.</p>
<h2>Questions?</h2>
<p>Contact us via our <a href="/contact">contact page</a> for any cookie-related questions.</p>
HTML,
            ],
            [
                'title'            => 'Refund Policy',
                'slug'             => 'refund-policy',
                'meta_description' => 'Our policy on cancellations and refunds.',
                'sort_order'       => 40,
                'content'          => <<<HTML
<h2>Business-Side Cancellations</h2>
<p>Cancellation and refund terms for individual bookings are set by each service provider on this platform. Please review the specific business's policy before confirming your appointment.</p>
<h2>Platform Subscription Fees</h2>
<p>Subscription fees paid by business accounts for access to the platform are non-refundable except where required by applicable consumer protection law in your jurisdiction.</p>
<h2>Disputes</h2>
<p>If you believe you have been incorrectly charged, please contact us within 14 days of the charge using our <a href="/contact">contact form</a> and we will investigate promptly.</p>
HTML,
            ],
        ];

        foreach ($pages as $data) {
            Page::updateOrCreate(['slug' => $data['slug']], $data + ['is_enabled' => true]);
        }
    }
}
