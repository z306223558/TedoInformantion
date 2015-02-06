<?php
namespace Home\Model;
use Think\Model;
class PostModel extends Model
{
    /**
     * 构造函数，继承父类构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function getPostInfoByUid($uid)
    {
        if(empty($uid))
        {
            return false;
        }

        return M('post')->where(array('isDel'  => 0,
                                      'uid'    => $uid
                      ))->order('isDefault desc','cTime desc')->select();
    }

    public function getUserPostCount($uid)
    {
        if(empty($uid))
        {
            return false;
        }

        return M('post')->where(array('uid'=>intval($uid),
                                      'isDel'=>0))->count();
    }

    public function setPostInfo($data,$postId = 0)
    {
        if(empty($data))
        {
            return false;
        }

        if(empty($postId))
        {
            return M('post')->data($data)->add();
        }
        else
        {
            return M('post')->data($data)->where('postId = '.$postId)->save();
        }
    }

    public function setDefaultPost($postId)
    {
        if(empty($postId))
        {
            return false;
        }
        return M('post')->where(array('postId'=>$postId,
                                      'isDel'=>0))->setField('isDefault',1);
    }

    public function updataOldDefaultPost($postId)
    {
        if(empty($postId))
        {
            return false;
        }
        return M('post')->where(array('postId'=>intval($postId),
                                      'isDel'=>0))->setField('isDefault',0);
    }

    public function getOldDefaultPost($uid)
    {
        return M('post')->where(array('isDefault'=>1,
                                      'uid'=>$uid,
                                      'isDel'=>0))->field('postId')->find();
    }

    public function getPostInfoByPostIdAndUid($postId,$uid)
    {
        if(empty($postId) || empty($uid))
        {
            return false;
        }

        return M('post')->where(array(
                'postId' => intval($postId),
                'isDel'=> 0,
                'uid' => intval($uid)
            ))->find();
    }

    public function delPostByPostId($postId)
    {
        if(empty($postId))
        {
            return false;
        }

        return M('post')->where(array(
                'postId' =>intval($postId),
                'isDel' => 0
            ))->setField('isDel',1);
    }

    public function getPostInfoByPostId($postId)
    {
        if(empty($postId))
        {
            return false;
        }

        return M('post')->where(array(
                'postId'=>intval($postId),
                'isDel'=>0
            ))->find();
    }
}