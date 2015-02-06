<?php

namespace Home\Controller;
use Home\Model\UserModel;
use Think\Controller;
    class UserController extends Controller
    {
        /**
         * @var array 私有变量，用来返回信息
         */
        private $result = array();

        private $model = NULL;

        private $data = NULL;

        /**
         * 构造函数，继承父类的构造函数，初始化变量
         */
        public function __construct()
        {
            parent::__construct();
            $this->result = array();
            $this->model = new UserModel();
            $this->result['status'] = 0;
            $this->result['message'] = '非法操作';
            $this->result['emessage'] = 'Illegal operation';
        }

        /**
         * 用于防止非法操作
         */
        public function index()
        {
            $this->R($this->result);
        }

        /**
         * 个人资料修改
         */
        public function modUserInfo()
        {
            if(!IS_POST) $this->R($this->result);
            $uid = intval(I('uid'));
            if(empty($uid))
            {
                $this->result['message'] = '必要信息不能为空';
                $this->result['emessage'] = 'The necessary information can not be empty';
                $this->R($this->result);
            }
            $this->data['user_email'] = trim(I('email'));
            $this->data['user_tel'] = trim(I('tel'));
            $this->data['user_content'] = trim(I('content'));
            $rs = $this->model->setUserInfo($this->data,$uid);
            if(empty($rs))
            {
                $this->result['message'] = '更新信息失败';
                $this->result['emessage'] = 'Update information failed';
                $this->R($this->result);
            }
            $this->result['status'] = 1;
            $this->result['message'] = '更新成功';
            $this->result['emessage'] = 'Update information success';
            $this->R($this->result);
        }

        /**
         * 更新用户头像
         */
        public function modUserAvatar()
        {
            if(!IS_POST) $this->R($this->result);
            $this->data['uid'] = intval(I('uid'));
            if(empty($this->data['uid']))
            {
                $this->result['message'] = '必要信息不能为空';
                $this->result['emessage'] = 'The necessary information can not be empty';
                $this->R($this->result);
            }
            //上传头像
            $image = new ImageController();
            $rs = $image->uploadAvatar($this->data['uid']);
            $setRes = $this->model->setUserAvatar($rs,$this->data['uid']);
            if(empty($setRes))
            {
                $this->result['message'] = '更新头像失败';
                $this->result['emessage'] = 'Update avatar failed';
                $this->R($this->result);
            }
            $this->result['status'] = 1;
            $this->result['message'] = '更新成功';
            $this->result['emessage'] = 'Update avatar success';
            $this->R($this->result);
        }

        /**
         * 获取用户的详细信息
         */
        public function userInfo()
        {
            if(!IS_GET) $this->R($this->result);
            $this->data['uid'] = intval(I('uid'));
            if(empty($this->data['uid']))
            {
                $this->result['message'] = '必要信息不能为空';
                $this->result['emessage'] = 'The necessary information can not be empty';
                $this->R($this->result);
            }
            $rs = $this->model->getUserInfoById($this->data['uid']);
            if(empty($rs))
            {
                $this->result['message'] = '获取信息失败';
                $this->result['emessage'] = 'Access to information failure';
                $this->R($this->result);
            }
            $uImage = unserialize($rs['user_image']);
            if(empty($uImage))
            {
                $uImage = array();
            }
            $this->result['status'] = 1;
            $this->result['message'] = 'OK';
            $this->result['emessage'] = 'OK';
            $this->result['userInfo'] = $rs;
            $this->result['userInfo']['user_image'] = $uImage;
            unset($rs);
            $this->R($this->result);
        }

        /**
         * 修改密码
         */
        public function changePass()
        {
            if(!IS_POST) $this->R($this->result);
            $uid= intval(I('uid'));
            $oldPassword = trim(I('old_pass'));
            $newPassword = trim(I('new_pass'));
            $mobile = I('mobile');
            $type = intval(I('type'));    //type为0表示输入原密码修改密码，type为1表示电话修改密码，无需原密码
            if(empty($type))
            {
                //登陆后修改密码流程
                if(empty($uid) || empty($newPassword) || empty($oldPassword))
                {
                    $this->result['message'] = '必要信息不能为空';
                    $this->result['emessage'] = 'The necessary information can not be empty';
                    $this->R($this->result);
                }
                if($this->model->checkPassword($uid,$oldPassword))
                {
                    $rs = $this->model->setUserPassword($uid,$newPassword);
                    if(empty($rs))
                    {
                        $this->result['message'] = '修改密码失败';
                        $this->result['emessage'] = 'Change the password failed';
                        $this->R($this->result);
                    }
                    $this->result['status'] = 1;
                    $this->result['message'] = '修改密码成功';
                    $this->result['emessage'] = 'Change the password success';
                    $this->R($this->result);
                }
                else
                {
                    $this->result['message'] = '原密码不正确';
                    $this->result['emessage'] = 'The old password is incorrect';
                    $this->R($this->result);
                }
            }
            else
            {
                //手机发送验证码修改密码流程
                if(empty($mobile) || empty($newPassword))
                {
                    $this->result['message'] = '必要信息不能为空';
                    $this->result['emessage'] = 'The necessary information can not be empty';
                    $this->R($this->result);
                }
                $sUid = $this->model->getUidByMobile($mobile);
                if(empty($sUid))
                {
                    $this->result['message'] = '该手机信息不存在';
                    $this->result['emessage'] = 'The mobile phone information does not exist';
                    $this->R($this->result);
                }
                $res = $this->model->setUserPassword(intval($sUid[0]['uid']),$newPassword);
                if(empty($res))
                {
                    $this->result['message'] = '修改密码失败';
                    $this->result['emessage'] = 'Change the password failed';
                    $this->R($this->result);
                }
                $this->result['status'] = 1;
                $this->result['message'] = '修改密码成功';
                $this->result['emessage'] = 'Change the password success';
                $this->R($this->result);
            }
        }
}
?>
