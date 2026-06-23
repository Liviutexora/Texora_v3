<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * BookingSaasSeeder
 *
 * Seeds a full multi-business Slotara demo with 8 business verticals,
 * real SVG logos, meaningful booking history, and one set of demo
 * quick-login accounts per role.
 *
 * ┌──────────────────────────────────────────────────────────────────────┐
 * │  BUSINESSES                                          PLAN           │
 * │  1. Velvet Chair Studio  (Salon)       velvet-chair  Pro (Monthly)  │
 * │  2. ClearPath Clinic     (Clinic)      clearpath     Pro (Annually) │
 * │  3. Apex Advisory        (Consultant)  apex-advisory Free           │
 * │  4. IronEdge Fitness     (Gym)         ironedge      Pro (Monthly)  │
 * │  5. BrightMind Tutoring  (Tutor)       brightmind    Free           │
 * │  6. Pixora Creative      (Agency)      pixora        Pro (Monthly)  │
 * │  7. RevUp Auto           (Auto Shop)   revup-auto    Pro (Annually) │
 * │  8. LensLife Studio      (Photographer)lenslife      Pro (Monthly)  │
 * └──────────────────────────────────────────────────────────────────────┘
 *
 * DEMO QUICK-LOGIN ACCOUNTS (password: password)
 * ─────────────────────────────────────────────────────────────
 *  Role          Email                          Panel
 *  super_admin   admin@slotara.app             /admin
 *  super_admin   admin2@slotara.app            /admin
 *  super_admin   admin3@slotara.app            /admin
 *  tenant_owner  owner@velvet-chair.demo        /manage  (Salon)
 *  tenant_owner  owner@ironedge-fitness.demo    /manage  (Gym)
 *  tenant_owner  owner@lenslife-studio.demo     /manage  (Photographer)
 *  staff         staff@slotara.app             /manage  (Salon – Sofia)
 *  staff         staff2@slotara.app            /manage  (Gym – Carlos)
 *  staff         staff3@slotara.app            /manage  (Clinic – Dr. Amara)
 *  client        client@slotara.app            /my-bookings
 *  client        client2@slotara.app           /my-bookings
 *  client        client3@slotara.app           /my-bookings
 */
class BookingSaasSeeder extends Seeder
{
    private Carbon $now;

    // ── Entry Point ───────────────────────────────────────────────────────────

    public function run(): void
    {
        $this->now = now();

        $this->clearDemoData();
        $this->createLogos();

        [$freeId, $monthlyId, $yearlyId] = $this->seedPlans();

        // 3 super admin accounts
        foreach ([
            ['admin@slotara.app',  'Alex Morgan'],
            ['admin2@slotara.app', 'Sarah Chen'],
            ['admin3@slotara.app', 'David Park'],
        ] as [$email, $name]) {
            $this->assignRole($this->upsertUser($email, $name), 'super_admin');
        }

        // 8 businesses  (2 on Free, 3 on Pro Monthly, 3 on Pro Annually)
        $salonId       = $this->seedSalon($monthlyId);
        $clinicId      = $this->seedClinic($yearlyId);
        $consultantId  = $this->seedConsultant($freeId);    // Free plan — 1 provider, 1 service
        $gymId         = $this->seedGym($monthlyId);
        $tutorId       = $this->seedTutor($freeId);         // Free plan — 1 provider, 1 service
        $agencyId      = $this->seedAgency($monthlyId);
        $autoId        = $this->seedAutoShop($yearlyId);
        $photoId       = $this->seedPhotographer($monthlyId);

        // 3 demo staff users (easy quick-login, linked as providers)
        $this->seedDemoStaff($salonId, $gymId, $clinicId);

        // 3 demo client users with cross-business bookings
        $this->seedDemoClients($salonId, $clinicId, $gymId, $tutorId, $agencyId, $photoId);

        $this->printSummary();
    }

    // ── LOGO GENERATION ──────────────────────────────────────────────────────

    private function createLogos(): void
    {
        $logos = [

            'logo-velvet-chair.svg' => <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <rect width="200" height="200" rx="40" fill="#9333ea"/>
  <circle cx="70" cy="65" r="22" fill="none" stroke="white" stroke-width="11"/>
  <circle cx="130" cy="65" r="22" fill="none" stroke="white" stroke-width="11"/>
  <line x1="85" y1="80" x2="100" y2="100" stroke="white" stroke-width="11" stroke-linecap="round"/>
  <line x1="115" y1="80" x2="100" y2="100" stroke="white" stroke-width="11" stroke-linecap="round"/>
  <line x1="100" y1="100" x2="76" y2="155" stroke="white" stroke-width="11" stroke-linecap="round"/>
  <line x1="100" y1="100" x2="124" y2="155" stroke="white" stroke-width="11" stroke-linecap="round"/>
  <circle cx="100" cy="100" r="7" fill="#9333ea"/>
</svg>
SVG,

            'logo-clearpath-clinic.svg' => <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <rect width="200" height="200" rx="40" fill="#0891b2"/>
  <rect x="82" y="44" width="36" height="112" rx="10" fill="white"/>
  <rect x="44" y="82" width="112" height="36" rx="10" fill="white"/>
</svg>
SVG,

            'logo-apex-advisory.svg' => <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <rect width="200" height="200" rx="40" fill="#1e40af"/>
  <rect x="35" y="132" width="28" height="36" rx="6" fill="white" opacity="0.4"/>
  <rect x="86" y="100" width="28" height="68" rx="6" fill="white" opacity="0.7"/>
  <rect x="137" y="58" width="28" height="110" rx="6" fill="white"/>
  <polyline points="49,126 100,88 151,50" stroke="white" stroke-width="7" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
  <polyline points="136,50 151,50 151,65" stroke="white" stroke-width="7" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
SVG,

            'logo-ironedge-fitness.svg' => <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <rect width="200" height="200" rx="40" fill="#ea580c"/>
  <rect x="18" y="78" width="26" height="44" rx="9" fill="white"/>
  <rect x="40" y="86" width="16" height="28" rx="5" fill="white"/>
  <rect x="56" y="91" width="88" height="18" rx="5" fill="white"/>
  <rect x="144" y="86" width="16" height="28" rx="5" fill="white"/>
  <rect x="156" y="78" width="26" height="44" rx="9" fill="white"/>
</svg>
SVG,

            'logo-brightmind-tutoring.svg' => <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <rect width="200" height="200" rx="40" fill="#16a34a"/>
  <circle cx="100" cy="88" r="44" fill="white"/>
  <rect x="76" y="124" width="48" height="12" rx="5" fill="white" opacity="0.9"/>
  <rect x="80" y="138" width="40" height="10" rx="5" fill="white" opacity="0.65"/>
  <rect x="86" y="150" width="28" height="9" rx="4" fill="white" opacity="0.4"/>
  <polyline points="88,102 88,78 100,65 112,78 112,102" stroke="#16a34a" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
SVG,

            'logo-pixora-creative.svg' => <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <rect width="200" height="200" rx="40" fill="#7c3aed"/>
  <polygon points="100,38 162,90 100,165 38,90" fill="white" opacity="0.18"/>
  <polygon points="100,38 162,90 100,165 38,90" fill="none" stroke="white" stroke-width="9" stroke-linejoin="round"/>
  <polygon points="100,38 162,90 100,96 38,90" fill="white" opacity="0.5"/>
  <line x1="38" y1="90" x2="162" y2="90" stroke="white" stroke-width="7" opacity="0.7"/>
  <line x1="68" y1="90" x2="100" y2="38" stroke="white" stroke-width="4" opacity="0.5"/>
  <line x1="132" y1="90" x2="100" y2="38" stroke="white" stroke-width="4" opacity="0.5"/>
  <line x1="68" y1="90" x2="100" y2="165" stroke="white" stroke-width="4" opacity="0.3"/>
  <line x1="132" y1="90" x2="100" y2="165" stroke="white" stroke-width="4" opacity="0.3"/>
</svg>
SVG,

            'logo-revup-auto.svg' => <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <rect width="200" height="200" rx="40" fill="#dc2626"/>
  <circle cx="100" cy="100" r="48" fill="none" stroke="white" stroke-width="13"/>
  <circle cx="100" cy="100" r="17" fill="white"/>
  <rect x="87" y="38" width="26" height="24" rx="7" fill="white"/>
  <rect x="87" y="138" width="26" height="24" rx="7" fill="white"/>
  <rect x="38" y="87" width="24" height="26" rx="7" fill="white"/>
  <rect x="138" y="87" width="24" height="26" rx="7" fill="white"/>
  <rect x="52" y="52" width="20" height="20" rx="5" fill="white" transform="rotate(45 62 62)"/>
  <rect x="128" y="52" width="20" height="20" rx="5" fill="white" transform="rotate(45 138 62)"/>
  <rect x="52" y="128" width="20" height="20" rx="5" fill="white" transform="rotate(45 62 138)"/>
  <rect x="128" y="128" width="20" height="20" rx="5" fill="white" transform="rotate(45 138 138)"/>
</svg>
SVG,

            'logo-lenslife-studio.svg' => <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <rect width="200" height="200" rx="40" fill="#d97706"/>
  <rect x="26" y="72" width="148" height="98" rx="16" fill="white"/>
  <rect x="68" y="54" width="64" height="28" rx="10" fill="white"/>
  <circle cx="100" cy="121" r="36" fill="#d97706"/>
  <circle cx="100" cy="121" r="27" fill="none" stroke="white" stroke-width="7" opacity="0.55"/>
  <circle cx="100" cy="121" r="14" fill="white" opacity="0.85"/>
  <circle cx="152" cy="90" r="9" fill="#d97706"/>
  <circle cx="152" cy="90" r="6" fill="white" opacity="0.7"/>
</svg>
SVG,
        ];

        foreach ($logos as $filename => $svg) {
            Storage::disk('public')->put("logos/{$filename}", trim($svg));
        }
    }

