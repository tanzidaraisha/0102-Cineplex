<?php
/**
 * Email Configuration for 0102 Cineplex
 * 
 * INSTRUCTIONS:
 * 1. Go to https://myaccount.google.com/security
 * 2. Enable "2-Step Verification"
 * 3. Go to https://myaccount.google.com/apppasswords
 * 4. Generate an App Password for "Mail"
 * 5. Paste the 16-character app password below (without spaces)
 */

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 465);
define('SMTP_USERNAME', 'shaharearrakib55@gmail.com');  // <-- Put your Gmail address here, e.g. yourname@gmail.com
define('SMTP_PASSWORD', 'esjl uooc xfxv uhvp');  // <-- Put your Gmail App Password here (16 chars, no spaces)
define('SMTP_FROM_NAME', '0102 Cineplex');

// Set to true once you've configured the above
define('EMAIL_ENABLED', true);
