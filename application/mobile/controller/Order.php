<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/9
 * Time: 23:37
 */
namespace app\mobile\controller;

use app\common\logic\OrderLogic;
use app\mobile\controller\Wechat;

class Order extends Base
{
    public  $vid_ids=[];
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $user = session('userinfo');
        $this->vid_ids=session('vip_ids');
        if (empty($user)) {
            $this->error('你还没有登录，请先登录');
        }

    }

    public function myorder()
    {
        $user = session('userinfo');
        $vip =db(\tname::vip)->where(array('id'=>$user['vip_id']))->find();
        $vip_ids = session('vip_ids');
        if(request()->post()){
            $OrderLogic = new OrderLogic();
            $pdata=input('');
            $vip_ids = session('vip_ids');
            $pdata['vip_id']=array('in',$vip_ids);
            $pdata['pagenum']=10;

            $list =$OrderLogic->getOrderList($vip_ids,$pdata['status'],$pdata['keyword'],$pdata['sort'],$pdata['asc'],$pdata['page'],$pdata['pagenum']);
            $this->assign('dataList', $list);
            $html = $this->fetch('order/ajaxdata');
            $attach = array(
                'total' => '',
                'page_size' => 10,
                'count' => count($list),
            );
            return ajaxSuccess($html, '', '', $attach);
        }

        $order_tab =array('全部','待付款','待发货','已发货','已完成','售后');

        $status = input('param.status',0);
        $this->assign('vip',$vip);
        $this->assign('status',$status);
        $this->assign('order_tab',$order_tab);
        $this->assign('title','我的订单');
        return $this->fetch();

    }
    /**
     * 订单详情
     */
    public function orderdetail(){
        $user = session('userinfo');
        $OrderLogic = new OrderLogic();
        $pdata=input('param.');
        $pdata['vip_id']=$user['vip_id'];
        $result =$OrderLogic->getOrderInfo($pdata['vip_id'],$pdata['order_id']);
        $ajaxdata = [
            'order' => $result,
        ];
        $this->assign('order',$result);
        $this->assign('title','订单详情');
        return $this->fetch();
    }


    /**
     * 添加订单
     * @return \think\response\Json
     */
    public function orderadd(){
        $user = session('userinfo');
        $pdata = input('post.');
        $pdata['vip_id']=$user['vip_id'];
        //生成订单
        $OrderLogic = new \app\common\logic\OrderLogic();
        $pdata['order_source']='mobile';
        $result = $OrderLogic->orderAdd($pdata);
        if($result['status']!=1){
            return json(ajaxFalse($result['msg']));
        }

        if($pdata['pay_type']=='alipay'){
            $url=url('order/order_pay',array('order_id'=>$result['order']['id'],'pay_type'=>'alipay'));
        }
        return json(ajaxSuccess($result['order'],$result['msg'],$url));
    }

    public function order_pay(){
        $user = session('userinfo');
        $pdata = input('param.');
        $pay_type = $pdata['pay_type'];
        $OrderLogic = new OrderLogic();
        $order =$OrderLogic->getOrderInfo($user['vip_id'],$pdata['order_id']);
        if($pay_type=='alipay'){
            $apipay = new \alipay\Wappay();
            $money = $order['pay_money'];
//            $money = 0.01;
            $params=array(
                'out_trade_no'=>$order['order_number'],
                'subject'=>'晓爱科技',
                'total_amount'=>$money,
                'return_url'=>get_domain().url('order/myorder'),
                'notify_url'=>get_domain().url('Special/indepNotify',array('uid'=>WID)),
            );
            $apipay::pay($params);
        }
    }

    /**
     * 取消订单
     */
    public function order_cancel(){
        $user = session('userinfo');
        $OrderLogic = new OrderLogic();
        $pdata=input('');
        $pdata['vip_id']=$user['vip_id'];
        $result = $OrderLogic->order_cancel($pdata['order_id']);
        if($result['status']!=1){
            return  json(ajaxFalse($result['msg']));
        }
        $order =$OrderLogic->getOrderInfo($pdata['vip_id'],$pdata['order_id']);
        return  json(ajaxSuccess($order,$result['msg']));
    }

    /**
     * 删除订单
     */
    public function order_del(){
        $OrderLogic = new OrderLogic();
        $pdata=input('');
        $result = $OrderLogic->order_del($pdata['order_id']);
        if($result['status']!=1){
            return  json(ajaxFalse($result['msg']));
        }
        return  json(ajaxSuccess('',$result['msg']));
    }

    /**
     * 订单确认收货
     */
    public function order_confirm(){
        $user = session('userinfo');
        $OrderLogic = new OrderLogic();
        $pdata=input('');
        $pdata['vip_id']=$user['vip_id'];
        $result = $OrderLogic->order_confirm($pdata['order_id']);
        if($result['status']!=1){
            return  json(ajaxFalse($result['msg']));
        }
        $order =$OrderLogic->getOrderInfo($pdata['vip_id'],$pdata['order_id']);
        return  json(ajaxSuccess($order,$result['msg']));
    }
    /**
     * 订单申请退款
     */
    public function order_refund(){
        $user = session('userinfo');
        $OrderLogic = new OrderLogic();
        $pdata=input('');
        $result = $OrderLogic->order_refund($pdata['order_id']);
        if($result['status']!=1){
            return  json(ajaxFalse($result['msg']));
        }
        $order =$OrderLogic->getOrderInfo($user['vip_id'],$pdata['order_id']);
        return  json(ajaxSuccess($order,$result['msg']));
    }
    /**
     * 评价
     */
    public function comment(){
        $user = session('userinfo');
        $pdata=input('');
        $OrderLogic = new OrderLogic();
        $order =$OrderLogic->getOrderInfo($user['vip_id'],$pdata['order_id']);
        if(request()->post()){
            foreach($pdata['comment'] as $val){
                $data['vip_id'] = $user['vip_id'];
                $data['nickname'] = $user['nickname'];
                $data['headimg'] = $user['headimgurl'];
                $data['order_id'] = $pdata['order_id'];
                $data['product_id'] = $val['goods_id'];
                $data['star'] = $val['star'];
                $data['content'] = $val['content'];
                $data['is_anonymous'] = $pdata['is_anonymous'];
                $images= array();
                foreach($val['imgs'] as $v ){
                    $path ="uploads";
                    $image= base64_image_content($v,$path);
                    if($image){
                        $images[]=$image;
                    }
                }
                $data['imgpath'] = serialize($images);
                $data['create_time'] = time();
                $datas[]=$data;
            }
            $res = db(\tname::mall_comment)->insertAll($datas);
            if (!$res) {
                return ajaxFalse();
            }
            $updataorder = array(
                'id'=>$pdata['order_id'],
                'is_comment'=>1,
            );
            $res1 = dataUpdate(\tname::mall_order, $updataorder);
            return ajaxSuccess('', '提交成功', url('Order/myorder'));
        }
        $this->assign('order',$order);
        return $this->fetch();
    }


    /**
     * 查看物流
     */
    public function wuliu(){
        $user = session('userinfo');
        $pdata=input('');
        $OrderLogic = new OrderLogic();
        $order =$OrderLogic->getOrderInfo($user['vip_id'],$pdata['order_id']);
        //获取物流
        $BasicLogic = new \app\common\logic\BasicLogic();
        $wuliu = $BasicLogic->queryExpress($order['express_id'],$order['express_number']);
        $express = db(\tname::express)->where(array('id' => $order['express_id']))->find();
        $this->assign('order',$order);
        $this->assign('wuliu',$wuliu);
        $this->assign('express',$express);
        return $this->fetch();
    }


    /**
     * 处理支付的反馈结果——订单支付完成    2018-10-19
     */
    public function indepNotify()
    {
        $uid = input('param.uid');
        $xml = file_get_contents('php://input');
        $data = xmltoarray($xml);
        $pay = db(\tname::weixin_pay)->where(array('uid' => $uid,'type'=>'applet'))->find();
        $wxpayparam = serializeMysql($pay['wxpay'], 1);
        $Wxpay = new \wechat\Wxpay();
        $sign = $Wxpay->checksign($data, $wxpayparam);
        $returnParameters = array();
        if ($sign == false) {
            $returndata = array(
                'return_code' => 'FAIL',
                'return_msg' => '签名失败'
            );
        } else {
            $returndata = array(
                'return_code' => 'SUCCESS',
                'return_msg' => 'OK'
            );
        }
        $xml = new \SimpleXMLElement('<xml></xml>');
        data2xml($xml, $returndata);
        $returnXml = $xml->asXML();
        echo $returnXml;
        apilog($uid, 'wechat', 'wxpaynotify-money', '', $data, $returndata);
        //$data['out_trade_no'] = '201711142845';
        $order = db(\tname::mall_order)->where('order_number', $data['out_trade_no'])->find();
        $OrderLogic = new OrderLogic();
        $OrderLogic->order_pay_sucsess($order);
    }








}