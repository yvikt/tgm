<?php
include '../secrets.php';
include "session.php";
include "roles.php";
include "nav.php";
include "user_bot.php";
include "user_user.php";
include "chat.php";
include "command_handler_0.php";
include "default_handler_0.php";
include "command_handler_1.php";


include "router.php";

const BASE_URL = 'https://api.telegram.org/bot' . TOKEN . '/';

# Принимаем запрос
$raw_data = file_get_contents('php://input');
file_put_contents('request.log', $raw_data ."\n", FILE_APPEND);
$incoming_data = json_decode($raw_data, TRUE);

# запись последних пришедших данных пришедших боту
file_put_contents('req_logs/bot_in.log', '$data: '.print_r($incoming_data, 1)."\n");

### MAIN ###
// обрабатывать только message или poll_answer
if (array_key_exists('message', $incoming_data) || array_key_exists('poll_answer', $incoming_data)){
  $date = $incoming_data['message']['date'];
  $id = $incoming_data['message']['from']['id'];
  $chat_id = $incoming_data['message']['chat']['id'];
  $incoming_text_message = $incoming_data['message']['text'];
  // для наглядности
  $outgoing_data = 'empty';
  $api_response = 'empty';

// для новых пользователей создаем сессию
if (!session_check($chat_id)){
  session_init($incoming_data);
  $outgoing_data = greeting(keyboard(1));
  $outgoing_data['chat_id'] = $chat_id;
  sendToTelegram($outgoing_data);
  exit;
}

$session = session_get($chat_id);

  switch ($session[5]){ // state routing (role routing выполняется внутри секций)

    case 0: // user <-> bot-menu
      $outgoing_data = user_bot($incoming_data, $session); break;

    case 1: // user <-> user(expert)
      $outgoing_data = user_user($incoming_data, $session); break; // TODO переработать ?

    case 2: // user <-> quiz-1

      $outgoing_data = user_bot($incoming_data, $session); break;

    case 3: // user <-> quiz-2
      $outgoing_data = user_bot($incoming_data, $session); break;
}
// TODO проверить работу наблюдателя

# здесь обновляем сессию (можно также обновлять имя юзера)
$session[0] = $date;
$session[6] = $incoming_text_message;

session_update($session);

# запись последних отправляемых ботом данных
//file_put_contents('req_logs/bot_out.log', '$data: ' . print_r($outgoing_data, 1) . "\n");
file_put_contents('req_logs/bot_out.log', json_encode($outgoing_data) . "\n", FILE_APPEND);

// TODO здесь quiz не будет работать ??
if($outgoing_data['text'] && $outgoing_data['chat_id']) { // проверка на пустые запросы
# отправка ответа
  $api_response = sendToTelegram($outgoing_data);
}
# запись ответа API
//file_put_contents('req_logs/api_response.log', '$data: ' . print_r($outgoing_data, 1) . "\n");
file_put_contents('req_logs/api_response.log', json_encode($api_response) . "\n", FILE_APPEND);

}
else exit;


function sendToTelegram($outgoing_data, $method = 'sendMessage', $headers = [])
{
  $curl = curl_init();
  curl_setopt_array($curl, [
      CURLOPT_POST => 1,
      CURLOPT_HEADER => 0,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_URL => 'https://api.telegram.org/bot' . TOKEN . '/' . $method,
      CURLOPT_POSTFIELDS => json_encode($outgoing_data),
      CURLOPT_HTTPHEADER => array_merge(array("Content-Type: application/json"), $headers)
  ]);

  $result = curl_exec($curl);
  curl_close($curl);
  return (json_decode($result, 1) ? json_decode($result, 1) : $result);
}