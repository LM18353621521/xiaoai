<?php
/**
 * Created by PhpStorm.
 * User: Lu
 * Date: 2018/9/9
 * Time: 23:37
 */
namespace app\home\controller;

use app\common\logic\GoodsLogic;

class Home extends Base
{
    public function test()
    {
        return $this->fetch();
    }

    /**
     * 切换账号
     */
    public function change_login()
    {
        $pdata = input('post.');
        //当前的登录状态
        if ($pdata['login_vip_type'] == 2) {
            if (!isMobilephone($pdata['mobile'])) {
                return json(ajaxFalse("！输入手机号码格式有误"));
            }
            $wx_vip = db(\tname::vip)->where(array('id' => $pdata['wx_vip_id']))->find();
            $curr_vip = db(\tname::vip)->where(array('id' => $pdata['vip_id']))->find();
            $new_vip = db(\tname::vip)->where(array('mobilephone' => $pdata['mobile'], 'source' => 3))->find();
            if (empty($new_vip)) {
                return json(ajaxFalse("！抱歉，您输入的号码不正确，或者该手机号未注册"));
            }
            $login_info = array(
                'vip_id' => $new_vip['id'],
                'wx_vip_id' => $wx_vip['id'],
                'openid' => $wx_vip['openid'],
                's_vip_id' => $curr_vip['id'],
                'login_vip_type' => $new_vip['source'],
            );
            return json(ajaxSuccess($login_info, "切换成功！"));
        } else {

        }
    }


    /**
     * 首页
     */
    public function index()
    {
        $GoodsLogic = new GoodsLogic();
        //轮播图
        $carousel = getCarousel('mall');

        //最新商品
        $dataList = $GoodsLogic->getNewGoods();

        //是否有优惠券
        $where_coupon = array(
            'is_hidden' => 0,
            'is_publish' => 1,
        );
        $where_coupon['number'] = ['exp', ">actualsales"];
        $where_coupon['use_etime'] = ['gt', time()];
        $has_coupon = db(\tname::coupon)->where($where_coupon)->order('money desc')->find();
        if ($has_coupon) {
            $has_coupon['use_stime'] = date('Y.m.d', $has_coupon['use_stime']);
            $has_coupon['use_etime'] = date('Y.m.d', $has_coupon['use_etime']);
        }


        $categoryList = db(\tname::mall_category)->where(array('is_hidden' => 0, 'pid' => 0))->order("is_recommend desc,id")->limit(0, 3)->select();
        foreach ($categoryList as &$value) {
            $value['coverimg'] = imgurlToAbsolute($value['coverimg']);
        }

        $returndata = array(
            'carousel' => $carousel,
            'dataList' => $dataList,
            'has_coupon' => $has_coupon,
            'categoryList' => $categoryList,
        );
        $this->assign('carousel', $carousel);
        $this->assign('categoryList', $categoryList);
        $this->assign('title', "商城首页");
        return $this->fetch();
    }

    /**
     * 获取最新商品
     */
    public function getNewGoods()
    {
        $GoodsLogic = new GoodsLogic();
        $pdata = input('post.');
        $category_id = $pdata['category_id'];
        $keyword = $pdata['keyword'];
        $sort = $pdata['sort'];
        $asc = $pdata['asc'];
        $page = $pdata['page'];

        $list = $GoodsLogic->getNewGoods($category_id, $keyword, $sort, $asc, $page);

        $this->assign('dataList', $list);
        $html = $this->fetch('home/ajaxdata');
        $attach = array(
            'total' => '',
            'page_size' => 10,
            'count' => count($list),
        );
        return ajaxSuccess($html, '', '', $attach);
    }


    /**
     * 一级商品分类
     */
    public function types()
    {
        $GoodsLogic = new GoodsLogic();
        $pid = input('post.pid', 0);
        $dataList = $GoodsLogic->getNextCategory($pid);

        $this->assign('cateList', $dataList);
        $this->assign('title', "商品分类");
        return $this->fetch();
    }

    /**
     * 商品子级分类
     */
    public function categoryChild()
    {
        $GoodsLogic = new GoodsLogic();
        $pid = input('post.pid', 0);
        $dataList = $GoodsLogic->getNextCategory($pid);

        $categoryParent = db(\tname::mall_category)->find($pid);
        $categoryParent['ad_img'] = imgurlToAbsolute($categoryParent['ad_img']);

        $returndata = array(
            'categoryParent' => $categoryParent,
            'categoryChild' => $dataList,
        );
        $this->assign('dataList', $dataList);
        $html = $this->fetch('home/ajaxdata');
        $attach = array(
            'total' => '',
            'page_size' => 10,
            'count' => count($dataList),
            'categoryParent' => $categoryParent,
        );
        return ajaxSuccess($html, '', '', $attach);
    }

