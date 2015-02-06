<?php
namespace Home\Model;
use Think\Model;

class ImageModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getImageTotal($type)
    {
        return M('image')->where(array('type' => $type,'isDel' => 0))->count();
    }

    /**
     * 根据uid获取用户图片信息
     * @param $uid
     *
     * @return bool
     */
    public function getUserImage($uid)
    {
        if(empty($uid))
        {
            return false;
        }
        return M('users')->where('uid = '.$uid)->field('user_image')->select();
    }



    public function setImageInfo($image,$uid)
    {
        if(empty($image) || empty($uid))
        {
            return false;
        }

        return M('users')->where('uid = '.$uid)->setField('user_image',$image);
    }

    public function getUserAvatar($uid)
    {
        if(empty($uid))
        {
            return false;
        }

        return M('users')->where(array('uid = '.$uid,
                                      'isDel = 0'
                      ))->field('user_avatar')
                        ->select();
    }


    public function getGoodsUserImage($pid)
    {
        if(empty($pid))
        {
            return false;
        }

        return M('goods')->where(array('pid'=>intval($pid),
                                       'isDel'=>0,
                                       'confirm'=>0
            ))->select();
    }

    public function setGoodsUserImage($image,$pid)
    {
        if(empty($image) || empty($pid))
        {
            return false;
        }

        return M('goods')->where(array('pid'=> intval($pid),
                                      'confirm'=>0,
                                      'isDel'=>0
                                    ))->setField('user_image',$image);
    }

    public function getDisguiseTypeNum($type)
    {
        if(empty($type))
        {
            $model = M('material_disguise');
        }
        else
        {
            $model = M('material_change_face');
        }

        return $model->where(array('isDel'=>0))->count('id');
    }

    public function getDisguiseInfo($type)
    {
        if(empty($type))
        {
            $model = M('material_disguise');
        }
        else
        {
            $model = M('material_change_face');
        }
        return $model->where(array('isDel'=> 0))->select();
    }

    public function getDisguiseTypeInfoByDid($did,$type)
    {
        if(empty($did))
        {
            return false;
        }
        if(empty($type))
        {
            $model = M('disguise_type');
            return $model->where(array('did'=>intval($did),
                                       'isDel'=> 0,
                                       'free'=> 1
                ))->field('tid,name,count,background,type,sort')
                ->order('sort asc')
                ->select();
        }
        else
        {
            $model = M('change_face_type');
            return $model->where(array('did'=>intval($did),
                                       'isDel'=> 0,
                                       'free'=> 1
                ))->field('tid,name,count,background,sort')
                ->order('sort asc')
                ->select();
        }
    }

    public function getDisguiseImageListByTypeId($tid)
    {
        $model = M('disguise_img');
        if(empty($tid))
        {
            return false;
        }

        return $model->where(array('tid' => intval($tid),
                                              'isDel'=> 0
                              ))->field('url,updateTime')
                                ->order('mid desc','updateTime desc')
                                ->select();
    }

    public function getChangeFaceBackImage($tid)
    {
        $model = M('change_face_img');
        if(empty($tid))
        {
            return false;
        }

        return $model->where(array('tid' => intval($tid),
                                   'isDel'=> 0
            ))->field('mid,url,updateTime,count')
            ->order('updateTime desc')
            ->select();
    }

    public function getChangeFaceChangeImage($mid)
    {
        if(empty($mid))
        {
            return false;
        }

        return M('change_face_ins')->where(array('parentId'=>intval($mid),
                                                 'isDel'=>0))->field('url,location,sort,updateTime')->order('sort asc')->select();
    }
}
