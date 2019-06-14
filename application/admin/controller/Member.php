<?php
namespace app\admin\controller;

use think\Controller;

class Member extends Controller
{

    public function _empty()
    {
//        $this->error('当前页面不存在', controller('Index/index'));
    }

    /**
     * 构造函数    2017-10-15
     */
    protected function _initialize()
    {
        $this->login();    //获取登录信息
        $menu = config('menu');
        $loginer = session('loginer_auth');

        if ($loginer['rank'] != 1) {
            $group = db(\tname::auth_group)->find($loginer['type']);
            $controller_action = BIND_MODULE . '.php/' . strtolower(request()->controller()) . '/' . strtolower(request()->action());
            $auths = db(\tname::auth_rule)->where(array('id' => array('in', $group['rules'])))->column('name');
            $group = db(\tname::auth_rule)->field('control_name,group_name')->where(array('id' => array('in', $group['rules'])))->select();
            $controller_group = [];
            $auths_group = [];
            foreach ($group as $key => $value) {
                $auths_group[] = $value['group_name'];
                $controller_group[$value['group_name']][] = $value['control_name'];
            }
            foreach ($menu as $key => &$value) {
                if (!in_array($value['group'], $auths_group)) {
                    unset($menu[$key]);
                } else {
                    foreach ($value['_child'] as $k => &$v) {
                        if (!in_array($v['_child'][0], $controller_group[$value['group']])) {
                            unset($menu[$key]['_child'][$k]);
                        }
                    }
                }
            }
            if (strtolower(request()->controller()) == 'index') {
                if (strtolower(request()->action()) == 'authcreate') {
                } else {
                    config('menu', $menu);
                }
            } else {
                config('menu', $menu);
            }

            if (!(in_array($controller_action, $auths) || in_array(strtolower(request()->controller()), array('index', 'user', 'member')))) {
                $this->error('未授权访问!');
            }
        }

        isset($auths_group) ? $auths_group : $auths_group = 'all';
        $this->assign('auths_group', $auths_group);
    }

    /**
     * 获取登录信息    2017-10-15
     */
    protected function login()
    {
        $loginer = session('loginer_auth');
        $user = session('user_auth');
        define('LID', $loginer['id']);
        define('UID', $user['id']);
        if (!UID) {
            return $this->redirect('User/index');
        }
    }

    /**
     * 针对某表某字段进行更改    2017-10-15
     */
    public function updatefield()
    {
        $data = input('post.');

        $updatedata = array(
            'id' => $data['id'],
            $data['fieldname'] => $data['afterchange']
        );
        $res = dataUpdate($data['tablename'], $updatedata);

        if (!$res) {
            return ajaxFalse();
        }
        return ajaxSuccess('', '');
    }

    /**
     * 批量修改
     */
    public function updateAll()
    {
        $data = input('post.');

        if($data['operate_value'] == 'sort'){
            $id = object_to_array(json_decode($data['id']));
            db()->startTrans();
            foreach($id as $key=>$value){
                $res = db($data['tablename'])->update($value);
                if($res === false){
                    db()->rollback();
                    return ajaxFalse();
                }
            }
            db()->commit();
            return ajaxSuccess('','操作成功');
        }else{
            if (empty($data['id'])) {
                return ajaxFalse('无数据');
            }
            $where = [
                $data['where'] => ['in', $data['id']]
            ];
            $res = db($data['tablename'])->where($where)->update([$data['fieldname'] => $data['afterchange']]);
            if ($res === false) {
                return ajaxFalse();
            }
            return ajaxSuccess('', '操作成功');
        }
    }

    //加载下一级
    public function loadNextLevel(){
        $data = input('post.');

        $where_next_level = [
            'uid' => UID,
            'is_hidden' => 0,
            'pid' => $data['this_level_id']
        ];
        $dataList = db($data['tablename'])->where($where_next_level)->select();

        $this->assign('dataList', $dataList);
        $this->assign('next_level_id', $data['next_level_id']);
        $html = $this->fetch('public/form');
        return ajaxSuccess($html);
    }

    //百度上传图片插件返回路径    2017-10-15
    public function webfileupload()
    {
        $files = request()->file('');

        foreach ($files as $file) {
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'picture' . DS . 'uid' . UID . DS . 'member');

            if ($info) {
                $savename = str_replace('\\', '/', $info->getSaveName());
                $arr = array(
                    'imgpath' => '/uploads/picture/uid' . UID . '/member/' . $savename,
                    'status' => 1,
                );
                return json($arr);
            } else {
                // 上传失败获取错误信息
                return $file->getError();
            }
        }
    }

}
