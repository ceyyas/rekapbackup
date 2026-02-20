@extends('layouts.app')
@section('content')
<section class="home">
    <div class="entry-style">
        <h2>Edit Data Barang</h2>

        <form action="{{ route('stok.update', $stok->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="input-form">
                <input type="text"
                       name="nomor_sppb"
                       class="form-control @error('nomor_sppb') is-invalid @enderror"
                       placeholder="nomor_sppb"
                       value="{{ old('nomor_sppb', $stok->nomor_sppb) }}">
                @error('nomor_sppb')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="input-form">
                <input type="text"
                       name="jumlah_barang"
                       class="form-control @error('jumlah_barang') is-invalid @enderror"
                       placeholder="jumlah_barang"
                       value="{{ old('jumlah_barang', $stok->jumlah_barang) }}">
                @error('jumlah_barang')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="perusahaan-menu">
                <select name="nama_barang" class="perusahaan"
                        class="form-control @error('nama_barang') is-invalid @enderror">

                    <option value="">-- Pilih Barang --</option>
                    @foreach ($mediaList as $media)
                        <option value="{{ $media }}"
                            {{ old('nama_barang', $stok->nama_barang) == $media ? 'selected' : '' }}>
                            {{ $media }}
                        </option>
                    @endforeach
                </select>

                @error('nama_barang')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="button-action">
                <button type="submit" class="save">Save</button>
                <button type="reset" class="reset">Reset</button>
            </div>

            <div class="button-back">
                <button class="back">
                    <a href="{{ route('stok.index') }}" class="back">Kembali</a>
                </button> 
            </div>
        </form>
    </div>
@endsection
