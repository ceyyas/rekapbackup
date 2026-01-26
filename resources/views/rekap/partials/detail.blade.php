@extends('layouts.app')

@section('content')
<div class="table-style">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Komputer</th>
                <th>Size Backup</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($komputers as $pc)
            <tr>
                <td>{{ $pc->nama }}</td>
                <td>{{ number_format($pc->size_backup) }} MB</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection