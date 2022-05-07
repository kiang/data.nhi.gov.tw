<?php
require __DIR__ . '/vendor/autoload.php';
$json = json_decode(file_get_contents('/home/kiang/public_html/taiwan_basecode/city/city.geo.json'), true);
$pool = [];
$count = [];
foreach ($json['features'] as $f) {
    $key = $f['properties']['COUNTYNAME'] . $f['properties']['TOWNNAME'];
    $pool[$key] = geoPHP::load(json_encode($f['geometry']), 'json');
    $count[$key] = [
        'count' => 0,
        'population' => 0,
    ];
}
ksort($count);
$fh = fopen('/home/kiang/public_html/tw_population/cunli/2021/03.csv', 'r');
while ($line = fgetcsv($fh, 2048)) {
    if (isset($count[$line[2]])) {
        $count[$line[2]]['population'] += $line[5];
    } elseif (mb_substr($line[2], 0, 5, 'utf-8') === '苗栗縣頭份') {
        $count['苗栗縣頭份市']['population'] += $line[5];
    }
}

$json = json_decode(file_get_contents(dirname(__DIR__) . '/docs/antigen.json'), true);
foreach ($json['features'] as $f) {
    $point = geoPHP::load(json_encode($f['geometry']), 'json');
    $containFound = false;
    foreach ($pool as $key => $city) {
        if (false === $containFound && $city->contains($point)) {
            $containFound = $key;
            $count[$key]['count'] += 1;
        }
    }
}

$oFh = fopen(dirname(__DIR__) . '/report/city.csv', 'w');
fputcsv($oFh, ['city', 'population', 'count', 'rate']);
foreach ($count as $k => $v) {
    $rate = 0.0;
    if ($v['count'] > 0) {
        $rate = round($v['population'] / ($v['count'] * 78 * 5), 2);
    }
    fputcsv($oFh, [$k, $v['population'], $v['count'], $rate]);
}
