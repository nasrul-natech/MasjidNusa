@extends('layouts.admin.operasional')
@section('title', $period->name)
@section('page-title', 'Poin Kehadiran Jamaah')
@section('content')
<a class="btn-secondary mb-4 inline-block" href="{{ route('admin.operasional.attendance-rewards.index') }}">← Pilih Periode</a>
@if($errors->any())<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 mb-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
<div class="card overflow-hidden mb-5">
    <div class="card-header bg-gradient-to-r from-indigo-600 to-violet-600 border-0 flex flex-wrap items-start justify-between gap-3">
        <h2 class="text-xl font-bold text-white break-words min-w-0 flex-1">{{ $period->name }}</h2>
        <x-badge :type="$period->status === 'final' ? 'info' : ($period->status === 'active' ? 'success' : 'warning')" class="!ring-1 !ring-white/30 {{ $period->status === 'draft' ? '!bg-amber-100 !text-amber-900' : '' }}">{{ ucfirst($period->status) }}</x-badge>
    </div>
    <div class="card-body space-y-4 bg-gradient-to-b from-indigo-50/40 to-white">
    <dl class="grid gap-3 sm:grid-cols-3 text-sm">
        <div class="rounded-xl border border-indigo-100 bg-white p-3"><dt class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Rentang</dt><dd class="mt-1 font-semibold text-gray-900">{{ $period->start_date->format('d/m/Y') }} – {{ $period->end_date->format('d/m/Y') }}</dd></div>
        <div class="rounded-xl border border-indigo-100 bg-white p-3"><dt class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Zona waktu</dt><dd class="mt-1 font-semibold text-gray-900 break-words">{{ $period->timezone }}</dd></div>
        <div class="rounded-xl border border-indigo-100 bg-white p-3"><dt class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Penerima maks.</dt><dd class="mt-1 font-semibold text-gray-900">{{ $period->winner_count }} jamaah</dd></div>
    </dl>
    <p class="text-sm leading-relaxed text-gray-700">Seri: waktu mencapai poin, lalu ID akun. Hanya jamaah aktif tanpa peran admin.</p>
    <div class="rounded-xl border-2 border-indigo-100 bg-indigo-50/80 p-4 text-sm leading-relaxed text-indigo-950">
    @if($period->status === 'final')<p><strong>Cut-off: {{ $period->effective_cutoff_at->setTimezone($period->timezone)->format('d/m/Y H:i:s T') }}.</strong> Snapshot tetap; perubahan akun dan absensi berikutnya tidak mengubah hasil.</p>@else<p>Pratinjau dapat berubah sampai finalisasi. Rentang dan aturan terkunci setelah aktivasi.</p>@endif
    </div>
    @if($period->status === 'draft')
    @can('manage_attendance_rewards')<form method="POST" action="{{ route('admin.operasional.attendance-rewards.activate', $period) }}" class="mt-2">@csrf<button type="submit" class="btn-primary w-full sm:w-auto shadow-md">Aktifkan &amp; Kunci Aturan</button></form>@endcan
    @elseif($period->status === 'active')
    @can('finalize_attendance_rewards')
    <form method="POST" action="{{ route('admin.operasional.attendance-rewards.finalize', $period) }}" class="mt-2 space-y-4 rounded-xl border-2 border-amber-300 bg-amber-50 p-4">
        @csrf
        <p class="text-sm leading-relaxed text-amber-950"><x-badge type="warning" class="mr-1">Final</x-badge> <strong>Finalisasi tidak dapat dibatalkan.</strong> Sesi terbuka dalam rentang periode harus selesai dahulu.</p>
        <label class="flex items-start gap-2 text-sm leading-relaxed text-gray-800"><input class="mt-1 shrink-0 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" type="checkbox" name="confirm" value="1" required> <span>Saya sudah memeriksa pratinjau dan menyetujui cut-off sekarang.</span></label>
        <button type="submit" class="btn-primary w-full sm:w-auto shadow-md">Finalisasi Snapshot Penerima</button>
    </form>
    @endcan
    @endif
    </div>
