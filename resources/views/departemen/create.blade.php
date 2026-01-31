@extends('layouts.app')

@section('content')
<section class="home">
    <div class="entry-style">
        <h2>Tambah Departemen</h2>

        <form action="{{ route('departemen.store') }}" method="POST">
            @csrf

            <!-- Nama Departemen -->
            <div class="input-form">
                <input 
                    type="text" 
                    name="nama_departemen" 
                    placeholder="Nama Departemen"
                    value="{{ old('nama_departemen') }}"
                    required
                >
            </div>

            <!-- Perusahaan -->
            <div class="perusahaan-menu">
                <select name="perusahaan_id" required class="perusahaan">
                    <option value="">-- Pilih Perusahaan --</option>
                    @foreach($perusahaan as $p)
                        <option value="{{ $p->id }}">
                            {{ $p->nama_perusahaan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Button -->
            <div class="button-action">
                <button type="submit" class="save">Save</button>
                <button type="reset" class="reset">Reset</button>
            </div>

            <div class="button-back">
                <button class="back">
                    <a href="{{ route('departemen.index') }}" class="back">Kembali</a>
                </button>            
            </div>

        </form>
    </div>
</section>
@endsection
