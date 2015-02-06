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

class UserInformationModel extends Model{

    function getUnReadMessageCount($uid)
    {
        return M('user_message')->where(array('uid'=>$uid))->count();
    }

    function getUserCollectTotal($uid,$type)
    {
        return M('user_collect')->where(array('uid'=>$uid,'type'=>$type))->count();
    }

    function getUserCollectInfo($uid,$type,$start,$num)
    {
        switch($type)
        {
            case 1 :
                $result = false;
                break;
            case 2 :
                $result = M('store s')->join(C('DB_PREFIX').'user_collect uc ON s.st_id = uc.item_id')->where(array('uc.uid'=>$uid,'uc.type'=>$type))->limit($start,$num)->field('s.st_id,s.st_name,s.st_logo')->select();
                break;
            case 3 :
                $result = M('activity a')->join(C('DB_PREFIX').'user_collect uc ON a.aid = uc.item_id')->where(array('uc.uid'=>$uid,'uc.type'=>$type))->limit($start,$num)->field('a.aid,a.act_name,a.act_logo')->select();
                break;
            default :
                $result = false;
                break;
        }
        return $result;
    }

    function delUnReadMsg($mid)
    {
        return M('user_message')->delete($mid);
    }

    function getUnReadMessageListByPage($uid,$start,$num)
    {
        if(empty($uid))
        {
            return false;
        }
        return M('user_message um')->join(C('DB_PREFIX').'act_comment ac ON ac.id = um.id AND ac.type = um.type')
                                   ->join(C('DB_PREFIX').'comment_replay cr ON um.message_id = cr.rid')
                                   ->where(array('um.uid'=>$uid))
                                   ->field('um.mid,ac.cid,ac.content as main_content,ac.type,ac.uid as main_uid,ac.uuid as main_uuid,ac.c_time as main_cTime,cr.rid,cr.content,cr.c_time,cr.uid,cr.uuid')
                                   ->group('cr.rid')
                                   ->order(array('um.cTime'=>'desc'))
                                   ->limit($start,$num)
                                   ->select();
    }

    function getUserInfo($uid)
    {
        return M('users')->where(array('uid'=>$uid,'isDel'=>0))->field('uid,user_login as name,user_avatar as avatar')->find();
    }


}