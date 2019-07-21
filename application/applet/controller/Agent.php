<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/9
 * Time: 23:37
 */

namespace app\applet\controller;

use app\common\logic\AgentLogic;

class Agent extends Applet
{
    public function index()
    {
        $pdata = input('post.');
        $user = db(\tname::vip)->where(['uid' => WID, 'id' => $pdata['vip_id']])->find();
        $user['_mobile']=hidtel($user['mobile']);

        $config = tpCache('base');
        $webConfig = tpCache('web');
        $config['logo'] = imgurlToAbsolute($webConfig['logo']);
        $config['slogen'] = $webConfig['slogen'];
        //代理权益
        $article = db(\tname::topic)->find(2);
        $article['content'] = str_replace("<img ", "<img class='img_w' ", $article['content']);

        //代理信息
        $AgentLogic = new AgentLogic();
        $agent = $AgentLogic->agentInfo($user);
        $ajaxdata = [
            'user' => $user,
            'agent' => $agent,
            'article' => $article,
            'config' => $config,
        ];
        return json(ajaxSuccess($ajaxdata));
    }
    /**
     * 修改个人资料
     */
    public function userinfo(){
        $pdata = input('post.');
        $user = db(\tname::vip)->where(['id' => $pdata['vip_id']])->find();
        //代理信息
        $AgentLogic = new AgentLogic();
        $agent = $AgentLogic->agentInfo($user);
        $ajaxdata = [
            'user' => $user,
            'agent' => $agent,
        ];
        return json(ajaxSuccess($ajaxdata));
    }
    /**
     * 执行修改
     */
    public function  editInfo(){
        $pdata = input('post.');
        $user = db(\tname::vip)->where(['id' => $pdata['vip_id']])->find();
        $AgentLogic = new AgentLogic();
        $result = $AgentLogic->editInfo($user,$pdata);
        return json($result);
    }

    /**
     * 代理申请
     */
    public function apply()
    {
        $pdata = input('post.');
        $user = db(\tname::vip)->where(['id' => $pdata['vip_id']])->find();
        $agent = db(\tname::agent_user)->where(array('mobile' => $user['mobile']))->find();
        $xieyi = db(\tname::topic)->where(array('id' => 1))->find();
        $config = tpCache('base');
        $webConfig = tpCache('web');
        $config['logo'] = imgurlToAbsolute($webConfig['logo']);
        $config['agent_apply_bg'] = imgurlToAbsolute($config['agent_apply_bg']);
        $config['slogen'] = $webConfig['slogen'];
        $returnDate = [
            'user' => $user,
            'agent' => $agent,
            'xieyi' => $xieyi,
            'config' => $config,
        ];
        return json(ajaxSuccess($returnDate));
    }


    /**
     * 代理申请提交
     */
    public function apply_do()
    {
        $pdata = input('post.');
        $AgentLogic = new AgentLogic();
        $result = $AgentLogic->agentApply($pdata['vip_id'], $pdata);
        return json($result);
    }

    /**
     * 充值页面
     */
    public function recharge()
    {
        $pdata = input('post.');
        $user = db(\tname::vip)->where(['uid' => WID, 'id' => $pdata['vip_id']])->find();
        //代理信息
        $AgentLogic = new AgentLogic();
        $agent = $AgentLogic->agentInfo($user);

        //余额说明
        $article = db(\tname::topic)->find(3);
        $article['content'] = str_replace("<img ", "<img class='img_w' ", $article['content']);
        $moneyList = array(
            array('money' => 100),
            array('money' => 300),
            array('money' => 1500),
            array('money' => 1000),
            array('money' => 3000),
            array('money' => 5000),
        );
        $ajaxdata = [
            'user' => $user,
            'agent' => $agent,
            'article' => $article,
            'money' => $moneyList[0]['money'],
            'moneyList' => $moneyList,
        ];
        return json(ajaxSuccess($ajaxdata));
    }

    /**
     * 充值下单
     */
    public function orderadd()
    {
        $pdata = input('post.');
        $user = db(\tname::vip)->where(['uid' => WID, 'id' => $pdata['vip_id']])->find();
        $agent = db(\tname::agent_user)->where(array('mobile' => $user['mobile']))->find();
        if (empty($agent)) {
            return join(ajaxFalse('系统未检测到您的代理信息'));
        }
        $rechargeData = array(
            'agent_id' => $agent['id'],
            'vip_id' => $pdata['vip_id'],
            'pay_type' => 'wxpay',
            'order_sn' => createOrdernumber(\tname::agent_recharge, 'order_sn'),
            'pay_money' => $pdata['money'],
            'give_money' => 0,
            'create_time' => time(),
        );
        $res = dataUpdate(\tname::agent_recharge, $rechargeData);
        if (empty($agent)) {
            return join(ajaxFalse('充值失败，请稍后重试'));
        }
        $rechargeData['id'] = $res;
        $AgentLogic = new AgentLogic();
        $result = $AgentLogic->rechargePay($rechargeData,'applet');
        getFromId($pdata['vip_id'], $result[2]);
        $ajaxdata = array(
            'ret' => 1,
            'data' => $result[1],
            'msg' => "",
            'order' => $rechargeData,
            'attach' => ""
        );
//        $rechargeData['status']=0;
//        $res=$AgentLogic->rechargePaySuccess($rechargeData,'wxpay','000000');
        return json($ajaxdata);
    }


    /**
     * 余额记录
     */
    public function balance_log()
    {
        $pdata = input('post.');
        $AgentLogic = new AgentLogic();
        $dataList = $AgentLogic->getBalanceLog($pdata,$pdata['vip_id']);
        foreach ($dataList as &$val){
            $val['_create_time']=date('Y-m-d H:i:s',$val['create_time']);
        }
        return json(ajaxSuccess($dataList));
    }
    /**
     * 充值记录
     */
    public function recharge_log(){
        $pdata = input('post.');
        $AgentLogic = new AgentLogic();
        $dataList = $AgentLogic->getRechargeLog($pdata,$pdata['vip_id']);
        $statusList= array(
            '0'=>'未成功',
            '1'=>'充值成功',
            '2'=>'充值失败',
        );
        foreach ($dataList as &$val){
            $val['_create_time']=date('Y-m-d H:i:s',$val['create_time']);
        }
        return json(ajaxSuccess($dataList));
    }


    /**
     * 处理支付的反馈结果——订单支付完成
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
        apilog($uid, 'applet', 'wxpaynotify-rechange', '', $data, $returndata);
        $order = db(\tname::agent_recharge)->where('order_sn', $data['out_trade_no'])->find();
        $AgentLogic = new AgentLogic();
        $AgentLogic->rechargePaySuccess($order,'wxpay',$data['transaction_id']);
    }


}