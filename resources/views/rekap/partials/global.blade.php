<table class="display">
    <thead>
        <tr>
            <th>Departemen</th>
            <th>Total Size Backup</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($departemens as $dept)
        <tr data-id="{{ $dept->id }}">
            <td>
                <a href="#" class="btn-detail">
                    {{ $dept->nama_departemen }}
                </a>
            </td>
            <td>{{ number_format($dept->total_size) }} MB</td>
        </tr>
        @endforeach
    </tbody>
</table>
