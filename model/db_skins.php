<?php
/////////////////////////////////////////////////////////////////
//云边开源轻博, Copyright (C)   2010 - 2011  qing.thinksaas.cn 
//EMAIL:nxfte@qq.com QQ:234027573                              
//$Id: db_skins.php 31 2011-10-17 04:59:44Z anythink $ 

class db_skins extends spModel  
{  
	var $pk = "skindir"; //主键  
	var $table = "skins"; // 数据表的名称 
	
	
	//获取可用主题和非专属主题
	function getThemeList($type)
	{
		if($type =='my')
		{
			return  $this->findAll(array('open'=>1,'exclusive'=>$_SESSION['uid']),'','id,name,skindir,usenumber');
		}
		return  $this->findAll(array('open'=>1,'exclusive'=>0),'','id,name,skindir,usenumber');
	}
	
	

}
?>