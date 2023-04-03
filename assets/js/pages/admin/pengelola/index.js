$(function() {
    let current_page = 1;
    let total_page = 1;
    let is_fetching = false;

    const getData = () => {
        $('#admin-table>tbody').html('<tr><td colspan="5" class="text-center"><image src="/assets/images/loader/loading.svg"/></td></tr>');
        if(is_fetching) {
            return;
        }

        // set fetching state
        $('#prev-page').attr('disabled', true);
        $('#next-page').attr('disabled', true);
        is_fetching = true;

        axios.get(`/api/dashboard/admin?page=${$('#current-page').val()}`)
            .then(res => {
                const { total_page } = res.data;
                // reset table
                $('#admin-table>tbody').html('');

                // batasin min page
                if($('#current-page').val() == 1) {
                    $('#prev-page').attr('disabled', true);
                } else {
                    $('#prev-page').attr('disabled', false);
                }

                // batasin max page
                if($('#current-page').val() == total_page) {
                    $('#next-page').attr('disabled', true);
                } else {
                    $('#next-page').attr('disabled', false);
                }

                if($('#current-page').val() < 1) {
                    $('#current-page').val(1);
                    is_fetching = false;
                    getData();
                    return;
                }
                if($('#current-page').val() > total_page) {
                    $('#current-page').val(total_page);
                    is_fetching = false;
                    getData();
                    return;
                }
                $('#total-page').html('dari '+total_page);

                // set content
                res.data.data.forEach(item => {
                    $('#admin-table>tbody').append(`
                        <tr role="button" data-id="${item.id}">
                            <td><strong>${item.name}</strong></td>
                            <td>${item.email}</td>
                            <td>${item.arsip_count} arsip</td>
                            <td>${item.active == "1" ? `<span class="badge bg-success">Aktif</span>` : `<span class="badge bg-danger">Nonaktif</span>`}</td>
                            <td>${item.last_login ? item.last_login : '-'}</td>
                        </tr>
                    `)
                })
            })
            .catch(e => {
                alert(e.response.data.message)
            })
            .finally(() => {
                is_fetching = false;
            })
    }

    // load data on ready;
    getData(current_page);

    // get data on prev click
    $('#prev-page').on('click', function() {
        $('#current-page').val($('#current-page').val()*1-1)
        getData();
    });

    // get data on next click
    $('#next-page').on('click', function() {
        $('#current-page').val($('#current-page').val()*1+1)
        getData();
    });

    $('#current-page').on('keyup paste', function() {
        $(this).val($(this).val().replace(/[^0-9]/gi, ''));
    })
    $('#current-page').on('keyup paste', debounce(function() {
        getData();
    }, 300))
    // delegate tr click to detail
    $(document).on('click', 'tr', function() {
        let id = $(this).data('id');
        if(id) {
            window.location.href = '/dashboard/pengelola/detail/'+id;
        }
    })

    $('#pengelolaBaruBtn').on('click', function() {
        $('#pengelolaBaruModal').modal('show')
    })

    $('#pengelola-baru-form').on('submit', function(e) {
        $("#pengelola-baru-form button[type=submit]").html('<image src="/assets/images/loader/loading-light.svg"/>')
        e.preventDefault();
        let roles = {
            pengelola: $('#otoritas-pengelola').is(':checked'),
            arsip: $('#otoritas-arsip-publik').is(':checked') ? 'publik' : 'semua',
            klasifikasi: $('#otoritas-klasifikasi').is(':checked'),
            aduan: $('#otoritas-aduan').is(':checked'),
        };

        let data = new FormData();
        data.append($('meta[name=token_name]').attr('content'), $('meta[name=token_hash]').attr('content'))
        data.append('nama', $('#nama-input').val())
        data.append('email', $('#email-input').val())
        data.append('roles', JSON.stringify(roles))

        $('#pengelolaBaruModal :input').attr('disabled', true)

        axios.post(`/api/dashboard/admin/baru`, data)
            .then(res => {
                $('meta[name=token_hash]').attr('content', res.data.csrf)
                $('#pengelolaBaruModal').modal('hide')
                $('#nama-input').val('')
                $('#email-input').val('')
                current_page = 1;
                getData(current_page);
            })
            .catch(e => {
                $('meta[name=token_hash]').attr('content', e.response.data.csrf)
                if(e.response.data.validation) {
                    Object.entries(e.response.data.validation).forEach(([k, v]) => {
                        $(`#${k}Error`).html(v);
                    })
                }
            })
            .finally(() => {
                $('#pengelolaBaruModal :input').attr('disabled', false);
                $("#pengelola-baru-form button[type=submit]").html('Simpan')
            })
    })
})