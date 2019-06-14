<?php
namespace app\applet\controller;

use think\Controller;

class Applet extends Controller
{
    protected function _initialize()
    {
        $memberid = 2;
        define('MID', $memberid);    //用户账号id，不能叫UID是因为会和登录的用户的UID重复
        define('WID', MID);     //用户的公众账号id，目前是一个用户一个账号的情况，所以是WID == MID
    }

    //小程序获取用户OPENID    2018-02-28
    public function getopenid()
    {
        $data = input('post.');
        $share_id=$data['share_id'];
        $config = db(\tname::weixin_config)->where(['uid' => WID,'type'=>'applet'])->find();
        $appid = $config['appid'];
        $secret = $config['appsecret'];
        $js_code = $data['code'];
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$secret&js_code=$js_code&grant_type=authorization_code";

        $result = https_request($url);
        apilog(WID, 'applet', 'getopenid', $url, '', $result);

        $vip = db(\tname::vip)->where(['uid' => WID, 'openid' => $result['openid']])->find();
        if (empty($vip)) {
            $data = [
                'uid' => WID,
                'openid' => $result['openid'],
                //'unionid' 		=> $result['unionid'],//批注：2017-09-30小程序和公众号绑定的，必须先进入公众号，小程序才能获取到unionid
                'source' => 2,
                'subscribe' => 1,
                'create_time' => time(),
                'subscribe_time' => time(),
            ];
            $res = dataUpdate(\tname::vip, $data);
            $vip = db(\tname::vip)->where(['uid' => WID, 'openid' => $result['openid']])->find();
            if($res){
                //会员统计
                addStatistic(2, 'vip', 'applet', 1);
                //建立三级分销关系
                if ($share_id) {
                    $first_vip = db(\tname::vip)->where(array('id' => $share_id))->find();
                }else{
                    $first_vip['id'] = 0;
                }
                distributionBuildrelation(WID, $vip['id'], $first_vip['id']);
            }
        }

        $result['vip_id']=$vip['id'];

        if($vip['mobile']){
            $ids = db(\tname::vip)->where(array('mobile'=>$vip['mobile']))->column('id');
        }else{
            $ids=[$vip['id']];
        }
        $vip_ids = implode(',',$ids);
        $result['vip_ids']=$vip_ids;
        return json(ajaxSuccess($result));
    }

    //根据openid和vipid判断是否生成新的用户    2018-02-28
    public function checkopenid()
    {
        $data = input('post.');

        $vip = db(\tname::vip)->where(['uid' => WID, 'openid' => $data['openid']])->find();
        if (empty($vip)) {
            $data['source'] = 2;
            $data['create_time'] = time();
            $data['subscribe_time'] = time();
            $data['subscribe'] = 1;
            $res=dataUpdate(\tname::vip, $data);
            $vip = db(\tname::vip)->where(['uid' => WID, 'openid' => $data['openid']])->find();
            if($res){
                //会员统计
                addStatistic(2, 'vip', 'applet', 1);
                //建立三级分销关系
                if (isset($data['share_id']) && $data['share_id']) {
                    $first_vip = db(\tname::vip)->where(array('id' => $data['share_id']))->find();
                }else{
                    $first_vip['id'] = 0;
                }
                distributionBuildrelation(WID, $vip['id'], $first_vip['id']);
            }

        } else {
            $vip_cur = db(\tname::vip)->where(['uid' => WID, 'openid' => $data['openid']])->field('openid,nickname,headimgurl,country,province,city,sex')->find();
            if ($data != $vip_cur) {
                $data['id'] = $vip['id'];
                dataUpdate(\tname::vip, $data);
            }
        }
    }
    /**
     * 检查是否绑定手机号
     */
    function check_bind_mobile(){
        $pdata = input('post.');
        $vip = db(\tname::vip)->where(['uid' => WID, 'id' => $pdata['vip_id']])->find();
        $ajaxdata = [
            'vip' => $vip,
        ];
        return json(ajaxSuccess($ajaxdata));
    }

    /**
     * 生成带参数小程序码-第1种
     * 总共生成的码数量限制为100,000
     * @param $appletcode 生成的二维码需要保存的路径 $data 二维码参数，参照小程序文档
     * @批注 2017-09-29 生成的二维码宽度最小为280px
     */
    public function createappletcode1($uid, $appletcode, $data)
    {
        $access_token = getaccesstoken($uid,'applet');
        $url = "https://api.weixin.qq.com/wxa/getwxacode?access_token=$access_token";
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $timeout = 5;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $img = curl_exec($ch);
        curl_close($ch);

        $fp2 = @fopen($appletcode, 'a');
        fwrite($fp2, $img);
        fclose($fp2);
        unset($img, $url);
    }


    /**
     * 生成带参数小程序码-第2种
     * @param $appletcode 生成的二维码需要保存的路径 $data 二维码参数，参照小程序文档
     * @批注 2017-09-29 只有有正式版了才会合成成功，否则图片会报错
     */
    public function createappletcode2($uid, $appletcode, $data)
    {
        $access_token = getaccesstoken($uid,'applet');
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=$access_token";
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $timeout = 5;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $img = curl_exec($ch);
        curl_close($ch);

        $fp2 = @fopen($appletcode, 'a');
        fwrite($fp2, $img);
        fclose($fp2);
        unset($img, $url);
    }

    /**
     * 生成小程序二维码
     * 总共生成的码数量限制为100,000
     * @param $appletcode 生成的二维码需要保存的路径 $data 二维码参数，参照小程序文档
     */
    public function createappletqrcode($uid, $appletcode, $data)
    {
        $access_token = getaccesstoken($uid,'applet');
        $url = "https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=$access_token";
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $timeout = 5;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $img = curl_exec($ch);
        curl_close($ch);

        $fp2 = @fopen($appletcode, 'a');
        fwrite($fp2, $img);
        fclose($fp2);
        unset($img, $url);
    }

    /**
     * 生成带参数小程序码-第2种
     * @param $appletcode 生成的二维码需要保存的路径 $data 二维码参数，参照小程序文档
     * @批注 2017-09-29 只有有正式版了才会合成成功，否则图片会报错
     */
    public function create_user_good_qrcode($uid, $appletcode, $data)
    {
        $access_token = getaccesstoken($uid,'applet');
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=$access_token";
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $timeout = 5;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $img = curl_exec($ch);
        curl_close($ch);
        $fp2 = @fopen($appletcode, 'a');
        fwrite($fp2, $img);
        fclose($fp2);
        unset($img, $url);
    }


    /**
     * 下载图片保存到本地    2016-03-11
     */
    public function saveimg()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('photos');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'picture' . DS . 'uid' . WID . DS . 'applet');
            if ($info) {
                $savename = '/uploads/picture/uid' . WID . '/applet/' . str_replace('\\', '/', $info->getSaveName());
                return json(ajaxSuccess($savename));
            } else {
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }
    }
}