    /**
     * 获取商品列表
     */
    public function goodlist()
    {
        $user = session('userinfo');
        if (request()->isPost()) {
            $GoodsLogic = new GoodsLogic();
            $pdata = input('post.');
            $category_id = $pdata['category_id'];
            $keyword = $pdata['keyword'];
            $sort = $pdata['sort'];
            $asc = $pdata['asc'];
            $page = $pdata['page'];

            $list = $GoodsLogic->getGoodsList($category_id, $keyword, $sort, $asc, $page);
            $this->assign('dataList', $list);
            $html = $this->fetch('home/ajaxdata');
            $attach = array(
                'total' => '',
                'page_size' => 10,
                'count' => count($list),
            );
            return ajaxSuccess($html, '', '', $attach);
        }

        $data = array(
            'category_id' => input('param.category_id', 0),
            'keyword' => input('param.keyword', ''),
        );

        if($data['keyword']&&$user['vip_id']){
            $BasicLogic = new \app\common\logic\BasicLogic();
            $result = $BasicLogic->record_search($user['vip_id'], $data['keyword']);
        }

        $this->assign('data',$data);
        $this->assign('title', "商品列表");
        return $this->fetch();
    }

    /**
     * 获取分享商品
     */
    public function distributgoods()
    {
        if(request()->post()){
            $GoodsLogic = new GoodsLogic();
            $pdata = input('post.');
            $category_id = $pdata['category_id'];
            $keyword = $pdata['keyword'];
            $sort = $pdata['sort'];
            $asc = $pdata['asc'];
            $page = $pdata['page'];

            $list = $GoodsLogic->getShareGoodsList($category_id, $keyword, $sort, $asc, $page);
            $this->assign('dataList', $list);
            $html = $this->fetch('home/ajaxdata');
            $attach = array(
                'total' => '',
                'page_size' => 10,
                'count' => count($list),
            );
            return ajaxSuccess($html, '', '', $attach);
        }

        return $this->fetch();

    }


    public function create_poster()
    {
        $GoodsLogic = new GoodsLogic();
        $pdata = input('post.');

        $goodsInfo = $GoodsLogic->getGoodsInfo($pdata['goods_id']);


        $config = tpCache('base');

        $textInfo[0] = array(
            'text' => $goodsInfo['name'],
            'color' => array(95, 95, 95),
            'fontsize' => 14,
            'width' => 310,
            'left' => 20,
            'top' => 350,
        );
        $textInfo[1] = array(
            'text' => "￥ " . $goodsInfo['price'],
            'color' => array(255, 0, 0),
            'fontsize' => 14,
            'width' => 310,
            'left' => 20,
            'top' => 420,
        );

        $imgInfo[0] = array(
            'img' => $goodsInfo['coverimg'],
            'left' => 20,
            'top' => 20,
        );
        $bg_img = imgurlToAbsolute($config['goods_poster_bg']);
        $result = create_goods_poster($goodsInfo['id'], $pdata['vip_id'], $bg_img, $imgInfo, $textInfo);
//        dump($result);
        $poster_img = imgurlToAbsolute($result);
        $returndata = array(
            'poster_img' => $poster_img,
        );
        return json(ajaxSuccess($returndata));

    }


