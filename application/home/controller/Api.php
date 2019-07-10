<?php

namespace app\home\controller;

use think\Controller;

class Api extends Controller
{
    public function test_url()
    {
        $data = array(
            'mobile' => 123456789,
            'nickname' => date("Y-m-d H:i:s", time()),
        );

        $res = dataUpdate(\tname::vip, $data);
    }

    public function kdn()
    {
        $kdniao = new \kdnlogistics\Logistics();
        $pdata['shipping_code'] = "ZTO";
        $pdata['invoice_no'] = "75111715435209";
        $data['OrderCode'] = empty($pdata['order_sn']) ? date('YmdHis') : $pdata['order_sn'];
        $data['ShipperCode'] = $pdata['shipping_code'];
        $data['LogisticCode'] = $pdata['invoice_no'];

        $kdniao->logistics($data['OrderCode'], $data['ShipperCode'], $data['LogisticCode']);
    }

    /**
     * 检查是否已经登录
     */
    public function check_login()
    {
        $user = session('userinfo');
        if (empty($user)) {
            return ajaxFalse('您未登录，立即登录？', '', '', 2);
        }
        return ajaxSuccess();
    }

    /**
     * 查询物流
     */
    public function queryExpress()
    {
        $express_switch = tpCache('express.express_switch');
        $express_switch = 2;
        $pdata = input('post.');

        if ($express_switch == 1) {
//            require_once(PLUGIN_PATH . 'kdniao/kdniao.php');
            $pdata['shipping_code'] = "ZTO";
            $pdata['invoice_no'] = "75111715435209";
            $kdniao = new \kdniao\Kdniao();
            $data['OrderCode'] = empty($pdata['order_sn']) ? date('YmdHis') : $pdata['order_sn'];
            $data['ShipperCode'] = $pdata['shipping_code'];
            $data['LogisticCode'] = $pdata['invoice_no'];
            $res = $kdniao->getOrderTracesByJson(json_encode($data));
            $res = json_decode($res, true);
            if ($res['State'] == 3) {
                foreach ($res['Traces'] as $val) {
                    $tmp['context'] = $val['AcceptStation'];
                    $tmp['time'] = $val['AcceptTime'];
                    $res['data'][] = $tmp;
                }
                $res['status'] = "200";
            } else {
                $res['message'] = $res['Reason'];
            }
            return json($res);
        } else {
            $shipping_code = input('shipping_code');
            $invoice_no = input('invoice_no');
            if (empty($shipping_code) || empty($invoice_no)) {
                return json(['status' => 0, 'message' => '参数有误', 'result' => '']);
            }
            return json(queryExpress($shipping_code, $invoice_no));
        }
    }


    /*
 * 获取地区
 */
    public function getRegion()
    {
        $parent_id = input('param.parent_id/d');
        $selected = input('param.selected', 0);
        $data = db(\tname::region)->where("parent_id", $parent_id)->select();
        $html = '';
        if ($data) {
            foreach ($data as $h) {
                if ($h['id'] == $selected) {
                    $html .= "<option value='{$h['id']}' selected>{$h['name']}</option>";
                }
                $html .= "<option value='{$h['id']}'>{$h['name']}</option>";
            }
        }
        echo $html;
    }


