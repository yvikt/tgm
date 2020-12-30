<?php

// общение между пользователем и экспертом
function user_user(&$incoming_data, &$session){ // TODO проставить амперсанды
  global $commands;

  $id = $incoming_data['message']['from']['id']; // ?? отправлять наблюдателю chat_id вместо id

  $chat_id = $incoming_data['message']['chat']['id']; // меняется в зависимости чье сообщение - юзер/эксперт
  $incoming_text_message = &$incoming_data['message']['text'];


  switch ($session[2]){ // коммутация "пользователь->эксперт" - "эксперт->пользователь"

    case 0: // это пользователь (отправка сообщения от пользователя к эксперту)
        $expert_id = get_my_expert($chat_id); // пользователь отправляет в id експерта
        $first_name = $incoming_data['message']['from']['first_name'];
        $last_name = $incoming_data['message']['from']['last_name'];
        $username = $incoming_data['message']['from']['username'];

        if($incoming_text_message == $commands[121]){ // прекращение общения
          $message = "$first_name $last_name $username (пользователь) покинул чат";
          disconnect($chat_id, $expert_id); // юзер-id, эксперт-id
          // TODO сделать единую точку вЫхода
          $session[5] = 0;
          $session[4] = 10;
          $session[0] = $incoming_data['message']['date'];
          $session[6] = $incoming_text_message;
          session_update($session);  // ОТПРАВКА (юзеру в случае выхода)

          $outgoing_data = [
              'text' => 'Главное меню',
              'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(10)]
          ];
          $outgoing_data['chat_id'] = $chat_id;
          sendToTelegram($outgoing_data);
        }
        else { // общение продолжается
          $message = "$first_name $last_name $username спрашивает:  $incoming_text_message";//TODO заменить на "Вы: "
          $keyboard =  ['reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(12)] ];
        }
        chat_log($chat_id, "$message\n"); // пишет в свой же (пользователя) лог
        // ОТПРАВКА (эксперту)
        fopen(BASE_URL . "sendMessage?chat_id={$expert_id}&parse_mode=html&text={$message}", "r");
        exit;
        break;

    case 1: // это эксперт - (отправка сообщения от эксперта к пользователю)
          if(get_my_user($chat_id) == '0'){ // нет подключенных пользователей
            file_put_contents('logs/user-user_in.log', "incoming_text_message: $incoming_text_message; chat_id: $chat_id \n");
            $outgoing_data = command_handler_1($incoming_text_message, $chat_id, $session);
            $outgoing_data['chat_id'] = $chat_id;
            return $outgoing_data;
          }
          else $user_id = get_my_user($chat_id);

        if($incoming_text_message == $commands[121]){ // прекращение общения
          $message = '(эксперт) покинул чат';
          disconnect($user_id, $chat_id); // юзер-id, эксперт-id
        }
        else { // общение продолжается
          $message = "эксперт отвечает: $incoming_text_message";
        }
        chat_log($user_id, "$message\n"); // пишет в пользователя лог
        fopen(BASE_URL . "sendMessage?chat_id={$user_id}&parse_mode=html&text={$message}", "r");
        exit;
        break;
  }




/*
// дублирование сообщений пользователей наблюдателю
  if($observer_id = any_observer()) {
    if ($id != $observer_id) { // только не самому себе
      $first_name = $incoming_data['message']['from']['first_name'];
      $last_name = $incoming_data['message']['from']['last_name'];
      $username = $incoming_data['message']['from']['username'];
      $message = $first_name . ' ' . $last_name . ' (' . $username . ') ' . 'нажал: "' . $incoming_text_message . '"';
      fopen(BASE_URL . "sendMessage?chat_id={$observer_id}&parse_mode=html&text={$message}", "r");
    }
  }
  */
}