// dashboard
document.addEventListener("DOMContentLoaded", function() {
    const chartElement = document.getElementById('backupChart');
    if (!chartElement) return; // keluar kalau bukan di dashboard

    const rawData = window.dashboardData.rawData;
    const labels = window.dashboardData.labels;

    const perusahaanList = [...new Set(Object.values(rawData).flatMap(obj => Object.keys(obj)))];

    const colors = [
        '#36a2eb80',
        '#ff638480',
        '#4bc0c080',
        '#ffce5680',
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


$(document).ready(function () {
    let table = $('#komputerTable').DataTable({
        dom: 'lfrtip' 
    });

    $('#customSearch').on('keyup', function () {
        table.search(this.value).draw();
    });

    function loadDepartemen(perusahaanId, departemenSelect) {
        departemenSelect.html('<option>Loading...</option>');

        if (!perusahaanId) {
            departemenSelect.html('<option>-- Pilih Departemen --</option>');
            return;
        }

        $.get('/departemen/by-perusahaan', { perusahaan_id: perusahaanId }, function (data) {
            departemenSelect.html('<option>-- Pilih Departemen --</option>');
            $.each(data, function (i, d) {
                departemenSelect.append(`<option value="${d.id}">${d.nama_departemen}</option>`);
            });
        });
    }

    // === FILTER KOMPUTER ===
    $('#perusahaan_id_komputer').on('change', function () {
        let perusahaanId = $(this).val();
        loadDepartemen(perusahaanId, $('#departemen_id_komputer'));
    });

    $('#departemen_id_komputer').on('change', function () {
        let perusahaanId = $('#perusahaan_id_komputer').val();
        let departemenId = $(this).val();

        $.get('/komputers/filter', { perusahaan_id: perusahaanId, departemen_id: departemenId }, function (html) {
            $('#komputerTable tbody').html(html);
        });
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


// create data 
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

// rekap backup global
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

                let rowNode = table.row.add([
                    d.nama_departemen,
                    formatSizeBoth(d.size_data),
                    formatSizeBoth(d.size_email),
                    formatSizeBoth(d.total_size),
                    '<span class="status '+d.status_backup+'">'+d.status_backup+'</span>',
                    '<span class="status_data '+d.status_data+'">'+d.status_data+'</span>'
                ]).draw(false).node();

                $(rowNode).css('cursor','pointer').on('click', function() {
                    window.location = detailUrl;
                });
            });

            $('#btnExport')
            .attr('href', window.rekapRoutes.export + 
                '?perusahaan_id=' + perusahaanId + 
                '&periode_id=' + periodeId)
            .show();
        });
    }

}

// input cd/dvd
function initCdDvdPage() {
    if (!document.getElementById('cdDvdTable')) return;

    let table = $('#cdDvdTable').DataTable({
        paging: false,
        info: false,
        searching: false,
        lengthChange: false,
        destroy: true
    });

    $('#perusahaan_id, #periode_id').on('change', function() {
        loadCdDvdData(table);
    });

    function loadCdDvdData(table) {
        let perusahaanId = $('#perusahaan_id').val();
        let periodeId = $('#periode_id').val();

        if (!perusahaanId || !periodeId) return;

        let filterUrl = window.rekapRoutes.filter;

        $.get(filterUrl, { perusahaan_id: perusahaanId, periode_id: periodeId }, function(data) {
            table.clear();

            $.each(data, function(i, d) {
                let cd700Col = (d.status_backup === 'completed')
                    ? '<input type="number" name="cd700['+d.id+']" value="'+(d.jumlah_cd700 ?? 0)+'" min="0" class="jumlah-cd" data-inventori-id="'+d.inventori_id+'">'
                    : (d.jumlah_cd700 ?? 0);

                let dvd47Col = (d.status_backup === 'completed')
                    ? '<input type="number" name="dvd47['+d.id+']" value="'+(d.jumlah_dvd47 ?? 0)+'" min="0" class="jumlah-cd" data-inventori-id="'+d.inventori_id+'">'
                    : (d.jumlah_dvd47 ?? 0);

                let dvd85Col = (d.status_backup === 'completed')
                    ? '<input type="number" name="dvd85['+d.id+']" value="'+(d.jumlah_dvd85 ?? 0)+'" min="0" class="jumlah-cd" data-inventori-id="'+d.inventori_id+'">'
                    : (d.jumlah_dvd85 ?? 0);

                table.row.add([
                    d.nama_departemen,
                    d.size_data + ' MB',
                    d.size_email + ' MB',
                    d.total_size + ' MB',
                    cd700Col,
                    dvd47Col,
                    dvd85Col,
                    '<span class="status '+d.status_backup+'">'+d.status_backup+'</span>'
                ]).draw(false);
            });
        });
    }

    // auto save
    $('#cdDvdTable').on('change', 'input[type=number]', function() {
        let $input = $(this);
        let inventoriId = $input.data('inventori-id');
        let periodeId = $('#periode_id').val();
        let perusahaanId = $('#perusahaan_id').val();

        let payload = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            inventori_id: inventoriId,
            periode_id: periodeId,
            perusahaan_id: perusahaanId
        };

        if ($input.attr('name').startsWith('cd700')) {
            payload.cd700 = parseInt($input.val());
        } else if ($input.attr('name').startsWith('dvd47')) {
            payload.dvd47 = parseInt($input.val());
        } else if ($input.attr('name').startsWith('dvd85')) {
            payload.dvd85 = parseInt($input.val());
        }

        console.log('Payload autosave:', payload);

        $.post(window.rekapRoutes.autoSave, payload)
         .done(function(res) {
            if (res.success) {
                console.log('Auto-save berhasil untuk inventori ' + inventoriId);
            }
         })
         .fail(function(xhr) {
            console.error('Auto-save gagal:', xhr.responseText);
         });
    });
}

