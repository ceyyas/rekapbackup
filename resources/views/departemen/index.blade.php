@extends('layouts.app')

@section('content')
<div class="table-style">

    <div class="header-departemen">
        <h2>Data Departemen</h2>

        <a href="{{ route('departemen.create') }}" class="entry-button">
            + Tambah Data
        </a>
    </div>



    <table id="departemenTable" class="display">
        <thead>
            <tr>
                <th>No</th>
                <th>Divisi</th>
                <th>Departemen</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($departemens as $index => $departemen)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $departemen->perusahaan->nama_perusahaan }}</td>
                <td>{{ $departemen->nama_departemen }}</td>
                <!-- awal tombol aksi -->
                <td class="text-center">
                    <!-- tombol show -->
                    <button class="aksi-show"><a href="{{ route('departemen.show', $departemen->id) }}"><i class='bx bx-show'></i></a></button>
                            
                    <!-- tombol edit -->
                    <button class="aksi-edit"><a href="{{ route('departemen.edit', $departemen->id) }}"><i class='bx bx-edit-alt'></i></a></button>

                    <!-- form untuk tombol delete -->
                    <form action="{{ route('departemen.destroy', $departemen->id) }}"
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
