@extends('layouts.app')

@section('content')
<section class="home">
    <div class="entry-style">
        <h2>Tambah Stok CD/DVD</h2>

        <form action="{{ route('stok.store') }}" method="POST">
            @csrf

            <div class="input-form">
                <input type="text"
                    name="nomor_sppb"
                    placeholder="Nomor SPPB"
                    value="{{ old('nomor_sppb') }}"
                    required>
            </div>
            
            <div class="input-form">
                <input type="number"
                    name="jumlah_barang"
                    placeholder="Jumlah"
                    min="1"
                    value="{{ old('jumlah_barang') }}"
                    required>
            </div>

            <div class="perusahaan-menu">
                <select name="nama_barang" required class="perusahaan">
                    <option value="">-- Pilih Media --</option>
                    <option value="CD 700 MB">CD 700 MB</option>
                    <option value="DVD 4.7 GB">DVD 4.7 GB</option>
                    <option value="DVD 8.5 GB">DVD 8.5 GB</option>
                </select>
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
</section>
@endsection


