<?php

date_default_timezone_set('Asia/Taipei');
$start = strtotime('2017/3/8 17:30');
$end = strtotime('2017/3/8 21:00');

$fp = gzopen('ptthot-201703.csv.gz', 'r');
fgetcsv($fp); // column

while ($rows = fgetcsv($fp)) {
    list($board, $time, $count) = $rows;
    if ($board != 'Baseball') {
        continue;
    }
    if ($time < $start or $time > $end) {
        continue;
    }
    echo $time . ' ' . $count . "\n";
}
