<?php
$log = file_get_contents('storage/logs/laravel.log');
$parts = explode('[2026-04-26', $log);
$last = end($parts);
echo '[2026-04-26' . substr($last, 0, 3000);
