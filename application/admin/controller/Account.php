<?php
namespace app\admin\controller;

class Account extends Member
{

    /**
     * 权限组列表    2017-10-15
     */
    public function auth()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where = array(
                'status' => 1,
                'is_hidden' => 0
            );
            $dataList = db(\tname::auth_group)->where($where)->order('id desc')->paginate(500);

            $this->assign('dataList', $dataList);
            $html = $this->fetch('account/form');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }
        return $this->fetch();
    }

    /**
     * 权限组列表    2017-10-15
     */
    public function authadd()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['rules'] = implode(',',$data['rules']);
            $data['uid'] = UID;
            $res = dataUpdate(\tname::auth_group, $data);
            if(!$res){
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::auth_group)->find($id);

            $where = [
                'status'=>1
            ];
            $loginer  =db(\tname::system_loginer)->find(LID);
            if($loginer['type'] != 0){
                $group = db(\tname::auth_group)->find($loginer['type']);
                $where['id'] = array('in',$group['rules']);

            }
            $ruleList = db(\tname::auth_rule)->where($where)->select();
            foreach($ruleList as $k=>$v){
                $auth[$v['group_name']][] = $v;
            }

            $this->assign('data', $data);
            $this->assign('auth', $auth);
            return $this->fetch();
        }
    }


    /**
     * 子账号列表    2017-10-15
     */
    public function account()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where = array(
                'uid' => UID,
                'status' => array('egt', 0),
                'rank' => array('gt', 1)
            );
            if ($search['stime']&&$search['etime']) {
                $where['a.create_time'] = array('between time', [$search['stime'], $search['etime']]);
            }

            if ($search['keyword']) {
                $where['username'] = array('like', '%' . $search['keyword'] . '%');
            }

            $dataList = db(\tname::system_loginer)->where($where)->order('id desc')->paginate(50, false, array('page' => $search['page']));

            $this->assign('dataList', $dataList);
            $html = $this->fetch('account/form');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }
        return $this->fetch();
    }

    /**
     * 子账号添加    2017-10-15
     */
    public function accountadd()
    {
        if (request()->isPost()) {
            $data = input('post.');

            if ($data['password'] != $data['repassword']) {
                return ajaxFalse('添加失败，密码和重复密码不一致！');
            }

            if (!$data['id']) {
                $Login = new \wechat\Login;
                $loginerid = $Login->register($data['username'], $data['password']);

                if ($loginerid > 0) { //注册成功
                    $my = db(\tname::system_loginer)->find(LID);
                    $userData = array(
                        'id' => $loginerid,
                        'uid' => UID,
                        'pid' => LID,
                        'rank' => $my['rank'] + 1,
                        'reg_time' => time(),
                        'reg_ip' => get_client_ip(1),
                        'type' => $data['type'],
                        'status' => 1
                    );

                    $res = dataUpdate(\tname::system_loginer, $userData);
                    if (!$res) {
                        return ajaxFalse();
                    }
                    return ajaxSuccess();
                } else {
                    return ajaxFalse('用户名重复，请更换用户名');
                }
            } else {
                if (!empty($data['password'])) {
                    $arr = array('password' => $data['password']);
                    $Login = new \wechat\Login;
                    $res1 = $Login->updateinfo($data['id'], false, $arr);
                }
                unset($data['password']);
                $res = dataUpdate(\tname::system_loginer, $data);
                if (!$res) {
                    return ajaxFalse();
                }
                return ajaxSuccess();
            }
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::system_loginer)->find($id);

            $groupList = db(\tname::auth_group)->where(array('is_hidden'=>0,'status'=>1))->select();

            $this->assign('data', $data);
            $this->assign('groupList', $groupList);
            return $this->fetch();
        }
    }
}