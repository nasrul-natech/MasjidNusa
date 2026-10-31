@if(\App\Support\NewFeature::enabled('activity_attendance') && $activity->attendance_phase !== 'disabled')
@php($phase = $activity->attendance_phase)
<span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-1 text-xs font-semibold text-emerald-800 my-2"
    data-attendance-activity="{{ $activity->id }}" x-show="phase !== 'disabled'"
    x-data="{ phase: @js($phase), start: @js($activity->start_date->getTimestampMs()), deadline: @js($activity->attendanceSession?->closes_at->getTimestampMs()), offset: {{ now()->getTimestampMs() }}, mounted: Date.now(), timer: null,
        tick() { const current = this.offset + Date.now() - this.mounted; if (this.phase === 'upcoming' &amp;&amp; current &gt;= this.start) this.phase = 'ongoing'; if (this.phase === 'checkin' &amp;&amp; current &gt;= this.deadline) this.phase = 'closed'; },
        init() { this.tick(); this.timer = setInterval(() => this.tick(), 1000); }, destroy() { clearInterval(this.timer); } }"
    @activity-phase.window="if ($event.detail.id === {{ $activity->id }}) { phase = $event.detail.phase; start = $event.detail.starts_at; deadline = $event.detail.closes_at; offset = $event.detail.server_now; mounted = Date.now(); tick(); }">
    <span class="h-1.5 w-1.5 rounded-full bg-emerald-600" aria-hidden="true"></span>
    <span x-text="{upcoming:'Terjadwal', ongoing:'Sesi Aktif · Berlangsung', checkin:'Check-in Dibuka', closed:'Check-in Ditutup'}[phase]">{{ ['upcoming' => 'Terjadwal', 'ongoing' => 'Sesi Aktif · Berlangsung', 'checkin' => 'Check-in Dibuka', 'closed' => 'Check-in Ditutup'][$phase] }}</span>
</span>
@once
@push('scripts')
<script>
(() => {
    let timer, delay = 15000, running = false;
    async function refreshPhases() {
        clearTimeout(timer);
        if (document.hidden || running) return;
        running = true;
        try {
            const ids = [...new Set([...document.querySelectorAll('[data-attendance-activity]')].map(node => node.dataset.attendanceActivity))].slice(0, 20);
            if (!ids.length) return;
            const url = new URL(@js(route('attendance.phases')));
            ids.forEach(id => url.searchParams.append('ids[]', id));
            const response = await fetch(url, {cache:'no-store', headers:{Accept:'application/json'}});
            if (!response.ok) throw new Error('unavailable');
            const result = await response.json();
            result.activities.forEach(activity => window.dispatchEvent(new CustomEvent('activity-phase', {detail:{...activity, server_now:result.server_now}})));
            delay = 15000;
        } catch (_) { delay = Math.min(delay * 2, 60000); }
        finally { running = false; if (!document.hidden) timer = setTimeout(refreshPhases, delay); }
    }
    document.addEventListener('visibilitychange', () => { clearTimeout(timer); if (!document.hidden) refreshPhases(); });
    window.addEventListener('online', refreshPhases);
    refreshPhases();
})();
</script>
@endpush
@endonce
@endif
