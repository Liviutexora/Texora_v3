<div>
{{-- booking-card CSS variables extracted to resources/css/components.css --}}
@vite('resources/js/booking-theme.js')
@php
    $brand = $tenant?->booking_page_color ?? '#4f46e5';
    $hex = ltrim($brand, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    [$br, $bg, $bb] = array_map('hexdec', str_split($hex, 2));
    $brandLight   = "rgba($br,$bg,$bb,0.08)";
    $brandMid     = "rgba($br,$bg,$bb,0.15)";
    $brandBorder  = "rgba($br,$bg,$bb,0.35)";

    $stepLabels = [__('Service'), __('Provider'), __('Date & Time'), __('Your Details'), __('Confirm')];
    $currentStep = $step; // 1–5 (6 = success)

    $bookingTheme      = \App\Booking\Themes\ThemeRegistry::resolve($bookingTheme ?? 'classic');
    $datePickerStyle   = in_array($datePickerStyle ?? 'monthly', ['monthly','weekly']) ? $datePickerStyle : 'monthly';
@endphp

@include("livewire.booking.themes.{$bookingTheme}.layout")

</div>
