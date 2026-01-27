@extends('layouts.app')

@section('content')
<section class="home">
    <div class="table-style">
        <div class="table-feature">
            <h2>Detail Departemen</h2>
        </div>

        <table>
            <tr>
                <td>ID</td>
                <td>:</td>
                <td>{{ $departemen->id }}</td>
            </tr>
            <tr>
                <td>Nama Departemen</td>
                <td>:</td>
                <td>{{ $departemen->nama_departemen }}</td>
            </tr>
            <tr>
                <td>Divisi</td>
                <td>:</td>
                <td>{{ $departemen->perusahaan->nama_perusahaan }}</td>
            </tr>
            <tr>
                <td>Dibuat</td>
                <td>:</td>
                <td>{{ $departemen->created_at }}</td>
            </tr>
        </table>

        <div class="button-place">
            <button class="show-back">
                <a href="{{ route('departemen.index') }}">Kembali</a>
            </button>
        </div>
    </div>
</section>
@endsection
