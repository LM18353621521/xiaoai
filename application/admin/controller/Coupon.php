<?php
/**
 * 优惠券管理
 * Created by PhpStorm.
 * User: Lu
 * Date: 2018/9/10
 * Time: 22:58
 */

namespace app\admin\controller;


class Coupon extends  Member
{
    public function _initialize()
    {
        parent::_initialize();
        $this->assign('start_time',strtotime('-1 years'));
        $this->assign('end_time',strtotime('+1 day'));
    }

    /**
     * 优惠券列表
     */
    public function coupon_list(){
        if (request()->isPost()) {
            $search = input('post.');
            $where = [
                'a.uid' => UID,
                'a.is_hidden' => 0
            ];
            $start_time = strtotime($search['start_time']);
            $end_time = strtotime($search['end_time']);


            if($start_time&&$end_time){
                $where['a.create_time'] = ['between', [$start_time, $end_time]];
            }

            if ($search['keyword']) {
                $where['a.name'] = ['like', '%' . $search['keyword'] . '%'];
            }
            if ($search['is_publish'] != '') {
                $where['a.is_publish'] = $search['is_publish'];
            }

            $dataList = db(\tname::coupon)
                ->alias('a')
                ->field('a.* ')
                ->where($where)->order('id desc')->paginate(20, false, ['page' => $search['page']]);

            $this->assign('dataList', $dataList);
            $html = $this->fetch('coupon/form');

            return ajaxSuccess($html);
        }
        return $this->fetch();
    }

    /**
     * 添加优惠券2018-09-10
     * @return array|mixed|string
     */
    public function coupon_add()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;
            $data['use_stime']=strtotime($data['use_stime']);
            $data['use_etime']=strtotime($data['use_etime']);
            if($data['use_stime']>=$data['use_etime']){
                return ajaxFalse('使用开始时间必须小于结束时间');
            }

            $res = dataUpdate(\tname::coupon, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::coupon)->find($id);
            if($data){
                $use_stime = $data['use_stime'];
                $use_etime= $data['use_etime'];
            }else{
                $use_stime = strtotime("+1 day");
                $use_etime= strtotime("+1 month");
            }
            //类型
            $typeList = array(
                'free'=>'免费领取'
            );
            $this->assign('typeList', $typeList);
            $this->assign('data', $data);
            $this->assign('use_stime', $use_stime);
            $this->assign('use_etime', $use_etime);
            return $this->fetch();
        }
    }

}