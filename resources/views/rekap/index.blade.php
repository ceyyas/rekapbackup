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

@push('scripts')
<script>
$(document).ready(function() {
    let table = $('#rekapTable').DataTable({
        paging: false,       
        info: false,         
        searching: false,   
        lengthChange: false 
    });

    $('#perusahaan_id, #periode_id').on('change', function() {
        loadData(table);
    });
});

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
                + "?periode_id=" + $('#periode_id').val()
                + "&perusahaan_id=" + $('#perusahaan_id').val();

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
