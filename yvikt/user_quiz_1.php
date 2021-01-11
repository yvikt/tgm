<?php

function user_quiz_1( &$incoming_data, &$session){
  global $commands;
  $text = $incoming_data['message']['text'];
  $chat_id = $incoming_data['message']['chat']['id'];

  $answer = $incoming_data['poll_answer']['option_ids'][0] ?? null;
  if(isset($answer)){ // обработка ответов
    $chat_id = $incoming_data['poll_answer']['user']['id'];
    $outgoing_data =  get_answer($answer, $session);
    $outgoing_data['chat_id'] = $chat_id;
    return $outgoing_data;
  }

  elseif ($text){ // обработка текста (входящего сообщения)

    switch ($text) {

      // обработка текстых команд 'начать' 'в другой раз' 'прекратить' 'отказаться' ////$commands[909],
      case in_array($text, [$commands[906], $commands[907], $commands[908], $commands[910]]) :
//      case in_array($text, [ 'начать', 'отказаться', 'прекратить' ]) :
        $outgoing_data = command_handler_2($text,$session);
        $outgoing_data['chat_id'] = $chat_id;
        return $outgoing_data;

      // обработка текста (входящего сообщения) если он не подпадает под команду
      default :
        $outgoing_data = ['text' => 'продолжайте выполнять тест или выберите необходимую команду'];
        $outgoing_data['chat_id'] = $chat_id;
        return $outgoing_data;
    }
  }

  elseif ($contact = $incoming_data['message']['contact']) { // если это контакт
          $session[4] = 909; // условно 909, так как эта комада не работает как текстовая команда, а как кнопка запроса контакта
          $session[5] = 0; // конец режима quiz - снова обычный юзер
          $user_contact = "first_name: {$contact['first_name']}\nlast_name: {$contact['last_name']}\nphone: {$contact['phone_number']}\n";

    if($expert_id = any_expert()) {
            // $user_id = $contact[3]; // он же равен $chat_id
            // TODO добавить инлайн кнопку для начала беседы с пользователем

            $outgoing_data['chat_id'] = $expert_id;
            $outgoing_data['text'] = "$user_contact"; // !!! важно - двойные кавычки нужны для правильной интерполяции символов &#x2705
            $outgoing_data['parse_mode'] = 'HTML';
            sendToTelegram($outgoing_data);
          }
          else{
            que_push($chat_id, $user_contact);
          }
          $outgoing_data = [
              'chat_id' => $chat_id,
              'text' => 'Спасибо. С вами свяжется наш эксперт.',
              'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(10)]
          ];
          return $outgoing_data;
  }


}