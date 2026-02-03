<form method="POST" action="{{ route('rekap-backup.save') }}">
    @csrf
    <input type="hidden" name="periode_id" value="{{ \Carbon\Carbon::parse($periodeId)->format('Y-m') }}">

    <table class="display">
        <thead>
            <tr>
                <th>Hostname</th>
                <th>User</th>
                <th>Email</th>
                <th>Size Data (MB)</th>
                <th>Size Email (MB)</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($inventoris as $inv)
            <tr>
                <td>{{ $inv->hostname }}</td>
                <td>{{ $inv->username }}</td>
                <td>{{ $inv->email }}</td>
                <td>
                <input type="number"
                        name="data[{{ $inv->id }}][size_data]"
                        value="{{ $inv->size_data }}"
                        class="size-input size-data"> 
                    <small>({{ number_format($inv->size_data / 1024, 2) }} GB)</small>
                </td>

                <td>
                    <input type="number"
                        name="data[{{ $inv->id }}][size_email]"
                        value="{{ $inv->size_email }}"
                        class="size-input size-email"> 
                    <small>({{ number_format($inv->size_email / 1024, 2) }} GB)</small>
                </td>

                <td class="total-size">
                    {{ $inv->size_data + $inv->size_email }} MB 
                    <small>({{ number_format(($inv->size_data + $inv->size_email) / 1024, 2) }} GB)</small>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="button-action">
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>

    <div class="button-back">
        <button class="back">
        <a href="{{ route('rekap-backup.index', [
            'perusahaan_id' => request('perusahaan_id'),
            'periode_id' => request('periode_id')]) }}" >Kembali</a>
    </div>
</form>
