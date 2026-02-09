@extends('layouts.app')

@section('content')
<div class="table-style">
    <section class="laporan">
        <h2>Laporan Bulanan</h2>

        <div class="filter-menu">
            <label for="periode_id">Pilih Periode:</label>
            <input type="month" id="periode_id" name="periode_id" class="date-picker" value="{{ request('periode_id') }}">
        </div>
      
        <table id="laporanTable" class="table mt-3">
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
    </section>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let table = $('#laporanTable').DataTable({
        paging: false,
        searching: false,
        info: false
    });

    $('#perusahaan_id').on('change', function() {
        let perusahaanId = $(this).val();
        if (!perusahaanId) return;

        $.get("{{ route('rekap-backup.laporan-perusahaan-data') }}", { perusahaan_id: perusahaanId }, function(data) {
            console.log(data); 
            table.clear();    
        

            $.each(data, function(i, d) {
                table.row.add([
                    d.nama_perusahaan,
                    (d.rekap_backup?.size_data ?? 0) + ' MB',
                    (d.rekap_backup?.size_email ?? 0) + ' MB',
                    (d.rekap_backup?.total_size ?? 0) + ' MB'
                ]);
            });

            table.draw();
        });
    });
});
</script>
@endpush
