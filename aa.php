<?php

include('wechat.class.php');
include('db.class.php');
include('db_config.php');
//define("FIXURL", 'http://yydzhweixin.sinaapp.com/');


//ֻ��
  $options = array(
 			'token'=>'yydzh', //��д���趨��key
      //'appid'=>'wx597cdd905084084e', //��д�߼����ù��ܵ�app id
      //	'appsecret'=>'15dc1382668532fe788aceb4fa02596b', //��д�߼����ù��ܵ���Կ
       'appid' => 'wxeca2f99bd7484d67', //��д�߼����ù��ܵ�app id
       'appsecret' => 'cb5a0c9e2e8b9b686f04a0fd4f56499e', //��д�߼����ù��ܵ���Կ
 			'partnerid'=>'88888888', //�Ƹ�ͨ�̻���ݱ�ʶ
			'partnerkey'=>'', //�Ƹ�ͨ�̻�Ȩ����ԿKey
			'paysignkey'=>'' //�̻�ǩ����ԿKey
		);
	 $weObj = new Wechat($options);
     $weObj->valid();
     
   
     $user_id = $weObj->getRev()->getRevFrom();
     $type = $weObj->getRev()->getRevType();
     
    
 
     
     //���Ĵ����ظ���Ϣ
   if ($type==Wechat::MSGTYPE_EVENT){
   		$rev_event = $weObj->getRevEvent();		
		if($rev_event['event']=="subscribe"){
  		$weObj->text("��ӭ����עCANDY��΢�Ź���ƽ̨��\n��Ҫע����ظ�R")->reply(); 
   
        }elseif($rev_event['event']=="CLICK" && $rev_event['key']=='MENU_KEY_SALES'){
            $db_uri= new db("user_registration_info");
            $regArr=$db_uri->where("weixin_id='{$user_id}'")->select();
  			if (empty( $regArr)){
  				echo "�㻹ûע��,��ظ�R����ע��" ;
        	 	
            }else {
                $db_uri= new db("sales_department_session");
                $db_uri->delete($user_id);
                $db_uri->insert(array("user_id"=>$user_id,"step"=>0));
            	$weObj->text("���������ѯ�ĵ��")->reply(); 
                
            }
  		  
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
  		 	  $user_id = trim($weObj->getRevFrom());
  		 	   $time =time();
  		       $db_uri=	new db("user_registration_info");
  		      // $weObj->text("user_registration_info")->reply();
  		       $regArr=$db_uri->where("reg_phone={$rev_content}")->select();
  		       if (empty($regArr)){
  		       $db_uri->insert(array("reg_phone"=>$rev_content,"weixin_id"=>$user_id,"update_date"=>$time,"reg_name"=>$resultArr[0]['user_name']));
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
   

     
  
   
  /* switch($type) {
  		case Wechat::MSGTYPE_TEXT:
   			$weObj->text("hello, I'm wechat")->reply();
  			exit;
  			break;
  		default:
   			$weObj->text("help info")->reply();
   }
   */
   

   
  