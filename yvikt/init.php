<?php

include_once '../secrets.php';

$dirs = [
    'communication',
    'communication/chat_archive/',
    'communication/experts/',
    'communication/users/',
    'communication/que/',
    'logs',
    'quiz',
    'sessions'
];
foreach ($dirs as $dir) { // создание необходимых директорий
  if (!is_dir($dir)) {
    mkdir($dir);
    echo "directory $dir was successively created\n";
  }
  else echo "directory $dir is already presents\n";
}


$url = WEBHOOK_URL;
$token = TOKEN;

// установка вебхука
$response = file_get_contents("https://api.telegram.org/bot$token/setwebhook?url=$url");
$result = json_decode($response, true);

if($result['ok'] == 1){
  echo "{\n{$result['description']}\n\n";
}
echo "run this command: chown -R www-data:www-data ./\n";
//echo "put this in your browser\nhttps://api.telegram.org/bot$token/setwebhook?url=$url\n";