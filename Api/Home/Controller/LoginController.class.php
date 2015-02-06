<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 14-11-5
 * Time: 上午11:26
 */

namespace Home\Controller;
use Home\Model\LoginModel;
use Think\Controller;

class LoginController extends Controller
{
    /**
     * @var array 私有变量，用来返回信息
     */
    private $result = array();

    private $model = NULL;

    /**
     * 构造函数，继承父类的构造函数，初始化变量
     */
    public function __construct()
    {
        parent::__construct();
        $this->result = array();
        $this->model = new LoginModel();
        $this->result['status'] = 0;
        $this->result['message'] = '非法操作';
        $this->result['emessage'] = 'Illegal operation';
    }

    /**
     * index方法，用于控制非法访问
     */
    public function index()
    {
        $this->R($this->result);
    }

    /**
     * 登陆方法，验证登陆信息的额正确性。根据登陆信息返回相应的登陆状态
     */
    public function login()
    {
        if(!IS_POST) $this->R($this->result);
        $account = I('username');
        $password = I('password');
        if(empty($account))
        {
            $this->result['message'] = '用户名或手机为空';
            $this->result['emessage'] = 'The user name or mobile phone is empty';
            $this->R($this->result);
        }
        if(empty($password))
        {
            $this->result['message'] = '密码为空';
            $this->result['emessage'] = 'The password is empty';
            $this->R($this->result);
        }
        $userInfo = $this->model->checkAccount($account);
        if(empty($userInfo))
        {
            $this->result['message'] = '该用户不存在';
            $this->result['emessage'] = 'The user does not exist';
            $this->R($this->result);
        }
        if($userInfo[0]['user_pass'] === MD5($password))
        {
            if($this->model->loginIn($userInfo[0]['uid']))
            {
                $this->result['status'] = 1;
                $this->result['message'] = '登陆成功';
                $this->result['emessage'] = 'Login success!';
                $this->result['uid'] = $userInfo[0]['uid'];
                $this->result['avatar'] = $userInfo[0]['user_avatar'];
                $this->result['username'] = $userInfo[0]['user_login'];
                $this->R($this->result);
            }
            else
            {
                $this->result['message'] = '登陆失败';
                $this->result['emessage'] = 'Login failed';
                $this->R($this->result);
            }
        }
        else
        {
            $this->result['message'] = '密码错误';
            $this->result['emessage'] = 'Wrong password';
            $this->R($this->result);
        }
    }

    /**
     * 注册操作
     */
    public function sign()
    {
        //对post信息进行过滤
        if(!IS_POST) $this->R($this->result);
        $data['user_mobile'] = trim(I('mobile'));
        if(empty($data['user_mobile']))
        {
            $this->result['message'] = '电话不能为空';
            $this->result['emessage'] = 'The phone can not be empty';
            $this->R($this->result);
        }
        $data['user_login'] = I('username');
        if(empty($data['user_login']))
        {
            $this->result['message'] = '用户名不能为空';
            $this->result['emessage'] = 'The user name cannot be empty';
            $this->R($this->result);
        }
        $username = isNames($data['user_login'],2,16);
        if($username === 0)
        {
            $this->result['message'] = '用户名中不能包含特殊符号，长度在2~16个字符之间';
            $this->result['emessage'] = 'The user name cannot be empty';
            $this->R($this->result);
        }
        $data['user_pass'] = I('password');
        if(empty($data['user_pass']))
        {
            $this->result['message'] = '密码不能为空';
            $this->result['emessage'] = 'Password can not be empty';
            $this->R($this->result);
        }
        $password = isPWD($data['user_pass'],6,20);
        if($password === 0)
        {
            $this->result['message'] = '密码不能包含非法字符，且长度在6~20个字符之间';
            $this->result['emessage'] = 'The password is not correct, please ensure that the password in 6~20 characters';
            $this->R($this->result);
        }
        //检查电话，用户名是否已经注册
        $isExist_mobile = $this->model->checkLogin($data['user_mobile'],0) ;
        if(!empty($isExist_mobile))
        {
            $this->result['message'] = '该电话已经存在';
            $this->result['emessage'] = 'The phone has been in existence';
            $this->R($this->result);
        }
        $isExist_name  = $this->model->checkLogin($data['user_login'],1);
        if(!empty($isExist_name))
        {
            $this->result['message'] = '该用户名已经存在';
            $this->result['emessage'] = 'This user name already exists';
            $this->R($this->result);
        }
        unset($isExist_mobile);
        unset($isExist_name);
        //封装其他信息
        $data['user_email'] = I('email');
        $data['user_content'] = I('content');
        $data['user_tel'] = I('tel');
        //头像上传
        if(!empty($_FILES))
        {
            //找到最大的uid，用来图片命名
            $maxUid = $this->model->getMaxUid();
            $image = new ImageController();
            $rs = $image->uploadAvatar($maxUid+1);
            $data['user_avatar'] = $rs;
        }
        $uid = $this->model->signInfo($data);
        $this->result['status'] = $uid ? 1 : 0;
        $this->result['message'] = $this->result['status'] ? '注册成功' : '注册失败';
        $this->result['emessage'] = $this->result['status'] ? 'Sign up success!' : 'Sign up failed!';
        $this->result['uid'] = $uid;
        $this->result['avatar'] = empty($data['avatar']) ? C('__PUBLIC__').'UserAvatar/user_avatar_default.png' : $data['avatar'];
        $this->result['username'] = $data['user_login'];
        unset($data);
        $this->R($this->result);
    }

