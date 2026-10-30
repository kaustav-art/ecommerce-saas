<?php
/**
 * Setup & Consolidate Bazaro Template Assets (Electronics & Grocery)
 * Copies templates/assets/ to website/assets/templates/bazaro/
 * Repairs 0-byte or .html images using templates/assets/img1/
 * Copies electronics & grocery images into website/assets/images/ for tenant catalog usage
 */

$root = dirname(__DIR__);
$srcAssets = $root . '/templates/assets';
$srcImg1   = $root . '/templates/assets/img1';
$dstBazaro = $root . '/website/assets/templates/bazaro';
$dstWebImg = $root . '/website/assets/images';

function copyDirRecursive($src, $dst) {
    if (!is_dir($src)) return;
    if (!is_dir($dst)) @mkdir($dst, 0777, true);
    $items = scandir($src);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $s = $src . '/' . $item;
        $d = $dst . '/' . $item;
        if (is_dir($s)) {
            copyDirRecursive($s, $d);
        } else {
            // Only overwrite if destination doesn't exist or is 0 bytes while source > 0
            if (!file_exists($d) || (filesize($d) == 0 && filesize($s) > 0)) {
                @copy($s, $d);
            }
        }
    }
}

echo "1. Copying templates/assets to website/assets/templates/bazaro...\n";
copyDirRecursive($srcAssets, $dstBazaro);

echo "2. Merging valid non-zero images from templates/assets/img1 into website/assets/templates/bazaro/img...\n";
function mergeValidImages($src, $dst) {
    if (!is_dir($src)) return;
    if (!is_dir($dst)) @mkdir($dst, 0777, true);
    foreach (scandir($src) as $item) {
        if ($item === '.' || $item === '..') continue;
        $s = $src . '/' . $item;
        $d = $dst . '/' . $item;
        if (is_dir($s)) {
            mergeValidImages($s, $d);
        } else {
            if (filesize($s) > 0 && pathinfo($s, PATHINFO_EXTENSION) !== 'html') {
                if (!file_exists($d) || filesize($d) == 0) {
                    @copy($s, $d);
                    echo "   Recovered: $d (" . filesize($s) . " bytes)\n";
                }
            }
        }
    }
}
mergeValidImages($srcImg1, $dstBazaro . '/img');
// Also repair inside templates/assets/img directly
mergeValidImages($srcImg1, $srcAssets . '/img');

echo "3. Copying Electronics & Grocery catalog images to website/assets/images/...\n";
copyDirRecursive($dstBazaro . '/img/electronics', $dstWebImg . '/electronics');
copyDirRecursive($dstBazaro . '/img/grocery', $dstWebImg . '/grocery');
copyDirRecursive($dstBazaro . '/img/watch', $dstWebImg . '/watch');
copyDirRecursive($dstBazaro . '/img/product', $dstWebImg . '/bazaro_products');

// Ensure fallback grocery product images exist for grocery-p-1..16
$validGroceryImgs = [
    $dstBazaro . '/img/product/grocery-p-3.jpg',
    $dstBazaro . '/img/product/grocery-p-4.jpg',
    $dstBazaro . '/img/product/grocery-p-9.jpg',
    $dstBazaro . '/img/product/grocery-p-11.jpg',
    $dstBazaro . '/img/product/grocery-p-12.jpg',
    $dstBazaro . '/img/product/grocery-p-13.jpg',
];
$validGroceryImgs = array_values(array_filter($validGroceryImgs, function($f) {
    return file_exists($f) && filesize($f) > 0;
}));

if (!empty($validGroceryImgs)) {
    for ($i = 1; $i <= 16; $i++) {
        $targetJpg = $dstBazaro . "/img/product/grocery-p-$i.jpg";
        $targetWeb = $dstWebImg . "/grocery/grocery-p-$i.jpg";
        if (!file_exists($targetJpg) || filesize($targetJpg) == 0) {
            $fallback = $validGroceryImgs[($i - 1) % count($validGroceryImgs)];
            @copy($fallback, $targetJpg);
        }
        @copy($targetJpg, $targetWeb);
    }
}

// Also ensure electronics arrival product-4/product-2.jpg has a valid image
$p4_1 = $dstBazaro . '/img/electronics/arrival-product/product-4/product-1.jpg';
$p4_2 = $dstBazaro . '/img/electronics/arrival-product/product-4/product-2.jpg';
if (file_exists($p4_1) && filesize($p4_1) > 0 && (!file_exists($p4_2) || filesize($p4_2) == 0)) {
    @copy($p4_1, $p4_2);
    @copy($p4_1, $dstWebImg . '/electronics/arrival-product/product-4/product-2.jpg');
}

// Create template preview images in website/assets/images/templates/
$tplPreviewDir = $dstWebImg . '/templates';
if (!is_dir($tplPreviewDir)) @mkdir($tplPreviewDir, 0777, true);
@copy($dstWebImg . '/slider/fashion-slideshow-01.jpg', $tplPreviewDir . '/fashion.jpg');
@copy($dstBazaro . '/img/electronics/slider/slide-1.jpg', $tplPreviewDir . '/electronics.jpg');
@copy($dstBazaro . '/img/grocery/grocery-banner-1.jpg', $tplPreviewDir . '/grocery.jpg');

echo "Done setting up Bazaro template assets!\n";
