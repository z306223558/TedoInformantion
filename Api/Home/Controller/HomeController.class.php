<?php

namespace Home\Controller;
use Home\Model\HomeModel;
use Think\Controller;

class HomeController extends Controller
{

    //返回变量，用于返回全局结果
    private $result = array();
    //模型变量，用来实例化对应模型
    private $model = NULL;
    //数据变量，存放中间数据
    private $data = NULL;

    /**
     * 构造函数，继承父类的构造，初始化相关变量
     */
    public function __construct()
    {
        parent::__construct();
        $this->result['status'] = 0;
        $this->result['message'] = '非法操作';
        $this->result['emessage'] = 'Illegal operation';
        $this->model = new HomeModel();
    }

    /**
     * 防止非法进入
     */
    public function index()
    {
        $this->R($this->result);
    }

    /**
     * 主页面头饰和身体图片返回接口
     */
    public function mainFrame()
    {
        if(!IS_GET) $this->R($this->result);
        $type = intval(I('type'));//0表示299 1表示399 2表示199
        $this->data['hdList_num'] = $this->model->getDecorationsNum($type,0);
        $this->data['bdList_num'] = $this->model->getDecorationsNum($type,1);
        if(!empty($this->data['hdList_num']))
        {
            $this->data['hdList'] = $this->model->getDecorationsList($type,0);
        }
        if(!empty($this->data['bdList_num']))
        {
            $this->data['bdList'] = $this->model->getDecorationsList($type,1);
        }
        if(!empty($this->data['hdList']) || !empty($this->data['bdList']))
        {
            $this->result['status'] = 1;
            $this->result['message'] = 'OK';
            $this->result['emessage'] = 'OK';
            $this->result['hdList_num'] = $this->data['hdList_num'];
            $this->result['bdList_num'] = $this->data['bdList_num'];
            $this->result['hdList'] = $this->data['hdList'];
            $this->result['bdList'] = $this->data['bdList'];
            $this->R($this->result);
        }
        else
        {
            $this->result['message'] = '商品信息不全';
            $this->result['emessage'] = 'Commodity information incomplete';
            $this->R($this->result);
        }
    }

    /**
     * 意见反馈接口
     */
    public function feedBack()
    {
        if(!IS_POST) $this->R($this->result);
        $content = trim(I('content'));
        $uid = intval(I('uid'));
        $type = intval(I('type'));
        $info = trim(I('info'));
        if(empty($content))
        {
            $this->result['message'] = '必要信息不能为空';
            $this->result['emessage'] = 'The necessary information can not be empty';
            $this->R($this->result);
        }
        $res = $this->model->setFeedBackInfo($content,$uid,$type,$info);
        if(empty($res))
        {
            $this->result['message'] = '反馈信息失败';
            $this->result['emessage'] = 'Commodity information incomplete';
            $this->R($this->result);
        }
        $this->result['status'] = 1;
        $this->result['message'] = '反馈成功';
        $this->result['emessage'] = 'Update information success';
        $this->R($this->result);
    }

    /**
     * 搜狗的下载统计
     */
    public function addDownloadNum()
    {
        header("Access-Control-Allow-Origin: http://www.do-bi.cn");
        if(!IS_GET) {echo 500;exit;}
        $type = intval(I('type'));
        $addNum = $this->model->addDownloadNum($type);
        if(empty($addNum))
        {
            echo 500;exit;
        }else{
            echo 200;exit;
        }
    }

    /**
     * 主页商城信息
     */
    public function ShopList()
    {
        if(!IS_GET) $this->R($this->result);
        $shopList = $this->model->getShopList();
        $rest = array();
        $this->result['status'] = 1;
        $this->result['message'] = '获取成功';
        $this->result['emessage'] = 'Get Info Success';
        $this->result['shopList'] = empty($shopList) ? $rest : $shopList;
        $this->R($this->result);
    }

