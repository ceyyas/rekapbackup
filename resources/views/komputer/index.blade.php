@extends('layouts.app')

@section('content')
<div class="table-style">

    <div class="header-departemen">
        <h2>Data Inventori</h2>

        <a href="{{ route('komputer.create') }}" class="entry-button">
            + Tambah Data
        </a>
    </div>

        <div class="filter-menu">
             <input type="text" id="customSearch" class="filter" placeholder="Cari data...">
        </div>
    

    <table id="komputerTable" class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Perusahaan</th>
                <th>Departemen</th>
                <th>Hostname</th>
                <th>Username</th>
                <th>Email</th>
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

