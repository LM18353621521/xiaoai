<?php
namespace app\wechat\controller;
use think\Controller;
use SimpleXMLElement;
class Special extends Controller 
{
    
    /**
    * 处理支付的反馈结果——积分兑换支付	2017-01-05
    */
    public function integral(){
    	$uid = input('get.uid');
    	$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
    	$data = xmltoarray($xml);
	
    	$pay = db(\tname::weixin_pay) -> where('uid',$uid) -> find();
    	$wxpayparam = serializeMysql($pay['wxpay'],1);
    	$Wxpay= controller('purewechat/Wxpay');
    	$sign = $Wxpay -> checksign($data,$wxpayparam);
    	$returnParameters = array();
    	if($sign == false){
    		$returndata = array(
    			'return_code'	=> 'FAIL',
    			'return_msg'	=> '签名失败'
    		);   
    	}else{
    		$returndata = array(
    			'return_code'	=> 'SUCCESS',
    			'return_msg'	=> 'OK'
    		);
    	} 	
    	$xml = new SimpleXMLElement('<xml></xml>');
    	data2xml($xml, $returndata);
    	$returnXml = $xml->asXML();
    	echo $returnXml;
		
    	//以下为微信支付需要修改的
    	$orderinfo = db(\tname::integral_order) -> where('order_number',$data['out_trade_no']) -> find();
    	if($orderinfo['is_pay'] != 1){
    		//记录微信支付返回结果
    		apilog($uid,'wechat','wxpaynotify-integral','', serializeMysql($data), serializeMysql($returndata));
    		
    		//修改订单数据
    		$orderdata=array(
    			'is_pay' => 1,
    			'paytime'=>time(),
    			'status'=>1
    		);
    		$res = db(\tname::integral_order) -> where(array('order_number'=>$data['out_trade_no'])) -> update($orderdata);
    		
    		//积分变动
			dataChangeLog($uid, 'integral', 'integral', $orderinfo['openid'], '-' . $orderinfo['pay_integral'], $orderinfo['id'], '积分兑换');
    	}
    }
    

    
    public function couponupdate($uid){
    	$where['uid'] = $uid;
    	$where['is_publish'] = 1;
    	$where['is_hidden'] = 0;
    	$where['activityetime'] = array(array('gt',0),array('elt',time()));
    	db(\tname::coupon) -> where($where) -> update(array('is_publish'=>0));
    }
    
    public function mycouponupdate($uid){
    	$where['uid'] = $uid;
    	$where['status'] = 0;
    	$where['etime'] = array('elt',time());
    	db(\tname::coupon_mycoupon) -> where($where) -> update(array('status'=>-1));
    
    	if($this -> parameter['autodel']){
    		$autodel = $this -> parameter['autodel'];//设置几天自动过期
    		$where['uid'] = $uid;
    		$where['userhide'] = 0;
    		$where['status'] = array('in','-1,1');
    		$where['etime'] = array('elt',time() - $autodel * 86400);
    		db(\tname::coupon_mycoupon) -> where($where) -> update(array('userhide'=>1));
    	}
    }
}