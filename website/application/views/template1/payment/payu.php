<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to PayU...</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: sans-serif; text-align: center; padding: 30px 15px; background: #f8f9fa; margin: 0; }
        .box { max-width: 500px; width: 100%; margin: 0 auto; background: #fff; padding: 25px 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .spinner { margin: 20px auto; border: 4px solid #f3f3f3; border-top: 4px solid #007bff; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body onload="document.getElementById('payuForm').submit();">
    <div class="box">
        <h3>Redirecting to PayU Gateway</h3>
        <p>Please wait while we transfer you securely to PayU to complete your payment.</p>
        <div class="spinner"></div>
        <p><small style="color: #666;">Do not refresh or close this browser window...</small></p>

        <form action="<?= html_escape($payu['action_url']); ?>" method="POST" id="payuForm">
            <input type="hidden" name="key" value="<?= html_escape($payu['key']); ?>" />
            <input type="hidden" name="hash" value="<?= html_escape($payu['hash']); ?>" />
            <input type="hidden" name="txnid" value="<?= html_escape($payu['txnid']); ?>" />
            <input type="hidden" name="amount" value="<?= html_escape($payu['amount']); ?>" />
            <input type="hidden" name="currency" value="<?= html_escape($payu['currency'] ?? ($order['currency'] ?? ($currency_code ?? 'USD'))); ?>" />
            <input type="hidden" name="firstname" value="<?= html_escape($payu['firstname']); ?>" />
            <input type="hidden" name="email" value="<?= html_escape($payu['email']); ?>" />
            <input type="hidden" name="phone" value="<?= html_escape($payu['phone']); ?>" />
            <input type="hidden" name="productinfo" value="<?= html_escape($payu['productinfo']); ?>" />
            <input type="hidden" name="surl" value="<?= html_escape($payu['surl']); ?>" />
            <input type="hidden" name="furl" value="<?= html_escape($payu['furl']); ?>" />
            <noscript>
                <input type="submit" value="Click here if you are not redirected automatically" />
            </noscript>
        </form>
    </div>
</body>
</html>
