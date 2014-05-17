<?php

if (!defined('DB_HOST')) {
    define('DB_HOST', '<your_host_here>');
}
if (!defined('DB_USERNAME')) {
    define('DB_USERNAME', '<your_user_here>');
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', '<your_password_here>');
}
if (!defined('DB')) {
    define('DB', '<your_database_name_here>');
}
if (!defined('API_KEYS')) {
    define('API_KEYS', serialize(array('<api_keys_here>', '<api_keys_here>', '<api_keys_here>')));
}