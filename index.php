<?php
	/**
	 * 作者：张春生
	 * 日期：2012-09-24
	 * 说明：入口文件
	 */
	define('APP_PATH', dirname(__FILE__));
	define('IN_APP', TRUE);
	define("SP_PATH", APP_PATH . '/speedPHP');
	date_default_timezone_set('PRC');
	session_name('luomor');
	$lefttime = 24 * 3600;
	session_set_cookie_params($lefttime, '/');
	ini_set('session.gc_maxlifetime', $lefttime);
	if(isset($_COOKIE['luomor'])) {
		session_id($_COOKIE['luomor']);
	}
	if(isset($_POST['ssid'])) {
		session_id($_POST['ssid']);
	}//swfupload 提交也要判断
	if(!is_file(APP_PATH . "/config.php")){
		header('Location:install/');
	}
	require(APP_PATH . "/config.php"); //载入配置文件
	require(SP_PATH . "/init.php");
	require(APP_PATH . "/common/functions.php");
	import(APP_PATH . "/controller/base.php");
	header("Content-type: text/html; charset=utf-8");
	spRun();
?>