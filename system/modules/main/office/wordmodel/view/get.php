<?php

$MODEL_FILE = "../" . urldecode($_GET['model']) . ".dot";
if (!file_exists($MODEL_FILE)) {
    echo "文件不存在";
    exit;
}
readfile($MODEL_FILE);
?>