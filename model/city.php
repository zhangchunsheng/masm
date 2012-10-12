<?php
	/**
	 * 作者：peter
	 * 日期：2012-10-10
	 * 说明：城市
	 */
	class city extends spModel {
		public $pk = "id"; //数据库表主键
		public $table = "city";
		
		public function getAll() {
			$sql = "SELECT id,code,name,parentId,longitude,latitude FROM luomor_city WHERE bz=1";
			$result = $this -> findSql($sql);
			return $result;
		}
	}
?>