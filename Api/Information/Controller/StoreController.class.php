<?php
/**
 * Created by PhpStorm.
 * 店铺相关控制类
 *
 * User: xiaojun@tedochina.com
 * Date: 2015/1/22
 * Time: 13:33
 */

namespace Information\Controller;
use Common\Controller\InformationBaseController;
use Information\Model\StoreModel;

class StoreController extends InformationBaseController
{

    function __construct()
    {
        parent::__construct();
        $this->model = new StoreModel();
    }

    function index()
    {
        $this->R($this->result);
    }

    /**
     * GET 方式 获取店铺详细信息接口
     * 传递参数：店铺ID st_id（int）
     */
    function getStoreInfo()
    {
        if(!IS_GET) $this->R($this->result);
        $st_id = intval(I('get.st_id'));
        if(empty($st_id))
        {
            $this->getStatusInfo();
        }
        //获取主办方具体详细信息
        $storeInfo = $this->model->getStoreInfoBySid($st_id);
        //获取主办方照片墙的第一张图
        $firstUrl = $this->model->getStoreTopImage($st_id);
        $imageShow['url'] = $firstUrl['url'];
        $imageShow['count'] = $this->model->getStoreImageCount($st_id);

        $this->getStatusInfo(1);
        $this->result['storeInfo'] = empty($storeInfo) ? array() : $storeInfo;
        $this->result['imageShow'] = empty($imageShow) ? array() : $imageShow;
        $this->R($this->result);
    }


    /**
     * GET方式
     * 店铺照片墙接口
     */
    function storeImageList()
    {
        if(!IS_GET) $this->R($this->result);
        $st_id = intval(I('get.st_id'));
        if(empty($st_id))
        {
            $this->getStatusInfo();
        }
        $imageList = $this->model->getStoreImageList($st_id);
        $this->getStatusInfo(1);
        $this->result['storeImages'] = empty($imageList) ? array() : $imageList;
        $this->R($this->result);
    }

    /**
     * 店铺收藏与取消收藏接口
     */
    function collectStore()
    {
        if(!IS_GET) $this->R($this->result);
        $st_id = intval(I('get.st_id'));
        $uid = intval(I('get.uid'));
        if(empty($st_id)||empty($uid)) $this->getStatusInfo();
        $collectInfo = $this->model->getUserCollectBySt_idAndUid($st_id,$uid);
        if(empty($collectInfo))
        {
            if($this->model->addCollectInfo($st_id,$uid))
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
            if($this->model->delStoreCollectInfo($collectInfo['id']))
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