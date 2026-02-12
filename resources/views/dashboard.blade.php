@extends('layouts.app')

@section('content')
  <div class="dashboard">
    <div class="cards">
      <div class="big-card bg-komputer">
        <div class="card-body" 
         onclick="window.location='{{ url('/rekap-backup') }}'">
          <h3>Total Backup Data Keseluruhan</h3>
          <p class="card-value">{{ $totalDataGB }} GB</p>
        </div>
      </div>
      <div class="big-card bg-laptop">
        <div class="card-body"
        onclick="window.location='{{ url('/rekap-backup') }}'">
          <h3>Total Backup Email Keseluruhan</h3>
          <p class="card-value">{{ $totalEmailGB }} GB</p>
        </div>
      </div>
      <div class="big-card bg-stoksisa">
        <div class="card-body"     
        onclick="window.location='{{ route('stok.index') }}'">
          <h3>Sisa Stok CD/DVD</h3>
          <p class="card-value">{{ $totalTersisa }}</p>
        </div>
      </div>
      <div class="big-card bg-pemakaian">
        <div class="card-body"
         onclick="window.location='{{ route('rekap-backup.cd-dvd') }}'">
          <h3>Pemakaian CD/DVD</h3>
          <p class="card-value">{{ $totalPemakaian }}</p>
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

