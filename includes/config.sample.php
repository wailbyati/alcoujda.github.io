<?php
/**
 * Copy this file to config.php and fill in your real hosting credentials
 * (e.g. Bluehost cPanel > MySQL Databases).
 * config.php is the file includes/db.php actually loads — keep it OUT of
 * version control / never share it publicly.
 */

// --- Database (from your cPanel > MySQL Databases) ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'yourcpaneluser_alcalc');
define('DB_USER', 'yourcpaneluser_alcadmin');
define('DB_PASS', 'your-database-password');

// --- Site ---
define('SITE_URL', 'https://www.yourbranch.org'); // no trailing slash
define('ADMIN_URL', SITE_URL . '/admin');

// Random 32+ character string, unique to this install (used to sign session cookies).
// Generate one with: php -r "echo bin2hex(random_bytes(32));"
define('JWT_SECRET', 'replace-with-your-own-random-generated-string');