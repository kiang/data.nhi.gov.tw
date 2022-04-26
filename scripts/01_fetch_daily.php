<?php
require __DIR__ . '/vendor/autoload.php';

use Goutte\Client;

$client = new Client();
/**
 * 健保特約醫事機構-診所
 * https://data.nhi.gov.tw/Datasets/DatasetDetail.aspx?id=328
 */
$client->request('GET', 'https://data.nhi.gov.tw/DataSets/DataSetResource.ashx?rId=A21030000I-D21004-009');
$rawFile = dirname(__DIR__) . '/raw/A21030000I-D21004-009.csv';
file_put_contents($rawFile, $client->getResponse()->getContent());

/**
 * 健保特約醫事機構-藥局
 * https://data.nhi.gov.tw/Datasets/DatasetDetail.aspx?id=329&Mid=A111088
 */
$client->request('GET', 'https://data.nhi.gov.tw/DataSets/DataSetResource.ashx?rId=A21030000I-D21005-001');
$rawFile = dirname(__DIR__) . '/raw/A21030000I-D21005-001.csv';
file_put_contents($rawFile, $client->getResponse()->getContent());