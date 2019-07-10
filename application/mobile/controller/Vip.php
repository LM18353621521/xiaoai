<?php
namespace app\mobile\controller;

use think\Db;

class Vip extends Base
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $BasicLogic = new \app\common\logic\BasicLogic();
        $BasicLogic->auto_cancel_order();
        $BasicLogic->auto_confirm_order();
    }

    public function test()
    {
        Db::startTrans();
        $data = array(
            'nickname' => 100,
        );
        $res = dataUpdate(\tname::vip, $data);
        if ($res) {
            Db::rollback();
            dump($res);
        }
        Db::commit();
//        $ps = db(\tname::region)->where(array('parent_id'=>0))->select();
//        dump($ps);
//        $region=[];
//        foreach($ps as $keyp=>$p){
//            $region[$keyp]['name']=$p['name'];
//            $region[$keyp]['code']=$p['code'];
//            $cs =db(\tname::region)->where(array('parent_id'=>$p['id']))->select();
//            $sub=[];
//            foreach($cs as $keyc=>$c){
//                $sub[$keyc]['name']=$c['name'];
//                $sub[$keyc]['code']=$c['code'];
//                $subs=[];
//                $as =db(\tname::region)->where(array('parent_id'=>$c['id']))->select();
//                foreach($as as $keya=>$a){
//                    $subs[$keya]['name']=$a['name'];
//                    $subs[$keya]['code']=$a['code'];
//                }
//                if($subs){
//                    $sub[$keyc]['sub']=$subs;
//                }
//
//            }
//            $region[$keyp]['sub']=$sub;
//        }
//
//        $json_region= json_encode($region,JSON_UNESCAPED_UNICODE);
//        dump($region);
//        dump($json_region);

    }


    /**
     * 首页    2018-01-16
     */
    public function index()
    {
        $user = session('userinfo');
        if(!empty($user)){
            $AgentLogic = new \app\common\logic\AgentLogic();
            $AgentLogic->createAgent($user['mobile']);
        }
        $pdata = input('post.');
        $vip = db(\tname::vip)->where(['uid' => WID, 'id' => $user['vip_id']])->find();
        if ($vip) {
            $vip['headimg'] = $vip['headimgurl'];
        }
        $vip_ids = getVipIds($vip['id']);
        $config = tpCache('web');
        $OrderLogic = new \app\common\logic\OrderLogic();
        $order_status = $OrderLogic->get_order_num($vip_ids);

        $this->assign('vip', $vip);
        $this->assign('order_status', $order_status);
        $this->assign('config', $config);
        $this->assign('title', "商品列表");
        return $this->fetch();
    }

    /**
     * 更改头像
     */
    public function update_head_pic()
    {
        $user = session('userinfo');
        $pdata = input('');
        $image = base64_image_content($pdata['image'], 'uploads/picture');
        $data = array(
            'id' => $user['vip_id'],
            'headimgurl' => $image,
        );
        $res = dataUpdate(\tname::vip, $data);
        if ($res) {
            return ajaxSuccess('', '头像更换成功');
        } else {
            return ajaxFalse();
        }
    }

    /**
     * 获取订单数量
     */
    public function order_status()
    {
        $user = session('userinfo');
        $vip_ids = session('vip_ids');
        $pdata = input('post.');
        $OrderLogic = new \app\common\logic\OrderLogic();
        $order_status = $OrderLogic->get_order_num($vip_ids);
        return ajaxSuccess($order_status);
    }


    /**
     * 我的关注
     * @return \think\response\Json
     */
    public function mycollect()
    {
        $user = session('userinfo');
        $vip_ids = session('vip_ids');
        if (request()->post()) {
            $pdata = input('post.');
            $keyword = $pdata['keyword'];
            $asc = $pdata['asc'];
            $page = $pdata['page'];
            $sort = $pdata['sort'];
            $page_size = 10;
            $keyword = trim($keyword);
            $asc = $asc ? $asc : "asc";
            $p = $page ? $page : 0;
            $where = array(
                'vip_id' => array('in', $vip_ids),
            );

            if ($keyword) {
                $where['name'] = array('like', '%' . $keyword . '%');
            }

            $order = "";
            if ($sort) {
                $order .= $sort . " " . $asc . ",";
            } else {
                $order .= "create_time " . $asc . ",";
            }
            $order = $order . " id desc";

            $dataList = db(\tname::goods_collect)->alias('a')
                ->field('a.*,p.name,(p.sales_config+p.sales_actual) as sales,p.coverimg,p.price')
                ->join(\tname::mall_product . " p", 'a.goods_id=p.id')
                ->where($where)
                ->order($order)
                ->page($p, $page_size)
                ->select();
            foreach ($dataList as &$value) {
                $value['coverimg'] = imgurlToAbsolute($value['coverimg']);
            }
            $this->assign('dataList', $dataList);
            $html = $this->fetch('vip/ajaxdata');
            $attach = array(
                'total' => '',
                'page_size' => 10,
                'count' => count($dataList),
            );
            return ajaxSuccess($html, '', '', $attach);
        }

        $this->assign('title', "我的关注");
        return $this->fetch();
    }

    /**
     * 检查收藏商品是否下架
     * @return \think\response\Json
     */
    public function check_goods()
    {
        $pdata = input('post.');
        $goods = db(\tname::mall_product)->where(array('id' => $pdata['goods_id']))->find();
        if ($goods['is_hidden'] == 1 || $goods['is_publish'] == 0) {
            return json(ajaxFalse('商品已下架'));
        }
        return json(ajaxSuccess());
    }

    /**
     * 检查收藏商品是否下架
     * @return \think\response\Json
     */
    public function collect_del()
    {
        $pdata = input('post.');
        $res = db(\tname::goods_collect)->where(array('id' => $pdata['id']))->delete();
        if (!$res) {
            return json(ajaxFalse('！操作失败，请稍后重试'));
        }
        return json(ajaxSuccess());
    }


    /**
     * @return \think\response\Json
     */
    public function bind_mobile()
    {
        $user = session('userinfo');
        $pdata = input('post.');
        $mobile = $pdata['mobile'];
        $code = $pdata['code'];
        $vip = db(\tname::vip)->where(array('id' => $user['vip_id']))->find();
        //检查是否已经被绑定
        $check_bind = db(\tname::vip)->where(array('mobile' => $mobile, 'source' => $user['source']))->find();
        if ($check_bind) {
            return json(ajaxFalse('该手机号码已被绑定，请重新输入'));
        }
        //验证码信息
        $codeinfo = session('codeinfo');
        if ($codeinfo['check_mobile'] != $mobile) {
            return ajaxFalse('请输入正确的手机号码');
        }
        if ($codeinfo['check_code'] != $code) {
            return ajaxFalse('请输入正确的验证码');
        }
        $data = array(
            'id' => $vip['id'],
            'mobile' => $mobile,
        );
        $res = dataUpdate(\tname::vip, $data);

        if (!$res) {
            return json(ajaxFalse('绑定失败，请稍后重试'));
        }
        $ids = db(\tname::vip)->where(array('mobile' => $mobile))->column('id');
        $ids = implode(',', $ids);
        //测试环境手动配置
        $userinfo =$vip;
        $userinfo['vip_id']=$vip['id'];
        $userinfo['vip_ids']=$ids;
        session('userinfo', $userinfo);
        return json(ajaxSuccess($userinfo, "绑定成功！"));
    }

    /**
     * 修改手机第一步，检查验证码
     */
    public function change_mobile_one(){
        $user = session('userinfo');
        $pdata = input('post.');
        $mobile = $pdata['mobile'];
        $code = $pdata['code'];
        $vip = db(\tname::vip)->where(array('id' => $user['vip_id']))->find();
        //检查是否已经被绑定
        $check_bind = db(\tname::vip)->where(array('mobile' => $mobile, 'source' => $user['source']))->find();
        if ($check_bind && $check_bind['id'] != $user['vip_id']) {
            return json(ajaxFalse('该手机号码已被绑定，请重新输入'));
        }
        //验证码信息
        $codeinfo = session('codeinfo');
        if ($codeinfo['check_mobile'] != $mobile) {
            return ajaxFalse('请输入正确的手机号码');
        }
        if ($codeinfo['check_code'] != $code) {
            return ajaxFalse('请输入正确的验证码');
        }
        return json(ajaxSuccess('', "验证成功"));
    }
    /**
     * 修改手机号码
     * @return \think\response\Json
     */
    public function change_mobile_two()
    {
        $userLogic =new \app\common\logic\UserLogic();
        $user = session('userinfo');
        $pdata = input('post.');
        $mobile = $pdata['mobile'];
        $code = $pdata['code'];
        //验证码信息
        $codeinfo = session('codeinfo');
        if ($codeinfo['check_mobile'] != $mobile) {
            return ajaxFalse('请输入正确的手机号码');
        }
        if ($codeinfo['check_code'] != $code) {
            return ajaxFalse('请输入正确的验证码');
        }
        $vip = db(\tname::vip)->where(array('id' => $user['vip_id']))->find();
        $result = $userLogic->changeMobile($vip['mobile'],$mobile);
        if ($result['status']==0) {
            return json(ajaxFalse($result['msg']));
        }
        $ids = db(\tname::vip)->where(array('mobile' => $mobile))->column('id');
        $ids = implode(',', $ids);
        //测试环境手动配置
        $userinfo =$vip;
        $userinfo['vip_id']=$vip['id'];
        $userinfo['vip_ids']=$ids;
        session('userinfo', $userinfo);
        return json(ajaxSuccess('', "修改成功"));
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
    public function addrlist()
    {
        $user = session('userinfo');
        $vip_ids = session('vip_ids');
        $where['vip_id'] = array('in', $vip_ids);
        $myadressList = db(\tname::vip_myaddress)->where($where)->order('is_default desc')->select();
        $type = input('param.type', '');
        $this->assign('type', $type);
        $this->assign('myadressList', $myadressList);
        $this->assign('title', "地址管理");
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
    public function addredit()
    {
        $user = session('userinfo');
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = WID;
            $data['vip_id'] = $user['vip_id'];
            $address_pca = explode(' ', $data['region']);
            $addr_codes = explode(',', $data['code']);
            $data['province'] = $address_pca[0];
            $data['city'] = $address_pca[1];
            $data['district'] = $address_pca[2];
            $data['province_code'] = $addr_codes[0];
            $data['city_code'] = $addr_codes[1];
            $data['district_code'] = $addr_codes[2];

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
                db(\tname::vip_myaddress)->where(array('uid' => WID, 'vip_id' => $user['vip_id'], 'id' => array('neq', $curid)))->update(array('is_default' => 0));
            }
            return ajaxSuccess('', '保存成功', url('Vip/addrlist'), array('id' => $curid));
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::vip_myaddress)->find($id);
            $title = "添加地址";
            if ($id) {
                $data['pca'] = $data['province'] . ',' . $data['city'] . ',' . $data['area'];
                $data['code0'] = explode(',', $data['code']);
                $title = "编辑地址";
            }

            $this->assign('data', $data);
            $this->assign('title', $title);
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
     * 我的优惠券
     */
    public function mycoupon()
    {
        $user = session('userinfo');
        $vip_ids = session('vip_ids');
        if (request()->post()) {

            $post = input('post.');
            $keyword = $post['keyword'];
            $asc = 'asc';
            $page = $post['page'];
            $sort = 'status';
            $page_size = 15;
            $asc = $asc ? $asc : "asc";
            $p = $page ? $page : 0;
            $where = array(
                'vip_id' => array('in', [$user['vip_id']]),
            );

            switch ($post['status']) {
                case 0:
                    break;
                case 1:
                    $where['status'] = 0;
                    break;
                case 2:
                    $where['status'] = 1;
                    break;
                case 3:
                    $where['status'] = 2;
                    break;
            }


            $order = "";
            if ($sort) {
                $order .= $sort . " " . $asc . ",";
            } else {
                $order .= "create_time " . $asc . ",";
            }
            $order = $order . " id desc";

            $dataList = db(\tname::coupon_mycoupon)->alias('a')
                ->field('*,FROM_UNIXTIME(use_stime,"%Y.%m.%d") as use_stime,FROM_UNIXTIME(use_etime,"%Y.%m.%d") as use_etime')
                ->where($where)
                ->order($order)
                ->page($p, $page_size)
                ->select();

            $status_text = array(
                '0' => '未使用',
                '1' => '已使用',
                '2' => '已过期'
            );

            foreach ($dataList as &$value) {
                $value['status_text'] = $status_text[$value['status']];
                $value['_type'] = 1;

            }
            $this->assign('dataList', $dataList);
            $html = $this->fetch('vip/ajaxdata');
            $attach = array(
                'total' => '',
                'page_size' => $page_size,
                'count' => count($dataList),
            );
            return ajaxSuccess($html, '', '', $attach);
        }
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
