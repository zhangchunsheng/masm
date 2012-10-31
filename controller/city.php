<?php
	/**
	 * 作者：peter
	 * 日期：2012-10-30
	 * 说明：城市
	 */
	class city extends base {
		function __construct() {
			parent::__construct();
		}
		
		function getCitys() {
			$provinceId = $this -> spArgs("provinceId");
			$result = spClass("mCity") -> findAll(array("parentId" => $provinceId), "", "id,code,name");
			$citys = new StdClass();
			$citys -> citys = $result;
			echo json_encode($citys);
		}
	}
?>