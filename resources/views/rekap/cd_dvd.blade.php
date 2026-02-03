@extends('layouts.app')

@section('content')
<div class="table-style">
    <div class="header-departemen">
        <h2>Input Penggunaan CD/DVD</h2>
    </div>

    <form method="GET" action="{{ route('rekap-backup.cd-dvd') }}">
        <div class="filter-menu">
            <select id="perusahaan_id" name="perusahaan_id" class="filter">
                <option value="">-- Pilih Perusahaan --</option>
                @foreach ($perusahaans as $p)
                    <option value="{{ $p->id }}" {{ request('perusahaan_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->nama_perusahaan }}
                    </option>
                @endforeach
            </select>

            <label for="periode_id">Pilih Periode:</label>
            <input type="month" id="periode_id" name="periode_id" class="date-picker" value="{{ request('periode_id') }}">
        </div>
    </form>

    <table id="cdDvdTable" class="display">
        <thead>
            <tr>
                <th>Departemen</th>
                <th>Size Data</th>
                <th>Size Email</th>
                <th>Total Size</th>
                <th>CD 700 MB</th>
                <th>DVD 4.7 GB</th>
                <th>DVD 8.5 GB</th>
                <th>Status Backup</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($departemens as $dept)
            <tr>
                <td>{{ $dept->nama_departemen }}</td>
                <td>{{ number_format($dept->size_data) }} MB</td>
                <td>{{ number_format($dept->size_email) }} MB</td>
                <td><strong>{{ number_format($dept->total_size) }} MB</strong></td>
                <td>
                    @if($dept->status_backup === 'completed')
                        <input type="number" name="cd700[{{ $dept->id }}]"
                               value="{{ $dept->jumlah_cd700 ?? 0 }}" min="0"
                               class="size-input cd700"
                               data-inventori-id="{{ $dept->inventori_id }}">
                    @else
                        {{ $dept->jumlah_cd700 ?? 0 }}
                    @endif
                </td>
                <td>
                    @if($dept->status_backup === 'completed')
                        <input type="number" name="dvd47[{{ $dept->id }}]"
                               value="{{ $dept->jumlah_dvd47 ?? 0 }}" min="0"
                               class="size-input size-data"
                               data-inventori-id="{{ $dept->inventori_id }}">
                    @else
                        {{ $dept->jumlah_dvd47 ?? 0 }}
                    @endif
                </td>
                <td>
                    @if($dept->status_backup === 'completed')
                        <input type="number" name="dvd85[{{ $dept->id }}]"
                               value="{{ $dept->jumlah_dvd85 ?? 0 }}" min="0"
                               class="form-control form-control-sm"
                               data-inventori-id="{{ $dept->inventori_id }}">
                    @else
                        {{ $dept->jumlah_dvd85 ?? 0 }}
                    @endif
                </td>
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
