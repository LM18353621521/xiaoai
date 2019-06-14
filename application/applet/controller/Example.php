<?php
namespace app\applet\controller;

use SimpleXMLElement;

class Example extends Applet
{

	
	/**
	 * 生成带参数小程序码-第一种
	 */
	public function createappletcodefirst(){
		$savepath = '/uploads/picture/uid2/appletcode/';
		if(!file_exists($savepath)){
			mkdir(ROOT_PATH.$savepath,0777,true);
		}

		$appletcode = './uploads/picture/uid2/appletcode/2.jpg';//小程序码
		
		
		$data = array(
				'path'		=> '/pages/car/car',
				'width'		=> 300,
				'auto_color'=> false,
				'line_color'=> array(
					'r'	=> 255,
					'g'	=> 106,
					'b'	=> 106
				)
		);
		$this -> createappletcode1(2,$appletcode, $data);
	}
	
	/**
	 * 生成带参数小程序码-第二种
	 */
	public function createappletcodesecond(){
		$savepath = '/uploads/picture/uid2/appletcode/';
		if(!file_exists($savepath)){
			mkdir(ROOT_PATH.$savepath,0777,true);
		}
		$appletcode = '/uploads/picture/uid2/appletcode/2.jpg';//小程序码
		$data = array(
				'scene'	=> 'a1',
				'page'	=> 'pages/car/car',
				//'width'	=> 200,
		);
		$this -> createappletcode2(2,$appletcode, $data);
	}
	
	/**
	 * 生成小程序二维码
	 */
	public function createqrcode(){
		$savepath = '/uploads/picture/uid2/appletcode/';
		if(!file_exists($savepath)){
			mkdir(ROOT_PATH.$savepath,0777,true);
		}
		$appletcode = './uploads/picture/uid2/appletcode/2.jpg';//小程序码
		$data = array(
				'path'		=> '/pages/car/car',
				'width'		=> 300,
		);
		$this -> createappletqrcode(2,$appletcode, $data);
	}
	
	

}