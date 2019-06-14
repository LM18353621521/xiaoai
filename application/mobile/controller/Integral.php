<?php
namespace app\wechat\controller;

use SimpleXMLElement;

class Integral extends Base
{

    /**
     * 积分商城配置参数    2017-10-15
     */
    private $config = array(
        'can_use_money' => 1, //0不可以用钱抵用积分 1可以用钱抵用积分
        'integral_to_money' => 3, //1积分可用多少钱抵用
        'deliver' => [1 => '快递包邮', 2 => '上门自提'],
        'pay' => [1 => '微信支付', 2 => '余额支付'],
    );

    /**
     * 积分商城首页    2017-10-15
     */
    public function index()
    {
        $userinfo = session('userinfo');

        if (request()->isPost()) {
            $pdata = input('post.');
            $where_p = array(
                'uid' => WID,
                'is_publish' => 1,
                'is_hidden' => 0,
            );
            $count = db(\tname::integral_product)->where($where_p)->count();
            $dataList = db(\tname::integral_product)->where($where_p)->order('id desc')->page($pdata['page'], 20)->select();
            $attach = array(
                'total' => $count
            );
            $this->assign('dataList', $dataList);
            $html = $this->fetch('integral/ajaxdata');
            return ajaxSuccess($html, '', '', $attach);
        } else {
            $carousel = db(\tname::carousel)->where(array('uid' => WID, 'type' => 'integral'))->field('carousel')->find();
            $carousel['carousel'] = serializeMysql($carousel['carousel'], 1);

            $vip = db(\tname::vip)->where(array('uid' => WID, 'openid' => $userinfo['openid']))->find();

            $this->assign('vip', $vip);
            $this->assign('carousel', $carousel['carousel']);
            return $this->fetch();
        }
    }

    /**
     * 产品详情    2017-10-15
     */
    public function detail()
    {
        $userinfo = session('userinfo');
        if (request()->isPost()) {
            $pdata = input('post.');
            $res = $this->indepCanExchange(WID, $pdata['product_id'], $userinfo['openid']);
            if (!$res[0]) {
                return ajaxFalse($res[1]);
            }
            return ajaxSuccess('', '', '/wechat.php/integral/order/?id='. $pdata['product_id']);
        } else {
            $id = input('param.id');
            $product = db(\tname::integral_product)->find($id);
            $vip = db(\tname::vip)->where(array('uid' => WID, 'openid' => $userinfo['openid']))->find();
            $product['imgpath'] = serializeMysql($product['imgpath'], 1);
            foreach ($product['imgpath'] as $key => &$value) {
                $value = get_domain() . $value;
            }
            if ($this->config['can_use_money'] == 0 && $vip['integral'] < $product['integral']) {
                $product['can_exchange'] = 0;
            } else {
                $product['can_exchange'] = 1;
            }

            $this->assign('product', $product);
            return $this->fetch();
        }
    }

