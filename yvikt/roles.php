<?php

$expert_dir = 'communication/experts/';
$observer_dir = 'communication/observers/';

### expert ###


function be_expert($user_id){
  global $expert_dir;
  $expert_file = $expert_dir . $user_id;
  if (!file_exists($expert_file)) {
    $f = fopen($expert_file, 'w');
    fputs($f, '0'); // эксперт свободен при инициализации - файл содержит 0
    fclose($f);
  }
}

function del_expert($user_id){
  global $expert_dir;
  $expert_file = $expert_dir . $user_id;
  if (file_exists($expert_file)){
    unlink($expert_file);
  }
}

// очередь бедет выглядеть в виде файлов /communications/users/user_id с нулем внутри
function any_expert(){
  global $expert_dir;
  $list = '';
  $files = array_slice(scandir($expert_dir), 2);
  foreach ($files as $file){
    if(file_get_contents("{$expert_dir}{$file}") == '0'){
      return $file; // возвращает первого свободного эксперта (по алфавиту - без учета равномерной нагрузки между экспертами)
    }
  }
}

### observer ###

function be_observer($user_id){
  global $observer_dir;
  $observer_file = $observer_dir . $user_id;
  if (!file_exists($observer_file)) {
    $f = fopen($observer_file, 'w');
    fputs($f, $user_id); // просто так
    fclose($f);
  }
}

function del_observer($user_id){
  global $observer_dir;
  $observer_file = $observer_dir . $user_id;
  if (file_exists($observer_file)){
    unlink($observer_file);
  }
}

function any_observer(){
  global $observer_dir;
  return array_slice(scandir($observer_dir), 2); // возвращает массив наблюдателей
}