    /**
     * 只是用来初始化数据库使用
     */
//    public function test()
//    {
//       $hd = M('change_face_img');
//        for($i=0;$i < 50;$i++)
//        {
//            $data['tid'] = 7;
//            $data['filename'] = ($i+1).'.png';
//            $data['url'] = '/Public/Image/Material/prop/bubble/'.($i+1).'.png';
//            $data['updateTime'] = time();
//            $hd->data($data)->add();
//        }
//    }
//
//    /**
//     * 只是用来初始化数据库使用
//     */
//    public function test_img()
//    {
//        ini_set("max_execution_time", "0");
//        $ty = M('disguise_type');
//        $hd = M('disguise_img');
//        for($j=1;$j<20;$j++)
//        {
//            $info = $ty->find($j);
//            if(in_array($info['tid'] ,range(13,16)))
//            {
//                for($i=0;$i < intval($info['count']);$i++)
//                {
//                    $data['tid'] = $info['tid'];
//                    $data['updateTime'] = time();
//                    $data['filename'] = ($i+1).'.jpg';
//                    $data['url'] = C('__MATERIAL_IMG__').$info['index'].($i+1).'.jpg';
//                    if($hd->data($data)->add())
//                    {
//                        echo 'tid='.$j.' count='.($i+1).'url:'.$data['url'].'成功！<br />';
//                    }else
//                    {
//                        echo 'tid='.$j.' count='.($i+1).'url:'.$data['url'].'<b style="color:red">失败！</b><br />';
//                    }
//                }
//            }
//            else
//            {
//                for($i=0;$i < intval($info['count']);$i++)
//                {
//                    $data['tid'] = $info['tid'];
//                    $data['updateTime'] = time();
//                    $data['filename'] = ($i+1).'.png';
//                    $data['url'] = C('__MATERIAL_IMG__').$info['index'].($i+1).'.png';
//                    if($hd->data($data)->add())
//                    {
//                        echo 'tid='.$j.' count='.($i+1).'url:'.$data['url'].'成功！<br />';
//                    }else
//                    {
//                        echo 'tid='.$j.' count='.($i+1).'url:'.$data['url'].'<b style="color:red">失败！</b><br />';
//                    }
//                }
//            }
//        }
//    }
//    public function test_img2()
//    {
//        $ty = M('disguise_type');
//        $hd = M('disguise_img');
//        $info = $ty->find(19);
//        for($i=0;$i < intval($info['count']);$i++)
//        {
//            $data['tid'] = $info['tid'];
//            $data['updateTime'] = time();
//            $data['filename'] = ($i+1).'.png';
//            $data['url'] = C('__MATERIAL_IMG__').$info['index'].($i+1).'.png';
//            $hd->data($data)->add();
//        }
//}
    /**
     * 只是用来初始化数据库使用
     */
//    public function test_bd()
//    {
//        $hd = M('decorations_bd');
//        for ($i = 0; $i < 30; $i++) {
//            $data['cTime'] = time();
//            $data['type'] = 2;
//            $data['url'] = C('__BODY_DECORATIONS__').'199/'.($i+1).'.png';
//            if($i > 4)
//            {
//                $data['isDel'] = 1;
//            }
//            $hd->data($data)->add();
//            unset($data);
//        }
//    }

//      public function background()
//      {
//          $back = M('disguise_type');
//          for($i= 0; $i<19;$i++)
//          {
//              $index = $this->model->getDecorationsTypeList($i+1);
////              $image['select'] = '/Public/Image/Material/'.$index['index'].($i+1).'-select.png';
////              $image['unselect'] = '/Public/Image/Material/'.$index['index'].($i+1).'-unselect.png';
//              $data['background'] = serialize(array('select'=>'/Public/Image/Material/'.$index['index'].($i+1).'-select.png',
//                                                    'unselect'=>'/Public/Image/Material/'.$index['index'].($i+1).'-unselect.png'));
//              $data['cTime'] = time();
//              $back->where(array('tid'=>($i+1)))->data($data)->save();
//          }
//      }

//    public function background1()
//    {
//        $back = M('change_face_type');
//        $image['select'] = '/Public/Image/Material/moreScene/world/world-select.png';
//        $image['unselect'] = '/Public/Image/Material/moreScene/world/world-unselect.png';
//        $data['background'] = serialize($image);
//            $data['cTime'] = time();
//            $back->where(array('tid'=>6))->data($data)->save();
//    }
//
//    public function jiekai()
//    {
//        $hah = 'a:2:{s:6:"select";s:55:"/Public/Image/Material/moreScene/cloud/cloud-select.png";s:8:"unselect";s:57:"/Public/Image/Material/moreScene/cloud/cloud-unselect.png";}';
//        var_dump(unserialize($hah));
//    }
//
//    public function test_hd()
//    {
//        $hd = M('decorations_hd');
//        for($i=0;$i < 30;$i++)
//        {
//            $data['cTime'] = time();
//            $data['type'] = 2;
//            $data['url'] = C('__HEAD_DECORATIONS__').'199/'.($i+1).'.png';
//            if($i > 4)
//            {
//                $data['isDel'] = 1;
//            }
//            $hd->data($data)->add();
//            unset($data);
//        }
//    }
//
//
//    public function test_md()
//    {
//        set_time_limit(0);
//        $md = M('decorations_model');
//        for($i=60;$i < 90;$i++)
//        {
//            for($j=60;$j<90;$j++)
//            {
//                $data['hd_id'] = ($i+1);
//                $data['bd_id'] = ($j+1);
//                $data['cTime'] = time();
//                $data['murl'] = C('__MODEL_DECORATIONS__').($i+1).'-'.($j+1).'.png';
//                $data['big_url'] =  C('__MODEL_BIG_DECORATIONS__').($i+1).'-'.($j+1).'.png';
//                if($i > 64 || $j > 64)
//                {
//                    $data['isDel'] = 1;
//                }
//                $md->data($data)->add();
//                unset($data);
//            }
//        }
//    }
//
//    function addPorp()
//    {
//        $md = M('change_face_img');
//        for($i=0;$i<62;$i++)
//        {
//            $data['tid'] = 7;
//            $data['filename'] = ($i+1).'.png';
//            $data['url'] = '/Public/Image/Material/prop/bubble/'.($i+1).'.png';
//            $data['count'] = 0;
//            $data['updateTime'] = time();
//            $md->data($data)->add();
//        }
//    }
} 