    // ── SUBSCRIPTION PLANS ────────────────────────────────────────────────────

    private function seedPlans(): array
    {
        $freeFeatures = json_encode([
            ['icon' => 'tag',        'text' => '1 service'],
            ['icon' => 'users',      'text' => '1 service provider'],
            ['icon' => 'calendar',   'text' => 'Up to 30 bookings per month'],
            ['icon' => 'globe',      'text' => 'Custom branded booking page'],
            ['icon' => 'mail',       'text' => 'Automated email confirmations'],
            ['icon' => 'clock',      'text' => 'Real-time availability management'],
            ['icon' => 'x-circle',   'text' => 'Client self-service cancellation'],
        ]);

        $proFeatures = json_encode([
            ['icon' => 'tag',        'text' => 'Unlimited services'],
            ['icon' => 'calendar',   'text' => 'Unlimited bookings per month'],
            ['icon' => 'users',      'text' => 'Up to 15 service providers'],
            ['icon' => 'globe',      'text' => 'Custom branded booking page'],
            ['icon' => 'mail',       'text' => 'Automated email confirmations & reminders'],
            ['icon' => 'link',       'text' => 'iCal / calendar export for clients'],
            ['icon' => 'sliders',    'text' => 'Custom fields on your booking form'],
            ['icon' => 'clock',      'text' => 'Real-time availability management'],
            ['icon' => 'x-circle',   'text' => 'Client self-service cancellation'],
            ['icon' => 'bar-chart',  'text' => 'Analytics & booking reports'],
            ['icon' => 'headphones', 'text' => 'Priority email support'],
        ]);

        $free = $this->upsertPlan([
            'name'                   => 'Free',
            'slug'                   => 'free',
            'price'                  => 0.00,
            'billing_cycle'          => 'monthly',
            'sort_order'             => 0,
            'max_providers'          => 1,
            'max_services'           => 1,
            'max_bookings_per_month' => 30,
            'features'               => $freeFeatures,
            'is_active'              => 1,
        ]);

        $monthly = $this->upsertPlan([
            'name'                   => 'Pro (Monthly)',
            'slug'                   => 'professional-monthly',
            'price'                  => 29.00,
            'billing_cycle'          => 'monthly',
            'sort_order'             => 1,
            'max_providers'          => 15,
            'max_services'           => null,
            'max_bookings_per_month' => null,
            'features'               => $proFeatures,
            'is_active'              => 1,
        ]);

        // $29 × 12 × 0.80 = $278.40 → $278
        $yearly = $this->upsertPlan([
            'name'                   => 'Pro (Annually)',
            'slug'                   => 'professional-yearly',
            'price'                  => 278.00,
            'billing_cycle'          => 'yearly',
            'sort_order'             => 2,
            'max_providers'          => 15,
            'max_services'           => null,
            'max_bookings_per_month' => null,
            'features'               => $proFeatures,
            'is_active'              => 1,
        ]);

        return [$free, $monthly, $yearly];
    }

    // ── BUSINESS 1 — Velvet Chair Studio (Salon) ─────────────────────────────

    private function seedSalon(int $planId): int
    {
        $ownerId = $this->upsertUser('owner@velvet-chair.demo', 'Isabella Grant', '+1 212 555 0101');
        $this->assignRole($ownerId, 'tenant_owner');

        $tenantId = $this->upsertTenant([
            'name'                 => 'Velvet Chair Studio',
            'slug'                 => 'velvet-chair',
            'owner_id'             => $ownerId,
            'plan_id'              => $planId,
            'status'               => 'active',
            'email'                => 'hello@velvetchair.demo',
            'phone'                => '+1 212 555 0101',
            'address'              => '142 West 46th Street',
            'city'                 => 'New York',
            'country'              => 'United States',
            'logo'                 => 'logos/logo-velvet-chair.svg',
            'timezone'             => 'America/New_York',
            'currency'             => 'USD',
            'booking_page_color'   => '#9333ea',
            'booking_page_tagline' => 'Transform your look. Own your confidence.',
        ]);
        DB::table('users')->where('id', $ownerId)->update(['tenant_id' => $tenantId]);

        $services = $this->seedServices($tenantId, 'USD', [
            ['name' => 'Haircut & Blowdry',      'minutes' => 60,  'price' => 85,  'color' => '#9333ea', 'desc' => 'Precision cut with a professional finish blowdry, tailored to your face shape.'],
            ['name' => 'Balayage Color',          'minutes' => 150, 'price' => 220, 'color' => '#a855f7', 'desc' => 'Hand-painted highlights blended seamlessly for a natural sun-kissed effect.'],
            ['name' => 'Deep Conditioning Treatment', 'minutes' => 45, 'price' => 65, 'color' => '#c084fc', 'desc' => 'Intensive moisture treatment that restores shine and softness.'],
            ['name' => 'Gel Manicure',            'minutes' => 60,  'price' => 55,  'color' => '#e879f9', 'desc' => 'Long-lasting gel polish with nail shaping and cuticle care.'],
            ['name' => 'Lash Lift & Tint',        'minutes' => 75,  'price' => 110, 'color' => '#f0abfc', 'desc' => 'Natural lash enhancement — no extensions, lasts 6–8 weeks.'],
        ]);

        $staff = [
            ['email' => 'sofia@velvet-chair.demo', 'name' => 'Sofia Reyes',  'title' => 'Senior Stylist',    'exp' => 8, 'color' => '#a855f7', 'start' => '09:00', 'weekend' => '10:00'],
            ['email' => 'maya@velvet-chair.demo',  'name' => 'Maya Chen',    'title' => 'Color Specialist',  'exp' => 6, 'color' => '#c026d3', 'start' => '10:00', 'weekend' => '11:00'],
        ];

        foreach ($staff as $s) {
            $uid = $this->upsertUser($s['email'], $s['name']);
            $pid = $this->upsertProvider($tenantId, $uid, $s['title'], $s['exp'], $s['color']);
            $this->attachServices($pid, $services);
            $this->seedShift($tenantId, $pid, 'Weekdays', $s['start'], 60, 9,  [1,2,3,4,5]);
            $this->seedShift($tenantId, $pid, 'Saturday', $s['weekend'], 60, 6, [6]);
            $this->seedBookings($tenantId, $pid, $services, 'USD', $this->salonClients());
        }

        return $tenantId;
    }

