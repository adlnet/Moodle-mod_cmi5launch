<?php

$loader = require 'vendor/autoload.php';

$lrs = new cmi5\RemoteLRS(
    'http://cloud.scorm.com/tc/public',
    '1.0.1',
    'user',
    'pass'
);
$response = $lrs->queryStatements(['limit' => 2]);
print_r($response);

