<?php
$dispatch_note = !empty($store_settings['shipping_dispatch_note']) 
    ? $store_settings['shipping_dispatch_note'] 
    : 'Dispatched within 24-48 hours with courier tracking.';
$is_cod = (!empty($order['payment_method']) && (strtolower($order['payment_method']) === 'cod' || strpos(strtolower($order['payment_method']), 'cash') !== false));
$is_paid = (!empty($order['payment_status']) && strtolower($order['payment_status']) === 'paid');

// Clean and deduplicate recipient name, address lines, and phone number
$raw_address = trim($order['shipping_address'] ?? '');
$lines = array_filter(array_map('trim', preg_split('/[\r\n]+/', $raw_address)));

$clean_lines = [];
$recipient_name = !empty($order['customer_name']) ? trim($order['customer_name']) : '';
$recipient_phone = !empty($order['customer_phone']) ? trim($order['customer_phone']) : '';
$addr_type = !empty($order['address_type']) ? strtoupper($order['address_type']) : '';

foreach ($lines as $line) {
    if (preg_match('/\((WORK|HOME)\)/i', $line, $m)) {
        if (empty($addr_type)) {
            $addr_type = strtoupper($m[1]);
        }
        $line = trim(preg_replace('/\((WORK|HOME)\)/i', '', $line));
    }

    // Skip if line matches recipient name or begins with it
    if (!empty($recipient_name) && (strcasecmp($line, $recipient_name) === 0 || stripos($line, $recipient_name) === 0)) {
        continue;
    }

    // Skip if line contains phone/mobile number
    if (preg_match('/^(phone|mobile):\s*(.*)$/i', $line, $pm)) {
        if (empty($recipient_phone) && !empty($pm[2])) {
            $recipient_phone = trim($pm[2]);
        }
        continue;
    }

    if (!empty($line)) {
        $clean_lines[] = $line;
    }
}

$display_address = !empty($clean_lines) ? implode(', ', $clean_lines) : '';
?>

<style>
/* Mobile App-like Order Success Experience */
.mobile-app-wrapper {
    background-color: #f1f5f9;
    min-height: calc(100vh - 120px);
    display: flex;
    justify-content: center;
    padding: 20px 12px 36px;
}
.mobile-app-container {
    max-width: 480px;
    width: 100%;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* App Header Card */
.app-header-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 28px 20px 20px;
    text-align: center;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
}
.app-check-circle {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: #10b981;
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    margin-bottom: 14px;
    box-shadow: 0 0 0 7px #d1fae5, 0 4px 12px rgba(16, 185, 129, 0.25);
    animation: appCheckPop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
@keyframes appCheckPop {
    0% { transform: scale(0.5); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
.app-order-status {
    font-size: 21px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 4px;
    letter-spacing: -0.3px;
}
.app-order-email {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 12px;
}
.app-amount-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 6px 18px;
    border-radius: 30px;
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 12px;
}
.app-amount-chip .chip-label {
    color: #64748b;
    font-weight: 500;
    font-size: 12px;
}
.app-order-id-bar {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #64748b;
}
.app-order-id-bar strong {
    color: #1e293b;
    letter-spacing: 0.3px;
}
.app-copy-btn {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    padding: 2px 7px;
    border-radius: 6px;
    color: #475569;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.15s ease;
}
.app-copy-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
}

/* App Info Tiles */
.app-tile-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
    display: flex;
    align-items: flex-start;
    gap: 14px;
}
.app-tile-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    flex-shrink: 0;
}
.icon-delivery {
    background: #ecfdf5;
    color: #059669;
}
.icon-address {
    background: #eff6ff;
    color: #2563eb;
}
.icon-payment {
    background: #f5f3ff;
    color: #7c3aed;
}
.app-tile-body {
    flex: 1;
    min-width: 0;
}
.app-tile-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #94a3b8;
    margin-bottom: 3px;
}
.app-tile-title {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.35;
}
.app-tile-desc {
    font-size: 12px;
    color: #64748b;
    margin-top: 3px;
    line-height: 1.4;
}
.app-pill {
    display: inline-block;
    padding: 1px 7px;
    font-size: 10px;
    font-weight: 700;
    border-radius: 10px;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    vertical-align: middle;
    margin-left: 4px;
}
.app-badge-paid {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #bbf7d0;
}
.app-badge-cod {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    background: #fffbeb;
    color: #d97706;
    border: 1px solid #fde68a;
}

/* App Action Buttons */
.app-actions-card {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 6px;
}
.app-btn-primary {
    background-color: #2874f0;
    color: #ffffff !important;
    border: none;
    border-radius: 14px;
    padding: 14px 20px;
    font-size: 15px;
    font-weight: 700;
    text-align: center;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 14px rgba(40, 116, 240, 0.28);
    transition: transform 0.1s ease, background-color 0.2s ease;
}
.app-btn-primary:active {
    transform: scale(0.98);
}
.app-btn-primary:hover {
    background-color: #1a62d6;
}
.app-btn-secondary {
    background-color: #ffffff;
    color: #1e293b !important;
    border: 1px solid #cbd5e1;
    border-radius: 14px;
    padding: 13px 20px;
    font-size: 14px;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: background-color 0.15s ease, transform 0.1s ease;
}
.app-btn-secondary:active {
    transform: scale(0.98);
}
.app-btn-secondary:hover {
    background-color: #f8fafc;
    border-color: #94a3b8;
}

