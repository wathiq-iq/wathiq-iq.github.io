<?php

define('BOT_TOKEN', '268863964:AAE-E3auDsdlT63CwITWKAQU1p4fYtVRyys');
define('ADMIN', '249010980');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');
define('URLA', 'https://wathiq-iq.github.io');

function apiRequestWebhook($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  $parameters["method"] = $method;

  header("Content-Type: application/json");
  echo json_encode($parameters);
  return true;
}

function exec_curl_request($handle) {
  $response = curl_exec($handle);

  if ($response === false) {
    $errno = curl_errno($handle);
    $error = curl_error($handle);
    error_log("Curl returned error $errno: $error\n");
    curl_close($handle);
    return false;
  }

  $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
  curl_close($handle);

  if ($http_code >= 500) {
    // do not wat to DDOS server if something goes wrong
    sleep(10);
    return false;
  } else if ($http_code != 200) {
    $response = json_decode($response, true);
    error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
    if ($http_code == 401) {
 throw new Exception('Invalid access token provided');
    }
    return false;
  } else {
    $response = json_decode($response, true);
    if (isset($response['description'])) {
      error_log("Request was successfull: {$response['description']}\n");
    }
    $response = $response['result'];
  }

  return $response;
}

function apiRequest($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  foreach ($parameters as $key => &$val) {
    // encoding to JSON array parameters, for example reply_markup
    if (!is_numeric($val) && !is_string($val)) {
      $val = json_encode($val);
    }
  }
  $url = API_URL.$method.'?'.http_build_query($parameters);

  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($handle, CURLOPT_TIMEOUT, 60);

  return exec_curl_request($handle);
}

