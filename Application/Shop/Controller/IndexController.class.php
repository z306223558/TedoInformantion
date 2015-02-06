<?php
namespace Shop\Controller;
use Think\Controller;
class IndexController extends Controller
{

    /**
     * @return bool
     */
    public function index(){
        if(empty($_SESSION['user'])){
            $this->redirect("Index/login");
        }else
        {
            $this->display("Index/main");
        }
    }

    /**
     * 是否登陆
     * @return bool
     */
    public function  islogin()
    {
        if(empty($_SESSION['user'])){
            $this->redirect("index/login");
            return false;
        }
    }

    /**
     * 首页信息
     */
    public function indexInfo()
    {
        $this->islogin();
        $username = $_SESSION['user']['user_login'];
        date_default_timezone_set('Asia/Shanghai');
        $hour=date("H");
        if($hour<11)  $ho = "早上好!";
        else if($hour<13)  $ho = "中午好！";
        else if($hour<17) $ho = "下午好！";
        else $ho = "晚上好！";
        $this->assign('username',$username);
        $this->assign('ho',$ho);
        $this->display('index');
    }

    /**
     * 用来做登出操作，销毁session
     */
    public function logout(){
        $this->islogin();
        unset($_SESSION["user"]);
        $this->redirect("login");
    }

    /**
     * 左侧跳转页面
     */
    public function order()
    {
        $this->islogin();
        $handle = I('handle');
        switch($handle){
            case 'selectOrderInfo':
                $this->display("Order/selectOrderInfo");
                break;
            case 'addPostNum':
                $this->display("Order/addPostNum");
                break;
            case 'return':
                $this->display("Order/return");
                break;
            default :
                $this->display("index");
                break;
        }
    }
}