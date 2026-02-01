document.addEventListener("DOMContentLoaded", function() {
    const rawData = window.dashboardData.rawData;
    const labels = window.dashboardData.labels;

    console.log("Labels:", labels);
    console.log("RawData:", rawData);

    const perusahaanList = [...new Set(Object.values(rawData).flatMap(obj => Object.keys(obj)))];

    const colors = [
        'rgba(54, 162, 235, 0.5)',
        'rgba(255, 99, 132, 0.5)',
        'rgba(75, 192, 192, 0.5)',
        'rgba(255, 206, 86, 0.5)',
        'rgba(153, 102, 255, 0.5)'
    ];

    const datasets = perusahaanList.map((nama, idx) => ({
        label: nama,
        data: labels.map(bulan => rawData[bulan] && rawData[bulan][nama] ? rawData[bulan][nama] : 0),
        backgroundColor: colors[idx % colors.length],
    }));

    const ctx = document.getElementById('backupChart').getContext('2d');
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

                // kolom input CD/DVD hanya jika completed
                let cd700Col = (d.status_backup === 'completed')
                    ? '<input type="number" name="cd700['+d.id+']" value="'+(d.jumlah_cd700 ?? 0)+'" min="0" class="form-control form-control-sm">'
                    : (d.jumlah_cd700 ?? 0);

                let dvd47Col = (d.status_backup === 'completed')
                    ? '<input type="number" name="dvd47['+d.id+']" value="'+(d.jumlah_dvd47 ?? 0)+'" min="0" class="form-control form-control-sm">'
                    : (d.jumlah_dvd47 ?? 0);

                let dvd85Col = (d.status_backup === 'completed')
                    ? '<input type="number" name="dvd85['+d.id+']" value="'+(d.jumlah_dvd85 ?? 0)+'" min="0" class="form-control form-control-sm">'
                    : (d.jumlah_dvd85 ?? 0);

                // tambahkan row
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

                // klik row -> redirect, kecuali kalau target input number
                $(rowNode).css('cursor','pointer').on('click', function(e) {
                    if ($(e.target).is('input[type=number]')) return;
                    window.location = detailUrl;
                });
            });
        });
    }

    // --- Auto-save listener ---
    $('#rekapTable').on('change', 'input[type=number]', function() {
        let $input = $(this);
        let match = $input.attr('name').match(/\[(\d+)\]/);
        if (!match) return;
        let departemenId = match[1];
        let periodeId = $('#periode_id').val();
        let perusahaanId = $('#perusahaan_id').val();

        let payload = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            departemen_id: departemenId,
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

        $.post(window.rekapRoutes.autoSave, payload, function(res) {
            if (res.success) {
                console.log('Auto-save berhasil untuk departemen ' + departemenId);
            }
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


