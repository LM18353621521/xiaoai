<?php

namespace app\mobile\controller;

use app\common\logic\CartLogic;

class Cart extends Base
{
    public function cart()
    {
        $user = session('userinfo');

        $pdata = input('');
        $pdata['vip_id'] = $user['vip_id'];
        $CartLogic = new CartLogic();
        $result = $CartLogic->getCartList($pdata);
        $returndata = array(
            'cartList' => $result['cartList'],
            'total_count' => $result['total_count'],
        );
        $this->assign('cartList', $result['cartList']);
        $this->assign('total_count', $result['total_count']);
        $this->assign('title', "购物车");
        return $this->fetch();
    }

    /**
     * 更新购物车
     */
    public function cart_update()
    {
        $user = session('userinfo');
        if (empty($user)) {
            return ajaxFalse('您未登录，立即登录？', '', '', 2);
        }

        $CartLogic = new CartLogic();
        $pdata = input('');
        $pdata['vip_id'] = $user['vip_id'];

        $result = $CartLogic->cart_update($pdata);
        if ($result['status'] == 0) {
            return json(ajaxFalse($result['msg']));
        }
        return json(ajaxSuccess("", $result['msg']));
    }


    /**
     * ajax更新购物车
     */
    public function ajax_cart_update()
    {
        $user = session('userinfo');

        $CartLogic = new CartLogic();
        $pdata = input('post.');
        $pdata['vip_id'] = $user['vip_id'];

        if (!$pdata['vip_id']) {
            return json(ajaxFalse('！您还没有登录'));
        }
        $result = $CartLogic->ajax_cart_update($pdata);
        if ($result['status'] == 0) {
            return json(ajaxFalse($result['msg']));
        }
        return json(ajaxSuccess("", $result['msg']));
    }

    public function cart_select()
    {
        $user = session('userinfo');
        $_post = input('');
        $where['vip_id'] = $user['vip_id'];
        if ($_post['type'] == 1) {
            $where['id'] = $_post['id'];
            $res = db(\tname::mall_cart)->where($where)->update(array('selected' => $_post['selected']));
        } else {
            $res = db(\tname::mall_cart)->where($where)->update(array('selected' => $_post['selected']));
        }
        if (!$res) {
            return json(ajaxFalse("！操作失败，请稍后重试"));
        }
        return json(ajaxSuccess("", "操作成功！"));
    }



}