function apiRequestJson($method, $parameters) {
  if (!is_string($method)) {
 error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  $parameters["method"] = $method;

  $handle = curl_init(API_URL);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($handle, CURLOPT_TIMEOUT, 60);
  curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
  curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

  return exec_curl_request($handle);
}

function processMessage($message) {
  // process incoming message
  $message_id = $message['message_id'];
  $chat_id = $message['chat']['id'];
  $id = $message['from']['id'];
  $firstname = $message['from']['first_name'];
  $lastname = $message['from']['last_name'];
  $username = $message['from']['username'];
  if (isset($message['text'])) {
    // incoming text message
    $text = $message['text'];
    $admin = ADMIN;
    $matches = explode(' ', $text);
    $substr = substr($text, 0,7 );
    $U = "[$firstname](https://telegram.me/$username)" or '*'.$firstname.'*';
    if (strpos($text, "/start") === 0) {
        apiRequest("sendMessage", 
        array('chat_id' => $chat_id, 
        "text" => " اهلا بك ".$U."
في البوت الرسمي لئنشاء بوتات سمسم بكل سهولة

كل ما عليك هو ان تقوم بجلب التوكن الخاص بك من بوت فاذر
@botfather 
وارسالة الى البوت الخاص  @CRSIMBOT

`327180897:AAH-jDaJQd0XGRuGbvAIKmv8ZMS9wQLjsJE`

وهاكذا سوف يكون لديك بوت سمسم 
مجاني ويشتغل في الكروبات وفي الخاص 

                  .",
"parse_mode"=>"MARKDOWN",
"disable_web_page_preview"=>"true",
"reply_to_message_id"=>$message_id,
'reply_markup' => array(
'keyboard' => array(array('⚜ Version ⚜')),
'one_time_keyboard' => true,
'selective' => true,
'resize_keyboard' => true,
)));


$txxt = file_get_contents('members.txt');
$pmembersid= explode("\n",$txxt);
	if (!in_array($chat_id,$pmembersid)) {
		$aaddd = file_get_contents('members.txt');
		$aaddd .= $chat_id."\n";
    	file_put_contents('members.txt',$aaddd);
}
        if($chat_id == ADMIN)
        {
          if(!file_exists('tokens.txt')){
        file_put_contents('tokens.txt',"");
           }
        $tokens = file_get_contents('tokens.txt');
        $part = explode("\n",$tokens);
       $tcount =  count($part)-1;

      apiRequestWebhook("sendMessage", array('chat_id' => $chat_id,  "text" => "العدد الكلي لل بوتات المتصلة حاليا <code>".$tcount."</code>","parse_mode"=>"HTML"));

        }
    }else if ($text == "⚜ Version ⚜") {
      apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
<b>WathiqApi</b>
<b>اصدار. 1.0 BETA</b>
الخصائص : يشتغل بل كروبات + بل خاص
<code>Coded By</code> <a href='https://telegram.me/iluli'>Wathiq</a>
Copy Right 2016©
.","parse_mode"=>"html","disable_web_page_preview"=>true));
    }else if ($matches[0] == "/update" && strpos($matches[1], ":")) {
      
    $txtt = file_get_contents('tokenstoupdate.txt');
		$banid= explode("\n",$txtt);
		$id=$chat_id;
if (in_array($matches[1],$banid)) {
      rmdir($chat_id);
      mkdir($id, 0700);
      file_put_contents($id.'/pmembers.txt',"");
      file_put_contents($id.'/msgs.txt',"Hi 😃👋 
      Powered by @WathiqApi
");
        file_put_contents($id.'/booleans.txt',"false");
        $phptext = file_get_contents('phptext.txt');
        $phptext = str_replace("**TOKEN**",$matches[1],$phptext);
        $phptext = str_replace("**ADMIN**",$chat_id,$phptext);
        file_put_contents($id.'/wathiq.php',$phptext);
        file_get_contents('https://api.telegram.org/bot'.$matches[1].'$texttwebhook?url=');
        file_get_contents('https://api.telegram.org/bot'.$matches[1].'/setwebhook?url='.URLA.'/'.$chat_id.'/wathiq.php');
apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "لقد قمت بتديث البوت بنجاح 🚀 ♻️"));


    }
    }
    else if ($matches[0] != "/update"&& $matches[1]==""&&$chat_id != ADMIN) {
      if (strpos($text, ":")) {
apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "في انتظار 🔃"));
    $url = "http://api.telegram.org/bot".$matches[0]."/getme";
    $json = file_get_contents($url);
    $json_data = json_decode($json, true);
    $id = $chat_id;
    
   $txt = file_get_contents('lastmembers.txt');
    $membersid= explode("\n",$txt);
    
    if($json_data["result"]["username"]!=null){
      
      if(file_exists($id)==false && in_array($chat_id,$membersid)==false){
          

        $aaddd = file_get_contents('tokens.txt');
                $aaddd .= $text."\n";
        file_put_contents('tokens.txt',$aaddd);

     mkdir($id, 0700);
        file_put_contents($id.'/pmembers.txt',"");
        file_put_contents($id.'/booleans.txt',"false");
        file_put_contents($id.'/msgs.txt',"Hi 😃👋 
Powered by @WathiqApi
        ");
        $phptext = file_get_contents('phptext.txt');
        $phptext = str_replace("**TOKEN**",$text,$phptext);
        $phptext = str_replace("**ADMIN**",$chat_id,$phptext);
        file_put_contents($token.$id.'/wathiq.php',$phptext);
        file_get_contents('https://api.telegram.org/bot'.$text.'/setwebhook?url=');
        file_get_contents('https://api.telegram.org/bot'.$text.'/setwebhook?url='.URLA.'/'.$chat_id.'/wathiq.php');
    $unstalled = "تم تثبيت الروبوت الخاص بك بنجاح 🚀
اضغط للدخول الروبوت الخاص بك 👇😃
لإعطاء تصنيف لدينا الروبوت 👇

 [CRSIMBOT](https://telegram.me/CRSIMBOT?start=start) ، [WathiqApi](https://telegram.me/joinchat/DtebJD-Yica3fIVrDhOo4g)
.";
    
    $bot_url    = "https://api.telegram.org/bot".BOT_TOKEN."/"; 
    $url        = $bot_url . "sendMessage?chat_id=" . $chat_id ; 

$post_fields = array('chat_id'   => $chat_id, 
    'text'     => $unstalled, 
	'parse_mode' =>'MARKDOWN',
    'reply_markup'   => '{"inline_keyboard":[[{"text":'.'"@'.$json_data["result"]["username"].'"'.',"url":'.'"'."http://telegram.me/".$json_data["result"]["username"].'"'.'},{"text":"اشترك بل قناة","url":"https://telegram.me/joinchat/DtebJD-Yica3fIVrDhOo4g"}]]}' ,
    'disable_web_page_preview'=>"true"
); 

$ch = curl_init(); 
curl_setopt($ch, CURLOPT_HTTPHEADER, array( 
    "Content-Type:multipart/form-data" 
)); 
curl_setopt($ch, CURLOPT_URL, $url); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields); 

$output = curl_exec($ch); 
    
    
    



      }
      else{
         apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "👾 كنت قد سجلت بالفعل الروبوت والروبوت الثاني غير قادر على تسجيل.

🔹 للشخص الواحد = الروبوت 

🤖 إذا كنت ترغب في تقديم المزيد من الروبوتات لزيارة الروبوت الخاص بك.
🚀 @LAZUBOT"));
      }
    }
      
    else{
          apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "رمز غير صالح ❌"));
    }
}
else{
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "رمز غير صالح ❌"));

}

        }else if ($matches[0] != "/update"&&$matches[1] != ""&&$matches[2] != ""&&$chat_id == ADMIN) {
          
        if (strpos($text, ":")) {
          
          
apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "في انتظار 🔃"));
    $url = "http://api.telegram.org/bot".$matches[0]."/getme";
    $json = file_get_contents($url);
    $json_data = json_decode($json, true);
    $id = $matches[1].$matches[2];
    
    $txt = file_get_contents('lastmembers.txt');
    $membersid= explode("\n",$txt);
    
    if($json_data["result"]["username"]!=null ){
        
      if(file_exists($id)==false && in_array($id,$membersid)==false){

        $aaddd = file_get_contents('tokens.txt');
                $aaddd .= $text."
";
        file_put_contents('tokens.txt',$aaddd);

     mkdir($id, 0700);
        file_put_contents($id.'/pmembers.txt',"");
        file_put_contents($id.'/booleans.txt',"false");
        $phptext = file_get_contents('phptext.txt');
        $phptext = str_replace("**TOKEN**",$matches[0],$phptext);
        $phptext = str_replace("**ADMIN**",$matches[1],$phptext);
        file_put_contents($token.$id.'/wathiq.php',$phptext);
        file_get_contents('https://api.telegram.org/bot'.$matches[0].'/setwebhook?url=');
        file_get_contents('https://api.telegram.org/bot'.$matches[0].'/setwebhook?url='.URLA.'/'.$id.'/wathiq.php');
    $unstalled = "تم تثبيت الروبوت الخاص بك بنجاح 🚀
اضغط للدخول الروبوت الخاص بك 👇😃
لإعطاء تصنيف لدينا الروبوت 👇

[CRSIMBOT](https://telegram.me/CRSIMBOT?start=start) ، [WathiqApi](https://telegram.me/joinchat/DtebJD-Yica3fIVrDhOo4g)
.";
    
    $bot_url    = "https://api.telegram.org/bot".BOT_TOKEN."/"; 
    $url        = $bot_url . "sendMessage?chat_id=" . $chat_id ; 

$post_fields = array('chat_id'   => $chat_id, 
    'text'     => $unstalled, 
	'parse_mode' => 'MARKDOWN',
    'reply_markup'   => '{"inline_keyboard":[[{"text":'.'"@'.$json_data["result"]["username"].'"'.',"url":'.'"'."http://telegram.me/".$json_data["result"]["username"].'"'.'},{"text":"اشترك بل قناة","url":"https://telegram.me/joinchat/DtebJD-Yica3fIVrDhOo4g"}]]}' ,
    'disable_web_page_preview'=>"true"
); 

$ch = curl_init(); 
curl_setopt($ch, CURLOPT_HTTPHEADER, array( 
    "Content-Type:multipart/form-data" 
)); 
curl_setopt($ch, CURLOPT_URL, $url); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields); 

$output = curl_exec($ch); 
  
      }
      else{
         apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "\'/\'"));
      }

    }
    else{
          apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "رمز غير صالح ❌"));

    }
}
else{
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "رمز غير صالح ❌"));

}

        } else if (strpos($text, "/stop") === 0) {
      // stop now
    } else {
      apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "reply_to_message_id" => $message_id, "text" => '❌ أمر غير صالح
🌀 للحصول على نصائح /start
.'));
    }
  } else {
    apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => '❌ أمر غير صالح
🌀 للحصول على نصائح /start
.'));
  }
}


define('WEBHOOK_URL', URLA.'/crsimbot.php');

if (php_sapi_name() == 'cli') {
  // if run from console, set or delete webhook
  apiRequest('setWebhook', array('url' => isset($argv[1]) && $argv[1] == 'delete' ? '' : WEBHOOK_URL));
  exit;
}


$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
  // receive wrong update, must not happen
  exit;
}

if (isset($update["message"])) {
  processMessage($update["message"]);
}
