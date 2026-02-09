@extends('layouts.app')

@section('content')
<div class="table-style">
        <h2>Laporan Perusahaan</h2>
        <div class="filter-menu">
            <select id="perusahaan_id" class="filter" name="perusahaan_id">
                <option value="">-- Pilih Perusahaan --</option>
                @foreach ($perusahaans as $p)
                    <option value="{{ $p->id }}" {{ request('perusahaan_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->nama_perusahaan }}
                    </option>
                @endforeach
            </select>

            <button id="btnExportPerusahaan" class="entry-button">Export Data</button>
        
        </div>
      
        <table id="laporanPivot" class="table mt-3">
            <thead>
                <tr>
                    <th>Departemen</th>
                    {{-- kolom periode akan diinject via JS --}}
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <canvas id="laporanChart" height="100"></canvas>
</div>
@endsection

