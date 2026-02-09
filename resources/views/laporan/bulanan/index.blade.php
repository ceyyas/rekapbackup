@extends('layouts.app')

@section('content')
<div class="table-style">
    <h2>Laporan Bulanan</h2>

    <div class="filter-menu">
        <label for="periode_bulanan">Pilih Periode:</label>
            <input type="month" id="periode_bulanan" name="periode_bulanan" class="date-picker" value="{{ request('periode_bulanan') }}">

        <button id="btnExportBulanan" class="entry-button">Export Data</button>
    </div>
  
    <table id="laporanTable" class="display">
        <thead>
            <tr>
                <th>Perusahaan</th>
                <th>Size Data</th>
                <th>Size Email</th>
                <th>Total Size</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
    <canvas id="laporanChart" height="100"></canvas>
</div>
@endsection

@push('scripts')
<script>

</script>
@endpush



