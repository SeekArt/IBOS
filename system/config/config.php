<?php

return array(
    // ----------------------------  CONFIG ENV  -----------------------------//
    'env' => array(
        'language' => 'zh_cn',
        'theme' => 'default'
    ),
    // ----------------------------  CONFIG DB  ----------------------------- //
    'db' => array(
        'host' => '127.0.0.1',
        'port' => '3306',
        'dbname' => 'ibos_open',
        'username' => 'root',
        'password' => 'root',
        'tableprefix' => 'ibos_',
        'charset' => 'utf8'
    ),
// -------------------------  CONFIG SECURITY  -------------------------- //
    'security' => array(
        'authkey' => '68fc036s6b6iTYUD',
    ),
// --------------------------  CONFIG COOKIE  --------------------------- //
    'cookie' => array(
        'cookiepre' => '9z99_',
        'cookiedomain' => '',
        'cookiepath' => '/',
    )
);
