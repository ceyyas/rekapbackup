@extends('layouts.app')


@section('content')
<div class="table-style">
    <div class="header-departemen">
        <h2>Stok CD/DVD</h2>

        <a href="{{ route('stok.create') }}" class="entry-button">
            + Tambah Data
        </a>
    </div>

     <table id="stokTable" class="display">
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor SPPB</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stoks as $index => $stok)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $stok->nomor_sppb }}</td>
                <td>{{ $stok->nama_barang }}</td>
                <td>{{ $stok->jumlah_barang }}</td>  
                <td class="text-center">
                    <!-- tombol show -->
                    <button class="aksi-show"><a href="{{ route('stok.show', $stok->id) }}"><i class='bx bx-show'></i></a></button>
                           
                    <!-- tombol edit -->
                    <button class="aksi-edit"><a href="{{ route('stok.edit', $stok->id) }}"><i class='bx bx-edit-alt'></i></a></button>

                    <!-- form untuk tombol delete -->
                    <form action="{{ route('stok.destroy', $stok->id) }}"
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
