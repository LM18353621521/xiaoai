<?php

namespace app\applet\controller;

use app\common\logic\UserLogic;

class Vip extends Applet
{
    /**
     * 首页    2018-01-16
     */
    public function index()
    {
        $pdata = input('post.');
        $vip = db(\tname::vip)->where(['uid' => WID, 'id' => $pdata['vip_id']])->find();
        $vip['headimg'] = $vip['headimgurl'];
        $config = tpCache('base');
        $config['logo'] = imgurlToAbsolute(tpCache('web.logo'));
        $config['slogen'] = tpCache('web.slogen');
        $config['bind_tips'] = "找不到订单？绑定手机试试";
        $OrderLogic = new \app\common\logic\OrderLogic();
        $vip_ids = getVipIds($pdata['vip_id']);
        $order_status = $OrderLogic->get_order_num($vip_ids);

        $ajaxdata = [
            'vip' => $vip,
            'order_status' => $order_status,
            'config' => $config,
        ];
        return json(ajaxSuccess($ajaxdata));
    }

    public function qrcode()
    {
        $result = $this->create_user_qrcode(1);
        $result = edit_img($result);
        dump(imgurlToAbsolute($result));

//        imagecopyresampled($target, $main, 0, 0, 0, 0, $width, $height, $width, $height);

//        dump($result);
    }

    public function create_user_poster()
    {
        $pdata = input('post.');
        $qrcode = $this->create_user_qrcode($pdata['vip_id']);
        $config = tpCache('base');
        $bg_img = $config['user_poster_bg'];

        $textInfo[0] = array(
            'text' => '长按识别小程序码进入商城',
            'color' => array(95, 95, 95),
            'fontsize' => 14,
            'width' => 310,
            'left' => 70,
            'top' => 395,
        );
        $textInfo[1] = array(
            'text' => "晓爱商城",
            'color' => array(255, 0, 0),
            'fontsize' => 10,
            'width' => 310,
            'left' => 150,
            'top' => 450,
        );
        $imgInfo[0] = array(
            'img' => $qrcode,
            'left' => 47.5,
            'top' => 30,
        );
        $result = create_user_poster(1, $bg_img, $imgInfo, $textInfo);
        $poster_img = imgurlToAbsolute($result);
        $returndata = array(
            'poster_img' => $poster_img,
        );
        return json(ajaxSuccess($returndata));
    }


    /**
     * 分销中心
     */
    public function distribution()
    {
        $pdata = input('post.');
        $vip = db(\tname::vip)->where(['uid' => WID, 'id' => $pdata['vip_id']])->find();
        $vip['headimg'] = $vip['headimgurl'];
        //生成二维码
        $qrcode = $this->create_user_qrcode($pdata['vip_id']);
        $vip['qrcode'] = $qrcode;
        $config = tpCache('base');
        $config['text_tips1'] = "长按识别小程序码进入商城";
        $config['text_tips2'] = "晓爱商城";
        $config['withdraw_tips'] = "提现金额将会以微信公众号红包的形式放至您的微信中，领取后提现至零钱包";
        //佣金收益
        $income['income_all'] = $vip['income_all'];
        $where_wei= array(
            'status'=>0,
            'order_status'=>1,
            'distributor_vip_id'=>$pdata['vip_id'],
        );
        $income_wei = db(\tname::distribution_income)->where($where_wei)->sum('money');
        $income['income_wei'] = sprintf("%.2f", $income_wei);
        //本月总佣金
        $month_start = strtotime('this month');
        $month_end = time();
        $where_month = array(
            'create_time'=>['between',[$month_start,$month_end]],
            'status'=>['gt',0],
            'order_status'=>['gt',0],
            'distributor_vip_id'=>$pdata['vip_id'],
        );
        $month_all = db(\tname::distribution_income)->where($where_month)->sum('money');
        $income['month_all'] = sprintf("%.2f", $month_all);
        //本月已返
        $where_month = array(
            'create_time'=>['between',[$month_start,$month_end]],
            'status'=>1,
            'distributor_vip_id'=>$pdata['vip_id'],
        );
        $month_fan = db(\tname::distribution_income)->where($where_month)->sum('money');
        $income['month_fan'] = sprintf("%.2f", $month_fan);

        $ajaxdata = [
            'vip' => $vip,
            'income' => $income,
            'config' => $config,
        ];
        return json(ajaxSuccess($ajaxdata));
    }