    /**
     * 手机号发送验证短息注册
     */
    public function smsSign()
    {
        if(!IS_POST) $this->R($this->result);
        $data['mobile'] = trim(I('mobile'));
        if(empty($data['mobile']))
        {
            $this->result['message'] = '必要信息不能为空';
            $this->result['emessage'] = 'The necessary information can not be empty';
            $this->R($this->result);
        }

        //手机发送短信验证码
        if (is_numeric($data['mobile']) || strlen($data['mobile']) == 11)
        {
            //短信平台设置
            srand((double)microtime()*1000000);
            while(($vcode=rand()%100000)<10000);
            $content = "感谢您在tedo特逗注册，验证码为".$vcode."。客服热线：400-108-6488（工作日9-18点）";
            $isExist_mobile = $this->model->checkLogin($data['mobile'],0) ;
            if(!empty($isExist_mobile))
            {
                $this->result['message'] = '该电话已经存在,请使用该电话登陆!';
                $this->result['emessage'] = 'The telephone already exists, please use the telephone landing!';
                $this->R($this->result);
            }
            $codeInfo = $this->model->getCodeLastTime($data['mobile']);
            if(!empty($codeInfo))
            {
                if($codeInfo[0]['cTime']-540 > time())
                {
                    $this->result['message'] = '请一分钟过后再试';
                    $this->result['emessage'] = 'Please Wait one minute';
                    $this->R($this->result);
                }
            }
            $rs = $this->sendMessage($vcode,$data['mobile'],$content);
            if(empty($rs))
            {
                $this->result['message'] = '信息发送失败';
                $this->result['emessage'] = 'Message sending failed';
                $this->R($this->result);
            }

            $rs = $this->model->setMobileVcode($vcode,$data['mobile']);
            if(empty($rs))
            {
                $this->result['message'] = '保存验证码失败';
                $this->result['emessage'] = 'Save validation failed';
                $this->R($this->result);
            }
            $this->result['status'] = 1;
            $this->result['message'] = '发送信息成功，请注意接收';
            $this->result['emessage'] = 'Send information successfully, please note reception';
            $this->result['codeId'] = $rs;
            $this->R($this->result);
        }
        else
        {
            $this->result['message'] = '输入的电话格式不正确';
            $this->result['emessage'] = 'The input of the phone is not in the correct format';
            $this->R($this->result);
        }

    }

