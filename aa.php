<?php

include('wechat.class.php');
include('db.class.php');
include('db_config.php');
//define("FIXURL", 'http://yydzhweixin.sinaapp.com/');


//只做
  $options = array(
 			'token'=>'yydzh', //填写你设定的key
      //'appid'=>'wx597cdd905084084e', //填写高级调用功能的app id
      //	'appsecret'=>'15dc1382668532fe788aceb4fa02596b', //填写高级调用功能的密钥
       'appid' => 'wxeca2f99bd7484d67', //填写高级调用功能的app id
       'appsecret' => 'cb5a0c9e2e8b9b686f04a0fd4f56499e', //填写高级调用功能的密钥
 			'partnerid'=>'88888888', //财付通商户身份标识
			'partnerkey'=>'', //财付通商户权限密钥Key
			'paysignkey'=>'' //商户签名密钥Key
		);
	 $weObj = new Wechat($options);
     $weObj->valid();
     
   
     $user_id = $weObj->getRev()->getRevFrom();
     $type = $weObj->getRev()->getRevType();
     
    
 
     
     //订阅触发回复消息
   if ($type==Wechat::MSGTYPE_EVENT){
   		$rev_event = $weObj->getRevEvent();		
		if($rev_event['event']=="subscribe"){
  		$weObj->text("欢迎来关注CANDY的微信公众平台！\n如要注册请回复R")->reply(); 
   
        }elseif($rev_event['event']=="CLICK" && $rev_event['key']=='MENU_KEY_SALES'){
            $db_uri= new db("user_registration_info");
            $regArr=$db_uri->where("weixin_id='{$user_id}'")->select();
  			if (empty( $regArr)){
  				echo "你还没注册,请回复R进行注册" ;
        	 	
            }else {
                $db_uri= new db("sales_department_session");
                $db_uri->delete($user_id);
                $db_uri->insert(array("user_id"=>$user_id,"step"=>0));
            	$weObj->text("请输入想查询的店号")->reply(); 
                
            }
  		  
    }
   }
  
   // 用户注册：
   if ($type==Wechat::MSGTYPE_TEXT ){
   		$rev_content = $weObj->getRevContent();		
		if(strtoupper($rev_content)=='R'){
			$weObj->text("请输入手机号")->reply(); 		 		
  		}elseif(is_numeric($rev_content) && strlen($rev_content)==11){ 
  			
  		 	$db_user = new db("user"); 
  		 			 	
  		 	$resultArr=$db_user->where("user_phone={$rev_content}")->select();
            
  		 	if (!empty($resultArr)){
  		 		//$weObj->text($rev_content)->reply();
  		 	  $user_id = trim($weObj->getRevFrom());
  		 	   $time =time();
  		       $db_uri=	new db("user_registration_info");
  		      // $weObj->text("user_registration_info")->reply();
  		       $regArr=$db_uri->where("reg_phone={$rev_content}")->select();
  		       if (empty($regArr)){
  		       $db_uri->insert(array("reg_phone"=>$rev_content,"weixin_id"=>$user_id,"update_date"=>$time,"reg_name"=>$resultArr[0]['user_name']));
  		        session_id($user_id);
                session_start();
                   $weObj->text("新用户已完成注册")->reply();
                  
  		       }else{
  		         $weObj->text("用户已注册")->reply();
  		       }
  		 	}else {
  		 		 $weObj->text("用户无权注册该系统，请与管理员联系")->reply();
  		 	}
  		 	
   		}			
  			
  		
  		 
   }
   

     
  
   
  /* switch($type) {
  		case Wechat::MSGTYPE_TEXT:
   			$weObj->text("hello, I'm wechat")->reply();
  			exit;
  			break;
  		default:
   			$weObj->text("help info")->reply();
   }
   */
   

   
  