@extends('layouts.app')

@section('content')
<div class="table-style">
    <h2>{{ $departemen->nama_departemen }} — {{ $periodeFormatted }}</h2>

    <input type="hidden" id="departemen_id" value="{{ $departemen->id }}">
    <input type="hidden" id="periode_id" value="{{ request('periode_id') }}">

    <div id="detail-container"><p>Memuat data...</p></div>
</div>
@endsection

