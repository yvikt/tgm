<?php
include '../secrets.php';
include "session.php";
include "roles.php";
include "nav.php";
include "user_bot.php";
include "user_user.php";
include "user_quiz_1.php";
include "chat.php";
include "handlers/texts.php";
include "handlers/command_handler_0.php";
include "handlers/default_handler_0.php";
include "handlers/command_handler_1.php";
include "handlers/command_handler_2.php";
include "handlers/quiz_handler_1.php";
include "log.php";

include "quiz.php";

const BASE_URL = 'https://api.telegram.org/bot' . TOKEN . '/';

# Принимаем запрос
$raw_data = file_get_contents('php://input');
//exit;
# запись последних пришедших данных пришедших боту
file_put_contents('logs/incoming_raw.log', $raw_data ."\n", FILE_APPEND);
# удаляем переносы строк (так как Телеграм почему-то присылает строку json с \n символами)
//file_put_contents('logs/incoming_json_string.log', str_replace("\n", '', $raw_data) ."\n", FILE_APPEND);
file_put_contents('logs/incoming_json_string.log', preg_replace('/[[:cntrl:]]/', '', $raw_data) ."\n", FILE_APPEND);

$incoming_data = json_decode($raw_data, TRUE);

### MAIN ###
// обрабатывать только message или poll_answer или callback_query
if (array_key_exists('message', $incoming_data) || array_key_exists('poll_answer', $incoming_data) || array_key_exists('callback_query', $incoming_data)) {
//    mylog("if 'message' || 'poll_answer'\n");
  if($incoming_data['message']){
      mylog("if 'message'\n");
    ### BAN ###
    if(is_banned($incoming_data['message']['from']['id']))//exit;
    {
      $outgoing_data = [
          'chat_id' => $incoming_data['message']['from']['id'],
          'text' => "вы заблокированы"
      ];
      sendToTelegram($outgoing_data);
      exit;
    }
    $date = $incoming_data['message']['date'];
    $chat_id = $incoming_data['message']['chat']['id'];
    $incoming_text_message = $incoming_data['message']['text'] ?? '_no_text_';// (возможно это контакт)
  }
  elseif($incoming_data['poll_answer']){ // эта часть нужна для корректной работы сессии
      mylog("if 'poll_answer'\n");
    $date = time();
    $chat_id = $incoming_data['poll_answer']['user']['id'];
    $incoming_text_message = "answer: {$incoming_data['poll_answer']['option_ids'][0]}";
//    $incoming_text_message = "answer";
  }

  elseif ($incoming_data['callback_query']){ // обработка callback-а инициирующего чат с пользователем
      mylog("if 'callback_query'\n");

      //TODO наладить связь с пользователем по нажатию inline кнопки
    if($incoming_data['callback_query']['data'] == 'begin_chat') {
      mylog("if 'begin chat'\n");
      $expert_id = $incoming_data['callback_query']['message']['chat']['id'];
      $user_id = intval( explode('\n', $incoming_data['callback_query']['message']['text'])[0] );

      create_chat($user_id, $expert_id);
      connect($user_id, $expert_id);

      $message_to_expert = "Вы подключились к пользователю $user_id";
      $outgoing_data_to_expert = [
          'chat_id' => $incoming_data['callback_query']['from']['id'],
          'text' => $message_to_expert,
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(15)]
      ];
      sendToTelegram($outgoing_data_to_expert);

      $user_session = session_get($user_id);// ищем юзера
      $user_session[5] = 1;  // быть "общительным" :-)
      $session[6] = 'begin_chat';
      session_update($user_session);

      $message_to_user = "К вам подключился эксперт.";
      $outgoing_data_to_user = [
          'chat_id' => $user_id,
          'text' => $message_to_user,
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(12)]
      ];
      sendToTelegram($outgoing_data_to_user);
      exit;
    }
  }


//   для наглядности
  $outgoing_data = 'empty';
  $api_response = 'empty';

// для новых пользователей создаем сессию
if (!session_check($chat_id)){
  session_init($incoming_data);
  $outgoing_data = greeting(keyboard(10));
  $outgoing_data['chat_id'] = $chat_id;
  $outgoing_data['parse_mode'] = 'HTML';
  sendToTelegram($outgoing_data);
  exit;
}

$session = session_get($chat_id);

  if($incoming_text_message != 'RESET') {//сброс сессии
    switch ($session[5]) { // state routing (role routing выполняется внутри секций)

      case 0: // user <-> bot-menu
        $outgoing_data = user_bot($incoming_data, $session);
        break;

      case 1: // user <-> user(expert)
        $outgoing_data = user_user($incoming_data, $session);
        break; // TODO переработать ?

      case 2: // user <-> quiz-1
      case 3: // user <-> quiz-2

        $outgoing_data = user_quiz_1($incoming_data, $session);
        break;

//    case 3: // user <-> quiz-2
//      $outgoing_data = user_quiz_2($incoming_data, $session); break;
    }
  }
  else{
    $session[2] = 0;
    $session[3] = $session[4];
    $session[4] = 0;
    $session[5] = 0;
    $outgoing_data = [
        'chat_id' => $chat_id,
        'text' => 'сессия сброшена',
        'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(10)]
    ];
  }
  // TODO проверить работу наблюдателя

  # здесь обновляем сессию (можно также обновлять имя юзера)
  $session[0] = $date;
  $session[6] = $incoming_text_message;

  // TODO в случае quiz режима, может происходить две отправки: 1-текстовое сообщение, 2-следующий poll
  if($outgoing_data['text'] && $outgoing_data['chat_id']) { // отправка текстовых сообщений
  # отправка ответа
    $outgoing_data['parse_mode'] = 'HTML';
    $api_response = sendToTelegram($outgoing_data);
    file_put_contents('logs/outgoing_json.log', json_encode($outgoing_data) . "\n", FILE_APPEND);
    file_put_contents('logs/telegram_api_response_json.log', json_encode($api_response) . "\n", FILE_APPEND);
    session_update($session);
  }

  if($session[4] == 0){// следующий вопрос для активного квиза
//    $session[6] = 'next question';
    $outgoing_data = next_poll($session);
    $api_response = sendToTelegram($outgoing_data, 'sendPoll'); // указываем метод sendPoll
    file_put_contents('logs/outgoing_json.log', json_encode($outgoing_data) . "\n", FILE_APPEND);
    file_put_contents('logs/telegram_api_response_json.log', json_encode($api_response) . "\n", FILE_APPEND);
    session_update($session);
  }

  if($session[4] == 906){// как только quiz начался (нажали кнопку 906 'начать'), отправляем первый вопрос
    $session[4] = 0; // временное решение - храним ноль пока идет quiz
//    $session[6] = 'begin quiz';
    $outgoing_data = next_poll($session);
    $api_response = sendToTelegram($outgoing_data, 'sendPoll'); // указываем метод sendPoll
    file_put_contents('logs/outgoing_json.log', json_encode($outgoing_data) . "\n", FILE_APPEND);
    file_put_contents('logs/telegram_api_response_json.log', json_encode($api_response) . "\n", FILE_APPEND);
    session_update($session);
    }


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


// TODO пояснения
/*
  # запись последних отправляемых ботом данных
  file_put_contents('logs/outgoing_json.log', json_encode($outgoing_data) . "\n", FILE_APPEND);
  # запись ответа API
  file_put_contents('logs/telegram_api_response_json.log', json_encode($api_response) . "\n", FILE_APPEND);
*/