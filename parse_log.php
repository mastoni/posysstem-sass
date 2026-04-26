<?php
$log = file_get_contents('storage/logs/laravel.log');
$lines = explode("\n", $log);
$relevant = array_filter($lines, function($l) {
    return strpos($l, '2026-04-26 14:4') !== false 
        || strpos($l, '2026-04-26 14:45') !== false
        || strpos($l, 'checkout') !== false
        || strpos($l, 'order_items') !== false
        || strpos($l, 'reduceStock') !== false
        || strpos($l, 'InventoryService') !== false;
});
echo implode("\n", array_slice(array_values($relevant), -40));
