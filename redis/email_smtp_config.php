<?php
// SMTP configuration for EmailHelper (replace values before use)
// Keep this file outside version control or add to .gitignore to avoid leaking credentials.
return [
    // SMTP server
    'host' => 'smtp.gmail.com',
    'port' => 587,
    // SMTP username (your Gmail address)
    'username' => 'projectmap1234@gmail.com',
    // SMTP app password (16 characters) or real password for the SMTP account
    'password' => 'iynhmgxtncvlzklk',
    // 'tls' or 'ssl' or empty for none
    'encryption' => 'tls',
    // From address used for outgoing mail
    'from_email' => 'no-reply@yourdomain.local',
    'from_name' => 'Sell Shop SPU',
    // Optional: whether to use SMTP (true) or fallback to PHP mail() (false)
    'use_smtp' => true,
];
