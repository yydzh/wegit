<?php
include('wechat.class.php');


//define("FIXURL", 'http://yydzhweixin.sinaapp.com/');
$options = array(
    'token' => 'yydzh', //填写你设定的key
    'appid' => 'wxedd6008ca1bc57aa', //填写高级调用功能的app id
    'appsecret' => 'b6a181f93030a22346b2bf9d4490fb8b', //填写高级调用功能的密钥
);
$weObj = new Wechat($options);
  $weObj->valid();
$user_id = $weObj->getRev()->getRevFrom();
 $type = $weObj->getRev()->getRevType();

   
  // $url = FIXURL."show_user_info.php";

$callback=  'http://yydzhweixin.sinaapp.com/show_user_info.php' ;
   
   //设置菜单
     $newmenu =  array(
   		"button"=>
   			array(
                array('type'=>'view','name'=>'用户清单','url'=>$weObj->getOauthRedirect($callback,$state='',$scope='snsapi_userinfo')),
   				array('type'=>'view','name'=>'销售','url'=>'http://yydzhweixin.sinaapp.com/show_user_info.php'),
   				array('type'=>'view','name'=>'百度','url'=>'http://www.baidu.com'),
   				)
  		);
   $result = $weObj->createMenu($newmenu);

//获取菜单操作:
    $menu = $weObj->getMenu();



 if ($type==Wechat::MSGTYPE_EVENT){
   		$rev_event = $weObj->getRevEvent();		
		if($rev_event[event]=="subscribe"){
  		$weObj->text(getOauthRedirect($callback,$state='',$scope='snsapi_userinfo'))->reply();   
  		}
 }