    /**
     * 用来调用企信通接口，给注册手机发送验证短信
     * @param $authnum
     * @param $mobile
     * @param $message
     *
     * @return bool
     */
    public function sendMessage($authnum,$mobile, $message)
    {
        /* 通过企信通的HTTP接口*/
        $ip = "121.52.220.246";
        $port = "8888";
        $x_url = "http://sh.ipyy.com:8888/sms.aspx";
        $xx = "action=send&userid=1564&account=jkcs45&password=jkcs45888&mobile=" . $mobile . "&content=" . $message . "";

        $fp = fsockopen($ip, $port, $errno, $errstr, 10);
        $reply = "";
        if ($fp) {
            fputs($fp, "POST " . $x_url . " HTTP/1.1\r\n");
            fputs($fp, "Host: " . $ip . "\r\n");
            fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
            fputs($fp, "Content-length: " . strlen($xx) . "\r\n");
            fputs($fp, "Connection: close\r\n\r\n");
            fputs($fp, $xx . "\r\n\r\n");

            $reply = "";
            while (!feof($fp)) {
                $reply .= fgets($fp, 4096);
            }
        }
        $reply_char=explode("Connection: close",$reply);
        $result=explode("-",trim($reply_char[1]));
        if(trim($result[0])=="DELIVRD"){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 验证短信验证码是否正确
     */
    public function validateMobileReg()
    {
        if(!IS_POST) $this->R($this->result);
        $data['mobile'] = trim(I('mobile'));
        $data['code'] = intval(I('code'));
        $data['codeId']=intval(I('codeId'));
        if(empty($data['mobile']) || empty($data['code']) || empty($data['codeId']))
        {
            $this->result['message'] = '必要信息不能为空';
            $this->result['emessage'] = 'The necessary information can not be empty';
            $this->R($this->result);
        }
        $rs = $this->model->checkMobileCode($data);
        if(empty($rs))
        {
            $this->result['message'] = '不存在该验证消息详情';
            $this->result['emessage'] = 'Does not exist the authentication message details';
            $this->R($this->result);
        }
        if($rs['cTime'] < time())
        {
            $this->result['message'] = '验证码过期';
            $this->result['emessage']='Verification code expired';
            $this->R($this->result);
        }
        if($rs['code'] != $data['code'])
        {
            $this->result['message'] = '验证码不正确';
            $this->result['emessage'] = 'Verification code is not correct';
            $this->R($this->result);
        }
        $this->result['status'] = 1;
        $this->result['message'] = '验证通过';
        $this->result['emessage'] = 'Verification is right';
        $this->R($this->result);
    }

    /**
     * 手机修改密码
     */
    public function mobileChangePass()
    {
        if(!IS_POST) $this->R($this->result);
        $data['mobile'] = trim(I('mobile'));
        if(empty($data['mobile']))
        {
            $this->result['message'] = '必要信息不能为空';
            $this->result['emessage'] = 'The necessary information can not be empty';
            $this->R($this->result);
        }

        //手机发送短信验证码
        if (is_numeric($data['mobile']) || strlen($data['mobile']) == 11)
        {
            //短信平台设置
            srand((double)microtime()*1000000);
            while(($vcode=rand()%100000)<10000);
            $content = "您于".date('y-m-d h:i:s',time())."申请重置密码，验证码为".$vcode."。客服热线400-180-6488（工作日9-18点）";
            $isExist_mobile = $this->model->checkLogin($data['mobile'],0) ;
            if(empty($isExist_mobile))
            {
                $this->result['message'] = '该电话不存在,无法修改密码';
                $this->result['emessage'] = 'The phone does not exist, the password cannot be modified';
                $this->R($this->result);
            }
            $rs = $this->sendMessage($vcode,$data['mobile'],$content);
            if(empty($rs))
            {
                $this->result['message'] = '信息发送失败';
                $this->result['emessage'] = 'Message sending failed';
                $this->R($this->result);
            }

            $re = $this->model->setMobileVcode($vcode,$data['mobile']);
            if(empty($re))
            {
                $this->result['message'] = '保存验证码失败';
                $this->result['emessage'] = 'Save validation failed';
                $this->R($this->result);
            }
            $this->result['status'] = 1;
            $this->result['message'] = '发送信息成功，请注意接收';
            $this->result['emessage'] = 'Send information successfully, please note reception';
            $this->result['codeId'] = $re;
            $this->R($this->result);
        }
        else
        {
            $this->result['message'] = '输入的电话格式不正确';
            $this->result['emessage'] = 'The input of the phone is not in the correct format';
            $this->R($this->result);
        }
    }
} 