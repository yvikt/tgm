<?php

function user_quiz_1( &$incoming_data, &$session){
  global $commands;
  $text = $incoming_data['message']['text'];
  $chat_id = $incoming_data['message']['chat']['id'];
  $answer = $incoming_data['poll_answer']['option_ids'][0];
  if(!is_null($answer)){
    $chat_id = $incoming_data['poll_answer']['user']['id'];
  }

  if($text){
    // обработка текста (входящего сообщения) если он не подпадает под команду
    switch ($text) {

      // обработка текстых команд 'начать' 'отказаться' 'прекратить'
      case in_array($text, [$commands[6], $commands[7], $commands[8]]) :
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

   elseif(!is_null($answer)){// обработка ответов на вопросы
//     get_answer($chat_id, $answer);
     $outgoing_data =  get_answer($answer, $session);
     $outgoing_data['chat_id'] = $chat_id;
     return $outgoing_data;
  }


}