/* App Footer */
.app-support-footer {
    text-align: center;
    font-size: 12px;
    color: #94a3b8;
    padding: 8px 0 16px;
}
.app-support-footer a {
    color: #2874f0;
    text-decoration: none;
    font-weight: 600;
}
.app-support-footer a:hover {
    text-decoration: underline;
}
</style>

<div class="mobile-app-wrapper">
    <div class="mobile-app-container">

        <!-- 1. App Header Card (Celebration + Total + Order ID) -->
        <div class="app-header-card">
            <div class="app-check-circle">
                <i class="fa-solid fa-check"></i>
            </div>
            
            <h1 class="app-order-status">Order Confirmed!</h1>
            <div class="app-order-email">
                Confirmation sent to <strong><?= html_escape($order['customer_email']); ?></strong>
            </div>

            <!-- Amount Chip -->
            <div>
                <div class="app-amount-chip">
                    <span class="chip-label"><?= $is_cod ? 'To Pay' : 'Total Paid'; ?>:</span>
                    <span class="text-primary"><?= $currency_symbol . number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>

            <!-- Order ID -->
            <div class="app-order-id-bar">
                <span>Order ID: <strong class="font-monospace">#<?= html_escape($order['order_number']); ?></strong></span>
                <button type="button" class="app-copy-btn" id="btn-copy-order-id" title="Copy Order ID" onclick="copyOrderId('<?= html_escape($order['order_number']); ?>')">
                    <i class="fa-regular fa-copy me-1"></i>Copy
                </button>
            </div>
        </div>

        <!-- 2. Dynamic Delivery Notice Tile -->
        <div class="app-tile-card">
            <div class="app-tile-icon icon-delivery">
                <i class="fa-solid fa-truck-fast"></i>
            </div>
            <div class="app-tile-body">
                <div class="app-tile-label">Estimated Delivery</div>
                <div class="app-tile-title"><?= html_escape($dispatch_note); ?></div>
                <div class="app-tile-desc">
                    <i class="fa-solid fa-circle-check text-success me-1"></i> Order placed & preparing for dispatch
                </div>
            </div>
        </div>

        <!-- 3. Delivery Address Tile -->
        <div class="app-tile-card">
            <div class="app-tile-icon icon-address">
                <i class="fa-solid fa-location-dot"></i>
            </div>
            <div class="app-tile-body">
                <div class="app-tile-label">Delivery Address</div>
                <div class="app-tile-title">
                    <?= html_escape($recipient_name); ?>
                    <?php if (!empty($addr_type)): ?>
                        <span class="app-pill"><?= $addr_type; ?></span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($display_address)): ?>
                    <div class="app-tile-desc"><?= html_escape($display_address); ?></div>
                <?php endif; ?>
                <?php if (!empty($recipient_phone)): ?>
                    <div class="app-tile-desc fw-medium text-dark mt-1">
                        <i class="fa-solid fa-phone me-1 text-muted" style="font-size: 11px;"></i> <?= html_escape($recipient_phone); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 4. Payment Method Tile -->
        <div class="app-tile-card">
            <div class="app-tile-icon icon-payment">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div class="app-tile-body">
                <div class="app-tile-label">Payment Mode</div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="app-tile-title"><?= strtoupper($order['payment_method']); ?></div>
                    <?php if ($is_paid): ?>
                        <span class="app-badge-paid"><i class="fa-solid fa-circle-check me-1"></i> Paid</span>
                    <?php else: ?>
                        <span class="app-badge-cod"><i class="fa-solid fa-clock me-1"></i> Cash on Delivery</span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($order['payment_transaction_id'])): ?>
                    <div class="app-tile-desc font-monospace mt-1" style="font-size: 11px;">
                        Txn ID: <?= html_escape(strlen($order['payment_transaction_id']) > 20 ? substr($order['payment_transaction_id'], 0, 20) . '...' : $order['payment_transaction_id']); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 5. App-like Action Buttons -->
        <div class="app-actions-card">
            <a href="<?= site_url('account/order/' . $order['order_number']); ?>" class="app-btn-primary">
                <i class="fa-solid fa-truck-fast"></i> Track Order
            </a>
            <a href="<?= site_url('shop'); ?>" class="app-btn-secondary">
                <i class="fa-solid fa-bag-shopping"></i> Continue Shopping
            </a>
        </div>

        <!-- 6. Trust & Help Footer -->
        <div class="app-support-footer">
            <div class="mb-1"><i class="fa-solid fa-shield-halved text-success me-1"></i> 100% Safe & Secure Purchase</div>
            <div>Need help? <a href="<?= site_url('contact'); ?>">Contact Support</a></div>
        </div>

    </div>
</div>

<script>
function copyOrderId(orderId) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(orderId).then(onCopied);
    } else {
        var textArea = document.createElement('textarea');
        textArea.value = orderId;
        textArea.style.position = 'fixed';
        textArea.style.opacity = '0';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            onCopied();
        } catch (err) {}
        document.body.removeChild(textArea);
    }
}
function onCopied() {
    var btn = document.getElementById('btn-copy-order-id');
    if (btn) {
        btn.innerHTML = '<i class="fa-solid fa-check text-success me-1"></i>Copied!';
        setTimeout(function() {
            btn.innerHTML = '<i class="fa-regular fa-copy me-1"></i>Copy';
        }, 2000);
    }
}
</script>
