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

            <a id="btnExportBurning" 
                href="{{ route('rekap-backup.export-burning') }}?perusahaan_id={{ request('perusahaan_id') }}&periode_id={{ request('periode_id') }}"     
                class="entry-button"
                style="display:none;">
                    Export Excel
            </a>

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
        <tbody></tbody>
    </table>
</div>
@endsection
