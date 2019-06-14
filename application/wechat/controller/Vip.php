<?php
namespace app\wechat\controller;

class Vip extends Wechat
{
    public function  test(){
//        $res=sendmessage(WID, "18353621521", "您的短信验证码为：807896，请勿泄漏", 'verifycode');

        $result = sendSmsAli(WID, "18353621521", "您的短信验证码为：807896，请勿泄漏", 'verifycode');

        dump($result);
        dump(json_encode($result,true));


        die();
        $province =db(\tname::region)->where(array('level'=>1))->order('code')->select();
//        dump($province);

        $json_province ="";
        foreach($province as $key=> $val){
            $json_province[$key]['text']=$val['name'];
            $json_province[$key]['value']=$val['code'];

            $data['region_id']=$val['id'];
            $data['region_code']=$val['code'];

            $res =dataUpdate(\tname::config_freight,$data);

        }
//        dump($json_province);
        $json_province= json_encode($json_province,JSON_UNESCAPED_UNICODE);

        $city =db(\tname::region)->where(array('level'=>2))->select();
        $json_city =[];

        foreach($province as  $valp){
            $arr =[];
            $i=0;
            foreach($city as $key=> $valc){
                if($valp['id']==$valc['parent_id']){
                    $arr[$i]['text']=$valc['name'];
                    $arr[$i]['value']=$valc['code'];
                    $json_city[$valp['code']]=$arr;
                    $i++;
                }

            }
        }

//        dump($json_city);
        $json_city= json_encode($json_city,JSON_UNESCAPED_UNICODE);
//        dump($json_city);

        $area =db(\tname::region)->where(array('level'=>3))->select();
        $json_area =[];

        foreach($city as  $valc){
            $arr =[];
            $i=0;
            foreach($area as $key=> $vala){
                if($valc['id']==$vala['parent_id']){
                    $arr[$i]['text']=$vala['name'];
                    $arr[$i]['value']=$vala['code'];
                    $json_area[$valc['code']]=$arr;
                    $i++;
                }

            }
        }

        dump($json_area);
        $json_area= json_encode($json_area,JSON_UNESCAPED_UNICODE);
        dump($json_area);



    }

    /**
     * 个人中心    2017-10-15
     */
    public function index()
    {
        $userinfo = session('userinfo');

        $where_vip = array(
            'uid' => WID,
            'openid' => $userinfo['openid']
        );
        $vip = db(\tname::vip)->where($where_vip)->find();

        $where_mycoupon = array(
            'uid' => WID,
            'openid' => $userinfo['openid'],
            'status' => 0
        );
        $number = db(\tname::coupon_mycoupon)->where($where_mycoupon)->count();

        $this->assign('vip', $vip);
        $this->assign('number', $number);

        return $this->fetch();
    }

    /**
     * 我的信息    2017-10-15
     */
    public function myinfo()
    {
        $userinfo = session('userinfo');

        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = WID;
            $data['openid'] = $userinfo['openid'];
            if (!isMobilephone($data['mobilephone'])) {
                return ajaxFalse('请输入正确的手机号');
            }

            $res = dataUpdate(\tname::vip_info, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess('', '', url('Vip/index'));
        } else {
            $where_v['a.uid'] = WID;
            $where_v['a.openid'] = $userinfo['openid'];
            $info = db(\tname::vip)->alias('a')
                ->join('wechat_' . \tname::vip_info . ' i', 'a.openid = i.openid', 'left')
                ->field('i.*,a.headimgurl headimgurl,a.openid openid')
                ->where($where_v)->find();

            $this->assign('info', $info);
            return $this->fetch();
        }
    }

    /**
     * 注册样式0 2017-10-15
     */
    public function register()
    {
        $userinfo = session('userinfo');
        if (request()->isPost()) {
            $code = input('post.code');
            $mobilephone = input('post.mobilephone');

            $checkinfo = session('checkinfo');
            if ($checkinfo['mobilephone'] != $mobilephone) {
                return ajaxFalse('请输入正确的手机号码');
            }
            if ($checkinfo['checkcode'] != $code) {
                return ajaxFalse('请输入正确的验证码');
            }

            $data = array(
                'mobilephone' => $mobilephone,
            );
            $res = db(\tname::vip)->where(array('openid' => $userinfo['openid']))->update($data);
            if (!$res) {
                return ajaxFalse('绑定出错，请稍后再试');
            }
            return ajaxSuccess('', '绑定成功', url('Vip/index'));
        } else {
            return $this->fetch();
        }
    }

