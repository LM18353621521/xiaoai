<?php
namespace app\admin\controller;

class Agent extends Member
{
    //用户列表    2017-10-15
    public function index()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where = [];
            if ($search['stime']&&$search['etime']) {
                $where['a.create_time'] = array('between time', [$search['stime'], $search['etime']]);
            }
            if ($search['keyword']) {
                $where['real_name|mobile'] = ['like', '%' . $search['keyword'] . '%'];
            }
            session('where', $where);
            $dataList = db(\tname::agent_user)->where($where)->order('id desc')->paginate(30, false, ['page' => $search['page']]);
            $this->assign('dataList', $dataList);
            $levelList = db(\tname::agent_level)->where($where)->order('id desc')->column('id,name');
            $this->assign('levelList', $levelList);
            $html = $this->fetch('agent/form');
            $attach = [
                'page' => $dataList->render()
            ];
            return ajaxSuccess($html, '', '', $attach);
        }
        return $this->fetch();
    }

    //用户列表导出    2017-10-15
    public function export()
    {
        $where = session('where');
        $dataList = db(\tname::agent_user)->where($where)->order('id desc')->select();
        $levelList = db(\tname::agent_level)->where($where)->order('id desc')->column('id,name');
        foreach ($dataList as $key => $value) {
            $data[$key] = [
                $value['real_name'],//昵称
                $value['mobile'],
                $levelList[$value['level_id']],
                $value['sex']==1?'男':'女',//性别
                $value['birthday'],
                $value['money'],
                $value['total_money'],
                date('Y-m-d H:i:s', $value['create_time']),//时间
            ];
        };

        $fileName = "代理商列表";
        $headArr = ["姓名", "手机号码","当前等级","性别", "出生日期","当前余额","累计充值","创建时间"];
        $msg = '性别：1代表男 2代表女';
        $res = getExcel($fileName, $headArr, $data, $msg);
        return $res;
    }

    //意见反馈    2017-10-15
    public function level_list()
    {
        if (request()->isPost()) {
            $dataList = db(\tname::agent_level)
                ->order('id asc')->select();
            $this->assign('dataList', $dataList);
            $html = $this->fetch('agent/form');
            return ajaxSuccess($html, '', '');
        }
        return $this->fetch();
    }

    //修改意见反馈    2017-10-15
    public function edit_level()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $where = array(
                'name'=>$data['name'],
                'id'=>['<>',$data['id']],
            );
            $check_name =db(\tname::agent_level)->where($where)->find();
            if($check_name){
                return ajaxFalse('已有相同名称等级存在');
            }
            $res = dataUpdate(\tname::agent_level, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::agent_level)
                ->find($id);
            $this->assign('data', $data);
            return $this->fetch();
        }
    }

    /**
     * 充值列表
     */
    public function recharge()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where = [];
            if ($search['time']) {
                $search['time'] = explode('-', $search['time']);
                $where['a.create_time'] = array('between time', [$search['time'][0], $search['time'][1]]);
            }
            if ($search['keyword'] !== null && $search['keyword'] !== '') {
                $where['v.real_name|a.order_sn'] = array('like', '%' . $search['keyword'] . '%');
            }
            $dataList = db(\tname::money_order)->alias('a')
                ->join('xa_' . \tname::vip . ' v', 'a.openid=v.openid', 'left')
                ->field('a.*,v.nickname nickname')
                ->where($where)->order('a.id desc')->paginate(50, false, array('page' => $search['page']));

            $this->assign('dataList', $dataList);
            $html = $this->fetch('money/form');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }

        return $this->fetch();
    }

}