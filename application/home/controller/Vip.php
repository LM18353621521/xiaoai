<?php

namespace app\home\controller;

use think\Db;
use think\AjaxPage;
use think\Page;

class Vip extends Base
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $user = session('userinfo');

        $order_num = db(\tname::mall_order)->where(array('vip_id' => $user['vip_id']))->count();
        $count_data['order_num'] = $order_num;
        $coupon_num = db(\tname::coupon_mycoupon)->where(array('vip_id' => $user['vip_id']))->count();
        $count_data['coupon_num'] = $coupon_num;
        $address_num = db(\tname::vip_myaddress)->where(array('vip_id' => $user['vip_id']))->count();
        $count_data['address_num'] = $address_num;
        $collect_num = db(\tname::goods_collect)->where(array('vip_id' => $user['vip_id']))->count();
        $count_data['collect_num'] = $collect_num;

        $res =  db(\tname::coupon_mycoupon)->where(array('use_etime'=>['lt',time()],'vip_id' => $user['vip_id']))->update(array('status'=>2));
        $this->assign('count_data', $count_data);
        if(empty($user)){
            $this->error('你还没有登录，请先登录');
        }
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
        $pdata = input('post.');
        $vip = db(\tname::vip)->where(['id' => $user['vip_id']])->find();
        if ($vip) {
            $vip['headimg'] = $vip['headimgurl'];
        }

        $config = tpCache('base');

        $OrderLogic = new \app\common\logic\OrderLogic();
        $order_status = $OrderLogic->get_order_num($user['vip_id']);

        $this->assign('vip', $vip);
        $this->assign('order_status', $order_status);
        $this->assign('config', $config);
        $this->assign('title', "商品列表");
        return $this->fetch();
    }

    public function update_head_pic()
    {

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
        error_reporting(E_ERROR | E_PARSE);
        $search = input("");
        $where = array(
            'a.vip_id' => $user['vip_id'],
        );

        if (trim($search['keyword'])) {
            $where['a.name'] = array('like', '%' . trim($search['keyword']) . '%');
        }

        $cate_id = input('cate_id',0);


        $sort = "";
        switch ($search['sort']) {
            case "sales":
                $sort = "sales desc,";
                break;
            case "price":
                $sort = "price asc,";
                break;
            case "comment_num":
                $sort = "comment_count desc,";
                break;
            case "comment_num":
                $sort = "create_time desc,";
                break;
        }
        $sort .= "a.id desc";

        $count = db(\tname::goods_collect)->alias('a')
            ->join('mall_product g', 'a.goods_id=g.id')
            ->where($where)->count();
        $page = new Page($count, 16);

        $dataList = db(\tname::goods_collect)->alias('a')
            ->join('mall_product g', 'a.goods_id=g.id')
            ->field('a.*,g.category_id,g.name,g.coverimg,g.price,(g.sales_config+g.sales_actual) as sales,g.comment_count')
            ->where($where)->order($sort)
            ->limit($page->firstRow . ',' . $page->listRows)->select();


        $cate_ids = [];
        foreach ($dataList as $val) {
            $cate_ids[] = $val['category_id'];
        }

        $categoryList = db(\tname::mall_category)->where(array('id' => ['in', $cate_ids]))->select();
        $this->assign('categoryList', $categoryList);

        //热门推荐
        $where_recommend = array(
            'is_publish' => 1,
            'is_hidden' => 0,
            'is_recommend' => 1
        );
        $recommend_list = db(\tname::mall_product)->alias('a')
            ->field('a.id,a.name,a.coverimg,a.price,(a.sales_config+a.sales_actual) as sales,a.comment_count')
            ->where($where_recommend)->order('id')
            ->limit(4)->select();

        $this->assign('dataList', $dataList);
        $this->assign('recommend_list', $recommend_list);// 赋值分页输出
        $this->assign('page', $page);// 赋值分页输出
        $this->assign('search', $search);
        $this->assign('cate_id', $cate_id);
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
        $check_bind = db(\tname::vip)->where(array('mobile' => $mobile, 'source' => 2))->find();
        if ($check_bind && $check_bind['id'] != $user['vip_id']) {
            return json(ajaxFalse('该手机号码已被绑定，请重新输入'));
        }

        //验证码信息
        $codeinfo = session('codeinfo');
//        dump($codeinfo);

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
        $userinfo = array(
            'vip_id' => $vip['id'],
            'vip_ids' => $ids,
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
        return json(ajaxSuccess($userinfo, "绑定成功！"));
    }


    /**
     * 我的信息    2017-10-15
     */
    public function myinfo()
    {
        $user = session('userinfo');
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = WID;
            $data['id'] = $user['vip_id'];

            $path = "uploads";
            $image = base64_image_content($data['head_pic'], $path);
            if ($image) {
                $data['headimgurl'] = $image;
            }

            $res = dataUpdate(\tname::vip, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess('', '修改成功', url('Vip/index'));
        }
    }


    public function edit_password()
    {
        if (request()->isPost()) {
            $userinfo = session('userinfo');

            if (empty($userinfo)) {
                return ajaxFalse("您还没有登录，请先登录");
            }

            $data = input('post.');
            if (empty($data['password_old'])) {
                return ajaxFalse("请输入原密码");
            }
            if (empty($data['password'])) {
                return ajaxFalse("请输入新密码");
            }
            if (strlen($data['password']) < 6) {
                return ajaxFalse("新密码长度不得小于6位");
            }
            if ($data['password'] != $data['passwords']) {
                return ajaxFalse("两次密码输入一致");
            }

            $user = db(\tname::vip)->where(array('id' => $userinfo['vip_id']))->find();

            if ($user['password'] != encrypt($data['password'])) {
                return ajaxFalse("两次密码输入一致");
            }

            $data['password'] = encrypt($data['password']);

            $res = dataUpdate(\tname::vip_info, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess('', '修改成功', url('Vip/index'));
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
    public function addresslist()
    {
        $user = session('userinfo');
        $where['vip_id'] = $user['vip_id'];
        $myadressList = db(\tname::vip_myaddress)->where($where)->order('is_default desc')->select();

        //省份列表
        $provinceList = db(\tname::region)->where(array('level' => 1))->order('id')->select();
        $this->assign('provinceList', $provinceList);

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

        $myaddressList = db(\tname::vip_myaddress)->where(array('vip_id' => $userinfo['vip_id'], 'id' => array('neq', $data['id'])))->find();
        if (!empty($myaddressList)) {
            $res2 = db(\tname::vip_myaddress)->where(array('vip_id' => $userinfo['vip_id'], 'id' => array('neq', $data['id'])))->update(array('is_default' => 0));
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
        $myaddress_id = input('post.address_id');
        $res = db(\tname::vip_myaddress)->delete($myaddress_id);
        if (!$res) {
            return ajaxFalse();
        }
        return ajaxSuccess();
    }

    /**
     * 新增地址    2017-10-15
     */
    public function address_add()
    {
        $user = session('userinfo');
        if (request()->isPost()) {
            $_post = input('post.');

            //省份
            $province = db(\tname::region)->where(array('id' => $_post['province_id']))->find();
            //市
            $city = db(\tname::region)->where(array('id' => $_post['city_id']))->find();
            //区
            $district = db(\tname::region)->where(array('id' => $_post['district_id']))->find();

            $data = $_post;
            $data['vip_id'] = $user['vip_id'];
            $data['province_code'] = $province['id'];
            $data['city_code'] = $city['id'];
            $data['district_code'] = $district['id'];
            $data['province'] = $province['name'];
            $data['city'] = $city['name'];
            $data['district'] = $district['name'];
            $has = db(\tname::vip_myaddress)->where(array('vip_id' => $user['vip_id']))->find();
            $data['is_default'] = 0;
            if (empty($has)) {
                $data['is_default'] = 1;
            }
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
            $address = db(\tname::vip_myaddress)->where(array('vip_id' => $user['vip_id']))->find();
            $address['linktel_hide'] = hidtel($address['linktel']);
            $this->assign('data', $address);
            $html = $this->fetch('vip/ajaxdata');
            return ajaxSuccess($html, '保存成功', url('Vip/addrlist'), array('data' => ''));
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
     * 新增地址    2017-10-15
     */
    public function addredit()
    {
        $user = session('userinfo');
        if (request()->isPost()) {
            $_post = input('post.');
            //省份
            $province = db(\tname::region)->where(array('id' => $_post['province_id']))->find();
            //市
            $city = db(\tname::region)->where(array('id' => $_post['city_id']))->find();
            //区
            $district = db(\tname::region)->where(array('id' => $_post['district_id']))->find();

            $data = $_post;
            $data['vip_id'] = $user['vip_id'];
            $data['province_code'] = $province['id'];
            $data['city_code'] = $city['id'];
            $data['district_code'] = $district['id'];
            $data['province'] = $province['name'];
            $data['city'] = $city['name'];
            $data['district'] = $district['name'];
            $has = db(\tname::vip_myaddress)->where(array('vip_id' => $user['vip_id']))->find();
            $data['is_default'] = 0;
            if (empty($has)) {
                $data['is_default'] = 1;
            }
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
            $address = db(\tname::vip_myaddress)->where(array('vip_id' => $user['vip_id']))->find();
            $address['linktel_hide'] = hidtel($address['linktel']);
            $this->assign('data', $address);
            $this->assign('act', 2);
            $html = $this->fetch('vip/ajaxdata');
            return ajaxSuccess($html, '保存成功', url('Vip/addrlist'), array('data' => ''));
        } else {
            $id = input('param.address_id', 0);
            $data = db(\tname::vip_myaddress)->find($id);
            $title = "添加地址";
            if ($id) {
                $data['pca'] = $data['province'] . ',' . $data['city'] . ',' . $data['area'];
                $data['code0'] = explode(',', $data['code']);
                $title = "编辑地址";
            }
            //省份列表
            $provinceList = db(\tname::region)->where(array('level' => 1))->order('id')->select();
            $this->assign('provinceList', $provinceList);
            $cityList = db(\tname::region)->where(array('level' => 2, 'parent_id' => $data['province_code']))->order('id')->select();
            $this->assign('cityList', $cityList);
            $districtList = db(\tname::region)->where(array('level' => 3, 'parent_id' => $data['city_code']))->order('id')->select();
            $this->assign('districtList', $districtList);
            $this->assign('data', $data);
            $this->assign('title', $title);
            $this->assign('act', 1);
            return $this->fetch("vip/ajaxdata");
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
     * 我的优惠券
     */
    public function mycoupon()
    {
        $user = session('userinfo');
        $vip_ids = session('vip_ids');
        $post = input('');
        $where = array(
            'vip_id' => array('in', $vip_ids),
        );
        $status = isset($post['status']) ? $post['status'] : 0;

        switch ($status) {
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
            case 4:
                break;
        }

        if ($status == 4) {
            $mycoupon_ids = db(\tname::coupon_mycoupon)->alias('a')->where($where)->column('coupon_id');

            $where_coupon = array(
                'is_hidden' => 0,
                'is_publish' => 1,
            );
            $where_coupon['number'] = array('exp', '>actualsales');
            $where_coupon['id'] = array('not in', $mycoupon_ids);

            $dataList = db(\tname::coupon)
                ->field('*,FROM_UNIXTIME(use_stime,"%Y.%m.%d") as use_stime,FROM_UNIXTIME(use_etime,"%Y.%m.%d") as use_etime, 3 as status')
                ->where($where_coupon)
                ->order("use_stime asc,money desc")
                ->select();

        } else {
            $order = " id desc";
            $dataList = db(\tname::coupon_mycoupon)->alias('a')
                ->field('*,FROM_UNIXTIME(use_stime,"%Y.%m.%d") as use_stime,FROM_UNIXTIME(use_etime,"%Y.%m.%d") as use_etime')
                ->where($where)
                ->order($order)
                ->select();
        }

        $status_text = array(
            '0' => '未使用',
            '1' => '已使用',
            '2' => '已过期',
            '3' => '可领取'
        );

        foreach ($dataList as &$value) {
            $value['status_text'] = $status_text[$value['status']];
            $value['_type'] = 1;
        }
        $this->assign('dataList', $dataList);
        $this->assign('status', $status);
        return $this->fetch();
    }

    /**
     * 领取优惠券
     * @return
     */
    public function getcoupon()
    {
        $user = session('userinfo');
        if (empty($user)) {
            return ajaxFalse('您未登录，立即登录？', '', '', 2);
        }

        $couponLogic = new \app\common\logic\CouponLogic();
        $pdata = input('post.');
        $result = $couponLogic->getCoupon($user['vip_id'], $pdata['id']);
        if ($result['status'] != 1) {
            return json(ajaxFalse($result['msg']));
        }
        return json(ajaxSuccess('', $result['msg']));
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
