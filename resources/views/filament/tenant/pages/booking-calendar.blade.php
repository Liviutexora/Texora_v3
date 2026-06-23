<x-filament-panels::page>

{{-- FullCalendar v6 – served locally from public/vendor/fullcalendar/ --}}
<script src="{{ asset('vendor/fullcalendar/index.global.min.js') }}"></script>

{{-- Status legend --}}
<div style="display:flex;gap:14px;align-items:center;flex-wrap:wrap;margin-bottom:10px;">
    @foreach([
        ['label' => __('Pending'),   'color' => '#f59e0b'],
        ['label' => __('Confirmed'), 'color' => '#10b981'],
        ['label' => __('Completed'), 'color' => '#6366f1'],
        ['label' => __('Cancelled'), 'color' => '#ef4444'],
        ['label' => __('No Show'),   'color' => '#9ca3af'],
    ] as $item)
    <span style="display:inline-flex;align-items:center;gap:5px;font-size:12px;color:#6b7280;">
        <span style="width:8px;height:8px;border-radius:50%;background:{{ $item['color'] }};display:inline-block;flex-shrink:0;"></span>
        {{ $item['label'] }}
    </span>
    @endforeach
</div>

{{-- Calendar --}}
<x-filament::section :padding="false">
    <div id="slotara-calendar" class="p-4 min-h-[600px]"></div>
</x-filament::section>

{{-- ── BOOKING DETAIL MODAL ─────────────────────────────────────────────── --}}
<div id="cal-modal-backdrop"
     onclick="calModal.close()"
     style="display:none;position:fixed;inset:0;z-index:9998;background:rgba(0,0,0,0.4);backdrop-filter:blur(3px);">
</div>

