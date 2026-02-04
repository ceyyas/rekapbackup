@extends('layouts.app')

@section('content')
<div class="table-style">

    <div class="header-departemen">
        <h2>Data Laptop</h2>

        <a href="{{ route('laptop.create') }}" class="entry-button">
            + Tambah Data
        </a>
    </div>

    <form method="GET" action="{{ route('laptop.index') }}">
        <div class="filter-menu">
            <select id="perusahaan_id_laptop" name="perusahaan_id_laptop" class="filter">
                <option value="">-- Pilih Perusahaan --</option>
                @foreach ($perusahaans as $perusahaan)
                    <option value="{{ $perusahaan->id }}"
                        {{ request('perusahaan_id') == $perusahaan->id ? 'selected' : '' }}>
                        {{ $perusahaan->nama_perusahaan }}
                    </option>
                @endforeach
            </select>

            <select id="departemen_id_laptop" name="departemen_id_laptop" class="filter">
                <option value="">-- Pilih Departemen --</option>
            </select>
        </div>
    </form>

    <table id="laptopTable" class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Perusahaan</th>
                <th>Departemen</th>
                <th>Hostname</th>
                <th>User</th>
                <th>Email</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($laptops as $index => $inventori)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $inventori->perusahaan->nama_perusahaan }}</td>
                <td>{{ $inventori->departemen->nama_departemen }}</td>     
                <td>{{ $inventori->hostname }}</td>
                <td>{{ $inventori->username }}</td>
                <td>{{ $inventori->email }}</td>
                <!-- awal tombol aksi -->
                <td class="text-center">
                    <!-- tombol show -->
                    <button class="aksi-show"><a href="{{ route('laptop.show', $inventori->id) }}"><i class='bx bx-show'></i></a></button>
                            
                    <!-- tombol edit -->
                    <button class="aksi-edit"><a href="{{ route('laptop.edit', $inventori->id) }}"><i class='bx bx-edit-alt'></i></a></button>

                    <!-- form untuk tombol delete -->
                    <form action="{{ route('laptop.destroy', $inventori->id) }}"
                        method="POST"
                        onsubmit="return confirm('Are you sure?')"
                        style="display: inline;">
                        @csrf <!-- hidden token -->
                        @method('DELETE') <!-- tambahlan untuk delete form method post -->
                                
                        <!-- tombol delete -->
                        <button type="submit" class="aksi-delete"><i class='bx bx-trash'></i></button>
                    </form>
                </td>

            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection