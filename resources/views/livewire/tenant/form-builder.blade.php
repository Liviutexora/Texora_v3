@php
$fbIcon = function(string $type, string $color = '#6b7280', int $size = 14): string {
    $s = $size;
    return match ($type) {
        'short_text'  => "<svg width='$s' height='$s' viewBox='0 0 24 24' fill='none' stroke='$color' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><line x1='3' y1='6' x2='21' y2='6'/><line x1='3' y1='12' x2='15' y2='12'/><line x1='3' y1='18' x2='18' y2='18'/></svg>",
        'email'       => "<svg width='$s' height='$s' viewBox='0 0 24 24' fill='none' stroke='$color' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><rect x='2' y='4' width='20' height='16' rx='2'/><polyline points='2,4 12,13 22,4'/></svg>",
        'phone'       => "<svg width='$s' height='$s' viewBox='0 0 24 24' fill='none' stroke='$color' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.57 3.41 2 2 0 0 1 3.54 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.59a16 16 0 0 0 6 6l.9-.9a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z'/></svg>",
        'dropdown'    => "<svg width='$s' height='$s' viewBox='0 0 24 24' fill='none' stroke='$color' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'/></svg>",
        'date_picker' => "<svg width='$s' height='$s' viewBox='0 0 24 24' fill='none' stroke='$color' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><rect x='3' y='4' width='18' height='18' rx='2'/><line x1='3' y1='9' x2='21' y2='9'/><line x1='8' y1='2' x2='8' y2='6'/><line x1='16' y1='2' x2='16' y2='6'/></svg>",
        'time_slot'   => "<svg width='$s' height='$s' viewBox='0 0 24 24' fill='none' stroke='$color' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><circle cx='12' cy='12' r='10'/><polyline points='12 6 12 12 16 14'/></svg>",
        'file_upload' => "<svg width='$s' height='$s' viewBox='0 0 24 24' fill='none' stroke='$color' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='16 16 12 12 8 16'/><line x1='12' y1='12' x2='12' y2='21'/><path d='M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3'/></svg>",
        'checkbox'    => "<svg width='$s' height='$s' viewBox='0 0 24 24' fill='none' stroke='$color' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='20 6 9 17 4 12'/></svg>",
        'radio_group' => "<svg width='$s' height='$s' viewBox='0 0 24 24' fill='none' stroke='$color' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><circle cx='12' cy='12' r='10'/><circle cx='12' cy='12' r='4' fill='$color'/></svg>",
        'signature'   => "<svg width='$s' height='$s' viewBox='0 0 24 24' fill='none' stroke='$color' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M12 20h9'/><path d='M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z'/></svg>",
        default       => "<svg width='$s' height='$s' viewBox='0 0 24 24' fill='none' stroke='$color' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><rect x='3' y='3' width='18' height='18' rx='2'/></svg>",
    };
};
$typeColors = collect($fieldTypes)->keyBy('key')->map(fn($t) => $t['color']);
$typeLabels = collect($fieldTypes)->keyBy('key')->map(fn($t) => $t['label']);
$tabList = [
    ['key'=>'settings',     'label'=>__('General')],
    ['key'=>'services',     'label'=>__('Services')],
    ['key'=>'providers',    'label'=>__('Providers')],
    ['key'=>'builder',      'label'=>__('Form Builder')],
    ['key'=>'themes',       'label'=>__('Themes')],
    ['key'=>'success_page', 'label'=>__('Success page')],
    ['key'=>'sharing',      'label'=>__('Sharing')],
    ['key'=>'preview',      'label'=>__('Preview')],
];
$presetColors = ['#7c3aed','#6366f1','#3b82f6','#06b6d4','#10b981','#f59e0b','#ef4444','#ec4899','#111827','#374151'];
$inputFocus = "onfocus=\"this.style.borderColor='#7c3aed';this.style.boxShadow='0 0 0 3px #7c3aed18'\" onblur=\"this.style.borderColor='';this.style.boxShadow='none'\"";
$currSym = ['INR'=>'₹','USD'=>'$','GBP'=>'£','EUR'=>'€','AED'=>'AED ','SGD'=>'S$','AUD'=>'A$'][$tenantCurrency] ?? $tenantCurrency.' ';
@endphp

<div>

