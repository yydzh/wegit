<?php


//header("Content-type: text/html; charset=utf-8");


//session_start();

//echo session_id();
//echo memcache_get($mmc,"session"); 




include('wechat.class.php');
include('db.class.php');
include('db_config.php');



 $options = array(
 			'token'=>'yydzh', //填写你设定的key
 			'appid'=>'wxedd6008ca1bc57aa', //填写高级调用功能的app id
 			'appsecret'=>'b6a181f93030a22346b2bf9d4490fb8b', //填写高级调用功能的密钥
 			'partnerid'=>'88888888', //财付通商户身份标识
			'partnerkey'=>'', //财付通商户权限密钥Key
			'paysignkey'=>'' //商户签名密钥Key
		);
	 $weObj = new Wechat($options);
     $user_info=$weObj->getOauthAccessToken() ;
	$user_id= $user_info['openid'];


//$sql = "$sql = select user_name,weixin_id,status from user,user_registration_info where user_phone=reg_phone";

 $db_uri= new db("user_registration_info");

 $regArr=$db_uri->where("weixin_id={$user_id}")->select();
  			if (empty( $regArr)){
  				echo "你还没注册,请回复R进行注册" ;
  			
            }else{
                     echo "你可以使用此系统!";
            	}


     
      