<?php defined('ALTUMCODE') || die() ?>

<div class="my-3">
    <a href="<?= $data->link->location_url . $data->link->utm_query ?>" data-location-url="<?= $data->link->url ?>" class="btn btn-block btn-primary link-btn <?= $data->link->design->link_class ?>" style="display: flex; align-items: center; padding: 5px; padding-left: 50px;<?= $data->link->design->link_style ?>">
    <canvas id="<?= 'thumb_pdf'. $data->link->link_id ?>" style="width:40px;"></canvas>&nbsp;&nbsp;&nbsp;&nbsp;
    <strong><?= $data->link->settings->title ?></strong>
    </a>
</div>

<script>

    (async () => {

        const loadingTask = PDFJS.getDocument("<?= $data->link->location_url ?>");
        const pdf = await loadingTask.promise;

        // Load information form the first page
        const page = await pdf.getPage(1);
        const scale = 1;
        const viewport = page.getViewport(scale);

        // Apply page dimensions to the <canvas> element
        const canvas = document.getElementById("thumb_pdf" + "<?= $data->link->link_id ?>");
        const context = canvas.getContext("2d");
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        // Render the page into the <canvas> element
        const renderContext = {
            canvasContext: context,
            viewport: viewport
        };
        await page.render(renderContext);
        // console.log("Page rendered");

    })();

</script>