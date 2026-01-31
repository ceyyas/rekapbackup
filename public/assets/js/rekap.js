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

        $.get('/komputers/filter', { perusahaan_id: perusahaanId, departemen_id: departemenId }, function (data) {
            let tbody = $('#departemenTable tbody');
            tbody.html('');
            $.each(data, function (i, k) {
                tbody.append(`
                    <tr>
                        <td>${i+1}</td>
                        <td>${k.perusahaan?.nama_perusahaan ?? '-'}</td>
                        <td>${k.departemen?.nama_departemen ?? '-'}</td>
                        <td>${k.hostname}</td>
                        <td>${k.username}</td>
                        <td>${k.email}</td>
                        <td class="text-center">
                            <!-- tombol show -->
                            <button class="aksi-show">
                                <a href="/komputers/${k.id}">
                                    <i class='bx bx-show'></i>
                                </a>
                            </button>

                            <!-- tombol edit -->
                            <button class="aksi-edit">
                                <a href="/komputers/${k.id}/edit">
                                    <i class='bx bx-edit-alt'></i>
                                </a>
                            </button>

                            <!-- tombol delete -->
                            <form action="/komputers/${k.id}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="aksi-delete"><i class='bx bx-trash'></i></button>
                            </form>
                        </td>
                    </tr>
                `);
            });
        });
    });

// edit data komputer 
document.getElementById('perusahaan_id').addEventListener('change', function () {
    let perusahaanId = this.value;
    let departemen = document.getElementById('departemen_id');

    departemen.innerHTML = '<option>Loading...</option>';

    fetch(`/departemen/by-perusahaan?perusahaan_id=${perusahaanId}`)
        .then(res => res.json())
        .then(data => {
            departemen.innerHTML = '<option>-- Pilih Departemen --</option>';

            data.forEach(d => {
                departemen.innerHTML +=
                    `<option value="${d.id}">${d.nama_departemen}</option>`;
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

// index blade rekap
$(document).ready(function() {
    let table = $('#rekapTable').DataTable({
        paging: false,       
        info: false,         
        searching: false,   
        lengthChange: false 
    });

    $('#perusahaan_id, #periode_id').on('change', function() {
        loadData(table);
    });
});

function loadData(table) {
    let perusahaanId = $('#perusahaan_id').val();
    let periodeId = $('#periode_id').val();

    if (!perusahaanId || !periodeId) return;

    $.get("{{ route('rekap.filter') }}", 
        { perusahaan_id: perusahaanId, periode_id: periodeId }, 
        function(data) {
            table.clear();

            $.each(data, function(i, d) {
            let detailUrl = "{{ route('rekap-backup.detail-page', ':id') }}"
                .replace(':id', d.id)
                + "?periode_id=" + $('#periode_id').val()
                + "&perusahaan_id=" + $('#perusahaan_id').val();

                table.row.add([
                    d.nama_departemen,
                    d.size_data + ' MB',
                    d.size_email + ' MB',
                    d.total_size + ' MB',
                    d.status_backup
                ]).node().setAttribute('onclick', "window.location='" + detailUrl + "'");
            });

            table.draw();
        }
    );
}

// rekap - detail page
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


