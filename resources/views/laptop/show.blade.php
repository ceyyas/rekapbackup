@extends('layouts.app')

@section('content')
<section class="home">
    <div class="table-style">
        <div class="table-feature">
            <h2>Detail Data Inventori Laptop</h2>
        </div>

        <table>
            <tr>
                <td>Hostname</td>
                <td>:</td>
                <td>{{ $laptop->hostname }}</td>
            </tr>
            <tr>
                <td>Username</td>
                <td>:</td>
                <td>{{ $laptop->username }}</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>:</td>
                <td>{{ $laptop->email }}</td>
            </tr>
            <tr>
                <td>Kategori</td>
                <td>:</td>
                <td>{{ $laptop->kategori }}</td>
            </tr>
            <tr>
                <td>Departemen</td>
                <td>:</td>
                <td>{{ $laptop->departemen->nama_departemen ?? '-' }}</td>
            </tr>
            <tr>
                <td>Perusahaan</td>
                <td>:</td>
                <td>{{ $laptop->perusahaan->nama_perusahaan ?? '-' }}</td>
            </tr>
        </table>

        <div class="button-place">
            <button class="show-back">
                <a href="{{ route('laptop.index') }}">Kembali</a>
            </button>
        </div>
    </div>
</section>
@endsection
