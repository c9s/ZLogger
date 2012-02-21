#!/usr/bin/env php
<?php
require 'tests/bootstrap.php';
$logger = new ZLogger\FileLogger(array( 
    'bind' => 'tcp://127.0.0.1:5555',
    'path' => 'log/info.log',
));
echo "Listening " . $logger->bind . "...\n";
$logger->start();
