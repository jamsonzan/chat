<?php
use Workerman\Worker;
require_once __DIR__ . '/workerman/Autoloader.php';

// 注意：这里与上个例子不同，使用的是websocket协议
$worker = new Worker("websocket://localhost:9100");

// 启动4个进程对外提供服务
$worker->count = 1;

$user_count = 0;

// 当收到客户端发来的数据后返回hello $data给客户端
$worker->onMessage = function($connection, $data)
{
	global $worker;
    $data_arr = json_decode($data,true);
    // 登录
    if ($data_arr['action_type'] == 'login') {
	    // 向客户端发送hello $data
	    $connection->uid = $data_arr['uid'];
	    $connection->photo = $data_arr['photo'];
	    $connection->username = $data_arr['username'];
	    // 给所用用户广播新用户加入
		$send_data = json_encode([
			'action_type' => 'new_user_login',
			'username' => $data_arr['username'],
			'photo' => $data_arr['photo'],
		]);
	    // 遍历当前进程所有的客户端连接，发送当前服务器的时间
	    foreach($worker->connections as $con)
	    {
	    	$con->send($send_data);
	    }
    }
    // 发送消息
    if ($data_arr['action_type'] == 'send_msg') {
	    // 遍历当前进程所有的客户端连接，发送当前服务器的时间
	    foreach($worker->connections as $con)
	    {
		    // 给所用用户广播新用户加入
			$send_data = [
				'action_type' => 'new_msg',
				'my_msg' => 0,
				'uid' => $connection->uid,
				'photo' => $connection->photo,
				'username' => $connection->username,
				'content' => $data_arr['content'],
			];
	    	if ($connection->uid == $con->uid) {
	    		$send_data['my_msg'] = 1;
	    	}
	    	$con->send(json_encode($send_data));
	    }
    }
};

$worker->onConnect = function($connection)
{
	global $worker,$user_count;
	$user_count++;
    // 遍历当前进程所有的客户端连接，发送当前服务器的时间
    foreach($worker->connections as $connection)
    {
	    // 给所用用户广播当前在线人数
		$send_data = json_encode([
			'action_type' => 'online_user_count',
			'online_user_count' => $user_count,
		]);
        $connection->send($send_data);
    }
};

// 用户断开链接
$worker->onClose = function($connection)
{
	global $worker,$user_count;
	$user_count = $user_count-1;
    // 遍历当前进程所有的客户端连接，发送当前服务器的时间
    foreach($worker->connections as $con)
    {
	    // 给所用用户广播用户退出
		$send_data = json_encode([
			'action_type' => 'user_on_close',
			'username' => $connection->username,
		]);
		$con->send($send_data);

	    // 给所用用户广播当前在线人数
		$online_user_count_send_data = json_encode([
			'action_type' => 'online_user_count',
			'online_user_count' => $user_count,
		]);
        $con->send($online_user_count_send_data);
    }
};

// 运行worker
Worker::runAll();