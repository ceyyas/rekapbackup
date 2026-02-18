@extends('layouts.app')

@section('content')
<div class="table-style">

    <div class="header-departemen">
        <h2>Data Inventori</h2>

        <a href="{{ route('komputer.create') }}" class="entry-button">
            + Tambah Data
        </a>
    </div>

        <form method="GET" action="{{ route('komputer.index') }}">
            <div class="filter-menu">

                <select id="perusahaan_id" name="perusahaan_id" class="filter">
                    <option value="">-- Pilih Perusahaan --</option>
                    @foreach ($perusahaans as $perusahaan)
                        <option value="{{ $perusahaan->id }}"
                            {{ request('perusahaan_id') == $perusahaan->id ? 'selected' : '' }}>
                            {{ $perusahaan->nama_perusahaan }}
                        </option>
                    @endforeach
                </select>

                <select id="departemen_id" name="departemen_id" class="filter">
                    <option value="">-- Pilih Departemen --</option>
                </select>

                <select id="kategori_id" name="kategori_id" class="filter">
                    <option value="">-- Pilih Kategori --</option>
                    <option value="PC">PC</option>
                    <option value="Laptop">Laptop</option>
                </select>
            </div>
        </form>
    
    <table id="komputerTable" class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Perusahaan</th>
                <th>Departemen</th>
                <th>Hostname</th>
                <th>Username</th>
                <th>Email</th>
                <th>Kategori</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
@endsection

