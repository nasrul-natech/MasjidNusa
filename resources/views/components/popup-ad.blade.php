@php
    $popupAds = \App\Models\SystemModule::isActive('popup_ads')
        ? \App\Models\PopupAd::active()->get()
        : collect();
@endphp

@if($popupAds->isNotEmpty())
<div id="popup-ad-overlay"
    class="fixed inset-0 flex items-center justify-center p-5 transition-opacity duration-300"
    style="display:none; opacity:0; z-index:99999; background:rgba(0,0,0,0.45); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);"
    onclick="if(event.target===this) popupManager.close()">

    @foreach($popupAds as $i => $popup)
    <div id="popup-ad-{{ $i }}"
        data-duration="{{ $popup->duration }}"
        data-index="{{ $i }}"
        class="popup-ad-item relative w-full max-w-sm transform transition-all duration-300 scale-95 opacity-0"
        style="display:none">

        {{-- Card putih dengan padding/gap --}}
        <div class="bg-white rounded-3xl overflow-hidden shadow-2xl" style="margin: 0 2px;">

            {{-- Header bar dengan timer --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Informasi</span>
                </div>
                <div id="popup-timer-{{ $i }}"
                    class="flex items-center gap-1 bg-gray-100 text-gray-600 text-xs font-bold px-2.5 py-1 rounded-full">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span id="popup-timer-text-{{ $i }}">{{ $popup->duration }}s</span>
                </div>
            </div>

            {{-- Gambar --}}
            @if($popup->image)
            <div class="{{ $popup->ad_type === 'link' ? 'h-52' : 'aspect-[3/4] max-h-[60vh]' }} overflow-hidden">
                @if($popup->ad_type === 'link' && $popup->link)
                <a href="{{ $popup->link }}" target="{{ str_starts_with($popup->link, 'http') ? '_blank' : '_self' }}" onclick="popupManager.close()">
                    <img src="{{ asset('storage/' . $popup->image) }}" alt="{{ $popup->title }}" class="w-full h-full object-cover" loading="eager">
                </a>
                @else
                <img src="{{ asset('storage/' . $popup->image) }}" alt="{{ $popup->title }}" class="w-full h-full object-cover" loading="eager">
                @endif
            </div>
            @endif

            {{-- Konten teks (hanya untuk tipe link) --}}
            @if($popup->ad_type === 'link')
            <div class="px-4 pt-3 pb-1">
                <h3 class="font-bold text-gray-900 text-base leading-tight">{{ $popup->title }}</h3>
                @if($popup->content)
                <p class="text-sm text-gray-500 mt-1 leading-relaxed">{{ $popup->content }}</p>
                @endif
            </div>
            @endif

            {{-- Footer: action button + close --}}
            <div class="p-4 pt-3 flex flex-col gap-2">
                @if($popup->ad_type === 'link' && $popup->link)
                <a href="{{ $popup->link }}"
                    target="{{ str_starts_with($popup->link, 'http') ? '_blank' : '_self' }}"
                    onclick="popupManager.close()"
                    class="w-full py-3 bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold rounded-2xl flex items-center justify-center gap-2 transition-colors">
                    Lihat Selengkapnya
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endif

                <button onclick="popupManager.close()"
                    class="w-full py-2.5 border border-gray-200 hover:bg-gray-50 text-gray-500 text-sm font-medium rounded-2xl flex items-center justify-center gap-1.5 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Tutup
                </button>
            </div>
        </div>
    </div>
    @endforeach
</div>

<script>
window.popupManager = (function() {
    const popups = @json($popupAds->values()->map(fn($p, $i) => ['index' => $i, 'duration' => $p->duration]));
    const SESSION_KEY = 'popup_shown_{{ now()->format("Ymd") }}';
    let currentIndex = 0;
    let timer = null;

    if (sessionStorage.getItem(SESSION_KEY) || popups.length === 0) {
        return { close: function(){} };
    }

    function showOverlay() {
        const el = document.getElementById('popup-ad-overlay');
        el.style.display = 'flex';
        requestAnimationFrame(() => { el.style.opacity = '1'; });
    }

    function hideOverlay() {
        const el = document.getElementById('popup-ad-overlay');
        el.style.opacity = '0';
        setTimeout(() => { el.style.display = 'none'; }, 300);
        sessionStorage.setItem(SESSION_KEY, '1');
        clearInterval(timer);
    }

    function showPopup(idx) {
        if (idx >= popups.length) { hideOverlay(); return; }

        showOverlay();
        document.querySelectorAll('.popup-ad-item').forEach(el => {
            el.style.display = 'none';
            el.classList.add('scale-95', 'opacity-0');
        });

        const el = document.getElementById('popup-ad-' + idx);
        if (!el) { hideOverlay(); return; }
        el.style.display = 'block';
        requestAnimationFrame(() => {
            el.classList.remove('scale-95', 'opacity-0');
            el.classList.add('scale-100', 'opacity-100');
        });

        let remaining = popups[idx].duration;
        const timerEl = document.getElementById('popup-timer-' + idx);
        const timerText = document.getElementById('popup-timer-text-' + idx);

        clearInterval(timer);
        timer = setInterval(() => {
            remaining--;
            if (timerText) timerText.textContent = remaining + 's';
            if (remaining <= 0) {
                clearInterval(timer);
                if (timerEl) timerEl.style.display = 'none';
            }
        }, 1000);
    }

    function close() {
        clearInterval(timer);
        currentIndex++;
        if (currentIndex >= popups.length) {
            hideOverlay();
        } else {
            showPopup(currentIndex);
        }
    }

    setTimeout(() => showPopup(0), 800);

    return { close: close };
})();
</script>
@endif
