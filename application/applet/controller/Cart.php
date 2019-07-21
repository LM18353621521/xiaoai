<?php
namespace app\applet\controller;
use app\common\logic\CartLogic;
class Cart extends Applet
{

    public function cart(){
        $pdata =input('');
        $CartLogic = new CartLogic();
        //获取代理信息
        $AgentLogic = new \app\common\logic\AgentLogic();
        $agent =$AgentLogic->getAgentInfo($pdata['vip_id']);
        $result = $CartLogic->getCartList($pdata,$agent);
        $returndata = array(
            'cartList' => $result['cartList'],
            'total_count' => $result['total_count'],
        );
        return json(ajaxSuccess($returndata));
    }


    /**
     * 更新购物车
     */
    public function cart_update(){
        $CartLogic = new CartLogic();
        $pdata = input('');

        if(!$pdata['vip_id']){
            return json(ajaxFalse('！您还没有登录'));
        }
        $result =$CartLogic->cart_update($pdata);
        if($result['status']==0){
             return json(ajaxFalse($result['msg']));
        }
        return json(ajaxSuccess("",$result['msg']));
    }


    /**
     * ajax更新购物车
     */
    public function ajax_cart_update(){
        $CartLogic = new CartLogic();
        $pdata = input('');

        if(!$pdata['vip_id']){
            return json(ajaxFalse('！您还没有登录'));
        }
        $result =$CartLogic->ajax_cart_update($pdata);
        if($result['status']==0){
            return json(ajaxFalse($result['msg']));
        }
        return json(ajaxSuccess("",$result['msg']));
    }
    public function cart_select(){
        $pdata = input('');
        $res = db(\tname::mall_cart)->whereIn('id',$pdata['cart_ids'])->update(array('selected'=>$pdata['selected']));
        if(!$res){
            return json(ajaxFalse("！操作失败，请稍后重试"));
        }
        return json(ajaxSuccess("","操作成功！"));
    }

    /**
     * 删除购物车
     */
    public function cart_del(){
        $pdata = input('post.');
        $res = db(\tname::mall_cart)->where(array('id' => $pdata['id']))->delete();
        if (!$res) {
            return json(ajaxFalse('！操作失败，请稍后重试'));
        }
        return json(ajaxSuccess("",'删除成功'));
    }

}