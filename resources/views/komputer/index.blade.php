@extends('layouts.app')

@section('content')
<div class="table-style">

    <div class="header-departemen">
        <h2>Data Komputer</h2>

        <a href="{{ route('komputer.create') }}" class="entry-button">
            + Tambah Data
        </a>
    </div>

    <form method="GET" action="{{ route('komputer.index') }}">
        <div class="filter-menu">

            <select name="perusahaan_id"
                    class="filter"
                    onchange="this.form.submit()">
                <option value="">-- Pilih Perusahaan --</option>
                @foreach ($perusahaans as $perusahaan)
                    <option value="{{ $perusahaan->id }}"
                        {{ request('perusahaan_id') == $perusahaan->id ? 'selected' : '' }}>
                        {{ $perusahaan->nama_perusahaan }}
                    </option>
                @endforeach
            </select>

            <select name="departemen_id"
                    class="filter"
                    onchange="this.form.submit()">
                <option value="">-- Pilih Departemen --</option>
                @foreach ($perusahaans as $perusahaan)
                    @foreach ($perusahaan->departemen as $departemen)
                        <option value="{{ $departemen->id }}"
                            {{ request('departemen_id') == $departemen->id ? 'selected' : '' }}>
                            {{ $departemen->nama_departemen }}
                        </option>
                    @endforeach
                @endforeach
            </select>

        </div>
    </form>

    <table id="departemenTable" class="display">
        <thead>
            <tr>
                <th>No</th>
                <th>Hostname</th>
                <th>User</th>
                <th>Email</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($komputers as $index => $inventori)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $inventori->hostname }}</td>
                <td>{{ $inventori->username }}</td>
                <td>{{ $inventori->email }}</td>
                <!-- awal tombol aksi -->
                <td class="text-center">
                    <!-- tombol show -->
                    <button class="aksi-show"><a href="{{ route('komputer.show', $inventori->id) }}"><i class='bx bx-show'></i></a></button>
                            
                    <!-- tombol edit -->
                    <button class="aksi-edit"><a href="{{ route('komputer.edit', $inventori->id) }}"><i class='bx bx-edit-alt'></i></a></button>

                    <!-- form untuk tombol delete -->
                    <form action="{{ route('komputer.destroy', $inventori->id) }}"
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