    public function sendSmsCode()
    {
        $pdata = input('');
        $mobile = $pdata['mobile'];
        $type = input('type', 0);
        if (!isMobilephone($mobile)) {
            return json(ajaxFalse('请输入正确的手机号'));
        }
        //判断是否存在验证码
        $data = db(\tname::data_message)->where(array('mobile' => $mobile, 'error_code' => 0, 'type' => $type))->order('id DESC')->find();

        $sms_time_out = 60;
        //60秒以内不可重复发送
        if ($data && (time() - $data['create_time']) < $sms_time_out) {
            return json(ajaxFalse($sms_time_out . '秒内不允许重复发送'));
        }
        //绑定
        if ($type == 1) {
            $userInfo = session('userinfo');
            //检查是否已经被绑定
            $check_bind = db(\tname::vip)->where(array('mobile' => $mobile, 'source' => $userInfo['source']))->find();
            if ($check_bind) {
                return json(ajaxFalse('该手机号码已被绑定，请重新输入'));
            }
            $code = createverifycode(6);
            $content = "";
            $ajaxdata = [
                'check_mobile' => $mobile,
                'check_code' => $code
            ];
            session('codeinfo', $ajaxdata);
            $result = sendSmsjh($mobile, $content, $code,$type);//发送短信

            if ($result['error_code']) {
                return json(ajaxFalse('验证码发送失败，请稍后重试'));
            }
            $ajaxdata = [
                'check_mobile' => $mobile,
                'check_code' => $code
            ];
            session('codeinfo', $ajaxdata);
            return json(ajaxSuccess($ajaxdata, "验证码已发送至：" . $mobile . "，请注意查收"));
        }
        //注册
        if ($type == 2) {
            //检查是否已经被绑定
            $check_res = db(\tname::vip)->where(array('mobile' => $mobile, 'source' => 3))->find();
            if ($check_res) {
                return json(ajaxFalse('该手机号码已被注册，请重新输入'));
            }
            $code = createverifycode(6);
            $content = "";
            $result = sendSmsjh($mobile, $content, $code,$type);//发送短信
            if ($result['error_code']) {
                return json(ajaxFalse('验证码发送失败，请稍后重试'));
            }
            $ajaxdata = [
                'check_mobile' => $mobile,
                'check_code' => $code
            ];
            session('codeinfo', $ajaxdata);
            return json(ajaxSuccess($ajaxdata, "验证码已发送至：" . $mobile . "，请注意查收"));
        }
        //找回密码
        if ($type == 3) {
            $code = createverifycode(6);
            $content = "";
            $result = sendSmsjh($mobile, $content, $code,$type);//发送短信
            if ($result['error_code']) {
                return json(ajaxFalse('验证码发送失败，请稍后重试'));
            }
            $ajaxdata = [
                'check_mobile' => $mobile,
                'check_code' => $code
            ];
            session('codeinfo', $ajaxdata);
            return json(ajaxSuccess($ajaxdata, "验证码已发送至：" . $mobile . "，请注意查收"));
        }

        //手机端，电脑端绑定
        if ($type == 4) {
            $user = session('userinfo');
            if(empty($user)){
                return json(ajaxFalse('登录超时，请重新登录'));
            }
            if($user['mobile']){
                return json(ajaxFalse('您已经绑定了手机号码，无需再绑定'));
            }
            //检查是否已经被绑定
            $check_bind = db(\tname::vip)->where(array('mobile' => $mobile, 'source' => $user['source']))->find();
            if ($check_bind && $check_bind['id'] != $pdata['vip_id']) {
                return json(ajaxFalse('该手机号码已被绑定，请重新输入'));
            }

            $code = createverifycode(6);
            $content = "";
            $result = sendSmsjh($mobile, $content, $code,$type);//发送短信

            if ($result['error_code']) {
                return json(ajaxFalse('验证码发送失败，请稍后重试'));
            }
            $ajaxdata = [
                'check_mobile' => $mobile,
                'check_code' => $code
            ];
            session('codeinfo', $ajaxdata);
            return json(ajaxSuccess($ajaxdata, "验证码已发送至：" . $mobile . "，请注意查收"));
        }
        if($type==5){
            $user = session('userinfo');
            $hasOldMobile=db(\tname::vip)->where(array('mobile' => $mobile, 'source' => $user['source']))->find();
            if(empty($hasOldMobile)){
                return json(ajaxFalse('手机号码不正确'));
            }
            $code = createverifycode(6);
            $content = "";
            $result = sendSmsjh($mobile, $content, $code,$type);//发送短信
            if ($result['error_code']) {
                return json(ajaxFalse('验证码发送失败，请稍后重试'));
            }
            $ajaxdata = [
                'check_mobile' => $mobile,
                'check_code' => $code
            ];
            session('codeinfo', $ajaxdata);
            return json(ajaxSuccess($ajaxdata, "验证码已发送至：" . $mobile . "，请注意查收"));
        }
        if($type==6){
            $has_mobile = db(\tname::vip)->where(array('mobile'=>$mobile))->find();
            if($has_mobile){
                return json(ajaxFalse('该手机号码已被使用，请重新输入'));
            }
            $code = createverifycode(6);
            $content = "";
            $result = sendSmsjh($mobile, $content, $code,$type);//发送短信
            if ($result['error_code']) {
                return json(ajaxFalse('验证码发送失败，请稍后重试'));
            }
            $ajaxdata = [
                'check_mobile' => $mobile,
                'check_code' => $code
            ];
            session('codeinfo', $ajaxdata);
            return json(ajaxSuccess($ajaxdata, "验证码已发送至：" . $mobile . "，请注意查收"));
        }
    }

    /**
     * 提醒支付
     */
    public function taskMinute(){
        $OrderLogic = new \app\common\logic\OrderLogic();
        //未付款-提醒付款
        $OrderLogic->orderRemindPay();
        //未支付-自动取消
        $OrderLogic->orderAutoCancel();
    }


}