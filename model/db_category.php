<?php
/////////////////////////////////////////////////////////////////
//云边开源轻博, Copyright (C)   2010 - 2011  qing.thinksaas.cn 
//EMAIL:nxfte@qq.com QQ:234027573                              
//$Id: db_category.php 7 2011-09-20 15:02:20Z anythink $ 

class db_category extends spModel  
{  
	var $pk = "cid"; // 主键  
	var $table = "catetags"; // 数据表的名称 
	
	
	function findCate()
	{
		return $this->spCache(3600)->findAll('','sort ASC');
	}
	
	
	function saveCate($post)
	{
		if(is_array($post['cate']))
		{
			foreach($post['cate'] as $k=>$v)
			{
				$arr['sort'] = $v['sort'];
				$arr['catename'] = $v['catename'];
				$this->update(array('cid'=>$k),$arr);
			}
		}
	}

}
?>