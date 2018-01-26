<?php
/*
به نام خداوند جان و خرد
سورس ارسال پیام به کانال با برداشتن ایدی کانال فوروارد شده!
توسط:
@worldtm
*/
error_reporting(0);
//--------[Your Config]--------//
$Dev = ایدی عددی ادمین; //--Put Dev ID
$Token = 'توکن'; //--Put BotToken
$ex = explode(":",$Token);
$BotID = $ex[0];
//-----------------------------//
define('API_KEY',$Token);
//------------------------------------------------------------------------------
function WorldTm($method,$datas=[]){
    $url = "https://api.telegram.org/bot".API_KEY."/".$method;
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($datas));
    $res = curl_exec($ch);
    if(curl_error($ch)){
        var_dump(curl_error($ch));
    }else{
        return json_decode($res);
    }
}
//------------------------------------------------------------------------------
function SendMessage($id,$textm,$key){
 WorldTm('SendMessage',[
 'chat_id'=>$id,
 'text'=>$textm,
 'parse_mode'=>'MarkDown',
 'reply_markup'=>$key
 ]);
 }
function save($filename,$TXTdata){
 $myfile = fopen($filename, "w") or die("Unable to open file!");
 fwrite($myfile, "$TXTdata");
 fclose($myfile);
 }
function savea($filename,$TXTdata){
 $myfile = fopen($filename, "a") or die("Unable to open file!");
 fwrite($myfile, "$TXTdata");
 fclose($myfile);
 }
function GetChatMember($chatid,$userid){
 global $Token;
 $check = json_decode(file_get_contents("https://api.telegram.org/bot".$Token."/getChatMember?chat_id=".$chatid."&user_id=".$userid), true);
 $status = $check['result']['status'];
 return $status;
 }
function admins($from_id){
 global $from_id;
 $file = file_get_contents('admin.txt');
 $ex = explode("\n",$file);
if(!in_array($from_id,$ex)){
 return  false;
}else{
 return true;
}
}
//------------------------------------------------------------------------------
$update = json_decode(file_get_contents('php://input'));
$up = json_decode(file_get_contents('php://input'), true);
$message = $update->message; 
$chat_id = $message->chat->id;
$textmessage = $message->text;
$message_id = $update->message->message_id;
$from_id = $message->from->id;
$name = $message->from->first_name;
$lastname = $message->from->last_name;
$username = $message->from->username;
$fwc = $update->message->forward_from_chat->username;
$forward_chat_id = $update->message->forward_from_chat->id;
$admin = admins($from_id);
//Get Contents
@$step = file_get_contents("step.txt");
@$ch = file_get_contents("ch.txt");
@$chid = file_get_contents("chid.txt");
//KeyBoard
$d_home = json_encode(['keyboard'=>[
[['text'=>'ارسال پست جدید']],
[['text'=>'تغییر کانال']]
],'resize_keyboard'=>true]);
$d_back = json_encode(['keyboard'=>[
[['text'=>'بازگشت']]
],'resize_keyboard'=>true]);
//------------------------------------------------------------------------------

if($textmessage == "/start" and $from_id == $Dev and $admin != true){
if(!file_exists("step.txt")){
save("step.txt","ch");
SendMessage($chat_id,"پیامی از کانال خود فوروارد کنید!\nتوجه داشته باشید که از قبل ربات را در کانال خود ادمین کرده باشید!");
}
}
if($textmessage == "/start"){
    if($admin == true || $from_id == $Dev){
SendMessage($chat_id,"چه کاری براتون انجام بدم؟
جهت اضافه کردن ادمین به ربات:
/admin *id*
به صورت بالا عمل کرده و به جای عدد ، آیدی عددی یارو را بگذارید.",$d_home);
}
}
elseif(strpos($textmessage , '/admin ')!== false && $from_id == $Dev){
    $id = str_replace('/admin ',"",$textmessage);
savea("admin.txt","$id\n");
SendMessge($chat_id,"کاربر *$id* ادمین ربات شد");
}
elseif($textmessage == "بازگشت"){
    if($admin == true || $from_id == $Dev){
save("step.txt","none");
SendMessage($chat_id,"به منوی اصلی برگشتیم!",$d_home);
return;
}
}
elseif($step == "ch" and $fwc != null){
$status = GetChatMember("@$fwc",$BotID);
if($status == "administrator"){
save("ch.txt",$fwc);
save("chid.txt",$forward_chat_id);
save("step.txt","none");
SendMessage($chat_id,"تنظیم شد!",$d_home);
}else{
SendMessage($chat_id,"ربات در کانال ادمین نیست!");
}
}
if($step != "ch"){

if($textmessage == "تغییر کانال" and $from_id == $Dev){
save("step.txt","ch");
SendMessage($chat_id,"پیامی از کانال خود فوروارد کنید!\nتوجه داشته باشید که از قبل ربات را در کانال خود ادمین کرده باشید!");
}
if($admin == true || $from_id == $Dev){
if($textmessage == "ارسال پست جدید"){
save("step.txt","post");
SendMessage($chat_id,"لطفا پست خود را فوروارد کنید!",$d_back);
}
elseif($up['message']['text'] and $step == "post"){
save("step.txt","none");
$text = str_replace($fwc,$ch,$textmessage);

$msg = WorldTm('SendMessage',[
    'chat_id'=>$chid,
    'text'=>"$text",
    ])->result->message_id;
$for = WorldTm('ForwardMessage',[
    'chat_id'=>$chat_id,
    'from_chat_id'=>"@$ch",
    'message_id'=>$msg
    ])->result->message_id;
WorldTm('SendMessage',[
    'chat_id'=>$chat_id,
    'text'=>"ارسال شد!",
    'reply_markup'=>$d_home,
    'reply_to_message_id'=>$for
    ]);
}
}
}else{
    if($fwc == null){
   sendmessage($chat_id,"کانال تنظیم نشده است!\nیک پیام از کانال خود فوروارد کنید!"); 
}
}
?>
