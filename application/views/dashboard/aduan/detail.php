<!DOCTYPE html>
<html dir="ltr" lang="en">

<?php include_once(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'html_head.php'); ?>

<body>
    <?php include_once(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'preloader.php'); ?>
    <div 
        id="main-wrapper" 
        data-layout="vertical" 
        data-navbarbg="skin5" 
        data-sidebartype="full"
        data-sidebar-position="absolute" 
        data-header-position="absolute" 
        data-boxed-layout="full">
        <?php include_once(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'top_bar.php'); ?>
        <?php include_once(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'left_sidebar.php'); ?>
        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row align-items-center">
                    <div class="col-6">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 d-flex align-items-center">
                              <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="link"><i class="mdi mdi-home-outline fs-4"></i></a></li>
                              <li class="breadcrumb-item"><a href="<?= base_url('dashboard/aduan') ?>" class="link">Aduan</a></li>
                              <li id="nomor-aduan-breadcrumb" class="breadcrumb-item active" aria-current="page"><image src="/assets/images/loader/loading.svg"/></li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-6">
                        <div class="text-end upgrade-btn">
                            <a href="<?= base_url('dashboard/aduan') ?>" class="btn btn-primary text-white">
                                <i class="mdi mdi-arrow-left"></i>
                                Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="d-flex">
                    <h1 id="nomor-aduan-title" class="mb-0 fw-bold me-2"><image src="/assets/images/loader/loading.svg"/></h1>
                </div>
                <hr>
                <div class="row d-flex align-items-stretch">
                    <div class="col-lg-4 col-xlg-3 col-md-5">
                        <div class="card">
                            <div class="card-body">
                                <small class="text-muted">Nama Pengadu</small>
                                <h6 id="nama-aduan-text"><image src="/assets/images/loader/loading.svg"/></h6>
                                <small class="text-muted p-t-30 db">Email Pengadu</small>
                                <h6 id="email-aduan-text"><image src="/assets/images/loader/loading.svg"/></h6>
                                <small class="text-muted p-t-30 db">Status Aduan</small>
                                <h6 id="status-aduan-text"><image src="/assets/images/loader/loading.svg"/></h6>
                                <small class="text-muted p-t-30 db">Terakhir Diupdate</small>
                                <h6 id="last-updated-aduan-text"><image src="/assets/images/loader/loading.svg"/></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8 col-xlg-9 col-md-7">
                        <div class="card mb-4">
                            <div id="aduan-text" class="card-body">
                                <image src="/assets/images/loader/loading.svg"/>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <div class="card-body">
                                <h4>Tandai aduan sebagai:</h4>
                                <div class="row">
                                    <div class="col-3">
                                        <button id="diterima-btn" data-status="1" data-text="Diterima" class="btn btn-danger w-100 text-white update-btn">Diterima</button>
                                    </div>
                                    <div class="col-3">
                                        <button id="dibaca-btn" data-status="2" data-text="Dibaca" class="btn btn-warning w-100 text-white update-btn">Dibaca</button>
                                    </div>
                                    <div class="col-3">
                                        <button id="ditindaklanjuti-btn" data-status="3" data-text="Ditindaklanjuti" class="btn btn-success w-100 text-white update-btn">Ditindaklanjuti</button>
                                    </div>
                                    <div class="col-3">
                                        <button id="selesai-btn" data-status="4" data-text="Selesai" class="btn btn-info w-100 text-white update-btn">Selesai</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
    <?php include_once(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'base_js.php'); ?>
    <script src="<?= assets_url() ?>js/pages/admin/aduan/detail.js?v=<?= time() ?>"></script>
</body>

</html>