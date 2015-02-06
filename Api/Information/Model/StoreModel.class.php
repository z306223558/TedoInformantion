<?php
/**
 * Created by PhpStorm.
 * 店铺的模型类
 *
 * User: jun
 * Date: 2015/1/22
 * Time: 13:51
 */

namespace Information\Model;
use Think\Model;

class StoreModel extends Model{

    function getStoreInfoBySid($st_id)
    {
        return M('store')->where(array('isDel'=>0,'st_id'=>$st_id))->field('st_id,sid,st_name,st_logo,st_banner,desc,short_desc,cTime')->find();
    }

    function getStoreTopImage($st_id)
    {
        return M('store_image')->where(array('isDel'=>0,'st_id'=>$st_id))->field('url')->order(array('top'=>'desc','cTime'=>'desc'))->find();
    }

    function getStoreImageCount($st_id)
    {
        return M('store_image')->where(array('isDel'=>0,'st_id'=>$st_id))->count();
    }

    function getStoreImageList($st_id)
    {
        return M('store_image')->where(array('isDel'=>0,'st_id'=>$st_id))->order(array('top'=>'desc','cTime'=>'desc'))->field('id,url')->select();
    }

    function getUserCollectBySt_idAndUid($st_id,$uid)
    {
        return M('user_collect')->where(array('item_id'=>$st_id,'uid'=>$uid,'type'=>1))->find();
    }

    function addCollectInfo($st_id,$uid)
    {
        $data['item_id'] = $st_id;
        $data['uid'] = $uid;
        $data['type'] = 1;
        $data['cTime'] = date('Y-m-d H:i:s',time());
        return M('user_collect')->data($data)->add();
    }

    function delStoreCollectInfo($id)
    {
        if(empty($id))
        {
            return false;
        }
        return M('user_collect')->delete($id);
    }

}