<?php

$expert_dir = 'expert/';
$observer_dir = 'observer/';

### expert ###

function check_expert($user_id){
  global $expert_dir;
  $expert_file = $expert_dir . $user_id;
  return file_exists($expert_file);
}

function any_expert(){
  global $expert_dir;
  return scandir($expert_dir)[2];
}

function be_expert($user_id){
  global $expert_dir;
  $expert_file = $expert_dir . $user_id;
  if (!file_exists($expert_file)) {
    $f = fopen($expert_file, 'a+');
    fputs($f, $user_id);
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

### observer ###

function check_observer($user_id){
  global $observer_dir;
  $observer_file = $observer_dir . $user_id;
  return file_exists($observer_file);
}

function any_observer(){
  global $observer_dir;
  return scandir($observer_dir)[2];
}

function be_observer($user_id){
  global $observer_dir;
  $observer_file = $observer_dir . $user_id;
  if (!file_exists($observer_file)) {
    $f = fopen($observer_file, 'a+');
    fputs($f, $user_id);
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


