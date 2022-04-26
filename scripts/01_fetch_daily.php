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
 * https://data.nhi.gov.tw/Datasets/DatasetDetail.aspx?id=329
 */
$client->request('GET', 'https://data.nhi.gov.tw/DataSets/DataSetResource.ashx?rId=A21030000I-D21005-001');
$rawFile = dirname(__DIR__) . '/raw/A21030000I-D21005-001.csv';
file_put_contents($rawFile, $client->getResponse()->getContent());

/**
 * 全民健康保險特約院所固定服務時段
 * https://data.nhi.gov.tw/Datasets/DatasetDetail.aspx?id=441
 */
$client->request('GET', 'https://data.nhi.gov.tw/resource/Opendata/%E5%85%A8%E6%B0%91%E5%81%A5%E5%BA%B7%E4%BF%9D%E9%9A%AA%E7%89%B9%E7%B4%84%E9%99%A2%E6%89%80%E5%9B%BA%E5%AE%9A%E6%9C%8D%E5%8B%99%E6%99%82%E6%AE%B5.csv');
$rawFile = dirname(__DIR__) . '/raw/A21030000I-D21006-001.csv';
file_put_contents($rawFile, $client->getResponse()->getContent());