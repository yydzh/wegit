
<?php

include('wechat.class.php');
include('db.class.php');
include('db_config.php');
//define("FIXURL", 'http://yydzhweixin.sinaapp.com/');



  $options = array(
 			'token'=>'yydzh', //��д���趨��key
 			'appid'=>'wxedd6008ca1bc57aa', //��д�߼����ù��ܵ�app id
 			'appsecret'=>'b6a181f93030a22346b2bf9d4490fb8b', //��д�߼����ù��ܵ���Կ
 			'partnerid'=>'88888888', //�Ƹ�ͨ�̻���ݱ�ʶ
			'partnerkey'=>'', //�Ƹ�ͨ�̻�Ȩ����ԿKey
			'paysignkey'=>'' //�̻�ǩ����ԿKey
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
 
     
     //���Ĵ����ظ���Ϣ�Ͳ˵���Ȩ��
   if ($type==Wechat::MSGTYPE_EVENT){
   		$rev_event = $weObj->getRevEvent();		
		if($rev_event[event]=="subscribe"){
             $weObj->text("��ӭ����עCANDY��΢�Ź���ƽ̨��\n��Ҫע����ظ�R")->reply();   
              
  		}
  		 
   }
  
   // �û�ע�᣺
   if ($type==Wechat::MSGTYPE_TEXT ){
   		$rev_content = $weObj->getRevContent();		
		if(strtoupper($rev_content)=='R'){
			$weObj->text("�������ֻ���")->reply(); 		 		
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
  		       $weObj->text("���û������ע��")->reply();
  		       }else{
  		         $weObj->text("�û���ע��")->reply();
  		       }
  		 	}else {
  		 		 $weObj->text("�û���Ȩע���ϵͳ���������Ա��ϵ")->reply();
  		 	}
  		 	
   		}			
  			
  		
  		 
   }
   
     
   
//header('Location: courseCenter.php');
   
  // $url = FIXURL."show_user_info.php";
     
   //���ò˵�
  /* $newmenu =  array(
   		"button"=>
   			array(
   				array('type'=>'view','name'=>'�û��嵥','url'=>'http://yydzhweixin.sinaapp.com/show_user_info.php'),
   				array('type'=>'click','name'=>'����','key'=>'MENU_KEY_SALES'),
   				)
  		);
    $result = $weObj->createMenu($newmenu);
    //��ȡ�˵�����:
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
   