    // ── BUSINESS 2 — ClearPath Clinic ────────────────────────────────────────

    private function seedClinic(int $planId): int
    {
        $ownerId = $this->upsertUser('owner@clearpath-clinic.demo', 'Dr. Michael Torres', '+1 312 555 0202');
        $this->assignRole($ownerId, 'tenant_owner');

        $tenantId = $this->upsertTenant([
            'name'                 => 'ClearPath Clinic',
            'slug'                 => 'clearpath-clinic',
            'owner_id'             => $ownerId,
            'plan_id'              => $planId,
            'status'               => 'active',
            'email'                => 'appointments@clearpathclinic.demo',
            'phone'                => '+1 312 555 0202',
            'address'              => '890 North Michigan Avenue, Suite 12',
            'city'                 => 'Chicago',
            'country'              => 'United States',
            'logo'                 => 'logos/logo-clearpath-clinic.svg',
            'timezone'             => 'America/Chicago',
            'currency'             => 'USD',
            'booking_page_color'   => '#0891b2',
            'booking_page_tagline' => 'Your health. Our priority.',
        ]);
        DB::table('users')->where('id', $ownerId)->update(['tenant_id' => $tenantId]);

        $services = $this->seedServices($tenantId, 'USD', [
            ['name' => 'General Consultation',     'minutes' => 30,  'price' => 150, 'color' => '#0891b2', 'desc' => 'Comprehensive consultation with a general practitioner for acute or chronic concerns.'],
            ['name' => 'Annual Health Checkup',    'minutes' => 60,  'price' => 250, 'color' => '#0e7490', 'desc' => 'Full-body health screening including blood work, vitals and lifestyle review.'],
            ['name' => 'Physiotherapy Session',    'minutes' => 45,  'price' => 130, 'color' => '#06b6d4', 'desc' => 'Targeted physiotherapy for injury recovery and chronic pain management.'],
            ['name' => 'Nutrition Counselling',    'minutes' => 45,  'price' => 95,  'color' => '#22d3ee', 'desc' => 'Personalised dietary plan to support your health and wellness goals.'],
            ['name' => 'Mental Health Session',    'minutes' => 50,  'price' => 160, 'color' => '#67e8f9', 'desc' => 'Confidential session with a licensed therapist for anxiety, stress or depression.'],
        ]);

        $staff = [
            ['email' => 'amara@clearpath-clinic.demo', 'name' => 'Dr. Amara Osei',   'title' => 'General Practitioner', 'exp' => 12, 'color' => '#0e7490', 'start' => '08:00', 'weekend' => null],
            ['email' => 'kiran@clearpath-clinic.demo', 'name' => 'Dr. Kiran Patel',  'title' => 'Physiotherapist',      'exp' => 7,  'color' => '#0891b2', 'start' => '09:00', 'weekend' => '09:00'],
        ];

        foreach ($staff as $s) {
            $uid = $this->upsertUser($s['email'], $s['name']);
            $pid = $this->upsertProvider($tenantId, $uid, $s['title'], $s['exp'], $s['color']);
            $this->attachServices($pid, $services);
            $this->seedShift($tenantId, $pid, 'Weekdays', $s['start'], 30, 16, [1,2,3,4,5]);
            if ($s['weekend']) {
                $this->seedShift($tenantId, $pid, 'Saturday', $s['weekend'], 30, 10, [6]);
            }
            $this->seedBookings($tenantId, $pid, $services, 'USD', $this->clinicClients());
        }

        return $tenantId;
    }

    // ── BUSINESS 3 — Apex Advisory (Consultant) — Free plan ─────────────────

    private function seedConsultant(int $planId): int
    {
        $ownerId = $this->upsertUser('owner@apex-advisory.demo', 'Marcus Williams', '+1 415 555 0303');
        $this->assignRole($ownerId, 'tenant_owner');

        $tenantId = $this->upsertTenant([
            'name'                 => 'Apex Advisory',
            'slug'                 => 'apex-advisory',
            'owner_id'             => $ownerId,
            'plan_id'              => $planId,
            'status'               => 'active',
            'email'                => 'hello@apexadvisory.demo',
            'phone'                => '+1 415 555 0303',
            'address'              => '1 Market Street, 30th Floor',
            'city'                 => 'San Francisco',
            'country'              => 'United States',
            'logo'                 => 'logos/logo-apex-advisory.svg',
            'timezone'             => 'America/Los_Angeles',
            'currency'             => 'USD',
            'booking_page_color'   => '#1e40af',
            'booking_page_tagline' => 'Strategic clarity for sustainable growth.',
        ]);
        DB::table('users')->where('id', $ownerId)->update(['tenant_id' => $tenantId]);

        // Free plan: 1 service only
        $services = $this->seedServices($tenantId, 'USD', [
            ['name' => 'Business Strategy Session', 'minutes' => 90, 'price' => 450, 'color' => '#1e40af', 'desc' => 'In-depth session to define your market position, growth levers and 12-month roadmap.'],
        ]);

        // Free plan: owner is the sole consultant (1 provider)
        $pid = $this->upsertProvider($tenantId, $ownerId, 'Business Consultant', 12, '#1e40af');
        $this->attachServices($pid, $services);
        $this->seedShift($tenantId, $pid, 'Weekdays', '09:00', 60, 7, [1,2,3,4,5]);
        $this->seedBookings($tenantId, $pid, $services, 'USD', $this->consultantClients());

        return $tenantId;
    }

    // ── BUSINESS 4 — IronEdge Fitness (Gym) ──────────────────────────────────

