@props(['campaign'])

<div x-data="paymentPopup()" class="contents">
    {{-- Trigger button --}}
    <button @click="openDonation()" class="btn-primary w-full py-3.5 text-base">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
        </svg>
        Donasi Sekarang
    </button>

    {{-- Step 1: Amount --}}
    <div x-show="step === 1" x-transition class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/50" style="display:none">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-bold text-lg text-gray-900">Masukkan Nominal</h3>
                <button @click="step = 0" class="p-1 rounded-lg hover:bg-gray-100 text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="mb-4">
                <label class="form-label">Jumlah Donasi</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold text-sm">Rp</span>
                    <input
                        type="number"
                        x-model="amount"
                        min="10000"
                        class="form-input pl-10"
                        placeholder="50.000"
                    >
                </div>
            </div>

            {{-- Quick amounts --}}
            <div class="grid grid-cols-3 gap-2 mb-5">
                @foreach([25000, 50000, 100000, 200000, 500000, 1000000] as $amt)
                <button @click="amount = {{ $amt }}" :class="amount == {{ $amt }} ? 'bg-emerald-700 text-white border-emerald-700' : 'bg-white text-gray-700 border-gray-200 hover:border-emerald-500'" class="px-3 py-2 text-sm font-semibold rounded-xl border transition-colors">
                    {{ 'Rp ' . number_format($amt, 0, ',', '.') }}
                </button>
                @endforeach
            </div>

            <div class="mb-4">
                <label class="form-label">Pesan (Opsional)</label>
                <textarea x-model="message" class="form-input" rows="2" placeholder="Semoga bermanfaat..."></textarea>
            </div>

            <label class="flex items-center gap-2 mb-5 cursor-pointer">
                <input type="checkbox" x-model="isAnonymous" class="rounded text-emerald-600">
                <span class="text-sm text-gray-600">Donasi sebagai anonim</span>
            </label>

            <button @click="nextStep()" :disabled="!amount || amount < 10000" class="btn-primary w-full py-3 disabled:opacity-50">
                Lanjutkan →
            </button>
        </div>
    </div>

    {{-- Step 2: Payment Method --}}
    <div x-show="step === 2" x-transition class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/50" style="display:none">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-5">
                <button @click="step = 1" class="p-1 rounded-lg hover:bg-gray-100 text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <h3 class="font-bold text-lg text-gray-900">Pilih Metode</h3>
                <button @click="step = 0" class="p-1 rounded-lg hover:bg-gray-100 text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="bg-emerald-50 rounded-xl p-4 mb-5 flex justify-between">
                <span class="text-sm text-emerald-700">Total Donasi</span>
                <span class="text-lg font-bold text-emerald-800" x-text="'Rp ' + parseInt(amount).toLocaleString('id-ID')"></span>
            </div>

            <div class="space-y-3">
                @if(\App\Models\PaymentConfiguration::isManualEnabled())
                <button @click="selectMethod('manual')" :class="method === 'manual' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200 hover:border-gray-300'" class="w-full flex items-center gap-4 p-4 rounded-xl border-2 transition-colors text-left">
                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">Transfer Manual</p>
                        <p class="text-xs text-gray-500">Transfer ke rekening masjid</p>
                    </div>
                    <svg x-show="method === 'manual'" class="w-5 h-5 text-emerald-600 ml-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                </button>
                @endif

                @if(\App\Models\PaymentConfiguration::isDuitkuEnabled())
                <button @click="selectMethod('duitku')" :class="method === 'duitku' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200 hover:border-gray-300'" class="w-full flex items-center gap-4 p-4 rounded-xl border-2 transition-colors text-left">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">Duitku Gateway</p>
                        <p class="text-xs text-gray-500">QRIS, VA, Kartu Kredit, dll</p>
                    </div>
                    <svg x-show="method === 'duitku'" class="w-5 h-5 text-emerald-600 ml-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                </button>
                @endif
            </div>

            <button @click="submitDonation()" :disabled="!method || loading" class="btn-primary w-full py-3 mt-5 disabled:opacity-50">
                <span x-show="!loading">Konfirmasi Donasi</span>
                <span x-show="loading" class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Memproses...
                </span>
            </button>
        </div>
    </div>

    {{-- Step 3: Manual Transfer Instructions --}}
    <div x-show="step === 3 && method === 'manual'" x-transition class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/50" style="display:none">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[85vh] overflow-y-auto">
            <div class="p-6 pb-0">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-bold text-lg text-gray-900">Info Transfer</h3>
                    <button @click="step = 0; reset()" class="p-1 rounded-lg hover:bg-gray-100 text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="bg-emerald-700 rounded-xl p-4 text-white mb-4">
                    <p class="text-sm opacity-80">Total yang ditransfer</p>
                    <p class="text-2xl font-bold" x-text="'Rp ' + parseInt(amount).toLocaleString('id-ID')"></p>
                    <p class="text-xs opacity-70 mt-1" x-text="'Kode: ' + (donationCode || '-')"></p>
                </div>
            </div>

            <div class="px-6 pb-6">
                @php $accounts = \App\Models\BankAccount::active()->get(); @endphp
                @foreach($accounts as $account)
                <div class="border border-gray-200 rounded-xl p-4 mb-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold text-gray-900 text-sm">{{ $account->bank_name }}</span>
                    </div>
                    <div class="space-y-1.5 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">No. Rekening</span>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-semibold text-gray-900">{{ $account->account_number }}</span>
                                <button onclick="navigator.clipboard.writeText('{{ $account->account_number }}')" class="text-xs text-emerald-600 hover:text-emerald-700">Salin</button>
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Atas Nama</span>
                            <span class="font-semibold text-gray-900">{{ $account->account_name }}</span>
                        </div>
                    </div>
                </div>
                @endforeach

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4">
                    <p class="text-xs font-semibold text-amber-800 mb-2">⚠️ Penting:</p>
                    <ul class="text-xs text-amber-700 space-y-1 list-disc list-inside">
                        <li>Transfer sesuai nominal yang tertera</li>
                        <li>Sertakan kode donasi pada berita transfer</li>
                        <li>Simpan bukti transfer untuk diupload</li>
                    </ul>
                </div>

                {{-- Upload proof form --}}
                <div x-show="transactionId">
                    <form :action="'{{ url('user/upload-bukti') }}/' + transactionId" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label class="form-label">Upload Bukti Transfer</label>
                        <input type="file" name="proof" accept="image/*" required class="form-input mb-3">
                        <button type="submit" class="btn-primary w-full py-3">Upload Bukti Transfer</button>
                    </form>
                </div>
                <div x-show="!transactionId" class="text-center text-sm text-red-500 py-2">
                    Kode transaksi tidak ditemukan. Silakan hubungi admin atau upload dari halaman riwayat donasi.
                </div>

                <button @click="step = 0; reset()" class="w-full mt-3 text-sm text-gray-500 hover:text-gray-700 py-2">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function paymentPopup() {
    return {
        step: 0,
        amount: null,
        message: '',
        isAnonymous: false,
        method: null,
        loading: false,
        donationId: null,
        donationCode: null,
        transactionId: null,

        openDonation() {
            @auth
            this.step = 1;
            @else
            window.location.href = '{{ route("login") }}?redirect={{ url()->current() }}';
            @endauth
        },

        nextStep() {
            if (this.amount >= 10000) this.step = 2;
        },

        selectMethod(m) {
            this.method = m;
        },

        reset() {
            this.step = 0;
            this.amount = null;
            this.message = '';
            this.isAnonymous = false;
            this.method = null;
            this.loading = false;
            this.donationId = null;
            this.donationCode = null;
            this.transactionId = null;
        },

        async submitDonation() {
            if (!this.method || this.loading) return;
            this.loading = true;

            try {
                const res = await fetch('{{ route("user.donations.store", $campaign->slug) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        amount: this.amount,
                        message: this.message,
                        is_anonymous: this.isAnonymous,
                        payment_method: this.method,
                    })
                });

                const data = await res.json();

                if (data.success) {
                    this.donationId = data.donation_id;
                    this.donationCode = data.donation_code;
                    this.transactionId = data.transaction_id || null;

                    if (this.method === 'duitku' && data.payment_url) {
                        window.location.href = data.payment_url;
                    } else {
                        this.step = 3;
                    }
                } else {
                    showSysModal({ title: 'Gagal', body: data.message || 'Terjadi kesalahan', icon: '❌', type: 'error', confirmText: 'OK' });
                }
            } catch (e) {
                showSysModal({ title: 'Koneksi Gagal', body: 'Gagal memproses donasi. Silakan coba lagi.', icon: '⚠️', type: 'warning', confirmText: 'OK' });
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