    /**
     * 注册样式1 2017-10-15
     */
    public function register1()
    {
        return $this->fetch();
    }

    /**
     * 生成短信验证码并写入session    2017-10-15
     */
    public function createsms()
    {
        $mobilephone = input('post.mobilephone');

        if (!isMobilephone($mobilephone)) {
            return ajaxFalse('请输入正确的手机号');
        }

        $checkcode = createverifycode();
        $checkinfo = array(
            'mobilephone' => $mobilephone,
            'checkcode' => $checkcode,
        );
        session('checkinfo', $checkinfo);

        $content = '您好,您的验证码为' . $checkcode;
        sendmessage(WID, $mobilephone, $content, 'verifycode');

        return ajaxSuccess();
    }

    /**
     * 我的地址    2017-10-15
     */
    public function myaddress()
    {
        $userinfo = session('userinfo');

        $where['uid'] = WID;
        $where['openid'] = $userinfo['openid'];
        $myadressList = db(\tname::vip_myaddress)->where($where)->order('is_default desc')->select();

        $type = input('param.type', '');

        $this->assign('type', $type);
        $this->assign('myadressList', $myadressList);
        return $this->fetch();
    }

    /**
     * 设为默认地址    2017-10-15
     */
    public function addressdefault()
    {
        $userinfo = session('userinfo');

        db()->startTrans();
        $data = array(
            'id' => input('post.myaddress_id'),
            'is_default' => 1
        );
        $res1 = dataUpdate(\tname::vip_myaddress, $data);
        if (!$res1) {
            db()->rollback();
            return ajaxFalse('1');
        }

        $myaddressList = db(\tname::vip_myaddress)->where(array('uid' => WID, 'openid' => $userinfo['openid'], 'id' => array('neq', $data['id'])))->find();
        if (!empty($myaddressList)) {
            $res2 = db(\tname::vip_myaddress)->where(array('uid' => WID, 'openid' => $userinfo['openid'], 'id' => array('neq', $data['id'])))->update(array('is_default' => 0));
            if ($res2 === false) {
                db()->rollback();
                return ajaxFalse();
            }
        }
        db()->commit();
        return ajaxSuccess();
    }

    /**
     * 删除地址    2017-10-15
     */
    public function addressdel()
    {
        $myaddress_id = input('post.myaddress_id');
        $res = db(\tname::vip_myaddress)->delete($myaddress_id);
        if (!$res) {
            return ajaxFalse();
        }
        return ajaxSuccess();
    }

    /**
     * 新增地址    2017-10-15
     */
    public function addressadd()
    {
        $userinfo = session('userinfo');
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = WID;
            $data['openid'] = $userinfo['openid'];
            $address_pca = explode(',', $data['address_pca']);
            $data['province'] = $address_pca[0];
            $data['city'] = $address_pca[1];
            $data['area'] = $address_pca[2];

            (!isset($data['is_default']) || !$data['is_default']) && $data['is_default'] = 0;
            if (!isMobilephone($data['linktel'])) {
                return ajaxFalse('请输入正确的手机格式');
            }
            $res = dataUpdate(\tname::vip_myaddress, $data);

            if (!$res) {
                return ajaxFalse();
            }
            $data['id'] ? $curid = $data['id'] : $curid = $res;
            if ($data['is_default']) {
                db(\tname::vip_myaddress)->where(array('uid' => WID, 'openid' => $userinfo['openid'], 'id' => array('neq', $curid)))->update(array('is_default' => 0));
            }
            return ajaxSuccess('', '', url('Vip/myaddress'), array('id' => $curid));
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::vip_myaddress)->find($id);
            if ($id) {
                $data['pca'] = $data['province'] . ',' . $data['city'] . ',' . $data['area'];
                $data['code0'] = explode(',', $data['code']);
            }

