<?php
error_reporting(E_ALL);
set_time_limit(0);

$port = 8888;
$ip = "120.76.233.4";


while(1){
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	if ($socket < 0) {
		echo "���Ӵ���" . socket_strerror($socket) . "\n";
	}else {
		echo "�����Ž�����\n";
	}

	echo "��������ip'$ip' �˿�'$port'...\n";
	$result = socket_connect($socket, $ip, $port);
	if ($result < 0) {
		echo "���Ӵ���". socket_strerror($result) . "\n";
	}else {
		echo "���ӷ������ɹ�\n";
	}

	$in = "d:1";
	$out = '';

	if(!socket_write($socket, $in, strlen($in))) {
		echo "���Ӵ���" . socket_strerror($socket) . "\n";
	}else {
		echo "������Ϣ��".$in."�Ž�����¼\n";
	}

	$bk=70;
	while($out = socket_read($socket, 8192)) {
		echo "������Ϣ��$out\n";
		if($bk){
			$bk--;
			if($out=="o"){
				echo "�����Ž�������\n";
				$in="y";
				if(!socket_write($socket, $in, strlen($in))) {
					echo "���Ӵ���" . socket_strerror($socket) . "\n";
				}else {
					echo "������Ϣ��".$in."��ʾ�����ɹ�\n";
				}
			}
			if($out=="t"){
				echo "�������\n";
				$bk=70;
				$in="t";
				if(!socket_write($socket, $in, strlen($in))) {
					echo "���Ӵ���" . socket_strerror($socket) . "\n";
				}else {
					echo "������������$in\n";
				}
			}
		}else{
			break;
		}
		sleep(0.5);

	}
	socket_close($socket);

	echo "���ӷ�����ʧ�ܣ�����\n";
	sleep(20);
}

?>