// detail backup per departemen
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
    initCdDvdPage();
    initDetailPage();
});

// laporan perusahaan
let chartInstance = null;

    $('#perusahaan_id').on('change', function() {
        let perusahaanId = $(this).val();
        if (!perusahaanId) return;

        $.get(window.rekapRoutes.pivot, { perusahaan_id: perusahaanId }, function(res) {
            console.log(res); // cek isi JSON

            // pastikan periodes array
            let periodes = Array.isArray(res.periodes) ? res.periodes : [];
            let pivot = res.pivot || {};

            // rebuild header
            let thead = '<tr><th>Departemen</th>';
            periodes.forEach(p => { thead += '<th>'+p+'</th>'; });
            thead += '</tr>';
            $('#laporanPivot thead').html(thead);

            // rebuild body
            let tbody = '';
            Object.keys(pivot).forEach(dept => {
                tbody += '<tr><td>'+dept+'</td>';
                periodes.forEach(p => {
                    let val = pivot[dept][p] ?? '-';
                    tbody += '<td>'+val+'</td>';
                });
                tbody += '</tr>';
            });
            $('#laporanPivot tbody').html(tbody);

            // data grafik: total per departemen (akumulasi semua periode)
            let labels = [];
            let data = [];
            Object.keys(pivot).forEach(dept => {
                labels.push(dept);
                let total = 0;
                periodes.forEach(p => {
                    let val = pivot[dept][p];
                    if (val) {
                        // hapus " MB" lalu parse angka
                        total += parseInt(val.toString().replace(/\D/g,'')) || 0;
                    }
                });
                data.push(total);
            });

            // buat grafik bar
            if (chartInstance) chartInstance.destroy();
            let ctx = document.getElementById('laporanChart').getContext('2d');
            chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Size (MB)',
                        data: data,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        title: { display: true, text: 'Total Size per Departemen' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        });
    });

    $('#btnExportPerusahaan').on('click', function() {
        let perusahaanId = $('#perusahaan_id').val();
        if (!perusahaanId) return;
        window.location.href = window.rekapRoutes.exportPerusahaan + '?perusahaan_id=' + perusahaanId;
    });