</div>
<x-admin.table :mobile-cards="true" :title="$period->status === 'final' ? 'Snapshot Penerima' : 'Pratinjau Peringkat'" :badge="$period->status === 'final' ? 'Terkunci' : 'Live'" class="mb-5">
<thead><tr><th>Peringkat</th><th>Nama</th><th>Poin</th><th>Mencapai Poin</th><th>Hadiah</th></tr></thead><tbody>
@forelse($leaderboard as $row)
@php($rank = $period->status === 'final' ? $row->rank : $leaderboard->firstItem() + $loop->index)
@php($topRank = $rank <= 3)
<tr class="{{ $topRank && $period->status === 'final' ? 'bg-amber-50/50' : '' }}"><td data-label="Peringkat"><span class="inline-flex items-center justify-center min-w-[2.5rem] font-bold tabular-nums {{ $rank === 1 ? 'text-amber-600' : ($rank === 2 ? 'text-slate-500' : ($rank === 3 ? 'text-orange-700' : 'text-gray-900')) }}">#{{ $rank }}</span></td><td data-label="Nama"><div class="space-y-2"><a class="font-semibold text-indigo-700 hover:underline break-words" href="{{ route('admin.operasional.attendance-rewards.show', [$period, 'user_id' => $row->user_id]) }}">{{ $row->display_name }}</a>@if($period->status !== 'final' && $rank <= $period->winner_count)<div><x-badge type="success">Calon penerima</x-badge></div>@endif</div></td><td data-label="Poin"><span class="inline-flex items-center rounded-lg bg-emerald-100 px-2.5 py-1 text-sm font-bold text-emerald-800 tabular-nums">{{ $row->points }}</span></td><td data-label="Mencapai Poin" class="text-xs text-gray-600">{{ \Carbon\CarbonImmutable::parse($row->reached_at)->setTimezone($period->timezone)->format('d/m/Y H:i:s T') }}</td><td data-label="Hadiah">
@if($period->status === 'final')
<div class="mb-2"><x-badge :type="$row->reward_status === 'delivered' ? 'success' : ($row->reward_status === 'ready' ? 'info' : 'warning')">{{ ['pending' => 'Belum diproses', 'ready' => 'Siap diserahkan', 'delivered' => 'Sudah diserahkan'][$row->reward_status] }}</x-badge></div>
@if($row->delivered_at)<p class="text-xs text-gray-500">{{ $row->delivered_at->setTimezone($period->timezone)->format('d/m/Y H:i:s T') }}</p>@endif
@can('manage_attendance_rewards')
@if($row->reward_status !== 'delivered')<form method="POST" action="{{ route('admin.operasional.attendance-rewards.recipients.update', [$period, $row]) }}" class="space-y-2">@csrf @method('PATCH')<label for="reward-status-{{ $row->id }}" class="form-label text-xs">Status hadiah</label><select id="reward-status-{{ $row->id }}" name="reward_status" class="form-input text-sm"><option value="pending" @selected($row->reward_status === 'pending')>Belum diproses</option><option value="ready" @selected($row->reward_status === 'ready')>Siap diserahkan</option><option value="delivered">Sudah diserahkan</option></select><button type="submit" class="btn-secondary text-sm w-full">Catat Manual</button></form>@endif
@endcan
@else <x-badge type="gray">Belum final</x-badge> @endif
</td></tr>
@empty<tr><td colspan="5" class="text-center py-8"><p class="font-semibold text-gray-900">Belum ada kandidat dengan poin sah.</p><p class="mt-2 text-sm text-gray-500">Rekap hanya memuat kehadiran yang memenuhi rentang dan aturan periode.</p></td></tr>@endforelse
</tbody>
</x-admin.table>
@if($leaderboard instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)<div class="mb-5">{{ $leaderboard->links() }}</div>@endif
@if($details !== null)
<div class="card overflow-hidden"><div class="card-header bg-emerald-50 border-b border-emerald-100"><h2 class="font-semibold text-emerald-950">Rincian Kehadiran Dalam Periode</h2></div><div class="card-body"><div class="divide-y divide-emerald-100">@forelse($details as $detail)<div class="py-3 first:pt-0 flex flex-wrap items-start justify-between gap-2"><div class="min-w-0"><h3 class="text-sm font-semibold text-gray-900 break-words">{{ $detail->title }}</h3><p class="mt-1 text-xs text-emerald-800/70">{{ \Carbon\CarbonImmutable::parse($detail->checked_in_at)->setTimezone($period->timezone)->format('d/m/Y H:i:s T') }}</p></div><x-badge type="success">1 poin</x-badge></div>@empty<p class="py-5 text-center text-sm text-gray-500">Tidak ada kehadiran.</p>@endforelse</div><div class="mt-4">{{ $details->links() }}</div></div></div>
@endif
@endsection
