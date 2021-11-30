<?php
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
    $err_ip = "Error, Bad IP address! $br";
    $mode = "console";
}

//判断get请求是否携带ip 参数
if(isset($_GET['ip'])) {
    $ip_exp="/^(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])$/i";
    preg_match($ip_exp,$_GET['ip']) OR die($err_ip);
    $onlineip = $_GET['ip'];
    $nn="您查询的IP是：";
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
    $nn="您的来源IP是：";
}

//txt模式输出,给DDNS脚本检测公网IP地址使用，只返回公网IP
if (isset($_GET['mod']) && $_GET['mod'] == "txt") {
    header("Content-Type: text/plain; charset=utf-8");
    echo $onlineip;
    exit;
}

$ptr = gethostbyaddr($onlineip); //获取ip的ptr记录
echo $nn.$onlineip.$br;
if ( $ptr !== $onlineip )
    echo "PTR反向解析：$ptr $br";

/*************
// ip138网站API已不可用，换成其他API查询
$url138 = "http://www.ip138.com/ips138.asp?ip=${onlineip}&action=2";

$ch = curl_init();
curl_setopt($ch,CURLOPT_URL,$url138);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_HEADER,0);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2486.0 Safari/537.36 Edge/13.10586');
$content = iconv('GB2312','UTF-8',curl_exec($ch));
curl_close($ch);

echo $content;
if (mb_strstr($content, '本站数据：'))
{
    $tag1 = stripos($content,'<ul class="ul1">');
    $tag2 = stripos($content,'</ul>') - $tag1;
    $info_138 = strip_tags(substr($content,$tag1,$tag2));
    echo "IP138归属地： $info_138 $br";
}else{
    echo "从IP138获取归属地信息遇到了问题，请手动访问连接 $url138 查询";
}
**************/

//从ipip.net free api 获取IP归属地信息
$ipipurl = "http://freeapi.ipip.net/$onlineip";
$info_ipip = json_decode(file_get_contents($ipipurl),true);
echo "IPIP.NET显示归属地： ".$info_ipip[0].",".$info_ipip[1].",".$info_ipip[2].",".$info_ipip[3].", ISP:".$info_ipip[4].$br;

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
