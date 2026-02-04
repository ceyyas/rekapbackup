@extends('layouts.app')

@section('content')
<section class="home">
    <div class="entry-style">
        <h2>Edit Data Inventori Laptop</h2>

        <form action="{{ route('laptop.update', $laptop->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Hostname --}}
            <div class="input-form">
                <input type="text"
                       name="hostname"
                       class="form-control @error('hostname') is-invalid @enderror"
                       placeholder="Hostname"
                       value="{{ old('hostname', $laptop->hostname) }}">
                @error('hostname')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Username --}}
            <div class="input-form">
                <input type="text"
                       name="username"
                       class="form-control @error('username') is-invalid @enderror"
                       placeholder="Username"
                       value="{{ old('username', $laptop->username) }}">
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Email --}}
            <div class="input-form">
                <input type="email"
                       name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="Email"
                       value="{{ old('email', $laptop->email) }}">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Perusahaan --}}
            <div class="perusahaan-menu">
                <select name="perusahaan_id" id="perusahaan_id" class="perusahaan"
                        class="form-control @error('perusahaan_id') is-invalid @enderror">
                    <option value="">-- Pilih Perusahaan --</option>
                    @foreach($perusahaans as $perusahaan)
                        <option value="{{ $perusahaan->id }}"
                            {{ old('perusahaan_id', $laptop->perusahaan_id) == $perusahaan->id ? 'selected' : '' }}>
                            {{ $perusahaan->nama_perusahaan }}
                        </option>
                    @endforeach
                </select>

                @error('perusahaan_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Departemen --}}
            <div class="perusahaan-menu">
                <select name="departemen_id" id="departemen_id" class="perusahaan">
                    @foreach ($departemens as $departemen)
                        <option value="{{ $departemen->id }}"
                            {{ $laptop->departemen_id == $departemen->id ? 'selected' : '' }}>
                            {{ $departemen->nama_departemen }}
                        </option>
                    @endforeach
                </select>

                @error('departemen_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tombol --}}
            <div class="button-action">
                <button type="submit" class="save">Save</button>
                <button type="reset" class="reset">Reset</button>
            </div>

            <div class="button-back">
                <button class="back">
                    <a href="{{ route('laptop.index', [
                        'perusahaan_id' => $laptop->perusahaan_id,
                        'departemen_id' => $laptop->departemen_id
                    ]) }}" class="back">Kembali</a>
                </button> 
            </div>
        </form>
    </div>
</section>
@endsection
