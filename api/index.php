<?php
if (!isset($_GET['route'])) {
    if (isset($_GET['path'])) {
        $_GET['route'] = $_GET['path'];
    } elseif (isset($_REQUEST['route'])) {
        $_GET['route'] = $_REQUEST['route'];
    }
}
require_once __DIR__ . '/Bootstrap.php';
Bootstrap::run();

