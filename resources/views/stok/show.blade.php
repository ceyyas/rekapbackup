@extends('layouts.app')
@section('content')
<section class="home">
    <div class="table-style">
        <div class="table-feature">
            <h2>Detail Barang</h2>
        </div>

        <table>
            <tr>
                <td>Nomor SPPB</td>
                <td>:</td>
                <td>{{ $stok->nomor_sppb }}</td>
            </tr>
            <tr>
                <td>Nama Barang</td>
                <td>:</td>
                <td>{{ $stok->nama_barang }}</td>
            </tr>
            <tr>
                <td>Jumlah Barang</td>
                <td>:</td>
                <td>{{ $stok->jumlah_barang }}</td>
            </tr>
        </table>

        <div class="button-place">
            <button class="show-back">
                <a href="{{ route('stok.index') }}">Kembali</a>
            </button>
        </div>
    </div>

@endsection
