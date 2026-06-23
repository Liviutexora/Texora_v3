{{--
    Weekly date picker strip.
    Expects these variables already in scope (set by booking-wizard.blade.php @php block):
      $calToday, $calMax, $calInitY, $calInitM, $calClosedDays, $selectedDate, $brand, $brandLight
--}}
<div x-data="{
    today:      '{{ $calToday }}',
    maxDate:    '{{ $calMax }}',
    selected:   '{{ $selectedDate ?? '' }}',
    closedDays: {{ $calClosedDays }},
    weekStart:  '',

    init() {
        let ref = this.selected || this.today;
        let d = new Date(ref + 'T00:00');
        d.setDate(d.getDate() - d.getDay()); // roll back to Sunday
        this.weekStart = this.fmt(d);
    },

    fmt(d) {
        return d.getFullYear() + '-' +
               String(d.getMonth()+1).padStart(2,'0') + '-' +
               String(d.getDate()).padStart(2,'0');
    },

    get weekDays() {
        let days = [], s = new Date(this.weekStart + 'T00:00');
        for (let i = 0; i < 7; i++) {
            let d = new Date(s); d.setDate(s.getDate() + i);
            days.push(this.fmt(d));
        }
        return days;
    },

    get weekLabel() {
        let d0 = new Date(this.weekStart + 'T00:00');
        let d6 = new Date(d0); d6.setDate(d0.getDate() + 6);
        if (d0.getMonth() === d6.getMonth()) {
            return d0.toLocaleString('default', { month: 'long', year: 'numeric' });
        }
        return d0.toLocaleString('default', { month: 'short', day: 'numeric' }) +
               ' – ' + d6.toLocaleString('default', { month: 'short', day: 'numeric', year: 'numeric' });
    },

    canPrev() {
        // allow prev if any day in the next-back week is >= today
        let d = new Date(this.weekStart + 'T00:00'); d.setDate(d.getDate() - 1);
        return this.fmt(d) >= this.today;
    },

    canNext() {
        let d = new Date(this.weekStart + 'T00:00'); d.setDate(d.getDate() + 7);
        return this.fmt(d) <= this.maxDate;
    },

    prevWeek() {
        if (!this.canPrev()) return;
        let d = new Date(this.weekStart + 'T00:00'); d.setDate(d.getDate() - 7);
        this.weekStart = this.fmt(d);
    },

    nextWeek() {
        if (!this.canNext()) return;
        let d = new Date(this.weekStart + 'T00:00'); d.setDate(d.getDate() + 7);
        this.weekStart = this.fmt(d);
    },

    isDisabled(d) {
        if (!d || d < this.today || d > this.maxDate) return true;
        if (this.closedDays.length) {
            let dow = new Date(d + 'T00:00').getDay();
            if (this.closedDays.includes(dow)) return true;
        }
        return false;
    },
    isSelected(d) { return d === this.selected; },
    isToday(d)    { return d === this.today; },

    selectDay(d) {
        if (this.isDisabled(d)) return;
        this.selected = d;
        $wire.set('selectedDate', d);
        $wire.set('selectedStart', null);
        $wire.set('selectedEnd', null);
    },

    dayAbbr(d) { return new Date(d + 'T00:00').toLocaleString('default', { weekday: 'short' }); },
    dayNum(d)  { return new Date(d + 'T00:00').getDate(); }
}" style="display:flex;flex-direction:column;gap:12px;">

    {{-- Week nav header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;
                padding:10px 14px;background:var(--bw-bg-subtle);
                border:1px solid var(--bw-border);border-radius:10px;
                color:var(--bw-text);">
        <button type="button" @click="prevWeek()"
            :style="canPrev()?'opacity:1;cursor:pointer;':'opacity:0.25;cursor:default;'"
            style="width:28px;height:28px;border:1px solid var(--bw-border);border-radius:6px;
                   background:var(--bw-bg);display:flex;align-items:center;justify-content:center;padding:0;flex-shrink:0;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <span x-text="weekLabel" style="font-size:13px;font-weight:600;color:var(--bw-text);"></span>
        <button type="button" @click="nextWeek()"
            :style="canNext()?'opacity:1;cursor:pointer;':'opacity:0.25;cursor:default;'"
            style="width:28px;height:28px;border:1px solid var(--bw-border);border-radius:6px;
                   background:var(--bw-bg);display:flex;align-items:center;justify-content:center;padding:0;flex-shrink:0;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
    </div>

    {{-- 7-day strip --}}
    <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:5px;">
        <template x-for="day in weekDays" :key="day">
            <button type="button"
                @click="selectDay(day)"
                :disabled="isDisabled(day)"
                :style="`
                    display:flex; flex-direction:column; align-items:center; gap:5px;
                    padding:10px 2px; border-radius:12px; font-family:inherit;
                    cursor: ${isDisabled(day) ? 'default' : 'pointer'};
                    opacity: ${isDisabled(day) ? '0.35' : '1'};
                    border: ${isSelected(day) ? '2px solid {{ $brand }}' : '1px solid var(--bw-border)'};
                    background: ${isSelected(day) ? '{{ $brandLight }}' : isToday(day) ? 'var(--bw-bg-subtle)' : 'var(--bw-bg)'};
                    transition: all .1s;
                `">
                <span x-text="dayAbbr(day)"
                    :style="`
                        font-size:10px; font-weight:600; letter-spacing:.2px; display:block;
                        color: ${isSelected(day) ? '{{ $brand }}' : 'var(--bw-text-4)'};
                    `"></span>
                <div :style="`
                    width:28px; height:28px; border-radius:50%;
                    display:flex; align-items:center; justify-content:center;
                    font-size:13px;
                    font-weight: ${isSelected(day) || isToday(day) ? '700' : '500'};
                    background: ${isSelected(day) ? '{{ $brand }}' : 'transparent'};
                    border: ${isToday(day) && !isSelected(day) ? '1.5px solid {{ $brand }}' : '1.5px solid transparent'};
                    color: ${isSelected(day) ? '#fff' : isToday(day) ? '{{ $brand }}' : 'var(--bw-text)'};
                `" x-text="dayNum(day)"></div>
            </button>
        </template>
    </div>

</div>