    private function seedGym(int $planId): int
    {
        $ownerId = $this->upsertUser('owner@ironedge-fitness.demo', 'Jake Torres', '+1 646 555 0404');
        $this->assignRole($ownerId, 'tenant_owner');

        $tenantId = $this->upsertTenant([
            'name'                 => 'IronEdge Fitness',
            'slug'                 => 'ironedge-fitness',
            'owner_id'             => $ownerId,
            'plan_id'              => $planId,
            'status'               => 'active',
            'email'                => 'train@ironedgefitness.demo',
            'phone'                => '+1 646 555 0404',
            'address'              => '320 West 57th Street',
            'city'                 => 'New York',
            'country'              => 'United States',
            'logo'                 => 'logos/logo-ironedge-fitness.svg',
            'timezone'             => 'America/New_York',
            'currency'             => 'USD',
            'booking_page_color'   => '#ea580c',
            'booking_page_tagline' => 'Train harder. Recover smarter. Live stronger.',
        ]);
        DB::table('users')->where('id', $ownerId)->update(['tenant_id' => $tenantId]);

        $services = $this->seedServices($tenantId, 'USD', [
            ['name' => 'Personal Training (1hr)',  'minutes' => 60, 'price' => 95,  'color' => '#ea580c', 'desc' => 'One-on-one session with a certified trainer — strength, cardio or hybrid.'],
            ['name' => 'Yoga Flow',                'minutes' => 60, 'price' => 42,  'color' => '#f97316', 'desc' => 'Guided yoga for all levels. Vinyasa, Hatha or Restorative — you choose.'],
            ['name' => 'HIIT Bootcamp',            'minutes' => 45, 'price' => 38,  'color' => '#fb923c', 'desc' => 'High-intensity group class for maximum calorie burn and cardio fitness.'],
            ['name' => 'Body Composition Assessment', 'minutes' => 30, 'price' => 65, 'color' => '#fdba74', 'desc' => 'InBody scan + consultation: body fat %, muscle mass, metabolic rate.'],
            ['name' => 'Pilates Session',          'minutes' => 55, 'price' => 50,  'color' => '#fed7aa', 'desc' => 'Core-focused Pilates for posture, stability and injury prevention.'],
        ]);

        $staff = [
            ['email' => 'anika@ironedge-fitness.demo',  'name' => 'Anika Patel',   'title' => 'Yoga & Pilates Instructor', 'exp' => 6,  'color' => '#f97316', 'start' => '06:00', 'weekend' => '07:00'],
            ['email' => 'carlos@ironedge-fitness.demo', 'name' => 'Carlos Mendez', 'title' => 'Strength Coach',            'exp' => 9,  'color' => '#ea580c', 'start' => '07:00', 'weekend' => '08:00'],
        ];

        foreach ($staff as $s) {
            $uid = $this->upsertUser($s['email'], $s['name']);
            $pid = $this->upsertProvider($tenantId, $uid, $s['title'], $s['exp'], $s['color']);
            $this->attachServices($pid, $services);
            $this->seedShift($tenantId, $pid, 'Weekdays', $s['start'], 60, 11, [1,2,3,4,5]);
            $this->seedShift($tenantId, $pid, 'Weekend',  $s['weekend'], 60, 7, [6,7]);
            $this->seedBookings($tenantId, $pid, $services, 'USD', $this->gymClients());
        }

        return $tenantId;
    }

    // ── BUSINESS 5 — BrightMind Tutoring — Free plan ────────────────────────

    private function seedTutor(int $planId): int
    {
        $ownerId = $this->upsertUser('owner@brightmind-tutoring.demo', 'Dr. Alice Kim', '+1 773 555 0505');
        $this->assignRole($ownerId, 'tenant_owner');

        $tenantId = $this->upsertTenant([
            'name'                 => 'BrightMind Tutoring',
            'slug'                 => 'brightmind-tutoring',
            'owner_id'             => $ownerId,
            'plan_id'              => $planId,
            'status'               => 'active',
            'email'                => 'learn@brightmindtutoring.demo',
            'phone'                => '+1 773 555 0505',
            'address'              => '77 West Wacker Drive, Suite 4500',
            'city'                 => 'Chicago',
            'country'              => 'United States',
            'logo'                 => 'logos/logo-brightmind-tutoring.svg',
            'timezone'             => 'America/Chicago',
            'currency'             => 'USD',
            'booking_page_color'   => '#16a34a',
            'booking_page_tagline' => 'Every student has potential. We unlock it.',
        ]);
        DB::table('users')->where('id', $ownerId)->update(['tenant_id' => $tenantId]);

        // Free plan: 1 service only
        $services = $this->seedServices($tenantId, 'USD', [
            ['name' => 'Math Tutoring (K–12)', 'minutes' => 60, 'price' => 65, 'color' => '#16a34a', 'desc' => 'Algebra, geometry, calculus and everything in between — concept mastery guaranteed.'],
        ]);

        // Free plan: owner is the sole tutor (1 provider)
        $pid = $this->upsertProvider($tenantId, $ownerId, 'Math & Science Tutor', 12, '#16a34a');
        $this->attachServices($pid, $services);
        $this->seedShift($tenantId, $pid, 'Weekdays', '14:00', 60, 4, [1,2,3,4,5]);
        $this->seedShift($tenantId, $pid, 'Weekend',  '10:00', 60, 5, [6,7]);
        $this->seedBookings($tenantId, $pid, $services, 'USD', $this->tutorClients());

        return $tenantId;
    }

    // ── BUSINESS 6 — Pixora Creative (Agency) ────────────────────────────────

    private function seedAgency(int $planId): int
    {
        $ownerId = $this->upsertUser('owner@pixora-creative.demo', 'Jordan Lee', '+1 310 555 0606');
        $this->assignRole($ownerId, 'tenant_owner');

        $tenantId = $this->upsertTenant([
            'name'                 => 'Pixora Creative',
            'slug'                 => 'pixora-creative',
            'owner_id'             => $ownerId,
            'plan_id'              => $planId,
            'status'               => 'active',
            'email'                => 'studio@pixoracreative.demo',
            'phone'                => '+1 310 555 0606',
            'address'              => '8424 Santa Monica Blvd',
            'city'                 => 'Los Angeles',
            'country'              => 'United States',
            'logo'                 => 'logos/logo-pixora-creative.svg',
            'timezone'             => 'America/Los_Angeles',
            'currency'             => 'USD',
            'booking_page_color'   => '#7c3aed',
            'booking_page_tagline' => 'Ideas that move people.',
        ]);
        DB::table('users')->where('id', $ownerId)->update(['tenant_id' => $tenantId]);

        $services = $this->seedServices($tenantId, 'USD', [
            ['name' => 'Brand Strategy Workshop',  'minutes' => 120, 'price' => 550, 'color' => '#7c3aed', 'desc' => 'Collaborative session to define brand positioning, voice and visual identity direction.'],
            ['name' => 'UX/UI Design Review',      'minutes' => 90,  'price' => 400, 'color' => '#6d28d9', 'desc' => 'Expert critique of your product UI with actionable improvements and wireframe notes.'],
            ['name' => 'Social Media Audit',       'minutes' => 60,  'price' => 225, 'color' => '#8b5cf6', 'desc' => 'Full audit of your social presence with a prioritised 30-day content strategy.'],
            ['name' => 'Content Strategy Session', 'minutes' => 90,  'price' => 350, 'color' => '#a78bfa', 'desc' => 'SEO-aligned content roadmap — topics, formats, distribution and KPIs.'],
            ['name' => 'Logo & Identity Consult',  'minutes' => 60,  'price' => 300, 'color' => '#c4b5fd', 'desc' => 'Discovery call to define visual identity — colours, typography and logo direction.'],
        ]);

        $staff = [
            ['email' => 'sam@pixora-creative.demo',   'name' => 'Sam Rivera',    'title' => 'Brand Designer',     'exp' => 7,  'color' => '#6d28d9', 'start' => '10:00'],
            ['email' => 'naomi@pixora-creative.demo', 'name' => 'Naomi Okafor', 'title' => 'Strategy Director',  'exp' => 11, 'color' => '#7c3aed', 'start' => '11:00'],
        ];

        foreach ($staff as $s) {
            $uid = $this->upsertUser($s['email'], $s['name']);
            $pid = $this->upsertProvider($tenantId, $uid, $s['title'], $s['exp'], $s['color']);
            $this->attachServices($pid, $services);
            $this->seedShift($tenantId, $pid, 'Weekdays', $s['start'], 90, 5, [1,2,3,4,5]);
            $this->seedBookings($tenantId, $pid, $services, 'USD', $this->agencyClients());
        }

        return $tenantId;
    }

