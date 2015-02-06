<?php
namespace Home\Controller;
use Home\Model\PostModel;
use Think\Controller;

class PostController extends Controller
{
    /**
     * @var array 私有变量，用来返回信息
     */
    private $result = array();

    private $model = NULL;

    private $data = NULL;

    /**
     * 构造函数，继承父类的构造函数，初始化变量
     */
    public function __construct()
    {
        parent::__construct();
        $this->result = array();
        $this->model = new PostModel();
        $this->result['status'] = 0;
        $this->result['message'] = '非法操作';
        $this->result['emessage'] = 'Illegal operation';
    }

    /**
     * 收货地址列表接口
     */
    public function postInfoList()
    {
        if(!IS_GET) $this->R($this->result);
        $uid = intval(I('uid'));
        if(empty($uid))
        {
            $this->result['message'] = '缺少必要信息';
            $this->result['emessage'] = 'The necessary information can not be empty';
            $this->R($this->result);
        }

        $postInfo = $this->model->getPostInfoByUid($uid);
        if(empty($postInfo))
        {
            $this->result['status'] = 1;
            $this->result['message'] = '收货信息不存在';
            $this->result['emessage'] = 'Receiving information does not exist';
            $this->R($this->result);
        }
        foreach($postInfo as $key => $value)
        {
            $post[$key]['isDefault'] = $value['isDefault'];
            $post[$key]['postId'] = $value['postId'];
            $post[$key]['postName'] = $value['pName'];
            $post[$key]['postAddr'] = $value['province'].$value['city'].$value['zone'].$value['addr'];
        }
        $this->result['status'] = 1;
        $this->result['message'] = 'OK';
        $this->result['emessage'] = 'OK';
        $this->result['postInfo'] = $post;
        $this->R($this->result);
    }

    /**
     * 添加或修改收货地址信息
     */
    public function OperationPost()
    {
        if(!IS_POST) $this->R($this->result);
        $this->data['uid'] = intval(I('uid'));
        if(empty( $this->data['uid']))
        {
            $this->result['message'] = '用户ID不能为空';
            $this->result['emessage'] = 'The user ID cannot be empty';
            $this->R($this->result);
        }
        $this->data['pName'] = trim(I('pName'));
        if(empty( $this->data['pName']))
        {
            $this->result['message'] = '收件人姓名不能为空';
            $this->result['emessage'] = 'The recipient name cannot be empty';
            $this->R($this->result);
        }
        $this->data['province'] = trim(I('province'));
        $this->data['city'] = trim(I('city'));
        if(empty($this->data['city']))
        {
            $this->result['message'] = '城市不能为空';
            $this->result['emessage'] = 'City can not be empty';
            $this->R($this->result);
        }
        $this->data['zone'] = trim(I('zone'));
        if(empty($this->data['zone']))
        {
            $this->result['message'] = '区县不能为空';
            $this->result['emessage'] = 'Zone can not be empty';
            $this->R($this->result);
        }
        $this->data['addr'] = trim(I('addr'));
        if(empty($this->data['addr']))
        {
            $this->result['message'] = '详细住址不能为空';
            $this->result['emessage'] = 'Detailed address can not be empty';
            $this->R($this->result);
        }
        $this->data['postNum'] = trim(I('postNum'));
        $this->data['pMobile'] = trim(I('pMobile'));
        if(empty($this->data['pMobile']))
        {
            $this->result['message'] = '收件人电话信息不能为空';
            $this->result['emessage'] = 'The recipient phone information cannot be empty';
            $this->R($this->result);
        }
        $this->data['pTel'] = trim(I('pTel'));
        $this->data['cTime'] = time();
        $postId = intval(I('postId'));
        if(empty($postId))
        {
            $user_post = $this->model->getUserPostCount($this->data['uid']);
            if(empty($user_post))
            {
                $this->data['isDefault'] = 1;
            }
            $post = $this->model->setPostInfo($this->data);
        }
        else
        {
            $post = $this->model->setPostInfo($this->data,$postId);
        }
        if(empty($post))
        {
            $this->result['status'] = '添加收货信息失败';
            $this->result['emessage'] = 'The addition of receiving information failed';
            $this->R($this->result);
        }
        $this->result['status'] = 1;
        $this->result['message'] = 'OK';
        $this->result['emessage'] = 'OK';
        $this->result['postId'] = $post;
        $this->R($this->result);
    }

    /**
     * 选取默认邮递地址
     */
    public function setDefaultPost()
    {
        if(!IS_POST) $this->R($this->result);
        $postId = intval(I('postId'));
        $uid = intval(I('uid'));
        if(empty($postId)||empty($uid))
        {
            $this->result['message'] = '必要信息不能为空';
            $this->result['emessage'] = 'City can not be empty';
            $this->R($this->result);
        }
        $post = $this->model->getOldDefaultPost($uid);
        if(empty($post))
        {
            $res = $this->model->setDefaultPost($postId);
        }else
        {
            $updata = $this->model->updataOldDefaultPost($post['postId']);
            if(empty($updata))
            {
                $this->result['message'] = '取消默认状态失败';
                $this->result['emessage'] = 'City can not be empty';
                $this->R($this->result);
            }
            $res = $this->model->setDefaultPost($postId);
        }
        if(empty($res))
        {
            $this->result['message'] = '更新状态失败';
            $this->result['emessage'] = 'City can not be empty';
            $this->R($this->result);
        }
        $this->result['status'] = 1;
        $this->result['message'] = 'OK';
        $this->result['emessage'] = 'OK';
        $this->R($this->result);
    }

    /**
     * 删除收货信息
     */
    public function delPostInfo()
    {
        if(!IS_POST) $this->R($this->result);
        $postId = intval(I('postId'));
        $uid = intval(I('uid'));
        if(empty($postId) || empty($uid))
        {
            $this->result['message'] = '必要信息不能为空';
            $this->result['emessage'] = 'City can not be empty';
            $this->R($this->result);
        }
        $res = $this->model->getPostInfoByPostIdAndUid($postId,$uid);
        if(empty($res))
        {
            $this->result['message'] = '未找到该收货地址信息';
            $this->result['emessage'] = 'City can not be empty';
            $this->R($this->result);
        }
        $result = $this->model->delPostByPostId($postId);
        if(empty($result))
        {
            $this->result['message'] = '操作失败';
            $this->result['emessage'] = 'City can not be empty';
            $this->R($this->result);
        }
        $this->result['status'] = 1;
        $this->result['message'] = 'OK';
        $this->result['emessage'] = 'OK';
        $this->R($this->result);
    }

    /**
     * 收货信息详细接口
     */
    public function postInfo()
    {
        if(!IS_GET) $this->R($this->result);
        $postId = intval(I('postId'));
        if(empty($postId))
        {
            $this->result['message'] = '必要信息不能为空';
            $this->result['emessage'] = 'City can not be empty';
            $this->R($this->result);
        }
        $res = $this->model->getPostInfoByPostId($postId);
        if(empty($res))
        {
            $this->result['message'] = '未找到该收货地址信息';
            $this->result['emessage'] = 'City can not be empty';
            $this->R($this->result);
        }
        $this->result['status'] = 1;
        $this->result['message'] = 'OK';
        $this->result['emessage'] = 'OK';
        $this->result['postInfo'] = $res;
        $this->R($this->result);

    }

} 