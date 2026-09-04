<?php
file_put_contents(__DIR__ . '/js_error.log', date('Y-m-d H:i:s') . ' - ' . file_get_contents('php://input') . "\n", FILE_APPEND);
echo "ok";
