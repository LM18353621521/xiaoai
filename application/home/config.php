<?php
$home_config = [
    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------
	//默认错误跳转对应的模板文件
    'dispatch_success_tmpl'    => APP_PATH .'home' . DS.'view'. DS .'public' . DS . 'dispatch_jump.html',
    'dispatch_error_tmpl'    => APP_PATH .'home' . DS.'view'. DS .'public' . DS . 'dispatch_jump.html',
    ];
return $home_config;
?>