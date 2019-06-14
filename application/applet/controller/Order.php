<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/9
 * Time: 23:37
 */
namespace app\applet\controller;

use app\common\logic\OrderLogic;

class Order extends Applet
{
    public function myorder()
    {
        $OrderLogic = new OrderLogic();
        $pdata=input('');
        $result =$OrderLogic->getOrderList($pdata['vip_ids'],$pdata['status'],$pdata['keyword'],$pdata['sort'],$pdata['asc'],$pdata['page'],$pdata['pagenum']);
        return json(ajaxSuccess($result));
    }
    /**
     * 订单详情
     */
    public function orderdetail(){
        $OrderLogic = new OrderLogic();
        $pdata=input('');
        $result =$OrderLogic->getOrderInfo($pdata['vip_id'],$pdata['order_id']);
        $ajaxdata = [
            'order' => $result,
        ];
        return json(ajaxSuccess($ajaxdata));
    }


    /**
     * 添加订单
     * @return \think\response\Json
     */
    public function orderadd(){
        $pdata = input('post.');
        //生成订单
        $OrderLogic = new \app\common\logic\OrderLogic();
        $pdata['order_source']='applet';
        $result = $OrderLogic->orderAdd($pdata);
        if($result['status']!=1){
            return json(ajaxFalse($result['msg']));
        }
        return json(ajaxSuccess($result['order'],$result['msg']));
    }



    public function order_pay(){
        $pdata = input('post.');
        $OrderLogic = new OrderLogic();
        $pdata['pay_type']="wxpay";

        $order =$OrderLogic->getOrderInfo($pdata['vip_id'],$pdata['order_id']);
        $order['status']=1;
        $order['pay_time']=date("Y-m-d H:i:s",time());

        $result = $OrderLogic->orderPay($pdata);
        $ajaxdata = array(
            'ret' => 1,
            'data' => $result[1],
            'msg' => "",
            'order'=>$order,
            'attach' => ""
        );
        return json($ajaxdata);
    }

    /**
     * 取消订单
     */
    public function order_cancel(){
        $OrderLogic = new OrderLogic();
        $pdata=input('');
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
        $OrderLogic = new OrderLogic();
        $pdata=input('');
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
        $OrderLogic = new OrderLogic();
        $pdata=input('');
        $result = $OrderLogic->order_refund($pdata['order_id']);
        if($result['status']!=1){
            return  json(ajaxFalse($result['msg']));
        }
        $order =$OrderLogic->getOrderInfo($pdata['vip_id'],$pdata['order_id']);
        return  json(ajaxSuccess($order,$result['msg']));
    }

    /**
     * 获取评价商品
     */
    public function comment(){
        $pdata=input('');
        $orderlog= db(\tname::mall_orderlog)->where(array('order_id'=>$pdata['order_id']))->field('id,goods_id,goods_name,coverimg')->select();
        $ajaxdata = [
            'orderlog' => $orderlog,
        ];
        return json(ajaxSuccess($ajaxdata));
    }
    public function comment_do(){
        $pdata=input('');
        $orderlog = json_decode($pdata['orderlog'],true);
        $user = db(\tname::vip)->where(array('id'=>$pdata['vip_id']))->find();
        $datas=[];
        foreach($orderlog as $val){
            $data['vip_id'] = $pdata['vip_id'];
            $data['nickname'] = $user['nickname'];
            $data['headimg'] = $user['headimgurl'];
            $data['order_id'] = $pdata['order_id'];
            $data['product_id'] = $val['goods_id'];
            $data['star'] = $val['star'];
            $data['content'] = $val['content'];
            $data['is_anonymous'] = $pdata['niming'];
            $data['imgpath'] = serialize($val['imgs_save']);
            $data['create_time'] = time();
            $datas[]=$data;
        }
        $res = db(\tname::mall_comment)->insertAll($datas);

        $updataorder = array(
            'id'=>$pdata['order_id'],
            'is_comment'=>1,
        );
        $res1 = dataUpdate(\tname::mall_order, $updataorder);

        if($res){
            return  json(ajaxSuccess('提交成功！'));
        }else{
            return  json(ajaxFalse('提交失败'));
        }
    }
    /**
     * 查看物流
     */
    public function  wuliu(){
        $OrderLogic = new OrderLogic();
        $pdata=input('');
        $pdata['order_id']=54;
        $pdata['vip_id']=40;
        $result =$OrderLogic->getOrderInfo($pdata['vip_id'],$pdata['order_id']);

        //获取物流
        $BasicLogic = new \app\common\logic\BasicLogic();
        $wuliu = $BasicLogic->queryExpress($result['express_id'],$result['shipping_sn']);
        dump($wuliu);

        $ajaxdata = [
            'order' => $result,
        ];
        return json(ajaxSuccess($ajaxdata));
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
        $OrderLogic->order_pay_sucsess($order,$data['transaction_id']);
    }








}