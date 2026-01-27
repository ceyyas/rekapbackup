@extends('layouts.app')

@section('content')
<div class="table-style">

    <div class="header-departemen">
        <h2>Rekap Size Backup Bulanan</h2>
    </div>

    <form method="GET" action="{{ route('rekap-backup.index') }}">
        <div class="filter-menu">

            <select name="perusahaan_id"
                    class="filter"
                    onchange="this.form.submit()">
                <option value="">-- Pilih Perusahaan --</option>
                @foreach ($perusahaans as $p)
                    <option value="{{ $p->id }}"
                        {{ request('perusahaan_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->nama_perusahaan }}
                    </option>
                @endforeach
            </select>

            <select name="periode_id"
                    class="filter"
                    onchange="this.form.submit()">
                <option value="">-- Pilih Periode --</option>
                @foreach ($periodes as $p)
                    <option value="{{ $p->id }}"
                        {{ request('periode_id') == $p->id ? 'selected' : '' }}>
                        {{ str_pad($p->bulan,2,'0',STR_PAD_LEFT) }}/{{ $p->tahun }}
                    </option>
                @endforeach
            </select>

        </div>
    </form>

    @if(count($departemens))
    <table class="display">
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
    @endif

</div>
@endsection
