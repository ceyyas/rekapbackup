@extends('layouts.app')

@section('content')
<section class="home">
    <div class="entry-style">
        <h2>Tambah Data</h2>

        <form action="{{ route('komputer.store') }}" method="POST">
            @csrf

            {{-- Hostname --}}
            <div class="input-form">
                <input type="text"
                       name="hostname"
                       placeholder="Hostname"
                       value="{{ old('hostname') }}"
                       required>
            </div>

            {{-- Username --}}
            <div class="input-form">
                <input type="text"
                       name="username"
                       placeholder="Username"
                       value="{{ old('username') }}"
                       required>
            </div>

            {{-- Email --}}
            <div class="input-form">
                <input type="email"
                       name="email"
                       placeholder="Email"
                       value="{{ old('email') }}">
            </div>
            {{-- Perusahaan --}}
            <div class="perusahaan-menu">
                <select name="perusahaan_id" id="perusahaan_id" required class="perusahaan">
                    <option value="">-- Pilih Perusahaan --</option>
                    @foreach ($perusahaans as $p)
                        <option value="{{ $p->id }}">
                            {{ $p->nama_perusahaan }}
                        </option>
                    @endforeach
                </select>
            </div>
            {{-- Departemen --}}
            <div class="perusahaan-menu">
                <select name="departemen_id" id="departemen_id" required class="perusahaan">
                    <option value="">-- Pilih Departemen --</option>
                </select>
            </div>

            <div class="perusahaan-menu">
                <select name="kategori" id="kategori" class="perusahaan" required>
                    <option value="PC" {{ old('kategori') === 'PC' ? 'selected' : '' }}>PC</option>
                    <option value="Laptop" {{ old('kategori') === 'Laptop' ? 'selected' : '' }}>Laptop</option>
                </select>
            </div>

            <div class="button-action">
                <button type="submit" class="save">Save</button>
                <button type="reset" class="reset">Reset</button>
            </div>

            <div class="button-back">
                <button class="back">
                    <a href="{{ route('komputer.index') }}" class="back">Kembali</a>
                </button>
            </div>

        </form>
    </div>
</section>
@endsection




