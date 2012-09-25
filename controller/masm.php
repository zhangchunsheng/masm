<?php
	class masm extends base {
		// 用来显示关于、版权、用户协议、服务等
		function index() {
			header("HTTP/1.1 301 Moved Permanently");
			header('Location:' . spUrl('masm', 'about'));
		}

		function about() {
			$this -> show = 'about';
			$this -> curr_about = 'current';
			$this -> display('masm.html');
		}

		function help() {
			$this -> show = 'help';
			$this -> curr_help = 'current';
			$this -> display('masm.html');
		}

		function call() {
			$this -> show = 'call';
			$this -> curr_call = 'current';
			$this -> display('masm.html');
		}

		function service() {
			$this -> show = 'service';
			$this -> curr_service = 'current';
			$this -> display('masm.html');
		}

		function privacy() {
			$this -> show = 'privacy';
			$this -> curr_privacy = 'current';
			$this -> display('masm.html');
		}
	}