            $this->assign('data', $data);
            return $this->fetch();
        }
    }

    /**
     * 加载地址   2017-10-15
     */
    public function address()
    {
        $pdata = input('post.');
        $userinfo = session('userinfo');

        if ($pdata['address_id']) {
            $data = db(\tname::vip_myaddress)->find($pdata['address_id']);
        } else {
            $where_a = array(
                'uid' => WID,
                'openid' => $userinfo['openid'],
                'is_default' => 1
            );
            $data = db(\tname::vip_myaddress)->where($where_a)->find();
        }
        $this->assign('data', $data);
        $this->assign('tool', 'address_select');
        $html = $this->fetch('public/tool');
        return ajaxSuccess($html, '', '');
    }

    /**
     * 二维码    2017-10-15
     */
    public function myqrcode()
    {
        $type = input('param.type');
        $userinfo = session('userinfo');

        if ($type == 1) {        //分享链接进入
            $vid = input('param.vid');
            $vip = db(\tname::vip)->find($vid);
            $imgpath = get_domain() . '/uploads/picture/uid' . WID . '/qrcode/' . $vip['sceneid'] . '.jpg';

            //distributionBuildrelation(WID, $openid, $vid);
        } else {
            $where_v = array(
                'uid' => WID,
                'openid' => $userinfo['openid']
            );
            $vip = db(\tname::vip)->where($where_v)->find();

            $sceneid = $vip['id'];
            if (!empty($vip) && !$vip['sceneid']) {
                $dir = './uploads/picture/uid' . WID . '/qrcode/' . $sceneid . '.jpg';
                $path = dirname($dir);
                !is_dir($path) && mkdir($path, 0777, true);

                if (!file_exists($dir)) {    //二维码不存在  则生成二维码
                    $ticket = getticket(WID, $sceneid);

                    $Ticket = new \wechat\Ticket();
                    $img = $Ticket->getqrcode($ticket['ticket'], array('uid' => WID));
                    if (!empty($img)) {
                        @file_put_contents($dir, $img);
                        db(\tname::vip)->where($where_v)->update(array('sceneid' => $sceneid));
                    }
                }
            }

            $imgpath = get_domain() . '/uploads/picture/uid' . WID . '/qrcode/' . $sceneid . '.jpg';
        }
        $share = array(
            'title' => '我的二维码',
            'desc' => '加入我们吧',
            'imgurl' => $imgpath,
            'link' => get_domain() . '/wechat.php/Vip/myqrcode/type/1/vid/' . $vip['id'],
        );

        $this->assign('vip', $vip);
        $this->assign('imgpath', $imgpath);
        $this->assign('share', $share);
        return $this->fetch();
    }

    /**
     * 意见反馈    2017-10-15
     */
    public function feedback()
    {
        $userinfo = session('userinfo');
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = WID;
            $data['openid'] = $userinfo['openid'];
            $data['nickname'] = $userinfo['nickname'];
            $data['create_time'] = time();

            //手机号正则验证
            if ($data['linktel']) {
                if (!isMobilephone($data['linktel'])) {
                    return ajaxFalse('请输入正确的手机格式');
                }
            }

            $res = db(\tname::vip_feedback)->insert($data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess('', '提交成功', url('Vip/index'));
        } else {
            return $this->fetch();
        }
    }

    /**
     * 关于我们    2017-10-15
     */
    public function about()
    {
        $type = input('param.type');

        switch ($type) {
            case 'about':
                $content = db(\tname::config)->where(array('uid' => WID))->column('about');
                $title = '关于我们';
                break;
            case 'integral':
                $content = db(\tname::integral_config)->where(array('uid' => WID))->column('content');
                $title = '积分规则';
                break;
        }

        $this->assign('title', $title);
        $this->assign('content', $content[0]);
        return $this->fetch();
    }


    /**
     * 微信支付    2017-10-15
     */
    public function wxpay()
    {
        $dealarr = session('dealarr');
        $userinfo = session('userinfo');

        $Wxpay = new \wechat\Wxpay();
        $prepay_id = $Wxpay->getprepayid($dealarr);
        $jsApiParameters = $Wxpay->getparameters($prepay_id, $dealarr);

        $url = array(
            $dealarr['success_url'],
            $dealarr['fail_url'],
        );

        $this->assign('jsApiParameters', $jsApiParameters);
        $this->assign('linkurl', $url);
        return $this->fetch();
    }

    /**
     * DEMO    2017-10-15
     */
    public function demo()
    {
        return $this->fetch();
    }
}
