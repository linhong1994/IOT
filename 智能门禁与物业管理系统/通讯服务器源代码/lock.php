<?php
error_reporting(E_ALL);
set_time_limit(0);

$port = 8888;
$ip = "120.76.233.4";


while(1){
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	if ($socket < 0) {
		echo "连接错误：" . socket_strerror($socket) . "\n";
	}else {
		echo "智能门禁开机\n";
	}

	echo "尝试连接ip'$ip' 端口'$port'...\n";
	$result = socket_connect($socket, $ip, $port);
	if ($result < 0) {
		echo "连接错误：". socket_strerror($result) . "\n";
	}else {
		echo "连接服务器成功\n";
	}

	$in = "d:1";
	$out = '';

	if(!socket_write($socket, $in, strlen($in))) {
		echo "连接错误：" . socket_strerror($socket) . "\n";
	}else {
		echo "发送信息：".$in."门禁锁登录\n";
	}

	$bk=70;
	while($out = socket_read($socket, 8192)) {
		echo "接收信息：$out\n";
		if($bk){
			$bk--;
			if($out=="o"){
				echo "控制门禁锁开锁\n";
				$in="y";
				if(!socket_write($socket, $in, strlen($in))) {
					echo "连接错误：" . socket_strerror($socket) . "\n";
				}else {
					echo "发送信息：".$in."表示开锁成功\n";
				}
			}
			if($out=="t"){
				echo "心跳检测\n";
				$bk=70;
				$in="t";
				if(!socket_write($socket, $in, strlen($in))) {
					echo "连接错误：" . socket_strerror($socket) . "\n";
				}else {
					echo "返回心跳包：$in\n";
				}
			}
		}else{
			break;
		}
		sleep(0.5);

	}
	socket_close($socket);

	echo "连接服务器失败，重启\n";
	sleep(20);
}

?>