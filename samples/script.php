<?php
use SSITU\Jack\Jack;
use SSITU\Sod\Sod;
use SSITU\Spirit\Spirit;

require_once dirname(__DIR__, 3) . '/app/vendor/autoload.php';

// To generate a key:
# Jack::Token()->hexBytes(32);
// And a hash: 
# Jack::Admin()->hashAdminKey($key);

$key = '266aaf895e788903045e38956ca5b1e96bfc6ec8cd4b7c42be466d8f53fba765'; 

$spiritConfig = Jack::File()->readJson(__DIR__ . '/Spirit.json');
$Spirit = new Spirit($spiritConfig);

$sodConfig = [
    "adminKeyHashes" => $spiritConfig["adminKeyHashes"],
    "flavour" => "Sugar"]; # prefer Sodium if installed
$Sod = new Sod($sodConfig);
$Sod->setCryptKey($key);

$Spirit->setSod($Sod);
$Spirit->setTestMode($key);

$accessUrl = 'www.ssitu.com/door';
$username = 'Bob';
$addtLines = ['guest of Ida'];

$secret = 'FseS3QeD3CD@s&XU0%Wv$O4^53vUT&9b';

echo '<h2>Print Passport</h2>';
$b64Img = $Spirit->printPassport($secret, $accessUrl, $username, $addtLines);
echo '<h2>Reading above Passport</h2>';
$Spirit->readPassport($b64Img, $secret, $accessUrl, $username);


echo '<h2>Previsouly saved Passport</h2>';
echo '<img src="./pass.png">';
echo '<h2>Reading said Passport File</h2>';
$b64Img2 = Jack::Images()->fileTob64(__DIR__.'/pass.png');
$Spirit->readPassport($b64Img2, $secret, $accessUrl, $username);

$logs = $Spirit->getLogs();
if(!empty($logs)){

    echo '<h2>Logs</h2>';
    echo json_encode($logs,JSON_PRETTY_PRINT);
}
