<?php

# Обрабатка сообщения user-bot
# обрабатывает сообщение и "сессию"
# отправляет ответ и [новую] клавиатуру

function user_bot(&$incoming_data, &$session){
  global $commands;
  $text = $incoming_data['message']['text'] ?? '_no_text_';// (возможно это контакт)
  $chat_id = $incoming_data['message']['chat']['id'];

  switch ($text) {

      // обработка текста (входящего сообщения) если он входит в список управляющих команд
      case in_array($text, $commands) :

        // в зависимости от команды, переключает состояние и клавиатуру
        $outgoing_data = command_handler_0($text, $chat_id, $session);
        $outgoing_data['chat_id'] = $chat_id;
        return $outgoing_data;

      // обработка текста (входящего сообщения) если он не подпадает под команду
      default :
        $outgoing_data =  default_handler_0($text, $session);
        $outgoing_data['chat_id'] = $chat_id;
        return $outgoing_data;
    }

}
