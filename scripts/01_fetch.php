<?php
require __DIR__ . '/vendor/autoload.php';
$basePath = dirname(__DIR__);

$targetFile = $basePath . '/docs/antigen.json';
$targetPool = [];
if (file_exists($targetFile)) {
    $json = json_decode(file_get_contents($targetFile), true);
    foreach ($json['features'] as $f) {
        $targetPool[$f['properties']['id']] = $f;
    }
}
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
$fc = [
    'type' => 'FeatureCollection',
    'features' => [],
];
$fh = fopen($rawFile, 'r');
fgetcsv($fh, 2048);
$check = [];

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
    $f = [
        'type' => 'Feature',
        'properties' => [
            'id' => $line[0],
            'name' => $line[1],
            'phone' => $line[5],
            'address' => $line[2],
            'brand' => $line[6],
            'count' => intval($line[7]),
            'note' => $line[9],
            'updated' => $line[8],
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
    if (isset($targetPool[$line[0]])) {
        unset($targetPool[$line[0]]);
    }
}
foreach ($targetPool as $f) {
    $f['properties']['count'] = 0; // force 0 if the record was removed
    $fc['features'][] = $f;
}

file_put_contents($targetFile, json_encode($fc, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
