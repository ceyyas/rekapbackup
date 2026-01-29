@extends('layouts.app')

@section('content')
<div class="table-style">

    <div class="header-departemen">
        <h2>Rekap Size Backup Bulanan</h2>
    </div>

    <form method="GET" action="{{ route('rekap-backup.index') }}">
        <div class="filter-menu">

            <select id="perusahaan_id" class="filter">
                <option value="">-- Pilih Perusahaan --</option>
                @foreach ($perusahaans as $p)
                    <option value="{{ $p->id }}">{{ $p->nama_perusahaan }}</option>
                @endforeach
            </select>

            <select id="periode_id" class="filter">
                <option value="">-- Pilih Periode --</option>
                @foreach ($periodes as $p)
                    <option value="{{ $p->id }}">{{ $p->nama_bulan }} {{ $p->tahun }}</option>
                @endforeach
            </select>

            @if(count($departemens))
                    <a href="{{ route('rekap-backup.export', request()->query()) }}"
                    class="entry-button">
                        Export Excel
                    </a>
            @endif

        </div>
    </form>

    <table id="rekapTable" class="display">
        <thead>
            <tr>
                <th>Departemen</th>
                <th>Size Data</th>
                <th>Size Email</th>
                <th>Total Size</th>
                <th>Status Backup</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Inisialisasi DataTables tanpa search & length menu
    let table = $('#rekapTable').DataTable({
        paging: false,       
        info: false,         
        searching: false,   
        lengthChange: false 
    });

    // Event listener untuk filter dropdown
    $('#perusahaan_id, #periode_id').on('change', function() {
        loadData(table);
    });
});

// Fungsi Ajax untuk ambil data sesuai filter
function loadData(table) {
    let perusahaanId = $('#perusahaan_id').val();
    let periodeId = $('#periode_id').val();

    if (!perusahaanId || !periodeId) return;

    $.get("{{ route('rekap.filter') }}", 
        { perusahaan_id: perusahaanId, periode_id: periodeId }, 
        function(data) {
            table.clear();

            $.each(data, function(i, d) {
            let detailUrl = "{{ route('rekap-backup.detail-page', ':id') }}"
                            .replace(':id', d.id) 
                            + "?periode_id=" + $('#periode_id').val();

                table.row.add([
                    d.nama_departemen,
                    d.size_data + ' MB',
                    d.size_email + ' MB',
                    d.total_size + ' MB',
                    d.status_backup
                ]).node().setAttribute('onclick', "window.location='" + detailUrl + "'");
            });


            table.draw();
        }
    );
}
</script>
@endpush
