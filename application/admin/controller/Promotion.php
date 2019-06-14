<?php
namespace app\admin\controller;
use think\Config;
use think\Paginator;
use think\Loader;
use think\Db;

class Promotion extends Member
{

    //秒杀活动列表    2018-11-18
    public function seckill()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where = array(
                'a.is_hidden' => 0
            );

            if ($search['stime'] && $search['etime']) {
                $where['a.create_time'] = array('between time', [$search['stime'], $search['etime']]);
            }

            if ($search['keyword']) {
                $where['a.title|a.goods_name'] = array('like', '%' . $search['keyword'] . '%');
            }
            if ($search['is_publish'] != '') {
                $where['a.is_publish'] = $search['is_publish'];
            }


            $dataList = db(\tname::flash_sale)->alias('a')
                ->field('a.*')
                ->where($where)->order('a.id desc')->paginate(20, false, array('page' => $search['page']));

            $this->assign('dataList', $dataList);
            $html = $this->fetch('promotion/form');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }

        return $this->fetch();
    }

    //添加产品    2017-10-15
    public function seckill_detail()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
            $res = dataUpdate(\tname::flash_sale, $data);

            if($data['id']){
                db(\tname::mall_product)->where(['prom_type' => 1, 'prom_id' => $data['id']])->update(array('prom_id' => 0, 'prom_type' => 0));
                if($data['item_id'] > 0){
                    //设置商品一种规格为活动
                    db(\tname::goods_spec_price)->where(['prom_type' => 1, 'prom_id' => $data['item_id']])->update(['prom_id' => 0, 'prom_type' => 0]);
                    db(\tname::goods_spec_price)->where('item_id', $data['item_id'])->update(['prom_id' => $data['id'], 'prom_type' => 1]);
                    db(\tname::mall_product)->where("id", $data['goods_id'])->update(array('prom_id' => 0, 'prom_type' => 1));
                }else{
                    db(\tname::mall_product)->where("id", $data['goods_id'])->update(array('prom_id' => $data['id'], 'prom_type' => 1));
                }
                adminLog("管理员修改抢购活动 " . $data['title']);
            }else{
                if($data['item_id'] > 0){
                    //设置商品一种规格为活动
                    db(\tname::goods_spec_price)->where('item_id',$data['item_id'])->update(['prom_id' => $res, 'prom_type' => 1]);
                    db(\tname::mall_product)->where("id", $data['goods_id'])->update(array('prom_id'=>0,'prom_type' => 1));
                }else{
                    db(\tname::mall_product)->where("id", $data['goods_id'])->update(array('prom_id' => $res, 'prom_type' => 1));
                }
                adminLog("管理员添加抢购活动 " . $data['title']);
            }



            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::flash_sale)->find($id);

            if($data){
                $data['specGoodsPrice']=array();
                $goods = db(\tname::mall_product)->where(array('id'=>$data['goods_id']))->find();
                if($data['item_id']>0){
                    $specGoodsPrice =db(\tname::goods_spec_price)->where(array('item_id'=>$data['item_id']))->find();
                    $data['specGoodsPrice']=$specGoodsPrice;
                }
                $data['goods']=$goods;
            }

            $this->assign('data', $data);
            return $this->fetch();
        }
    }


    public function search_goods()
    {
        error_reporting(E_ERROR | E_PARSE );
        $tpl = input('tpl', 'search_goods');
        if (request()->isPost()) {
            $search = input('post.');
            $where = array(
                'a.uid' => UID,
                'a.is_hidden' => 0,
//                'a.is_publish' => 1,
            );

            if ($search['keyword']) {
                $where['a.name|c.name|a.brief'] = array('like', '%' . $search['keyword'] . '%');
            }

            $dataList = db(\tname::mall_product)->alias('a')
                ->join(\tname::mall_category . ' c', 'a.category_id = c.id', 'left')
                ->field('a.*,c.name category_name')
                ->where($where)->order('a.is_publish desc,a.id desc')->paginate(10, false, array('page' => $search['page']))
                ->each(function ($item, $key) {
                    $specGoodsPrice = db(\tname::goods_spec_price)->where(array('goods_id'=>$item['id']))->select();
                    $item['specGoodsPrice'] = $specGoodsPrice;
                    return $item;
                });
            $this->assign('dataList', $dataList);
            $html = $this->fetch('promotion/ajax_search_goods');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }
        return $this->fetch($tpl);
    }





}