{{-- ── Toast ──────────────────────────────────────────────────── --}}
<div x-data="{ show:false, msg:'', kind:'success' }"
     x-on:form-builder-saved.window="msg=@js(__('Saved successfully.')); kind='success'; show=true; setTimeout(()=>show=false,2500)"
     x-on:toast-error.window="msg=$event.detail.message; kind='error'; show=true; setTimeout(()=>show=false,3500)"
     x-on:toast-warning.window="msg=$event.detail.message; kind='warning'; show=true; setTimeout(()=>show=false,6000)"
     x-show="show"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-1"
     :style="`position:fixed;bottom:24px;right:24px;z-index:9999;color:#fff;
              padding:10px 18px;border-radius:10px;font-size:13px;font-weight:500;
              display:flex;align-items:flex-start;gap:10px;box-shadow:0 4px 24px rgba(0,0,0,.22);pointer-events:none;max-width:340px;
              background:${kind==='warning'?'#92400e':kind==='error'?'#7f1d1d':'#18181b'}`">
    {{-- success / saved --}}
    <svg x-show="kind==='success'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px"><polyline points="20 6 9 17 4 12"/></svg>
    {{-- warning (plan limit) --}}
    <svg x-show="kind==='warning'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    {{-- error --}}
    <svg x-show="kind==='error'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
    <span x-text="msg" style="line-height:1.45"></span>
</div>

{{-- ── Top header ──────────────────────────────────────────────── --}}
<div style="display:flex;align-items:center;justify-content:space-between;padding-bottom:0;">
    <div style="display:flex;align-items:center;gap:6px;">
        <span style="font-size:13px;color:var(--fb-text-3);cursor:pointer;" onmouseover="this.style.color='var(--fb-text-2)'" onmouseout="this.style.color='var(--fb-text-3)'">{{ __('Forms') }}</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        <span style="font-size:13px;font-weight:600;color:var(--fb-text);">{{ $formTitle }}</span>
        <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 9px;background:#dcfce7;color:#15803d;font-size:11.5px;font-weight:600;border-radius:20px;margin-left:2px;">
            <svg width="7" height="7" viewBox="0 0 8 8" fill="#16a34a"><circle cx="4" cy="4" r="4"/></svg>
            Live
        </span>
    </div>
    <div style="display:flex;align-items:center;gap:8px;">
        @if($bookingUrl)
        <a href="{{ $bookingUrl }}" target="_blank"
           style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;font-size:13px;font-weight:500;font-family:inherit;border:1px solid var(--fb-border);border-radius:8px;background:var(--fb-bg);color:var(--fb-text-2);text-decoration:none;transition:background .12s;"
           onmouseover="this.style.background='var(--fb-bg-muted)'" onmouseout="this.style.background='var(--fb-bg)'">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            {{ __('Preview') }}
        </a>
        @endif
        @if($activeTab === 'builder')
            @if($isDirty)
            <button wire:click="discard" style="padding:7px 14px;font-size:13px;font-weight:500;font-family:inherit;border:1px solid var(--fb-border);border-radius:8px;background:var(--fb-bg);color:var(--fb-text-2);cursor:pointer;transition:background .12s;" onmouseover="this.style.background='var(--fb-bg-muted)'" onmouseout="this.style.background='var(--fb-bg)'">{{ __('Discard') }}</button>
            <button wire:click="save" wire:loading.attr="disabled"
                    style="padding:7px 18px;font-size:13px;font-weight:600;font-family:inherit;border:none;border-radius:8px;background:#7c3aed;color:#fff;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:opacity .15s;" wire:loading.class="opacity-60">
                <span wire:loading.remove wire:target="save">{{ __('Publish changes') }}</span>
                <span wire:loading wire:target="save">{{ __('Saving…') }}</span>
            </button>
            @else
            <button disabled style="padding:7px 18px;font-size:13px;font-weight:600;font-family:inherit;border:none;border-radius:8px;background:var(--fb-border);color:var(--fb-text-4);cursor:default;">{{ __('Publish changes') }}</button>
            @endif
        @endif
    </div>
</div>

{{-- ── Tab bar ──────────────────────────────────────────────────── --}}
<div style="display:flex;border-bottom:1px solid var(--fb-border);margin-top:4px;margin-bottom:16px;overflow-x:auto;">
    @foreach($tabList as $tab)
    @php $isActive = $activeTab === $tab['key']; @endphp
    <button wire:click="setTab('{{ $tab['key'] }}')"
            style="padding:11px 16px;font-size:13px;font-weight:{{ $isActive?'600':'500' }};font-family:inherit;border:none;background:transparent;cursor:pointer;color:{{ $isActive?'var(--fb-text)':'var(--fb-text-3)' }};border-bottom:{{ $isActive?'2px solid var(--fb-text)':'2px solid transparent' }};margin-bottom:-1px;white-space:nowrap;transition:color .12s;"
            @if(!$isActive) onmouseover="this.style.color='var(--fb-text-2)'" onmouseout="this.style.color='var(--fb-text-3)'" @endif>
        {{ $tab['label'] }}
    </button>
    @endforeach
</div>

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- ══ BUILDER TAB ════════════════════════════════════════════ --}}
{{-- ════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'builder')
<div style="display:flex;border:1px solid var(--fb-border);border-radius:12px;overflow:hidden;height:calc(100vh - 260px);min-height:480px;">

    {{-- LEFT --}}
    <div style="width:196px;flex-shrink:0;border-right:1px solid var(--fb-border);background:var(--fb-bg-subtle);overflow-y:auto;padding:16px 12px;">
        <div style="font-size:10px;font-weight:700;color:var(--fb-text-3);letter-spacing:.7px;text-transform:uppercase;margin-bottom:10px;">{{ __('Add Field') }}</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
            @foreach ($fieldTypes as $ft)
            @php $fc = $ft['color']; @endphp
            <button wire:click="addField('{{ $ft['key'] }}')"
                style="display:flex;flex-direction:column;align-items:center;gap:5px;padding:11px 6px 9px;background:var(--fb-bg);border:1px solid var(--fb-border);border-radius:8px;cursor:pointer;font-family:inherit;transition:border-color .12s,background .12s;text-align:center;"
                onmouseover="this.style.borderColor='{{ $fc }}';this.style.background='{{ $fc }}11';"
                onmouseout="this.style.borderColor='';this.style.background='var(--fb-bg)';">
                <div style="width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:{{ $fc }}1a;">{!! $fbIcon($ft['key'],$fc,15) !!}</div>
                <span style="font-size:10.5px;font-weight:500;color:var(--fb-text-2);line-height:1.2;">{{ $ft['label'] }}</span>
            </button>
            @endforeach
        </div>
        <div style="margin-top:14px;padding:10px 10px 10px 12px;background:var(--fb-tip-bg);border-radius:8px;border-left:3px solid #7c3aed;">
            <div style="font-size:11px;font-weight:600;color:var(--fb-tip-title);margin-bottom:3px;">{{ __('💡 Tip') }}</div>
            <div style="font-size:11px;color:var(--fb-tip-text);line-height:1.5;">{{ __('Drag fields to reorder. Click any field to edit its properties.') }}</div>
        </div>
    </div>

    {{-- CENTER --}}
    <div style="flex:1;overflow:hidden;background:var(--fb-bg);display:flex;flex-direction:column;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px 13px;border-bottom:1px solid var(--fb-border-subtle);flex-shrink:0;">
            <div>
                <div style="font-size:11px;color:var(--fb-text-4);font-weight:500;margin-bottom:2px;">{{ __('Form structure') }}</div>
                <div style="font-size:14px;font-weight:700;color:var(--fb-text);">
                    {{ $fieldCount }} {{ trans_choice(__('field|fields'), $fieldCount) }}
                    @if($requiredCount)<span style="color:var(--fb-text-3);font-weight:500;"> · {{ $requiredCount }} {{ __('required') }}</span>@endif
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:5px;">
                <button wire:click="undo" {{ $canUndo?'':'disabled' }}
                    style="padding:5px 13px;font-size:12.5px;font-weight:500;font-family:inherit;border:1px solid var(--fb-border);border-radius:7px;background:var(--fb-bg);color:{{ $canUndo?'var(--fb-text-2)':'var(--fb-text-5)' }};cursor:{{ $canUndo?'pointer':'default' }};transition:background .12s;"
                    @if($canUndo) onmouseover="this.style.background='var(--fb-bg-raised)'" onmouseout="this.style.background='var(--fb-bg)'" @endif>{{ __('Undo') }}</button>
                <button wire:click="redo" {{ $canRedo?'':'disabled' }}
                    style="padding:5px 13px;font-size:12.5px;font-weight:500;font-family:inherit;border:1px solid var(--fb-border);border-radius:7px;background:var(--fb-bg);color:{{ $canRedo?'var(--fb-text-2)':'var(--fb-text-5)' }};cursor:{{ $canRedo?'pointer':'default' }};transition:background .12s;"
                    @if($canRedo) onmouseover="this.style.background='var(--fb-bg-raised)'" onmouseout="this.style.background='var(--fb-bg)'" @endif>{{ __('Redo') }}</button>
            </div>
        </div>

        <div style="flex:1;overflow-y:auto;padding:16px 20px;">
        @if(count($fields)===0)
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;min-height:280px;color:var(--fb-text-4);text-align:center;gap:12px;">
            <div style="width:56px;height:56px;border-radius:14px;background:var(--fb-bg-raised);display:flex;align-items:center;justify-content:center;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
            </div>
            <div>
                <div style="font-size:14px;font-weight:600;color:var(--fb-text-2);margin-bottom:4px;">{{ __('No fields yet') }}</div>
                <div style="font-size:13px;color:var(--fb-text-3);">{{ __('Click a field type on the left to add your first field.') }}</div>
            </div>
        </div>
        @else
        <div wire:key="fb-sortable-{{ $tenantId }}"
             id="fb-sortable-{{ $tenantId }}" style="display:flex;flex-direction:column;gap:8px;">
            @foreach($fields as $field)
            @php
                $sel   = $selectedId === $field['id'];
                $fc    = $typeColors->get($field['type'],'#6b7280');
                $flabel = $typeLabels->get($field['type'],$field['type']);
                $fid   = $field['id'];
            @endphp
            <div
                wire:key="fb-field-{{ $fid }}"
                data-field-id="{{ $fid }}"
                wire:click="selectField('{{ $fid }}')"
                style="position:relative;padding:13px 44px 13px 10px;border-radius:10px;cursor:pointer;transition:all .12s;border:{{ $sel?'1.5px solid '.$fc:'1px solid var(--fb-border)' }};background:{{ $sel?$fc.'0d':'var(--fb-bg)' }};box-shadow:{{ $sel?'0 0 0 3px '.$fc.'18':'0 1px 2px rgba(0,0,0,.04)' }};"
            >
                <div style="position:absolute;top:10px;right:10px;display:flex;gap:3px;z-index:1;">
                    <button wire:click.stop="duplicateField('{{ $fid }}')"
                        style="width:26px;height:26px;border-radius:6px;border:none;background:transparent;color:var(--fb-text-5);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .12s;"
                        onmouseover="this.style.background='#ede9fe';this.style.color='#7c3aed';"
                        onmouseout="this.style.background='transparent';this.style.color='var(--fb-text-5)';" title="{{ __('Duplicate') }}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    </button>
                    <button wire:click.stop="confirmDeleteField('{{ $fid }}')"
                        style="width:26px;height:26px;border-radius:6px;border:none;background:transparent;color:var(--fb-text-5);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .12s;"
                        onmouseover="this.style.background='#fee2e2';this.style.color='#dc2626';"
                        onmouseout="this.style.background='transparent';this.style.color='var(--fb-text-5)';" title="{{ __('Remove') }}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </button>
                </div>
                <div style="display:flex;align-items:flex-start;gap:10px;pointer-events:none;">
                    <div class="fb-handle" style="display:flex;align-items:center;padding-top:3px;color:var(--fb-text-5);cursor:grab;flex-shrink:0;pointer-events:auto;" title="{{ __('Drag to reorder') }}">
                        <svg width="12" height="16" viewBox="0 0 12 16" fill="currentColor"><circle cx="3.5" cy="3.5" r="1.5"/><circle cx="8.5" cy="3.5" r="1.5"/><circle cx="3.5" cy="8" r="1.5"/><circle cx="8.5" cy="8" r="1.5"/><circle cx="3.5" cy="12.5" r="1.5"/><circle cx="8.5" cy="12.5" r="1.5"/></svg>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:7px;margin-bottom:5px;flex-wrap:wrap;">
                            <div style="display:inline-flex;align-items:center;gap:5px;padding:2px 8px 2px 6px;border-radius:5px;background:{{ $fc }}18;">
                                {!! $fbIcon($field['type'],$fc,12) !!}
                                <span style="font-size:11.5px;font-weight:600;color:{{ $fc }};">{{ $flabel }}</span>
                            </div>
                            @if($field['required'])<span style="font-size:11.5px;font-weight:600;color:#dc2626;">{{ __('Required') }}</span>@endif
                            @if($field['hidden'])<span style="font-size:11px;font-weight:500;color:var(--fb-text-4);padding:1px 6px;background:var(--fb-bg-raised);border-radius:4px;">{{ __('Hidden') }}</span>@endif
                        </div>
                        <div style="font-size:14px;font-weight:700;color:var(--fb-text);margin-bottom:7px;">
                            {{ $field['label']?:__('(no label)') }}@if($field['required'])<span style="color:#dc2626;margin-left:1px;">*</span>@endif
                        </div>
                        @if(in_array($field['type'],['short_text','email','phone']))
                        <div style="padding:8px 12px;background:var(--fb-bg-muted);border:1px solid var(--fb-border);border-radius:8px;font-size:13px;color:var(--fb-text-4);pointer-events:none;">{{ $field['placeholder']?:__('Type your answer') }}</div>
                        @elseif($field['type']==='dropdown')
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:var(--fb-bg-muted);border:1px solid var(--fb-border);border-radius:8px;font-size:13px;color:var(--fb-text-4);pointer-events:none;"><span>{{ $field['placeholder']?:__('Pick one') }}</span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></div>
                        @elseif($field['type']==='checkbox')
                        <div style="display:flex;align-items:center;gap:8px;pointer-events:none;"><div style="width:16px;height:16px;border:1.5px solid #d1d5db;border-radius:4px;background:var(--fb-bg-muted);flex-shrink:0;"></div><span style="font-size:13px;color:var(--fb-text-3);">{{ $field['label'] }}</span></div>
                        @elseif($field['type']==='radio_group')
                        <div style="display:flex;flex-direction:column;gap:5px;pointer-events:none;">@foreach(array_slice($field['options']??[],0,3) as $opt)<div style="display:flex;align-items:center;gap:8px;"><div style="width:15px;height:15px;border:1.5px solid #d1d5db;border-radius:50%;background:var(--fb-bg-muted);flex-shrink:0;"></div><span style="font-size:12.5px;color:var(--fb-text-3);">{{ $opt }}</span></div>@endforeach</div>
                        @elseif($field['type']==='date_picker')
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:var(--fb-bg-muted);border:1px solid var(--fb-border);border-radius:8px;font-size:13px;color:var(--fb-text-4);pointer-events:none;"><span>{{ __('MM / DD / YYYY') }}</span>{!! $fbIcon('date_picker','#d1d5db',14) !!}</div>
                        @elseif($field['type']==='time_slot')
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:var(--fb-bg-muted);border:1px solid var(--fb-border);border-radius:8px;font-size:13px;color:var(--fb-text-4);pointer-events:none;"><span>{{ __('HH : MM') }}</span>{!! $fbIcon('time_slot','#d1d5db',14) !!}</div>
                        @elseif($field['type']==='file_upload')
                        <div style="padding:16px;background:var(--fb-bg-muted);border:2px dashed var(--fb-border);border-radius:8px;text-align:center;pointer-events:none;">{!! $fbIcon('file_upload','#d1d5db',20) !!}<div style="font-size:11.5px;color:var(--fb-text-4);margin-top:5px;">{{ __('Click to upload') }}</div></div>
                        @elseif($field['type']==='signature')
                        <div style="padding:16px;background:var(--fb-bg-muted);border:1px solid var(--fb-border);border-radius:8px;text-align:center;pointer-events:none;"><div style="font-size:12px;color:var(--fb-text-4);font-style:italic;">{{ __('Sign here') }}</div><div style="border-top:1px solid var(--fb-border);margin-top:10px;"></div></div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <button wire:click="addField('short_text')"
            style="margin-top:12px;width:100%;padding:13px;font-size:13px;font-weight:500;font-family:inherit;border:1.5px dashed var(--fb-text-5);border-radius:10px;background:transparent;color:var(--fb-text-3);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;transition:all .12s;"
            onmouseover="this.style.borderColor='#7c3aed';this.style.color='#7c3aed';this.style.background='var(--fb-hover-selected-bg)';"
            onmouseout="this.style.borderColor='var(--fb-text-5)';this.style.color='var(--fb-text-3)';this.style.background='transparent';">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            {{ __('Add field') }}
        </button>
        </div>
    </div>

    {{-- RIGHT --}}
    <div style="width:256px;flex-shrink:0;border-left:1px solid var(--fb-border);background:var(--fb-bg);overflow-y:auto;">
        @if($selectedField)
        @php
            $sid    = $selectedField['id'];
            $sfc    = $typeColors->get($selectedField['type'],'#6b7280');
            $slabel = $typeLabels->get($selectedField['type'],$selectedField['type']);
            $hasOpts= in_array($selectedField['type'],['dropdown','radio_group']);
            $hasPh  = !in_array($selectedField['type'],['checkbox','radio_group','file_upload','signature','date_picker','time_slot']);
        @endphp
        <div wire:key="field-props-{{ $sid }}">
        <div style="padding:14px 16px 13px;border-bottom:1px solid var(--fb-border);background:var(--fb-bg-subtle);">
            <div style="display:flex;align-items:center;gap:7px;margin-bottom:9px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                <span style="font-size:13px;font-weight:600;color:var(--fb-text-2);">{{ __('Field properties') }}</span>
            </div>
            <div style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;background:{{ $sfc }}15;border-radius:7px;">
                {!! $fbIcon($selectedField['type'],$sfc,13) !!}
                <span style="font-size:12px;font-weight:600;color:{{ $sfc }};">{{ $slabel }}</span>
                <span style="font-size:11px;color:var(--fb-text-4);margin-left:1px;">· {{ $sid }}</span>
            </div>
        </div>
        <div style="padding:16px;">
            <div style="margin-bottom:14px;">
                <label class="fb-label">{{ __('Label') }}</label>
                <input type="text" value="{{ $selectedField['label'] }}"
                    @change="$wire.updateProp('{{ $sid }}','label',$event.target.value)"
                    class="fb-input">
            </div>
            @if($hasPh)
            <div style="margin-bottom:14px;">
                <label class="fb-label">{{ __('Placeholder') }}</label>
                <input type="text" value="{{ $selectedField['placeholder'] }}"
                    @change="$wire.updateProp('{{ $sid }}','placeholder',$event.target.value)"
                    class="fb-input">
            </div>
            @endif
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-top:1px solid var(--fb-border-subtle);">
                <div><div style="font-size:13.5px;font-weight:500;color:var(--fb-text);">{{ __('Required') }}</div><div style="font-size:11.5px;color:var(--fb-text-4);margin-top:2px;">{{ __('Customers must fill this in') }}</div></div>
                <button wire:click="updateProp('{{ $sid }}','required',{{ $selectedField['required']?'false':'true' }})"
                    style="position:relative;width:42px;height:24px;border-radius:12px;border:none;cursor:pointer;flex-shrink:0;outline:none;transition:background .2s;background:{{ $selectedField['required']?'#7c3aed':'var(--fb-border)' }};">
                    <span style="position:absolute;top:3px;width:18px;height:18px;background:var(--fb-bg);border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,.25);transition:left .2s;left:{{ $selectedField['required']?'21px':'3px' }};"></span>
                </button>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-top:1px solid var(--fb-border-subtle);">
                <div><div style="font-size:13.5px;font-weight:500;color:var(--fb-text);">{{ __('Always hidden') }}</div><div style="font-size:11.5px;color:var(--fb-text-4);margin-top:2px;">{{ __('Never shown to customers') }}</div></div>
                <button wire:click="updateProp('{{ $sid }}','hidden',{{ $selectedField['hidden']?'false':'true' }})"
                    style="position:relative;width:42px;height:24px;border-radius:12px;border:none;cursor:pointer;flex-shrink:0;outline:none;transition:background .2s;background:{{ $selectedField['hidden']?'#7c3aed':'var(--fb-border)' }};">
                    <span style="position:absolute;top:3px;width:18px;height:18px;background:var(--fb-bg);border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,.25);transition:left .2s;left:{{ $selectedField['hidden']?'21px':'3px' }};"></span>
                </button>
            </div>

            {{-- Conditional logic: show this field only when another field matches a value --}}
            @php
                $otherFields = collect($fields)->filter(fn($f) => $f['id'] !== $sid)->values();
            @endphp
            <div style="padding:12px 0;border-top:1px solid var(--fb-border-subtle);">
                <div style="font-size:13.5px;font-weight:500;color:var(--fb-text);margin-bottom:2px;">{{ __('Show only when') }}</div>
                <div style="font-size:11.5px;color:var(--fb-text-4);margin-bottom:10px;">{{ __('Hide this field unless another field matches a value') }}</div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <div>
                        <label class="fb-label">{{ __('Field') }}</label>
                        <select
                            @change="$wire.updateProp('{{ $sid }}','condition_field',$event.target.value||null)"
                            class="fb-input fb-select">
                            <option value="">{{ __('— Always show —') }}</option>
                            @foreach($otherFields as $otherF)
                                <option value="{{ $otherF['id'] }}" {{ ($selectedField['condition_field'] ?? '') === $otherF['id'] ? 'selected' : '' }}>
                                    {{ $otherF['label'] ?: $otherF['id'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if(!empty($selectedField['condition_field']))
                    <div>
                        <label class="fb-label">{{ __('Equals') }}</label>
                        <input type="text"
                            value="{{ $selectedField['condition_value'] ?? '' }}"
                            placeholder="{{ __('Value to match…') }}"
                            @change="$wire.updateProp('{{ $sid }}','condition_value',$event.target.value)"
                            class="fb-input">
                    </div>
                    @endif
                </div>
            </div>
            @if($hasOpts)
            <div style="padding-top:12px;border-top:1px solid var(--fb-border-subtle);">
                <div class="fb-label">{{ __('Options') }}</div>
                <div style="display:flex;flex-direction:column;gap:5px;">
                    @foreach($selectedField['options'] as $oi=>$opt)
                    <div style="display:flex;align-items:center;gap:5px;">
                        <input type="text" value="{{ $opt }}"
                            @change="$wire.updateOption('{{ $sid }}',{{ $oi }},$event.target.value)"
                            style="flex:1;padding:7px 9px;border:1px solid var(--fb-border);border-radius:7px;font-size:12.5px;font-family:inherit;color:var(--fb-text);outline:none;box-sizing:border-box;transition:border .15s;"
                            onfocus="this.style.borderColor='#7c3aed'" onblur="this.style.borderColor=''">
                        <button wire:click="removeOption('{{ $sid }}',{{ $oi }})"
                            style="width:26px;height:26px;border:none;border-radius:6px;background:transparent;color:var(--fb-text-4);cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;"
                            onmouseover="this.style.background='#fee2e2';this.style.color='#dc2626'" onmouseout="this.style.background='transparent';this.style.color='var(--fb-text-4)'">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                    @endforeach
                </div>
                <button wire:click="addOption('{{ $sid }}')"
                    style="margin-top:8px;width:100%;padding:7px;font-size:12px;font-weight:500;font-family:inherit;border:1.5px dashed var(--fb-text-5);border-radius:7px;background:transparent;color:var(--fb-text-3);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:5px;"
                    onmouseover="this.style.borderColor='#7c3aed';this.style.color='#7c3aed'" onmouseout="this.style.borderColor='var(--fb-text-5)';this.style.color='var(--fb-text-3)'">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add option
                </button>
            </div>
            @endif
        </div>
        </div>{{-- /wire:key field-props --}}
        @else
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;padding:24px;text-align:center;color:var(--fb-text-4);">
            <div style="width:44px;height:44px;border-radius:11px;background:var(--fb-bg-raised);margin-bottom:12px;display:flex;align-items:center;justify-content:center;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            </div>
            <div style="font-size:13px;font-weight:600;color:var(--fb-text-2);margin-bottom:4px;">{{ __('Field properties') }}</div>
            <div style="font-size:12px;color:var(--fb-text-4);line-height:1.5;">{{ __('Click any field to edit its label, placeholder and validation.') }}</div>
        </div>
        @endif
    </div>
</div>

{{-- Delete field confirm modal --}}
@if($showDeleteFieldConfirm)
<div style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px;"
    wire:click.self="cancelDeleteField">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(2px);"></div>
    <div style="position:relative;width:100%;max-width:400px;background:var(--fb-bg);border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,0.18),0 0 0 1px rgba(0,0,0,0.06);overflow:hidden;">
        <div style="padding:24px 24px 0;display:flex;align-items:flex-start;gap:16px;">
            <div style="width:44px;height:44px;border-radius:12px;background:#fef2f2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
            </div>
            <div style="flex:1;">
                <div style="font-size:16px;font-weight:700;color:var(--fb-text);line-height:1.3;">{{ __('Delete field?') }}</div>
                <div style="font-size:13px;color:var(--fb-text-3);margin-top:6px;line-height:1.5;">
                    <strong style="color:var(--fb-text);">{{ $deleteFieldLabel }}</strong> {{ __('will be permanently removed from your form.') }}
                </div>
            </div>
            <button wire:click="cancelDeleteField"
                style="width:28px;height:28px;flex-shrink:0;border:none;background:var(--fb-bg-raised);border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--fb-text-3);"
                onmouseover="this.style.background='var(--fb-border)'" onmouseout="this.style.background='var(--fb-bg-raised)'">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div style="padding:20px 24px 24px;display:flex;gap:10px;justify-content:flex-end;margin-top:12px;">
            <button wire:click="cancelDeleteField"
                style="padding:9px 18px;font-size:13px;font-weight:500;font-family:inherit;border:1px solid var(--fb-border);border-radius:9px;background:var(--fb-bg);color:var(--fb-text-2);cursor:pointer;transition:background .12s;"
                onmouseover="this.style.background='var(--fb-bg-muted)'" onmouseout="this.style.background='var(--fb-bg)'">
                {{ __('Cancel') }}
            </button>
            <button wire:click="deleteField('{{ $deleteFieldId }}')" wire:loading.attr="disabled"
                style="padding:9px 20px;font-size:13px;font-weight:600;font-family:inherit;border:none;border-radius:9px;background:#dc2626;color:#fff;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:opacity .15s;" wire:loading.class="opacity-60">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:-2px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                {{ __('Delete field') }}
            </button>
        </div>
    </div>
</div>
@endif

@endif

@script
<script>
(function () {
    'use strict';
    const id = 'fb-sortable-{{ $tenantId }}';

    function init() {
        const el = document.getElementById(id);
        if (!el || el.__sortable) return;
        el.__sortable = Sortable.create(el, {
            animation: 150,
            handle: '.fb-handle',
            ghostClass: 'fb-ghost',
            chosenClass: 'fb-chosen',
            forceFallback: false,
            onEnd: function () {
                const ids = Array.from(el.querySelectorAll('[data-field-id]')).map(function (e) { return e.dataset.fieldId; });
                $wire.reorder(ids);
            }
        });
    }

    // Run immediately (covers builder as the initial tab)
    init();

    // Watch for the sortable container to appear after tab switches.
    // MutationObserver fires after every Livewire DOM patch, so this
    // handles the case where $activeTab starts as something other than
    // 'builder' and the user switches to it later.
    new MutationObserver(function () { init(); })
        .observe(document.body, { childList: true, subtree: true });
})();
</script>
@endscript

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- ══ SERVICES TAB ══════════════════════════════════════════ --}}
{{-- ════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'services')
@php
    $activeCount = $services->where('is_active', true)->count();
@endphp
<div style="background:var(--fb-bg);border:1px solid var(--fb-border);border-radius:14px;padding:24px;">
<div style="display:flex;gap:24px;align-items:flex-start;">

    {{-- Left: main services panel --}}
    <div style="flex:1;min-width:0;">

        {{-- Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <div>
                <div style="font-size:22px;font-weight:700;color:var(--fb-text);letter-spacing:-.3px;">{{ __('Services') }}</div>
                <div style="font-size:13px;color:var(--fb-text-3);margin-top:3px;">{{ $services->count() }} {{ trans_choice(__('service|services'), $services->count()) }} · {{ $activeCount }} {{ __('active') }} · {{ __('shown to customers in step 1 of the booking flow') }}</div>
            </div>
            <button wire:click="openServiceCreate"
                style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;font-size:13px;font-weight:600;font-family:inherit;border:none;border-radius:8px;background:#7c3aed;color:#fff;cursor:pointer;white-space:nowrap;transition:opacity .15s;"
                onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                {{ __('New service') }}
            </button>
        </div>

        {{-- Table --}}
        <div style="border:1px solid var(--fb-border);border-radius:12px;overflow:hidden;background:var(--fb-bg);">
            {{-- Column headers --}}
            <div style="display:grid;grid-template-columns:1fr 100px 90px 110px 36px;padding:10px 20px;background:var(--fb-bg-muted);border-bottom:1px solid var(--fb-border);">
                <span style="font-size:11px;font-weight:700;color:var(--fb-text-4);text-transform:uppercase;letter-spacing:.06em;">{{ __('Service') }}</span>
                <span style="font-size:11px;font-weight:700;color:var(--fb-text-4);text-transform:uppercase;letter-spacing:.06em;text-align:center;">{{ __('Duration') }}</span>
                <span style="font-size:11px;font-weight:700;color:var(--fb-text-4);text-transform:uppercase;letter-spacing:.06em;text-align:center;">{{ __('Price') }}</span>
                <span style="font-size:11px;font-weight:700;color:var(--fb-text-4);text-transform:uppercase;letter-spacing:.06em;text-align:center;">{{ __('Status') }}</span>
                <span></span>
            </div>

            {{-- Rows --}}
            @forelse($services as $svc)
            @php
                $isSel       = $editingServiceId === $svc->id;
                $pCount      = $svc->providers_count ?? 0;
                $subtext     = collect([$svc->category, $pCount ? $pCount.' '.trans_choice(__('provider|providers'), $pCount) : null])->filter()->implode(' · ');
            @endphp
            <div wire:click="openServiceEdit({{ $svc->id }})"
                 style="display:grid;grid-template-columns:1fr 100px 90px 110px 36px;padding:14px 20px;border-bottom:1px solid var(--fb-border-subtle);border-left:3px solid {{ $isSel ? '#7c3aed' : 'transparent' }};background:{{ $isSel ? 'var(--fb-hover-selected-bg)' : 'var(--fb-bg)' }};cursor:pointer;transition:background .1s;"
                 onmouseover="this.style.background='{{ $isSel ? 'var(--fb-hover-selected-bg)' : 'var(--fb-bg-muted)' }}'" onmouseout="this.style.background='{{ $isSel ? 'var(--fb-hover-selected-bg)' : 'var(--fb-bg)' }}'">
                <div>
                    <div style="font-size:14px;font-weight:600;color:{{ $isSel ? '#7c3aed' : 'var(--fb-text)' }};">{{ $svc->name }}</div>
                    @if($subtext)<div style="font-size:12px;color:var(--fb-text-4);margin-top:2px;">{{ $subtext }}</div>@endif
                </div>
                <div style="font-size:13px;color:var(--fb-text-2);text-align:center;align-self:center;">{{ $svc->duration_label }}</div>
                <div style="font-size:13px;color:var(--fb-text-2);text-align:center;align-self:center;font-weight:500;">{{ $svc->price > 0 ? $currSym.number_format($svc->price, 0) : __('Free') }}</div>
                <div style="text-align:center;align-self:center;">
                    <button wire:click.stop="toggleServiceActive({{ $svc->id }})"
                        title="{{ $svc->is_active ? __('Click to deactivate') : __('Click to activate') }}"
                        style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;border:none;cursor:pointer;font-size:12px;font-weight:600;transition:opacity .15s;{{ $svc->is_active ? 'background:#dcfce7;color:#15803d;' : 'background:var(--fb-bg-raised);color:var(--fb-text-3);' }}"
                        onmouseover="this.style.opacity='.7'" onmouseout="this.style.opacity='1'">
                        <span style="width:6px;height:6px;border-radius:50%;{{ $svc->is_active ? 'background:#22c55e;' : 'border:1.5px solid var(--fb-text-4);' }}"></span>
                        {{ $svc->is_active ? __('Live') : __('Off') }}
                    </button>
                </div>
                <div style="align-self:center;text-align:center;">
                    <button wire:click.stop="confirmDeleteService({{ $svc->id }})"
                        style="width:28px;height:28px;border:none;background:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;color:var(--fb-text-5);border-radius:6px;padding:0;"
                        onmouseover="this.style.color='#dc2626';this.style.background='#fef2f2'" onmouseout="this.style.color='var(--fb-text-5)';this.style.background='none'">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </button>
                </div>
            </div>
            @empty
            <div style="padding:56px 24px;text-align:center;color:var(--fb-text-4);">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 12px;display:block;"><path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                <div style="font-size:13px;font-weight:500;color:var(--fb-text-3);">{{ __('No services yet') }}</div>
                <div style="font-size:12px;margin-top:4px;">{{ __('Click "New service" to add your first one.') }}</div>
            </div>
            @endforelse

            {{-- Add service row --}}
            @if($services->isNotEmpty())
            <div style="padding:12px 20px;border-top:1px solid var(--fb-border-subtle);">
                <button wire:click="openServiceCreate"
                    style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--fb-text-3);background:none;border:none;cursor:pointer;padding:0;font-family:inherit;"
                    onmouseover="this.style.color='#7c3aed'" onmouseout="this.style.color='var(--fb-text-3)'">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    {{ __('Add service') }}
                </button>
            </div>
            @endif
        </div>

        {{-- Info box --}}
        <div style="margin-top:16px;padding:14px 18px;background:var(--fb-selected-bg);border-radius:10px;display:flex;align-items:flex-start;gap:10px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px;"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
            <div style="font-size:13px;color:#4c1d95;line-height:1.5;">{!! __('Services appear on <strong>step 1</strong> of the customer booking flow. Toggle a service off to temporarily hide it without deleting.') !!}</div>
        </div>

    </div>

    {{-- Delete confirm modal --}}
    @if($showDeleteConfirm)
    <div
        style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px;"
        wire:click.self="cancelDeleteService"
    >
        {{-- Backdrop --}}
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(2px);"></div>

        {{-- Modal card --}}
        <div style="position:relative;width:100%;max-width:400px;background:var(--fb-bg);border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,0.18),0 0 0 1px rgba(0,0,0,0.06);overflow:hidden;">

            {{-- Header --}}
            <div style="padding:24px 24px 0;display:flex;align-items:flex-start;gap:16px;">
                <div style="width:44px;height:44px;border-radius:12px;background:#fef2f2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </div>
                <div style="flex:1;">
                    <div style="font-size:16px;font-weight:700;color:var(--fb-text);line-height:1.3;">{{ __('Delete service?') }}</div>
                    <div style="font-size:13px;color:var(--fb-text-3);margin-top:6px;line-height:1.5;">
                        <strong style="color:var(--fb-text);">{{ $deleteServiceName }}</strong> {{ __('will be permanently removed. This cannot be undone.') }}
                    </div>
                </div>
                <button wire:click="cancelDeleteService"
                    style="width:28px;height:28px;flex-shrink:0;border:none;background:var(--fb-bg-raised);border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--fb-text-3);"
                    onmouseover="this.style.background='var(--fb-border)'" onmouseout="this.style.background='var(--fb-bg-raised)'">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- Body note --}}
            <div style="margin:16px 24px 0;padding:12px 14px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;display:flex;gap:10px;align-items:flex-start;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <div style="font-size:12px;color:#92400e;line-height:1.5;">{{ __('Any existing bookings for this service will remain in your records, but new bookings won\'t be possible.') }}</div>
            </div>

            {{-- Actions --}}
            <div style="padding:20px 24px 24px;display:flex;gap:10px;justify-content:flex-end;margin-top:4px;">
                <button wire:click="cancelDeleteService"
                    style="padding:9px 18px;font-size:13px;font-weight:500;font-family:inherit;border:1px solid var(--fb-border);border-radius:9px;background:var(--fb-bg);color:var(--fb-text-2);cursor:pointer;transition:background .12s;"
                    onmouseover="this.style.background='var(--fb-bg-muted)'" onmouseout="this.style.background='var(--fb-bg)'">
                    {{ __('Cancel') }}
                </button>
                <button wire:click="deleteService({{ $deleteServiceId }})" wire:loading.attr="disabled"
                    style="padding:9px 20px;font-size:13px;font-weight:600;font-family:inherit;border:none;border-radius:9px;background:#dc2626;color:#fff;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:opacity .15s;" wire:loading.class="opacity-60">
                    <span wire:loading.remove wire:target="deleteService">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:-2px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                        {{ __('Delete service') }}
                    </span>
                    <span wire:loading wire:target="deleteService">{{ __('Deleting…') }}</span>
                </button>
            </div>

        </div>
    </div>
    @endif

    {{-- Right panel: inline editor --}}
    @if($showServiceModal)
    <div style="width:380px;min-width:380px;border:1px solid var(--fb-border);border-radius:12px;background:var(--fb-bg);display:flex;flex-direction:column;overflow:hidden;">

        {{-- Panel title = service name --}}
        <div style="padding:18px 20px;border-bottom:1px solid var(--fb-border-subtle);display:flex;align-items:center;justify-content:space-between;">
            <div style="font-size:15px;font-weight:700;color:var(--fb-text);truncate;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $svcName ?: ($editingServiceId ? __('Edit service') : __('New service')) }}</div>
            <button wire:click="closeServiceModal"
                style="width:28px;height:28px;border:none;background:var(--fb-bg-raised);border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--fb-text-3);flex-shrink:0;"
                onmouseover="this.style.background='var(--fb-border)'" onmouseout="this.style.background='var(--fb-bg-raised)'">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div style="padding:20px;display:flex;flex-direction:column;gap:16px;overflow-y:auto;flex:1;">

            {{-- Service name --}}
            <div>
                <label class="fb-label">{{ __('Service name') }}</label>
                <input type="text" wire:model.live="svcName" autofocus placeholder="{{ __('e.g. Haircut & Style') }}" class="fb-input">
                @if($svcError)<p style="margin:4px 0 0;font-size:12px;color:#dc2626;">{{ $svcError }}</p>@endif
            </div>

            {{-- Description --}}
            <div>
                <label class="fb-label">{{ __('Description') }}</label>
                <textarea wire:model="svcDescription" rows="3" placeholder="{{ __('Brief description visible to customers') }}" class="fb-input fb-textarea"></textarea>
            </div>

            {{-- Category --}}
            <div>
                <label class="fb-label">{{ __('Category') }}</label>
                <input type="text" wire:model="svcCategory" placeholder="{{ __('e.g. Hair, Nails, Massage') }}" class="fb-input">
            </div>

            {{-- Duration + Price --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label class="fb-label">{{ __('Duration (min)') }}</label>
                    <input type="number" wire:model="svcDuration" min="5" max="480" class="fb-input">
                </div>
                <div>
                    <label class="fb-label">{{ __('Price') }} ({{ rtrim($currSym) }})</label>
                    <input type="number" wire:model="svcPrice" min="0" step="0.01" class="fb-input">
                </div>
            </div>

            {{-- Available providers --}}
            @if($providers->isNotEmpty())
            <div>
                <label class="fb-label">{{ __('Available providers') }}</label>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;">
                    @foreach($providers as $prov)
                    @php
                        $pid      = $prov->id;
                        $assigned = in_array($pid, $svcProviderIds, true);
                        $pcolor   = $prov->color ?? '#7c3aed';
                        $pname    = $prov->user?->name ?? 'Provider';
                    @endphp
                    <button wire:click="toggleSvcProvider({{ $pid }})"
                        style="display:inline-flex;align-items:center;gap:7px;padding:5px 12px 5px 6px;border-radius:20px;border:1.5px solid {{ $assigned ? '#7c3aed' : 'var(--fb-border)' }};background:{{ $assigned ? 'var(--fb-selected-bg)' : 'var(--fb-bg)' }};cursor:pointer;font-size:12.5px;font-weight:500;color:{{ $assigned ? 'var(--fb-tip-text)' : 'var(--fb-text-3)' }};font-family:inherit;transition:all .12s;">
                        <span style="width:22px;height:22px;border-radius:50%;background:{{ $pcolor }};display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;flex-shrink:0;">{{ strtoupper(substr($pname, 0, 2)) }}</span>
                        {{ $pname }}
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Active toggle --}}
            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 0;border-top:1px solid var(--fb-border-subtle);">
                <div>
                    <div style="font-size:14px;font-weight:500;color:var(--fb-text);">{{ __('Active') }}</div>
                    <div style="font-size:12px;color:var(--fb-text-4);margin-top:2px;">{{ __('Show on the booking page') }}</div>
                </div>
                <button wire:click="$toggle('svcActive')"
                    style="position:relative;width:44px;height:26px;border-radius:13px;border:none;cursor:pointer;flex-shrink:0;outline:none;transition:background .2s;background:{{ $svcActive ? '#7c3aed' : 'var(--fb-border)' }};">
                    <span style="position:absolute;top:3px;width:20px;height:20px;background:var(--fb-bg);border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,.25);transition:left .2s;left:{{ $svcActive ? '21px' : '3px' }};"></span>
                </button>
            </div>

            {{-- Save --}}
            <button wire:click="saveService" wire:loading.attr="disabled"
                style="width:100%;padding:10px;font-size:13px;font-weight:600;font-family:inherit;border:none;border-radius:8px;background:#7c3aed;color:#fff;cursor:pointer;" wire:loading.class="opacity-60">
                <span wire:loading.remove wire:target="saveService">{{ $editingServiceId ? __('Save changes') : __('Create service') }}</span>
                <span wire:loading wire:target="saveService">{{ __('Saving…') }}</span>
            </button>

        </div>
    </div>
    @endif

</div>
</div>
@endif

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- ══ PROVIDERS TAB ════════════════════════════════════════ --}}
{{-- ════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'providers')
@php
    $avatarColors = ['#f59e0b','#7c3aed','#10b981','#3b82f6','#ec4899','#ef4444'];
@endphp
<div style="border:1px solid var(--fb-border);border-radius:12px;overflow:hidden;background:var(--fb-bg);display:flex;min-height:580px;">

    {{-- Left panel: card grid --}}
    <div style="flex:1;min-width:0;display:flex;flex-direction:column;">

        {{-- Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid var(--fb-border-subtle);">
            <div>
                <div style="font-size:15px;font-weight:700;color:var(--fb-text);">{{ __('Providers') }}</div>
                <div style="font-size:13px;color:var(--fb-text-3);margin-top:2px;">{{ $providers->count() }} {{ trans_choice(__('team member|team members'), $providers->count()) }} · {{ __('shown to customers in step 2 of the booking flow') }}</div>
            </div>
            <button wire:click="openProviderCreate"
                style="display:inline-flex;align-items:center;gap:6px;padding:8px 18px;font-size:13px;font-weight:600;font-family:inherit;border:none;border-radius:8px;background:#7c3aed;color:#fff;cursor:pointer;transition:opacity .15s;white-space:nowrap;"
                onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                {{ __('Add provider') }}
            </button>
        </div>

        {{-- Card grid --}}
        <div style="padding:20px 24px;display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;align-content:start;">
            @foreach($providers as $prov)
            @php
                $pname   = $prov->user?->name ?? 'Provider';
                $pcolor  = $prov->color ?? '#7c3aed';
                $isSel   = $editingProviderId === $prov->id;
                $svcList = $prov->services->take(3);
                $extra   = $prov->services_count - 3;
            @endphp
            <div wire:click="openProviderEdit({{ $prov->id }})"
                 style="border:2px solid {{ $isSel ? '#7c3aed' : 'var(--fb-border)' }};border-radius:12px;padding:16px;background:var(--fb-bg);cursor:pointer;transition:border-color .15s,box-shadow .15s;"
                 onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,.07)'" onmouseout="this.style.boxShadow='none'">
                {{-- Row 1: avatar + name + active badge --}}
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                    <div style="width:44px;height:44px;border-radius:50%;flex-shrink:0;background:{{ $pcolor }};display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;color:#fff;">{{ strtoupper(substr($pname,0,2)) }}</div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:14px;font-weight:700;color:var(--fb-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $pname }}</div>
                        @if($prov->job_title || $prov->experience_years)
                        <div style="font-size:12px;color:var(--fb-text-3);margin-top:1px;">{{ $prov->job_title }}{{ ($prov->job_title && $prov->experience_years) ? ' · ' : '' }}{{ $prov->experience_years ? trans_choice(':count year|:count years', $prov->experience_years, ['count' => $prov->experience_years]) : '' }}</div>
                        @endif
                    </div>
                    <span style="flex-shrink:0;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:{{ ($prov->is_active ?? true) ? '#dcfce7' : 'var(--fb-bg-raised)' }};color:{{ ($prov->is_active ?? true) ? '#15803d' : 'var(--fb-text-3)' }};">{{ ($prov->is_active ?? true) ? __('Active') : __('Inactive') }}</span>
                </div>
                {{-- Service chips --}}
                @if($prov->services_count > 0)
                <div style="font-size:11.5px;color:var(--fb-text-4);margin-bottom:6px;">{{ $prov->services_count }} {{ trans_choice(__('service|services'), $prov->services_count) }}</div>
                <div style="display:flex;flex-wrap:wrap;gap:5px;">
                    @foreach($svcList as $s)
                    <span style="padding:3px 9px;background:var(--fb-bg-raised);border-radius:6px;font-size:11.5px;color:var(--fb-text-2);">{{ $s->name }}</span>
                    @endforeach
                    @if($extra > 0)
                    <span style="padding:3px 9px;background:var(--fb-bg-raised);border-radius:6px;font-size:11.5px;color:var(--fb-text-4);">+{{ $extra }}</span>
                    @endif
                </div>
                @endif
            </div>
            @endforeach

            {{-- "Add a team member" dashed card --}}
            <div wire:click="openProviderCreate"
                 style="border:2px dashed var(--fb-border);border-radius:12px;padding:16px;background:var(--fb-bg-subtle);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;min-height:80px;color:var(--fb-text-4);transition:border-color .15s;"
                 onmouseover="this.style.borderColor='#7c3aed';this.style.color='#7c3aed'" onmouseout="this.style.borderColor='';this.style.color='var(--fb-text-4)'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span style="font-size:13px;font-weight:500;">{{ __('Add a team member') }}</span>
            </div>
        </div>
    </div>

    {{-- Right panel: inline editor (visible when a provider is selected or being created) --}}
    @if($showProviderModal)
    <div style="width:360px;min-width:360px;border-left:1px solid var(--fb-border);display:flex;flex-direction:column;overflow-y:auto;">

        {{-- Panel header: name + close --}}
        <div style="padding:18px 20px;border-bottom:1px solid var(--fb-border-subtle);display:flex;align-items:center;justify-content:space-between;">
            <div style="font-size:15px;font-weight:700;color:var(--fb-text);">{{ $prvName ?: ($editingProviderId ? __('Edit provider') : __('New provider')) }}</div>
            <button wire:click="closeProviderModal"
                style="width:28px;height:28px;border:none;background:var(--fb-bg-raised);border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--fb-text-3);"
                onmouseover="this.style.background='var(--fb-border)'" onmouseout="this.style.background='var(--fb-bg-raised)'">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div style="padding:20px;display:flex;flex-direction:column;gap:16px;flex:1;">

            {{-- Avatar preview + color picker --}}
            <div style="display:flex;align-items:center;gap:16px;">
                <div style="width:60px;height:60px;border-radius:50%;background:{{ $prvColor }};display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:700;color:#fff;flex-shrink:0;">{{ strtoupper(substr($prvName ?: '?', 0, 2)) }}</div>
                <div>
                    <div style="font-size:12px;font-weight:600;color:var(--fb-text-2);margin-bottom:8px;">{{ __('Avatar color') }}</div>
                    <div style="display:flex;gap:6px;">
                        @foreach($avatarColors as $ac)
                        <button wire:click="$set('prvColor','{{ $ac }}')"
                            style="width:24px;height:24px;border-radius:50%;background:{{ $ac }};border:2px solid {{ $prvColor===$ac ? '#111827' : 'transparent' }};cursor:pointer;transition:border-color .1s;padding:0;"
                            title="{{ $ac }}"></button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Full name --}}
            <div>
                <label class="fb-label">{{ __('Full name') }}</label>
                @if($editingProviderId)
                    <input type="text" wire:model="prvName" placeholder="{{ __('Jane Doe') }}" class="fb-input">
                @else
                    {{-- New provider: pick from users not yet assigned to a provider --}}
                    <select wire:model="prvUserId" class="fb-input fb-select">
                        <option value="">{{ __('— Select a user —') }}</option>
                        @foreach($availableUsers as $u)
                        <option value="{{ $u->id }}" {{ $prvUserId==$u->id?'selected':'' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                    @if($prvError)<p style="margin:4px 0 0;font-size:12px;color:#dc2626;">{{ $prvError }}</p>@endif
                    @if($availableUsers->isEmpty())
                    <div style="font-size:12px;color:var(--fb-text-4);margin-top:4px;">
                        @if($allUsers->isEmpty())
                            {{ __('No user accounts found. Create one first.') }}
                        @else
                            {{ __('All users are already assigned as providers.') }}
                        @endif
                    </div>
                    @endif
                @endif
            </div>

            {{-- Role / title --}}
            <div>
                <label class="fb-label">{{ __('Role / title') }}</label>
                <input type="text" wire:model="prvJobTitle" placeholder="{{ __('e.g. Senior stylist') }}" class="fb-input">
            </div>

            {{-- Years of experience --}}
            <div>
                <label class="fb-label">{{ __('Years of experience') }}</label>
                <input type="number" wire:model="prvExperience" min="0" placeholder="0" class="fb-input">
            </div>

            {{-- Email (edit mode only — read-only hint for create) --}}
            @if($editingProviderId)
            <div>
                <label class="fb-label">{{ __('Email') }}</label>
                <input type="email" wire:model="prvEmail" class="fb-input">
            </div>
            @endif

            {{-- Provides services checkboxes --}}
            @if($services->isNotEmpty())
            <div>
                <label class="fb-label">{{ __('Provides services') }}</label>
                <div style="margin-top:6px;border:1px solid var(--fb-border);border-radius:8px;overflow:hidden;">
                    @foreach($services as $svc)
                    @php $checked = in_array($svc->id, $prvServiceIds, true); @endphp
                    <label style="display:flex;align-items:center;gap:12px;padding:10px 14px;cursor:pointer;border-bottom:1px solid var(--fb-border-subtle);background:{{ $checked ? 'var(--fb-hover-selected-bg)' : 'var(--fb-bg)' }};transition:background .1s;" wire:click="toggleProvService({{ $svc->id }})">
                        <span style="width:18px;height:18px;border-radius:4px;border:2px solid {{ $checked ? '#7c3aed' : '#d1d5db' }};background:{{ $checked ? '#7c3aed' : 'var(--fb-bg)' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .12s;">
                            @if($checked)
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            @endif
                        </span>
                        <span style="flex:1;font-size:13px;font-weight:500;color:var(--fb-text);">{{ $svc->name }}</span>
                        <span style="font-size:12px;color:var(--fb-text-4);">{{ $svc->duration_label }} · {{ $currSym.number_format($svc->price,0) }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Active toggle --}}
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-top:1px solid var(--fb-border-subtle);">
                <div>
                    <div style="font-size:13.5px;font-weight:500;color:var(--fb-text);">{{ __('Active') }}</div>
                    <div style="font-size:11.5px;color:var(--fb-text-4);margin-top:2px;">{{ __('Bookable by customers') }}</div>
                </div>
                <button wire:click="$toggle('prvActive')"
                    style="position:relative;width:42px;height:24px;border-radius:12px;border:none;cursor:pointer;flex-shrink:0;outline:none;transition:background .2s;background:{{ $prvActive ? '#7c3aed' : 'var(--fb-border)' }};">
                    <span style="position:absolute;top:3px;width:18px;height:18px;background:var(--fb-bg);border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,.25);transition:left .2s;left:{{ $prvActive ? '21px' : '3px' }};"></span>
                </button>
            </div>

            {{-- Save button --}}
            <button wire:click="saveProvider" wire:loading.attr="disabled"
                style="width:100%;padding:10px;font-size:13px;font-weight:600;font-family:inherit;border:none;border-radius:8px;background:#7c3aed;color:#fff;cursor:pointer;transition:opacity .15s;" wire:loading.class="opacity-60">
                <span wire:loading.remove wire:target="saveProvider">{{ $editingProviderId ? __('Save changes') : __('Create provider') }}</span>
                <span wire:loading wire:target="saveProvider">{{ __('Saving…') }}</span>
            </button>

            {{-- Remove provider --}}
            @if($editingProviderId)
            <button wire:click="confirmDeleteProvider({{ $editingProviderId }})"
                style="width:100%;padding:10px;font-size:13px;font-weight:500;font-family:inherit;border:1.5px solid #fca5a5;border-radius:8px;background:var(--fb-bg);color:#dc2626;cursor:pointer;transition:background .12s;"
                onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='var(--fb-bg)'">{{ __('Remove') }}</button>
            @endif

        </div>
    </div>
    @else
    {{-- Right panel empty state --}}
    <div style="width:360px;min-width:360px;border-left:1px solid var(--fb-border);display:flex;align-items:center;justify-content:center;">
        <div style="text-align:center;color:var(--fb-text-4);padding:24px;">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 12px;display:block;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <div style="font-size:13px;font-weight:500;color:var(--fb-text-3);">{{ __('Select a provider to edit') }}</div>
            <div style="font-size:12px;color:var(--fb-text-4);margin-top:4px;">{{ __('or click "+ Add provider"') }}</div>
        </div>
    </div>
    @endif

</div>

{{-- Remove provider confirm modal --}}
@if($showDeleteProviderConfirm)
<div
    style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px;"
    wire:click.self="cancelDeleteProvider"
>
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(2px);"></div>

    <div style="position:relative;width:100%;max-width:400px;background:var(--fb-bg);border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,0.18),0 0 0 1px rgba(0,0,0,0.06);overflow:hidden;">

        {{-- Header --}}
        <div style="padding:24px 24px 0;display:flex;align-items:flex-start;gap:16px;">
            <div style="width:44px;height:44px;border-radius:12px;background:#fef2f2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><line x1="18" y1="8" x2="23" y2="13"/><line x1="23" y1="8" x2="18" y2="13"/></svg>
            </div>
            <div style="flex:1;">
                <div style="font-size:16px;font-weight:700;color:var(--fb-text);line-height:1.3;">{{ __('Remove provider?') }}</div>
                <div style="font-size:13px;color:var(--fb-text-3);margin-top:6px;line-height:1.5;">
                    <strong style="color:var(--fb-text);">{{ $deleteProviderName }}</strong> {{ __('will be removed as a provider. Their staff account will not be deleted.') }}
                </div>
            </div>
            <button wire:click="cancelDeleteProvider"
                style="width:28px;height:28px;flex-shrink:0;border:none;background:var(--fb-bg-raised);border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--fb-text-3);"
                onmouseover="this.style.background='var(--fb-border)'" onmouseout="this.style.background='var(--fb-bg-raised)'">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        {{-- Body note --}}
        <div style="margin:16px 24px 0;padding:12px 14px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;display:flex;gap:10px;align-items:flex-start;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <div style="font-size:12px;color:#92400e;line-height:1.5;">{{ __('Upcoming bookings assigned to this provider will remain. Consider reassigning them before removing.') }}</div>
        </div>

        {{-- Actions --}}
        <div style="padding:20px 24px 24px;display:flex;gap:10px;justify-content:flex-end;margin-top:4px;">
            <button wire:click="cancelDeleteProvider"
                style="padding:9px 18px;font-size:13px;font-weight:500;font-family:inherit;border:1px solid var(--fb-border);border-radius:9px;background:var(--fb-bg);color:var(--fb-text-2);cursor:pointer;transition:background .12s;"
                onmouseover="this.style.background='var(--fb-bg-muted)'" onmouseout="this.style.background='var(--fb-bg)'">
                {{ __('Cancel') }}
            </button>
            <button wire:click="deleteProvider({{ $deleteProviderId }})" wire:loading.attr="disabled"
                style="padding:9px 20px;font-size:13px;font-weight:600;font-family:inherit;border:none;border-radius:9px;background:#dc2626;color:#fff;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:opacity .15s;" wire:loading.class="opacity-60">
                <span wire:loading.remove wire:target="deleteProvider">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:-2px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    {{ __('Remove provider') }}
                </span>
                <span wire:loading wire:target="deleteProvider">{{ __('Removing…') }}</span>
            </button>
        </div>

    </div>
</div>
@endif

@endif

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- ══ SETTINGS TAB ══════════════════════════════════════════ --}}
{{-- ════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'settings')
@php
$avDays   = ['mon'=>__('Monday'),'tue'=>__('Tuesday'),'wed'=>__('Wednesday'),'thu'=>__('Thursday'),'fri'=>__('Friday'),'sat'=>__('Saturday'),'sun'=>__('Sunday')];
$halfHours = [];
for ($h = 0; $h < 24; $h++) {
    $halfHours[sprintf('%02d:00',$h)] = sprintf('%02d:00',$h);
    $halfHours[sprintf('%02d:30',$h)] = sprintf('%02d:30',$h);
}
@endphp

<div style="display:flex;flex-direction:column;gap:14px;background:var(--fb-bg-muted);border-radius:16px;padding:20px;border:1px solid var(--fb-border);">

    {{-- Error banner --}}
    @if($settingsError)
    <div style="padding:12px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;font-size:13px;color:#dc2626;display:flex;align-items:center;gap:9px;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ $settingsError }}
    </div>
    @endif

    {{-- ── 1. Business profile ───────────────────────────────── --}}
    <div class="fb-card">
        <div class="fb-card-hdr">
            <div style="width:36px;height:36px;border-radius:10px;background:var(--fb-tip-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <div>
                <div style="font-size:14px;font-weight:600;color:var(--fb-text);line-height:1.3;">{{ __('Business profile') }}</div>
                <div style="font-size:12.5px;color:var(--fb-text-4);margin-top:1px;">{{ __('Your name, contact details and booking URL') }}</div>
            </div>
        </div>
        <div class="fb-card-body">

            {{-- Logo upload --}}
            <div style="margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid var(--fb-border-subtle);">
                <label class="fb-label">{{ __('Company logo') }}</label>
                <div style="display:flex;align-items:center;gap:14px;margin-top:4px;">
                    {{-- Current logo / placeholder --}}
                    <div style="width:60px;height:60px;border-radius:10px;border:1px solid var(--fb-border);background:var(--fb-bg-muted);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;">
                        @if($tenantLogoPath)
                        <img src="{{ asset('storage/' . $tenantLogoPath) }}" alt="logo"
                             style="width:100%;height:100%;object-fit:contain;">
                        @elseif($logoFile)
                        <img src="{{ $logoFile->temporaryUrl() }}" alt="preview"
                             style="width:100%;height:100%;object-fit:contain;">
                        @else
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        @endif
                    </div>
                    <div style="flex:1;">
                        <input type="file" wire:model="logoFile" accept="image/*"
                            style="width:100%;padding:7px 10px;border:1px dashed #d1d5db;border-radius:8px;font-size:12.5px;font-family:inherit;color:var(--fb-text-2);cursor:pointer;background:var(--fb-bg-subtle);"
                            onfocus="this.style.borderColor='#7c3aed'" onblur="this.style.borderColor=''">
                        <div style="font-size:11.5px;color:var(--fb-text-4);margin-top:4px;">{{ __('PNG, JPG or SVG · max 2 MB · Displayed on your booking page') }}</div>
                        @error('logoFile') <div style="font-size:11.5px;color:#dc2626;margin-top:3px;">{{ $message }}</div> @enderror
                    </div>
                    @if($tenantLogoPath)
                    <button wire:click="removeLogo" wire:confirm="{{ __('Remove the current logo?') }}"
                        style="padding:6px 12px;font-size:12px;font-weight:500;font-family:inherit;border:1px solid #fca5a5;border-radius:7px;background:var(--fb-bg);color:#dc2626;cursor:pointer;white-space:nowrap;"
                        onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='var(--fb-bg)'">
                        {{ __('Remove') }}
                    </button>
                    @endif
                </div>
            </div>

            {{-- Fields grid --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                    <label class="fb-label">{{ __('Business name') }}</label>
                    <input type="text" wire:model="tenantName" placeholder="{{ __('e.g. Studio Mira') }}" class="fb-input">
                </div>
                <div>
                    <label class="fb-label">{{ __('Booking URL') }}</label>
                    <div style="display:flex;align-items:center;border:1px solid var(--fb-border);border-radius:8px;overflow:hidden;" id="slug-wrap">
                        <span style="padding:9px 10px;background:var(--fb-bg-muted);color:var(--fb-text-4);font-size:12px;border-right:1px solid var(--fb-border);white-space:nowrap;flex-shrink:0;">{{ url('/') }}/</span>
                        <input type="text" wire:model="tenantSlugEdit" placeholder="{{ __('your-business') }}"
                            style="flex:1;padding:9px 10px;border:none;outline:none;font-size:13.5px;font-family:inherit;color:var(--fb-text);min-width:0;"
                            onfocus="document.getElementById('slug-wrap').style.cssText+='border-color:#7c3aed;box-shadow:0 0 0 3px #7c3aed18;'"
                            onblur="document.getElementById('slug-wrap').style.borderColor='';document.getElementById('slug-wrap').style.boxShadow='none'">
                    </div>
                    <div style="font-size:11.5px;color:var(--fb-text-4);margin-top:4px;">{{ __('Your public booking page URL') }}</div>
                </div>
                <div>
                    <label class="fb-label">{{ __('Email') }}</label>
                    <input type="email" wire:model="tenantEmail" placeholder="{{ __('hello@yourbusiness.com') }}" class="fb-input">
                </div>
                <div>
                    <label class="fb-label">{{ __('Phone') }}</label>
                    <input type="text" wire:model="tenantPhone" placeholder="{{ __('+1 (555) 000-0000') }}" class="fb-input">
                </div>
                <div style="grid-column:span 2;">
                    <label class="fb-label">{{ __('Address') }}</label>
                    <input type="text" wire:model="tenantAddress" placeholder="{{ __('123 Main St, City, Country') }}" class="fb-input">
                </div>
                <div>
                    <label class="fb-label">{{ __('Website') }}</label>
                    <input type="url" wire:model="tenantWebsite" placeholder="{{ __('https://yourbusiness.com') }}" class="fb-input">
                </div>
                <div>
                    <label class="fb-label">{{ __('Tagline') }}</label>
                    <input type="text" wire:model="tenantTagline" placeholder="{{ __('e.g. Premium cuts, zero wait time.') }}" class="fb-input">
                </div>
            </div>
        </div>
    </div>

    {{-- ── 2. Timing ─────────────────────────────────────────── --}}
    <div class="fb-card">
        <div class="fb-card-hdr">
            <div style="width:36px;height:36px;border-radius:10px;background:#ecfdf5;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
                <div style="font-size:14px;font-weight:600;color:var(--fb-text);line-height:1.3;">{{ __('Timing') }}</div>
                <div style="font-size:12.5px;color:var(--fb-text-4);margin-top:1px;">{{ __('Default session length and gap between appointments') }}</div>
            </div>
        </div>
        <div class="fb-card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                    <label class="fb-label">{{ __('Default booking duration') }}</label>
                    <select wire:model="defaultDuration" class="fb-input fb-select">
                        @foreach([15=>__('15 minutes'),30=>__('30 minutes'),45=>__('45 minutes'),60=>__('60 minutes'),90=>__('90 minutes'),120=>__('2 hours')] as $val=>$lbl)
                        <option value="{{ $val }}" {{ $defaultDuration==$val?'selected':'' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="fb-label">{{ __('Buffer between bookings') }}</label>
                    <select wire:model="bufferTime" class="fb-input fb-select">
                        @foreach([0=>__('No buffer'),5=>__('5 minutes'),10=>__('10 minutes'),15=>__('15 minutes'),20=>__('20 minutes'),30=>__('30 minutes'),60=>__('1 hour')] as $val=>$lbl)
                        <option value="{{ $val }}" {{ $bufferTime==$val?'selected':'' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 3. Regional ───────────────────────────────────────── --}}
    <div class="fb-card">
        <div class="fb-card-hdr">
            <div style="width:36px;height:36px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            </div>
            <div>
                <div style="font-size:14px;font-weight:600;color:var(--fb-text);line-height:1.3;">{{ __('Regional') }}</div>
                <div style="font-size:12.5px;color:var(--fb-text-4);margin-top:1px;">{{ __('Timezone and currency shown on your booking page') }}</div>
            </div>
        </div>
        <div class="fb-card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                    <label class="fb-label">{{ __('Timezone') }}</label>
                    <select wire:model="tenantTimezone" class="fb-input fb-select">
                        @foreach($timezones as $tz=>$label)
                        <option value="{{ $tz }}" {{ $tenantTimezone===$tz?'selected':'' }}>{{ $tz }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="fb-label">{{ __('Currency') }}</label>
                    <select wire:model="tenantCurrency" class="fb-input fb-select">
                        @foreach(['INR'=>'INR — Indian Rupee','USD'=>'USD — US Dollar','GBP'=>'GBP — British Pound','EUR'=>'EUR — Euro','AED'=>'AED — UAE Dirham','SGD'=>'SGD — Singapore Dollar','AUD'=>'AUD — Australian Dollar'] as $code=>$name)
                        <option value="{{ $code }}" {{ $tenantCurrency===$code?'selected':'' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 4. Availability ───────────────────────────────────── --}}
    <div class="fb-card">
        <div class="fb-card-hdr">
            <div style="width:36px;height:36px;border-radius:10px;background:var(--fb-tip-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
            </div>
            <div>
                <div style="font-size:14px;font-weight:600;color:var(--fb-text);line-height:1.3;">{{ __('Availability') }}</div>
                <div style="font-size:12.5px;color:var(--fb-text-4);margin-top:1px;">{{ __('Set which days and hours customers can book') }}</div>
            </div>
        </div>
        {{-- Day rows --}}
        @foreach($avDays as $avKey => $dayLabel)
        @php $day = $availability[$avKey] ?? ['enabled'=>false,'start'=>'09:00','end'=>'18:00'];
             $avOn = $day['enabled']; @endphp
        <div @class(['flex items-center gap-[14px] px-[22px] py-[13px]', 'fb-sep' => !$loop->last])>
            <div style="width:88px;font-size:13px;font-weight:500;flex-shrink:0;color:{{ $avOn ? 'var(--fb-text-2)' : 'var(--fb-text-4)' }};">{{ $dayLabel }}</div>
            <button wire:click="$set('availability.{{ $avKey }}.enabled', {{ $avOn ? 'false' : 'true' }})"
                style="position:relative;width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;flex-shrink:0;outline:none;transition:background .2s;background:{{ $avOn ? '#7c3aed' : 'var(--fb-border)' }};">
                <span style="position:absolute;top:3px;left:{{ $avOn ? '23px' : '3px' }};width:18px;height:18px;background:var(--fb-bg);border-radius:50%;box-shadow:0 1px 2px rgba(0,0,0,.18);transition:left .2s;display:block;"></span>
            </button>
            @if($avOn)
            <div style="display:flex;align-items:center;gap:8px;flex:1;">
                <select wire:model="availability.{{ $avKey }}.start"
                    style="flex:1;padding:8px 10px;border:1px solid var(--fb-border);border-radius:8px;font-size:13px;font-family:inherit;color:var(--fb-text);outline:none;background:var(--fb-bg);cursor:pointer;" {!! $inputFocus !!}>
                    @foreach($halfHours as $tv=>$tl)
                    <option value="{{ $tv }}" {{ ($day['start']===$tv)?'selected':'' }}>{{ $tl }}</option>
                    @endforeach
                </select>
                <span style="font-size:12px;color:var(--fb-text-4);flex-shrink:0;">{{ __('to') }}</span>
                <select wire:model="availability.{{ $avKey }}.end"
                    style="flex:1;padding:8px 10px;border:1px solid var(--fb-border);border-radius:8px;font-size:13px;font-family:inherit;color:var(--fb-text);outline:none;background:var(--fb-bg);cursor:pointer;" {!! $inputFocus !!}>
                    @foreach($halfHours as $tv=>$tl)
                    <option value="{{ $tv }}" {{ ($day['end']===$tv)?'selected':'' }}>{{ $tl }}</option>
                    @endforeach
                </select>
            </div>
            @else
            <div style="font-size:13px;color:var(--fb-text-5);">{{ __('Unavailable') }}</div>
            @endif
        </div>
        @endforeach
        {{-- Blocked dates --}}
        <div style="border-top:1px solid var(--fb-border-subtle);padding:16px 22px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <div>
                    <div style="font-size:13px;font-weight:500;color:var(--fb-text-2);">{{ __('Holidays & blocked dates') }}</div>
                    <div style="font-size:12px;color:var(--fb-text-4);margin-top:2px;">{{ __('Days where all bookings are disabled') }}</div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <input type="date" wire:model="newBlockedDate"
                        style="padding:7px 10px;border:1px solid var(--fb-border);border-radius:8px;font-size:12.5px;font-family:inherit;color:var(--fb-text-2);outline:none;cursor:pointer;"
                        onfocus="this.style.borderColor='#7c3aed';this.style.boxShadow='0 0 0 3px #7c3aed18'"
                        onblur="this.style.borderColor='';this.style.boxShadow='none'">
                    <button wire:click="addBlockedDate"
                        style="display:inline-flex;align-items:center;gap:5px;padding:7px 13px;font-size:12.5px;font-weight:600;font-family:inherit;border:1px solid var(--fb-border);border-radius:8px;background:var(--fb-bg);color:var(--fb-text-2);cursor:pointer;white-space:nowrap;"
                        onmouseover="this.style.background='var(--fb-bg-muted)'" onmouseout="this.style.background='var(--fb-bg)'">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        {{ __('Add date') }}
                    </button>
                </div>
            </div>
            @if(count($blockedDates))
            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                @foreach($blockedDates as $bd)
                <span style="display:inline-flex;align-items:center;gap:5px;padding:5px 10px 5px 12px;background:var(--fb-bg-raised);border-radius:20px;font-size:12.5px;color:var(--fb-text-2);font-weight:500;">
                    {{ $bd }}
                    <button wire:click="removeBlockedDate('{{ $bd }}')" style="display:flex;align-items:center;background:none;border:none;cursor:pointer;color:var(--fb-text-4);padding:0;line-height:1;" title="{{ __('Remove') }}">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </span>
                @endforeach
            </div>
            @else
            <div style="font-size:12.5px;color:#c4c9d4;font-style:italic;">{{ __('No blocked dates — all enabled days are bookable.') }}</div>
            @endif
        </div>
    </div>

    {{-- ── 5. Limits ─────────────────────────────────────────── --}}
    <div class="fb-card">
        <div class="fb-card-hdr">
            <div style="width:36px;height:36px;border-radius:10px;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
            </div>
            <div>
                <div style="font-size:14px;font-weight:600;color:var(--fb-text);line-height:1.3;">{{ __('Limits') }}</div>
                <div style="font-size:12.5px;color:var(--fb-text-4);margin-top:1px;">{{ __('Cap how many bookings come through each day') }}</div>
            </div>
        </div>
        <div class="fb-card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                    <label class="fb-label">{{ __('Max bookings per day') }}</label>
                    <input type="number" wire:model="maxBookingsPerDay" min="0" placeholder="{{ __('Unlimited') }}"
                        class="fb-input">
                    <div style="font-size:11.5px;color:var(--fb-text-4);margin-top:4px;">{{ __('0 = no limit') }}</div>
                </div>
                <div>
                    <label class="fb-label">{{ __('Minimum advance notice') }}</label>
                    <select wire:model="minAdvanceNotice" class="fb-input fb-select">
                        @foreach(['0'=>__('No minimum'),'1'=>__('1 hour'),'2'=>__('2 hours'),'4'=>__('4 hours'),'8'=>__('8 hours'),'24'=>__('24 hours'),'48'=>__('48 hours')] as $val=>$lbl)
                        <option value="{{ $val }}" {{ $minAdvanceNotice===$val?'selected':'' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    <div style="font-size:11.5px;color:var(--fb-text-4);margin-top:4px;">{{ __('How far ahead customers must book') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 6. Booking behaviour ──────────────────────────────── --}}
    <div class="fb-card">
        <div class="fb-card-hdr">
            <div style="width:36px;height:36px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            </div>
            <div>
                <div style="font-size:14px;font-weight:600;color:var(--fb-text);line-height:1.3;">{{ __('Booking behaviour') }}</div>
                <div style="font-size:12.5px;color:var(--fb-text-4);margin-top:1px;">{{ __('Control how customers interact with your booking flow') }}</div>
            </div>
        </div>
        {{-- Allow multiple services --}}
        <div class="fb-toggle fb-sep">
            <div>
                <div style="font-size:13.5px;font-weight:500;color:var(--fb-text);">{{ __('Allow multiple services') }}</div>
                <div style="font-size:12px;color:var(--fb-text-4);margin-top:2px;">{{ __('Customers can select more than one service per booking') }}</div>
            </div>
            <button wire:click="$set('allowMultipleServices', {{ $allowMultipleServices ? 'false' : 'true' }})"
                style="position:relative;width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;flex-shrink:0;outline:none;transition:background .2s;background:{{ $allowMultipleServices ? '#7c3aed' : 'var(--fb-border)' }};margin-left:24px;">
                <span style="position:absolute;top:3px;left:{{ $allowMultipleServices ? '23px' : '3px' }};width:18px;height:18px;background:var(--fb-bg);border-radius:50%;box-shadow:0 1px 2px rgba(0,0,0,.18);transition:left .2s;display:block;"></span>
            </button>
        </div>
        {{-- Allow client cancellation --}}
        <div class="fb-toggle fb-sep">
            <div>
                <div style="font-size:13.5px;font-weight:500;color:var(--fb-text);">{{ __('Allow client cancellation') }}</div>
                <div style="font-size:12px;color:var(--fb-text-4);margin-top:2px;">{{ __('Clients can cancel their own bookings from My Bookings page') }}</div>
            </div>
            <button wire:click="$set('allowClientCancellation', {{ $allowClientCancellation ? 'false' : 'true' }})"
                style="position:relative;width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;flex-shrink:0;outline:none;transition:background .2s;background:{{ $allowClientCancellation ? '#7c3aed' : 'var(--fb-border)' }};margin-left:24px;">
                <span style="position:absolute;top:3px;left:{{ $allowClientCancellation ? '23px' : '3px' }};width:18px;height:18px;background:var(--fb-bg);border-radius:50%;box-shadow:0 1px 2px rgba(0,0,0,.18);transition:left .2s;display:block;"></span>
            </button>
        </div>
        {{-- Show cancellation policy --}}
        <div @class(['fb-toggle', 'fb-sep' => $showCancellationPolicy])>
            <div>
                <div style="font-size:13.5px;font-weight:500;color:var(--fb-text);">{{ __('Show cancellation policy') }}</div>
                <div style="font-size:12px;color:var(--fb-text-4);margin-top:2px;">{{ __('Display your cancellation policy on the booking page') }}</div>
            </div>
            <button wire:click="$set('showCancellationPolicy', {{ $showCancellationPolicy ? 'false' : 'true' }})"
                style="position:relative;width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;flex-shrink:0;outline:none;transition:background .2s;background:{{ $showCancellationPolicy ? '#7c3aed' : 'var(--fb-border)' }};margin-left:24px;">
                <span style="position:absolute;top:3px;left:{{ $showCancellationPolicy ? '23px' : '3px' }};width:18px;height:18px;background:var(--fb-bg);border-radius:50%;box-shadow:0 1px 2px rgba(0,0,0,.18);transition:left .2s;display:block;"></span>
            </button>
        </div>
        @if($showCancellationPolicy)
        <div style="padding:4px 22px 20px;">
            <label class="fb-label">{{ __('Policy text') }}</label>
            <textarea wire:model="cancellationPolicy" rows="3"
                placeholder="{{ __('e.g. Free cancellation up to 24 hours before your appointment.') }}"
                class="fb-input fb-textarea"></textarea>
        </div>
        @endif
    </div>

    {{-- ── 7. Notifications ─────────────────────────────────── --}}
    <div class="fb-card">
        <div class="fb-card-hdr">
            <div style="width:36px;height:36px;border-radius:10px;background:var(--fb-tip-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </div>
            <div>
                <div style="font-size:14px;font-weight:600;color:var(--fb-text);line-height:1.3;">{{ __('Notifications') }}</div>
                <div style="font-size:12.5px;color:var(--fb-text-4);margin-top:1px;">{{ __('Control who gets alerted and when') }}</div>
            </div>
        </div>
        {{-- Email confirmation --}}
        <div class="fb-toggle fb-sep">
            <div>
                <div style="font-size:13.5px;font-weight:500;color:var(--fb-text);">{{ __('Email confirmation to customer') }}</div>
                <div style="font-size:12px;color:var(--fb-text-4);margin-top:2px;">{{ __('Sent immediately after a booking is made') }}</div>
            </div>
            <button wire:click="$set('emailConfirmation', {{ $emailConfirmation ? 'false' : 'true' }})"
                style="position:relative;width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;flex-shrink:0;outline:none;transition:background .2s;background:{{ $emailConfirmation ? '#7c3aed' : 'var(--fb-border)' }};margin-left:24px;">
                <span style="position:absolute;top:3px;left:{{ $emailConfirmation ? '23px' : '3px' }};width:18px;height:18px;background:var(--fb-bg);border-radius:50%;box-shadow:0 1px 2px rgba(0,0,0,.18);transition:left .2s;display:block;"></span>
            </button>
        </div>
        {{-- SMS reminder --}}
        <div class="fb-toggle fb-sep">
            <div>
                <div style="font-size:13.5px;font-weight:500;color:var(--fb-text);">{{ __('SMS reminder') }}</div>
                <div style="font-size:12px;color:var(--fb-text-4);margin-top:2px;">{{ __('Sent 24 hours before the appointment · uses 1 SMS credit') }}</div>
            </div>
            <button wire:click="$set('smsReminder', {{ $smsReminder ? 'false' : 'true' }})"
                style="position:relative;width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;flex-shrink:0;outline:none;transition:background .2s;background:{{ $smsReminder ? '#7c3aed' : 'var(--fb-border)' }};margin-left:24px;">
                <span style="position:absolute;top:3px;left:{{ $smsReminder ? '23px' : '3px' }};width:18px;height:18px;background:var(--fb-bg);border-radius:50%;box-shadow:0 1px 2px rgba(0,0,0,.18);transition:left .2s;display:block;"></span>
            </button>
        </div>
        {{-- Notify owner --}}
        <div class="fb-toggle">
            <div>
                <div style="font-size:13.5px;font-weight:500;color:var(--fb-text);">{{ __('Notify me on every new booking') }}</div>
                <div style="font-size:12px;color:var(--fb-text-4);margin-top:2px;">{{ $tenantEmail ?: __('To your account email') }}</div>
            </div>
            <button wire:click="$set('notifyOwner', {{ $notifyOwner ? 'false' : 'true' }})"
                style="position:relative;width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;flex-shrink:0;outline:none;transition:background .2s;background:{{ $notifyOwner ? '#7c3aed' : 'var(--fb-border)' }};margin-left:24px;">
                <span style="position:absolute;top:3px;left:{{ $notifyOwner ? '23px' : '3px' }};width:18px;height:18px;background:var(--fb-bg);border-radius:50%;box-shadow:0 1px 2px rgba(0,0,0,.18);transition:left .2s;display:block;"></span>
            </button>
        </div>
    </div>

    {{-- ── Outbound Webhook ────────────────────────────────── --}}
    <div style="border-top:1px solid var(--fb-border);margin-top:20px;padding-top:20px;">
        <div style="font-size:13px;font-weight:600;color:var(--fb-text);margin-bottom:4px;">{{ __('Outbound Webhook') }}</div>
        <div style="font-size:12px;color:var(--fb-text-2);margin-bottom:14px;">
            {{ __('Slotara will POST booking data to your endpoint on every confirmed booking. Verify requests using the') }} <code>X-Slotara-Signature</code> {{ __('header (HMAC-SHA256).') }}
        </div>
        <div style="margin-bottom:12px;">
            <label style="font-size:12px;font-weight:500;color:var(--fb-text);display:block;margin-bottom:4px;">{{ __('Webhook URL') }}</label>
            <input type="url" wire:model="webhookUrl" placeholder="{{ __('https://your-app.com/webhooks/slotara') }}"
                style="width:100%;padding:8px 10px;font-size:13px;font-family:inherit;border:1px solid var(--fb-border);border-radius:7px;background:var(--fb-bg);color:var(--fb-text);box-sizing:border-box;outline:none;">
        </div>
        @if ($webhookSecret)
        <div style="margin-bottom:4px;">
            <label style="font-size:12px;font-weight:500;color:var(--fb-text);display:block;margin-bottom:4px;">{{ __('Webhook Secret') }} <span style="font-weight:400;color:var(--fb-text-2);">{{ __('(copy this — use to verify HMAC-SHA256 signature)') }}</span></label>
            <div style="display:flex;align-items:center;gap:8px;">
                <input type="text" value="{{ $webhookSecret }}" readonly
                    style="flex:1;padding:8px 10px;font-size:12px;font-family:monospace;border:1px solid var(--fb-border);border-radius:7px;background:var(--fb-bg-muted);color:var(--fb-text-2);box-sizing:border-box;outline:none;">
                <button type="button" onclick="navigator.clipboard.writeText('{{ $webhookSecret }}')"
                    style="padding:8px 14px;font-size:12px;font-weight:500;font-family:inherit;border:1px solid var(--fb-border);border-radius:7px;background:var(--fb-bg);color:var(--fb-text);cursor:pointer;white-space:nowrap;flex-shrink:0;">
                    {{ __('Copy') }}
                </button>
            </div>
        </div>
        @else
        <p style="font-size:12px;color:var(--fb-text-2);">{{ __('A secret will be auto-generated when you save a Webhook URL.') }}</p>
        @endif
    </div>

    {{-- ── Save ──────────────────────────────────────────────── --}}
    <div style="display:flex;justify-content:flex-end;padding:4px 0 12px;">
        <button wire:click="saveSettings" wire:loading.attr="disabled"
            style="padding:10px 28px;font-size:13.5px;font-weight:600;font-family:inherit;border:none;border-radius:9px;background:#7c3aed;color:#fff;cursor:pointer;transition:opacity .15s,box-shadow .15s;box-shadow:0 1px 4px rgba(124,58,237,.3);"
            wire:loading.class="opacity-60"
            onmouseover="this.style.boxShadow='0 4px 14px rgba(124,58,237,.4)'"
            onmouseout="this.style.boxShadow='0 1px 4px rgba(124,58,237,.3)'">
            <span wire:loading.remove wire:target="saveSettings">{{ __('Save settings') }}</span>
            <span wire:loading wire:target="saveSettings">{{ __('Saving…') }}</span>
        </button>
    </div>

</div>
@endif

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- ══ THEMES TAB ════════════════════════════════════════════ --}}
{{-- ════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'themes')
@php
    $fonts = ['Inter','Helvetica Neue','Söhne','Geist','Source Sans 3'];
    $fontStacks = [
        'Inter'          => "'Inter', ui-sans-serif, sans-serif",
        'Helvetica Neue' => "'Helvetica Neue', Helvetica, Arial, sans-serif",
        'Söhne'          => "'Söhne', 'Inter', sans-serif",
        'Geist'          => "'Geist', 'Inter', sans-serif",
        'Source Sans 3'  => "'Source Sans 3', ui-sans-serif, sans-serif",
    ];
    $btnStyles = [
        'rounded' => ['label'=>__('Rounded'), 'r'=>'8px'],
        'pill'    => ['label'=>__('Pill'),    'r'=>'999px'],
        'sharp'   => ['label'=>__('Sharp'),   'r'=>'3px'],
    ];
    $previewBtnR   = $btnStyles[$buttonStyle]['r'] ?? '8px';
    $previewFont   = $fontStacks[$bookingFont] ?? "'Inter', sans-serif";
    $allThemes     = \App\Booking\Themes\ThemeRegistry::all();
@endphp
<div style="display:flex;gap:24px;align-items:flex-start;">

    {{-- LEFT: settings --}}
    <div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:16px;">

        {{-- Layout theme --}}
        <div style="border:1px solid var(--fb-border);border-radius:12px;background:var(--fb-bg);padding:20px 22px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                <div style="width:32px;height:32px;border-radius:8px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
                </div>
                <div>
                    <div style="font-size:14px;font-weight:600;color:var(--fb-text);">{{ __('Layout theme') }}</div>
                    <div style="font-size:12px;color:var(--fb-text-3);">{{ __('Overall structure of your booking page') }}</div>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                @foreach($allThemes as $themeKey => $themeData)
                @php $isSelected = $bookingTheme === $themeKey; @endphp
                <button wire:click="$set('bookingTheme','{{ $themeKey }}')"
                    style="display:flex;align-items:center;gap:14px;padding:14px 16px;border-radius:10px;cursor:pointer;font-family:inherit;text-align:left;width:100%;transition:all .12s;
                           border:{{ $isSelected ? '2px solid '.$bookingColor : '1px solid var(--fb-border)' }};
                           background:{{ $isSelected ? 'var(--fb-selected-bg)' : 'var(--fb-bg)' }};">
                    {{-- Mini preview icon --}}
                    @if($themeKey === 'lumina')
                    <div style="width:54px;height:38px;border-radius:6px;overflow:hidden;flex-shrink:0;border:1px solid var(--fb-border);display:flex;">
                        <div style="width:18px;background:linear-gradient(160deg,{{ $bookingColor }},color-mix(in srgb,{{ $bookingColor }} 70%,#000));flex-shrink:0;"></div>
                        <div style="flex:1;background:var(--fb-bg-muted);display:flex;flex-direction:column;gap:3px;padding:5px 4px;">
                            <div style="height:3px;border-radius:2px;background:var(--fb-border);width:90%;"></div>
                            <div style="height:3px;border-radius:2px;background:var(--fb-border);width:70%;"></div>
                            <div style="height:6px;border-radius:3px;background:{{ $bookingColor }};width:60%;margin-top:auto;opacity:.85;"></div>
                        </div>
                    </div>
                    @else
                    <div style="width:54px;height:38px;border-radius:6px;overflow:hidden;flex-shrink:0;border:1px solid var(--fb-border);background:var(--fb-bg-muted);display:flex;flex-direction:column;gap:3px;padding:6px 6px;">
                        <div style="height:3px;border-radius:2px;background:{{ $bookingColor }};width:80%;opacity:.9;"></div>
                        <div style="height:3px;border-radius:2px;background:var(--fb-border);width:60%;"></div>
                        <div style="height:3px;border-radius:2px;background:var(--fb-border);width:100%;"></div>
                        <div style="height:5px;border-radius:3px;background:{{ $bookingColor }};width:50%;margin-top:auto;opacity:.85;"></div>
                    </div>
                    @endif
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:14px;font-weight:{{ $isSelected ? '700' : '500' }};color:var(--fb-text);">{{ $themeData['name'] }}</div>
                        <div style="font-size:12px;color:var(--fb-text-3);margin-top:2px;">{{ $themeData['description'] }}</div>
                    </div>
                    @if($isSelected)
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="{{ $bookingColor }}" stroke="none" style="flex-shrink:0;"><circle cx="12" cy="12" r="12"/><polyline points="7 12 10 15 17 8" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                    @endif
                </button>
                @endforeach
            </div>
        </div>

        {{-- Brand color --}}
        <div style="border:1px solid var(--fb-border);border-radius:12px;background:var(--fb-bg);padding:20px 22px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                <div style="width:32px;height:32px;border-radius:8px;background:var(--fb-selected-bg);display:flex;align-items:center;justify-content:center;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/></svg>
                </div>
                <div>
                    <div style="font-size:14px;font-weight:600;color:var(--fb-text);">{{ __('Brand color') }}</div>
                    <div style="font-size:12px;color:var(--fb-text-3);">{{ __('Used on buttons, accents, focus rings') }}</div>
                </div>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
                @foreach($presetColors as $pc)
                <button wire:click="$set('bookingColor','{{ $pc }}')" title="{{ $pc }}"
                    style="width:36px;height:36px;border-radius:50%;background:{{ $pc }};cursor:pointer;transition:transform .1s,box-shadow .1s;border:none;
                           {{ $bookingColor===$pc ? 'box-shadow:0 0 0 3px #fff,0 0 0 5px '.$pc.';' : '' }}"
                    onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'"></button>
                @endforeach
                {{-- Custom --}}
                <label style="width:36px;height:36px;border-radius:50%;border:2px dashed #d1d5db;display:flex;align-items:center;justify-content:center;cursor:pointer;background:var(--fb-bg-muted);" title="{{ __('Custom color') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <input type="color" wire:model.live="bookingColor" style="opacity:0;position:absolute;width:0;height:0;">
                </label>
            </div>
            <div style="font-size:12px;color:var(--fb-text-3);">{{ __('Use the global theme picker (top right) to set your active brand color.') }}</div>
        </div>

        {{-- Typography --}}
        <div style="border:1px solid var(--fb-border);border-radius:12px;background:var(--fb-bg);padding:20px 22px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                <div style="width:32px;height:32px;border-radius:8px;background:#f0f9ff;display:flex;align-items:center;justify-content:center;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>
                </div>
                <div>
                    <div style="font-size:14px;font-weight:600;color:var(--fb-text);">{{ __('Typography') }}</div>
                    <div style="font-size:12px;color:var(--fb-text-3);">{{ __('Heading & body font') }}</div>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                @foreach($fonts as $f)
                <button wire:click="$set('bookingFont','{{ $f }}')"
                    style="display:flex;justify-content:space-between;align-items:center;padding:11px 14px;border-radius:8px;cursor:pointer;font-family:inherit;text-align:left;transition:all .12s;
                           border:{{ $bookingFont===$f ? '2px solid '.$bookingColor : '1px solid var(--fb-border)' }};
                           background:{{ $bookingFont===$f ? 'var(--fb-selected-bg)' : 'var(--fb-bg)' }};">
                    <span style="font-size:14px;font-weight:{{ $bookingFont===$f?'600':'500' }};color:var(--fb-text);font-family:{{ $fontStacks[$f] }};">{{ $f }}</span>
                    <span style="font-size:12px;color:var(--fb-text-4);font-family:{{ $fontStacks[$f] }};">Aa Bb Cc 123</span>
                </button>
                @endforeach
            </div>
        </div>

        {{-- Button style --}}
        <div style="border:1px solid var(--fb-border);border-radius:12px;background:var(--fb-bg);padding:20px 22px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                <div style="width:32px;height:32px;border-radius:8px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="8" width="18" height="8" rx="4"/></svg>
                </div>
                <div>
                    <div style="font-size:14px;font-weight:600;color:var(--fb-text);">{{ __('Button style') }}</div>
                    <div style="font-size:12px;color:var(--fb-text-3);">{{ __('Corner radius applied across the form') }}</div>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
                @foreach($btnStyles as $key => $bs)
                <button wire:click="$set('buttonStyle','{{ $key }}')"
                    style="display:flex;flex-direction:column;align-items:center;gap:10px;padding:16px 10px;border-radius:10px;cursor:pointer;font-family:inherit;transition:all .12s;
                           border:{{ $buttonStyle===$key ? '2px solid '.$bookingColor : '1px solid var(--fb-border)' }};
                           background:{{ $buttonStyle===$key ? 'var(--fb-selected-bg)' : 'var(--fb-bg)' }};">
                    <div style="width:100%;height:32px;background:{{ $buttonStyle===$key ? $bookingColor : '#6366f1' }};border-radius:{{ $bs['r'] }};"></div>
                    <span style="font-size:12px;font-weight:{{ $buttonStyle===$key?'600':'500' }};color:{{ $buttonStyle===$key?'var(--fb-tip-title)':'var(--fb-text-2)' }};">{{ $bs['label'] }}</span>
                </button>
                @endforeach
            </div>
        </div>

        {{-- Date picker style --}}
        <div style="border:1px solid var(--fb-border);border-radius:12px;background:var(--fb-bg);padding:20px 22px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                <div style="width:32px;height:32px;border-radius:8px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                </div>
                <div>
                    <div style="font-size:14px;font-weight:600;color:var(--fb-text);">{{ __('Date picker') }}</div>
                    <div style="font-size:12px;color:var(--fb-text-3);">{{ __('How customers pick their appointment date') }}</div>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                @foreach(['monthly' => ['label'=>__('Monthly'),'sub'=>__('Full month grid'),'icon'=>'M'], 'weekly' => ['label'=>__('Weekly'),'sub'=>__('7-day compact strip'),'icon'=>'W']] as $dpKey => $dp)
                @php $dpSel = $datePickerStyle === $dpKey; @endphp
                <button wire:click="$set('datePickerStyle','{{ $dpKey }}')"
                    style="display:flex;flex-direction:column;align-items:center;gap:10px;padding:16px 12px;border-radius:10px;cursor:pointer;font-family:inherit;transition:all .12s;
                           border:{{ $dpSel ? '2px solid '.$bookingColor : '1px solid var(--fb-border)' }};
                           background:{{ $dpSel ? 'var(--fb-selected-bg)' : 'var(--fb-bg)' }};">
                    {{-- Mini calendar preview --}}
                    @if($dpKey === 'monthly')
                    <div style="width:52px;height:40px;border-radius:6px;border:1px solid var(--fb-border);overflow:hidden;background:var(--fb-bg-muted);">
                        <div style="height:8px;background:{{ $dpSel ? $bookingColor : 'var(--fb-border)' }};opacity:.7;"></div>
                        <div style="padding:3px 3px 0;display:grid;grid-template-columns:repeat(7,1fr);gap:1px;">
                            @for($r=0;$r<3;$r++)
                                @for($c=0;$c<7;$c++)
                                <div style="height:5px;border-radius:50%;background:{{ ($r===1&&$c===2) ? $bookingColor : 'var(--fb-border)' }};opacity:{{ ($r===1&&$c===2) ? '1' : '0.4' }};"></div>
                                @endfor
                            @endfor
                        </div>
                    </div>
                    @else
                    <div style="width:52px;height:40px;border-radius:6px;border:1px solid var(--fb-border);overflow:hidden;background:var(--fb-bg-muted);display:flex;align-items:center;padding:0 3px;gap:2px;">
                        @for($c=0;$c<7;$c++)
                        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:2px;">
                            <div style="height:3px;width:100%;border-radius:2px;background:var(--fb-border);opacity:.5;"></div>
                            <div style="width:6px;height:6px;border-radius:50%;background:{{ $c===2 ? $bookingColor : 'var(--fb-border)' }};opacity:{{ $c===2 ? '1' : '0.4' }};"></div>
                        </div>
                        @endfor
                    </div>
                    @endif
                    <div style="text-align:center;">
                        <div style="font-size:13px;font-weight:{{ $dpSel?'700':'500' }};color:var(--fb-text);">{{ $dp['label'] }}</div>
                        <div style="font-size:11px;color:var(--fb-text-3);margin-top:1px;">{{ $dp['sub'] }}</div>
                    </div>
                </button>
                @endforeach
            </div>
        </div>

        {{-- Display mode --}}
        <div style="border:1px solid var(--fb-border);border-radius:12px;background:var(--fb-bg);padding:20px 22px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                <div style="width:32px;height:32px;border-radius:8px;background:var(--fb-hover-selected-bg);display:flex;align-items:center;justify-content:center;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                </div>
                <div>
                    <div style="font-size:14px;font-weight:600;color:var(--fb-text);">{{ __('Display mode') }}</div>
                    <div style="font-size:12px;color:var(--fb-text-3);">{{ __('Default appearance for the booking form') }}</div>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <label style="display:flex;align-items:center;justify-content:space-between;cursor:pointer;gap:12px;">
                    <div>
                        <div style="font-size:13.5px;font-weight:500;color:var(--fb-text);">{{ __("Match customer's system theme") }}</div>
                        <div style="font-size:12px;color:var(--fb-text-3);margin-top:1px;">{{ __('Auto switch between light & dark') }}</div>
                    </div>
                    <div wire:click="$set('matchSystemTheme', {{ $matchSystemTheme ? 'false' : 'true' }})"
                        style="width:44px;height:24px;border-radius:12px;cursor:pointer;transition:background .2s;flex-shrink:0;position:relative;
                               background:{{ $matchSystemTheme ? $bookingColor : '#d1d5db' }};">
                        <div style="position:absolute;top:3px;width:18px;height:18px;border-radius:50%;background:var(--fb-bg);transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,.2);
                                    left:{{ $matchSystemTheme ? '23px' : '3px' }};"></div>
                    </div>
                </label>
                <label style="display:flex;align-items:center;justify-content:space-between;cursor:pointer;gap:12px;">
                    <div>
                        <div style="font-size:13.5px;font-weight:500;color:var(--fb-text);">{{ __('Force dark mode') }}</div>
                        <div style="font-size:12px;color:var(--fb-text-3);margin-top:1px;">{{ __('Override system preference') }}</div>
                    </div>
                    <div wire:click="$set('forceDarkMode', {{ $forceDarkMode ? 'false' : 'true' }})"
                        style="width:44px;height:24px;border-radius:12px;cursor:pointer;transition:background .2s;flex-shrink:0;position:relative;
                               background:{{ $forceDarkMode ? $bookingColor : '#d1d5db' }};">
                        <div style="position:absolute;top:3px;width:18px;height:18px;border-radius:50%;background:var(--fb-bg);transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,.2);
                                    left:{{ $forceDarkMode ? '23px' : '3px' }};"></div>
                    </div>
                </label>
            </div>
        </div>

        {{-- Save button --}}
        <button wire:click="saveTheme" wire:loading.attr="disabled"
            style="padding:11px 28px;font-size:14px;font-weight:600;font-family:inherit;border:none;border-radius:8px;background:#7c3aed;color:#fff;cursor:pointer;transition:opacity .15s;align-self:flex-start;" wire:loading.class="opacity-60">
            <span wire:loading.remove wire:target="saveTheme">{{ __('Save changes') }}</span>
            <span wire:loading wire:target="saveTheme">{{ __('Saving…') }}</span>
        </button>
    </div>

    {{-- RIGHT: live preview --}}
    <div style="width:320px;flex-shrink:0;position:sticky;top:16px;">
        <div style="font-size:11px;font-weight:700;color:var(--fb-text-3);letter-spacing:.6px;text-transform:uppercase;margin-bottom:10px;display:flex;align-items:center;gap:5px;">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            {{ __('Live preview') }}
        </div>
        <div style="border:1px solid var(--fb-border);border-radius:14px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.07);background:var(--fb-bg);font-family:{{ $previewFont }};">
            <div style="padding:14px 16px 10px;border-bottom:1px solid var(--fb-border-subtle);">
                <div style="font-size:10px;font-weight:700;color:var(--fb-text-4);letter-spacing:.5px;text-transform:uppercase;margin-bottom:3px;">{{ strtoupper($tenantName ?: 'Your Business') }}</div>
                <div style="font-size:17px;font-weight:700;color:var(--fb-text);line-height:1.3;margin-bottom:4px;">{{ $formTitle }}</div>
                <div style="display:flex;gap:10px;font-size:11px;color:var(--fb-text-3);">
                    <span>⏱ 60 min</span>
                    <span>📅 Tue, May 12</span>
                </div>
            </div>
            <div style="padding:16px;">
                <div style="margin-bottom:12px;">
                    <div style="font-size:12px;font-weight:600;color:var(--fb-text-2);margin-bottom:5px;">{{ __('Full name') }}</div>
                    <div style="padding:9px 12px;border:1px solid var(--fb-border);border-radius:7px;font-size:13px;color:var(--fb-text-2);background:var(--fb-bg);">{{ __('Jordan Lewis') }}</div>
                </div>
                <div style="margin-bottom:14px;">
                    <div style="font-size:12px;font-weight:600;color:var(--fb-text-2);margin-bottom:5px;">{{ __('Email') }}</div>
                    <div style="padding:9px 12px;border:2px solid {{ $bookingColor }};border-radius:7px;font-size:13px;color:var(--fb-text-2);background:var(--fb-bg);">{{ __('jordan@email.com') }}</div>
                </div>
                <button style="width:100%;padding:11px;font-size:13px;font-weight:600;font-family:inherit;border:none;border-radius:{{ $previewBtnR }};background:{{ $bookingColor }};color:#fff;cursor:default;">
                    {{ __('Confirm booking') }}
                </button>
            </div>
        </div>
    </div>

</div>
@endif

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- ══ SUCCESS PAGE TAB ════════════════════════════════════ --}}
{{-- ════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'success_page')
<div style="border:1px solid var(--fb-border);border-radius:12px;overflow:hidden;min-height:480px;background:var(--fb-bg);">
    <div style="padding:18px 24px;border-bottom:1px solid var(--fb-border-subtle);background:var(--fb-bg-subtle);">
        <div style="font-size:15px;font-weight:700;color:var(--fb-text);">{{ __('Success page') }}</div>
        <div style="font-size:13px;color:var(--fb-text-3);margin-top:2px;">{{ __('Shown to customers after a booking is confirmed') }}</div>
    </div>
    <div style="display:flex;gap:32px;padding:28px 24px;flex-wrap:wrap;">
        <div style="flex:1;min-width:280px;max-width:460px;">
            <div style="margin-bottom:18px;">
                <label class="fb-label">{{ __('Heading') }}</label>
                <input type="text" wire:model.live="successTitle" placeholder="{{ __("You're booked!") }}" class="fb-input">
            </div>
            <div style="margin-bottom:22px;">
                <label class="fb-label">{{ __('Message') }}</label>
                <textarea wire:model.live="successBody" rows="4" class="fb-input fb-textarea"></textarea>
            </div>
            <button wire:click="saveSuccessPage" wire:loading.attr="disabled"
                style="padding:10px 24px;font-size:13.5px;font-weight:600;font-family:inherit;border:none;border-radius:8px;background:#7c3aed;color:#fff;cursor:pointer;transition:opacity .15s;" wire:loading.class="opacity-60">
                <span wire:loading.remove wire:target="saveSuccessPage">{{ __('Save') }}</span>
                <span wire:loading wire:target="saveSuccessPage">{{ __('Saving…') }}</span>
            </button>
        </div>
        <div style="flex:1;min-width:240px;max-width:320px;">
            <div style="font-size:11px;font-weight:700;color:var(--fb-text-3);letter-spacing:.6px;text-transform:uppercase;margin-bottom:12px;">{{ __('Preview') }}</div>
            <div style="border:1px solid var(--fb-border);border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.06);padding:28px 20px;text-align:center;">
                <div style="width:56px;height:56px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div style="font-size:18px;font-weight:800;color:var(--fb-text);margin-bottom:8px;line-height:1.3;">{{ $successTitle?:"You're booked!" }}</div>
                <div style="font-size:13px;color:var(--fb-text-3);line-height:1.6;">{{ $successBody?:"We just sent a confirmation to your email." }}</div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- ══ SHARING TAB ══════════════════════════════════════════ --}}
{{-- ════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'sharing')
@php
    $embedBase = config('app.url');
    $jsSnippet     = "<!-- Slotara embed: drop anywhere on your site -->\n<script src=\"{$embedBase}/embed.js\" defer><" . "/script>\n<div data-slotara=\"{$tenantSlug}\"></div>";
    $iframeSnippet = "<iframe\n  src=\"{$bookingUrl}?embed=inline\"\n  style=\"border:0;width:100%;min-height:680px\"\n  loading=\"lazy\"></iframe>";
    $popupSnippet  = "<button onclick=\"Slotara.popup('{$tenantSlug}')\">\n  Book now\n</button>\n<script src=\"{$embedBase}/embed.js\" defer><" . "/script>";
@endphp
<div style="max-width:720px;background:var(--fb-bg);border:1px solid var(--fb-border);border-radius:14px;padding:28px;display:flex;flex-direction:column;gap:0;">

    {{-- Hero banner --}}
    <div style="display:flex;align-items:flex-start;gap:16px;padding:20px 22px;border-radius:14px;margin-bottom:28px;
                background:linear-gradient(135deg,#eef2ff,#f5f3ff);border:1px solid #e0e7ff;">
        <div style="width:44px;height:44px;border-radius:10px;background:#4f46e5;flex-shrink:0;
                    display:flex;align-items:center;justify-content:center;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
        </div>
        <div>
            <div style="font-size:15px;font-weight:700;color:#1e1b4b;margin-bottom:4px;">{{ __('One snippet. Live booking on any website.') }}</div>
            <div style="font-size:13px;color:#4338ca;line-height:1.5;">{{ __('Works in WordPress, Wix, Squarespace, Webflow, Shopify, Framer or plain HTML — paste, save, done.') }}</div>
        </div>
    </div>

    @if($bookingUrl)

    {{-- ─── Public link ─────────────────────────────────────────── --}}
    <div style="margin-bottom:28px;" x-data="{linkCopied:false}">
        <div style="font-size:11px;font-weight:700;color:var(--fb-text-3);letter-spacing:.7px;text-transform:uppercase;margin-bottom:10px;">{{ __('Public link') }}</div>
        <div style="display:flex;gap:8px;align-items:center;">
            <div style="flex:1;display:flex;align-items:center;gap:9px;padding:10px 14px;
                        background:var(--fb-bg-muted);border:1px solid var(--fb-border);border-radius:8px;overflow:hidden;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;color:var(--fb-text-4);"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                <span style="font-size:13px;color:var(--fb-text-2);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-family:monospace;">{{ $bookingUrl }}</span>
            </div>
            <button @click="navigator.clipboard.writeText('{{ $bookingUrl }}').then(()=>{linkCopied=true;setTimeout(()=>linkCopied=false,2000)})"
                style="display:inline-flex;align-items:center;gap:6px;padding:10px 15px;font-size:13px;font-weight:500;font-family:inherit;
                       border:1px solid var(--fb-border);border-radius:8px;background:var(--fb-bg);color:var(--fb-text-2);cursor:pointer;flex-shrink:0;"
                onmouseover="this.style.background='var(--fb-bg-raised)'" onmouseout="this.style.background='var(--fb-bg)'">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                <span x-show="!linkCopied">{{ __('Copy link') }}</span>
                <span x-show="linkCopied" style="color:#16a34a;">{{ __('Copied!') }}</span>
            </button>
            <a href="{{ $bookingUrl }}" target="_blank"
               style="display:inline-flex;align-items:center;padding:10px 20px;font-size:13px;font-weight:600;font-family:inherit;
                      border:none;border-radius:8px;background:#4f46e5;color:#fff;text-decoration:none;flex-shrink:0;cursor:pointer;"
               onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
                {{ __('Open') }}
            </a>
        </div>
    </div>

    {{-- ─── Embed snippets ──────────────────────────────────────── --}}
    <div style="margin-bottom:16px;">
        <div style="font-size:11px;font-weight:700;color:var(--fb-text-3);letter-spacing:.7px;text-transform:uppercase;margin-bottom:14px;">{{ __('Embed snippets') }}</div>
        <div style="display:flex;flex-direction:column;gap:12px;">

            {{-- JS snippet (recommended) --}}
            <div style="border:1px solid var(--fb-border);border-radius:12px;overflow:hidden;" x-data="{c1:false}">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 18px;
                            background:var(--fb-bg-subtle);border-bottom:1px solid var(--fb-border);">
                    <div>
                        <div style="display:inline-flex;align-items:center;gap:8px;font-size:14px;font-weight:600;color:var(--fb-text);">
                            {{ __('JS snippet (recommended)') }}
                        </div>
                        <div style="font-size:12px;color:var(--fb-text-3);margin-top:3px;">{{ __('Auto-resizes, listens for booking events, passes UTM params') }}</div>
                    </div>
                    <button @click="navigator.clipboard.writeText({{ json_encode($jsSnippet) }}).then(()=>{c1=true;setTimeout(()=>c1=false,2000)})"
                        style="display:inline-flex;align-items:center;gap:5px;padding:7px 13px;font-size:12px;font-weight:500;font-family:inherit;
                               border:1px solid var(--fb-border);border-radius:7px;background:var(--fb-bg);color:var(--fb-text-2);cursor:pointer;flex-shrink:0;"
                        onmouseover="this.style.background='var(--fb-bg-raised)'" onmouseout="this.style.background='var(--fb-bg)'">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        <span x-show="!c1">{{ __('Copy') }}</span><span x-show="c1" style="color:#16a34a;">{{ __('Copied!') }}</span>
                    </button>
                </div>
                <pre style="margin:0;padding:16px 18px;font-size:12px;line-height:1.75;overflow-x:auto;color:var(--fb-text-2);
                            background:var(--fb-bg-muted);font-family:ui-monospace,'Cascadia Code','Fira Code',monospace;white-space:pre;">{{ $jsSnippet }}</pre>
            </div>

            {{-- Inline iframe --}}
            <div style="border:1px solid var(--fb-border);border-radius:12px;overflow:hidden;" x-data="{c2:false}">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 18px;
                            background:var(--fb-bg-subtle);border-bottom:1px solid var(--fb-border);">
                    <div>
                        <div style="font-size:14px;font-weight:600;color:var(--fb-text);">{{ __('Inline iframe') }}</div>
                        <div style="font-size:12px;color:var(--fb-text-3);margin-top:3px;">{{ __('Drop the form straight into your page — no JS needed') }}</div>
                    </div>
                    <button @click="navigator.clipboard.writeText({{ json_encode($iframeSnippet) }}).then(()=>{c2=true;setTimeout(()=>c2=false,2000)})"
                        style="display:inline-flex;align-items:center;gap:5px;padding:7px 13px;font-size:12px;font-weight:500;font-family:inherit;
                               border:1px solid var(--fb-border);border-radius:7px;background:var(--fb-bg);color:var(--fb-text-2);cursor:pointer;flex-shrink:0;"
                        onmouseover="this.style.background='var(--fb-bg-raised)'" onmouseout="this.style.background='var(--fb-bg)'">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        <span x-show="!c2">{{ __('Copy') }}</span><span x-show="c2" style="color:#16a34a;">{{ __('Copied!') }}</span>
                    </button>
                </div>
                <pre style="margin:0;padding:16px 18px;font-size:12px;line-height:1.75;overflow-x:auto;color:var(--fb-text-2);
                            background:var(--fb-bg-muted);font-family:ui-monospace,'Cascadia Code','Fira Code',monospace;white-space:pre;">{{ $iframeSnippet }}</pre>
            </div>

            {{-- Popup modal trigger --}}
            <div style="border:1px solid var(--fb-border);border-radius:12px;overflow:hidden;" x-data="{c3:false}">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 18px;
                            background:var(--fb-bg-subtle);border-bottom:1px solid var(--fb-border);">
                    <div>
                        <div style="font-size:14px;font-weight:600;color:var(--fb-text);">{{ __('Popup modal trigger') }}</div>
                        <div style="font-size:12px;color:var(--fb-text-3);margin-top:3px;">{{ __('Open the form in a centered overlay on click') }}</div>
                    </div>
                    <button @click="navigator.clipboard.writeText({{ json_encode($popupSnippet) }}).then(()=>{c3=true;setTimeout(()=>c3=false,2000)})"
                        style="display:inline-flex;align-items:center;gap:5px;padding:7px 13px;font-size:12px;font-weight:500;font-family:inherit;
                               border:1px solid var(--fb-border);border-radius:7px;background:var(--fb-bg);color:var(--fb-text-2);cursor:pointer;flex-shrink:0;"
                        onmouseover="this.style.background='var(--fb-bg-raised)'" onmouseout="this.style.background='var(--fb-bg)'">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        <span x-show="!c3">{{ __('Copy') }}</span><span x-show="c3" style="color:#16a34a;">{{ __('Copied!') }}</span>
                    </button>
                </div>
                <pre style="margin:0;padding:16px 18px;font-size:12px;line-height:1.75;overflow-x:auto;color:var(--fb-text-2);
                            background:var(--fb-bg-muted);font-family:ui-monospace,'Cascadia Code','Fira Code',monospace;white-space:pre;">{{ $popupSnippet }}</pre>
            </div>

        </div>
    </div>

    {{-- ─── QR Code ──────────────────────────────────────────────── --}}
    <div style="margin-bottom:16px;">
        <div style="font-size:11px;font-weight:700;color:var(--fb-text-3);letter-spacing:.7px;text-transform:uppercase;margin-bottom:14px;">{{ __('QR Code') }}</div>
        <div style="border:1px solid var(--fb-border);border-radius:12px;padding:24px;display:flex;flex-direction:column;align-items:center;gap:16px;background:var(--fb-bg);"
             x-data="{ downloaded: false }"
             x-init="
               if (typeof QRCode === 'undefined') {
                 const s = document.createElement('script');
                 s.src = '{{ asset('js/qrcode.min.js') }}';
                 s.onload = () => new QRCode($el.querySelector('#sl-qr-canvas-{{ $tenantId }}'), {
                   text: '{{ $bookingUrl }}',
                   width: 180, height: 180,
                   colorDark: '#000000', colorLight: '#ffffff',
                   correctLevel: QRCode.CorrectLevel.M
                 });
                 document.head.appendChild(s);
               } else {
                 new QRCode($el.querySelector('#sl-qr-canvas-{{ $tenantId }}'), {
                   text: '{{ $bookingUrl }}',
                   width: 180, height: 180,
                   colorDark: '#000000', colorLight: '#ffffff',
                   correctLevel: QRCode.CorrectLevel.M
                 });
               }
             ">
            <div style="padding:10px;background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.1);">
                <div id="sl-qr-canvas-{{ $tenantId }}"></div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:13px;font-weight:500;color:var(--fb-text);margin-bottom:4px;">{{ __('Scan to book') }}</div>
                <div style="font-size:12px;color:var(--fb-text-3);">{{ __('Print and display at your location so walk-ins can book instantly') }}</div>
            </div>
            <button
                @click="
                  const img = $el.querySelector('#sl-qr-canvas-{{ $tenantId }} img') || $el.querySelector('#sl-qr-canvas-{{ $tenantId }} canvas');
                  if (!img) return;
                  const a = document.createElement('a');
                  a.download = 'booking-qr.png';
                  a.href = img.tagName === 'CANVAS' ? img.toDataURL() : img.src;
                  a.click();
                  downloaded = true; setTimeout(() => downloaded = false, 2000);
                "
                style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;font-size:12px;font-weight:500;font-family:inherit;
                       border:1px solid var(--fb-border);border-radius:8px;background:var(--fb-bg);color:var(--fb-text-2);cursor:pointer;"
                onmouseover="this.style.background='var(--fb-bg-raised)'" onmouseout="this.style.background='var(--fb-bg)'">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <span x-show="!downloaded">{{ __('Download PNG') }}</span><span x-show="downloaded" style="color:#16a34a;">{{ __('Downloaded!') }}</span>
            </button>
        </div>
    </div>

    @else
    <div style="text-align:center;padding:60px 24px;">
        <div style="width:48px;height:48px;border-radius:12px;background:var(--fb-bg-raised);border:1px solid var(--fb-border);
                    display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="color:var(--fb-text-4);"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
        </div>
        <div style="font-size:14px;font-weight:600;color:var(--fb-text-2);margin-bottom:6px;">{{ __('No booking URL configured') }}</div>
        <div style="font-size:13px;color:var(--fb-text-3);">{!! __('Set your business slug in <strong>Settings</strong> to generate your booking link and embed snippets.') !!}</div>
    </div>
    @endif
</div>
@endif

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- ══ PREVIEW TAB ══════════════════════════════════════════ --}}
{{-- ════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'preview')
<div style="border:1px solid var(--fb-border);border-radius:12px;overflow:hidden;min-height:480px;background:var(--fb-bg);">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid var(--fb-border-subtle);background:var(--fb-bg-subtle);">
        <div>
            <div style="font-size:15px;font-weight:700;color:var(--fb-text);">{{ __('Live preview') }}</div>
            <div style="font-size:13px;color:var(--fb-text-3);margin-top:1px;">{{ __('Your booking page as customers see it') }}</div>
        </div>
        @if($bookingUrl)
        <a href="{{ $bookingUrl }}" target="_blank" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;font-size:13px;font-weight:500;font-family:inherit;border:1px solid var(--fb-border);border-radius:8px;background:var(--fb-bg);color:var(--fb-text-2);text-decoration:none;" onmouseover="this.style.background='var(--fb-bg-muted)'" onmouseout="this.style.background='var(--fb-bg)'">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            {{ __('Open in new tab') }}
        </a>
        @endif
    </div>
    @if($bookingUrl)
    <div style="height:calc(100vh - 340px);min-height:500px;">
        <iframe src="{{ $bookingUrl }}" style="width:100%;height:100%;border:none;" title="{{ __('Booking page preview') }}" loading="lazy"></iframe>
    </div>
    @else
    <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:500px;color:var(--fb-text-4);text-align:center;gap:12px;">
        <div style="font-size:14px;font-weight:600;color:var(--fb-text-2);margin-bottom:6px;">{{ __('No preview available') }}</div>
        <div style="font-size:13px;color:var(--fb-text-3);">{{ __('Set your booking URL slug in Settings to enable preview.') }}</div>
    </div>
    @endif
</div>
@endif

{{-- Service editing is now inline in the split-panel above --}}

{{-- Provider editing is now inline in the split-panel above --}}

</div>

{{-- form-builder CSS variables injected here because Filament panel does not load the app Vite bundle --}}
@assets
<script src="{{ asset('vendor/sortablejs/Sortable.min.js') }}"></script>
@endassets
