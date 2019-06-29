<?php

namespace app\admin\controller;

use think\Controller;
use think\captcha\Captcha;
use think\Request;

class User extends Member
{
    /**
     * 登录    2017-10-15
     */
    public function index($username = '', $password = '', $verify = '')
    {
        if (request()->isPost()) {
            $captcha = new Captcha();
            $res1 = $captcha->check($verify);
            if (!$res1) {
//     			return array('ret'=>0,'msg'=>'验证码错误');
            }

            //登录是否成功
            $Login = new \wechat\Login();
            $loginerid = $Login->login($username, $password);
            if ($loginerid == -1) {
                return array('ret' => 0, 'msg' => '用户不存在或被禁用');
            } else if ($loginerid == -2) {
                return array('ret' => 0, 'msg' => '密码错误');
            }

            $loginer = db(\tname::system_loginer)->find($loginerid);
            session('loginer_auth', $loginer);

            $auth_group = db(\tname::auth_group)->where(array('id'=>$loginer['type']))->find();
            $this->handleLogin($loginer, $auth_group['rules']);
            $this->handMenu();
            $uid = $loginer['uid'];
            if ($uid > 0) {
                $res2 = $Login->usercheck($uid);
                if ($res2 > 0) {
                    $user = db(\tname::system_user)->find($uid); //登录用户
                    session('user_auth', $user);

                    //判断用户是否是普通
                    if ($user['usertype'] != 2 && $user['usertype'] != 1) {
                        $this->destory();
                        return array('ret' => 0, 'msg' => '权限错误');
                    }
                    return array('ret' => 1, 'url' => url('Index/index'));
                } else {
                    switch ($res2) {
                        case -1:
                            return array('ret' => 0, 'msg' => '用户不存在！');
                            break;
                        case -2:
                            return array('ret' => 0, 'msg' => '用户未激活或已禁用！');
                            break;
                        default:
                            return array('ret' => 0, 'msg' => '未知错误');
                            break;
                    }
                }
            } else {
                $this->destory();
                switch ($loginerid) {
                    case -1:
                        return array('ret' => 0, 'msg' => '用户不存在或被禁用');
                        break; //系统级别禁用
                    case -2:
                        return array('ret' => 0, 'msg' => '密码错误');
                        break;
                    default:
                        return array('ret' => 0, 'msg' => '未知错误');
                        break; // 0-接口参数错误（调试阶段使用）
                }
            }
        } else {
            return $this->fetch();
        }
    }

    public function handleLogin($admin, $actList)
    {
        $this->sessionRoleRights($admin, $actList);
        session('admin_id', $admin['id']);
        adminLog('后台登录');
    }

    public function handMenu(){
        $menuList =getMenuArr();
        session('menuList',$menuList);
    }

    public function sessionRoleRights($admin, $actList)
    {
        $roleRights = $actList;
        session('act_list', $roleRights);
    }

    /**
     * 退出登录    2017-10-15
     */
    public function logout()
    {
        $user = session('user_auth');
        if ($user['id']) {
            session('loginer_auth', null);
            session('user_auth', null);
            session('[destroy]');
            return $this->redirect(url('index'));
        } else {
            return $this->redirect(url('index'));
        }
    }

    /**
     * 销毁登录成功的信息    2017-10-15
     */
    protected function destory()
    {
        session('loginer_auth', null);
        session('user_auth', null);
        session('[destroy]');
    }

    /**
     * 登录页验证码    2017-10-15
     */
    public function verify()
    {
        $captcha = new Captcha();
        return $captcha->entry();
    }
}