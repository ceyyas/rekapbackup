<table class="display">
    <thead>
        <tr>
            <th>Hostname</th>
            <th>Total Size Backup</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($inventoris as $inv)
        <tr>
            <td>{{ $inv->hostname }}</td>
            <td>{{ number_format($inv->total_size) }} MB</td>
        </tr>
        @endforeach
    </tbody>
</table>
