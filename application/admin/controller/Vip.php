<?php
namespace app\admin\controller;

class Vip extends Member
{
    //用户列表    2017-10-15
    public function vip()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where = [
                'uid' => UID,
                'subscribe' => 1
            ];
            if ($search['stime']&&$search['etime']) {
                $where['a.create_time'] = array('between time', [$search['stime'], $search['etime']]);
            }

            if ($search['keyword']) {
                $where['nickname|province|city|country'] = ['like', '%' . $search['keyword'] . '%'];
            }
            session('where', $where);
            $dataList = db(\tname::vip)->where($where)->order('id desc')->paginate(50, false, ['page' => $search['page']]);

            $this->assign('dataList', $dataList);
            $html = $this->fetch('vip/form');
            $attach = [
                'page' => $dataList->render()
            ];
            return ajaxSuccess($html, '', '', $attach);
        }
        return $this->fetch();
    }

    //用户列表导出    2017-10-15
    public function vipexport()
    {
        $where = session('where');
        $dataList = db(\tname::vip)->where($where)->order('id desc')->select();
        foreach ($dataList as $key => $value) {
            $data[$key] = [
                $value['nickname'],//昵称
                $value['sex'],//性别
                $value['country'] . $value['province'] . $value['city'],//地址
                date('Y-m-d H:i:s', $value['subscribe_time']),//时间
            ];
        };

        $fileName = "会员表";
        $headArr = ["昵称", "性别", "所在地", "时间"];
        $msg = '性别：1代表男 2代表女';
        $res = getExcel($fileName, $headArr, $data, $msg);
        return $res;
    }

    //意见反馈    2017-10-15
    public function feedback()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where = [
                'a.uid' => UID,
                'a.is_hidden' => 0
            ];
            if ($search['stime']&&$search['etime']) {
                $where['a.create_time'] = array('between time', [$search['stime'], $search['etime']]);
            }
            if ($search['keyword']) {
                $where['v.nickname|a.linkman|a.linktel|a.content|a.remark'] = ['like', '%' . $search['keyword'] . '%'];
            }
            if ($search['status'] != '') {
                $where['a.status'] = $search['status'];
            }
            session('where', $where);
            $dataList = db(\tname::vip_feedback)->alias('a')
                ->join('xa_' . \tname::vip . ' v', 'a.openid=v.openid', 'left')
                ->field('a.*,v.nickname nickname')
                ->where($where)->order('a.id desc')->paginate(50, false, ['page' => $search['page']]);

            $this->assign('dataList', $dataList);
            $html = $this->fetch('vip/form');
            $attach = [
                'page' => $dataList->render()
            ];
            return ajaxSuccess($html, '', '', $attach);
        }
        return $this->fetch();
    }

    //修改意见反馈    2017-10-15
    public function feedbackdetail()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;

            $prefeedback = db(\tname::vip_feedback)->find($data['id']);
            if ($prefeedback['remark'] != $data['remark']) {
                $data['update_time'] = time();
            }
            $res = dataUpdate(\tname::vip_feedback, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::vip_feedback)->alias('a')
                ->join('xa_' . \tname::vip . ' v', 'a.openid=v.openid', 'left')
                ->field('a.*,v.nickname nickname')
                ->find($id);
            if ($id) {
                $data['imgpath'] = serializeMysql($data['imgpath'], 1);
            }

            $this->assign('data', $data);
            return $this->fetch();
        }
    }

    //意见反馈列表导出    2017-10-15
    public function feedbackexport()
    {
        $where = session('where');
        $dataList = db(\tname::vip_feedback)->alias('a')
            ->join('xa_' . \tname::vip . ' v', 'a.openid=v.openid', 'left')
            ->field('a.*,v.nickname nickname')
            ->where($where)->order('a.id desc')->select();
        foreach ($dataList as $key => $value) {
            $data[$key] = [
                $value['nickname'],//昵称
                $value['linkman'],//联系人
                $value['linktel'],//联系电话
                $value['content'],//内容
                date('Y-m-d H:i:s', $value['create_time']),//时间
                $value['status'],//状态
            ];
        };

        $fileName = "意见反馈表";
        $headArr = ["昵称", "联系人", "联系电话", "内容", "时间", "状态"];
        $msg = '状态：0代表未处理 1代表已处理';
        $res = getExcel($fileName, $headArr, $data, $msg);
        return $res;
    }
}