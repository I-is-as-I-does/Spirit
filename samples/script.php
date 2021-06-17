<?php
use SSITU\Jack\Jack;
use SSITU\Onement\Sod\Sod;
use SSITU\Spirit\Spirit;

require_once dirname(__DIR__, 3) . '/app/vendor/autoload.php';

// To generate a proper key:
# Jack::Token()->hexBytes(32);
// And a proper hash: 
# Jack::Admin()->hashAdminKey($key);

$key = '266aaf895e788903045e38956ca5b1e96bfc6ec8cd4b7c42be466d8f53fba765'; 

$spiritConfig = Jack::File()->readJson(__DIR__ . '/Spirit-config.json');
$Spirit = new Spirit($spiritConfig);

$sodConfig = [
    "adminKeyHashes" => $spiritConfig["adminKeyHashes"],
    "flavour" => "Sugar"]; # prefer Sodium if installed
$Sod = new Sod($sodConfig);
$Sod->setCryptKey($key);

$Spirit->setSod($Sod);
$Spirit->setTestMode($key);

$accessUrl = 'www.ssitu.com/door';
$parentNickname = 'Bob';
$nextNicknames = ['Barnett', 'Ida'];

$Spirit->printPassport($accessUrl, $parentNickname, $nextNicknames);
#var_dump($Spirit->getErrLog());