    /**
     * 提交订单    2017-10-15
     */
    public function order()
    {
        $userinfo = session('userinfo');
        if (request()->isPost()) {
            $pdata = input('post.');
            $product = db(\tname::integral_product)->find($pdata['product_id']);
            $pdata['uid'] = WID;
            $pdata['order_number'] = createOrdernumber(\tname::integral_order);
            $pdata['openid'] = $userinfo['openid'];
            $pdata['nickname'] = $userinfo['nickname'];
            $pdata['product_name'] = $product['name'];
            $pdata['product'] = serializeMysql($product);
            isset($pdata['pay']) || $pdata['pay'] = 0;
            $vip = db(\tname::vip)->where(array('openid' => $userinfo['openid']))->find();
            if ($this->config['can_use_money'] == 0 && $vip['integral'] < $product['integral']) {
                return ajaxFalse('您的积分不足');
            }

            if ($this->config['can_use_money'] == 0) {
                $pdata['pay_integral'] = $product['integral'];
            } else if ($this->config['can_use_money'] == 1) {
                if ($vip['integral'] < $product['integral']) {
                    $pdata['pay_integral'] = $vip['integral'];
                    $pdata['pay_money'] = ($product['integral'] - $vip['integral']) * $this->config['integral_to_money'];
                    if ($this->config['can_use_money'] == 1 && $pdata['pay'] == 2 && $vip['money'] < $pdata['pay_money']) {
                        return ajaxFalse('您的余额不足');
                    }
                } else {
                    $pdata['pay_integral'] = $product['integral'];
                }
            }

            $res = dataUpdate(\tname::integral_order, $pdata);
            if ($res) {
                if ($pdata['pay'] == 1) {
                    $url = [
                        'notify_url' => get_domain() . '/wechat.php/Integral/indepNotify/uid/' . WID,
                        'success_url' =>get_domain() . '/wechat.php/Integral/myorder',
                        'fail_url' => get_domain() . '/wechat.php/Integral/myorder',
                    ];
                    $result = wxPay(WID,$userinfo['openid'],0.01,$pdata['order_number'],$url,'积分兑换','wechat');

                    return ajaxSuccess($result[1], '', '', [$result[0]['success_url'], $result[0]['fail_url']]);
                } else if ($pdata['pay'] == 2) {
                    $this->indepOrderPay(WID, $res);
                    return ajaxSuccess('', '', url('Integral/myorder'));
                } else if ($pdata['pay'] == 0) {
                    $this->indepOrderPay(WID, $res);
                    return ajaxSuccess('', '', url('Integral/myorder'));
                }
            }
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::integral_product)->find($id);
            $vip = db(\tname::vip)->where(array('uid' => WID, 'openid' => $userinfo['openid']))->find();

            //我的积分
            if ($this->config['can_use_money'] == 1 && ($data['integral'] > $vip['integral'])) {
                $data['need_money'] = ($data['integral'] - $vip['integral']) * $this->config['integral_to_money'];
            } else {
                $data['need_money'] = 0;
            }

            $this->assign('vip', $vip);
            $this->assign('data', $data);
            $this->assign('config', $this->config);
            $this->assign('deliver', $this->config['deliver']);
            $this->assign('pay', $this->config['pay']);
            return $this->fetch();
        }
    }

    /**
     * 我的积分兑换   2017-10-15
     */
    public function myorder()
    {
        $userinfo = session('userinfo');

        if (request()->isPost()) {
            $pdata = input('post.');
            $where_m = array(
                'uid' => WID,
                'openid' => $userinfo['openid'],
                'status' => array('gt', 0),
                'user_hide' => 0,
                'is_hidden' => 0
            );
            $count = db(\tname::integral_order)->where($where_m)->count();
            $dataList = db(\tname::integral_order)->where($where_m)->order('id desc')->page($pdata['page'], 10)->select();
            foreach ($dataList as $key => &$value) {
                $value['product'] = serializeMysql($value['product'], 1);
            }
            $attach = array(
                'total' => $count
            );

            $this->assign('dataList', $dataList);
            $html = $this->fetch('integral/ajaxdata');
            return ajaxSuccess($html, '', '', $attach);
        } else {
            return $this->fetch();
        }
    }

    /**
     * 订单删除    2017-10-15
     */
    public function orderdel()
    {
        $order_id = input('post.order_id');
        $data = array(
            'id' => $order_id,
            'user_hide' => 1
        );
        $res = dataUpdate(\tname::integral_order, $data);
        if (!$res) {
            return ajaxFalse();
        }
        return ajaxSuccess();
    }

    /**
     * 订单确认收货    2017-10-15
     */
    public function orderfinish()
    {
        $order_id = input('post.order_id');
        $data = array(
            'id' => $order_id,
            'status' => 3
        );
        $res = dataUpdate(\tname::integral_order, $data);
        if (!$res) {
            return ajaxFalse();
        }
        return ajaxSuccess();
    }

    /**
     * 积分明细    2017-10-15
     */
    public function myintegral()
    {
        $userinfo = session('userinfo');
        if (request()->isPost()) {
            $pdata = input('post.');
            $where_l = array(
                'uid' => WID,
                'main_id' => $userinfo['openid'],
                'classify'=>'integral'
            );
            $count = db(\tname::data_changelog)->where($where_l)->count();
            $dataList = db(\tname::data_changelog)->where($where_l)->order('id desc')->page($pdata['page'], 100)->select();
            $attach = array(
                'total' => $count
            );

            $this->assign('dataList', $dataList);
            $html = $this->fetch('integral/ajaxdata');
            return ajaxSuccess($html, '', '', $attach);
        } else {
            $vip = db(\tname::vip)->where(array('uid' => WID, 'openid' => $userinfo['openid']))->find();
            $this->assign('vip', $vip);
            return $this->fetch();
        }
    }

    //******************************************以下方法不支持微信环境*******************************************
    /**
     * 判断能否兑换该产品    2017-10-15
     */
    public function indepCanExchange($uid, $product_id, $openid)
    {
        $vip = db(\tname::vip)->where(array('uid' => $uid, 'openid' => $openid))->find();
        $product = db(\tname::integral_product)->find($product_id);
        if ($product['is_publish'] == 0 || $product['is_hidden'] == 1) {
            return array(0, '您兑换的产品已经下架');
        }
        if ($this->config['can_use_money'] == 0) {
            if ($vip['integral'] < $product['integral']) {
                return array(0, '您的积分不足');
            }
        }
        return array(1);
    }


    /**
     * 处理支付的反馈结果——积分兑换    2017-10-15
     */
    public function indepNotify()
    {
        $uid = input('param.uid');
        $xml = file_get_contents('php://input');
        $data = xmltoarray($xml);
        $pay = db(\tname::weixin_pay)->where(array('uid' => $uid))->find();
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
        $xml = new SimpleXMLElement('<xml></xml>');
        data2xml($xml, $returndata);
        $returnXml = $xml->asXML();
        echo $returnXml;

        //$data['out_trade_no'] = '201711142845';
        $order = db(\tname::integral_order)->where('order_number', $data['out_trade_no'])->find();
        if ($order['is_pay'] != 1) {
            //记录微信支付返回结果
            apilog($uid, 'wechat', 'wxpaynotify-integral', '', $data, $returndata);

            $this->indepOrderPay($uid, $order['id']);
        }
    }

    /**
     * 订单支付    2017-10-15
     */
    public function indepOrderPay($uid, $orderid)
    {
        $order = db(\tname::integral_order)->find($orderid);

        $data = array(
            'id' => $orderid,
            'is_pay' => 1,
            'pay_time' => time(),
            'status' => 1
        );
        dataUpdate(\tname::integral_order, $data);
        dataChangeLog($uid, 'integral', 'integral', $order['openid'], '-' . $order['pay_integral'], $order['id'], '积分兑换');
        if ($order['pay'] == 2) {
            dataChangeLog($uid, 'money', 'integral', $order['openid'], $order['pay_money'], $order['id'], '积分兑换');
        }

    }
}
