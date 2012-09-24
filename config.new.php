<?php
/*系统公用配置文件位置*/
define('APP_NAME','/yunbian/trunk/');
$spConfig = array(

	'yb' =>array(
		'loginCodeSwitch'=>'close',//登陆验证开关 打开请写open
		'regCodeSwitch'=>'close', //注册验证开关 打开请写open
	),
	
	 'mode' => 'debug', // 应用程序模式，默认为调试模式debug  部署release
	 'dispatcher_error' => "err404();",
	 'encodestring' =>'请在这里填写您之前配置文件的encodestring码',
	  "db" => array(  
		  'host' => 'localhost', // 数据库地址
		  'login' => 'root',   
		  'password' => '',  //数据库密码
		  'database' => '',  //数据库
		  'prefix'  => 'th_', //表前缀
		  'db_driver_path' => SP_PATH.'/Drivers/pdo.php', 
 	 ), 
	  'launch' => array( 
		 'router_prefilter' => array( array('spUrlRewrite', 'setReWrite') ),
		 'function_url' => array( array("spUrlRewrite", "getReWrite"), ),
		 'function_access' => array(array('spAccessCache','db')),
	  ),
	  
	'url' => array(  
		'url_path_info'=>false,
		'url_path_base' => APP_NAME.'index.php'
	),  
	
	 'ext' => array(
	 		'spUrlRewrite' => array(
				'hide_default' => true, 
				'suffix' => '', 
				'sep' =>'/',
				'map' => array(   
					 'index' => 'main@index',
					 'recommend'=>'main@recommend',
					 'discovery'=>'main@discovery',
					 'now'      =>'main@now',
       				 'register' => 'main@reg', 
					 'login' => 'main@login',
					 'tag' => 'blog@tag',
					 'pm'=>'user@pm',
					 'myfollow'=>'user@myfollow',
					 'mypost'=>'user@mypost',
					 'mylikes'=>'user@mylikes',
					 'myreplays'=>'user@myreplay',
					 'mynotices'=>'user@mynotice',
					 'edit' => 'add@edit',
					 'logout' => 'main@logout',
					 'custom'=>'userblog@customize',
					 'read' =>'userblog@show', 
					 '@' => 'userblog@index',   
     			), 
				'args' => array(
					   'tag' => array('tag','page'),
					   'gomember' => array('uid'),
					   'read' => array('bid','domain','uid'),
					   'mylikes'=>array('page'), 
					    '@' => array('domain','uid')
   				 ),  
			),
			'aUpload' => array( //上传
				'savepath' => APP_PATH , //保存的绝对位置
				'savedir'  => 'attachs', //相对位置
				'tmppath' => APP_PATH .'/attachs/tmp',  //上传临时位置必须存在
				'filetype' => 'jpg,png,gif,bmp,rar,zip,mp3,wma,mid,doc,pdf', 
				'filesize' =>4194304, //4M
				'fileinput' =>'filedata' ,//默认文件上传域
				'dirtype' => 4,  //文件夹保存格式
				'imgresize' => TRUE,  //图片文件自动创建缩略图
				'imgmask'   => TRUE,  //图片自动加水银
				'imgmasksrc' => SP_PATH.'/Extensions/font/logo.png', //水印文字 
				'imgresizew' => 500, //缩略图比例宽度
			),
			'spVerifyCode' => array( //验证码扩展
				'width' => 60, //验证码宽度
				'height' => 32, //验证码高度
				'length' => 4, //验证码字符长度
				'bgcolor' => '#FFFFFF', //背景色
				'noisenum' => 50, //图像噪点数量
				'fontsize' => 22, //字体大小
				'fontfile' => 'font/font.ttf', //字体文件
				'format' => 'png', //验证码输出图片格式
			),


	 ),		


);

?>