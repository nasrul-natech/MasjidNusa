@extends('layouts.public')

@section('title', 'Donasi')

@section('content')
<div class="max-w-screen-md mx-auto px-4 py-6" x-data="donationForm()">

    {{-- Header --}}
    <div class="mb-5">
        <h1 class="text-xl font-bold text-gray-900">Donasi & Infaq</h1>
        <p class="text-sm text-gray-500 mt-0.5">Setiap kebaikan akan dibalas berlipat ganda</p>
    </div>

    {{-- Donation Form Card --}}
    <div class="card p-5 mb-6">

        {{-- Category Filter --}}
        @if($categories->count())
        <div class="mb-5">
            <p class="form-label">Kategori Donasi</p>
            <div class="flex flex-wrap gap-2">
                @foreach($categories as $cat)
                <button type="button"
                    @click="category = '{{ $cat->slug }}'; categoryName = '{{ $cat->name }}'"
                    :class="category === '{{ $cat->slug }}'
                        ? 'bg-emerald-700 text-white border-emerald-700'
                        : 'bg-white text-gray-600 border-gray-200 hover:border-emerald-400'"
                    class="px-4 py-2 rounded-xl text-sm font-semibold border transition-colors">
                    {{ $cat->name }}
                </button>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Preset Amounts --}}
        <div class="mb-5">
            <p class="form-label">Jumlah Donasi</p>
            <div class="grid grid-cols-3 gap-2 mb-3">
                @foreach([10000, 25000, 50000, 100000, 250000, 500000] as $preset)
                <button type="button"
                    @click="amount = {{ $preset }}; customAmount = ''"
                    :class="amount === {{ $preset }} && !customAmount
                        ? 'border-emerald-600 bg-emerald-50 text-emerald-700 font-bold'
                        : 'border-gray-200 text-gray-700 hover:border-emerald-400'"
                    class="py-2.5 px-2 rounded-xl text-sm font-semibold border transition-colors text-center">
                    Rp {{ number_format($preset, 0, ',', '.') }}
                </button>
                @endforeach
            </div>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold text-sm">Rp</span>
                <input type="text" x-model="customAmount"
                    @input="amount = parseInt(customAmount.replace(/\D/g, '')) || 0"
                    class="form-input pl-10" placeholder="Atau masukkan nominal lain">
            </div>
        </div>

        {{-- Donor Name --}}
        <div class="mb-4">
            <label class="form-label">Nama Donatur</label>
            <input type="text" x-model="donorName" class="form-input" placeholder="Masukkan nama Anda">
        </div>

        {{-- Anonymous --}}
        <label class="flex items-center gap-2 mb-4 cursor-pointer">
            <input type="checkbox" x-model="isAnonymous" class="rounded text-emerald-600">
            <span class="text-sm text-gray-700">Sembunyikan nama (Hamba Allah)</span>
        </label>

        {{-- Message --}}
        <div class="mb-5">
            <label class="form-label">Pesan/Doa (Opsional)</label>
            <textarea x-model="message" class="form-input" rows="3" placeholder="Semoga menjadi amal jariyah..."></textarea>
        </div>

        {{-- Submit Button --}}
        @auth
        <button @click="openPaymentMethod()"
            :disabled="!amount || amount < 1000"
            class="btn-primary w-full py-3.5 text-base disabled:opacity-50 disabled:cursor-not-allowed">
            💝 Kirim Donasi
        </button>
        @else
        <a href="{{ route('login') }}?redirect={{ urlencode(route('donasi.index')) }}"
            class="btn-primary w-full py-3.5 text-base text-center block">
            💝 Login untuk Berdonasi
        </a>
        @endauth

        <p x-show="errorMsg" x-text="errorMsg" class="text-red-500 text-sm mt-2 text-center"></p>
    </div>

    {{-- ========== POPUP: Payment Method Selection ========== --}}
    <div x-show="step === 1" x-transition
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        style="display:none" @click.self="step = 0">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-900">Pilih Metode Pembayaran</h3>
                <button @click="step = 0" class="p-1 rounded-lg hover:bg-gray-100 text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="bg-emerald-50 rounded-xl p-4 mb-5 flex justify-between items-center">
                <span class="text-sm text-emerald-700">Total Donasi</span>
                <span class="text-lg font-bold text-emerald-800" x-text="'Rp ' + parseInt(amount).toLocaleString('id-ID')"></span>
            </div>

            <div class="space-y-3">
                <button @click="selectMethod('manual')"
                    :class="paymentMethod === 'manual' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200 hover:border-gray-300'"
                    class="w-full flex items-center gap-4 p-4 rounded-xl border-2 transition-colors text-left">
                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">Transfer Manual</p>
                        <p class="text-xs text-gray-500">Transfer ke rekening masjid</p>
                    </div>
                    <svg x-show="paymentMethod === 'manual'" class="w-5 h-5 text-emerald-600 ml-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                </button>
            </div>

            <button @click="confirmPayment()"
                :disabled="!paymentMethod || submitting"
                class="btn-primary w-full py-3 mt-5 disabled:opacity-50">
                <span x-show="!submitting">Konfirmasi Donasi</span>
                <span x-show="submitting" class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Memproses...
                </span>
            </button>
        </div>
    </div>

    {{-- ========== POPUP: Bank Transfer Info ========== --}}
    <div x-show="step === 2" x-transition
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        style="display:none" @click.self="step = 0">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[85vh] overflow-y-auto">
            <div class="p-6 pb-0">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-900">Info Transfer</h3>
                    <button @click="step = 0; resetForm()" class="p-1 rounded-lg hover:bg-gray-100 text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="bg-emerald-700 rounded-xl p-4 text-white mb-4">
                    <p class="text-sm opacity-80">Total yang ditransfer</p>
                    <p class="text-2xl font-bold" x-text="'Rp ' + parseInt(amount).toLocaleString('id-ID')"></p>
                    <p class="text-xs opacity-70 mt-1" x-text="'Kode Donasi: ' + (donationCode || '-')"></p>
                </div>
            </div>

            <div class="px-6 pb-6">
                @foreach($bankAccounts as $bank)
                <div class="border border-gray-200 rounded-xl p-4 mb-3">
                    <div class="flex items-center justify-between mb-2">
                        @if($bank->bank_logo_url)
                        <img src="{{ $bank->bank_logo_url }}" class="h-6 object-contain" alt="{{ $bank->bank_name }}">
                        @else
                        <span class="font-semibold text-gray-900 text-sm">{{ $bank->bank_name }}</span>
                        @endif
                    </div>
                    <div class="space-y-1.5 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">No. Rekening</span>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-semibold text-gray-900">{{ $bank->account_number }}</span>
                                <button @click="copyText('{{ $bank->account_number }}')" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">Salin</button>
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Atas Nama</span>
                            <span class="font-semibold text-gray-900">{{ $bank->account_name }}</span>
                        </div>
                    </div>
                </div>
                @endforeach

                @if($bankAccounts->isEmpty())
                <div class="text-center py-6 text-gray-500 text-sm">
                    Belum ada rekening bank dikonfigurasi.
                </div>
                @endif

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4">
                    <p class="text-xs font-semibold text-amber-800 mb-2">Penting:</p>
                    <ul class="text-xs text-amber-700 space-y-1 list-disc list-inside">
                        <li>Transfer sesuai nominal yang tertera</li>
                        <li>Sertakan kode donasi pada berita transfer</li>
                        <li>Upload bukti transfer di menu Riwayat Donasi</li>
                    </ul>
                </div>

                <a href="{{ route('user.donations.history') }}" class="btn-primary w-full py-3 text-center">
                    Upload Bukti Transfer
                </a>
                <button @click="step = 0; resetForm()" class="w-full mt-3 text-sm text-gray-500 hover:text-gray-700 py-2">Tutup</button>
            </div>
        </div>
    </div>

    {{-- Recent Donations --}}
    @if($recentDonations->count())
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-3">
            <span class="text-base">🕐</span>
            <h2 class="font-bold text-gray-900">Donasi Terbaru</h2>
        </div>
        <div class="space-y-2">
            @foreach($recentDonations as $donation)
            <div class="bg-white rounded-2xl border border-gray-100 p-3.5 flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 text-sm font-bold shrink-0">
                    {{ substr($donation->is_anonymous ? 'H' : $donation->donor_name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ $donation->is_anonymous ? 'Hamba Allah' : $donation->donor_name }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                @if($donation->campaign)
                                <span class="text-xs text-emerald-600">{{ $donation->campaign->category?->name ?? 'Donasi' }}</span>
                                <span class="text-gray-300 text-xs">•</span>
                                @endif
                                <span class="text-xs text-gray-400">{{ $donation->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <p class="font-bold text-emerald-700 text-sm whitespace-nowrap ml-2">Rp {{ number_format($donation->amount, 0, ',', '.') }}</p>
                    </div>
                    @if($donation->message)
                    <p class="text-xs text-gray-500 mt-1.5 italic line-clamp-2">{{ $donation->message }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Active Programs --}}
    @if($campaigns->count())
    <div class="mb-4">
        <h2 class="font-bold text-gray-900 mb-3">Program Donasi</h2>
        <div class="space-y-2">
            @foreach($campaigns as $campaign)
            <a href="{{ route('donasi.show', $campaign->slug) }}" class="card p-3.5 flex items-center gap-3 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 overflow-hidden shrink-0">
                    @if($campaign->banner)
                    <img src="{{ $campaign->banner_url }}" class="w-full h-full object-cover" alt="{{ $campaign->title }}">
                    @else
                    <div class="w-full h-full flex items-center justify-center text-lg">💝</div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm text-gray-900 truncate">{{ $campaign->title }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full" style="width: {{ min($campaign->progress_percent, 100) }}%"></div>
                        </div>
                        <span class="text-xs font-semibold text-emerald-600">{{ number_format($campaign->progress_percent, 0) }}%</span>
                    </div>
                </div>
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function donationForm() {
    return {
        category: '{{ $categories->first()?->slug ?? '' }}',
        categoryName: '{{ $categories->first()?->name ?? 'Donasi' }}',
        amount: 0,
        customAmount: '',
        donorName: '{{ auth()->user()?->name ?? '' }}',
        isAnonymous: false,
        message: '',
        submitting: false,
        errorMsg: '',
        step: 0,
        paymentMethod: 'manual',
        donationCode: '',

        copyText(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Nomor rekening disalin!', 'success');
            });
        },

        openPaymentMethod() {
            if (!this.amount || this.amount < 1000) {
                this.errorMsg = 'Nominal minimal Rp 1.000';
                return;
            }
            this.errorMsg = '';
            this.step = 1;
        },

        selectMethod(method) {
            this.paymentMethod = method;
        },

        resetForm() {
            this.amount = 0;
            this.customAmount = '';
            this.message = '';
            this.donationCode = '';
            this.step = 0;
        },

        async confirmPayment() {
            if (!this.paymentMethod || this.submitting) return;
            this.submitting = true;

            try {
                const res = await fetch('{{ route("user.donations.quick") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        amount: this.amount,
                        donor_name: this.isAnonymous ? 'Hamba Allah' : this.donorName,
                        is_anonymous: this.isAnonymous,
                        message: this.message,
                        category: this.category,
                        payment_method: this.paymentMethod,
                    })
                });

                const data = await res.json();
                if (data.success) {
                    this.donationCode = data.donation_code;
                    if (this.paymentMethod === 'duitku' && data.payment_url) {
                        window.location.href = data.payment_url;
                    } else {
                        this.step = 2;
                    }
                } else {
                    this.step = 0;
                    showSysModal({ title: 'Gagal', body: data.message || 'Terjadi kesalahan', icon: '❌', type: 'error', confirmText: 'OK' });
                }
            } catch (e) {
                this.step = 0;
                showSysModal({ title: 'Koneksi Gagal', body: 'Gagal memproses donasi. Silakan coba lagi.', icon: '⚠️', type: 'warning', confirmText: 'OK' });
            }

            this.submitting = false;
        }
    }
}
</script>
@endpush
@endsection
