<?php


//header("Content-type: text/html; charset=utf-8");


//session_start();

//echo session_id();
//echo memcache_get($mmc,"session"); 




include('wechat.class.php');
include('db.class.php');
include('db_config.php');



 $options = array(
 			'token'=>'yydzh', //��д���趨��key
 			'appid'=>'wxedd6008ca1bc57aa', //��д�߼����ù��ܵ�app id
 			'appsecret'=>'b6a181f93030a22346b2bf9d4490fb8b', //��д�߼����ù��ܵ���Կ
 			'partnerid'=>'88888888', //�Ƹ�ͨ�̻���ݱ�ʶ
			'partnerkey'=>'', //�Ƹ�ͨ�̻�Ȩ����ԿKey
			'paysignkey'=>'' //�̻�ǩ����ԿKey
		);
	 $weObj = new Wechat($options);
     $user_info=$weObj->getOauthAccessToken() ;
	$user_id= $user_info['openid'];


//$sql = "$sql = select user_name,weixin_id,status from user,user_registration_info where user_phone=reg_phone";

 $db_uri= new db("user_registration_info");

 $regArr=$db_uri->where("weixin_id={$user_id}")->select();
  			if (empty( $regArr)){
  				echo "�㻹ûע��,��ظ�R����ע��" ;
  			
            }else{
                     echo "�����ʹ�ô�ϵͳ!";
            	}


     
      