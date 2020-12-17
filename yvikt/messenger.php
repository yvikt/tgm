<?php


# Обрабатка сообщения
# принимат сообщение и текущую клавиатуру
# обрабатывает сообщение и "сессию"
# отправляет ответ и [новую] клавиатуру
# •start•
function messenger($text, &$session){
global $commands;
    switch ($text) {
      // обработка управляющих команд
      case in_array($text, $commands) :
        return command_handler($text, $session);
      // обработка других команд
      default :
        return default_handler($text, $session);
    }
}











