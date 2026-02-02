@extends('layouts.app')

@section('content')
<section class="home">
    <div class="table-style">
        <div class="table-feature">
            <h2>Detail Data Inventori Komputer</h2>
        </div>

        <table>
            <tr>
                <td>Hostname</td>
                <td>:</td>
                <td>{{ $komputer->hostname }}</td>
            </tr>
            <tr>
                <td>Username</td>
                <td>:</td>
                <td>{{ $komputer->username }}</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>:</td>
                <td>{{ $komputer->email }}</td>
            </tr>
            <tr>
                <td>Kategori</td>
                <td>:</td>
                <td>{{ $komputer->kategori }}</td>
            </tr>
            <tr>
                <td>Departemen</td>
                <td>:</td>
                <td>{{ $komputer->departemen->nama_departemen ?? '-' }}</td>
            </tr>
            <tr>
                <td>Perusahaan</td>
                <td>:</td>
                <td>{{ $komputer->perusahaan->nama_perusahaan ?? '-' }}</td>
            </tr>
        </table>

        <div class="button-place">
            <button class="show-back">
                <a href="{{ route('komputer.index', [
                    'perusahaan_id' => $komputer->perusahaan_id,
                    'departemen_id' => $komputer->departemen_id
                ]) }}" class="back">Kembali</a>
            </button>
        </div>
    </div>
</section>
@endsection
