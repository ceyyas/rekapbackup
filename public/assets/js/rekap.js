document.addEventListener("DOMContentLoaded", function() {
    // pastikan hanya jalan kalau ada elemen #backupChart
    const chartElement = document.getElementById('backupChart');
    if (!chartElement) return; // keluar kalau bukan di dashboard

    const rawData = window.dashboardData.rawData;
    const labels = window.dashboardData.labels;

    const perusahaanList = [...new Set(Object.values(rawData).flatMap(obj => Object.keys(obj)))];

    const colors = [
        '#36a2eb80',
        '#ff638480',
        '#4bc0c080',
        'rgba(255, 206, 86, 0.5)',
        '#9966ff80'
    ];

    const datasets = perusahaanList.map((nama, idx) => ({
        label: nama,
        data: labels.map(bulan => rawData[bulan] && rawData[bulan][nama] ? rawData[bulan][nama] : 0),
        backgroundColor: colors[idx % colors.length],
    }));

    const ctx = chartElement.getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: { labels, datasets },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Rekap Backup Per Bulan (GB)'
                },
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});


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
                console.log('Row data:', d);
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
        lengthChange: false,
        destroy: true
    });

    $('#perusahaan_id, #periode_id').on('change', function() {
        loadData(table);
    });

    function formatSizeBoth(sizeMB) {
        let sizeGB = (sizeMB / 1024).toFixed(2);
        return sizeMB + ' MB (' + sizeGB + ' GB)';
    }

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

                // input dengan data-inventori-id
                let cd700Col = (d.status_backup === 'completed')
                    ? '<input type="number" name="cd700['+d.departemen_id+']" value="'+(d.jumlah_cd700 ?? 0)+'" min="0" class="form-control form-control-sm" data-inventori-id="'+d.departemen_id+'">'
                    : (d.jumlah_dvd47 ?? 0)

                let dvd47Col = (d.status_backup === 'completed')
                    ? '<input type="number" name="dvd47['+d.departemen_id+']" value="'+(d.jumlah_dvd47 ?? 0)+'" min="0" class="form-control form-control-sm" data-inventori-id="'+d.departemen_id+'">'
                    : (d.jumlah_dvd47 ?? 0);

                let dvd85Col = (d.status_backup === 'completed')
                    ? '<input type="number" name="dvd85['+d.departemen_id+']" value="'+(d.jumlah_dvd85 ?? 0)+'" min="0" class="form-control form-control-sm" data-inventori-id="'+d.departemen_id+'">'
                    : (d.jumlah_dvd85 ?? 0);

                let rowNode = table.row.add([
                    d.nama_departemen,
                    formatSizeBoth(d.size_data),
                    formatSizeBoth(d.size_email),
                    formatSizeBoth(d.total_size),
                    cd700Col,
                    dvd47Col,
                    dvd85Col,
                    '<span class="status '+d.status_backup+'">'+d.status_backup+'</span>'
                ]).draw(false).node();

                $(rowNode).css('cursor','pointer').on('click', function(e) {
                    if ($(e.target).is('input[type=number]')) return;
                    window.location = detailUrl;
                });
            });
        });
    }

    // --- Auto-save listener (delegated) ---
        $('#rekapTable').on('change', 'input[type=number]', function() {
        let $input = $(this);
        let rekapId = $input.data('rekap-id'); 
        let periodeId = $('#periode_id').val();
        let perusahaanId = $('#perusahaan_id').val();

        let payload = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            rekap_id: rekapId,
            periode_id: periodeId,
            perusahaan_id: perusahaanId
        };

        if ($input.attr('name').startsWith('cd700')) {
            payload.cd700 = $input.val();
        } else if ($input.attr('name').startsWith('dvd47')) {
            payload.dvd47 = $input.val();
        } else if ($input.attr('name').startsWith('dvd85')) {
            payload.dvd85 = $input.val();
        }

        console.log('Payload autosave:', payload);

        $.post(window.rekapRoutes.autoSave, payload, function(res) {
            if (res.success) {
                console.log('Auto-save berhasil untuk rekap ' + rekapId);
            }
        }).fail(function(xhr) {
            console.error('Auto-save gagal:', xhr.responseText);
        });
    });

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


