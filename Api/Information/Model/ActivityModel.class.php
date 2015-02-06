<?php
/**
 * Created by PhpStorm.
 * 主办方的模型类
 *
 * User: jun
 * Date: 2015/1/22
 * Time: 13:51
 */

namespace Information\Model;
use Think\Model;

class ActivityModel extends Model{

    function getActivityInfo($aid)
    {
        return M('activity')->where(array('isDel'=>0,'aid'=>$aid))->field('aid,sid,act_name,act_logo,act_begin,act_end,act_addr,act_people_num,act_desc,like_num')->find();
    }

    function getActStoreByAid($aid)
    {
        return M('act_store_relation asr')->join(C('DB_PREFIX').'store s ON asr.st_id = s.st_id')->where(array('asr.aid'=>$aid,'isDel'=>0))->field('s.st_id,s.st_name,s.st_logo')->order(array('asr.cTime'=>'asc'))->select();
    }

    function getActivityTopImage($aid)
    {
        return M('activity_image')->where(array('isDel'=>0,'aid'=>$aid,'top'=>1))->field('url')->find();
    }

    function getActivityImageCount($aid)
    {
        return M('activity_image')->where(array('isDel'=>0,'aid'=>$aid))->count();
    }

    function getActivitySponsorLogoInfo($sid)
    {
        return M('sponsor')->where(array('sid'=>$sid,'isDel'=>0))->field('sLogo,sName,sid')->find();
    }

    function getActivityLikeNum($aid)
    {
        return M('activity')->where(array('isDel'=>0,'aid'=>$aid))->field('aid,like_num')->find();
    }

    function getActivityImageList($aid)
    {
        return M('activity_image')->where(array('isDel'=>0,'aid'=>$aid))->field('id,url')->order(array('top'=>'desc','cTime'=>'desc'))->select();
    }

    function updateLikeNum($aid)
    {
        return M('activity')->where(array('aid'=>$aid,'isDel'=>0))->setInc('like_num');
    }

    function getUserCollectByAidAndUid($aid,$uid)
    {
        return M('user_collect')->where(array('item_id'=>$aid,'uid'=>$uid,'type'=>2))->find();
    }

    function addCollectInfo($aid,$uid)
    {
        $data['item_id'] = $aid;
        $data['uid'] = $uid;
        $data['type'] = 2;
        $data['cTime'] = date('Y-m-d H:i:s',time());
        return M('user_collect')->data($data)->add();
    }

    function delActCollectInfo($id)
    {
        if(empty($id))
        {
            return false;
        }
        return M('user_collect')->delete($id);
    }

}