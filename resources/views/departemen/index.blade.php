@extends('layouts.app')

@section('content')
<div class="table-style">

    <div class="header-departemen">
        <h2>Data Departemen</h2>

        <a href="{{ route('departemen.create') }}" class="entry-button">
            + Tambah Data
        </a>
    </div>

    <form method="GET" action="{{ route('departemen.index') }}">
        <div class="filter-menu">

            <select id="perusahaan_id" class="filter">
                <option value="">-- Pilih Perusahaan --</option>
                @foreach ($perusahaans as $perusahaan)
                    <option value="{{ $perusahaan->id }}">{{ $perusahaan->nama_perusahaan }}</option>
                @endforeach
            </select>
        </div>
    </form>


    <table id="departemenTable" class="display">
        <thead>
            <tr>
                <th>No</th>
                <th>Divisi</th>
                <th>Departemen</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

</div>
@endsection
