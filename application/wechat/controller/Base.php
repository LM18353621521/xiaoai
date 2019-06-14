<?php
namespace app\wechat\controller;

use think\Controller;


class Wechat extends Base
{

    public function _empty()
    {
        $this->error('当前页面不存在', 'Home/index');
    }

    /**
     * 构造函数    2017-10-15
     */
    protected function _initialize()
    {
        $memberid = 2;
        define('WID', $memberid);
        //获取参数配置
        $wxconfig = db(\tname::weixin_config)->where(['uid' => WID,'type'=>'wechat'])->find();
        $signature = input('get.signature');

        config('wx_name', $wxconfig['name']);
        $this->assign('signpackage', $this->getsignpackage());

        $actionname = request()->action();
        $index = strpos($actionname, 'indep');
        if (!$signature && ($index === false || $index != 0)) {
            $wxconfig['certified'] = 4;
            if ($wxconfig['certified'] == 2) {//订阅认证
                $openid = input('get.openid');
                if ($openid == null) {
                    $openid = session('openid');
                } else {
                    session('openid', $openid);
                }

                $userinfo = session('userinfo');
                if ($userinfo['openid'] == null) {
                    $access_token = getaccesstoken(WID,'wechat');
                    $User = controller('purewechat/User');
                    $userinfo = $User->getbyopenid($openid, $access_token);
                }

                session('userinfo', $userinfo);
                session('openid', $openid);

                if ($memberid !== '' && $userinfo['openid'] != null) {
                    $this->checkopenid($userinfo);
                }
                if ($wxconfig['certified'] == 1 && $openid == null) {
                    $url = get_url();
                    $rurl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $wxconfig['appid'] . '&redirect_uri=' . $url . '&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect';
                    redirect($rurl);
                }
            } elseif ($wxconfig['certified'] == 4) {  //服务认证
                $code = input('get.code');
                $userinfo = session('userinfo');

                //测试环境手动配置
                $userinfo = array(
                    'openid' => 'okuRis1pF6mqQXikrHdv07fBJiM4',
                    'nickname' => 'PC端测试账号',
                    'sex' => 1,
                    'province' => '天津',        //省份
                    'city' => '南开',        //城市
                    'country' => '中国',        //国家
                    'language' => 'zh_CN',
                    'headimgurl' => 'http://wx.qlogo.cn/mmopen/IcgOoUqN7GwSjlHLJ52VmWLNrNiaEBRYhdpyWAuceAiaCkgGGnLKToUJIiaoXE1YTeUVjEpiaiajVoCFIKictpMJkfM2k6lgKkxMIH/0',
                    'unionid' => 'uniontest',    //一般无用
                );
                session('userinfo', $userinfo);

                if ($userinfo['openid'] == null) {
                    if ($code) {    //code有值时获取到用户信息  没有值时获取不到
                        $userinfo = $this->getuserinfo($code);

                        session('userinfo', $userinfo);
                    } else {
                        $url = get_url();
                        $rurl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $wxconfig['appid'] . '&redirect_uri=' . $url . '&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect';
                        return $this->redirect($rurl);
                    }
                }
                if ($memberid !== '' && $userinfo['openid'] != null) {
                    $this -> checkopenid($userinfo);
                }

                session('userinfo', $userinfo);
            }

        }
    }

    /**
     * 根据openid和vipid判断是否生成新的用户    2017-10-15
     */
    public function checkopenid($userinfo)
    {
        $where_vip = array(
            'uid' => WID,
            'openid' => $userinfo['openid']
        );
        $vip = db(\tname::vip)->where($where_vip)->find();

        if (!$vip['id']) {
            $vipData = array(
                'uid' => WID,
                'openid' => $userinfo['openid'],
                'headimgurl' => $userinfo['headimgurl'],
                'nickname' => $userinfo['nickname'],
                'sex' => $userinfo['sex'],
                'city' => $userinfo['city'],
                'country' => $userinfo['country'],
                'province' => $userinfo['province'],
                'source' => 1,
                'createtime' => time(),
            );
            db(\tname::vip)->insert($vipData);
        } else {
            $data0 = array($vip['nickname'], $vip['headimgurl'], $vip['sex'], $vip['province'], $vip['city'], $vip['country']);
            $data1 = array($userinfo['nickname'], $userinfo['headimgurl'], $userinfo['sex'], $userinfo['province'], $userinfo['city'], $userinfo['country']);
            if ($data0 != $data1) {
                $vipData = array(
                    'nickname' => $userinfo['nickname'],
                    'headimgurl' => $userinfo['headimgurl'],
                    'sex' => $userinfo['sex'],
                    'province' => $userinfo['province'],
                    'city' => $userinfo['city'],
                    'country' => $userinfo['country']
                );
                db(\tname::vip)->where('id', $vip['id'])->update($vipData);
            }
        }
    }

    /**
     * 服务号获取用户OPENID    2017-10-15
     */
    public function getuserinfo($code)
    {
        //通过code换取网页授权access_token
        $weixinconfig = db(\tname::weixin_config)->where(['uid' => WID,'type'=>'wechat'])->find();

        $Token = new \wechat\Token();
        $data = $Token->getauthtoken($weixinconfig['appid'], $weixinconfig['appsecret'], $code, array('uid' => WID));
        $openid = $data['openid'];

        //拉取用户信息
        $User = new \wechat\User();
        $userinfo = $User->getuserinfo($openid, $data['access_token'], array('uid' => WID));
        return $userinfo;
    }


    /**
     * 生成signpackage JSSDK分享用    2017-10-15
     */
    public function getsignpackage()
    {
        $jsapiTicket = getjsapiticket(WID);

        $weixinconfig = db(\tname::weixin_config)->where(['uid' => WID,'type'=>'wechat'])->find();
        $Token = new \wechat\Token();
        $signPackage = $Token->getsignature($jsapiTicket);
        $signPackage['appid'] = $weixinconfig['appid'];

        return $signPackage;
    }


    /**
     * 处理图片    2017-10-15
     */
    public function getPath($imgPath, $id)
    {
        $path = './uploads/picture/uid' . WID . '/wechat/' . date('Ymd') . '/';
        if (!file_exists($path)) {
            $savepath = './uploads/picture/uid' . WID . '/wechat/' . date('Ymd') . '/';
            mkdir($savepath, 0777, true);
        }
        if ($imgPath) {
            $file = $path . time() . $id . '.jpeg';
            $filepath = '/uploads/picture/uid' . WID . '/wechat/' . date('Ymd') . '/' . time() . $id . '.jpeg';
            $base64 = base64_decode($imgPath);
            file_put_contents($file, $base64);
        }
        return $filepath;
    }


    /**
     * 微信下载图片保存到本地    2017-10-15
     */
    public function saveimg($mediaid)
    {
        $access_token = getaccesstoken(WID,'wechat');
        $path = './uploads/picture/uid' . WID . '/wechat/' . date('Ymd') . '/';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $code = createverifycode();
        $filename = time() . $code;
        $dir = $path . $filename . '.jpg';
        $Material = new \wechat\Material();
        $img = $Material->getmedia($mediaid, $access_token);
        if (!empty($img)) {
            @file_put_contents($dir, $img);
        }
        return substr($dir, 1);
    }
}
