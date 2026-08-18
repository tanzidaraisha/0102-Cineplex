<?php
// SSLCommerz Configuration

// Sandbox credentials - replace with live credentials for production
define('SSLCZ_STORE_ID', 'testbox');
define('SSLCZ_STORE_PASSWORD', 'qwerty');
define('SSLCZ_IS_SANDBOX', true);

// API URLs based on sandbox setting
if (SSLCZ_IS_SANDBOX) {
    define('SSLCZ_SESSION_API', 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php');
    define('SSLCZ_VALIDATION_API', 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php');
} else {
    define('SSLCZ_SESSION_API', 'https://securepay.sslcommerz.com/gwprocess/v4/api.php');
    define('SSLCZ_VALIDATION_API', 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php');
}
