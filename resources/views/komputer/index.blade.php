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
                <button type="submit">Terapkan</button>
                <button type="submit">
                <a href="{{ route('komputer.index') }}" 
                    id="resetFilter" 
                    class="btn btn-secondary">
                    Reset Filter
                </a>
                </button>
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
            @foreach ($komputers as $index => $inventori)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $inventori->perusahaan->nama_perusahaan }}</td>
                <td>{{ $inventori->departemen->nama_departemen }}</td>
                <td>{{ $inventori->hostname }}</td>
                <td>{{ $inventori->username }}</td>
                <td>{{ $inventori->email }}</td>
                <td>{{ $inventori->kategori }}</td>
                <td>
                    <span class="status 
                        @if($inventori->status === 'active') status-active 
                        @elseif($inventori->status === 'inactive') status-inactive
                        @endif">
                        {{ ucfirst($inventori->status) }}
                    </span>
                </td>
                
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