    public function create_user_qrcode($vip_id)
    {
        $savepath = '/public/uploads/picture/uid2/qrcode/';
        if (!file_exists(ROOT_PATH . $savepath)) {
            mkdir(ROOT_PATH . $savepath, 0777, true);
        }
        $qrocde_path = '/uploads/picture/uid2/qrcode/' . md5(get_domain() . $vip_id) . '.jpg';//小程序码
        if (file_exists(ROOT_PATH . "/public" . $qrocde_path)) {
            return get_domain() . $qrocde_path;
        }
        $data = array(
            'scene' => $vip_id,
            'page' => 'pages/home/index/index',
            'width' => 200,
        );
        $this->createappletcode2(2, ROOT_PATH . "/public" . $qrocde_path, $data);
        return get_domain() . $qrocde_path;
    }

    public function getqrcode(){
        $savepath = '/public/uploads/picture/uid2/qrcode/';
        if (!file_exists(ROOT_PATH . $savepath)) {
            mkdir(ROOT_PATH . $savepath, 0777, true);
        }
        $qrocde_path = '/uploads/picture/uid2/qrcode/' . md5(get_domain() ) . '001.jpg';//小程序码
        if (file_exists(ROOT_PATH . "/public" . $qrocde_path)) {
            return get_domain() . $qrocde_path;
        }
        $data = array(
            'scene' => 0,
            'page' => 'pages/home/distributgoods/distributgoods',
            'width' => 200,
        );
        $this->createappletcode2(2, ROOT_PATH . "/public" . $qrocde_path, $data);
        return get_domain() . $qrocde_path;
    }

    /**
     * 佣金记录
     */
    public function distribution_log()
    {
        $UserLogic = new UserLogic();
        $pdata = input('');
        $result = $UserLogic->getDistributionLog($pdata['vip_id'], $pdata['status'], $pdata['keyword'], $pdata['sort'], $pdata['asc'], $pdata['page'], $pdata['pagenum']);
        return json(ajaxSuccess($result));
    }

    /**
     * 提现
     */
    public function withdraw()
    {
        $pdata = input('');

        $vip = db(\tname::vip)->where(array('id' => $pdata['vip_id']))->find();
        if ($vip['income'] < $pdata['money']) {
            return json(ajaxSuccess('您的可提现金额为：' . $vip['income'] . '元'));
        }

        $order_number = createOrdernumber(\tname::money_withdraw, "order_number", "", 4);
        $data = array(
            'uid' => WID,
            'vip_id' => $pdata['vip_id'],
            'money' => $pdata['money'],
            'order_number' => $order_number,
        );
        $res = dataUpdate(\tname::money_withdraw, $data);
        if (!$res) {
            return json(ajaxFalse('！提现失败，请稍后重试'));
        }
        $res1 = dataChangeLog(WID, "income", "withdraw", $pdata['vip_id'], -$pdata['money'], $res, "提现扣除");
        return json(ajaxSuccess('！提现成功'));

    }

    /**
     * 提现历史记录
     */
    public function withdrawlist()
    {
        $pdata = input('post.');
        $vip = db(\tname::vip)->where(['uid' => WID, 'id' => $pdata['vip_id']])->find();
        $vip['headimg'] = $vip['headimgurl'];
        $config = tpCache('base');
        $config['withdraw_tips'] = "提现金额将会以微信公众号红包的形式放至您的微信中，领取后提现至零钱包";

        $ajaxdata = [
            'vip' => $vip,
            'config' => $config,
        ];
        return json(ajaxSuccess($ajaxdata));
    }

    /**
     * 提现历史记录列表
     */
    public function withdraw_log()
    {
        $UserLogic = new UserLogic();
        $pdata = input('');
        $result = $UserLogic->getWithdrawLog($pdata['vip_id'], $pdata['status'], $pdata['keyword'], $pdata['sort'], $pdata['asc'], $pdata['page'], $pdata['pagenum']);
        return json(ajaxSuccess($result));
    }


