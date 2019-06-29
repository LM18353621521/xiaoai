<?php

namespace app\admin\controller;

use app\common\logic\ModuleLogic;

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
                'is_hidden' => 0,
                'id'=>['gt',1]
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
            $data['rules'] = implode(',', $data['rules']);
            $data['uid'] = UID;
            $res = dataUpdate(\tname::auth_group, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::auth_group)->find($id);

            $where = [
                'status' => 1
            ];
            $loginer = db(\tname::system_loginer)->find(LID);
            if ($loginer['type'] != 0) {
                $group = db(\tname::auth_group)->find($loginer['type']);
                $where['id'] = array('in', $group['rules']);
            }
            $right_list = db(\tname::system_menu)->order('id')->select();
            foreach ($right_list as $k => $v) {
                $auth[$v['group']][] = $v;
            }
            //admin权限组
            $group = (new ModuleLogic)->getPrivilege(0);
            $this->assign('group',$group);
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
            );
            if ($search['stime'] && $search['etime']) {
                $where['a.create_time'] = array('between time', [$search['stime'], $search['etime']]);
            }

            if ($search['keyword']) {
                $where['username'] = array('like', '%' . $search['keyword'] . '%');
            }
            $dataList = db(\tname::system_loginer)->where($where)->order('id asc')->paginate(50, false, array('page' => $search['page']));
            $this->assign('dataList', $dataList);

            $groupList = db(\tname::auth_group)->where(array('is_hidden' => 0, 'status' => 1))->column('id,title');
            $this->assign('groupList', $groupList);

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

            $groupList = db(\tname::auth_group)->where(array('is_hidden' => 0, 'status' => 1))->select();

            $this->assign('data', $data);
            $this->assign('groupList', $groupList);
            return $this->fetch();
        }
    }


    function right_list()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $type = input('type', 0);
            $where['type'] = $type;
            $name = input('name');
            if (!empty($name)) {
                $where['name|right'] = array('like', "%$name%");
            }
            $dataList = db(\tname::system_menu)->where($where)->order('id desc')->paginate(20, false, array('page' => $search['page']));
            $this->assign('dataList', $dataList);

            $moduleLogic = new ModuleLogic;
            if (!$moduleLogic->isModuleExist($type)) {
                $this->error('权限类型不存在');
            }
            $modules = $moduleLogic->getModules();
            $group = $moduleLogic->getPrivilege($type);
            $this->assign('group', $group);
            $this->assign('modules', $modules);
            $html = $this->fetch('account/form');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }
        return $this->fetch();
    }

    public function edit_right()
    {
        $type = input('type', 0);  //0:平台权限资源;1:商家权限资源
        $moduleLogic = new ModuleLogic;
        if (!$moduleLogic->isModuleExist($type)) {
            $this->error('模块不存在或不可见');
        }
        if (request()->isPost()) {
            $data = input('post.');
            if (!isset($data['right'])) {
                return ajaxFalse('请添加权限码');
            }
            $data['right'] = implode(',', $data['right']);
            if (!empty($data['id'])) {
                db(\tname::system_menu)->where(array('id' => $data['id']))->update($data);
            } else {
                if (db(\tname::system_menu)->where(array('type' => $data['type'], 'name' => $data['name']))->count() > 0) {
                    $this->error('该权限名称已添加，请检查', url('Account/right_list'));
                }
                unset($data['id']);
                dataUpdate(\tname::system_menu,$data);
            }
            return ajaxSuccess();
            exit;
        }
        $id = input('id');

        $info = db(\tname::system_menu)->where(array('id' => $id))->find();
        if ($id) {
            $info['right'] = explode(',', $info['right']);
        }
        $this->assign('data', $info);

        $modules = $moduleLogic->getModules();
        $group = $moduleLogic->getPrivilege($type);
        $planPath = APP_PATH . $modules[$type]['name'] . '/controller';
        $planList = array();
        $dirRes = opendir($planPath);
        while ($dir = readdir($dirRes)) {
            if (!in_array($dir, array('.', '..', '.svn'))) {
                $planList[] = basename($dir, '.php');
            }
        }
        $this->assign('modules', $modules);
        $this->assign('planList', $planList);
        $this->assign('group', $group);
        return $this->fetch();
    }

    function ajax_get_action()
    {
        $control = input('controller');
        $type = input('type',0);
        $module = (new ModuleLogic)->getModule($type);
        if (!$module) {
            exit('模块不存在或不可见');
        }

        $selectControl = [];
        $className = "app\\".$module['name']."\\controller\\".$control;
        $methods = (new \ReflectionClass($className))->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if ($method->class == $className) {
                if ($method->name != '__construct' && $method->name != '_initialize') {
                    $selectControl[] = $method->name;
                }
            }
        }
        $html = '';
        foreach ($selectControl as $val){
            $html .= "<li><label><input class='checkbox' name='act_list' value=".$val." type='checkbox'>".$val."</label></li>";
            if($val && strlen($val)> 18){
                $html .= "<li></li>";
            }
        }
        exit($html);
    }
    public function right_del(){
        $data = input('post.');
        $id= $data['id'];
        if(!empty($id)){
            $res = db(\tname::system_menu)->where(['id'=>$id])->delete();
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess('', '');
        }else{
            return ajaxFalse('参数有误');
        }
    }


}