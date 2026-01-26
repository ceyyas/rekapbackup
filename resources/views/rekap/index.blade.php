@extends('layouts.app')
@section('content')
<div class="table-style">

    <div class="header-departemen">
        <h2>Rekap Size Backup Bulanan</h2>
    </div>

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
                <option value="{{ $p->id }}">
                    {{ str_pad($p->bulan,2,'0',STR_PAD_LEFT) }}/{{ $p->tahun }}
                </option>
            @endforeach
        </select>

    </div>
        <hr>
        <h5>Rekap Global</h5>
        <div id="rekap-global"></div>
        <hr>
        <h5>Detail Departemen</h5>
        <div id="rekap-detail"></div>
    </div>   
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('assets/js/rekap-backup.js') }}"></script>
@endpush
