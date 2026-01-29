@extends('layouts.app')

@section('content')
    <div class="row">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                Total Komputer: {{ $totalKomputer }}
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                Total Laptop: {{ $totalLaptop }}
            </div>
        </div>
    </div>
</div>
@endsection
