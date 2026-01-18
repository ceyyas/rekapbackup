@extends('layouts.app')

@section('content')
<section class="home">
    <div class="entry-style">
        <h2>Tambah Data Laptop</h2>

        <form action="{{ route('laptop.store') }}" method="POST">
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
                       value="{{ old('email') }}"
                       required>
            </div>
            {{-- Perusahaan --}}
            <div class="perusahaan-menu">
                <select name="perusahaan_id" id="perusahaan" required class="perusahaan">
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
                <select name="departemen_id" id="departemen" required class="perusahaan">
                    <option value="">-- Pilih Departemen --</option>
                    @foreach ($perusahaans as $p)
                        @foreach ($p->departemen as $d)
                            <option value="{{ $d->id }}">
                                {{ $d->nama_departemen }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>


            <div class="button-action">
                <button type="submit" class="save">Save</button>
                <button type="reset" class="reset">Reset</button>
            </div>

            <div class="button-back">
                <a href="{{ route('laptop.index') }}" class="back">Back</a>
            </div>

        </form>
    </div>
</section>
@endsection

