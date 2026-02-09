@extends('layouts.app')

@section('content')
<div class="table-style">
    <section class="laporan">
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

    </section>
</div>
@endsection

@push('scripts')
<script>
$('#perusahaan_id').on('change', function() {
    let perusahaanId = $(this).val();
    if (!perusahaanId) return;

    $.get("{{ route('rekap-backup.laporan-perusahaan-pivot') }}", { perusahaan_id: perusahaanId }, function(res) {
        let periodes = res.periodes;
        let pivot = res.pivot;

        // rebuild header
        let thead = '<tr><th>Departemen</th>';
        periodes.forEach(p => { thead += '<th>'+p+'</th>'; });
        thead += '</tr>';
        $('#laporanPivot thead').html(thead);

        // rebuild body
        let tbody = '';
        Object.keys(pivot).forEach(dept => {
            tbody += '<tr><td>'+dept+'</td>';
            periodes.forEach(p => {
                tbody += '<td>'+(pivot[dept][p] ?? '-')+'</td>';
            });
            tbody += '</tr>';
        });
        $('#laporanPivot tbody').html(tbody);
    });
});

</script>
@endpush
