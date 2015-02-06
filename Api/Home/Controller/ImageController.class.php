<?php
namespace Home\Controller;
use Home\Model\ImageModel;
use Think\Upload;
use Think\Controller;

class ImageController extends Controller
{
    private $result = array();

    private $model = NULL;

    private $data = NULL;

    public function __construct()
    {
        parent::__construct();
        $this->result['status'] = 0;
        $this->result['message'] = '非法操作';
        $this->result['emessage'] = 'Illegal operation';
        $this->model = new ImageModel();
    }

    public function getDisguiseImage()
    {
        if(!IS_GET) $this->R($this->result);
        $type = intval(I('type'));
        //获取妆扮的素材
        $disguiseType = $this->model->getDisguiseTypeNum($type);
        if(empty($disguiseType))
        {
            $this->result['message'] = '无分类信息';
            $this->result['emessage'] = 'Has No tpye Info';
            $this->R($this->result);
        }
        $disguiseInfo = $this->model->getDisguiseInfo($type);
        if(empty($disguiseInfo))
        {
            $this->result['message'] = '素材信息为空';
            $this->result['emessage'] = 'Has No Info';
            $this->R($this->result);
        }
        $emtpyArr = array();
        foreach($disguiseInfo as $key=>$value)
        {
            $tp = $value['name'];
            $typeInfo = $this->model->getDisguiseTypeInfoByDid($value['id'],$type);
            foreach($typeInfo as $k => $v)
            {
                $this->data[$tp]['id'] = $key;
                $this->data[$tp][$v['sort']]['tid'] = $v['tid'];
                $this->data[$tp][$v['sort']]['type_name'] = $v['name'];
                $this->data[$tp][$v['sort']]['count'] = $v['count'];
                if(isset($v['type']))
                {
                    $this->data[$tp][$v['sort']]['type'] = $v['type'];
                }
                $this->data[$tp][$v['sort']]['background'][] = unserialize($v['background']);
                if(empty($type))
                {
                    $imageList = $this->model->getDisguiseImageListByTypeId($v['tid']);
                    $this->data[$tp][$v['sort']]['imageList'] = empty($imageList) ? $emtpyArr : $imageList;
                    unset($imageList);
                }else
                {
                    $backImageList = $this->model->getChangeFaceBackImage($v['tid']);
                    $this->data[$tp][$v['sort']]['backImageList'] = empty($backImageList) ? $emtpyArr : $backImageList;
                    foreach($this->data[$tp][$v['sort']]['backImageList'] as $ke => $va)
                    {
                        $this->data[$tp][$v['sort']]['backImageList'][$ke]['changeImage'][] = $this->model->getChangeFaceChangeImage($va['mid']);
                    }
                }
            }
        }
        if(empty($this->data))
        {
            $this->result['message'] = '没有素材信息';
            $this->result['emessage'] = 'Has No Info';
            $this->R($this->result);
        }
        $this->result['status'] = 1;
        $this->result['message'] = 'OK';
        $this->result['emessage'] = 'OK';
        $this->result['disguise'] = $this->data;
        $this->R($this->result);

    }

    public function getChangeFaceImage()
    {
        if(!IS_GET) $this->R($this->result);
        $result = array();
        $result['back_img'] = '/Public/Image/Material/changeFace/0.jpg';
        $result['type_img'][0]['url'] = '/Public/Image/Material/changeFace/1.png';
        $result['type_img'][0]['location'] = '02640144';
        $result['type_img'][1]['url'] = '/Public/Image/Material/changeFace/2.png';
        $result['type_img'][1]['location'] = '05490195';
        $this->result['imageInfo'] = $result;
        $this->result['status'] = 1;
        $this->result['message'] = 'OK';
        $this->result['emessage'] = 'OK';
        $this->R($this->result);
    }

