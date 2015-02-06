<?php

namespace Home\Model;
use Think\Model;

class UserModel extends Model
{

    /**
     * 构造函数，继承父类的构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 更新用户消息
     * @param $data
     * @param $uid
     *
     * @return bool
     */
    public function setUserInfo($data,$uid)
    {
        if(empty($data) || empty($uid))
        {
            return false;
        }

        return M('users')->data($data)->where('uid = '.$uid)->save();
    }

    /**
     * 获取用户信息
     * @param $uid
     *
     * @return bool
     */
    public function getUserInfoById($uid)
    {
        if(empty($uid))
        {
            return false;
        }

        return M('users')->where(array( 'uid = '.$uid,
                                       'isDel = 0'
                      ))->find();
    }

    /**
     * 检查密码是否正确
     * @param $uid
     * @param $pass
     *
     * @return bool
     */
    public function checkPassword($uid,$pass)
    {
        if(empty($uid) || empty($pass))
        {
            return false;
        }

        $user = M('users')->find($uid);
        if($user['user_pass'] === MD5($pass))
        {
            return true;
        }
        return false;
    }

    /**
     * 设置新的密码
     * @param $uid
     * @param $pass
     *
     * @return bool
     */
    public function setUserPassword($uid,$pass)
    {
        if(empty($uid) || empty($pass))
        {
            return false;
        }

        $data['user_pass'] = MD5($pass);
        return M('users')->where('uid = '.$uid)->data($data)->save();
    }

    /**
     * 根据手机得到uid
     * @param $mobile
     *
     * @return bool
     */
    public function getUidByMobile($mobile)
    {

        if(empty($mobile))
        {
            return false;
        }
        $cond['user_mobile'] = $mobile;
        $cond['isDel'] = 0;

        return M('users')->where($cond)->field('uid')->select();
    }

    public function setUserAvatar($avatar,$uid)
    {
        if(empty($uid) || empty($avatar))
        {
            return false;
        }

        return M('users')->where('uid = '.intval($uid))->setField('user_avatar',$avatar);
    }
}