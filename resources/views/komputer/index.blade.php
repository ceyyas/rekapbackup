@extends('layouts.app')

@section('content')
<div class="table-style">

    <div class="header-departemen">
        <h2>Data Komputer</h2>

        <a href="{{ route('komputer.create') }}" class="entry-button">
            + Tambah Data
        </a>
    </div>

    <form method="GET" action="{{ route('komputer.index') }}">
        <div class="filter-menu">

            <select id="perusahaan_id" name="perusahaan_id" class="filter">
                <option value="">-- Pilih Perusahaan --</option>
                @foreach ($perusahaans as $perusahaan)
                    <option value="{{ $perusahaan->id }}"
                        {{ request('perusahaan_id') == $perusahaan->id ? 'selected' : '' }}>
                        {{ $perusahaan->nama_perusahaan }}
                    </option>
                @endforeach
            </select>

            <select id="departemen_id" name="departemen_id" class="filter">
                <option value="">-- Pilih Departemen --</option>
            </select>


        </div>
    </form>

    <table id="departemenTable" class="display">
        <thead>
            <tr>
                <th>No</th>
                <th>Perusahaan</th>
                <th>Departemen</th>
                <th>Hostname</th>
                <th>Username</th>
                <th>Email</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($komputers as $index => $inventori)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $inventori->perusahaan->nama_perusahaan }}</td>
                <td>{{ $inventori->departemen->nama_departemen }}</td>
                <td>{{ $inventori->hostname }}</td>
                <td>{{ $inventori->username }}</td>
                <td>{{ $inventori->email }}</td>
                <!-- awal tombol aksi -->
                <td class="text-center">
                    <!-- tombol show -->
                    <button class="aksi-show"><a href="{{ route('komputer.show', $inventori->id) }}"><i class='bx bx-show'></i></a></button>
                            
                    <!-- tombol edit -->
                    <button class="aksi-edit"><a href="{{ route('komputer.edit', $inventori->id) }}"><i class='bx bx-edit-alt'></i></a></button>

                    <!-- form untuk tombol delete -->
                    <form action="{{ route('komputer.destroy', $inventori->id) }}"
                        method="POST"
                        onsubmit="return confirm('Are you sure?')"
                        style="display: inline;">
                        @csrf <!-- hidden token -->
                        @method('DELETE') <!-- tambahlan untuk delete form method post -->
                                
                        <!-- tombol delete -->
                        <button type="submit" class="aksi-delete"><i class='bx bx-trash'></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
    $('#departemenTable').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50]
    });
});

    $('#perusahaan_id').on('change', function () {
        let perusahaanId = $(this).val();
        let departemen = $('#departemen_id');

        departemen.html('<option>Loading...</option>');

        if (!perusahaanId) {
            departemen.html('<option>-- Pilih Departemen --</option>');
            return;
        }

        $.get('/departemen/by-perusahaan', { perusahaan_id: perusahaanId }, function (data) {
            departemen.html('<option>-- Pilih Departemen --</option>');
            $.each(data, function (i, d) {
                departemen.append(`<option value="${d.id}">${d.nama_departemen}</option>`);
            });
        });
    });

    $('#departemen_id').on('change', function () {
        let perusahaanId = $('#perusahaan_id').val();
        let departemenId = $(this).val();

        $.get('/komputers/filter', { perusahaan_id: perusahaanId, departemen_id: departemenId }, function (data) {
            let tbody = $('#departemenTable tbody');
            tbody.html('');
            $.each(data, function (i, k) {
                tbody.append(`
                    <tr>
                        <td>${i+1}</td>
                        <td>${k.perusahaan?.nama_perusahaan ?? '-'}</td>
                        <td>${k.departemen?.nama_departemen ?? '-'}</td>
                        <td>${k.hostname}</td>
                        <td>${k.username}</td>
                        <td>${k.email}</td>
                        <td class="text-center">
                            <!-- tombol show -->
                            <button class="aksi-show">
                                <a href="/komputers/${k.id}">
                                    <i class='bx bx-show'></i>
                                </a>
                            </button>

                            <!-- tombol edit -->
                            <button class="aksi-edit">
                                <a href="/komputers/${k.id}/edit">
                                    <i class='bx bx-edit-alt'></i>
                                </a>
                            </button>

                            <!-- tombol delete -->
                            <form action="/komputers/${k.id}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="aksi-delete"><i class='bx bx-trash'></i></button>
                            </form>
                        </td>
                    </tr>
                `);
            });
        });
    });
</script>
@endpush
