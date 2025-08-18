<?php

$date = new DateTime($_GET['after'] ?? '');

if ($date->format('N') != 1)
    $date->modify('next monday');


$weeks = rangeOfWeeksFrom($date, 52, 850, 1370);

http_out(
    200,
    json_encode($weeks, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    ['Content-Type' => 'application/json; charset=utf-8']
);
exit;

