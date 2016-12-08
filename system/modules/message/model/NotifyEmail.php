<?php

namespace application\modules\message\model;

use application\core\model\Model;
use application\core\utils as util;
use application\modules\user\model\User;

class NotifyEmail extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{notify_email}}';
    }

    public function formatEmailNotify($data)
    {
        $baseUrl = util\Ibos::app()->setting->get('siteurl');
        $fullName = util\Ibos::app()->setting->get('setting/unit/fullname');
        $user = User::model()->fetchByUid($data['uid']);
        $named = $user['realname'] . ($user['gender'] == 1 ? ' 先生' : ' 女士');
        $body = $data['body'];
        $time = date('Y', time());
        if ($data['hasContent']) {
            $bodyStr = <<<str
        <tr>
			<td colspan="2">
				<div style="width:493px; padding:25px; margin:0 auto; background:#FFF; border:1px solid #ededed">
					{$body}
				</div>
			</td>
		</tr>   
str;
        } else {
            $bodyStr = <<<EOT
<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title>邮件提醒</title>
</head>
<body>
	<style type="text/css">
		a{ text-decoration:none; }
		a:hover{ text-decoration:underline; }
	</style>
	<table style="width:598px; border:1px solid #e8e8e8;  background:#fcfcfc; margin:0 auto;" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<!-- 公司名称 -->
			<td style="width:425px; height:49px; line-height:49px; overflow:hidden; background:#1180c6; font-size:18px; font-weight:bold; color:#FFF; font-family:'Microsoft YaHei';">&#12288;{$fullName}</td>
			<td style="width:173px; height:49px; line-height:49px; overflow:hidden; background:#1180c6; font-size:12px; color:#FFF">IBOS云服务中心·邮件提醒</td>
		</tr>
		<tr>
			<td colspan="2" style="width:598px; height:30px; overflow:hidden;">&nbsp;</td>
		</tr>
		<tr>
			<!-- 收件人姓名 -->
			<td colspan="2" style="width:548px; height:40px; line-height:40px; overflow:hidden;font-size:16px; font-family:'\5b8b\4f53';"><div style="width:543px; margin:0 auto; font-size:16px;">HELLO！{$named}:</div></td>
		</tr>
		<tr>
			<td colspan="2" style="width:598px; height:80px; overflow:hidden; ">
				<div style="width:543px; margin:0 auto;">
					<!-- 通知标题 -->
					<p align="center" style="width:493px; margin:0 auto; font-size:14px; line-height:20px; font-family:'\5b8b\4f53';color:#50545f;">{$data['title']}</p>
				</div>
			</td>
		</tr>
        {$body}
		<tr>
			<td colspan="2" style="width:598px; height:50px; overflow:hidden;">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2" style="width:598px; height:40px; overflow:hidden;">
				<!-- 登录按钮 -->
				<div style="width:380px; height:40px; line-height:40px; background:#1180c6; margin:0 auto; color:#fff; text-align:center">
                    <a href="{$baseUrl}{$data['url']}" target="_blank" style=" color:#fff;font-size:16px;">现在就登录 IBOS协同办公平台，处理相关事宜！</a>
                </div>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="width:598px; height:40px; overflow:hidden;">&nbsp;</td>
		</tr>
		<tr>
			<!-- 提示 -->
			<td colspan="2" align="center" style="width:598px; height:80px; overflow:hidden; font-size:12px;">
                <span style="color:#1180c6">■&nbsp;</span>您可以在<span style="color:#1180c6">&#12288;
                    <a style="color:#1180c6;" href="{$baseUrl}?r=user/home/index">个人中心</a>&#12288;->&#12288;
                    <a style="color:#1180c6;" href="{$baseUrl}?r=user/home/personal">个人资料</a>&#12288;->&#12288;
                    <a style="color:#1180c6;" href="{$baseUrl}?r=user/home/personal&op=remind">提醒设置</a>&#12288;
                 </span>中管理来自IBOS协同办公平台的邮件提醒
            </td>
		</tr>
	</table>
	<table style="width:600px; margin:0 auto;" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td style="width:600px; height:30px; font-size:12px; font-family:'\5b8b\4f53';color:#50545f;">
                <div style="line-height:30px; padding-top:5px;">{$time} ©  IBOS协同办公平台</div>
            </td>
		</tr>
		<tr>
			<!-- 其他链接 -->
			<td style="width:600px; height:30px; font-size:12px; font-family:'\5b8b\4f53';color:#50545f; line-height:30px;">
                <a href="http://www.ibos.com.cn" style="color:#50545f;" target="_blank">开发者平台</a>&#12288;/&#12288;
                <a href="http://kf.ibos.com.cn" style="color:#50545f;" target="_blank">问答社区</a>&#12288;/&#12288;
                <a href="http://doc.ibos.com.cn/article/lists/category/home" style="color:#50545f;" target="_blank">文档中心</a>&#12288;&#12288;客户支持: 400-838-1185&#12288;&#12288;&#12288;support@ibos.com.cn
            </td>
		</tr>
	</table>
</body>
</html>
EOT;
        }

        return $bodyStr;
    }

}
