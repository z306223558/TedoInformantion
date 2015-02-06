<?php
/**
 * Created by PhpStorm.
 * 评论相关控制类
 *
 * User: xiaojun@tedochina.com
 * Date: 2015/1/22
 * Time: 13:33
 */

namespace Information\Controller;
use Common\Controller\InformationBaseController;
use Information\Model\CommentModel;

class CommentController extends InformationBaseController
{

    function __construct()
    {
        parent::__construct();
        $this->model = new CommentModel();
    }

    function index()
    {
        $this->R($this->result);
    }

    /**
     * GET方式 参数：
     * 可选 start （int）
     * 可选 num （int）
     * 评论内容获取
     */
    function getActCommentInfo()
    {
        if(!IS_GET) $this->R($this->result);
        $type = intval(I('get.type'));
        $id = intval(I('get.id'));
        if(empty($id)||empty($type))
        {
            $this->getStatusInfo();
        }
        $start = intval(I('get.start'));
        $num = intval(I('get.num'));
        //分页获取主评论列
        $total = intval($this->model->getMainCommentTotal($id,$type));//根据type来获取不同的评论
        $page = $this->getPageInfo($total,$start,$num);
        //获取分页评论信息
        $mainCommentList = $this->model->getMainCommInfo($id,$type,$page->firstRow,$page->listRows);
        if(empty($mainCommentList))
        {
            $this->getStatusInfo(-1);
            $this->result['comment'] = array();
            $this->R($this->result);
        }
        $gustAvatar = C('YOUKE_AVATAR');
        foreach($mainCommentList as $key=>$val)
        {
            $comment[$key]['cid'] = $val['cid'];
            $comment[$key]['content'] = $val['content'];
            $comment[$key]['cTime'] = $val['c_time'];
            $comment[$key]['uid'] = $val['uid'];
            $comment[$key]['uuid'] = $val['uuid'];
            if(empty($val['uid']))
            {
                $comment[$key]['uname'] = C('YOUKE_NAME').$val['uuid'];
                $comment[$key]['avatar'] = $gustAvatar;
            }else
            {
                //获取用户的名字和头像
                $userInfo = $this->model->getUserInfo($val['uid']);
                $comment[$key]['uname'] =$userInfo['user_login'];
                $comment[$key]['avatar'] = $userInfo['user_avatar'];
            }
            if(empty($val['hasImage']))
            {
                $comment[$key]['images'] = array();
            }else{
                $comment[$key]['images'] = $this->model->getMainCommentImage($val['cid']);
            }

            //获取回复评论
            if(empty($val['hasComment']))
            {
                $comment[$key]['replay'] = array();
            }else
            {
                $replay = $this->model->getReplayInfo($val['cid']);
                if(empty($replay))
                {
                    $comment[$key]['replay'] = array();
                }else
                {
                    foreach($replay as $k=>$v)
                    {
                        $comment[$key]['replay'][$k]['content'] = $v['content'];
                        $comment[$key]['replay'][$k]['cTime'] = $v['c_time'];
                        $comment[$key]['replay'][$k]['uid'] = $v['uid'];
                        $comment[$key]['replay'][$k]['uuid'] = $v['uuid'];
                        $comment[$key]['replay'][$k]['r_uid'] = $v['r_uid'];
                        $comment[$key]['replay'][$k]['r_uuid'] = $v['r_uuid'];
                        if(empty($v['uid']))
                        {
                            $comment[$key]['replay'][$k]['from_name'] = '匿名用户'.$v['uuid'];
                            $comment[$key]['replay'][$k]['from_avatar'] = $gustAvatar;
                        }else
                        {
                            //获取用户的名字和头像
                            $userInfo = $this->model->getUserInfo($v['uid']);
                            $comment[$key]['replay'][$k]['from_name'] =$userInfo['user_login'];
                            $comment[$key]['replay'][$k]['from_avatar'] = $userInfo['user_avatar'];
                        }

                        if(empty($v['r_uid']))
                        {
                            $comment[$key]['replay'][$k]['to_name'] = '匿名用户'.$v['r_uuid'];
                            $comment[$key]['replay'][$k]['to_avatar'] = $gustAvatar;
                        }else
                        {
                            //获取用户的名字和头像
                            $userInfo = $this->model->getUserInfo($v['r_uid']);
                            $comment[$key]['replay'][$k]['to_name'] =$userInfo['user_login'];
                            $comment[$key]['replay'][$k]['to_avatar'] = $userInfo['user_avatar'];
                        }

                        if(empty($v['hasImage']))
                        {
                            $comment[$key]['replay'][$k]['images'] = array();
                        }else{
                            $comment[$key]['replay'][$k]['images'] = $this->model->getReplayCommentImage($v['rid']);
                        }
                    }
                }
            }
        }
        //最后返回数据
        $this->getStatusInfo(1);
        $this->result['comment'] = empty($comment) ? array(): $comment;
        unset($comment);
        $this->R($this->result);
    }

