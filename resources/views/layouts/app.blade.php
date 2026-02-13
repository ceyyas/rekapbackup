<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BMS</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</head>
<body>

    {{-- SIDEBAR --}}
    @include('layouts.sidebar')

    {{-- KONTEN HALAMAN --}}
    <section class="home">
        @yield('content')
    </section>

    <script>
        window.rekapRoutes = {
            detailData: "{{ route('rekap-backup.detail-data', ':id') }}",
            filter: "{{ route('rekap.filter') }}",
            detail: "{{ route('rekap-backup.detail-page', ':id') }}",
            autoSave: "{{ route('rekap.autoSave') }}",
            export: "{{ route('rekap-backup.export') }}",
            departemenByPerusahaan: "{{ url('/departemen/by-perusahaan') }}",
            pivot: "{{ route('rekap-backup.laporan-perusahaan-pivot') }}",
            exportPerusahaan: "{{ route('rekap-backup.export-perusahaan') }}",
            laporanBulanan: "{{ route('laporan-bulanan.data')}}",
            exportBulanan: "{{ route('rekap-backup.export-bulanan') }}",
            data: "{{ route('komputer.data') }}",
            exportBurning: "{{ route('rekap-backup.export-burning') }}"
        };
    </script>


    <script src="{{ asset('assets/js/rekap.js') }}"></script>
    <script src="{{ asset('assets/js/script.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0"></script>
    @stack('scripts')
</body>

</html>


