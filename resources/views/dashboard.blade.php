@extends('layouts.app')

@section('content')
  <div class="container mt-4">
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

    <div class="chart">
      <canvas id="backupChart"></canvas>
    </div>
  </div>
@endsection


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const labels = {!! json_encode(array_keys($dataChart)) !!};
    const rawData = {!! json_encode($dataChart) !!};

    // Ambil daftar perusahaan unik
    const perusahaanList = [...new Set(Object.values(rawData).flatMap(obj => Object.keys(obj)))];

    // Buat dataset per perusahaan
    const colors = [
        'rgba(54, 162, 235, 0.5)',
        'rgba(255, 99, 132, 0.5)',
        'rgba(75, 192, 192, 0.5)',
        'rgba(255, 206, 86, 0.5)',
        'rgba(153, 102, 255, 0.5)'
    ];

    const datasets = perusahaanList.map((nama, idx) => ({
        label: nama,
        data: labels.map(bulan => rawData[bulan][nama] ?? 0),
        backgroundColor: colors[idx % colors.length],
    }));

    new Chart(document.getElementById('backupChart'), {
        type: 'bar',
        data: { labels, datasets },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Rekap Backup Per Bulan (GB)'
                }
            }
        }
    });
    </script>



