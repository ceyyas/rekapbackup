@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="big-card bg-primary text-white">
            <div class="card-body">
                <h3>Total Komputer</h3>
                <p class="card-value">{{ $totalKomputer }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="big-card bg-success text-white">
            <div class="card-body">
                <h3>Total Laptop</h3>
                <p class="card-value">{{ $totalLaptop }}</p>
            </div>
        </div>
    </div>
</div>

<div class="chart-container">
    <canvas id="backupChart"></canvas>
</div>
@endsection
