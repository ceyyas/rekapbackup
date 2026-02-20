// dashboard
document.addEventListener("DOMContentLoaded", function() {
    const chartElement = document.getElementById('backupChart');
    if (!chartElement) return; 
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

function initDepartemenPage() {
    let table = $('#departemenTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.rekapRoutes.dataDepartemen,
            data: function (d) {
                d.perusahaan_id = $('#perusahaan_id').val(); 
            }
        },
        dom: 'lfrtip',
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'perusahaan', name: 'perusahaan.nama_perusahaan' },
            { data: 'nama_departemen', name: 'nama_departemen' },
            { data: 'aksi', orderable: false, searchable: false }
        ]
    });

    $('#perusahaan_id').on('change', function () {
        table.ajax.reload(); 
    });
}

function initKomputerPage() {
    $(document).ready(function () {
        if ($('#komputerTable').length) {
            let table = $('#komputerTable').DataTable({
                dom: 'lfrtip',
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: window.rekapRoutes.data,
                    data: function (d) {
                        d.perusahaan_id = $('#perusahaan_id').val();
                        d.departemen_id = $('#departemen_id').val();
                        d.kategori = $('#kategori_id').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'perusahaan.nama_perusahaan', name: 'perusahaan.nama_perusahaan' },
                    { data: 'departemen.nama_departemen', name: 'departemen.nama_departemen' },
                    { data: 'hostname', name: 'hostname' },
                    { data: 'username', name: 'username' },
                    { data: 'email', name: 'email' },
                    { data: 'kategori', name: 'kategori' },
                    { data: 'status', name: 'status' },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
                ],
            });
            
            $('#perusahaan_id').on('change', function () {
                loadDepartemen($(this).val(), $('#departemen_id'));
                table.ajax.reload();
            });
            $('#departemen_id').on('change', function () {
                table.ajax.reload();
            });
            $('#kategori_id').on('change', function () {
                table.ajax.reload();
            });

            function loadDepartemen(perusahaanId, departemenSelect) {
                departemenSelect.html('<option value="">Loading...</option>');
                if (!perusahaanId) {
                    departemenSelect.html('<option value="">-- Pilih Departemen --</option>');
                    return;
                }
                $.get(window.rekapRoutes.departemenByPerusahaan, { perusahaan_id: perusahaanId }, function (data) {
                    departemenSelect.html('<option value="">-- Pilih Departemen --</option>');
                    $.each(data, function (i, d) {
                        departemenSelect.append(`<option value="${d.id}">${d.nama_departemen}</option>`);
                    });
                });
            }
        }

        if ($('#createForm').length) {
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
        }

        if ($('#editForm').length) {
            let perusahaanSelect = document.getElementById('perusahaan_id');
            let departemenSelect = document.getElementById('departemen_id');

            if (perusahaanSelect && departemenSelect) {
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
            }
        }
    });
}

// master data stok 
function initStokPage() {
    $('#stokTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: window.rekapRoutes.dataStok,
        dom: 'lfrtip',
        columns: [
            { data: 'id' },
            { data: 'nomor_sppb' },
            { data: 'nama_barang' },
            { data: 'jumlah_barang' },
            { data: 'pemakaian' },
            { data: 'tersisa' },
            { data: 'aksi' }
        ]
    });
}

