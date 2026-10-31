<section class="card overflow-hidden mb-5" x-data="{
    state: null, label: 'Memuat status absensi...', available: false, busy: false, online: navigator.onLine, timer: null, ticker: null, delay: 5000, offset: 0, stopped: false,
    async refresh() {
        if (this.stopped || document.hidden) return;
        try {
            if (!navigator.onLine) throw new Error('offline');
            const response = await fetch(@js(route('attendance.state', $activity)), {cache: 'no-store', headers: {'Accept':'application/json'}});
            if (!response.ok) throw new Error('unavailable');
            this.state = await response.json(); this.offset = Date.parse(this.state.server_now) - Date.now(); this.online = true; this.delay = 5000; this.tick();
        } catch (_) { this.online = false; this.available = false; this.label = 'Status belum dapat diperbarui. Check-in memerlukan koneksi.'; this.delay = Math.min(this.delay * 2, 60000); }
        const terminal = this.state &amp;&amp; (this.state.checked_in || (this.state.closes_at &amp;&amp; Date.now() + this.offset &gt;= Date.parse(this.state.closes_at)));
        if (!this.stopped &amp;&amp; !terminal &amp;&amp; !document.hidden) this.timer = setTimeout(() => this.refresh(), this.delay);
    },
    tick() {
        this.available = false;
        if (!this.online || !this.state) return;
        const now = Date.now() + this.offset;
        if (!this.state.enabled) { this.label = 'Absensi tidak diaktifkan.'; return; }
        if (this.state.checked_in) { this.label = 'Sudah check-in. 1 poin kehadiran tercatat.'; return; }
        if (!this.state.closes_at &amp;&amp; now &lt; Date.parse(this.state.starts_at)) { this.label = 'Kegiatan dimulai dalam ' + this.remaining(Date.parse(this.state.starts_at) - now); return; }
        if (!this.state.closes_at) { this.label = 'Kegiatan berlangsung, absensi belum dibuka.'; return; }
        const left = Date.parse(this.state.closes_at) - now;
        this.label = left &gt; 0 ? 'Check-in ditutup dalam ' + this.remaining(left) : 'Check-in ditutup';
        this.available = left &gt; 0 &amp;&amp; this.state.eligible;
    },
    remaining(ms) { const seconds = Math.max(0, Math.ceil(ms / 1000)); return Math.floor(seconds / 60) + ':' + String(seconds % 60).padStart(2, '0'); },
    async checkIn() {
        if (this.busy || !this.available) return; this.busy = true;
        try {
            const response = await fetch(@js(route('attendance.store', $activity)), {method:'POST', cache:'no-store', headers:{'Accept':'application/json','X-CSRF-TOKEN': @js(csrf_token())}});
            const result = await response.json();
            if (!response.ok) throw new Error(result.errors?.attendance?.[0] || result.message || 'Check-in gagal. Muat ulang halaman dan coba kembali.');
            this.state.checked_in = true; this.tick();
        } catch (error) { this.label = error.message; this.available = false; }
        finally { this.busy = false; }
    },
    init() { this.refresh(); this.ticker = setInterval(() => this.tick(), 1000); },
    destroy() { this.stopped = true; clearTimeout(this.timer); clearInterval(this.ticker); }
}" @offline.window="online = false; available = false; label = 'Koneksi terputus. Check-in memerlukan koneksi.'" @online.window="clearTimeout(timer); refresh()" @visibilitychange.document="clearTimeout(timer); if (!document.hidden) refresh()">
    <div class="card-header flex flex-wrap items-center justify-between gap-2">
        <h2 class="font-semibold text-gray-900">Check-in Kegiatan</h2>
        <x-badge type="success">1 kegiatan = 1 poin</x-badge>
    </div>
    <div class="card-body">
    <p class="text-sm text-gray-500 mb-3">Catat kehadiran saat check-in dibuka oleh pengurus.</p>
    <p class="rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-sm font-medium leading-relaxed text-emerald-900 tabular-nums" x-text="label" role="status" aria-live="polite"></p>
    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:flex-wrap">
    @auth
    <button type="button" class="btn-primary w-full sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed" x-show="available" :disabled="busy || !available" @click="checkIn()" x-cloak>Check-in</button>
    @if(auth()->user()->isJamaah() && !auth()->user()->isAdmin())
    <a href="{{ route('user.attendance') }}" class="btn-secondary text-center">Riwayat kehadiran saya</a>
    @endif
    @else
    <a href="{{ route('login') }}" class="btn-primary text-center">Login untuk Check-in</a>
    @endauth
    </div>
    </div>
</section>
