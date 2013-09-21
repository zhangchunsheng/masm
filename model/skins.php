<?php
	/**
	 * 作者：peter
	 * 日期：2012-10-07
	 * 说明：样式
	 */
	class skins extends spModel {
		var $pk = "skindir"; //主键
		var $table = "skins"; // 数据表的名称

		//获取可用主题和非专属主题
		function getThemeList($type) {
			if($type =='my') {
				return  $this -> findAll(array('open' => 1, 'exclusive' => $_SESSION['uid']), '', 'id,name,skindir,usenumber');
			}
			return  $this -> findAll(array('open' => 1, 'exclusive' => 0), '', 'id,name,skindir,usenumber');
		}
	}
?>