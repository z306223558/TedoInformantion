<?php
/**
 * Created by PhpStorm.
 * 活动相关控制类
 *
 * User: xiaojun@tedochina.com
 * Date: 2015/1/22
 * Time: 13:33
 */

namespace Information\Controller;
use Common\Controller\InformationBaseController;
use Information\Model\ActivityModel;

class ActivityController extends InformationBaseController
{

    function __construct()
    {
        parent::__construct();
        $this->model = new ActivityModel();
    }

    function index()
    {
        $this->R($this->result);
    }

    /**
     * GET方式 参数：aid int
     * 获取活动详细信息
     */
    function activityInfo()
    {
        if(!IS_GET) $this->R($this->result);
        $aid = intval(I('get.aid'));
        if(empty($aid))
        {
            $this->getStatusInfo();
        }
        //获取活动详细信息
        $info = $this->model->getActivityInfo($aid);
        //获取参与活动的商家信息
        $storeList = $this->model->getActStoreByAid($aid);
        //获取活动照片墙的第一张图a
        $firstUrl = $this->model->getActivityTopImage($aid);
        $imageShow['url'] = $firstUrl['url'];
        $imageShow['count'] = $this->model->getActivityImageCount($aid);
        //获取活动的主办方
        $sponsorLogo = $this->model->getActivitySponsorLogoInfo($info['sid']);

        $this->getStatusInfo(1);
        $this->result['actInfo'] = empty($info) ? array() : $info;
        $this->result['sponsorInfo'] = empty($sponsorLogo) ? array() : $sponsorLogo;
        $this->result['imageShow'] = empty($imageShow) ? array() : $imageShow;
        $this->result['store'] = empty($storeList) ? array() : $storeList;
        $this->R($this->result);
    }

    /**
     * GET方式
     * 活动照片墙接口
     */
    function activityImageList()
    {
        if(!IS_GET) $this->R($this->result);
        $aid = intval(I('get.aid'));
        if(empty($aid))
        {
            $this->getStatusInfo();
        }
        $imageList = $this->model->getActivityImageList($aid);
        $this->getStatusInfo(1);
        $this->result['actImages'] = empty($imageList) ? array() : $imageList;
        $this->R($this->result);
    }

    /**
     * 活动点赞接口
     * 参数 ： aid （int） 必须
     */
    function activityLikeNumChange()
    {
        if(!IS_GET) $this->R($this->result);
        $aid = intval((I('get.aid')));
        if(empty($aid)) $this->getStatusInfo();
        $updateLikeNum = $this->model->updateLikeNum($aid);
        if(empty($updateLikeNum)) $this->getStatusInfo(-3);
        $this->getStatusInfo(1);
        $this->R($this->result);
    }

    /**
     * 活动收藏与取消收藏接口
     */
    function collectActivity()
    {
        if(!IS_GET) $this->R($this->result);
        $aid = intval(I('get.aid'));
        $uid = intval(I('get.uid'));
        if(empty($aid)||empty($uid)) $this->getStatusInfo();
        $collectInfo = $this->model->getUserCollectByAidAndUid($aid,$uid);
        if(empty($collectInfo))
        {
            if($this->model->addCollectInfo($aid,$uid))
            {
                $this->result['status'] = 1;
                $this->result['message'] = '收藏成功';
                $this->result['emessage'] = 'Collection success';
                $this->R($this->result);
            }else
            {
                $this->getStatusInfo(-2);
            }
        }else{
            if($this->model->delActCollectInfo($collectInfo['id']))
            {
                $this->result['status'] = 1;
                $this->result['message'] = '取消关注成功';
                $this->result['emessage'] = 'Cancel Collection success';
                $this->R($this->result);
            }else{
                $this->getStatusInfo(-3);
            }
        }
    }
}