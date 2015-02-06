<?php
/**
 * Created by PhpStorm.
 * 主办方相关控制类
 *
 * User: xiaojun@tedochina.com
 * Date: 2015/1/22
 * Time: 13:33
 */

namespace Information\Controller;
use Common\Controller\InformationBaseController;
use Information\Model\SponsorModel;

class SponsorController extends InformationBaseController {

    function __construct()
    {
        parent::__construct();
        $this->model = new SponsorModel();
    }

    function index()
    {
        $this->R($this->result);
    }

    /**
     * GET 方式  获取资讯主界面接口
     * 传递参数 （可选）start（int）
     *          （可选）num（int）
     */
    function mainInfoList()
    {
        //判断是否
        if(!IS_GET) $this->R($this->result);
        //分页加载活动
        $city = intval(I('city'));
        if(empty($city))
        {
            $city = 1;
        }
        $start = intval(I('get.start'));
        $num = intval(I('get.num'));
        $this->data['ac_count'] = $this->model->getActivtyTotal();
        $page = $this->getPageInfo($this->data['ac_count'],$start,$num);
        //获取最上方滚动商标图
        $sponsorList = $this->model->getSponsorList(0,8);
        //获取活动列表
        $list = $this->model->getActivtyInfoByPage($page->firstRow,$page->listRows,$city);
        $this->getStatusInfo(1);
        $this->result['sponsorList'] = empty($sponsorList) ? array() :$sponsorList ;
        $this->result['activityList'] = empty($list) ? array():$list;
        $this->R($this->result);
    }

    /**
     * GET 方式 获取主办方详细信息接口
     * 传递参数：主办方ID sid（int）
     */
    function getSponsorInfo()
    {
        if(!IS_GET) $this->R($this->result);
        $sid = intval(I('get.sid'));
        if(empty($sid))
        {
            $this->getStatusInfo();
        }
        //获取主办方具体详细信息
        $sponsorInfo = $this->model->getSponsorInfoBySid($sid);
        //获取主办方照片墙的第一张图
        $firstUrl = $this->model->getSponsorTopImage($sid);
        $imageShow['url'] = $firstUrl['url'];
        $imageShow['count'] = $this->model->getSponsorImageCount($sid);
        //获取主办方举办过的互动集合
        $sponsorActList = $this->model->getSidAidList($sid);
        //获取主办方的合作店铺
        $sponsorShopList = $this->model->getSponsorShopList($sid);

        $this->getStatusInfo(1);
        $this->result['sponsorInfo'] = empty($sponsorInfo) ? array() : $sponsorInfo;
        $this->result['imageShow'] = empty($imageShow) ? array() : $imageShow;
        $this->result['actList'] = empty($sponsorActList) ? array() : $sponsorActList;
        $this->result['store'] = empty($sponsorShopList) ? array() : $sponsorShopList;
        $this->R($this->result);
    }

    /**
     * GET方式
     * 全部主办方接口
     */
    function getMoreSponsorList()
    {
        if(!IS_GET) $this->R($this->result);
        $more = $this->model->getMoreSponsor();
        $this->getStatusInfo(1);
        $this->result['sponsorList'] = empty($more) ? array() : $more;
        $this->R($this->result);
    }

    /**
     * GET方式
     * 主办方照片墙接口
     */
    function sponsorImageList()
    {
        if(!IS_GET) $this->R($this->result);
        $sid = intval(I('get.sid'));
        if(empty($sid))
        {
            $this->getStatusInfo();
        }
        $imageList = $this->model->getSponsorImageList($sid);
        $this->getStatusInfo(1);
        $this->result['sponsorImages'] = empty($imageList) ? array() : $imageList;
        $this->R($this->result);
    }
}
