@extends('layouts.app')

@section('content')
<div class="table-style">

    <h2>
        Detail Backup — {{ $departemen->nama_departemen }}
    </h2>

    {{-- AJAX --}}
    <div id="detail-container">
        <p>Memuat data...</p>
    </div>

</div>

<script>
$(function () {
    $.get(
        "{{ route('rekap-backup.detail-data', $departemen->id) }}",
        { periode_id: "{{ request('periode_id') }}" }
    )
    .done(function (html) {
        $('#detail-container').html(html);
    })
    .fail(function (xhr) {
        $('#detail-container').html(
            '<pre style="color:red">'+xhr.responseText+'</pre>'
        );
    });
});
</script>
@endsection
