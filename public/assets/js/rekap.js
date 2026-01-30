// index data komputer 
    $(document).ready(function() {
    // $('#departemenTable').DataTable({
    //     pageLength: 10,
    //     lengthMenu: [5, 10, 25, 50]
    // });
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

        $.get('/komputers/filter', { perusahaan_id: perusahaanId, departemen_id: departemenId }, function (html) {
            $('#departemenTable tbody').html(html);
        });
    });

// edit data komputer 
document.addEventListener('DOMContentLoaded', function () {
    let perusahaanSelect = document.getElementById('perusahaan_id');
    let departemenSelect = document.getElementById('departemen_id');

    if (!perusahaanSelect || !departemenSelect) return;

    perusahaanSelect.addEventListener('change', function () {
        let perusahaanId = this.value;

        departemenSelect.innerHTML = '<option>Loading...</option>';

        fetch(`/departemen/by-perusahaan?perusahaan_id=${perusahaanId}`)
            .then(res => res.json())
            .then(data => {
                departemenSelect.innerHTML = '<option>-- Pilih Departemen --</option>';
                data.forEach(d => {
                    departemenSelect.innerHTML += `<option value="${d.id}">${d.nama_departemen}</option>`;
                });
            });
    });
});


// create data komputer
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
            departemen.append(
                `<option value="${d.id}">${d.nama_departemen}</option>`
            );
        });
    });
});

// --- Index Page ---
function initIndexPage() {
    if (!document.getElementById('rekapTable')) return;

    let table = $('#rekapTable').DataTable({
        paging: false,
        info: false,
        searching: false,
        lengthChange: false
    });

    $('#perusahaan_id, #periode_id').on('change', function() {
        loadData(table);
    });

    function loadData(table) {
        let perusahaanId = $('#perusahaan_id').val();
        let periodeId = $('#periode_id').val();

        if (!perusahaanId || !periodeId) return;

        let filterUrl = window.rekapRoutes.filter;
        let detailUrlTemplate = window.rekapRoutes.detail;

        $.get(filterUrl, { perusahaan_id: perusahaanId, periode_id: periodeId }, function(data) {
            table.clear();

            $.each(data, function(i, d) {
                let detailUrl = detailUrlTemplate.replace(':id', d.id)
                    + "?periode_id=" + periodeId
                    + "&perusahaan_id=" + perusahaanId;

                table.row.add([
                    d.nama_departemen,
                    formatSizeBoth(d.size_data),
                    formatSizeBoth(d.size_email),
                    formatSizeBoth(d.total_size),
                    d.status_backup
                ]).node().setAttribute('onclick', "window.location='" + detailUrl + "'");

                    // Fungsi helper: tampilkan MB dan GB
                    function formatSizeBoth(sizeMB) {
                        let sizeGB = (sizeMB / 1024).toFixed(2);
                        return sizeMB + ' MB (' + sizeGB + ' GB)';
                    }
                });


            table.draw();
        });
    }
}

// --- Detail Page ---
function initDetailPage() {
    let container = document.getElementById('detail-container');
    if (!container) return;

    let departemenId = document.getElementById('departemen_id')?.value;
    let periodeId = document.getElementById('periode_id')?.value;

    if (!departemenId || !periodeId) return;

    let url = window.rekapRoutes.detailData.replace(':id', departemenId) +
              "?periode_id=" + periodeId;

    fetch(url)
        .then(res => res.text())
        .then(html => {
            container.innerHTML = html;
        });
}

// --- Panggil sesuai halaman ---
document.addEventListener('DOMContentLoaded', function () {
    initIndexPage();
    initDetailPage();
});


