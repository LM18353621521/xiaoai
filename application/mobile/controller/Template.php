<?php
namespace app\wechat\controller;
use think\Controller;

class Template extends Controller
{
    
	/**
	* 绑定会员通知用户 2017-10-15
	*/
   	public function bindviptouser(){
   		$data = array(
   			'openid'	=> 'ofCkbwuaCNFRwVcIgcWdubRzzQbU',
   			'tid'		=> 'Uk6eBh07e_-OfUDQDrAl2srrEGvYbfRTseV0rtbvlkY',
   			'url' 		=> get_domain().'/wechat.php?s=Vip/index',
   			'content'	=> array(
   				'first'			=> '尊敬的用户，您已成功绑定会员账号，详情：',
   				'keyword1'		=> '123456789',
   				'keyword2'		=> '20140830',
   				'remark'		=> '感谢您的支持，如有疑问，请于客服人员联系。'
   			)
   		);
   		$result = sendtemplate(2,$data);
   	}
}