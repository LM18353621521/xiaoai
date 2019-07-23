<?php
namespace app\mobile\controller;

use think\Controller;


class Base extends Controller
{
    public $session_id;
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
        header("Cache-control: private");  // history.back返回后输入框值丢失问题 参考文章 http://www.tp-shop.cn/article_id_1465.html  http://blog.csdn.net/qinchaoguang123456/article/details/29852881
        $this->session_id = session_id(); // 当前的 session_id
        define('SESSION_ID',$this->session_id); //将当前的session_id保存为常量，供其它方法调用

        //测试环境手动配置
//        $userinfo = array(
//            'vip_id'=>118,
//            'openid' => 'C4031E3A9AC944CF4B0889E887A852C8',
//            'mobile'=>17695514618,
//            'source'=>3,
//            'nickname' => 'PC端测试账号',
//            'sex' => 1,
//            'province' => '天津',        //省份
//            'city' => '南开',        //城市
//            'country' => '中国',        //国家
//            'language' => 'zh_CN',
//            'headimgurl' => 'http://wx.qlogo.cn/mmopen/IcgOoUqN7GwSjlHLJ52VmWLNrNiaEBRYhdpyWAuceAiaCkgGGnLKToUJIiaoXE1YTeUVjEpiaiajVoCFIKictpMJkfM2k6lgKkxMIH/0',
//            'unionid' => 'uniontest',    //一般无用
//        );
//        if($userinfo['mobile']){
//            $ids = db(\tname::vip)->where(array('mobile'=>$userinfo['mobile']))->column('id');
//        }else{
//            $ids=[$userinfo['vip_id']];
//        }
//        $vip_ids = implode(',',$ids);
//        session('vip_id',$userinfo['vip_id']);
//        session('vip_ids',$vip_ids);
//        session('userinfo', $userinfo);
        //获取参数配置
        $wxconfig = db(\tname::weixin_config)->where(['uid' => WID,'type'=>'wechat'])->find();
        $actionname = request()->action();

        $this->public_assign();
    }

    /**
     * 保存公告变量到 smarty中 比如 导航
     */
    public function public_assign()
    {
        $tpshop_config = array();
        $user = session('userinfo');
        $xa_config = db(\tname::config)->cache(true,'xa_config')->select();
        if($xa_config){
            foreach($xa_config as $k => $v)
            {
                if($v['name'] == 'hot_keywords'){
                    $xa_config['hot_keywords'] = explode('|', $v['value']);
                }
                $xa_config[$v['inc_type'].'_'.$v['name']] = $v['value'];
            }
        }
        $is_login = empty($user)?0:1;
        $this->assign('user', $user);
        $this->assign('is_login', $is_login);
        $this->assign('xa_config', $xa_config);
        $this->assign('signpackage', $this->getsignpackage());
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