<div id="cal-modal"
     style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);z-index:9999;width:100%;max-width:480px;padding:16px;box-sizing:border-box;">

    <div style="background:#fff;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,0.18),0 4px 16px rgba(0,0,0,0.08);overflow:hidden;">

        {{-- Modal header --}}
        <div id="cal-modal-header"
             style="padding:16px 20px;display:flex;align-items:flex-start;justify-content:space-between;border-bottom:1px solid #f3f4f6;gap:12px;">
            <div style="min-width:0;flex:1;">
                <div style="font-size:15px;font-weight:700;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" id="cal-modal-title">{{ __('Booking') }}</div>
                <div style="font-size:12px;color:#6b7280;margin-top:2px;" id="cal-modal-subtitle"></div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                <span id="cal-modal-badge" style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;"></span>
                <button onclick="calModal.close()"
                        style="width:28px;height:28px;border-radius:50%;border:1px solid #e5e7eb;background:#f9fafb;cursor:pointer;font-size:16px;color:#6b7280;display:flex;align-items:center;justify-content:center;line-height:1;flex-shrink:0;">
                    &times;
                </button>
            </div>
        </div>

        {{-- Loading state --}}
        <div id="cal-modal-loading" style="padding:48px 20px;text-align:center;color:#9ca3af;">
            <svg style="width:28px;height:28px;margin:0 auto 10px;display:block;animation:spin 1s linear infinite;"
                 fill="none" viewBox="0 0 24 24">
                <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span style="font-size:13px;">{{ __('Loading…') }}</span>
        </div>

        {{-- Content --}}
        <div id="cal-modal-body" style="display:none;padding:18px 20px 14px;">

            {{-- Date + Time highlight row --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
                <div style="background:#f5f3ff;border-radius:10px;padding:10px 12px;">
                    <div style="font-size:10px;font-weight:700;color:#7c3aed;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:3px;">{{ __('Date') }}</div>
                    <div style="font-size:13px;font-weight:600;color:#1f2937;" id="cm-date"></div>
                </div>
                <div style="background:#f5f3ff;border-radius:10px;padding:10px 12px;">
                    <div style="font-size:10px;font-weight:700;color:#7c3aed;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:3px;">{{ __('Time') }}</div>
                    <div style="font-size:13px;font-weight:600;color:#1f2937;" id="cm-time"></div>
                </div>
            </div>

            {{-- Details grid --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:x:12px;gap:10px;">
                <div>
                    <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:3px;">{{ __('Service') }}</div>
                    <div style="font-size:13px;color:#374151;font-weight:500;" id="cm-service"></div>
                </div>
                <div>
                    <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:3px;">{{ __('Provider') }}</div>
                    <div style="font-size:13px;color:#374151;" id="cm-provider"></div>
                </div>
                <div>
                    <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:3px;">{{ __('Client') }}</div>
                    <div style="font-size:13px;color:#111827;font-weight:600;" id="cm-client"></div>
                </div>
                <div>
                    <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:3px;">{{ __('Phone') }}</div>
                    <div style="font-size:13px;color:#374151;" id="cm-phone"></div>
                </div>
                <div style="grid-column:span 2;">
                    <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:3px;">{{ __('Email') }}</div>
                    <div style="font-size:13px;color:#374151;word-break:break-all;" id="cm-email"></div>
                </div>
                <div style="grid-column:span 2;">
                    <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:3px;">{{ __('Payment') }}</div>
                    <div style="font-size:13px;color:#374151;" id="cm-payment"></div>
                </div>
            </div>

            {{-- Note --}}
            <div id="cm-note-wrap" style="display:none;margin-top:12px;padding-top:12px;border-top:1px solid #f3f4f6;">
                <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">{{ __('Note') }}</div>
                <div style="font-size:13px;color:#374151;line-height:1.55;" id="cm-note"></div>
            </div>

            {{-- Cancellation reason --}}
            <div id="cm-cancel-wrap" style="display:none;margin-top:12px;padding:10px 12px;background:#fef2f2;border-radius:8px;border:1px solid #fecaca;">
                <div style="font-size:10px;font-weight:700;color:#dc2626;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">{{ __('Cancellation Reason') }}</div>
                <div style="font-size:13px;color:#991b1b;" id="cm-cancel-reason"></div>
            </div>
        </div>

        {{-- Footer actions --}}
        <div id="cal-modal-footer"
             style="display:none;padding:12px 20px 14px;border-top:1px solid #f3f4f6;justify-content:flex-end;gap:8px;">
            <button onclick="calModal.close()"
                    style="padding:8px 16px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;font-size:13px;font-weight:600;color:#374151;cursor:pointer;font-family:inherit;"
                    onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">
                {{ __('Close') }}
            </button>
            <a id="cm-view-link" href="#"
               style="padding:8px 16px;border-radius:8px;background:#6d28d9;font-size:13px;font-weight:600;color:#fff;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:background .12s;"
               onmouseover="this.style.background='#5b21b6'" onmouseout="this.style.background='#6d28d9'">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                </svg>
                {{ __('Full Details') }}
            </a>
        </div>

    </div>
</div>

{{-- Calendar styles extracted to resources/css/components.css --}}

<script>
'use strict';
const calModal = {
    detailUrl: '{{ url('/manage/booking-calendar/show') }}',

    open(bookingId) {
        document.getElementById('cal-modal-backdrop').style.display = 'block';
        document.getElementById('cal-modal').style.display = 'block';
        document.getElementById('cal-modal-loading').style.display = 'block';
        document.getElementById('cal-modal-body').style.display    = 'none';
        document.getElementById('cal-modal-footer').style.display  = 'none';
        document.getElementById('cal-modal-title').textContent     = '{{ __('Loading…') }}';
        document.getElementById('cal-modal-subtitle').textContent  = '';
        document.getElementById('cal-modal-badge').textContent     = '';
        document.body.style.overflow = 'hidden';

        fetch(this.detailUrl + '/' + bookingId, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
        .then(r => r.ok ? r.json() : Promise.reject(r.status))
        .then(d => this.populate(d))
        .catch(() => {
            document.getElementById('cal-modal-title').textContent = '{{ __('Could not load booking.') }}';
            document.getElementById('cal-modal-loading').style.display = 'none';
        });
    },

    populate(d) {
        // Header
        document.getElementById('cal-modal-title').textContent    = d.service + ' — #' + d.id;
        document.getElementById('cal-modal-subtitle').textContent = d.date;
        const badge = document.getElementById('cal-modal-badge');
        badge.textContent  = d.status;
        badge.style.background = d.status_color + '1a';
        badge.style.color      = d.status_color;
        badge.style.border     = '1px solid ' + d.status_color + '44';

        // Fields
        document.getElementById('cm-date').textContent     = d.date;
        document.getElementById('cm-time').textContent     = d.start_time + ' – ' + d.end_time;
        document.getElementById('cm-service').textContent  = d.service;
        document.getElementById('cm-provider').textContent = d.provider;
        document.getElementById('cm-client').textContent   = d.client;
        document.getElementById('cm-phone').textContent    = d.phone  || '—';
        document.getElementById('cm-email').textContent    = d.email  || '—';
        document.getElementById('cm-payment').textContent  = d.payment_status + (d.amount ? ' · ' + d.currency + ' ' + d.amount : '');

        // Note
        const noteWrap = document.getElementById('cm-note-wrap');
        if (d.note) {
            document.getElementById('cm-note').textContent = d.note;
            noteWrap.style.display = 'block';
        } else {
            noteWrap.style.display = 'none';
        }

        // Cancellation reason
        const cancelWrap = document.getElementById('cm-cancel-wrap');
        if (d.cancellation_reason) {
            document.getElementById('cm-cancel-reason').textContent = d.cancellation_reason;
            cancelWrap.style.display = 'block';
        } else {
            cancelWrap.style.display = 'none';
        }

        // Full details link
        document.getElementById('cm-view-link').href = d.view_url;

        // Swap loading → body
        document.getElementById('cal-modal-loading').style.display = 'none';
        document.getElementById('cal-modal-body').style.display    = 'block';
        document.getElementById('cal-modal-footer').style.display  = 'flex';
    },

    close() {
        document.getElementById('cal-modal-backdrop').style.display = 'none';
        document.getElementById('cal-modal').style.display          = 'none';
        document.body.style.overflow = '';
    },
};

// Close on Escape key
document.addEventListener('keydown', e => { if (e.key === 'Escape') calModal.close(); });

(function () {
    function initCalendar() {
        const el = document.getElementById('slotara-calendar');
        if (!el || el.dataset.initialized) return;
        el.dataset.initialized = '1';

        const eventsUrl = '{{ route('booking.calendar.events') }}';

        const calendar = new FullCalendar.Calendar(el, {
            initialView:  'dayGridMonth',
            firstDay:     1,
            nowIndicator: true,
            dayMaxEvents: 3,
            height:       'auto',
            headerToolbar: {
                left:   'prev,next today',
                center: 'title',
                right:  'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
            },
            buttonText: { today: '{{ __('Today') }}', month: '{{ __('Month') }}', week: '{{ __('Week') }}', day: '{{ __('Day') }}', list: '{{ __('List') }}' },
            slotMinTime: '07:00:00',
            slotMaxTime: '22:00:00',
            eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },

            events: function (info, successCb, failureCb) {
                fetch(eventsUrl + '?start=' + info.startStr + '&end=' + info.endStr, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                })
                .then(r => r.ok ? r.json() : Promise.reject(r.status))
                .then(successCb)
                .catch(failureCb);
            },

            // Open modal instead of navigating
            eventClick: function (info) {
                info.jsEvent.preventDefault();
                const bookingId = info.event.extendedProps.bookingId;
                if (bookingId) calModal.open(bookingId);
            },

            eventDidMount: function (info) {
                const t = info.event.start
                    ? info.event.start.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })
                    : '';
                info.el.title = t ? t + ' · {{ __('Click to view details') }}' : '{{ __('Click to view details') }}';
                info.el.style.cursor = 'pointer';
            },
        });

        calendar.render();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCalendar);
    } else {
        initCalendar();
    }

    document.addEventListener('livewire:navigated', function () {
        const el = document.getElementById('slotara-calendar');
        if (el) el.dataset.initialized = '';
        initCalendar();
    });
})();
</script>

</x-filament-panels::page>
