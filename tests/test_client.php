<?php
require_once __DIR__ . '/bootstrap.php';

$conn     = new \Processing\Connection\RedisConnection();
$procesor = new \Processing\Processor($conn);

$procesor -> push('filesystem', 'ls', '.');