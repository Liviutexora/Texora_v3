@php
    use App\Helpers\DemoModeHelper;
@endphp

@if(DemoModeHelper::shouldShowBanner())
<style>
.demo-mode-banner{background:linear-gradient(135deg,#fef3c7 0%,#fde68a 100%);border-bottom:2px solid #f59e0b;box-shadow:0 2px 8px rgba(0,0,0,.1);position:sticky;top:0;z-index:50;overflow:hidden;width:100%}
.demo-mode-banner::before{content:'';position:absolute;top:0;left:0;right:0;bottom:0;background:repeating-linear-gradient(45deg,transparent,transparent 10px,rgba(245,158,11,.05) 10px,rgba(245,158,11,.05) 20px);pointer-events:none}
.demo-mode-banner-content{position:relative;z-index:1;display:flex;align-items:center;gap:12px;width:100%;padding:10px 20px}
.demo-mode-icon{flex-shrink:0;width:20px;height:20px;color:#d97706;animation:dmb-pulse 2s cubic-bezier(.4,0,.6,1) infinite}
@keyframes dmb-pulse{0%,100%{opacity:1}50%{opacity:.7}}
.demo-mode-text{font-size:14px;font-weight:600;color:#92400e;letter-spacing:.025em;line-height:1.5}
.demo-mode-badge{display:inline-flex;align-items:center;padding:3px 10px;background:rgba(217,119,6,.15);border:1px solid rgba(217,119,6,.3);border-radius:6px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#92400e;margin-left:auto;white-space:nowrap}
.dark .demo-mode-banner,[data-theme=dark] .demo-mode-banner{background:linear-gradient(135deg,#78350f 0%,#92400e 100%);border-bottom-color:#d97706}
.dark .demo-mode-text,[data-theme=dark] .demo-mode-text{color:#fde68a}
.dark .demo-mode-icon,[data-theme=dark] .demo-mode-icon{color:#fbbf24}
.dark .demo-mode-badge,[data-theme=dark] .demo-mode-badge{background:rgba(251,191,36,.2);border-color:rgba(251,191,36,.4);color:#fde68a}
@media(max-width:640px){.demo-mode-banner-content{padding:9px 14px;gap:8px}.demo-mode-text{font-size:13px}.demo-mode-badge{display:none}}
</style>

<div class="demo-mode-banner">
    <div class="demo-mode-banner-content">
        <svg class="demo-mode-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <span class="demo-mode-text">
            {{ DemoModeHelper::getBannerMessage() }}
        </span>
        <span class="demo-mode-badge">
            {{ __('Demo Mode') }}
        </span>
    </div>
</div>

@vite('resources/js/demo-mode-banner.js')
@endif
