@extends('layouts.app')

@section('content')
<section class="home">
    <div class="entry-style">
        <h2>Edit Departemen</h2>

        <form action="{{ route('departemen.update', $departemen->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Nama Departemen --}}
            <div class="input-form">
                <input type="text"
                       name="nama_departemen"
                       class="form-control @error('nama_departemen') is-invalid @enderror"
                       placeholder="Nama Departemen"
                       value="{{ old('nama_departemen', $departemen->nama_departemen) }}">
                @error('nama_departemen')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Pilih Perusahaan --}}
            <div class="perusahaan-menu">
                <select name="perusahaan_id" class="perusahaan">
                        class="form-control @error('perusahaan_id') is-invalid @enderror">
                    <option value="">-- Pilih Perusahaan --</option>
                    @foreach($listPerusahaan as $id => $nama)
                        <option value="{{ $id }}"
                            {{ old('perusahaan_id', $departemen->perusahaan_id) == $id ? 'selected' : '' }}>
                            {{ $nama }}
                        </option>
                    @endforeach
                </select>

                @error('perusahaan_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tombol --}}
            <div class="button-action">
                <button type="submit" class="save">Save</button>
                <button type="reset" class="reset">Reset</button>
            </div>

            <div class="button-back">
                <a href="{{ route('departemen.index') }}" class="back">Back</a>
            </div>
        </form>
    </div>
</section>
@endsection
