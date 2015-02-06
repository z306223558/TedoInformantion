<?php
/**
 * Created by PhpStorm.
 * 评论的模型类
 *
 * User: jun
 * Date: 2015/1/22
 * Time: 13:51
 */

namespace Information\Model;
use Think\Model;

class CommentModel extends Model
{

    function getActInfoByAid($id,$type)
    {
        return M('activity')->where(array('isDel'=>0,'id'=>$id,'type'=>$type))->field('id')->find();
    }

    function getMainCommentTotal($id)
    {
        return M('act_comment')->where(array('id'=>$id,'isDel'=>0))->count();
    }

    function getMainCommInfo($id,$type,$start,$num)
    {
        return M('act_comment')->where(array('id'=>$id,'type'=>$type,'isDel'=>0))->order(array('c_time'=>'desc'))->limit($start,$num)->field('cid,uid,uuid,content,hasImage,c_time,hasComment')->select();
    }

    function getUserInfo($uid)
    {
        return M('users')->where(array('uid'=>$uid,'isDel'=>0))->field('uid,user_login,user_avatar')->find();
    }

    function getMainCommentImage($cid)
    {
        return M('comment_image')->where(array('mid'=>$cid,'type'=>0,'isDel'=>0))->field('id,url,thumb_url')->order(array('sort'=>'asc'))->select();
    }

    function getReplayInfo($cid)
    {
        return M('comment_replay')->where(array('cid'=>$cid,'isDel'=>0))->field('rid,uid,uuid,r_uid,r_uuid,content,c_time,hasImage')->order(array('c_time'=>'asc'))->select();
    }

    function getReplayCommentImage($rid)
    {
        return M('comment_image')->where(array('mid'=>$rid,'type'=>1,'isDel'=>0))->field('id,url,thumb_url')->order(array('sort'=>'asc'))->select();
    }

    function setMainCommentInfo($data)
    {
        if(empty($data)) return false;
        return M('act_comment')->data($data)->add();
    }

    function setCommentImageInfo($data)
    {
        if(empty($data)) return false;
        return M('comment_image')->data($data)->add();
    }
    function updateCommentHasImage($cid)
    {
        if(empty($cid))
        {
            return false;
        }
        return M('act_comment')->where(array('cid'=>$cid))->setField(array('hasImage'=>1));
    }

    function setReplayCommentInfo($data)
    {
        if(empty($data))
        {
            return false;
        }
        return M('comment_replay')->data($data)->add();
    }

    function getCommentHasCommentStatus($cid)
    {
        return M('act_comment')->where(array('cid'=>$cid))->field('hasComment')->find();
    }

    function updateHasComment($cid)
    {
        return M('act_comment')->where(array('cid'=>$cid))->setField(array('hasComment'=>1));
    }

    function setReplayCommentImageInfo($data)
    {
        if(empty($data))
        {
            return false;
        }
        return M('comment_image')->data($data)->add();
    }

    function updateReplayCommentHasImage($rid)
    {
        if(empty($rid))
        {
            return false;
        }
        return M('comment_replay')->where(array('rid'=>$rid,'isDel'=>0))->setField(array('hasImage'=>1));
    }

    function getMainCommItemIdInfo($cid)
    {
        return M('act_comment')->where(array('isDel'=>0,'cid'=>$cid))->field('id,type')->find();
    }

    function setUserMessageInfo($data)
    {
        if(empty($data))
        {
            return false;
        }

        return M('user_message')->data($data)->add();
    }



}
