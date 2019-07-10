<?php
namespace app\applet\controller;
class Api extends Applet
{
    public function sendSmsCode(){
        $pdata =input('');
        $mobile = $pdata['mobile'];
        $type = $pdata['type'];
        if (!isMobilephone($mobile)) {
            return json(ajaxFalse('请输入正确的手机号'));
        }

        if($type==1){
            //检查是否已经被绑定
            $check_bind = db(\tname::vip)->where(array('mobile'=>$mobile,'source'=>2))->find();
            if($check_bind&&$check_bind['id']!=$pdata['vip_id']){
                return json(ajaxFalse('该手机号码已被绑定，请重新输入'));
            }
        }
        if($type==6){
            $has_mobile = db(\tname::vip)->where(array('mobile'=>$mobile))->find();
            if($has_mobile){
                return json(ajaxFalse('该手机号码已被使用，请重新输入'));
            }
        }
        //判断是否存在验证码
        $data = db(\tname::data_message)->where(array('mobile' => $mobile,'error_code' => 0,'type'=>$type))->order('id DESC')->find();
        $sms_time_out = 60;
        //60秒以内不可重复发送
        if ($data && (time() - $data['create_time']) < $sms_time_out) {
            return json(ajaxFalse($sms_time_out . '秒内不允许重复发送'));
        }
        $code=createverifycode(6);
        $content="";
        $result=sendSmsjh($mobile,$content,$code,$type);//发送短信
        if($result['error_code']){
            return json(ajaxFalse('验证码发送失败，请稍后重试'));
        }
        $ajaxdata = [
            'code_mobile' => $mobile,
            'code_code'=>$code
        ];
        return json(ajaxSuccess($ajaxdata,"验证码已发送至：".$mobile."，请注意查收"));
    }
    /**
     * 收集FormID
     */
    public function getFormId(){
        $_post = input('post.');
        getFromId($_post['vip_id']);
    }

}