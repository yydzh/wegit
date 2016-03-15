
<?php

include('wechat.class.php');
include('db.class.php');
include('db_config.php');
//define("FIXURL", 'http://yydzhweixin.sinaapp.com/');



  $options = array(
 			'token'=>'yydzh', //填写你设定的key
 			'appid'=>'wxedd6008ca1bc57aa', //填写高级调用功能的app id
 			'appsecret'=>'b6a181f93030a22346b2bf9d4490fb8b', //填写高级调用功能的密钥
 			'partnerid'=>'88888888', //财付通商户身份标识
			'partnerkey'=>'', //财付通商户权限密钥Key
			'paysignkey'=>'' //商户签名密钥Key
		);
	 $weObj = new Wechat($options);
     $weObj->valid();
     
      
    
   
     $user_id = $weObj->getRev()->getRevFrom();
     $type = $weObj->getRev()->getRevType();
     
//$mmc=memcache_init();
//memcache_set($mmc,"session",$user_id);

//$a= memcache_get($mmc,"session"); 

//session_id($user_id);
//session_start();
 
     
     //订阅触发回复消息和菜单的权限
   if ($type==Wechat::MSGTYPE_EVENT){
   		$rev_event = $weObj->getRevEvent();		
		if($rev_event[event]=="subscribe"){
             $weObj->text("欢迎来关注CANDY的微信公众平台！\n如要注册请回复R")->reply();   
              
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
  		 	  //$user_id = trim($weObj->getRevFrom());
  		 	   $time =time();
  		       $db_uri=	new db("user_registration_info");
  		      // $weObj->text("user_registration_info")->reply();
  		       $regArr=$db_uri->where("reg_phone={$rev_content}")->select();
  		       if (empty($regArr)){
  		       $db_uri->insert(array("reg_phone"=>$rev_content,"weixin_id"=>$user_id,"update_date"=>$time));
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
   
     
   
//header('Location: courseCenter.php');
   
  // $url = FIXURL."show_user_info.php";
     
   //设置菜单
  /* $newmenu =  array(
   		"button"=>
   			array(
   				array('type'=>'view','name'=>'用户清单','url'=>'http://yydzhweixin.sinaapp.com/show_user_info.php'),
   				array('type'=>'click','name'=>'销售','key'=>'MENU_KEY_SALES'),
   				)
  		);
    $result = $weObj->createMenu($newmenu);
    //获取菜单操作:
    $menu = $weObj->getMenu();
   */
   
   
   
   
   

   
   
  /* switch($type) {
  		case Wechat::MSGTYPE_TEXT:
   			$weObj->text("hello, I'm wechat")->reply();
  			exit;
  			break;
  		default:
   			$weObj->text("help info")->reply();
   }
   */
   