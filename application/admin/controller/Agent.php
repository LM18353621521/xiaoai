<?php
namespace app\admin\controller;

use think\Db;

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

    //修改等级    2017-10-15
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

    //申请列表
    public function apply_list()
    {
        $statusList = array(
            '0'=>'待审核',
            '1'=>'审核通过',
            '2'=>'审核不通过'
        );
        $this->assign('statusList', $statusList);
        if (request()->isPost()) {
            $search = input('post.');
            $where = [];
            if ($search['stime'] && $search['etime']) {
                $where['a.create_time'] = array('between time', [$search['stime'], $search['etime']]);
            }
            if ($search['keyword']) {
                $where['a.real_name|a.mobile'] = ['like', '%' . $search['keyword'] . '%'];
            }
            if ($search['status'] != null && $search['status'] != "") {
                $where['a.status'] = $search['status'];
            }
            session('where', $where);
            $dataList = db(\tname::agent_apply)->alias('a')
                ->join('vip v','a.vip_id=v.id')
                ->field('a.*,v.nickname')
                ->where($where)->order('a.id desc')->paginate(30, false, ['page' => $search['page']]);
            $this->assign('dataList', $dataList);
            $html = $this->fetch('agent/form');
            $attach = [
                'page' => $dataList->render()
            ];
            return ajaxSuccess($html, '', '', $attach);
        }
        return $this->fetch();
    }
    /**
     * 申请审核
     */
    public function applyHandle(){
        if (request()->isPost()) {
            $_post = input('post.');
            $apply = db(\tname::agent_apply)->where(array('id'=>$_post['id']))->find();
            if(empty($apply)){
                return ajaxFalse('抱歉，参数错误，请联系管理员');
            }
            $user = db(\tname::vip)->where(array('id'=>$apply['vip_id']))->find();
            if($apply['status']!=0){
                return ajaxFalse('该记录已审核，请勿重复操作');
            }
            $updateData =array(
                'id'=>$_post['id'],
                'status'=>$_post['afterchange'],
                'check_time'=>time()
            );
            Db::startTrans();
            if($_post['afterchange']==1){
                $agent = db(\tname::agent_user)->where(array('mobile' => $apply['mobile']))->find();
                if(empty($agent)==false){
                    return ajaxFalse("审核失败，手机号[{$apply['mobile']}]已存在代理");
                }
                $agentData = array(
                    'vip_id'=>$apply['vip_id'],
                    'real_name'=>$apply['real_name'],
                    'birthday'=>$apply['birthday'],
                    'wechat'=>$apply['wechat'],
                    'mobile'=>$user['mobile'],
                    'level_id'=>1,
                    'status'=>1,
                );
                $res1 = dataUpdate(\tname::agent_apply,$updateData);
                if(empty($res1)){
                    Db::rollback();
                    return ajaxFalse('操作失败，请联系管理员');
                }
                $res2 = dataUpdate(\tname::agent_user,$agentData);
                if(empty($res2)){
                    Db::rollback();
                    return ajaxFalse('操作失败，写入代理信息失败，请联系管理员');
                }
            }else{
                $res1 = dataUpdate(\tname::agent_apply,$updateData);
                if(empty($res1)){
                    Db::rollback();
                    return ajaxFalse('操作失败，请联系管理员');
                }
            }
            Db::commit();
            return ajaxSuccess('', '');
        }
    }

    /**
     * 充值列表
     */
    public function recharge_list()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where['a.status'] = 1;
            if ($search['time']) {
                $search['time'] = explode('-', $search['time']);
                $where['a.create_time'] = array('between time', [$search['time'][0], $search['time'][1]]);
            }
            if ($search['keyword'] !== null && $search['keyword'] !== '') {
                $where['v.real_name|a.order_sn'] = array('like', '%' . $search['keyword'] . '%');
            }
            $dataList = db(\tname::agent_recharge)->alias('a')
                ->join('xa_' . \tname::agent_user . ' v', 'a.agent_id=v.id', 'left')
                ->field('a.*,v.real_name')
                ->where($where)->order('a.id desc')->paginate(50, false, array('page' => $search['page']));

            $statusList =array(
                '0'=>'未支付',
                '1'=>'充值成功',
                '2'=>'充值失败',
            );
            $this->assign('statusList', $statusList);
            $this->assign('dataList', $dataList);
            $html = $this->fetch('agent/form');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }

        return $this->fetch();
    }

    //余额明细    2018-11-05
    public function balance_log()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where = array(
                'a.uid' => UID,
                'a.classify'=>'agentMoney'
            );
            if ($search['stime']&&$search['etime']) {
                $where['a.create_time'] = array('between time', [$search['stime'], $search['etime']]);
            }
            if ($search['keyword'] !== null && $search['keyword'] !== '') {
                $where['v1.nickname|v2.nickname|o.order_number'] = array('like', '%' . $search['keyword'] . '%');
            }

            $dataList = db(\tname::data_changelog)->alias('a')
                ->join('xa_' . \tname::agent_user . ' v', 'a.main_id = v.id', 'left')
                ->field('a.*,v.real_name')
                ->where($where)->order('a.id desc')->paginate(30, false, array('page' => $search['page']));

            $this->assign('dataList', $dataList);
            $html = $this->fetch('agent/form');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }

        return $this->fetch();
    }


}