    /**
     * 上传图片方法
     */
    public function uploadImage()
    {
        $uid = intval(I('uid'));
        $type = intval(I('type'));
        $name = trim(I('name'));
        if(empty($uid) || empty($_FILES) || empty($type) || empty($name))
        {
            $this->result['message'] = '缺少必要信息';
            $this->result['emessage'] = 'The necessary information can not be empty';
            $this->R($this->result);
        }
        $fileExtension = end(explode('.',$_FILES[$name]['name']));
        $time = time();
        $upload = new Upload();
        //设置上传文件大小
        $upload->maxSize            = 3292200;
        //设置上传文件类型
        $upload->exts          = explode(',', 'jpg,jpeg,png,gif');
        //设置附件上传根目录
        $upload->rootPath           = './Public/Image/';
        //设置附件上传目录
        $upload->savePath           = 'UserImage/';
        //设置上传文件规则
        $upload->saveName           = array('trim',$uid.'_'.$type.'_'.$time);
        //删除原图
        $upload->thumbRemoveOrigin  = true;

        if (!$upload->upload())
        {
            //捕获上传异常
            $this->result['message'] =$upload->getError();
            $this->R($this->result);
        }
        $userImage = $this->model->getUserImage($uid);
        $arr_image = unserialize($userImage[0]['user_image']);
        unset($userImage);
        if(file_exists('.'.$arr_image[$type]))
        {
            unlink('.'.$arr_image[$type]);
        }
        $arr_image[$type] = C('__PUBLIC__').$upload->savePath.date('Y-m-d',$time).'/'.$uid.'_'.$type.'_'.$time.'.'.$fileExtension;
        $image = serialize($arr_image);
        unset($arr_image);
        $rs = $this->model->setImageInfo($image,$uid);
        if(empty($rs))
        {
            $this->result['message'] = '写入数据库失败';
            $this->result['emessage'] = 'Write to the database failed';
            $this->R($this->result);
        }
        $this->result['status'] = 1;
        $this->result['message'] = 'OK';
        $this->result['emessage'] = 'OK';
        $this->R($this->result);
    }

    /**
     * 上传更新头像信息
     * @param $uid
     * @return bool|string
     */
    public function uploadAvatar($uid)
    {
        if(empty($uid))
        {
            return false;
        }
        $fileExtension = end(explode('.',$_FILES['avatar']['name']));
        $time = time();
        $upload = new Upload();
        //设置上传文件大小
        $upload->maxSize            = 3292200;
        //设置上传文件类型
        $upload->exts          = explode(',', 'jpg,png,jpeg,gif');
        //设置附件上传根目录
        $upload->rootPath           = './Public/Image/';
        //设置附件上传目录
        $upload->savePath           = 'UserAvatar/';
        //设置上传文件规则
        $upload->saveName           = array('trim',$uid.'_'.$time);
        //删除原图
        $upload->thumbRemoveOrigin  = true;

        if (!$upload->upload())
        {
            $this->result['message'] =$upload->getError();
            $this->R($this->result);
        }
        $userAvatar = $this->model->getUserAvatar($uid);
        $avatar = C('__PUBLIC__').$upload->savePath.date('Y-m-d',$time).'/'.$uid.'_'.$time.'.'.$fileExtension;
        if(file_exists('.'.$userAvatar[0]['avatar']) && ($userAvatar[0]['avatar'] !== '/Public/Image/UserAvatar/user_avatar_default.png'))
        {
            unlink('.'.$userAvatar[0]['avatar']);
        }
        return $avatar;
    }

    /**
     * 上传更新头像信息
     * @return bool|string
     */
    public function uploadUserGoodesImage()
    {
        $time = time();
        $upload = new Upload();
        //设置上传文件大小
        $upload->maxSize            = 3292200;
        //设置上传文件类型
        $upload->exts          = explode(',', 'jpg');
        //设置附件上传根目录
        $upload->rootPath           = './Public/Image/';
        //设置附件上传目录
        $upload->savePath           = 'UserGoods/';
        //设置上传文件规则
        $upload->saveName           = array('trim',mt_rand(0,100).'_'.$time);
        //删除原图
        $upload->thumbRemoveOrigin  = true;

        if (!$upload->upload())
        {
            //捕获上传异常
            $this->result['message'] =$upload->getError();
            $this->R($this->result);
        }
        $image = C('__PUBLIC__').$upload->savePath.date('Y-m-d',$time).'/'.$upload->saveName[1].'.jpg';
        return $image;
    }
}