@extends('layouts.app')

@section('content')
<div class="table-style">

    <div class="header-departemen">
        <h2>Data Periode Backup</h2>
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

        <button type="submit" class="aksi-edit">
            Generate Periode
        </button>
    </form>

    <table id="departemenTable" class="display">
        <thead>
            <tr>
                <th>No</th>
                <th>Bulan</th>
                <th>Tahun</th>
            </tr>
        </thead>
        <tbody>
            
        </tbody>


</div>
@endsection