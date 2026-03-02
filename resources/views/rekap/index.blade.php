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

            <input type="text" id="periode_id" name="periode_id" class="date-picker" value="{{ request('periode_id') }}" placeholder="Pilih Periode">
            <a id="btnExport" 
                href="{{ route('rekap-backup.export') }}?perusahaan_id={{ request('perusahaan_id') }}&periode_id={{ request('periode_id') }}"     
                class="entry-button"
                style="display:none;">
                    Export Excel
            </a>

        </div>
    </form>

    <table id="rekapTable" class="display">
       <thead>
            <tr>
                <th>Departemen</th>
                <th>Size Data</th>
                <th>Size Email</th>
                <th>Total Size</th>
                <th>CD 700 MB</th>
                <th>DVD 4.7 GB</th>
                <th>DVD 8.5 GB</th>
                <th>Total CD/DVD</th>
                <th>Status Backup</th>
                <th>Status Data</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
@endsection
