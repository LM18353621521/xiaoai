<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;

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
        $menuList = session('menuList');
        $this->check_priv();
        isset($auths_group) ? $auths_group : $auths_group = 'all';
        $webInfo =tpCache('web');
        $this->assign('webInfo', $webInfo);
        $this->assign('menuList', $menuList);
        $this->assign('auths_group', $auths_group);
    }


    public function check_priv()
    {
        $ctl = $this->request->controller();
        $act = $this->request->action();
        $act_list = session('act_list');
        //无需验证的操作
        $uneed_check = array('login','logout','vertifyHandle','vertify','imageUp','upload','videoUp','delupload','login_task','cleancache');
        if($ctl == 'Index'||$ctl == 'User' || $act_list == 'all'){
            //后台首页控制器无需验证,超级管理员无需验证
            return true;
        }elseif((request()->isAjax() && $this->verifyAjaxRequest($act)) || strpos($act,'ajax')!== false || in_array($act,$uneed_check)){
            //部分ajax请求不需要验证权限
            $res = $this->verifyAction();
            if($res['status'] == -1){
                $this->error($res['msg'],$res['url']);
            };
            return true;
        }else{
            $res = $this->verifyAction();
            if($res['status'] == -1){
                $this->error($res['msg'],$res['url']);
            };
        }
    }
    /**
     * 要验证的ajax
     * @param $act
     * @return bool
     */
    private function verifyAjaxRequest($act){
        $verifyAjaxArr = ['delGoodsCategory','delGoodsAttribute','delBrand','delGoods'];
        if(request()->isAjax() && in_array($act,$verifyAjaxArr)){
            $res = $this->verifyAction();
            if($res['status'] == -1){
                $this->ajaxReturn($res);
            }else{
                return true;
            };
        }else{
            return true;
        }
    }
    private function verifyAction(){
        $ctl = $this->request->controller();
        $act = $this->request->action();
        $act_list = session('act_list');
        $right = db(\tname::system_menu)->where("id", "in", $act_list)->cache(true)->column('right');
        $role_right = '';
        foreach ($right as $val){
            $role_right .= $val.',';
        }
        $role_right = explode(',', $role_right);
        //检查是否拥有此操作权限
        if(!in_array($ctl.'@'.$act, $role_right)){
            return ['status'=>-1,'msg'=>'您没有操作权限['.($ctl.'@'.$act).'],请联系超级管理员分配权限','url'=>url('Index/index')];
        }
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
        if (!UID&&($this->request->controller()!="User"&&$this->request->action()!='index')) {
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
