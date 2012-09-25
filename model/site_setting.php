<?php
	/**
	 * 作者：peter
	 * 日期：2012-09-25
	 * 说明：网站配置
	 */
	class site_setting extends spModel {
		var $pk = "name"; //主键
		var $table = "setting"; // 数据表的名称

		/*
		 *显示系统配置
		 */
		function getConfig() {
			$rs = $this -> findAll();
			$data = '';
			foreach($rs as $d){
				$data[$d['name']] = $d['val'];
			}
			return $data;
		}

		/*
		 * 保存系统配置
		 * input array key value
		 */
		function saveConfig($var) {
			spAccess('c', 'siteconfig');

			foreach($var as $k => $d) {
				$rs = $this -> find(array('name' => $k));
				if(is_array($rs)) {
					$this -> update(array('name' => $k), array('val' => $d));
				} else {
					$this -> create(array('name' => $k), array('val' => $d));
				}
			}
			$rs = $this -> getConfig();
			spAccess('w', 'siteconfig', $rs, 3600);
		}
	}
?>