    /**
     * 商品详情
     */
    public function detail()
    {
        $user = session('userinfo');
        $GoodsLogic = new GoodsLogic();
        $goods_id = input('param.goods_id');

        $goodsInfo = $GoodsLogic->getGoodsInfo($goods_id);
        //规格
        $filter_spec = $GoodsLogic->get_spec($goods_id);
        foreach ($filter_spec as $key => $spec) {
            foreach ($spec as $key_s => $s) {
                if ($key_s == 0) {
                    $filter_spec[$key][$key_s]['sel'] = 1;
                } else {
                    $filter_spec[$key][$key_s]['sel'] = 0;
                }
            }
        }

        // 规格 对应 价格 库存表
        $goods_spec_price = db(\tname::goods_spec_price)
            ->where("goods_id", $goods_id)
            ->column("key,key_name,price,store_count,item_id");

        //收藏人数
        $collect_num = db(\tname::goods_collect)->where(array('goods_id' => $goods_id))->count();
        //是否收藏
        if($user){
            $has_collect = db(\tname::goods_collect)->where(array('vip_id' => $user['vip_id'], 'goods_id' => $goods_id))->find();
            $has_collect = $has_collect ? 1 : 0;
            $myCoupon = db(\tname::coupon_mycoupon)->where(array('vip_id' => $user['vip_id']))->column('coupon_id');
        }else{
            $has_collect=0;
            $myCoupon =array();
        }


        //获取优惠券
        $couponLogic = new \app\common\logic\CouponLogic();
        $couponList = $couponLogic->getCouponList('free', '', '', 0, 100);

        foreach ($couponList as &$value) {
            if (empty($myCoupon)) {
                $value['has'] = 0;
            } else {
                $value['has'] = in_array($value['id'], $myCoupon) ? 1 : 0;
            }

            $value['type_desc'] = "满减券";
        }


//        dump(json_encode($goods_spec_price,true));

        $this->assign('goodsInfo', $goodsInfo);
        $this->assign('filter_spec', $filter_spec);
        $this->assign('goods_spec_price', json_encode($goods_spec_price,true)); // 规格 对应 价格 库存表
//        $this->assign('goods_spec_price', $goods_spec_price);
        $this->assign('couponList', $couponList);
        $this->assign('has_collect', $has_collect);
        $this->assign('collect_num', $collect_num);
        $this->assign('title', "商品详情");
        return $this->fetch();
    }
    /**
     * 获取购物车数量
     */
    public function ajax_cart_num(){
        $user = session('userinfo');
        if(empty($user)){
            $cart_num=0;
        }else{
            $cart_num = db(\tname::mall_cart)->where(array('vip_id'=>$user['vip_id']))->sum('number');
        }
        return ajaxSuccess($cart_num);
    }

    /**
     * 商品规格
     * @return \think\response\Json
     */

    public function activity()
    {
        $GoodsLogic = new GoodsLogic();
        $pdata = input('post.');

        $goods = db(\tname::mall_product)->where(array('id' => $pdata['goods_id']))->field('id,name,stock,price,price_origial')->find();
        $goods_spec_price = $GoodsLogic->goodsSpecPrice($pdata['item_id']);

        if ($goods_spec_price) {
            $goods['price'] = $goods_spec_price['price'];
            $goods['market_price'] = $goods_spec_price['market_price'];
            $goods['stock'] = $goods_spec_price['store_count'];
        }
        $returndata = array(
            'goods' => $goods,
        );
        return ajaxSuccess($returndata);
    }


    /**
     * 订单确认
     * @return \think\response\Json
     */
    public function orderconfirm()
    {
        $user = session('userinfo');
        $pdata = input('');
        $action = $pdata['action'];
        $goods_id = $pdata['goods_id'];
        $item_id = $pdata['item_id'];
        $buy_num = $pdata['buy_num'];
        $cart_ids = $pdata['cart_ids'];
        $vip_id = $user['vip_id'];
//        dump($pdata);


        $cartLogic = new \app\common\logic\CartLogic();
        if ($action == 'buy_now') {
            $result = $cartLogic->buy_now($goods_id, $item_id, $buy_num);
        } else {
            $result = $cartLogic->buy_cart($vip_id);
        }
        if ($result['status'] == !1) {
            return json(ajaxFalse($result['msg']));
        }

        $cartList = $result['cartList'];
        $total_count = $result['total_count'];

        //获取我的优惠券
        $couponLogic = new \app\common\logic\CouponLogic();
        $couponList = $couponLogic->getMyCoupon($vip_id, 'free', $status = 0, $limit = $total_count['goods_price'], $sort = 'money', $asc = 'desc', true);
        if ($couponList) {
            $not_coupon = array(
                'id' => 0,
                'name' => "不使用优惠券",
                'money' => 0.00,
            );
            array_unshift($couponList, $not_coupon);
        }

        //获取我的地址
        $AddressLogic = new \app\common\logic\AddressLogic();
        $addressList = $AddressLogic->getAddressList($vip_id);

        $default_address = db(\tname::vip_myaddress)->where(array('vip_id' => $vip_id, 'is_default' => 1))->find();

        $express_fee = 0;
        if ($default_address) {
            $express_fee = db(\tname::config_freight)->where(array('region_code' => $default_address['province_code']))->value('first_money');
        } else {
        }
        $total_count["express_fee"] = number_format($express_fee, 2, ".", "");

        $this->assign('cartList',$cartList);
        $this->assign('total_count',$total_count);
        $this->assign('couponList',$couponList);
        $this->assign('sel_address',$default_address);
        $this->assign('addressList',$addressList);
        $this->assign('action',$action);
        $this->assign('goods_id',$goods_id);
        $this->assign('item_id',$item_id);
        $this->assign('title', "确认订单");
        return $this->fetch();
    }

