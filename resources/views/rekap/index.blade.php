@extends('layouts.app')

@section('content')
<div class="table-style">

    <div class="header-departemen">
        <h2>Rekap Size Backup Bulanan</h2>
    </div>

    <form method="GET" action="{{ route('rekap-backup.index') }}">
        <div class="filter-menu">
            <select id="perusahaan_id" class="filter" name="perusahaan_id">
                <option value="">-- Pilih Perusahaan --</option>
                @foreach ($perusahaans as $p)
                    <option value="{{ $p->id }}" {{ request('perusahaan_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->nama_perusahaan }}
                    </option>
                @endforeach
            </select>

            <label for="periode_id">Pilih Periode:</label>
            <input type="month" id="periode_id" name="periode_id" class="date-picker" value="{{ request('periode_id') }}">

             @if($departemens->count())
                <a href="{{ route('rekap-backup.export', request()->query()) }}" class="entry-button">
                    Export Excel
                </a>
            @endif

            
        </div>
    </form>

    <table id="rekapTable" class="display"
        data-filter-url="{{ route('rekap.filter') }}"
        data-detail-url="{{ route('rekap-backup.detail-page', ':id') }}">
        
       <thead>
            <tr>
                <th>Departemen</th>
                <th>Size Data</th>
                <th>Size Email</th>
                <th>Total Size</th>
                <th>Status Backup</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($departemens as $dept)
            <tr onclick="window.location='{{ route('rekap-backup.detail-page', [
                    'departemen' => $dept->id,
                    'periode_id' => request('periode_id')
                ]) }}';" style="cursor:pointer;">
                <td>{{ $dept->nama_departemen }}</td>
                <td>{{ number_format($dept->size_data) }} MB</td>
                <td>{{ number_format($dept->size_email) }} MB</td>
                <td><strong>{{ number_format($dept->total_size) }} MB</strong></td>
                <td>
                    <span class="status {{ $dept->status_backup }}">
                        {{ ucfirst($dept->status_backup) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
