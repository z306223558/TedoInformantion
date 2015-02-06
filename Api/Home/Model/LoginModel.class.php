<?php
/**
 * 登陆模块的登陆模型类
 */
namespace Home\Model;
use Think\Model;

class LoginModel extends Model
{
    /**
     * 构造函数，继承父类构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 根据填写的账户信息，来确定该账户是否存在，若存在则返回这条数据
     * @param $account
     *
     * @return bool
     */
    public function checkAccount($account)
    {
        if(empty($account))
        {
            return false;
        }

        $cond['user_login'] = $account;
        $cond['user_mobile'] = $account;
        $cond['_logic'] = 'OR';
        return M('users')->where($cond)->field('uid,user_pass,user_avatar,user_login')->select();
    }


    /**
     * 检查用户名或手机是否存在 0表示检查手机 1 表示检查用户名
     * @param $account
     * @param $type
     *
     * @return bool
     */
    public function checkLogin($account,$type)
    {
        if(empty($account))
        {
            return false;
        }

        if(empty($type))
        {
            $cond['user_mobile'] = $account;
        }
        else
        {
            $cond['user_login'] = $account;
        }
        $cond['isDel'] = 0;

        return M('users')->where($cond)->field('uid,user_pass,user_avatar')->select();
    }

    /**
     * 根据账户和密码，查看密码是否正确
     * @param $account
     * @param $password
     *
     * @return bool
     */
    public function checkPassword($account,$password)
    {
        if(empty($account) || empty($password))
        {
            return false;
        }

        $cond['password'] = substr(MD5(MD5($password)),0,16);
        $cond['username'] = $account;
        $cond['mobile'] = $account;
        $cond['isDel'] = 0;
        $cond['_logic'] = 'OR';
        return M('users')->where($cond)->select();
    }

    /**
     * 注册信息处理方法
     * @param $data
     *
     * @return bool
     */
    public function signInfo($data)
    {
        if(empty($data))
        {
            return false;
        }

        if(empty($data['user_mobile']) || empty($data['user_login']) || empty($data['user_pass']))
        {
            return false;
        }

        $data['role_id'] = 5;
        $data['create_time'] = date('Y-m-d H:i:s',time());
        $data['user_avatar'] = empty($data['user_avatar']) ? C('__PUBLIC__').'UserAvatar/user_avatar_default.png': $data['user_avatar'];
        $data['user_pass'] = MD5($data['user_pass']);
        return M('users')->data($data)->add();
    }

    /**
     * 登陆时间更新方法
     * @param $uid
     *
     * @return bool
     */
    public function loginIn($uid)
    {
        if(empty($uid))
        {
            return false;
        }
        $data['last_login_ip'] = get_client_ip();
        $data['last_login_time'] = date('Y-m-d H:i:s',time());
        return M('users')->where('uid ='.$uid)->data($data)->save();
    }

    public function setMobileVcode($code,$mobile)
    {
        if(empty($code) || empty($mobile))
        {
            return false;
        }

        $data['code'] = $code;
        $data['mobile'] = $mobile;
        $data['cTime'] = time()+600;

        return M('mobile_reg')->data($data)->add();
    }

    public function checkMobileCode($data)
    {
        if(empty($data['mobile']) || empty($data['code']) || empty($data['codeId']))
        {
            return false;
        }

        return  M('mobile_reg')->find($data['codeId']);
    }

    public function getMaxUid()
    {
        return M('users')->max('uid');
    }

    public function getCodeLastTime($mobile)
    {
        if(empty($mobile))
        {
            return false;
        }

        return M('mobile_reg')->where(array('mobile'=>$mobile))->order('cTime desc')->select();
    }

}