<?php
/////////////////////////////////////////////////////////////////
//云边开源轻博, Copyright (C)   2010 - 2011  qing.thinksaas.cn 
//EMAIL:nxfte@qq.com QQ:234027573                              
//$Id: site.php 40 2011-11-05 16:51:05Z anythink $ 


// 用来显示 关于 版权 用户协议 招聘 等 
class site extends top
{ 

	function index()
	{
		header( "HTTP/1.1 301 Moved Permanently"); 
		header('Location:'.spUrl('site','about'));
	}
	
	
	function about()
	{
		$this->show = 'about';
		$this->curr_about = 'current';
		$this->display('site.html');
	}
	
	function help()
	{
		$this->show = 'help';
		$this->curr_help = 'current';
		$this->display('site.html');
	}
	
	
	function call()
	{
		$this->show = 'call';
		$this->curr_call = 'current';
		$this->display('site.html');
	}
	
	function service()
	{
		$this->show = 'service';
		$this->curr_service = 'current';
		$this->display('site.html');
	}
   
   function privacy()
   {
	   $this->show = 'privacy';
	   $this->curr_privacy = 'current';
	   $this->display('site.html');
   }
	
	
}