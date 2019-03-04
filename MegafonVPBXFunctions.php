<?php

  // Интеграция CRM Clientbase с Виртуальной АТС Мегафон
  // https://ClientbasePro.ru
  // https://www.megapbx.ru/rest_api
  
require_once 'common.php'; 


  // функция возвращает массив абонентов ВАТС по условиям поиска $search
  // $search = ['name', 'realName', 'ext', 'telnum', 'email']
function GetMegafonVPBXUsers($search) {
    // параметры запроса
  $params['cmd'] = 'accounts';
  $params['token'] = MEGAFONVPBX_VPBXKEY;
    // запрос
  $curl = curl_init(MEGAFONVPBX_URL);
  curl_setopt_array($curl, array(
    CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($params)
  ));
  if ($response=curl_exec($curl)) {
	if ($answer=json_decode($response,true)) { 
	  curl_close($curl); 
	    // если нет условий поиска, возвращаем полный массив всех
	  if (!$search) return $answer;
	  else {
		foreach ($answer as $index=>$abonent) foreach ($search as $key=>$value) if ($value!=$abonent[$key]) { unset($answer[$index]); break; }
		return $answer;  
	  }
	}
  }
  curl_close($curl);
  return false;
}


  // функция инициирует исходящий звонок от $from (первое плечо коллбэка, идентификатор сотрудника на Мегафон ВАТС) на $to (второе плечо коллбэка, номер клиента)
  // возвращает CallId
function MegafonVPBXCallback($to='', $from='') {
    // проверка входных данных
  $to = SetNumber($to);
  if (!$to || !$from) return false;
    // параметры запроса
  $params['cmd'] = 'makeCall';
  $params['phone'] = $to;
  $params['user'] = $from;
  $params['token'] = MEGAFONVPBX_VPBXKEY;
    // запрос
  $curl = curl_init(MEGAFONVPBX_URL);
  curl_setopt_array($curl, array(
    CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($params)
  ));
  if ($response=curl_exec($curl)) if ($answer=json_decode($response)) { curl_close($curl); return $answer->uuid; }
  curl_close($curl);
  return false;
}


?>