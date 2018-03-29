<?php
use \Workerman\Worker;
use \Workerman\Lib\Timer;
require_once './Workerman/Autoloader.php';
$ip = "120.76.233.4";//监听ip地址
$port = "8888";//监听端口号
$dpjlist=array();//单片机列表  格式 单片机序号：连接id
function uptime($lid){
	$mysqli = new mysqli('localhost:3306', 'root', '**********', 'xiaoqu');
	$rst = $mysqli->query("UPDATE wp_village_lock SET lstime='". date("Y-m-d H:i:s") ."' WHERE id='". $lid ."'");
	$mysqli->close();	
}



// 将屏幕打印输出到Worker::$stdoutFile指定的文件中
Worker::$stdoutFile = './tmp/stdout.log';
// 创建一个Worker监听，不使用任何应用层协议  TCP
$worker = new Worker("tcp://" . $ip . ":" . $port);
// 进程数设置为1
$worker->count = 1;
// 新增加一个属性，用来保存uid到connection的映射
$worker->uidConnections = array();

//定时器
$worker->onWorkerStart = function($worker)
{
	//每xx秒执行一次
    $time_interval = 30;
    Timer::add($time_interval, function()
    {
		global $worker;
		foreach($worker->uidConnections as $key=>$value){
			$connection = $value;
			$connection->send("t");//向指定单片机发送指令
		}
		echo "send check:tt-". date("Y-m-d H:i:s") . "\r\n";
    });
};

 // 当客户端发来数据时 connection连接 data数据
$worker->onMessage = function($connection, $data)
{
	global $worker;
	global $dpjlist;
	if(strstr($data,"d") != false)//单片机首次连接发送数据 d:单片机序号xxx  
	{
		
		$dpj = substr($data, 2);//获取d:后面的值
		$dpjlist[$dpj] = $connection->id;
		$worker->uidConnections[$connection->id] = $connection;
		$connection->send("o");//向指定单片机发送指令
		$mysqli = new mysqli('localhost:3306', 'root', '**********', 'xiaoqu');
		$rst = $mysqli->query("INSERT INTO wp_village_lockloginlog (lockid,logintime) VALUES (". $dpj .",'". date("Y-m-d H:i:s") ."')");
		$mysqli->close();
		uptime($dpj);
		echo "client:";
		echo $data;
		echo "-";
		echo date("Y-m-d H:i:s");
		echo "\r\n";
	
	}
	elseif(strstr($data,"o") != false)//打开单片机 o:单片机序号xxx  
	{
		$dpj = substr($data, 2);
		if(array_key_exists($dpj, $dpjlist))
		{
			$connection = $worker->uidConnections[$dpjlist[$dpj]];
			$connection->send("o");//向指定单片机发送指令
		}
		echo "open:";
		echo $data;
		echo "-";
		echo date("Y-m-d H:i:s");
		echo "\r\n";
	}
	elseif(strstr($data,"y") != false)//开锁成功
	{
		$mysqli = new mysqli('localhost:3306', 'root', '**********', 'xiaoqu');
		$rst = $mysqli->query("INSERT INTO wp_village_locklog (lockid,opentime) VALUES (". array_search($connection->id, $dpjlist) .",'". date("Y-m-d H:i:s") ."')");
		$mysqli->close();
		uptime(array_search($connection->id, $dpjlist));
		echo "y:";
		echo array_search($connection->id, $dpjlist);
		echo "-";
		echo date("Y-m-d H:i:s");
		echo "\r\n"; 
	} 
	elseif(strstr($data,"c") != false)//检测 
	{
		$dpj = substr($data, 2);
		if(array_key_exists($dpj, $dpjlist))
		{
			$connection = $worker->uidConnections[$dpjlist[$dpj]];
			$connection->send("t");//向指定单片机发送指令
		}
		echo "check:";
		echo $data;
		echo "-";
		echo date("Y-m-d H:i:s");
		echo "\r\n";
	}
	elseif(strstr($data,"t") != false)//心跳返回
	{
		uptime(array_search($connection->id, $dpjlist));
	}
	else{
		echo "?:";
		echo $data;
		echo "\r\n";		
	}
	//echo $data . "\n";
};

//当客户端关闭连接时
$worker->onClose = function($connection)
{
	global $worker;
	global $dpjlist;
	echo "close:";
	echo $connection->id;
	echo "-";
	echo date("Y-m-d H:i:s");
	echo "\r\n";
	if(array_search($connection->id,$dpjlist))
	{
		
		echo array_search($connection->id,$dpjlist);
		echo "\r\n";
		unset($dpjlist[array_search($connection->id,$dpjlist)]);
		unset($worker->uidConnections[$connection->id]);
	}
};
// 运行worker
Worker::runAll();