    // ── BUSINESS 7 — RevUp Auto ───────────────────────────────────────────────

    private function seedAutoShop(int $planId): int
    {
        $ownerId = $this->upsertUser('owner@revup-auto.demo', 'Dave Park', '+1 720 555 0707');
        $this->assignRole($ownerId, 'tenant_owner');

        $tenantId = $this->upsertTenant([
            'name'                 => 'RevUp Auto',
            'slug'                 => 'revup-auto',
            'owner_id'             => $ownerId,
            'plan_id'              => $planId,
            'status'               => 'active',
            'email'                => 'service@revupauto.demo',
            'phone'                => '+1 720 555 0707',
            'address'              => '4850 Brighton Blvd',
            'city'                 => 'Denver',
            'country'              => 'United States',
            'logo'                 => 'logos/logo-revup-auto.svg',
            'timezone'             => 'America/Denver',
            'currency'             => 'USD',
            'booking_page_color'   => '#dc2626',
            'booking_page_tagline' => 'Your car. Our expertise. Zero surprises.',
        ]);
        DB::table('users')->where('id', $ownerId)->update(['tenant_id' => $tenantId]);

        $services = $this->seedServices($tenantId, 'USD', [
            ['name' => 'Oil Change & Filter',        'minutes' => 45,  'price' => 49,  'color' => '#dc2626', 'desc' => 'Full synthetic oil change with filter replacement and top-up of all fluids.'],
            ['name' => 'Full Vehicle Service',       'minutes' => 120, 'price' => 225, 'color' => '#b91c1c', 'desc' => 'Comprehensive inspection and service — brakes, battery, filters, tyre pressure.'],
            ['name' => 'Tire Rotation & Balance',    'minutes' => 60,  'price' => 45,  'color' => '#ef4444', 'desc' => 'Rotate and rebalance all four tyres for even wear and better handling.'],
            ['name' => 'Brake Inspection',           'minutes' => 30,  'price' => 0,   'color' => '#f87171', 'desc' => 'Free 12-point brake inspection — pads, rotors, calipers and hydraulic system.'],
            ['name' => 'AC Service & Recharge',      'minutes' => 60,  'price' => 99,  'color' => '#fca5a5', 'desc' => 'AC system check, refrigerant recharge and leak test.'],
        ]);

        $staff = [
            ['email' => 'mike@revup-auto.demo', 'name' => 'Mike Santos',   'title' => 'Master Technician',  'exp' => 14, 'color' => '#b91c1c', 'start' => '08:00', 'weekend' => '09:00'],
            ['email' => 'lena@revup-auto.demo', 'name' => 'Lena Fischer',  'title' => 'Service Advisor',    'exp' => 5,  'color' => '#dc2626', 'start' => '08:30', 'weekend' => '09:30'],
        ];

        foreach ($staff as $s) {
            $uid = $this->upsertUser($s['email'], $s['name']);
            $pid = $this->upsertProvider($tenantId, $uid, $s['title'], $s['exp'], $s['color']);
            $this->attachServices($pid, $services);
            $this->seedShift($tenantId, $pid, 'Weekdays', $s['start'], 45, 12, [1,2,3,4,5]);
            $this->seedShift($tenantId, $pid, 'Saturday', $s['weekend'], 45, 8, [6]);
            $this->seedBookings($tenantId, $pid, $services, 'USD', $this->autoClients());
        }

        return $tenantId;
    }

    // ── BUSINESS 8 — LensLife Studio (Photographer) ──────────────────────────

    private function seedPhotographer(int $planId): int
    {
        $ownerId = $this->upsertUser('owner@lenslife-studio.demo', 'Clara Novak', '+1 929 555 0808');
        $this->assignRole($ownerId, 'tenant_owner');

        $tenantId = $this->upsertTenant([
            'name'                 => 'LensLife Studio',
            'slug'                 => 'lenslife-studio',
            'owner_id'             => $ownerId,
            'plan_id'              => $planId,
            'status'               => 'active',
            'email'                => 'bookings@lenslifestudio.demo',
            'phone'                => '+1 929 555 0808',
            'address'              => '68 Jay Street, Studio 3B',
            'city'                 => 'Brooklyn',
            'country'              => 'United States',
            'logo'                 => 'logos/logo-lenslife-studio.svg',
            'timezone'             => 'America/New_York',
            'currency'             => 'USD',
            'booking_page_color'   => '#d97706',
            'booking_page_tagline' => 'Moments worth keeping forever.',
        ]);
        DB::table('users')->where('id', $ownerId)->update(['tenant_id' => $tenantId]);

        $services = $this->seedServices($tenantId, 'USD', [
            ['name' => 'Portrait Session',        'minutes' => 90,  'price' => 275, 'color' => '#d97706', 'desc' => 'Studio or outdoor portrait session — up to 2 hours, 30+ edited images delivered.'],
            ['name' => 'Corporate Headshots',     'minutes' => 60,  'price' => 175, 'color' => '#b45309', 'desc' => 'Professional headshots for LinkedIn, press kits and company profiles.'],
            ['name' => 'Family Portrait',         'minutes' => 120, 'price' => 350, 'color' => '#f59e0b', 'desc' => 'Relaxed family session — up to 6 people, 50+ edited images, private gallery.'],
            ['name' => 'Product Photography',     'minutes' => 180, 'price' => 425, 'color' => '#fbbf24', 'desc' => 'E-commerce and brand product shoot — flat lay, lifestyle and detail shots.'],
            ['name' => 'Wedding Coverage',        'minutes' => 480, 'price' => 2800,'color' => '#fcd34d', 'desc' => 'Full-day wedding photography — ceremony, portraits, reception, 400+ images.'],
        ]);

        $staff = [
            ['email' => 'marcus@lenslife-studio.demo', 'name' => 'Marcus Bell',  'title' => 'Wedding & Events Photographer', 'exp' => 10, 'color' => '#b45309', 'start' => '09:00', 'weekend' => '09:00'],
            ['email' => 'yuki@lenslife-studio.demo',   'name' => 'Yuki Tanaka', 'title' => 'Portrait & Commercial Photographer', 'exp' => 7, 'color' => '#d97706', 'start' => '10:00', 'weekend' => '10:00'],
        ];

        foreach ($staff as $s) {
            $uid = $this->upsertUser($s['email'], $s['name']);
            $pid = $this->upsertProvider($tenantId, $uid, $s['title'], $s['exp'], $s['color']);
            $this->attachServices($pid, $services);
            $this->seedShift($tenantId, $pid, 'Weekdays',  $s['start'], 90, 4, [1,2,3,4,5]);
            $this->seedShift($tenantId, $pid, 'Weekend',   $s['weekend'], 90, 5, [6,7]);
            $this->seedBookings($tenantId, $pid, $services, 'USD', $this->photoClients());
        }

        return $tenantId;
    }

    // ── DEMO STAFF USERS ─────────────────────────────────────────────────────

