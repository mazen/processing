<?php
/*
 * This file is part of the Processing library.
 *
 * (c) Marcel Beerta <marcel@etcpasswd.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
require_once __DIR__ . '/bootstrap.php';

$conn     = new \Processing\Connection\RedisConnection();
$procesor = new \Processing\Processor($conn);


$procesor->addWorker('filesystem', 'ls', function($directory = '.'){
    echo `ls -al $directory`;
});

$procesor->doWork();