<?php

return [
    'mode' => env('MAIL_SERVER_MODE', 'external'),
    'domain' => env('MAIL_DOMAIN', 'carikerja.asia'),
    'hostname' => env('MAIL_HOSTNAME', 'mail.carikerja.asia'),
    'dkim_selector' => env('MAIL_DKIM_SELECTOR', 'default'),
    'postmaster' => env('MAIL_POSTMASTER', 'postmaster@carikerja.asia'),
    'required_ports' => [25, 465, 587, 993],
];
