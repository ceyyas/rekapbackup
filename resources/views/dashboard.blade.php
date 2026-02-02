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
      <div class="big-card bg-stok">
        <div class="card-body">
          <h3>Total Stok CD/DVD</h3>
          <p class="card-value">{{ $totalStok }}</p>
        </div>
      </div>
    </div>

    <div class="chart">
      <canvas id="backupChart"></canvas>
    </div>
  </div>
@endsection

@push('scripts')
<script>
    window.dashboardData = {
            rawData: @json($dataChart),
            labels: @json(array_keys($dataChart))
        };
</script>

