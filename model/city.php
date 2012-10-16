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
		
		/**
		 * 根据位置获取城市信息
		 */
		public function findByPosition($latitude, $longitude) {
			$sql = "SELECT id,code,name,formatted_address FROM luomor_city WHERE $latitude>=southwest_lat AND $latitude<=northeast_lat AND $longitude>=southwest_lng AND $longitude<=northeast_lng";
			$result = $this -> findSql($sql);
			return $result;
		}
		
		/**
		 * 根据Google城市名称查找
		 */
		public function findByGoogleName($positionInfo) {
			$sql = "SELECT id,code,name,formatted_address,type FROM luomor_city WHERE google_name='" . $positionInfo -> localityName . "'
					UNION
					SELECT id,code,name,formatted_address,type FROM luomor_city WHERE google_name='" . $positionInfo -> sublocalityName . "'";
			$result = $this -> findSql($sql);
			$array = array();
			if(is_array($result)) {
				if(array_key_exists("code", $result)) {
					$array["code"] = $result["code"];
					$array["name"] = $result["name"];
				} else {
					foreach($result as $key => $value) {
						if($value["type"] == "sublocality") {
							$array["code"] = $value["code"];
							$array["name"] = $value["name"];
							break;
						} else {
							$array["code"] = $value["code"];
							$array["name"] = $value["name"];
						}
					}
				}
			} else {
				$array["code"] = "0";
				$array["name"] = "中国";
			}
			return $array;
		}
	}
?>