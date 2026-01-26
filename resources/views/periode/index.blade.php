@extends('layouts.app')

@section('content')
<div class="table-style">

    <div class="header-departemen">
        <h2>Data Periode Backup</h2>

        <a href="{{ route('departemen.create') }}" class="entry-button">
            + Tambah Data
        </a>
    </div>

    <form method="POST" action="{{ route('periode.generate') }}">
        @csrf

        <label>Tahun Backup</label>
        <input type="number"
            name="tahun"
            value="{{ date('Y') }}"
            min="2000"
            max="2100"
            required>

        <button type="submit" class="btn btn-primary">
            Generate Periode
        </button>
    </form>

</div>
@endsection