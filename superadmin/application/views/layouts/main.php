<?php
$this->load->view('layouts/header', $data);
$this->load->view('layouts/sidebar', $data);
?>

<div class="sa-main">
    <?php $this->load->view('layouts/topbar', $data); ?>

    <main class="sa-content">
        <!-- Flash Messages -->
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
                <i class="fa-solid fa-circle-check fs-5 me-2"></i>
                <div class="flex-grow-1"><?= $this->session->flashdata('success') ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
                <i class="fa-solid fa-circle-exclamation fs-5 me-2"></i>
                <div class="flex-grow-1"><?= $this->session->flashdata('error') ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('info')): ?>
            <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
                <i class="fa-solid fa-circle-info fs-5 me-2"></i>
                <div class="flex-grow-1"><?= $this->session->flashdata('info') ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- View Content -->
        <?php $this->load->view($content_view, $data); ?>
    </main>

    <?php $this->load->view('layouts/footer', $data); ?>