    /**
     * POST方式 编写评论接口
     * 参数：
     * 必需 uid （int）
     * 必需 aid （int）
     * 必需 content （text）
     * 可选 uuid （string）
     * 可选 image （file）
     */
    function EditMainComment()
    {
        if(!IS_POST) $this->R($this->result);
        $type = intval(I('post.type'));
        $id = intval(I('post.id'));
        $uid = intval(I('post.uid'));
        $content = trim(I('post.content'));
        if(empty($id) || empty($content) ||empty($type))
        {
            $this->getStatusInfo();
        }
        if(empty($uid))
        {
            $uuid = trim(I('post.uuid'));
            if(empty($uuid)) $this->getStatusInfo();
        }else{
            $uuid = 0;
        }
        //除图片外的所有数据的整理
        $data['id'] = $id;
        $data['type'] = $type;
        $data['uid'] = $uid;
        $data['uuid'] = $uuid;
        $data['content'] = $content;
        $data['hasComment'] = 0;
        $data['hasImage'] = 0;
        $data['c_time'] = date('Y-m-d H:i:s',time());
        $res = $this->model->setMainCommentInfo($data);
        unset($data);

        if(empty($res)) $this->getStatusInfo(-2);
        //如果有图片上传则进行图片上传和剪裁
        if(!empty($_FILES))
        {
            if(count($_FILES) > 3)
            {
                $this->getStatusInfo(-4);
            }
            $rootPath = C("COMMENT_IMAGE_PATH.".$type).date('Y-m-d',time()).'/'.$res.'/Main/';
            $imageSavePath = C("COMMENT_IMAGE_SAVE_PATH.".$type).date('Y-m-d',time()).'/'.$res.'/Main/';
            if(!is_dir($rootPath))
            {
                mkdir($rootPath,0777,true);
            }
            $config=array(
                'rootPath' => $rootPath,
                'savePath' => './',
                'maxSize' => 11048576,
                'exts'       =>    array('jpg', 'gif', 'png', 'jpeg'),
                'autoSub'    =>    false,
            );
            $upload = new \Think\Upload($config);
            //文件数组上传
            $info = $upload->upload();
            if(!$info)
            {
                $this->getStatusInfo(-5);
                $upload->getError();
            }else{
                //生成缩略图
                $image = new \Think\Image();
                $i = 1;
                if(!is_dir($rootPath.'thumb/'))
                {
                    mkdir($rootPath.'thumb/',0777,true);
                }
                foreach($info as $k=>$v)
                {
                    $image->open($rootPath.$v['savename']);
                    $image->thumb(120,180,\Think\Image::IMAGE_THUMB_FILLED)->save($rootPath.'thumb/'.$v['savename']);
                    if(file_exists($rootPath.$v['savename']))
                    {
                        $imageData['mid'] = $res;
                        $imageData['type'] = 0;
                        $imageData['url'] = $imageSavePath.$v['savename'];
                        $imageData['thumb_url'] = $imageSavePath.'thumb/'.$v['savename'];
                        $imageData['sort'] = $i;
                        $thumbRes = $this->model->setCommentImageInfo($imageData);
                        unset($imageData);
                        if(!$thumbRes)
                        {
                            $this->getStatusInfo(-2);
                        }
                    }
                    $i++;
                }
                if(!$this->model->updateCommentHasImage($res))
                {
                    $this->getStatusInfo(-3);
                }
            }
        }
        $this->getStatusInfo(1);
        $this->R($this->result);
    }

