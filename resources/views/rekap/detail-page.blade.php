@extends('layouts.app')

@section('content')
<div class="table-style">

    <h2>
        Detail Backup — {{ $departemen->nama_departemen }}
    </h2>

    {{-- WADAH DATA --}}
    <div id="detail-container">
        <p>Loading data...</p>
    </div>

</div>

<script>
$(function () {
    $.get(
        "{{ route('rekap-backup.detail-data', $departemen->id) }}",
        { periode_id: "{{ request('periode_id') }}" },
        function (html) {
            $('#detail-container').html(html);
        }
    );
});
</script>
@endsection
