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
        'dbname' => 'ibos',
        'username' => '{username}',
        'password' => '{password}',
        'tableprefix' => '',
        'charset' => 'utf8'
    ),
// -------------------------  CONFIG SECURITY  -------------------------- //
    'security' => array(
        'authkey' => '{authkey}',
    ),
// --------------------------  CONFIG COOKIE  --------------------------- //
    'cookie' => array(
        'cookiepre' => '{cookiepre}_',
        'cookiedomain' => '',
        'cookiepath' => '/',
    )
);
