<?php
include('wechat.class.php');


//define("FIXURL", 'http://yydzhweixin.sinaapp.com/');
$options = array(
    'token' => 'yydzh', //��д���趨��key
    'appid' => 'wxedd6008ca1bc57aa', //��д�߼����ù��ܵ�app id
    'appsecret' => 'b6a181f93030a22346b2bf9d4490fb8b', //��д�߼����ù��ܵ���Կ
);
$weObj = new Wechat($options);
  $weObj->valid();
$user_id = $weObj->getRev()->getRevFrom();
 $type = $weObj->getRev()->getRevType();

   
  // $url = FIXURL."show_user_info.php";

$callback=  'http://yydzhweixin.sinaapp.com/show_user_info.php' ;
   
   //���ò˵�
     $newmenu =  array(
   		"button"=>
   			array(
                array('type'=>'view','name'=>'�û��嵥','url'=>$weObj->getOauthRedirect($callback,$state='',$scope='snsapi_userinfo')),
   				array('type'=>'view','name'=>'����','url'=>'http://yydzhweixin.sinaapp.com/show_user_info.php'),
   				array('type'=>'view','name'=>'�ٶ�','url'=>'http://www.baidu.com'),
   				)
  		);
   $result = $weObj->createMenu($newmenu);

//��ȡ�˵�����:
    $menu = $weObj->getMenu();



 if ($type==Wechat::MSGTYPE_EVENT){
   		$rev_event = $weObj->getRevEvent();		
		if($rev_event[event]=="subscribe"){
  		$weObj->text(getOauthRedirect($callback,$state='',$scope='snsapi_userinfo'))->reply();   
  		}
 }