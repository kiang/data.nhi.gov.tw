<?php
require __DIR__ . '/vendor/autoload.php';
$basePath = dirname(__DIR__);

$targetFile = $basePath . '/docs/antigen.json';
/**
    [0] => 3835010175
    [1] => 錦昌中醫診所
    [2] => 2
    [3] => 4
    [4] => 2022
    [5] => NNNNNNYNNNNNNYNNNNYYY
    [6] => 每週日及週五 週六晚上休診，每日下午3:00門診
    [7] => 0
    [8] => 20220426173036
 */
$fh = fopen($basePath . '/raw/A21030000I-D21006-001.csv', 'r');
fgetcsv($fh, 2048);
$pool = [];
while ($line = fgetcsv($fh, 2048)) {
    $pool[$line[0]] = $line[5];
}

$fullFile = $basePath . '/raw/full.csv';
$full = [];
if (file_exists($fullFile)) {
    $fh = fopen($fullFile, 'r');
    while ($line = fgetcsv($fh, 2048)) {
        $line[7] = 0; // reset the count
        $full[$line[0]] = $line;
    }
    fclose($fh);
}

use Goutte\Client;

$client = new Client();
/**
 * 健保特約機構防疫家用快篩剩餘數量明細
 * https://data.nhi.gov.tw/Datasets/DatasetDetail.aspx?id=698
 */
$rawFile = $basePath . '/raw/A21030000I-D03001-001.csv';
$client->request('GET', 'https://data.nhi.gov.tw/resource/Nhi_Fst/Fstdata.csv');
file_put_contents($rawFile, $client->getResponse()->getContent());

/**
    [0] => 醫事機構代碼
    [1] => 醫事機構名稱
    [2] => 醫事機構地址
    [3] => 經度
    [4] => 緯度
    [5] => 醫事機構電話
    [6] => 廠牌項目
    [7] => 快篩試劑截至目前結餘存貨數量
    [8] => 來源資料時間
    [9] => 備註
 */

$fh = fopen($rawFile, 'r');
fgetcsv($fh, 2048);
$check = [];
$pointMap = [
    '5903200194' => [120.70416, 24.094718],
    '5901190515' => [121.501308, 25.036584],
];

while ($line = fgetcsv($fh, 2048)) {
    if (isset($check[$line[0]])) {
        continue;
    }
    $check[$line[0]] = true;
    if (isset($line[10])) {
        $keyCount = count($line);
        for ($i = 10; $i <= $keyCount; $i++) {
            $line[9] .= ',' . $line[$i];
        }
    }
    foreach ($line as $k => $v) {
        if ($k !== 8) {
            $line[$k] = preg_replace('/\s+/', '', $v);
        }
    }
    if ($line[3] < $line[4]) {
        $tmp = $line[3];
        $line[3] = $line[4];
        $line[4] = $tmp;
    }
    if (isset($pointMap[$line[0]])) {
        $line[3] = $pointMap[$line[0]][0];
        $line[4] = $pointMap[$line[0]][1];
    }
    $full[$line[0]] = $line;
}
ksort($full);

$fc = [
    'type' => 'FeatureCollection',
    'features' => [],
];
$oFh = fopen($fullFile, 'w');
foreach ($full as $line) {
    fputcsv($oFh, $line);
    $f = [
        'type' => 'Feature',
        'properties' => [
            'id' => $line[0],
            'name' => $line[1],
            'phone' => $line[5],
            'address' => $line[2],
            'brand' => $line[6],
            'count' => intval($line[7]),
            'note' => isset($line[9]) ? $line[9] : '',
            'updated' => isset($line[8]) ? $line[8] : '',
            'service_periods' => isset($pool[$line[0]]) ? $pool[$line[0]] : '',
        ],
        'geometry' => [
            'type' => 'Point',
            'coordinates' => [
                floatval($line[3]),
                floatval($line[4]),
            ],
        ],
    ];
    $fc['features'][] = $f;
}

file_put_contents($targetFile, json_encode($fc, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
