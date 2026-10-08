<?php
/**
 * Mail configuration for Lost & Found password reset.
 * IMPORTANT: fill these values in your local environment; never commit real passwords.
 */
return [
    'host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
    'port' => (int) (getenv('SMTP_PORT') ?: 587),
    'username' => getenv('SMTP_USERNAME') ?: '',
    'password' => getenv('SMTP_PASSWORD') ?: '',
    'encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls',
    'from_email' => getenv('SMTP_FROM_EMAIL') ?: '',
    'from_name' => getenv('SMTP_FROM_NAME') ?: 'Lost & Found SMK Informatika Sumedang',
];
