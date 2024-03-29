<?php

namespace app\home\controller;

use think\Controller;
use \qqlogin\QC;
use \qqlogin\Oauth;
use \weixinlogin\WeixinLogin;

class Login extends Controller
{
// 处理qq登录
    public function qq_login()
    {
        $qq = new QC();
        $qq = new Oauth();
        $url = $qq->qq_login();
        $this->redirect($url);
    }

    // qq登录回调函数
    public function qqcallback()
    {
        $qq = new QC();
        $qq->qq_callback();
        $openid = $qq->get_openid();
        $userinfo['openid'] = $openid;
        $qq = new QC(session('access_token'), session('openid'));
        $datas = $qq->get_user_info();

        if ($datas['ret'] == 0) {
            $userinfo['nickname'] = $datas['nickname'];
            $userinfo['headimgurl'] = $datas['figureurl_2'];
            $userinfo['sex'] = $datas['gender'] == "女" ? 2 : 1;
            $userinfo['province'] = $datas['province'];
            $userinfo['city'] = $datas['city'];
        }
        $where_vip = array(
            'uid' => 2,
            'source' => 5,
            'openid' => $userinfo['openid']
        );
        $vip = db(\tname::vip)->where($where_vip)->find();

        if (!$vip['id']) {
            $userinfo['create_time'] = time();
            $userinfo['source'] = 5;
            $userinfo['uid'] = 2;
            $res = db(\tname::vip)->insert($userinfo);
        } else {
//            $data0 = array($vip['nickname'], $vip['headimgurl'], $vip['sex'], $vip['province'], $vip['city'], $vip['country']);
//            $data1 = array($userinfo['nickname'], $userinfo['headimgurl'], $userinfo['sex'], $userinfo['province'], $userinfo['city']);
//            if ($data0 != $data1) {
//                $vipData = array(
//                    'nickname' => $userinfo['nickname'],
//                    'headimgurl' => $userinfo['headimgurl'],
//                    'sex' => $userinfo['sex'],
//                    'province' => $userinfo['province'],
//                    'city' => $userinfo['city'],
//                );
//                db(\tname::vip)->where('id', $vip['id'])->update($vipData);
//            }
        }
        $vip = db(\tname::vip)->where($where_vip)->find();
        $this->login_session($vip);
        $this->redirect(url('Home/Index/index'));
//        $this->success('登陆成功', url('Home/Index/index'));
    }

    public function wx_login()
    {

    }

    public function weixincallback()
    {
        $weixin = new WeixinLogin();
        //传入授权临时票据（code）
        $code = $_GET['code'];
        if (!$code) {
            $code = $weixin->getcode();
        }
        $oauth2_info = $weixin->oauth2_access_token($code);
        $userinfo = $weixin->oauth2_get_user_info($oauth2_info['access_token'], $oauth2_info['openid']);

        $where_vip = array(
            'uid' => 2,
            'source' => 4,
            'openid' => $userinfo['openid']
        );
        $vip = db(\tname::vip)->where($where_vip)->find();

        if (!$vip['id']) {
            $userinfo['create_time'] = time();
            $userinfo['source'] = 4;
            $userinfo['uid'] = 2;
            $res = db(\tname::vip)->insert($userinfo);
        }

        $vip = db(\tname::vip)->where($where_vip)->find();
        $this->login_session($vip);
        $this->redirect(url('Home/Index/index'));
//        $this->success('登陆成功', url('Home/Index/index'));
    }


    public function login()
    {
        if (request()->isPost()) {
            $mobile = input('mobile');
            $password = input('password');
            if (!$mobile || !$password) {
                return ajaxFalse("请填写账号或密码");
            }
            $user = db(\tname::vip)->where(array('source' => 3, 'mobile' => $mobile, 'password' => xa_encrypt($password)))->find();
            if (empty($user)) {
                return ajaxFalse("账号或密码不正确");
            }
            $this->login_session($user);
            return ajaxSuccess("", '登录成功！');
        }
    }