    /**
     * @return \think\response\Json
     */
    public function mycollect()
    {
        $pdata = input('post.');
        $keyword = $pdata['keyword'];
        $asc = $pdata['asc'];
        $page = $pdata['page'];
        $sort = $pdata['sort'];
        $page_size = $pdata['pagenum'];
        $keyword = trim($keyword);
        $asc = $asc ? $asc : "asc";
        $p = $page ? $page : 0;
        $where = array(
            'vip_id' => array('in', $pdata['vip_ids']),
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
        return json(ajaxSuccess($dataList));
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
        $pdata = input('post.');
        $mobile = $pdata['mobile'];
        $vip = db(\tname::vip)->where(array('id' => $pdata['vip_id']))->find();
        //检查是否已经被绑定
        $check_bind = db(\tname::vip)->where(array('mobile' => $mobile, 'source' => 2))->find();
        if ($check_bind && $check_bind['id'] != $pdata['vip_id']) {
            return json(ajaxFalse('该手机号码已被绑定，请重新输入'));
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
        $login_info = array(
            'vip_id' => $vip['id'],
            'openid' => $vip['openid'],
            'vip_ids' => $ids,
        );
        return json(ajaxSuccess($login_info, "绑定成功！"));
    }

    /**
     * 我的优惠券
     * @return \think\response\Json
     */
    public function mycoupon()
    {
        $pdata = input('post.');
        $keyword = $pdata['keyword'];
        $asc = $pdata['asc'];
        $page = $pdata['page'];
        $sort = $pdata['sort'];
        $page_size = $pdata['pagenum'];
        $keyword = trim($keyword);
        $asc = $asc ? $asc : "asc";
        $p = $page ? $page : 0;
        $vip_id = $pdata['vip_id'];
        $where = array(
            'vip_id' => array('in', $pdata['vip_ids']),
        );

        switch ($pdata['status']) {
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
        foreach ($dataList as &$value) {

        }
        return json(ajaxSuccess($dataList));
    }


    //获取列表意见反馈 2018-02-28
    public function feedbackgetlist()
    {
        $data = input('post.');

        $where = [
            'a.uid' => WID,
            'a.ishidden' => 0,
            'a.openid' => $data['openid']
        ];

        $feedbackList = db(\tname::vip_feedback)->alias('a')
            ->join('wechat_' . \tname::vip . ' v', 'a.openid = v.openid', 'left')
            ->where($where)->field("a.*,v.nickname nickname")->order('a.id desc')->paginate(50, false, ['page' => $data['page']])
            ->each(function ($item, $key) {
                $item['create_time'] = date("Y-m-d H:i", $item['create_time']);
                return $item;
            });

        return json(ajaxSuccess($feedbackList));
    }

    //添加意见反馈 2018-02-28
    public function feedbackadd()
    {
        $data = input('post.');
        $data['uid'] = WID;
        $data['create_time'] = time();
        if (!$data['linkman']) {
            return json(ajaxFalse('请输入联系人'));
        }
        if (!isMobilephone($data['linktel'])) {
            return json(ajaxFalse('请输入正确的电话'));
        }
        if (!$data['content']) {
            return json(ajaxFalse('请输入您的反馈内容'));
        }
        if (!empty($data['imgurl'])) {
            $data['imgpath'] = serializeMysql(explode(',', $data['imgurl']));
        }

        $res = dataUpdate(\tname::vip_feedback, $data);
        if (!$res) {
            return json(ajaxFalse());
        }

        return json(ajaxSuccess('', '操作成功', '../feedback/feedback'));
    }

    //意见反馈详情    2018-02-28
    public function feedbackdetail()
    {
        $data = input('post.');

        $feedback = db(\tname::vip_feedback)->alias('a')
            ->join('wechat_' . \tname::vip . ' v', 'a.openid=v.openid', 'left')
            ->field('a.*,v.headimgurl headimgurl')->find($data['id']);
        $feedback['create_time'] = date("Y.m.d H:i", $feedback['create_time']);
        if ($feedback['update_time']) {
            $feedback['update_time'] = date("Y.m.d H:i", $feedback['update_time']);
        }
        if ($feedback['imgpath']) {
            $feedback['imgpath'] = serializeMysql($feedback['imgpath'], 1);
            foreach ($feedback['imgpath'] as $k => &$v) {
                $v = imgurlToAbsolute($v);
            }
        }

        $ajaxdata = [
            'feedback' => $feedback,
        ];
        return json(ajaxSuccess($ajaxdata));
    }

    //富文本   2018-03-01
    public function single()
    {
        $data = input('post.');
        $config = db(\tname::config)->where(['uid' => WID])->find();
        if ($data['type'] == 'aboutus') {
            $text['title'] = '关于我们';
            $text['content'] = $config['fuwenben'];
        }

        $ajaxdata = [
            'text' => $text
        ];
        return json(ajaxSuccess($ajaxdata));
    }

    //外部链接   2018-03-01
    public function webview()
    {
        $data = input('post.');

        if ($data['type'] == 'aboutus') {
            $linkurl = 'http://www.baidu.com';
        }

        $ajaxdata = array(
            'linkurl' => $linkurl
        );
        return json(ajaxSuccess($ajaxdata));
    }


}