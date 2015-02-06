<?php
/**
 * Created by PhpStorm.
 * 我的页面中用户相关控制类
 *
 * User: xiaojun@tedochina.com
 * Date: 2015/1/22
 * Time: 13:33
 */

namespace Information\Controller;
use Common\Controller\InformationBaseController;
use Information\Model\UserInformationModel;

class UserInformationController extends InformationBaseController
{

    function __construct()
    {
        parent::__construct();
        $this->model = new UserInformationModel();
    }

    function index()
    {
        $this->R($this->result);
    }

    //获取用户未读消息数量
    function getUserUnReadMessageCount()
    {
        if(!IS_GET) $this->R($this->result);
        $uid = intval(I('get.uid'));
        if(empty($uid))
        {
            $this->getStatusInfo();
        }
        $count = $this->model->getUnReadMessageCount($uid);
        $this->result['count'] = $count;
        $this->getStatusInfo(1);
        $this->R($this->result);
    }

    //获取用户的收藏信息，type表示 1商品 2店铺 3活动
    function getUserCollectInfo()
    {
        if(!IS_GET) $this->R($this->result);
        $uid = intval(I('get.uid'));
        $type = intval(I('get.type'));
        if(empty($uid)||empty($type))
        {
            $this->getStatusInfo();
        }
        $start = intval(I('get.start'));
        $num = intval(I('get.num'));
        //分页获取主评论列
        $total = intval($this->model->getUserCollectTotal($uid,$type));//根据type来获取不同的评论
        $page = $this->getPageInfo($total,$start,$num);
        //获取分页评论信息
        $collectInfo = $this->model->getUserCollectInfo($uid,$type,$page->firstRow,$page->listRows);
        $this->getStatusInfo(1);
        $this->result['collectInfo'] = empty($collectInfo) ? array() : $collectInfo;
        $this->R($this->result);
    }

    /**
     * 用户阅读未读信息接口
     */
    function userChekUnReadMsg()
    {
        if(!IS_POST) $this->R($this->result);
        $mid = intval(I('post.mid'));
        if(empty($mid))
        {
            $this->getStatusInfo();
        }
        $result = $this->model->delUnReadMsg($mid);
        if(empty($result))
        {
            $this->getStatusInfo(-3);
        }
        $this->getStatusInfo(1);
        $this->R($this->result);
    }

    /**
     * 获取用户未读信息详情接口
     */
    function getUserUnReadMsgInfo()
    {
        if(!IS_GET) $this->R($this->result);
        $uid = intval(I('get.uid'));
        if(empty($uid))
        {
            $this->getStatusInfo();
        }
        $start = intval(I('get.start'));
        $num = intval(I('get.num'));
        //获取未读消息总数
        $total = $this->model->getUnReadMessageCount($uid);
        $page = $this->getPageInfo($total,$start,$num);
        //分页获取详细的未读信息列表
        $list = $this->model->getUnReadMessageListByPage($uid,$page->firstRow,$page->listRows);
        //寻找回复人的名字和头像
        foreach($list as $key=>$val)
        {
            $data[$key]['mid'] = $val['mid'];
            //主评论消息
            $data[$key]['main']['cid'] = $val['cid'];
            $data[$key]['main']['content'] = $val['main_content'];
            $data[$key]['main']['cTime'] = $val['main_cTime'];
            if(empty($val['main_uid']))
            {
                $data[$key]['main']['name'] = C('YOUKE_NAME').$val['main_uuid'];
                $data[$key]['main']['avatar'] = C('YOUKE_AVATAR');
            }else{
                $userInfo = $this->model->getUserInfo($val['main_uid']);
                $data[$key]['main']['name'] =$userInfo['name'];
                $data[$key]['main']['avatar'] = $userInfo['avatar'];
            }

            //最新回复信息
            $data[$key]['replay']['rid'] = $val['rid'];
            $data[$key]['replay']['content'] = $val['content'];
            $data[$key]['replay']['cTime'] = $val['c_time'];
            if(empty($val['uid']))
            {
                $data[$key]['replay']['name'] = C('YOUKE_NAME').$val['uuid'];
                $data[$key]['replay']['avatar'] = C('YOUKE_AVATAR');
            }else{
                $userInfo = $this->model->getUserInfo($val['uid']);
                $data[$key]['replay']['name'] =$userInfo['name'];
                $data[$key]['replay']['avatar'] = $userInfo['avatar'];
            }
        }
        unset($list);
        $this->result['unReadList'] = empty($data) ? array() : $data;
        unset($data);
        $this->getStatusInfo(1);
        $this->R($this->result);
    }





}