    /**
     * 获取选择地址列表
     */
    public function addresslist(){
        $user = session('userinfo');
        $vip_id = $user['vip_id'];
        //获取我的地址
        $AddressLogic = new \app\common\logic\AddressLogic();
        $addressList = $AddressLogic->getAddressList($vip_id);
        $this->assign('dataList',$addressList);
        $html = $this->fetch('home/ajaxdata');
        $attach = array(
            'total' => '',
            'page_size' => 10,
            'count' => count($addressList),
        );
        return ajaxSuccess($html, '', '', $attach);
    }

    /**
     * 收藏商品
     */
    public function collect_do()
    {
        $user = session('userinfo');
        if(empty($user)){
            return ajaxFalse('您未登录，立即登录？','','',2);
        }
        $GoodsLogic = new GoodsLogic();
        $pdata = input('post.');
        $result = $GoodsLogic->goodsCollect($pdata['type'], $user['vip_id'], $pdata['goods_id']);
        if($result['status']==1){
            return ajaxSuccess('收藏成功！');
        }
        return ajaxFalse($result['msg']);
    }

    public function getcoupon()
    {
        $user = session('userinfo');
        if(empty($user)){
            return ajaxFalse('您未登录，立即登录？','','',2);
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
     * 开心一刻
     */
    public function happy()
    {
        if(request()->post()){
            $pdata = input('post.');
            $funLogic = new \app\common\logic\FunLogic();
            $list = $funLogic->getFunList($pdata['type'], '', '', '', $pdata['page']);
            $this->assign('dataList', $list);
            $this->assign('type',$pdata['type']);
            $html = $this->fetch('home/ajaxdata');
            $attach = array(
                'total' => '',
                'page_size' => 10,
                'count' => count($list),
            );

            return ajaxSuccess($html, '', '', $attach);
        }
        $this->assign('cateList', 12);
        $this->assign('title', "开心一刻");
        return $this->fetch();

    }

    /**
     * 优惠就列表
     */
    public function couponList()
    {
        $pdata = input('post.');
        $pdata['type'] = 'free';
        $pdata['page'] = 1;

        $funLogic = new \app\common\logic\CouponLogic();
        $list = $funLogic->getCouponList($pdata['type'], '', '', $pdata['page']);
        return json(ajaxSuccess($list));
    }

    /**
     * 我的优惠就列表
     */
    public function myCoupon()
    {
        $pdata = input('post.');
        $pdata['type'] = null;
        $pdata['vip_id'] = 1;
        $pdata['vip_id'] = 1;
        $funLogic = new \app\common\logic\CouponLogic();
        $list = $funLogic->getMyCoupon($pdata['vip_id'], $pdata['type'], null, 0, 'money', 'desc');
        return json(ajaxSuccess($list));
    }

    /**
     * 记录搜索历史
     */
    public function record_search()
    {
        $funLogic = new \app\common\logic\BasicLogic();
        $result = $funLogic->record_search(1, 'B');
    }

    /**
     * 获取搜索历史
     */
    public function search()
    {
        $user = session('userinfo');
        $funLogic = new \app\common\logic\BasicLogic();
        $result = $funLogic->get_search($user['vip_id']);
        $returndata = array(
            'keywordList' => $result,
        );
        $this->assign('keywordList',$result);
        $this->assign('title', "商品详情");
        return $this->fetch();
    }

    /**
     * 清空搜索历史
     */
    public function clear_keyword()
    {
        $user = session('userinfo');
        $res = db('vip_search')->where(array('vip_id' => $user['vip_id']))->update(array('keyword' => ""));
        return json(ajaxSuccess("", "搜索历史已清空！"));
    }
    /**
     * 检查是否已经登录
     */
    public function check_login(){
        $user = session('userinfo');
        if(empty($user)){
            return ajaxFalse('您未登录，立即登录？','','',2);
        }
        return ajaxSuccess();
    }


}