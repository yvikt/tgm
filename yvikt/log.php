<?php

function bot_logger($message)
{
  $f = fopen('logs/bot.log', 'a');
  fputs($f, $message);
  fclose($f);
}