<?php defined('ALTUMCODE') || die() ?>

<div class="d-flex justify-content-between mb-4">
    <h1 class="h3"><i class="fa fa-fw fa-xs fa-globe text-primary-900 mr-2"></i> <?= $this->language->admin_domains->header ?></h1>

    <div class="col-auto">
        <a href="<?= url('admin/domain-create') ?>" class="btn btn-primary rounded-pill"><i class="fa fa-fw fa-plus-circle"></i> <?= $this->language->admin_domain_create->menu ?></a>
    </div>
</div>
<p class="text-muted"><?= $this->language->admin_domains->subheader ?></p>

<?php display_notifications() ?>
<div class="mt-5">
    <table id="results" class="table table-custom">
        <thead class="thead-black">
        <tr>
            <th><?= $this->language->admin_domains->table->type ?></th>
            <th><?= $this->language->admin_domains->table->host ?></th>
            <th><?= $this->language->admin_domains->table->links ?></th>
            <th><?= $this->language->admin_domains->table->date ?></th>
            <th></th>
        </tr>
        </thead>
        <tbody id="dragtb"></tbody>
    </table>
</div>
<?php ob_start() ?>
<link href="<?= SITE_URL . ASSETS_URL_PATH . 'css/datatables.min.css' ?>" rel="stylesheet" media="screen">
<script src="<?= SITE_URL . ASSETS_URL_PATH . 'js/libraries/sortable.js' ?>"></script>
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
<script src="<?= SITE_URL . ASSETS_URL_PATH . 'js/libraries/datatables.min.js' ?>"></script>
<script>

let datatable = $('#results').DataTable({
    language: <?= json_encode($this->language->datatable) ?>,
    serverSide: true,
    processing: true,

    ajax: {
        url: <?= json_encode(url('admin/domains/read')) ?>,
        type: 'POST',
    },
    'fnCreatedRow': function (nRow, aData) {
        
            $(nRow).attr('id', 'myRow' + (aData.domain_id)); // or whatever you choose to set as the id
        },
    autoWidth: false,
    lengthMenu: [[25, 50, 100], [25, 50, 100]],
    rowReorder: true,
    columns: [

        {
            data: 'type',
            searchable: false,
            sortable: true,
            rowReorder: true
        },
        {
            data: 'host',
            searchable: true,
            sortable: true,
            rowReorder: true
        },
        {
            data: 'links',
            searchable: false,
            sortable: true,
            rowReorder: true
        },
        {
            data: 'date',
            searchable: false,
            sortable: true,
            rowReorder: true
        },
        {
            data: 'actions',
            searchable: false,
            sortable: true,
            rowReorder: true
        }
    ],
    responsive: true,
    drawCallback: () => {
        $('[data-toggle="tooltip"]').tooltip();
    },
    dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
        "<'table-responsive table-custom-container my-3'tr>" +
        "<'row'<'col-sm-12 col-md-5 text-muted'i><'col-sm-12 col-md-7'p>>"
});



let sortable = Sortable.create(document.getElementById('dragtb'), {
        animation: 150,
        handle: '.drag',
        onUpdate: (event) => {
            let global_token = $('input[name="global_token"]').val();

            let tableDom = $('#dragtb')[0];
            tableDom.querySelectorAll('[role="row"]').forEach(function (el){

            });
            let domains = [];
            $('#dragtb > [role=row]').each((i, elm) => {
                let domain = {
                    domain_id: $(elm).attr('id'),
                    order: i+1
                };

                domains.push(domain);
            });

            $.ajax({
                type: 'POST',
                url: 'admin/domains',
                data: {
                    request_type: 'order',
                    domains,
                    global_token
                },
                // dataType: 'json',
                success: function(response) {
                    console.log("success", response);
                },
                error: function() {
                    console.log("error")
                }
            });

            /* Refresh iframe */
            $('#biolink_preview_iframe').attr('src', $('#biolink_preview_iframe').attr('src'));
        }
    });

    // setTimeout(() => {
        
        
    // let docu = $('#dragtb')[0];
    // docu.querySelectorAll('[role="row"]').forEach(function (el){

    //     console.log(el)
    // // el.classList.remove("active");
    // });

    // }, 4000);

</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
