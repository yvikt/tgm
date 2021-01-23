<?php

// общение между пользователем и экспертом
function user_user(&$incoming_data, &$session){ // TODO проставить амперсанды
  global $commands;

  $id = $incoming_data['message']['from']['id']; // ?? отправлять наблюдателю chat_id вместо id

  $chat_id = $incoming_data['message']['chat']['id']; // меняется в зависимости чье сообщение - юзер/эксперт
  $incoming_text_message = &$incoming_data['message']['text'];


  switch ($session[2]){ // коммутация "пользователь->эксперт" - "эксперт->пользователь"

    // ПОЛЬЗОВАТЕЛЬ -> ЭКСПЕРТ \\
    case 0: // это пользователь (отправка сообщения от пользователя к эксперту)
        $expert_id = get_my_expert($chat_id); // пользователь отправляет в id експерта
        $first_name = $incoming_data['message']['from']['first_name'];
        $last_name = $incoming_data['message']['from']['last_name'];
        $username = $incoming_data['message']['from']['username'];

        // прекращение общения пользователем \\
        if($incoming_text_message == $commands[121]){ // прекращение общения
          disconnect($chat_id, $expert_id); // юзер-id, эксперт-id

          // TODO сделать единую точку вЫхода
          $session[5] = 0;
          $session[4] = 10;
          $session[0] = $incoming_data['message']['date'];
          $session[6] = $incoming_text_message;
          session_update($session);  // ОТПРАВКА (юзеру в случае выхода)

          $message_to_user = "Вы завершили общение\nГлавное меню";
          $outgoing_data_to_user = [
              'chat_id' => $chat_id,
              'text' => $message_to_user,
              'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(10)]
          ];
          chat_log($chat_id, "$message_to_user\n");
          sendToTelegram($outgoing_data_to_user);

          $message_to_expert = "пользователь $chat_id\n$first_name $last_name $username\nпокинул чат";
          $outgoing_data_to_expert = [
              'chat_id' => $expert_id,
              'text' => $message_to_expert,
              'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(5)]
          ];
          chat_log($chat_id, "$message_to_expert\n"); // пишет в свой же (пользователя) лог
          sendToTelegram($outgoing_data_to_expert);
        }

        else { // общение продолжается
          $message_to_expert = "🙋🙋‍♂️ $first_name $last_name ($username)\n$incoming_text_message";// вопрос пользователя
          $outgoing_data_to_expert = [
              'chat_id' => $expert_id,
              'text' => $message_to_expert,
              'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(15)]
          ];
          chat_log($chat_id, "$message_to_expert\n"); // пишет в свой же (пользователя) лог
          // ОТПРАВКА (эксперту)
          sendToTelegram($outgoing_data_to_expert);
        }
        exit;



    // ЭКСПЕРТ -> ПОЛЬЗОВАТЕЛЬ \\
    case 1: // это эксперт - (отправка сообщения от эксперта к пользователю)
          if(get_my_user($chat_id) == '0'){ // нет подключенных пользователей
            file_put_contents('logs/user-user_in.log', "incoming_text_message: $incoming_text_message; chat_id: $chat_id \n");
            $outgoing_data = command_handler_1($incoming_text_message, $chat_id, $session);//TODO это единственная точка выхода
            $outgoing_data['chat_id'] = $chat_id;
            return $outgoing_data;
          }
          else $user_id = get_my_user($chat_id);

        // прекращение общения экспертом \\
        if($incoming_text_message == $commands[121]){
          disconnect($user_id, $chat_id); // юзер-id, эксперт-id

          $message_to_expert = "вы отключились от пользователя\n$user_id";
          $outgoing_data_to_expert = [
              'chat_id' => $chat_id,
              'text' => $message_to_expert,
              'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(5)]
          ];
          chat_log($chat_id, "$message_to_expert\n"); // пишет в свой же (пользователя) лог
          sendToTelegram($outgoing_data_to_expert);

          $user_session = session_get($user_id);
          $user_session[5] = 0;
          $user_session[4] = 10;
          session_update($user_session);
          $message_to_user = "Эксперт покинул чат\nГлавное меню";
          $outgoing_data_to_user = [
              'chat_id' => $user_id,
              'text' => $message_to_user,
              'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(10)]
          ];
          chat_log($chat_id, "$message_to_user\n");
          sendToTelegram($outgoing_data_to_user);
        }

        // BAN \\
        elseif($incoming_text_message == $commands[6]){
          add_to_ban($user_id);
          disconnect($user_id, $chat_id); // юзер-id, эксперт-id

          $message_to_expert = "пользователь $user_id забанен";
          $outgoing_data_to_expert = [
              'chat_id' => $chat_id,
              'text' => $message_to_expert,
              'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(5)]
          ];
          chat_log($chat_id, "$message_to_expert\n");
          sendToTelegram($outgoing_data_to_expert);

          $user_session = session_get($user_id);
          $user_session[5] = 0;
          $user_session[4] = 'BAN';
          session_update($user_session);
          $message_to_user = "Вас заблокировали!";
          $outgoing_data_to_user = [
              'chat_id' => $user_id,
              'text' => $message_to_user,
              'reply_markup' => [ 'remove_keyboard' => true ]
          ];
          chat_log($chat_id, "$message_to_user\n");
          sendToTelegram($outgoing_data_to_user);
        }

        // общение продолжается \\
        else {
          $message_to_user = "🦊 $incoming_text_message"; // ответ эксперта
          $outgoing_data_to_user = [
              'chat_id' => $user_id,
              'text' => $message_to_user
          ];
          chat_log($user_id, "$message_to_user\n"); // пишет в пользователя лог
          sendToTelegram($outgoing_data_to_user);
        }

        exit;
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