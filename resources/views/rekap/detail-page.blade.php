@extends('layouts.app')

@section('content')
<div class="table-style">
    <h2>Detail Backup — {{ $departemen->nama_departemen }}</h2>

    {{-- Hidden input untuk JS --}}
    <input type="hidden" id="departemen_id" value="{{ $departemen->id }}">
    <input type="hidden" id="periode_id" value="{{ request('periode_id') }}">

    {{-- Container untuk inject partial --}}
    <div id="detail-container"><p>Memuat data...</p></div>
</div>
@endsection

