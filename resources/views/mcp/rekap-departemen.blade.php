@extends('layouts.app')

@section('content')
<div class="table-style">

    <div class="header-departemen">
        <h2>Murni Cahaya Pratama</h2>
    </div>

    <form method="GET" action="{{ route('mcp.rekap-departemen', $perusahaanId) }}">
        <select name="periode_id"
                class="filter"
                onchange="this.form.submit()">

            <option value="">-- Pilih Periode --</option>

            @foreach ($periodes as $periode)
                <option value="{{ $periode->id }}"
                    {{ request('periode_id') == $periode->id ? 'selected' : '' }}>
                    {{ str_pad($periode->bulan, 2, '0', STR_PAD_LEFT) }}/{{ $periode->tahun }}
                </option>
            @endforeach

        </select>
    </form>

    <table id="departemenTable" class="display">
        <thead>
            <tr>
                <th>No</th>
                <th>Departemen</th>
                <th>Size Data</th>
                <th>Size Email</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data as $index => $row)
        <tr onclick="window.location='{{ route(
            'mcp.detail-komputer',
            [$perusahaanId, $row->id]
        ) }}?periode_id={{ $periodeId }}'">

            <td>{{ $index + 1 }}</td>
            <td>{{ $row->nama_departemen }}</td>
            <td>{{ number_format($row->total_data / 1024, 2) }} GB</td>
            <td>{{ number_format($row->total_email / 1024, 2) }} GB</td>
            <td>
                @if($row->total_data == 0 && $row->total_email == 0)
                    <span class="badge bg-secondary">Pending</span>
                @else
                    <span class="badge bg-success">Completed</span>
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>

@endsection
