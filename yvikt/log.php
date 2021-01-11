<?php

function mylog($message)
{
  $f = fopen('logs/bot.log', 'a');
  fputs($f, $message);
  fclose($f);
}