    /**
     * POST方式 回复评论接口
     * 参数：
     * 必需 uid （int）
     * 必需 cid （int）
     * 必须 r_uid （int）
     * 必需 content （text）
     * 可选 uuid （string）
     * 可选 r_uuid （string）
     * 可选 image （file）
     */
    function replayComment()
    {
        if(!IS_POST) $this->R($this->result);
        $type = intval(I('post.type'));
        $cid = intval(I('post.cid'));
        $content = trim((I('post.content')));
        $uid = intval(I('post.uid'));
        $uuid = trim(I('post.uuid'));
        //判断匿名用户身份
        $uuid = empty($uid) ? (empty($uuid) ? $this->getStatusInfo() : $uuid) : 0;
        $r_uid = intval(I('post.r_uid'));
        $r_uuid = trim(I('post.r_uuid'));
        //判断被回复人的匿名身份
        $r_uuid = empty($r_uid) ? (empty($r_uuid) ? $this->getStatusInfo() : $r_uuid) : 0;
        if(empty($cid) || empty($content) ||empty($type))
        {
            $this->getStatusInfo();
        }
        //准备回复的信息
        $data['cid'] = $cid;
        $data['content'] = $content;
        $data['uid'] = $uid;
        $data['uuid'] = $uuid;
        $data['r_uid'] = $r_uid;
        $data['r_uuid'] = $r_uuid;
        $data['hasImage'] = 0;
        $data['c_time'] = date('Y-m-d H:i:s',time());
        $res = $this->model->setReplayCommentInfo($data);
        unset($data);
        //更新评论状态
        $hasComment = $this->model->getCommentHasCommentStatus($cid);
        if(empty($hasComment['hasComment']))
        {
            $updateHasComment = $this->model->updateHasComment($cid);
        }else{
            $updateHasComment = 1;
        }
        if(empty($res)||empty($updateHasComment)) $this->getStatusInfo(-2);
        //如果有图片上传则进行图片上传和剪裁
        if(!empty($updateHasComment))
        {
            if(!empty($_FILES))
            {
                if(count($_FILES) > 3)
                {
                    $this->getStatusInfo(-4);
                }
                $rootPath = C("COMMENT_IMAGE_PATH.".$type).date('Y-m-d',time()).'/'.$cid.'/Replay/';
                $imageSavePath = C("COMMENT_IMAGE_SAVE_PATH.".$type).date('Y-m-d',time()).'/'.$cid.'/Replay/';
                if(!is_dir($rootPath))
                {
                    mkdir($rootPath,0777,true);
                }
                $config=array(
                    'rootPath' => $rootPath,
                    'savePath' => './',
                    'maxSize' => 11048576,
                    'exts'       =>    array('jpg', 'gif', 'png', 'jpeg'),
                    'autoSub'    =>    false,
                );
                $upload = new \Think\Upload($config);
                //文件数组上传
                $info = $upload->upload();
                if(!$info)
                {
                    $this->getStatusInfo(-5);
                    $upload->getError();
                }else{
                    //生成缩略图
                    $image = new \Think\Image();
                    $i = 1;
                    foreach($info as $k=>$v)
                    {
                        $image->open($rootPath.$v['savename']);
                        if(!is_dir($rootPath.'thumb/'))
                        {
                            mkdir($rootPath.'thumb/',0777,true);
                        }
                        $image->thumb(120,180,\Think\Image::IMAGE_THUMB_FILLED)->save($rootPath.'thumb/'.$v['savename']);
                        if(file_exists($rootPath.$v['savename']))
                        {
                            $imageData['mid'] = $res;
                            $imageData['type'] = 1;
                            $imageData['url'] = $imageSavePath.$v['savename'];
                            $imageData['thumb_url'] = $imageSavePath.'thumb/'.$v['savename'];
                            $imageData['sort'] = $i;
                            $thumbRes = $this->model->setReplayCommentImageInfo($imageData);
                            if(!$thumbRes)
                            {
                                $this->getStatusInfo(-2);
                            }
                        }
                        $i++;
                    }
                }
                if(!$this->model->updateReplayCommentHasImage($res))
                {
                    $this->getStatusInfo(-3);
                }
            }
            if(!empty($r_uid))
            {
                if(!$this->addUserMessageInfo($cid,$r_uid,$type,$res))
                {
                    $this->getStatusInfo(-2);
                }
            }
            $this->getStatusInfo(1);
            $this->R($this->result);

        }else{
            $this->getStatusInfo(-3);
        }
    }


    //当被回复人不是游客的时候，向未读消息表中填入记录
    function addUserMessageInfo($cid,$uid,$type,$rid)
    {
        $item = $this->model->getMainCommItemIdInfo($cid);
        if($item['type'] == $type)
        {
            $ins['id'] = $item['id'];
        }else{
            return false;
        }
        $ins['uid'] = $uid;
        $ins['message_id'] = $rid;
        $ins['type'] = $type;
        $ins['cTime'] = date('Y-m-d H:i:s',time());
        return $this->model->setUserMessageInfo($ins);
    }
}