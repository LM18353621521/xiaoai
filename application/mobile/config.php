<?php
$home_config = [
    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------
	//默认错误跳转对应的模板文件
    // 视图输出字符串内容替换
    'dispatch_success_tmpl'    => APP_PATH .'mobile' . DS.'view'. DS .'public' . DS . 'dispatch_jump.html',
    'dispatch_error_tmpl'    => APP_PATH .'mobile' . DS.'view'. DS .'public' . DS . 'dispatch_jump.html',
    ];
return $home_config;
?>