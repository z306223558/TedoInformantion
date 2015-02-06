<?php
//公共公有控制器

namespace Shop\Controller;
use Think\Controller;

class PublicController extends Controller
{
    //输出验证图片
    public function verify()
    {
        $Verify = new \Think\Verify();
        $Verify->fontSize = 50;
        $Verify->length = 2;
        $Verify->useCurve = false;
        $Verify->entry();
    }

    // 检验验证码
    public function check_verify()
    {
        $verify = new \Think\Verify();
        if(!$verify->check($_POST['code'],"")){
            $this->ajaxReturn("0","json");
            exit();
        }else{
            $this->ajaxReturn("1","json");
        }

    }

    //显示登录页
    public function login()
    {
        $this->display("login");
    }

    // 登录验证
    public function dologin()
    {
        $data['user_login'] = $_POST["loginname"];
        $data['user_pass'] = md5($_POST["loginpwd"]);

        $cond['user_login'] = $data['user_login'];
        $user = M("users")->where($cond)->find();
        if($user)
        {
            if($user['user_pass']==$data['user_pass'])
            {
                $_SESSION["user"]=$user;
                if($user['role_id']>2)
                {
                    $this->assign("errorinfo","权限不足，请联系管理员");
                    $this->ajaxReturn("密码错误","json");
                }
                if($_POST['reme']=="1")
                {
                    cookie('user',$user,3600*24*30);
                }
                $this->ajaxReturn("通过","json");
            }
            else
            {
                $this->assign("errorinfo","密码错误");
                $this->ajaxReturn("密码错误","json");
            }
        }
        else
        {
            $this->assign("errorinfo","用户名不存在");
            $this->ajaxReturn("用户名不存在","json");
        }
    }

    //执行退出
    public function logout()
    {
        unset($_SESSION["user"]);
        unset($_SESSION["cart"]);
        $this->redirect("login");
    }

    //用户信息注册页面
    public function reg()
    {
        $this->display("reg");
    }
} 