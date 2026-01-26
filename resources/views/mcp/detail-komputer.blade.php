@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Detail Backup Komputer</h4>

    <form method="POST" action="{{ route('backup.store') }}">
        @csrf

        <input type="hidden" name="perusahaan_id" value="{{ $perusahaanId }}">
        <input type="hidden" name="departemen_id" value="{{ $departemenId }}">
        <input type="hidden" name="periode_id" value="{{ $periodeId }}">

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Hostname</th>
                    <th>User</th>
                    <th>Data (MB)</th>
                    <th>Email (MB)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inventori as $pc)
                <tr>
                    <td>{{ $pc->hostname }}</td>
                    <td>{{ $pc->username }}</td>

                    <td>
                        <input type="number"
                               name="backup[{{ $pc->id }}][size_data]"
                               class="form-control"
                               min="0">
                    </td>

                    <td>
                        <input type="number"
                               name="backup[{{ $pc->id }}][size_email]"
                               class="form-control"
                               min="0">
                    </td>

                    <td>
                        <span class="badge bg-info">Input</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <button class="btn btn-primary">Simpan Backup</button>
    </form>
</div>
@endsection
