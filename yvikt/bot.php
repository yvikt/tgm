<?php
include '../secrets.php';
include "nav.php";
include "messenger.php";
include "session.php";
include "handlers.php";
include "roles.php";
include "chatting.php";

const BASE_URL = 'https://api.telegram.org/bot' . TOKEN . '/';

# Принимаем запрос
$raw_data = file_get_contents('php://input');
file_put_contents('request.log', $raw_data ."\n\n", FILE_APPEND);
$receive_data = json_decode($raw_data, TRUE);

# запись последних пришедших данных пришедших боту
file_put_contents('req_logs/bot_in.log', '$data: '.print_r($receive_data, 1)."\n");

// умеем обрабатывать только message
if (!array_key_exists('message', $receive_data)){
  exit;
}

$date = $receive_data['message']['date'];
$id = $receive_data['message']['from']['id'];
$chat_id = $receive_data['message']['chat']['id'];
$incoming_text_message = $receive_data['message']['text'];

### MAIN ###

if (!session_check($chat_id)){
  session_init($receive_data);
  $send_data = greeting(keyboard(1));
  $send_data['chat_id'] = $chat_id;
  sendToTelegram($send_data);
  exit;
}

$session = session_reed($chat_id);

# MESSENGER обработка сообщения с учетом сессии
$send_data = messenger($incoming_text_message, $session);
# затем incoming_text_message идет дальше

# здесь обновляем сессию (можно также обновлять имя юзера)
$session[0] = $date;
$session[6] = $incoming_text_message;

session_update($session);

//$send_data = greeting(keyboard(11));

# отправка ответа обратно в чат
$send_data['chat_id'] = $chat_id;
$res = sendToTelegram($send_data);
# запись последних отправленных ботом данных
file_put_contents('req_logs/bot_out.log', '$data: '.print_r($send_data, 1)."\n");

# здесь нужно понимать что сообщение от пользователя было обработано,
# и в случае режима общения с ботом, пользователю отвечает бот,
# в случае общения с экспертом, сообщение ретранслируется эксперту
# или дублируется наблюдателю

// общение между пользователем и экспертом
if($session[5] == 1 || $incoming_text_message == 'завершить общение') { // если идет чат ("общительность" пользователя либо эксперта)
  // отправка сообщения от пользователя к эксперту
  if($session[2] == 0) { // это пользователь
    if ($expert_id = any_expert()) { // пользователь отправляет в id експерта
        $first_name = $receive_data['message']['from']['first_name'];
        $last_name = $receive_data['message']['from']['last_name'];
        $username = $receive_data['message']['from']['username'];
        if($incoming_text_message == 'завершить общение'){
          $incoming_text_message = 'пользователь покинул чат';
          $message = $first_name . ' ' . $last_name . ' (' . $username . ') ' . 'спрашивает: "' . $incoming_text_message . '"';
          fopen(BASE_URL . "sendMessage?chat_id={$expert_id}&parse_mode=html&text={$message}", "r");
        }
        else {
          $message = $first_name . ' ' . $last_name . ' (' . $username . ') ' . 'спрашивает: "' . $incoming_text_message . '"';
          chat_log($chat_id, "$message\n");
          fopen(BASE_URL . "sendMessage?chat_id={$expert_id}&parse_mode=html&text={$message}", "r");
        }
    }
  }
  // отправка сообщения от эксперта к пользователю
  if($session[2] == 1) { // это эксперт
    if ($chat = any_chat()) { // эксперт отправляет в chat пользователя, который был создан
                              // в обработчике handler_1 согласно chat_id пользователя с ботом
        $first_name = $receive_data['message']['from']['first_name'];
        $last_name = $receive_data['message']['from']['last_name'];
        $message = $first_name . ' ' . $last_name . ' ' . 'отвечает: "' . $incoming_text_message . '"';
        // лог чата
        chat_log($chat, "$message\n");
        fopen(BASE_URL . "sendMessage?chat_id={$chat}&parse_mode=html&text={$message}", "r");
    }
  }


}
// дублирование сообщений пользователей наблюдателю
if($observer_id = any_observer()) {
  if ($id != $observer_id) { // только не самому себе
    $first_name = $receive_data['message']['from']['first_name'];
    $last_name = $receive_data['message']['from']['last_name'];
    $username = $receive_data['message']['from']['username'];
    $message = $first_name . ' ' . $last_name . ' (' . $username . ') ' . 'нажал: "' . $incoming_text_message . '"';
    fopen(BASE_URL . "sendMessage?chat_id={$observer_id}&parse_mode=html&text={$message}", "r");
  }
}
 /*  */


function sendToTelegram($send_data, $method = 'sendMessage', $headers = [])
{
  $curl = curl_init();
  curl_setopt_array($curl, [
      CURLOPT_POST => 1,
      CURLOPT_HEADER => 0,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_URL => 'https://api.telegram.org/bot' . TOKEN . '/' . $method,
      CURLOPT_POSTFIELDS => json_encode($send_data),
      CURLOPT_HTTPHEADER => array_merge(array("Content-Type: application/json"), $headers)
  ]);

  $result = curl_exec($curl);
  curl_close($curl);
  return (json_decode($result, 1) ? json_decode($result, 1) : $result);
}