<?php

return array(
    // ----------------------------  CONFIG ENV  -----------------------------//
    'env' => array(
        'language' => 'zh_cn',
        'theme' => 'default'
    ),
    // ----------------------------  CONFIG DB  ----------------------------- //
    'db' => array(
        'host' => '{host}',
        'port' => '{port}',
        'dbname' => '{dbname}',
        'username' => '{username}',
        'password' => '{password}',
        'tableprefix' => '{tableprefix}',
        'charset' => '{charset}'
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