    public function register()
    {
        if (request()->isPost()) {
            $mobile = input('username');
            $password = input('password');
            $code = input('code');
            $codeinfo = session('codeinfo');
            if (!$mobile) {
                return ajaxFalse("请填写手机号码");
            }
            if (!$code) {
                return ajaxFalse("请填写验证码");
            }
            if (!$password) {
                return ajaxFalse("请填写密码");
            }
//            if ($mobile != $codeinfo['check_mobile']) {
//                return ajaxFalse("您输入的手机号不正确");
//            }
//            if ($code != $codeinfo['check_code']) {
//                return ajaxFalse("您输入的验证码不正确");
//            }
            if (strlen($password) < 6) {
                return ajaxFalse("密码长度不能小于6位");
            }

            //检查是否已经被绑定
            $check_reg = db(\tname::vip)->where(array('mobile' => $mobile, 'source' => 3))->find();
            if ($check_reg) {
                return ajaxFalse("该手机号已被注册");
            }

            $data = array(
                'nickname' => "XA_" . rand(10000, 99909) . substr($mobile, 7, 12),
                'mobile' => $mobile,
                'password' => xa_encrypt($password),
                'source' => 3,
                'create_time' => time(),
                'uid' => 2,
            );
            $res = dataUpdate(\tname::vip, $data);

            if (!$res) {
                return ajaxFalse("注册失败");
            }
            session('codeinfo', null);
            $user = db(\tname::vip)->where(array('mobile' => $mobile, 'source' => 3))->find();
            $this->login_session($user);
            return ajaxSuccess("", '恭喜您，注册成功！');
        }
    }


    public function login_out()
    {
        if (request()->isPost()) {
            session('vip_id', null);
            session('vip_ids', null);
            session('userinfo', null);
            setcookie('is_bind',0);
            setcookie('bind_tips',0);
            return ajaxSuccess("", '退出成功！');
        }
    }

    private function login_session($vip)
    {
        if ($vip['mobile']) {
            $ids = db(\tname::vip)->where(array('mobile' => $vip['mobile']))->column('id');
            setcookie('is_bind',1);
        } else {
            $ids = [$vip['id']];
        }
        $vip['vip_id'] = $vip['id'];
        $vip['vip_ids'] = $ids;
        session('vip_id', $vip['id']);
        session('vip_ids', $ids);
        session('userinfo', $vip);
    }

    /**
     * 验证找回密码手机
     */
    public function check_find_mobile()
    {
        $mobile = input('mobile');
        //判断是否存在
        $check_mobile = db(\tname::vip)->where(array('mobile' => $mobile, 'source' => 3))->find();
        if (empty($check_mobile)) {
            return ajaxFalse("未查询到该手机号码");
        }
        return ajaxSuccess();
    }

    public function check_find_code()
    {
        return ajaxSuccess();
        $mobile = input('username');
        $password = input('password');
        $code = input('code');
        $codeinfo = session('codeinfo');
        //判断是否存在
        if ($mobile != $codeinfo['check_mobile']) {
            return ajaxFalse("手机号错误");
        }
        if ($code != $codeinfo['check_code']) {
            return ajaxFalse("验证码错误");
        }
        return ajaxSuccess();
    }

    /**
     * 找回密码
     */
    public function find_password()
    {
        $mobile = input('mobile');
        $password = input('password');
        $code = input('code');
        $codeinfo = session('codeinfo');
        if (!$password) {
            return ajaxFalse("请填写密码");
        }
        //判断是否存在
        if ($mobile != $codeinfo['check_mobile']) {
            return ajaxFalse("手机号错误");
        }
        if ($code != $codeinfo['check_code']) {
            return ajaxFalse("验证码错误");
        }
        $check_mobile = db(\tname::vip)->where(array('mobile' => $mobile, 'source' => 3))->find();
        if (empty($check_mobile)) {
            return ajaxFalse("未查询到该手机号码");
        }
        if (strlen($password) < 6) {
            return ajaxFalse("密码长度不能小于6位");
        }
        $res = db(\tname::vip)->where(array('id' => $check_mobile['id'], 'source' => 3))->update(array('password' => xa_encrypt($password)));
        if (!$res) {
            return ajaxFalse("密码修改失败");
        }
        return ajaxSuccess("", '密码修改成功');
    }


    /**
     * @return \think\response\Json
     */
    public function bind_mobile()
    {
        $user = session('userinfo');
        $mobile = input('mobile');
        $code = input('code');
        $codeinfo = session('codeinfo');
        if(empty($user)){
            return json(ajaxFalse('登录超时，请重新登录'));
        }
        if($user['mobile']){
            return json(ajaxFalse('您已经绑定了手机号码，无需再绑定'));
        }
        //判断是否存在
        if ($mobile != $codeinfo['check_mobile']) {
            return ajaxFalse("手机号错误");
        }
        if ($code != $codeinfo['check_code']) {
            return ajaxFalse("验证码错误");
        }

        //检查是否已经被绑定
        $check_bind = db(\tname::vip)->where(array('mobile' => $mobile, 'source' => $user['source']))->find();
        if ($check_bind) {
            return json(ajaxFalse('该手机号码已被绑定'));
        }

        $data = array(
            'id' => $user['vip_id'],
            'mobile' => $mobile,
        );
        $res = dataUpdate(\tname::vip, $data);
        if (!$res) {
            return json(ajaxFalse('绑定失败，请稍后重试'));
        }
        $this->login_session($user);
        return json(ajaxSuccess([], "绑定成功！"));
    }


}