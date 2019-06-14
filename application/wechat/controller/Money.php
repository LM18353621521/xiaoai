<?php

namespace app\wechat\controller;

use SimpleXMLElement;

class Money extends Wechat
{

    /**
     * 余额明细   2017-10-15
     */
    public function index()
    {
        $userinfo = session('userinfo');
        if (request()->isPost()) {
            $pdata = input('post.');
            $where_l = array(
                'uid' => WID,
                'main_id' => $userinfo['openid'],
                'classify' => 'money'
            );
            $count = db(\tname::data_changelog)->where($where_l)->count();
            $dataList = db(\tname::data_changelog)->where($where_l)->order('id desc')->page($pdata['page'], 100)->select();
            $attach = array(
                'total' => $count
            );

            $this->assign('dataList', $dataList);
            $html = $this->fetch('money/ajaxdata');
            return ajaxSuccess($html, '', '', $attach);
        } else {
            $vip = db(\tname::vip)->where(array('uid' => WID, 'openid' => $userinfo['openid']))->find();
            $this->assign('vip', $vip);
            return $this->fetch();
        }
    }

    /**
     * 会员卡充值   2017-10-15
     */
    public function card()
    {
        $userinfo = session('userinfo');

        if (request()->isPost()) {
            $pdata = input('post.');
            $ordernumber = createOrdernumber(\tname::money_order);
            $data = array(
                'uid' => WID,
                'order_number' => $ordernumber,
                'openid' => $userinfo['openid'],
                'nickname' => $userinfo['nickname'],
                'card_id' => $pdata['card_id'],
            );
            if ($data['card_id']) {
                $card = db(\tname::money_card)->find($data['card_id']);
                $data['pay_money'] = $card['price'];
                $data['give_money'] = $card['give_money'];
                $data['card'] = serializeMysql($card);
            } else {
                if ($pdata['money'] <= 0) {
                    return ajaxFalse('请输入正确的金额');
                }
                $data['pay_money'] = $pdata['money'];
                $data['give_money'] = 0;
            }
            $res = dataUpdate(\tname::money_order, $data);
            if (!$res) {
                return ajaxFalse();
            }

            $url = [
                'notify_url' => get_domain() . '/wechat.php/Money/indepNotify/uid/' . WID,
                'success_url' => get_domain() . '/wechat.php/Money/index',
                'fail_url' => get_domain() . '/wechat.php/Money/index',
            ];
            $result = wxPay(WID, $userinfo['openid'], 0.01, $ordernumber, $url, '充值', 'wechat');

            return ajaxSuccess($result[1], '', '', [$result[0]['success_url'], $result[0]['fail_url']]);
        } else {
            $cardList = db(\tname::money_card)->where(array('uid' => WID, 'is_hidden' => 0, 'is_publish' => 1))->select();
            $vip = db(\tname::vip)->where(array('uid' => WID, 'openid' => $userinfo['openid']))->find();

            $this->assign('vip', $vip);
            $this->assign('cardList', $cardList);
            return $this->fetch();
        }
    }

    //余额提现   2018-02-05
    public function withdraw()
    {
        $userinfo = session('userinfo');

        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = WID;
            $data['openid'] = $userinfo['openid'];

            db()->startTrans();
            $where_vip = [
                'uid' => WID,
                'openid' => $userinfo['openid']
            ];
            $vip = db(\tname::vip)->where($where_vip)->find();
            if ($data['money'] < 1) {
                return ajaxFalse('请输入不小于1的金额');
            }
            $res = dataUpdate(\tname::money_withdraw, $data);
            if (!$res) {
                db()->rollback();
                return ajaxFalse();
            }

            $res1 = dataChangeLog(WID, 'money', 'withdraw', $userinfo['openid'], '-' . $data['money'], $res, '申请提现');
            if (!$res1[0]) {
                db()->rollback();
                return ajaxFalse($res1[1]);
            }
            db()->commit();
            return ajaxSuccess('', '提交成功', url('Money/index'));
        } else {
            $where_vip = [
                'uid' => WID,
                'openid' => $userinfo['openid']
            ];
            $vip = db(\tname::vip)->where($where_vip)->find();

            $this->assign('vip', $vip);
            return $this->fetch();
        }
    }

    //******************************************以下方法不支持微信环境*******************************************

    /**
     * 处理支付的反馈结果——余额充值    2017-10-15
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
        $order = db(\tname::money_order)->where('order_number', $data['out_trade_no'])->find();
        if ($order['is_pay'] != 1) {
            //记录微信支付返回结果
            apilog($uid, 'wechat', 'wxpaynotify-money', '', $data, $returndata);
            //修改订单数据
            $orderdata = array(
                'is_pay' => 1,
                'pay_time' => time()
            );
            $res = db(\tname::money_order)->where(array('order_number' => $order['order_number']))->update($orderdata);

            //余额变动
            dataChangeLog($uid, 'money', 'recharge', $order['openid'], $order['pay_money'] + $order['give_money'], $order['id'], '充值');
        }
    }
}
