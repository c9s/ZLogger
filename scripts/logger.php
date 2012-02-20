#!/usr/bin/env php
<?php
require 'tests/bootstrap.php';
$logger = new ZLogger\FileLogger(array( 
    'path' => 'log/info.log',
));
echo "Listening...\n";
$logger->start();
