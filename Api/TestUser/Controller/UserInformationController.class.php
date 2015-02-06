<?php
/**
 * Created by PhpStorm.
 * 我的页面中用户相关控制类
 *
 * User: xiaojun@tedochina.com
 * Date: 2015/1/22
 * Time: 13:33
 */

namespace TestUser\Controller;
use Common\Controller\InformationBaseController;

class UserInformationController extends InformationBaseController
{

    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        $this->R($this->result);
    }

    function getUserInfo()
    {
        $start = intval(I('get.start'));
        $num = intval(I('get.num'));
        $user = M('users');
        $count = $user->where()->count();
        $page = $this->getPageInfo(intval($count),$start,$num);
        $list = $user->where(array('user_type'=>2))->order(array('create_time'=>'asc'))->limit($page->firstRow,$page->listRows)->select();
        $this->getStatusInfo(1);
        $this->result['userList'] = empty($list) ? array() : $list;
        $this->R($this->result);
    }





}