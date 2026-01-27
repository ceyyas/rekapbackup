@extends('layouts.app')

@section('content')
<div class="table-style">

    <div class="header-departemen">
        <h2>Data Periode Backup</h2>
    </div>

    <form method="POST" action="{{ route('periode.generate') }}">
        @csrf
        <label>Tahun Backup</label>
        <input type="number" name="tahun" value="{{ date('Y') }}" min="2000" max="2100" required>
        <button type="submit" class="aksi-edit">Generate Periode</button>
    </form>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif


    <table id="departemenTable" class="display">
        <thead>
            <tr>
                <th>No</th>
                <th>Bulan</th>
                <th>Tahun</th>
            </tr>
        </thead>
        <tbody>
            @foreach($periodes as $index => $periode)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $periode->bulan }}</td>
                    <td>{{ $periode->tahun }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>



</div>
@endsection