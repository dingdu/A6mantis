<?php
/**
 * Notice: 测试邮件系统
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\7\24 0024
 * Time: 17:58
 */

require ('core.php');
require_api('email_api.php');
require_api('email_queue_api.php');

//echo email_store('1257566343@qq.com', 'Mantis错误提醒', '您的项目未完成');
//email_send_all();
//
//$ew = new EmailData();
//# 收件人
//$ew->email = '1257566343@qq.com';
//$ew->subject = 'Mantis温馨提醒：';
//$ew->body = '您的内容未完成';
//
//email_send($ew);

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
require './vendor/phpmailer/phpmailer/PHPMailerAutoload.php';
//
//$mail=new PHPMailer;
//$mail->isSMTP();
//$mail->Host="smtp.163.com";//发件人使用的smtp服务地址
//$mail->SMTPAuth=true;
//$mail->Username="13560482742@163.com";//发件人邮箱地址
//$mail->Password="51930you";//发件人密码（这个密码不是登录密码而是smtp密码）
//
//$mail->setFrom("13560482742@163.com","Mantis");
//$mail->addAddress("1257566343@qq.com","丁度铭");//收件人地址和姓名
//
//$mail->Subject="PHPMailer测试";//标题
//$mail->Body="PHPMailer是一个用来发送电子邮件的函数包，这是使用它发送邮件的一个demo";//正文

$mail=new PHPMailer;

$mail->CharSet    ="UTF-8";
$mail->isSMTP();
$mail->Host="smtp.mxhichina.com";//发件人使用的smtp服务地址
$mail->SMTPAuth=true;
$mail->Username="ddm@a6shop.com";//发件人邮箱地址
$mail->Password="hanquan20!6";//发件人密码（这个密码不是登录密码而是smtp密码）

$mail->setFrom("ddm@a6shop.com","Mantis");
$mail->addAddress("1257566343@qq.com","丁度铭");//收件人地址和姓名

$mail->Subject="PHPMailer测试";//标题
$mail->Body="PHPMailer是一个用来发送电子邮件的函数包，这是使用它发送邮件的一个demo";//正文

//$mail->SMTPDebug = true;           // 开启调试
# 发送邮件
if(!$mail->send()){
    echo "send failed!";
    echo "error:".$mail->ErrorInfo;
}else{
    echo "send success!";
}