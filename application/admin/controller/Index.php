<?php

namespace app\admin\controller;

class Index extends Member
{

    /**
     * 主页面    2017-10-15
     */
    public function index()
    {
        if (UID) {
            $loginer = db(\tname::system_loginer)->find(LID);
            $this->assign('loginer', $loginer);
//            dump($loginer);exit;
            return $this->fetch();
        } else {
            return $this->redirect('User/login');
        }
    }

    /**
     * 修改密码  2017-10-15
     */
    public function changepasswd()
    {
        if (request()->isPost()) {
            $password = input('post.oldpasswd');
            $data['password'] = input('post.newpasswd');
            $repassword = input('post.confirmpasswd');
            if ($data['password'] !== $repassword) {
                return array('ret' => 0, 'msg' => '您输入的新密码与确认密码不一致');
            }
            if (strlen($data['password']) < 6) {
                return array('ret' => 0, 'msg' => '新密码的长度不能小于6位');
            }
            $Login = new \wechat\Login();
            $res = $Login->updateinfo(LID, $password, $data);
            if ($res > 0) {
                return array('ret' => 1, 'msg' => '修改密码成功！');
            } else {
                return array('ret' => 0, 'msg' => '更新密码出错');
            }
        } else {
            return $this->fetch();
        }
    }

    /**
     * 生成权限   2017-10-15
     */
    public function authcreate()
    {
        db(\tname::auth_rule)->where(array('id' => array('gt', 0)))->delete();
        $menu = config('menu');

        foreach ($menu as $k => $v) {
            foreach ($v['_child'] as $k1 => $v1) {
                foreach ($v1['_child'] as $k2 => $v2) {
                    $key = $k * 100 + $k1 * 10 + $k2;
                    $key < 9 && $key++;
                    $auth[] = [
                        'id' => $key,
                        'module' => 'member',
                        'type' => 2,
                        'name' => 'member.php/' . $v['group'] . '/' . $v2,
                        'title' => $v1['name'],
                        'group_name' => $v['group'],
                        'control_name' => $v2,
                        'group' => $v['name'],
                        'status' => 1,
                    ];
                }
            }
        }

        db(\tname::auth_rule)->insertAll($auth);
        dump('success');
    }


    /**
     * 同步微信公众号中的已关注用户
     * @param string $nextOpenid
     * @throws \think\Exception
     */
    public function getUserList($nextOpenid = '')
    {
        ini_set('max_execution_time', '0');
        $uid = 2;
        $access_token = getaccesstoken($uid);
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=" . $access_token . '&next_openid=' . $nextOpenid;
        $result = https_request($url);
        if (array_key_exists('data', $result)) {
            foreach ($result['data']['openid'] as $key => $value) {
                $User = new \wechat\User();
                $vipData = $User->getbyopenid($value, $access_token, array('uid' => $uid));
                $vipData['uid'] = $uid;
                $vipData['source'] = 1;
                $vipData['create_time'] = time();
                $vip = db(\tname::vip)->where('openid', $value)->count();

                if (!empty($vip)) {
                    db(\tname::vip)->where('openid', $value)->update($vipData);
                } else {
                    db(\tname::vip)->insert($vipData);
                }
            }
            //一次拉取调用最多拉取10000个关注者的OpenID
            if ($result['count'] >= 10000) {
                $this->getUserList($result['next_openid']);
            }
        } else {
            dump($result);
        }
    }
}