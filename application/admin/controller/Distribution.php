<?php
namespace app\admin\controller;

class Distribution extends Member
{

    //分销设置 2017-10-15
    public function config()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;

            $res = dataUpdate(\tname::distribution_config, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $data = db(\tname::distribution_config)->where('uid', UID)->find();

            $this->assign('data', $data);
            return $this->fetch();
        }
    }

    //分销关系表 2017-10-15
    public function mytrader()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where = array(
                'a.uid' => UID
            );
            if ($search['stime']&&$search['etime']) {
                $where['a.create_time'] = array('between time', [$search['stime'], $search['etime']]);
            }
            if ($search['keyword'] !== null && $search['keyword'] !== '') {
                $where['v1.nickname|v2.nickname|v3.nickname'] = array('like', '%' . $search['keyword'] . '%');
            }

            $dataList = db(\tname::distribution_mytrader)
                ->field('a.id,v.nickname,v1.nickname as first_nickname,v2.nickname as second_nickname,v3.nickname as third_nickname,a.create_time')
                ->alias('a')
                ->join('xa_' . \tname::vip . ' v', 'a.vip_id = v.id', 'left')
                ->join('xa_' . \tname::vip . ' v1', 'a.first_vip_id = v1.id', 'left')
                ->join('xa_' . \tname::vip . ' v2', 'a.second_vip_id = v2.id', 'left')
                ->join('xa_' . \tname::vip . ' v3', 'a.third_vip_id = v3.id', 'left')
                ->where($where)->order('a.id desc')->paginate(30, false, array('page' => $search['page']));

            $this->assign('dataList', $dataList);
            $html = $this->fetch('distribution/form');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }

        return $this->fetch();
    }

    //修改分销关系    2017-10-15
    public function mytraderdetail()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;

            $res = dataUpdate(\tname::distribution_mytrader, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::distribution_mytrader)->find($id);
            if ($id) {
                $data['vip'] = db(\tname::vip)->where(array('id' => $data['vip_id']))->find();
            }

            $where_vip = array(
                'uid' => UID,
                'subscribe' => 1
            );
            $vipList = db(\tname::vip)->where($where_vip)->select();

            $this->assign('vipList', $vipList);
            $this->assign('data', $data);
            return $this->fetch();
        }
    }

    //佣金记录    2017-10-15
    public function income()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where = array(
                'a.uid' => UID
            );
            if ($search['stime']&&$search['etime']) {
                $where['a.create_time'] = array('between time', [$search['stime'], $search['etime']]);
            }
            if ($search['keyword'] !== null && $search['keyword'] !== '') {
                $where['v1.nickname|v2.nickname|o.order_number'] = array('like', '%' . $search['keyword'] . '%');
            }

            $dataList = db(\tname::distribution_income)->alias('a')
                ->join('xa_' . \tname::vip . ' v1', 'a.distributor_vip_id = v1.id', 'left')
                ->join('xa_' . \tname::vip . ' v2', 'a.vip_id = v2.id', 'left')
                ->join('xa_' . \tname::mall_order . ' o', 'a.order_id = o.id', 'left')
                ->field('a.*,v2.nickname nickname,v1.nickname distributor_nickname,o.order_number order_number')
                ->where($where)->order('a.id desc')->paginate(30, false, array('page' => $search['page']));

            $this->assign('dataList', $dataList);
            $html = $this->fetch('distribution/form');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }

        return $this->fetch();
    }



    //佣金明细    2018-11-05
    public function income_log()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where = array(
                'a.uid' => UID
            );
            if ($search['stime']&&$search['etime']) {
                $where['a.create_time'] = array('between time', [$search['stime'], $search['etime']]);
            }
            if ($search['keyword'] !== null && $search['keyword'] !== '') {
                $where['v1.nickname|v2.nickname|o.order_number'] = array('like', '%' . $search['keyword'] . '%');
            }

            $dataList = db(\tname::data_changelog)->alias('a')
                ->join('xa_' . \tname::vip . ' v', 'a.main_id = v.id', 'left')
                ->field('a.*,v.mobile,v.nickname')
                ->where($where)->order('a.id desc')->paginate(30, false, array('page' => $search['page']));

            $this->assign('dataList', $dataList);
            $html = $this->fetch('distribution/form');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }

        return $this->fetch();
    }





















}