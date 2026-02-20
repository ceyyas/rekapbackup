@extends('layouts.app')


@section('content')
<div class="table-style">
    <div class="header-departemen">
        <h2>Stok CD/DVD</h2>

        <a href="{{ route('stok.create') }}" class="entry-button">
            + Tambah Data
        </a>
    </div>

     <table id="stokTable" class="display">
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor SPPB</th>
                <th>Nama Barang</th>
                <th>Stok Awal</th>
                <th>Pemakaian</th>
                <th>Stok Tersisa</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
@endsection