    private function seedDemoStaff(int $salonId, int $gymId, int $clinicId): void
    {
        // staff@slotara.app → Sofia Reyes role (Salon)
        $uid1 = $this->upsertUser('staff@slotara.app', 'Sofia Reyes (Demo)', '+1 212 555 9001');
        $this->assignRole($uid1, 'staff');
        DB::table('users')->where('id', $uid1)->update(['tenant_id' => $salonId]);
        $this->upsertProvider($salonId, $uid1, 'Senior Stylist', 8, '#a855f7');

        // staff2@slotara.app → Carlos Mendez role (Gym)
        $uid2 = $this->upsertUser('staff2@slotara.app', 'Carlos Mendez (Demo)', '+1 646 555 9002');
        $this->assignRole($uid2, 'staff');
        DB::table('users')->where('id', $uid2)->update(['tenant_id' => $gymId]);
        $this->upsertProvider($gymId, $uid2, 'Strength Coach', 9, '#ea580c');

        // staff3@slotara.app → Dr. Amara Osei role (Clinic)
        $uid3 = $this->upsertUser('staff3@slotara.app', 'Dr. Amara Osei (Demo)', '+1 312 555 9003');
        $this->assignRole($uid3, 'staff');
        DB::table('users')->where('id', $uid3)->update(['tenant_id' => $clinicId]);
        $this->upsertProvider($clinicId, $uid3, 'General Practitioner', 12, '#0e7490');
    }

    // ── DEMO CLIENT USERS ─────────────────────────────────────────────────────

