@extends('layouts.app')

@section('content')
  <div class="dashboard">
    <div class="cards">
      <div class="big-card bg-komputer">
        <div class="card-body">
          <h3>Total Komputer</h3>
          <p class="card-value">{{ $totalKomputer }}</p>
        </div>
      </div>
      <div class="big-card bg-laptop">
        <div class="card-body">
          <h3>Total Laptop</h3>
          <p class="card-value">{{ $totalLaptop }}</p>
        </div>
      </div>
    </div>

    <div class="chart">
      <canvas id="backupChart"></canvas>
    </div>
  </div>
@endsection