// rekap backup global
function initIndexPage() {
    if (!document.getElementById('rekapTable')) return;

    let table = $('#rekapTable').DataTable({
        paging: false,
        info: true,
        searching: true,
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

        let params = new URLSearchParams(window.location.search);
        params.set('periode_id', periodeId);
        params.set('perusahaan_id', perusahaanId);

        let newUrl = window.location.pathname + '?' + params.toString();
        window.history.replaceState(null, '', newUrl);

        let filterUrl = window.rekapRoutes.filter;
        let detailUrlTemplate = window.rekapRoutes.detail;

        $.get(filterUrl, { perusahaan_id: perusahaanId, periode_id: periodeId }, function(data) {
            table.clear();

            $.each(data, function(i, d) {
                let detailUrl = detailUrlTemplate.replace(':id', d.id) + '?' + params.toString();

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
                .attr('href', window.rekapRoutes.export + '?' + params.toString())
                .show();
        });
    }

}

// input cd/dvd
function initCdDvdPage() {
    if (!document.getElementById('cdDvdTable')) return;

    let table = $('#cdDvdTable').DataTable({
        paging: false,
        info: true,
        searching: true,
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

        $('#btnExportBurning')
            .attr('href', window.rekapRoutes.exportBurning + 
                '?perusahaan_id=' + perusahaanId + 
                '&periode_id=' + periodeId)
            .show();
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

        $.post(window.rekapRoutes.autoSave, payload)
         .fail(function(xhr) {
            let res = xhr.responseJSON;
            if (res && res.error) {
                alert(res.error); 
            }
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

// laporan per perusahaan
function initLaporanPerusahaan() {
    if (!document.getElementById('perusahaan_id') ||
        !document.getElementById('laporanPivot') ||
        !document.getElementById('laporanChart')) {
        return; 
    }
    let chartInstance = null;
    $('#perusahaan_id').on('change', function() {
        let perusahaanId = $(this).val();
        if (!perusahaanId) return;

        $.get(window.rekapRoutes.pivot, { perusahaan_id: perusahaanId }, function(res) {
            console.log(res); 
            let periodes = Array.isArray(res.periodes) ? res.periodes : [];
            let pivot = res.pivot || {};

            let thead = '<tr><th>Departemen</th>';
            periodes.forEach(p => { thead += '<th>'+p+'</th>'; });
            thead += '</tr>';
            $('#laporanPivot thead').html(thead);

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

            let labels = [];
            let data = [];
            Object.keys(pivot).forEach(dept => {
                labels.push(dept);
                let total = 0;
                periodes.forEach(p => {
                    let val = pivot[dept][p];
                    if (val) {
                        total += parseInt(val.toString().replace(/\D/g,'')) || 0;
                    }
                });
                data.push(total);
            });

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
}

// laporan bulanan
function initLaporanBulanan() {
    if (
        !document.getElementById('laporanTable') ||
        !document.getElementById('laporanChart')) {
        return; 
    }
    $(document).ready(function() {
        let chartInstance = null;

        $('#periode_bulanan').on('change', function() {
            let periode = $(this).val();
            if (!periode) return;

            $.get(window.rekapRoutes.laporanBulanan, { periode_bulanan: periode })
                .done(res => {
                    console.log("AJAX sukses:", res);

                    let tbody = '';
                    let labels = [];
                    let totals = [];
                    res.forEach(r => {
                        tbody += `<tr>
                            <td>${r.perusahaan ?? '-'}</td>
                            <td>${(r.data/1024).toFixed(2)} GB</td>
                            <td>${(r.email/1024).toFixed(2)} GB</td>
                            <td>${(r.total/1024).toFixed(2)} GB</td>
                        </tr>`;
                        labels.push(r.perusahaan ?? '-');
                        totals.push(r.total/1024);
                    });
                    $('#laporanTable tbody').html(tbody);

                    if (chartInstance) chartInstance.destroy();
                    let canvas = document.getElementById('laporanChart');
                    if (canvas) {
                        let ctx = canvas.getContext('2d');
                        chartInstance = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Total Size (GB)',
                                    data: totals,
                                    backgroundColor: 'rgba(75, 192, 192, 0.6)'
                                }]
                            }
                        });
                    }
                })
                .fail(err => console.error("AJAX gagal:", err));
        });
    });

        $('#btnExportBulanan').on('click', function() {
            let periode = $('#periode_bulanan').val();
            if (!periode) return;

            window.location.href = window.rekapRoutes.exportBulanan + '?periode_bulanan=' + periode;
        });
}

document.addEventListener('DOMContentLoaded', function () {
    initStokPage();
    initDepartemenPage();
    initKomputerPage();
    initIndexPage();
    initCdDvdPage();
    initDetailPage();
    initLaporanPerusahaan();
    initLaporanBulanan();
});