    private function seedDemoClients(
        int $salonId, int $clinicId, int $gymId,
        int $tutorId, int $agencyId, int $photoId
    ): void {
        $clients = [
            ['client@slotara.app',  'Emma Davis',     '+1 917 555 1001', [$gymId, $salonId, $clinicId]],
            ['client2@slotara.app', 'Liam Johnson',   '+1 917 555 1002', [$agencyId, $photoId, $gymId]],
            ['client3@slotara.app', 'Olivia Martinez','+1 917 555 1003', [$tutorId, $clinicId, $salonId]],
        ];

        $now = $this->now;

        foreach ($clients as [$email, $name, $phone, $tenantIds]) {
            $userId = $this->upsertUser($email, $name, $phone);
            $this->assignRole($userId, 'client');

            // Seed 2 future confirmed bookings across their first 2 tenants
            $slots = [
                $now->copy()->next(Carbon::TUESDAY)->setTime(11, 0),
                $now->copy()->next(Carbon::THURSDAY)->setTime(14, 0),
            ];

            foreach (array_slice($tenantIds, 0, 2) as $i => $tid) {
                $service = DB::table('services')->where('tenant_id', $tid)->orderBy('id')->first();
                $provider = DB::table('providers')->where('tenant_id', $tid)->orderBy('id')->first();
                if (! $service || ! $provider) {
                    continue;
                }

                $slot = $slots[$i];
                $dur  = $service->duration_minutes ?? 60;

                $alreadyExists = DB::table('slot_reservations')
                    ->where('provider_id', $provider->id)
                    ->whereDate('date', $slot->toDateString())
                    ->where('start_time', $slot->toTimeString())
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                DB::table('slot_reservations')->insert([
                    'tenant_id'          => $tid,
                    'provider_id'        => $provider->id,
                    'service_id'         => $service->id,
                    'user_id'            => $userId,
                    'name'               => $name,
                    'email'              => $email,
                    'phone'              => $phone,
                    'date'               => $slot->toDateString(),
                    'start_time'         => $slot->toTimeString(),
                    'end_time'           => $slot->copy()->addMinutes($dur)->toTimeString(),
                    'status'             => 'confirmed',
                    'is_verified'        => 1,
                    'amount'             => $service->price,
                    'currency'           => 'USD',
                    'payment_status'     => 'paid',
                    'cancellation_token' => (string) Str::uuid(),
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);
            }
        }
    }

    // ── CLIENT NAME POOLS ─────────────────────────────────────────────────────

    private function salonClients(): array
    {
        return [
            ['name' => 'Emily Watson',    'email' => 'emily.w@demo.com',   'phone' => '9171110001'],
            ['name' => 'Chloe Parker',    'email' => 'chloe.p@demo.com',   'phone' => '9171110002'],
            ['name' => 'Jessica Moore',   'email' => 'jess.m@demo.com',    'phone' => '9171110003'],
            ['name' => 'Natalie Brooks',  'email' => 'nat.b@demo.com',     'phone' => '9171110004'],
            ['name' => 'Sophia Turner',   'email' => 'sophia.t@demo.com',  'phone' => '9171110005'],
            ['name' => 'Ava Mitchell',    'email' => 'ava.m@demo.com',     'phone' => '9171110006'],
            ['name' => 'Lily Collins',    'email' => 'lily.c@demo.com',    'phone' => '9171110007'],
            ['name' => 'Zoe Harris',      'email' => 'zoe.h@demo.com',     'phone' => '9171110008'],
        ];
    }

    private function clinicClients(): array
    {
        return [
            ['name' => 'Robert Chen',      'email' => 'rob.c@demo.com',    'phone' => '9172220001'],
            ['name' => 'Patricia Kim',     'email' => 'pat.k@demo.com',    'phone' => '9172220002'],
            ['name' => 'Daniel Garcia',    'email' => 'dan.g@demo.com',    'phone' => '9172220003'],
            ['name' => 'Michelle Adams',   'email' => 'mich.a@demo.com',   'phone' => '9172220004'],
            ['name' => 'Thomas Wilson',    'email' => 'tom.w@demo.com',    'phone' => '9172220005'],
            ['name' => 'Angela Martinez',  'email' => 'ang.m@demo.com',    'phone' => '9172220006'],
            ['name' => 'Kevin Lee',        'email' => 'kev.l@demo.com',    'phone' => '9172220007'],
            ['name' => 'Sandra Thompson',  'email' => 'san.t@demo.com',    'phone' => '9172220008'],
        ];
    }

    private function consultantClients(): array
    {
        return [
            ['name' => 'Nathan Clarke',    'email' => 'nath.c@demo.com',   'phone' => '9173330001'],
            ['name' => 'Rachel Scott',     'email' => 'rach.s@demo.com',   'phone' => '9173330002'],
            ['name' => 'Benjamin Hall',    'email' => 'ben.h@demo.com',    'phone' => '9173330003'],
            ['name' => 'Victoria Young',   'email' => 'vic.y@demo.com',    'phone' => '9173330004'],
            ['name' => 'Samuel Evans',     'email' => 'sam.e@demo.com',    'phone' => '9173330005'],
            ['name' => 'Elizabeth Cooper', 'email' => 'eliz.c@demo.com',   'phone' => '9173330006'],
        ];
    }

    private function gymClients(): array
    {
        return [
            ['name' => 'Tyler Brooks',     'email' => 'tyler.b@demo.com',  'phone' => '9174440001'],
            ['name' => 'Ashley Reed',      'email' => 'ash.r@demo.com',    'phone' => '9174440002'],
            ['name' => 'Jordan Powell',    'email' => 'jord.p@demo.com',   'phone' => '9174440003'],
            ['name' => 'Morgan Rivera',    'email' => 'morg.r@demo.com',   'phone' => '9174440004'],
            ['name' => 'Cameron Hughes',   'email' => 'cam.h@demo.com',    'phone' => '9174440005'],
            ['name' => 'Quinn Peterson',   'email' => 'quinn.p@demo.com',  'phone' => '9174440006'],
            ['name' => 'Blake Anderson',   'email' => 'blake.a@demo.com',  'phone' => '9174440007'],
            ['name' => 'Ryan Torres',      'email' => 'ryan.t@demo.com',   'phone' => '9174440008'],
        ];
    }

    private function tutorClients(): array
    {
        return [
            ['name' => 'Ethan Foster',     'email' => 'ethan.f@demo.com',  'phone' => '9175550001'],
            ['name' => 'Grace Murphy',     'email' => 'grace.m@demo.com',  'phone' => '9175550002'],
            ['name' => 'Connor Bailey',    'email' => 'conn.b@demo.com',   'phone' => '9175550003'],
            ['name' => 'Haley Sanders',    'email' => 'hal.s@demo.com',    'phone' => '9175550004'],
            ['name' => 'Lucas Price',      'email' => 'luc.p@demo.com',    'phone' => '9175550005'],
            ['name' => 'Hannah Simmons',   'email' => 'han.s@demo.com',    'phone' => '9175550006'],
        ];
    }

    private function agencyClients(): array
    {
        return [
            ['name' => 'Diana Foster',     'email' => 'diana.f@demo.com',  'phone' => '9176660001'],
            ['name' => 'Oliver Grant',     'email' => 'oli.g@demo.com',    'phone' => '9176660002'],
            ['name' => 'Penelope Ross',    'email' => 'pen.r@demo.com',    'phone' => '9176660003'],
            ['name' => 'Felix Bennett',    'email' => 'felix.b@demo.com',  'phone' => '9176660004'],
            ['name' => 'Serena Webb',      'email' => 'ser.w@demo.com',    'phone' => '9176660005'],
            ['name' => 'Hugo Chambers',    'email' => 'hugo.c@demo.com',   'phone' => '9176660006'],
        ];
    }

    private function autoClients(): array
    {
        return [
            ['name' => 'Dennis Porter',    'email' => 'denn.p@demo.com',   'phone' => '9177770001'],
            ['name' => 'Carol Barnes',     'email' => 'car.b@demo.com',    'phone' => '9177770002'],
            ['name' => 'Leonard Wright',   'email' => 'leo.w@demo.com',    'phone' => '9177770003'],
            ['name' => 'Dorothy Long',     'email' => 'dor.l@demo.com',    'phone' => '9177770004'],
            ['name' => 'Harold Stewart',   'email' => 'har.s@demo.com',    'phone' => '9177770005'],
            ['name' => 'Beverly Kelly',    'email' => 'bev.k@demo.com',    'phone' => '9177770006'],
            ['name' => 'Frank Cox',        'email' => 'frank.c@demo.com',  'phone' => '9177770007'],
            ['name' => 'Ruth Russell',     'email' => 'ruth.r@demo.com',   'phone' => '9177770008'],
        ];
    }

    private function photoClients(): array
    {
        return [
            ['name' => 'Nora Wallace',     'email' => 'nora.w@demo.com',   'phone' => '9178880001'],
            ['name' => 'Isaac Burke',      'email' => 'isaac.b@demo.com',  'phone' => '9178880002'],
            ['name' => 'Abigail Mason',    'email' => 'abig.m@demo.com',   'phone' => '9178880003'],
            ['name' => 'Lucas Spencer',    'email' => 'luc.sp@demo.com',   'phone' => '9178880004'],
            ['name' => 'Aurora Knight',    'email' => 'aur.k@demo.com',    'phone' => '9178880005'],
            ['name' => 'Julian Stone',     'email' => 'jul.s@demo.com',    'phone' => '9178880006'],
        ];
    }

    // ── BOOKING SEEDER ────────────────────────────────────────────────────────

    private function seedBookings(
        int $tenantId, int $providerId, array $serviceIds,
        string $currency, array $clients
    ): void {
        $servicePrices = DB::table('services')->whereIn('id', $serviceIds)
            ->pluck('price', 'id')->toArray();
        $serviceDurations = DB::table('services')->whereIn('id', $serviceIds)
            ->pluck('duration_minutes', 'id')->toArray();

        // Time slots: two per day (morning + afternoon)
        $timeSlots = ['09:00:00', '10:00:00', '11:00:00', '13:00:00', '14:00:00', '15:00:00', '16:00:00'];

        $counter = 0;

        // Past 5 weeks: completed, cancelled, no_show
        for ($week = -5; $week <= -1; $week++) {
            $base = $this->now->copy()->startOfWeek()->addWeeks($week);
            foreach ([0, 2, 4] as $dayOffset) {   // Mon, Wed, Fri
                $date      = $base->copy()->addDays($dayOffset);
                $client    = $clients[$counter % count($clients)];
                $serviceId = $serviceIds[$counter % count($serviceIds)];
                $time      = $timeSlots[$counter % count($timeSlots)];
                $dur       = $serviceDurations[$serviceId] ?? 60;
                $endTime   = Carbon::createFromFormat('H:i:s', $time)->addMinutes($dur)->format('H:i:s');

                $status = match (($counter % 10)) {
                    0, 1, 2, 3, 4, 5 => 'completed',
                    6, 7             => 'cancelled',
                    8                => 'no_show',
                    default          => 'completed',
                };

                $this->insertBooking($tenantId, $providerId, $serviceId, $date, $time, $endTime,
                    $client, $status, $servicePrices[$serviceId] ?? 0, $currency);
                $counter++;
            }
        }

        // Current + next 4 weeks: confirmed, pending
        for ($week = 0; $week <= 4; $week++) {
            $base = $this->now->copy()->startOfWeek()->addWeeks($week);
            foreach ([1, 3] as $dayOffset) {   // Tue, Thu
                $date      = $base->copy()->addDays($dayOffset);
                if ($date->isPast() && ! $date->isToday()) {
                    continue;
                }
                $client    = $clients[$counter % count($clients)];
                $serviceId = $serviceIds[$counter % count($serviceIds)];
                $time      = $timeSlots[($counter + 2) % count($timeSlots)];
                $dur       = $serviceDurations[$serviceId] ?? 60;
                $endTime   = Carbon::createFromFormat('H:i:s', $time)->addMinutes($dur)->format('H:i:s');
                $status    = ($counter % 5 === 0) ? 'pending' : 'confirmed';

                $this->insertBooking($tenantId, $providerId, $serviceId, $date, $time, $endTime,
                    $client, $status, $servicePrices[$serviceId] ?? 0, $currency);
                $counter++;
            }
        }
    }

    private function insertBooking(
        int $tenantId, int $providerId, int $serviceId,
        Carbon $date, string $startTime, string $endTime,
        array $client, string $status, float $amount, string $currency
    ): void {
        $exists = DB::table('slot_reservations')
            ->where('provider_id', $providerId)
            ->whereDate('date', $date->toDateString())
            ->where('start_time', $startTime)
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('slot_reservations')->insert([
            'tenant_id'          => $tenantId,
            'provider_id'        => $providerId,
            'service_id'         => $serviceId,
            'date'               => $date->toDateString(),
            'start_time'         => $startTime,
            'end_time'           => $endTime,
            'name'               => $client['name'],
            'email'              => $client['email'],
            'phone'              => $client['phone'],
            'status'             => $status,
            'is_verified'        => 1,
            'amount'             => $amount,
            'currency'           => $currency,
            'payment_status'     => in_array($status, ['confirmed', 'completed']) ? 'paid' : 'pending',
            'cancellation_token' => (string) Str::uuid(),
            'created_at'         => $this->now,
            'updated_at'         => $this->now,
        ]);
    }

    // ── CLEAR OLD DEMO DATA ───────────────────────────────────────────────────

    private function clearDemoData(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('slot_reservations')->truncate();
        DB::table('provider_slot_overrides')->truncate();
        DB::table('provider_services')->truncate();
        DB::table('provider_shifts')->truncate();
        DB::table('providers')->truncate();
        DB::table('services')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('api_tokens')->truncate();
        DB::table('login_activities')->truncate();
        DB::table('user_sessions')->truncate();
        DB::table('users')->truncate();
        DB::table('tenants')->truncate();
        DB::table('subscription_plans')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    // ── GENERIC HELPERS ───────────────────────────────────────────────────────

    private function upsertPlan(array $data): int
    {
        $existing = DB::table('subscription_plans')->where('slug', $data['slug'])->first();
        if ($existing) {
            DB::table('subscription_plans')->where('id', $existing->id)
                ->update(array_merge($data, ['updated_at' => $this->now]));
            return $existing->id;
        }
        return DB::table('subscription_plans')->insertGetId(
            array_merge($data, ['created_at' => $this->now, 'updated_at' => $this->now])
        );
    }

    private function upsertUser(string $email, string $name, ?string $phone = null): int
    {
        $existing = DB::table('users')->where('email', $email)->first();
        if ($existing) {
            return $existing->id;
        }
        return DB::table('users')->insertGetId([
            'name'              => $name,
            'email'             => $email,
            'phone_number'      => $phone,
            'password'          => Hash::make('password'),
            'email_verified_at' => $this->now,
            'is_active'         => 1,
            'created_at'        => $this->now,
            'updated_at'        => $this->now,
        ]);
    }

    private function assignRole(int $userId, string $roleName): void
    {
        $role = DB::table('roles')->where('name', $roleName)->where('guard_name', 'web')->first();
        if (! $role) {
            return;
        }
        DB::table('model_has_roles')->insertOrIgnore([
            'role_id'    => $role->id,
            'model_type' => 'App\\Models\\User',
            'model_id'   => $userId,
        ]);
    }

    private function upsertTenant(array $data): int
    {
        $existing = DB::table('tenants')->where('slug', $data['slug'])->first();
        if ($existing) {
            DB::table('tenants')->where('id', $existing->id)
                ->update(array_merge($data, ['updated_at' => $this->now]));
            return $existing->id;
        }
        return DB::table('tenants')->insertGetId(
            array_merge($data, ['created_at' => $this->now, 'updated_at' => $this->now])
        );
    }

    private function upsertProvider(int $tenantId, int $userId, string $title, int $exp, string $color): int
    {
        $existing = DB::table('providers')
            ->where('tenant_id', $tenantId)->where('user_id', $userId)->first();
        if ($existing) {
            return $existing->id;
        }
        // Handle unique constraint on user_id
        $byUser = DB::table('providers')->where('user_id', $userId)->first();
        if ($byUser) {
            DB::table('providers')->where('id', $byUser->id)->update([
                'tenant_id'        => $tenantId,
                'job_title'        => $title,
                'experience_years' => $exp,
                'color'            => $color,
                'updated_at'       => $this->now,
            ]);
            return $byUser->id;
        }
        return DB::table('providers')->insertGetId([
            'tenant_id'        => $tenantId,
            'user_id'          => $userId,
            'job_title'        => $title,
            'experience_years' => $exp,
            'color'            => $color,
            'is_active'        => 1,
            'created_at'       => $this->now,
            'updated_at'       => $this->now,
        ]);
    }

    private function seedServices(int $tenantId, string $currency, array $defs): array
    {
        $ids = [];
        foreach ($defs as $i => $def) {
            $existing = DB::table('services')
                ->where('tenant_id', $tenantId)->where('name', $def['name'])->first();
            if ($existing) {
                $ids[] = $existing->id;
                continue;
            }
            $ids[] = DB::table('services')->insertGetId([
                'tenant_id'        => $tenantId,
                'name'             => $def['name'],
                'description'      => $def['desc'] ?? null,
                'duration_minutes' => $def['minutes'],
                'price'            => $def['price'],
                'color'            => $def['color'],
                'currency'         => $currency,
                'is_active'        => 1,
                'sort_order'       => $i + 1,
                'created_at'       => $this->now,
                'updated_at'       => $this->now,
            ]);
        }
        return $ids;
    }

    private function attachServices(int $providerId, array $serviceIds): void
    {
        foreach ($serviceIds as $sid) {
            DB::table('provider_services')->insertOrIgnore([
                'provider_id' => $providerId,
                'service_id'  => $sid,
            ]);
        }
    }

    private function seedShift(
        int $tenantId, int $providerId, string $name,
        string $start, int $duration, int $slots, array $days
    ): void {
        $exists = DB::table('provider_shifts')
            ->where('tenant_id', $tenantId)
            ->where('provider_id', $providerId)
            ->where('name', $name)
            ->exists();
        if ($exists) {
            return;
        }
        DB::table('provider_shifts')->insert([
            'tenant_id'             => $tenantId,
            'provider_id'           => $providerId,
            'name'                  => $name,
            'start_time'            => $start,
            'slot_duration_minutes' => $duration,
            'number_of_slots'       => $slots,
            'buffer_minutes'        => 0,
            'available_days'        => json_encode($days),
            'created_at'            => $this->now,
            'updated_at'            => $this->now,
        ]);
    }

    // ── SUMMARY ───────────────────────────────────────────────────────────────

    private function printSummary(): void
    {
        $this->command->newLine();
        $this->command->info('  ✅  BookingSaasSeeder complete — 8 businesses, ' .
            DB::table('users')->count() . ' users, ' .
            DB::table('slot_reservations')->count() . ' bookings');
        $this->command->newLine();

        $rows = [
            ['SUPER ADMIN',   'admin@slotara.app',                   'password', '/admin'],
            ['SUPER ADMIN',   'admin2@slotara.app',                  'password', '/admin'],
            ['SUPER ADMIN',   'admin3@slotara.app',                  'password', '/admin'],
            ['TENANT OWNER',  'owner@velvet-chair.demo',              'password', '/manage  (Salon)'],
            ['TENANT OWNER',  'owner@ironedge-fitness.demo',          'password', '/manage  (Gym)'],
            ['TENANT OWNER',  'owner@lenslife-studio.demo',           'password', '/manage  (Photographer)'],
            ['STAFF',         'staff@slotara.app',                   'password', '/manage  (Salon)'],
            ['STAFF',         'staff2@slotara.app',                  'password', '/manage  (Gym)'],
            ['STAFF',         'staff3@slotara.app',                  'password', '/manage  (Clinic)'],
            ['CLIENT',        'client@slotara.app',                  'password', '/my-bookings'],
            ['CLIENT',        'client2@slotara.app',                 'password', '/my-bookings'],
            ['CLIENT',        'client3@slotara.app',                 'password', '/my-bookings'],
        ];

        $this->command->table(['Role', 'Email', 'Password', 'Panel'], $rows);
        $this->command->newLine();

        $businesses = [
            ['Velvet Chair Studio',  'Salon',         '/velvet-chair'],
            ['ClearPath Clinic',     'Clinic',         '/clearpath-clinic'],
            ['Apex Advisory',        'Consultant',     '/apex-advisory'],
            ['IronEdge Fitness',     'Gym',            '/ironedge-fitness'],
            ['BrightMind Tutoring',  'Tutor',          '/brightmind-tutoring'],
            ['Pixora Creative',      'Agency',         '/pixora-creative'],
            ['RevUp Auto',           'Auto Shop',      '/revup-auto'],
            ['LensLife Studio',      'Photographer',   '/lenslife-studio'],
        ];

        $this->command->table(['Business', 'Vertical', 'Public URL'], $businesses);
        $this->command->newLine();
    }
}
