<?php
/**
 * Created by PhpStorm.
 * User: moon
 * Date: 16/8/1
 * Time: 11:15
 */

ini_set("display_errors", "On");  //错误调试
error_reporting(E_ALL | E_STRICT);

//判断 UserAgent 是否为浏览器
if (preg_match('/^Mozilla\/.*/i',strtolower($_SERVER['HTTP_USER_AGENT']))) {
    $br = "<br>";
    $err_ip = "您提交的不是有效的ip地址";
    $mode = "web";
    echo <<< HEADER
    <html>
    <head>
	<title>IP地址查询 - by moonfly</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    </head>
    <body>
HEADER;
}
else {
    $br = "\n";
    $err_ip = "Error, Bad IP address!$br";
    $mode = "console";
}

//判断get请求是否携带ip 参数
if(isset($_GET['ip'])) {
    $ip_exp="/^(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])$/i";
    preg_match($ip_exp,$_GET['ip']) OR die($err_ip);
    $onlineip = $_GET['ip'];
}
else {  //获取客户端真实IP
    if(getenv('HTTP_CLIENT_IP')) {
        $onlineip = getenv('HTTP_CLIENT_IP');
    }
    elseif(getenv('HTTP_X_FORWARDED_FOR')) {
        $onlineip = getenv('HTTP_X_FORWARDED_FOR');
    }
    elseif(getenv('REMOTE_ADDR')) {
        $onlineip = getenv('REMOTE_ADDR');
    }
    else {
        $onlineip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
    }
}

$ptr = gethostbyaddr($onlineip); //获取ip的ptr记录
echo "您的来源IP是：$onlineip $br";
if ( $ptr !== $onlineip )
    echo "反向地址解析名称为：$ptr $br";

//从ip138网站获取IP归属地信息
$url138 = "http://www.ip138.com/ips138.asp?ip=${onlineip}&action=2";
//$content = file_get_contents($url138);
//$content = iconv('GB2312','UTF-8',file_get_contents($url138));
//echo $content;

$ch = curl_init();
curl_setopt($ch,CURLOPT_URL,$url138);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_HEADER,0);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2486.0 Safari/537.36 Edge/13.10586');
$content = iconv('GB2312','UTF-8',curl_exec($ch));
curl_close($ch);
//echo $content;


if (mb_strstr($content, '本站数据：'))
{
    $tag1 = stripos($content,'<ul class="ul1">');
    $tag2 = stripos($content,'</ul>') - $tag1;
    $info_138 = strip_tags(substr($content,$tag1,$tag2));
    echo "IP138显示您的IP所在地==> $info_138 $br";
}else{
    echo "从IP138获取归属地信息遇到了问题，请手动访问连接 $url138 查询";
}

//从sina api 获取IP归属地信息
$sinaurl = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=$onlineip";
$info_sina = json_decode(file_get_contents($sinaurl),true);
//echo $info_sina['country'] $ipinfo['province'] $ipinfo['city'];
//echo "$br sina IP归属地接口查询数据：$br 您的IP来自于 $info_sina['country'] $ipinfo['province'] $ipinfo['city']";
echo "$br Sina IP数据库显示您的IP来自于==> ".$info_sina['country']." ".$info_sina['province']." ".$info_sina['city'];
echo $br;

//判断mode 如果为 web 则输出html查询表单
if ( $mode == "web" ) {
    echo <<< body
<div align="center">
<form name="set_page" action="ip.php" method="get">
输入IP地址：<input type="text" size="15" pattern="^\d+\.\d+\.\d+\.\d+$" maxlength="15" name="ip" />
<input type="submit" value="查询!" />
</form>
</div>
</body>
body;
}
?>