<?php
require __DIR__ . "/vendor/autoload.php";

use Aws\DynamoDb\DynamoDbClient;

$db = new DynamoDbClient([
    'version' => 'latest',
    'region' => 'us-east-1', // Change this to your desired region
    'endpoint' => 'http://localhost:4566', // Change this to your DynamoDB endpoint
    'credentials' => [
        'key' => 'YOUR_ACCESS_KEY',
        'secret' => 'YOUR_SECRET_KEY',
    ]
]);
?>
