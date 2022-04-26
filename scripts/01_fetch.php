<?php
require __DIR__ . '/vendor/autoload.php';

use Goutte\Client;

$client = new Client();
/**
 * 健保特約機構防疫家用快篩剩餘數量明細
 * https://data.nhi.gov.tw/Datasets/DatasetDetail.aspx?id=698
 */
$client->request('GET', 'https://data.nhi.gov.tw/resource/Nhi_Fst/Fstdata.csv');
$rawFile = dirname(__DIR__) . '/raw/A21030000I-D03001-001.csv';
file_put_contents($rawFile, $client->getResponse()->getContent());