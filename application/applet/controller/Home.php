<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/9
 * Time: 23:37
 */
namespace app\applet\controller;

use app\common\logic\GoodsLogic;
use app\common\logic\FunLogic;

class Home extends Applet
{
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
        $carousel = getCarousel('mobile');
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

        $categoryList = db(\tname::mall_category)->where(array('is_hidden' => 0, 'pid' => 0))->order("is_recommend desc,id")->select();
        foreach ($categoryList as &$value) {
            $value['coverimg'] = imgurlToAbsolute($value['coverimg']);
        }

        $config = tpCache('web');
        $applet_check_switch = tpCache('base.applet_check_switch');
        $config['happly_switch']=$applet_check_switch;
        $config['logo'] = imgurlToAbsolute( $config['logo']);

        $returndata = array(
            'carousel' => $carousel,
            'dataList' => $dataList,
            'has_coupon' => $has_coupon,
            'categoryList' => $categoryList,
            'config'=>$config,
            'show_coupon'=>1,
        );
        return json(ajaxSuccess($returndata));
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
        $page = $pdata['page'];

        $list = $GoodsLogic->getNewGoods($category_id, $keyword, $sort, $asc, $page);
        return json(ajaxSuccess($list));
    }


    /**
     * 一级商品分类
     */
    public function category()
    {
        $GoodsLogic = new GoodsLogic();
        $pid = input('post.pid', 0);
        $dataList = $GoodsLogic->getNextCategory($pid);

        $returndata = array(
            'dataList' => $dataList,
        );
        return json(ajaxSuccess($returndata));
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
        return json(ajaxSuccess($returndata));
    }

    /**
     * 获取商品列表
     */
    public function goodsList()
    {
        $GoodsLogic = new GoodsLogic();
        $pdata = input('post.');
        $category_id = $pdata['category_id'];
        $keyword = $pdata['keyword'];
        $sort = $pdata['sort'];
        $asc = $pdata['asc'];
        $page = $pdata['page'];

        if ($keyword && $pdata['vip_id']) {
            $funLogic = new \app\common\logic\BasicLogic();
            $result = $funLogic->record_search($pdata['vip_id'], $keyword);
        }

        $list = $GoodsLogic->getGoodsList($category_id, $keyword, $sort, $asc, $page);
        return json(ajaxSuccess($list));
    }


    /**
     * 分销页面
     */
    public function distributgoods()
    {

        $applet_check_switch = tpCache('base.applet_check_switch');
        $config['share_tips']="保存至相册可以分享到朋友圈";
        $config['share_btn_switch']=$applet_check_switch;
        $returndata = array(
            'config' => $config,
        );
        return json(ajaxSuccess($returndata));
    }

    /**
     * 获取分享商品
     */
    public function shareGoodsList()
    {
        $GoodsLogic = new GoodsLogic();
        $pdata = input('post.');
        $category_id = $pdata['category_id'];
        $keyword = $pdata['keyword'];
        $sort = $pdata['sort'];
        $asc = $pdata['asc'];
        $page = $pdata['page'];

        $list = $GoodsLogic->getShareGoodsList($category_id, $keyword, $sort, $asc, $page);
        return json(ajaxSuccess($list));
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
            'top' => 385,
        );
        $textInfo[1] = array(
            'text' => "￥ " . $goodsInfo['price'],
            'color' => array(255, 0, 0),
            'fontsize' => 14,
            'width' => 310,
            'left' => 20,
            'top' => 455,
        );
        $textInfo[2] = array(
            'text' => "长按识别小程序码",
            'color' => array(90, 90, 90),
            'fontsize' => 13,
            'width' => 310,
            'left' => 20,
            'top' => 515,
        );
        $textInfo[3] = array(
            'text' => "晓爱商城",
            'color' => array(90, 90, 90),
            'fontsize' => 12,
            'width' => 310,
            'left' => 20,
            'top' => 545,
        );

        $imgInfo[0] = array(
            'img' => $goodsInfo['coverimg'],
            'left' => 12.5,
            'top' => 20,
        );

        //获得分享二维码
        $goodshare_qrcode = $this->create_goodshare_qrcode($pdata['vip_id'], $pdata['goods_id']);

        $imgInfo[1] = array(
            'img' => $goodshare_qrcode,
            'left' => 225,
            'top' => 515,
        );

        $bg_img = $config['goods_poster_bg'];
        $result = create_goods_poster($goodsInfo['id'], $pdata['vip_id'], $bg_img, $imgInfo, $textInfo);

        $poster_img =imgurlToAbsolute($result);
        $returndata = array(
            'poster_img' => $poster_img,
        );
        return json(ajaxSuccess($returndata));

    }

    /**
     * 获取商品分享的二维码
     * @param $vip_id
     * @param $goods_id
     * @return string
     */
    public function create_goodshare_qrcode($vip_id, $goods_id)
    {
        $savepath = '/public/uploads/picture/uid2/qrcode/';
        if (!file_exists(ROOT_PATH . $savepath)) {
            mkdir(ROOT_PATH . $savepath, 0777, true);
        }
        $qrocde_path = '/uploads/picture/uid2/qrcode/' . md5(get_domain() . $vip_id . $goods_id) . '.jpg';//小程序码
        if (!file_exists(ROOT_PATH . "/public" . $qrocde_path)) {
            $data = array(
                'scene' => $vip_id . '&' . $goods_id,
                'page' => 'pages/home/detail/detail',
                'width' => 200,
            );
            $this->create_user_good_qrcode(2, ROOT_PATH . "/public" . $qrocde_path, $data);
        }
        $result = edit_img(get_domain() . $qrocde_path, 120, 120, 'uploads/usergoodsqrcode/');
        return get_domain() . $result;
    }


    /**
     * 商品详情
     */
    public function detail()
    {
        $GoodsLogic = new GoodsLogic();
        $pdata = input('post.');

        $goodsInfo = $GoodsLogic->getGoodsInfo($pdata['goods_id']);
        //规格
        $filter_spec = $GoodsLogic->get_spec($pdata['goods_id']);
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
            ->where("goods_id", $pdata['goods_id'])
            ->column("key,key_name,spec_img,price,store_count,item_id");
        foreach ($goods_spec_price as &$val){
            $val['spec_img']=$val['spec_img']?imgurlToAbsolute($val['spec_img']):'';
        }

        //收藏人数
        $collect_num = db(\tname::goods_collect)->where(array('goods_id' => $pdata['goods_id']))->count();
        //是否收藏
        $has_collect = db(\tname::goods_collect)->where(array('vip_id' => $pdata['vip_id'], 'goods_id' => $pdata['goods_id']))->find();
        $has_collect = $has_collect ? 1 : 0;

        //获取优惠券
        $couponLogic = new \app\common\logic\CouponLogic();
        $couponList = $couponLogic->getCouponList('free', '', '', 0, 100);

        $myCoupon = db(\tname::coupon_mycoupon)->where(array('vip_id' => $pdata['vip_id']))->column('coupon_id');
        foreach ($couponList as &$value) {
            if (empty($myCoupon)) {
                $value['has'] = 0;
            } else {
                $value['has'] = in_array($value['id'], $myCoupon) ? 1 : 0;
            }
            $value['type_desc'] = "满减券";
        }

        //全部评价
        $comment[0] = db(\tname::mall_comment)->where(array('product_id' => $pdata['goods_id'], 'is_show' => 1))->count();
        //好评
        $comment[1] = db(\tname::mall_comment)->where(array('product_id' => $pdata['goods_id'], 'is_show' => 1, 'star' => ['gt', 3]))->count();
        //中评
        $comment[2] = db(\tname::mall_comment)->where(array('product_id' => $pdata['goods_id'], 'is_show' => 1, 'star' => ['eq', 3]))->count();
        //差评
        $comment[3] = db(\tname::mall_comment)->where(array('product_id' => $pdata['goods_id'], 'is_show' => 1, 'star' => ['lt', 3]))->count();


        $config['share_tips']="保存至相册可以分享到朋友圈";
        $config['share_btn_switch']=0;

        $goodsInfo['description'] = str_replace("<img ", "<img class='img_w' ", $goodsInfo['description']);


        $returndata = array(
            'goodsInfo' => $goodsInfo,
            'filter_spec' => $filter_spec,
            'goods_spec_price' => $goods_spec_price,
            'couponList' => $couponList,
            'has_collect' => $has_collect,
            'collect_num' => $collect_num,
            'comment' => $comment,
            'config' => $config
        );
        return json(ajaxSuccess($returndata));
    }

    public function ajax_comment()
    {
        $pdata = input('post.');
        //获取评价
        $BasicLogic = new \app\common\logic\BasicLogic();
        $result = $BasicLogic->get_comment($pdata['goods_id'], 1, 1, $pdata['star'], $pdata['page'], $pdata['pagenum']);
        return json(ajaxSuccess($result));
    }


    /**
     * 获取购物车数量
     */
    public function ajax_cart_num()
    {
        $pdata = input('post.');
        $cart_num = db(\tname::mall_cart)->where(array('vip_id' => $pdata['vip_id']))->sum('number');
        $returndata = array(
            'cart_num' => $cart_num,
        );
        return json(ajaxSuccess($returndata));
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
            $goods['market_price'] = $goods_spec_price['price'];
            $goods['stock'] = $goods_spec_price['price'];
        }
        $returndata = array(
            'goods' => $goods,
        );
        return json(ajaxSuccess($returndata));
    }


    /**
     * 订单确认
     * @return \think\response\Json
     */
    public function orderconfirm()
    {
        $pdata = input('');
        $action = $pdata['action'];
        $goods_id = $pdata['goods_id'];
        $item_id = $pdata['item_id'];
        $buy_num = $pdata['buy_num'];
        $cart_ids = $pdata['cart_ids'];
        $vip_id = $pdata['vip_id'];
        $vip_ids = $pdata['vip_ids'];
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
        $addressList = $AddressLogic->getAddressList($vip_ids);

        $default_address = db(\tname::vip_myaddress)->where(array('vip_id' => $vip_id, 'is_default' => 1))->find();

        $express_fee = 0;
        if ($default_address) {
            $express_fee = db(\tname::config_freight)->where(array('region_code' => $default_address['province_code']))->value('first_money');
        } else {
            $default_address['id'] = 0;
        }
        $total_count["express_fee"] = number_format($express_fee, 2, ".", "");

        $returndata = array(
            'ret' => 1,
            'cartList' => $cartList,
            'total_count' => $total_count,
            'couponList' => $couponList,
            'sel_address' => $default_address,
            'addressList' => $addressList,
        );
        return json(ajaxSuccess($returndata));
    }

    /**
     * 计算运费
     */
    public function count_repress_fee()
    {
        $pdata = input('post.');
        $address = db(\tname::vip_myaddress)->where(array('id' => $pdata['id']))->find();
        $express_fee = 0;
        if ($address) {
            $express_fee = db(\tname::config_freight)->where(array('region_code' => $address['province_code']))->value('first_money');
        }
        $express_fee = number_format($express_fee, 2, ".", "");
        return json(ajaxSuccess($express_fee));
    }

    /**
     * 收藏商品
     */
    public function collect_do()
    {
        $GoodsLogic = new GoodsLogic();
        $pdata = input('post.');
        $result = $GoodsLogic->goodsCollect($pdata['type'], $pdata['vip_id'], $pdata['goods_id']);
        return json($result);
    }

    public function getcoupon()
    {
        $couponLogic = new \app\common\logic\CouponLogic();
        $pdata = input('post.');
        $result = $couponLogic->getCoupon($pdata['vip_id'], $pdata['id']);
        if ($result['status'] != 1) {
            return json(ajaxFalse($result['msg']));
        }
        return json(ajaxSuccess('', $result['msg']));
    }


    /**
     * 开心一刻
     */
    public function happly()
    {
        $pdata = input('post.');
        $funLogic = new FunLogic();
        $list = $funLogic->getFunList($pdata['type'], '', '', '', $pdata['page']);
        return json(ajaxSuccess($list));
    }

    /**
     * 优惠就列表
     */
    public function couponList()
    {
        $pdata = input('post.');
        $pdata['type'] = 'free';
        $funLogic = new \app\common\logic\CouponLogic();
        $list = $funLogic->getCouponList($pdata['type'], $pdata['sort'], $pdata['asc'],$pdata['page']);
        $mycoupon = db(\tname::coupon_mycoupon)->where(array('vip_id'=>$pdata['vip_id']))->column('coupon_id');
        foreach ($list as &$val){
            if(!empty($mycoupon)&&in_array($val['id'],$mycoupon)){
                $val['has']=1;
            }else{
                $val['has']=0;
            }

        }
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
    public function get_search()
    {
        $pdata = input('post.');
        $funLogic = new \app\common\logic\BasicLogic();
        $result = $funLogic->get_search($pdata['vip_id']);
        $returndata = array(
            'keywordList' => $result,
        );
        return json(ajaxSuccess($returndata));
    }

    /**
     * 清空搜索历史
     */
    public function clear_keyword()
    {
        $pdata = input('post.');
        $res = db('vip_search')->where(array('vip_id' => $pdata['vip_id']))->update(array('keyword' => ""));
        return json(ajaxSuccess("", "搜索历史已清空！"));
    }


}