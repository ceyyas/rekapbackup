$(function () {

    function loadGlobal() {
        $.get('/rekap-backup/global', {
            perusahaan_id: $('#perusahaan_id').val(),
            periode_id: $('#periode_id').val() // ⬅️ FIX
        }, function (res) {
            $('#rekap-global').html(res);
        });
    }

    $('#perusahaan_id, #periode_id').on('change', function () {
        loadGlobal();
        $('#rekap-detail').html('');
    });

    $(document).on('click', '.btn-detail', function (e) {
        e.preventDefault();

        let departemenId = $(this).closest('tr').data('id');

        $.get('/rekap-backup/detail/' + departemenId, {
            periode_id: $('#periode_id').val() // ⬅️ FIX
        }, function (res) {
            $('#rekap-detail').html(res);
        });
    });

});
