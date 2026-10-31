@extends('layouts.public')

@section('title', 'Kegiatan')

@section('content')
<div class="max-w-7xl mx-auto px-4 lg:px-6 py-6">

    {{-- Header Tabs --}}
    <div class="flex items-center gap-2 mb-5">
        <a href="{{ route('kegiatan.index', ['filter' => 'upcoming']) }}"
            class="flex-1 text-center py-2.5 rounded-xl text-sm font-semibold transition-colors {{ $filter === 'upcoming' ? 'bg-emerald-700 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-emerald-400' }}">
            Akan Datang
        </a>
        <a href="{{ route('kegiatan.index', ['filter' => 'past']) }}"
            class="flex-1 text-center py-2.5 rounded-xl text-sm font-semibold transition-colors {{ $filter === 'past' ? 'bg-emerald-700 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-emerald-400' }}">
            Telah Lewat
        </a>
    </div>

    {{-- Category Filter --}}
    <div class="overflow-x-auto -mx-4 px-4 mb-5 scrollbar-hide lg:mx-0 lg:px-0">
        <div class="flex gap-2 pb-1 lg:flex-wrap" style="width: max-content">
            <a href="{{ route('kegiatan.index', ['filter' => $filter]) }}"
                class="px-4 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap {{ !request('category') ? 'bg-emerald-700 text-white' : 'bg-white text-gray-600 border border-gray-200' }}">
                Semua
            </a>
            @foreach($categories as $cat)
            <a href="{{ route('kegiatan.index', ['filter' => $filter, 'category' => $cat->slug]) }}"
                class="px-4 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap {{ request('category') === $cat->slug ? 'bg-emerald-700 text-white' : 'bg-white text-gray-600 border border-gray-200' }}">
                {{ $cat->name }}
            </a>
            @endforeach
        </div>
    </div>

    {{-- Calendar (upcoming only) --}}
    @if($filter === 'upcoming')
    @php
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);
    $firstDayOfWeek = date('N', mktime(0, 0, 0, $monthNum, 1, $year));

    $monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $prevMonth = date('Y-m', mktime(0, 0, 0, $monthNum - 1, 1, $year));
    $nextMonth = date('Y-m', mktime(0, 0, 0, $monthNum + 1, 1, $year));

    $hijriDays = [];
    $hijriMonthsInView = [];
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $h = \App\Helpers\HijriDate::fromGregorian($year, $monthNum, $d);
        $hijriDays[$d] = $h;
        $hijriMonthsInView[$h['month_name']] = true;
    }
    $hijriLast = $hijriDays[$daysInMonth];
    $hijriSubtitle = collect(array_keys($hijriMonthsInView))->join(' / ') . ' ' . $hijriLast['year'] . ' H';
    @endphp

    <div class="card mb-5 overflow-hidden lg:max-w-md lg:mx-auto" x-data="kegiatanCalendar(@js($calendarData), @js($monthNames[$monthNum] . ' ' . $year))">
        {{-- Calendar Header --}}
        <div class="flex items-center justify-between px-4 py-3 bg-gradient-to-r from-emerald-700 to-emerald-600">
            <a href="{{ route('kegiatan.index', ['filter' => 'upcoming', 'month' => $prevMonth]) }}" class="p-2 rounded-lg hover:bg-white/10 transition-colors">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div class="text-center">
                <h3 class="font-bold text-base text-white">{{ $monthNames[$monthNum] }} {{ $year }}</h3>
                <p class="text-[11px] text-emerald-200 font-medium mt-0.5">{{ $hijriSubtitle }}</p>
            </div>
            <a href="{{ route('kegiatan.index', ['filter' => 'upcoming', 'month' => $nextMonth]) }}" class="p-2 rounded-lg hover:bg-white/10 transition-colors">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="px-2 sm:px-4 py-3 lg:py-3">
            {{-- Day names --}}
            <div class="grid grid-cols-7 text-center mb-2 lg:mb-1">
                @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $idx => $dayName)
                <div class="text-[11px] lg:text-[10px] font-bold uppercase tracking-wide {{ $idx >= 5 ? 'text-red-400' : 'text-gray-400' }} py-1 lg:py-0.5">{{ $dayName }}</div>
                @endforeach
            </div>

            {{-- Calendar Grid --}}
            <div class="grid grid-cols-7 gap-y-1 lg:gap-y-0.5">
                @for($i = 1; $i < $firstDayOfWeek; $i++)
                <div class="aspect-square lg:aspect-auto lg:h-10"></div>
                @endfor
                @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $dateStr = sprintf('%d-%02d-%02d', $year, $monthNum, $day);
                    $dayOfWeek = date('N', mktime(0, 0, 0, $monthNum, $day, $year));
                    $isToday = $dateStr === now()->format('Y-m-d');
                    $dayActivities = $calendarActivities->get($dateStr, collect());
                    $hasActivity = $dayActivities->isNotEmpty();
                    $isSunday = $dayOfWeek == 7;
                    $isSaturday = $dayOfWeek == 6;
                    $hDay = $hijriDays[$day];
                @endphp
                <button type="button"
                    @click="openDay('{{ $dateStr }}', {{ $day }}, '{{ $hDay['day'] }}', '{{ $hDay['month_name'] }}')"
                    class="aspect-square lg:aspect-auto lg:h-10 flex items-center justify-center p-0.5 lg:p-0 rounded-xl transition-all cursor-pointer
                        {{ !$isToday && !$hasActivity ? 'hover:bg-gray-50' : '' }}
                        {{ $isToday ? 'hover:opacity-90' : '' }}
                        {{ !$isToday && $hasActivity ? 'hover:opacity-90' : '' }}
                    ">
                    @if($isToday || $hasActivity)
                    <div class="w-full h-full flex flex-col items-center justify-center rounded-lg px-0.5 py-1 lg:py-0.5 shadow-sm
                        {{ $isToday ? 'bg-blue-500 text-white' : 'bg-emerald-500 text-white' }}
                    ">
                        <span class="text-xl lg:text-base font-extrabold leading-none">{{ $day }}</span>
                        <span class="text-[7px] lg:text-[9px] leading-none mt-0.5 lg:mt-px font-normal text-white/75">{{ $hDay['day'] }}</span>
                    </div>
                    @else
                    <div class="w-full h-full flex flex-col items-center justify-center">
                        <span class="text-xl lg:text-base font-extrabold leading-none
                            {{ $isSunday ? 'text-red-500' : '' }}
                            {{ $isSaturday ? 'text-blue-600' : '' }}
                            {{ !$isSunday && !$isSaturday ? 'text-gray-800' : '' }}
                        ">{{ $day }}</span>
                        <span class="text-[7px] lg:text-[9px] leading-none mt-0.5 lg:mt-px font-normal
                            {{ $hDay['day'] === 1 ? 'text-emerald-600 font-semibold' : 'text-gray-400' }}
                        ">{{ $hDay['day'] }}</span>
                    </div>
                    @endif
                </button>
                @endfor
            </div>
        </div>

        {{-- Day Detail Popup --}}
        <div x-show="popupOpen" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center" @keydown.escape.window="popupOpen = false">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="popupOpen = false"
                x-show="popupOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"></div>

            <div class="relative bg-white rounded-t-3xl sm:rounded-2xl w-full sm:max-w-md shadow-2xl safe-bottom max-h-[80vh] flex flex-col"
                x-show="popupOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-y-full sm:translate-y-4 sm:opacity-0"
                x-transition:enter-end="translate-y-0 sm:opacity-100">

                {{-- Popup Header --}}
                <div class="px-5 pt-5 pb-3 border-b border-gray-100 shrink-0">
                    <div class="w-10 h-1 bg-gray-200 rounded-full mx-auto mb-4 sm:hidden"></div>
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-extrabold text-gray-900" x-text="selectedDay"></span>
                                <span class="text-sm font-semibold text-gray-500" x-text="selectedMonthYear"></span>
                            </div>
                            <p class="text-xs text-emerald-600 font-medium mt-1">
                                <span x-text="selectedHijriDay"></span> <span x-text="selectedHijriMonth"></span> H
                            </p>
                        </div>
                        <button @click="popupOpen = false" class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Popup Body --}}
                <div class="overflow-y-auto flex-1 px-5 py-4">
                    <template x-if="selectedActivities.length === 0">
                        <div class="text-center py-8">
                            <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                @include('components.icons.calendar', ['class' => 'w-7 h-7 text-gray-400'])
                            </div>
                            <p class="text-gray-500 font-medium text-sm">Tidak ada kegiatan</p>
                            <p class="text-gray-400 text-xs mt-1">Belum ada jadwal kegiatan di tanggal ini</p>
                        </div>
                    </template>

                    <template x-if="selectedActivities.length > 0">
                        <div class="space-y-3">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide" x-text="selectedActivities.length + ' Kegiatan'"></p>
                            <template x-for="act in selectedActivities" :key="act.slug">
                                <a :href="act.url" class="flex gap-3 p-3 bg-gray-50 rounded-2xl border border-gray-100 hover:bg-emerald-50 hover:border-emerald-200 transition-colors">
                                    <div class="w-16 h-16 rounded-xl bg-emerald-100 flex-shrink-0 overflow-hidden">
                                        <template x-if="act.banner">
                                            <img :src="act.banner" :alt="act.title" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!act.banner">
                                            <div class="w-full h-full flex items-center justify-center">
                                                @include('components.icons.calendar', ['class' => 'w-6 h-6 text-emerald-400'])
                                            </div>
                                        </template>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <template x-if="act.category">
                                            <span class="inline-block text-[10px] font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full mb-1" x-text="act.category"></span>
                                        </template>
                                        <p class="text-sm font-bold text-gray-900 line-clamp-2" x-text="act.title"></p>
                                        <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 mt-1 text-xs text-gray-500">
                                            <span x-text="'🕐 ' + act.time"></span>
                                            <template x-if="act.location">
                                                <span x-text="'📍 ' + act.location"></span>
                                            </template>
                                        </div>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-300 shrink-0 self-center" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Activity List --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 lg:gap-4">
    @forelse($activities as $activity)
    <a href="{{ route('kegiatan.show', $activity->slug) }}" class="flex gap-4 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
        <div class="w-24 lg:w-28 flex-shrink-0 bg-emerald-50 relative">
            @if($activity->banner)
            <img src="{{ $activity->banner_url }}" class="w-full h-full object-cover min-h-24" alt="{{ $activity->title }}">
            @else
            <div class="w-full h-full flex items-center justify-center min-h-24">
                @include('components.icons.calendar', ['class' => 'w-8 h-8 text-emerald-300'])
            </div>
            @endif
        </div>
        <div class="flex-1 py-3 pr-3">
            @if($activity->category)
            <x-badge type="info" class="mb-1.5 text-[10px]">{{ $activity->category->name }}</x-badge>
            @endif
            <h3 class="text-sm font-bold text-gray-900 line-clamp-2">{{ $activity->title }}</h3>
            @include('public.kegiatan.session-badge')
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1.5 text-xs text-gray-500">
                <span>📅 {{ $activity->start_date->locale('id')->isoFormat('D MMM Y') }}</span>
                @if($activity->location)
                <span>📍 {{ Str::limit($activity->location, 25) }}</span>
                @endif
            </div>
        </div>
    </a>
    @empty
    <div class="col-span-full text-center py-16">
        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
            @include('components.icons.calendar', ['class' => 'w-8 h-8 text-gray-400'])
        </div>
        <p class="text-gray-500 font-medium">Belum ada kegiatan</p>
        <p class="text-gray-400 text-sm mt-1">Cek kembali nanti</p>
    </div>
    @endforelse
    </div>

    <div class="mt-6">
        {{ $activities->appends(request()->query())->links() }}
    </div>
</div>
@endsection

@push('scripts')
@if($filter === 'upcoming')
<script>
function kegiatanCalendar(activitiesByDate, monthYear) {
    return {
        activitiesByDate: activitiesByDate,
        monthYear: monthYear,
        popupOpen: false,
        selectedDay: '',
        selectedMonthYear: monthYear,
        selectedHijriDay: '',
        selectedHijriMonth: '',
        selectedActivities: [],

        openDay(dateStr, day, hijriDay, hijriMonth) {
            this.selectedDay = day;
            this.selectedActivities = this.activitiesByDate[dateStr] || [];
            this.selectedHijriDay = hijriDay;
            this.selectedHijriMonth = hijriMonth;
            this.popupOpen = true;
            document.body.style.overflow = 'hidden';
        },

        init() {
            this.$watch('popupOpen', val => {
                if (!val) document.body.style.overflow = '';
            });
        }
    };
}
</script>
@endif
@endpush
