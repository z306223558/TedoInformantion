<?php
namespace Shop\Controller;
use Shop\Model\OrderModel;
use ZipArchive;
use Think\Controller;
class OrderController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new OrderModel();
    }

    /**
     * 检测登陆状态
     * @return bool
     */
    public function islogin(){
        if(empty($_SESSION['user'])){
            $this->redirect("index/login");
            return false;
        }
    }

    /**
     * 分页浏览订单信息
     */
    public function orderInfo()
    {
        $this->islogin();
        $type = I("type");
        $total =intval($this->model->getOrderTotal($type));
        $page = new \Think\Page($total,10);
        $list = $this->model->getOrderInfoByPage($page->firstRow,$page->listRows,$type);
        $data = array();
        $status = C('orderStatus');
        foreach($list as $k =>$v)
        {
            $data[$k]['id'] = $v['orderId'];
            $data[$k]['num'] = $v['order_num'];
            $data[$k]['cTime'] = $v['cTime'];
            $data[$k]['status'] = $status[$v['status']];
            $data[$k]['status_time'] = empty($v['status_time']) ? $v['cTime'] : $v['status_time'];
            $data[$k]['posted'] = $v['posted'];
            $post_info = $this->model->getPostedNum($v['orderId']);
            $data[$k]['num_info'] = empty($post_info['num_info']) ? (empty($v['posted']) ? '': '<b style="color: #ff0000">订单已发货，暂无发货单号！请尽快处理</b>') : $post_info['num_info'];
            $data[$k]['class_type'] = (int)($k%2);
            $data[$k]['would_status'] = $status[$this->orderStatus($v['cTime'])];
            $data[$k]['last_time'] = $this->status_time($v['cTime']);
        }
        $show = $page->show();

        $this->assign("page",$show);
        $this->assign('data',$data);
        $this->display("orderlist");
    }

    /**
     * 发货，添加快递单号
     */
    public function addPostNum()
    {
        $this->islogin();
        $type = intval(I('type'));
        $orderId = intval(I('orderId'));
        $post = intval(I('post'));
        if(empty($orderId) || empty($post))
        {
            echo 500;exit;
        }
        $orderIno = $this->model->getOrderInfoByCond($orderId);
        if(empty($orderIno))
        {
            echo 500;exit;
        }
        if(empty($type))
        {
            $data['uid'] = $orderIno['uid'];
            $data['postId'] = $orderIno['postId'];
            $data['orderId'] = $orderIno['orderId'];
        }
        $data['num_info'] = $post;
        $data['cTime'] = time();
        $this->model->startTrans();
        $post_info = $this->model->setPostNumInfo($data,$type,$orderIno['orderId']);
        $update_order = 1;
        if(empty($type))
        {
            $update_order = $this->model->updateOrderPostInfo($orderIno['orderId']);
        }
        $this->model->commit();
        if(empty($post_info) || empty($update_order))
        {
            $this->model->rollback();
            echo 500;exit;
        }
        else
        {
            echo 200;exit;
        }
    }

    public function orderStatus($time)
    {
        //预计订单状态
        $would_time = time() - $time;
        $passDay = intval($would_time/(3600*24));
        switch ($passDay)
        {
            case 0 :
                $would_status = 1;
                break;
            case 1 :
            case 2 :
                $would_status = 2;
                break;
            case 3 :
            case 4 :
            case 5 :
            case 6 :
            case 7 :
            case 8 :
                $would_status = 3;
                break;
            case 9 :
            case 10:
                $would_status = 4;
                break;
            case 11 :
                $would_status = 7;
                break;
            default :
                $would_status = 7;
                break;
        }
        return $would_status;
    }

    /**
     * 状态剩余时间
     * @param $cTime
     *
     * @return string
     */
    public function status_time($cTime)
    {
        $status = $this->orderStatus($cTime);
        switch($status)
        {
            case 1:
                $last_time = $cTime + 24*3600 - time();
                break;
            case 2 :
                $last_time = $cTime + 3*24*3600 -time();
                break;
            case 3 :
                $last_time = $cTime + 8*24*3600 -time();
                break;
            case 4 :
                $last_time = $cTime + 10*24*3600 -time();
                break;
            default :
                $last_time = 0;
                break;
        }
        $date = $this->timeOperation($last_time);
        if($last_time === 0)
        {
            return $last_time = '<b style="color: blue;">无剩余时间</b>';
        }
        if(empty($date['day']) && ($date['hour'] < 12))
        {
            return $last_time = '<b style="color: red;">'.$date['hour'].'小时'.$date['min'].'分'.'</b>';
        }
        elseif(empty($date['day']) && (12 < $date['hour']))
        {
            return $last_time = '<b >'.$date['hour'].'小时'.$date['min'].'分'.'</b>';
        }
        return $last_time = '<b>'.$date['day'].'天 '.$date['hour'].'小时'.$date['min'].'分'.'</b>';
    }

    public function timeOperation($time)
    {
        if(empty($time))
        {
            return false;
        }
        $date['day'] = intval($time/(3600*24));
        $date['hour'] = intval(($time%(3600*24))/3600);
        $date['min'] = intval((($time%(3600*24))%3600)/60);
        return $date;
    }

    /**
     * 查询的订单详细信息展示
     */
    public function showOrderInfo()
    {
        $this->islogin();
        $info = I('id');
        $type = intval(I('type'));
        if($type == 3)
        {
            $this->assign('id',$info);
            $this->display('selectOrderInfo');
            exit;
        }
        if(empty($info))
        {
            $this->error('请提供要查询的订单信息');
        }
        if(is_numeric($info))
        {
            $orderIno = $this->model->getOrderInfoById($info);
        }else
        {
            $orderIno = $this->model->getOrderInfoById($info,true);
        }
        //获取购买人电话
        $mobile = $this->model->userMobile($orderIno['uid']);
        $orderIno['mobile'] = $mobile['mobile'];
        $userInfo = $this->model->getUserImageList($orderIno['uid']);
        $imageList = unserialize($userInfo['user_image']);
        $imageList['uid'] = $orderIno['uid'];
        $postInfo['posted_num'] = 0;
        $postInfo['posted_time'] = 0;
        if(!empty($orderIno))
        {
            $postInfo = $this->model->getPostInfoById($orderIno['postId']);
        }
        if(!empty($orderIno['posted']))
        {
            $post  = $this->model->getPostedNum($orderIno['orderId']);
            $postInfo['posted_num'] = $post['num_info'];
            $postInfo['posted_time'] = $post['cTime'];
        }
        //当前订单状态
        $status = C('orderStatus');
        $orderIno['status'] = $status[$orderIno['status']];
        $orderIno['would_status'] = $status[$this->orderStatus($orderIno['cTime'])];
        //订单商品数和种类数
        $orderIno['goods_count'] = 0;
        $orderIno['type_count'] = array();
        $size_count = 0;
        $goodsInfo = $this->model->getGoodsList(implode(',',explode('-',$orderIno['pid'])));
        foreach($goodsInfo as $key => $value)
        {
            $goodsInfo[$key]['hd_image'] = $this->model->getHdImage($value['hd_id']);
            $goodsInfo[$key]['bd_image'] = $this->model->getBdImage($value['bd_id']);
            if(empty($goodsInfo[$key]['size']))
            {
                $goodsInfo[$key]['size'] = '8cm';
            }elseif($goodsInfo[$key]['size'] == 1){
                $goodsInfo[$key]['size'] = '12cm';
            }else{
                $goodsInfo[$key]['size'] = '15cm';
            }
            $orderIno['goods_count']+=$value['num'];
            if(!in_array($value['size'],$orderIno['type_count']))
            {
                $orderIno['type_count'][] = $value['size'];
                $size_count++;
            }
        }
        $orderIno['total_type'] = $size_count;
        $this->assign("order",$orderIno);
        $this->assign('image',$imageList);
        $this->assign('post',$postInfo);
        $this->assign('good',$goodsInfo);
        $this->display("selectOrderInfo");
    }

    /**
     * 搜索订单信息
     */
    public function searchOrder()
    {
        $this->islogin();
        $info = trim(I('search'));
        $type = trim(I('type'));
        $total =intval($this->model->getSearchOrderTotal($info,$type));
        $page = new \Think\Page($total,10);
        $list = $this->model->getSearchOrder($info,$type,$page->firstRow,$page->listRows);
        $data = array();
        $status = C('orderStatus');
        foreach($list as $k =>$v)
        {
            $data[$k]['id'] = $v['orderId'];
            $data[$k]['num'] = $v['order_num'];
            $data[$k]['cTime'] = $v['cTime'];
            $data[$k]['status'] = $status[$v['status']];
            $data[$k]['status_time'] = empty($v['status_time']) ? '':date('H-m-d h:i',$v['status_time']);
            $data[$k]['posted'] = $v['posted'];
            $post_info = $this->model->getPostedNum($v['orderId']);
            $data[$k]['num_info'] = empty($post_info['num_info']) ? (empty($v['posted']) ? '': '<b style="color: #ff0000">订单已发货，暂无发货单号！请尽快处理</b>') : $post_info['num_info'];
            $data[$k]['class_type'] = (int)($k%2);
        }
        $show = $page->show();

        $this->assign("page",$show);
        $this->assign('data',$data);
        $this->display("orderlist");
    }

    public function returnOrder()
    {
        $this->islogin();
        $type = intval(I('type'));
        $id = intval(I('id'));
        if($type ==3)
        {
            $this->assign('id',$id);
            $this->display('return');
            exit;
        }
        $backInfo = $this->model->getBackInfo($id);
        if(empty($backInfo))
        {
            $this->error('没有该订单的退货信息');
        }
        $orderInfo = $this->model->getOrderInfoById($id);
        $backInfo[0]['orderId'] = $orderInfo['orderId'];
        $backInfo[0]['order_num'] = $orderInfo['order_num'];
        $backInfo[0]['return_good'] = explode('-',$backInfo[0]['return_good']);
        $pids = implode(',',explode('-',$orderInfo['pid']));
        $goodsInfo = $this->model->getGoodsListByPids($pids);
        foreach($goodsInfo as $key => $value)
        {
            $goodsInfo[$key]['hd_image'] = $this->model->getHdImage($value['hd_id']);
            $goodsInfo[$key]['bd_image'] = $this->model->getBdImage($value['bd_id']);
            if(in_array($value['pid'],$backInfo[0]['return_good']))
            {
                $goodsInfo[$key]['return_good'] = 1;
            }else
            {
                $goodsInfo[$key]['return_good'] = 0;
            }
        }
        $postInfo = $this->model->getPostInfoById($orderInfo['postId']);
        $backInfo[0]['pName'] = $postInfo['pName'];
        $backInfo[0]['addr'] = $postInfo['province'].$postInfo['city'].$postInfo['zone'].$postInfo['addr'];

        $this->assign('data',$backInfo[0]);
        $this->assign('good',$goodsInfo);
        $this->display('return');
    }

    public function confirmReturn()
    {
        if(!IS_POST)  {echo 500;exit;}
        $bid = intval(I('bid'));
        $pid = intval(I('pid'));
        if(empty($bid) || empty($pid))
        {
            echo 500;
            exit;
        }
        $result = $this->model->getBackInfo($bid);
        if(empty($result))
        {
            echo 500;
            exit;
        }
        $pids = explode('-',$result['return_good']);
        if(array_search($pid,$pids) === false)
        {
            echo 500;
            exit;
        }
        else
        {
            $data['cTime'] = time();
            unset($pids[array_search($pid,$pids)]);
            if(empty($pids))
            {

                if($this->model->updateReturnOrderInfo($bid,$data,$result['orderId'],$result['return_type']))
                {
                    echo 200;
                    exit;
                }
                else
                {
                    echo 500;
                    exit;
                }
            }
            $data['return_good'] = implode(',',$pids);
            if($this->model->updateReturnOrderInfo($bid,$data,$result['orderId'],$result['return_type']))
            {
                echo 200;
                exit;
            }
            else
            {
                echo 500;
                exit;
            }
        }
    }

    /**
     * ajax调用改变订单状态
     */
    public function changeStatus()
    {
        if(!IS_POST)  {echo 500;exit;}
        $orderId = intval(I('orderId'));
        $status = intval(I('status'));
        if(empty($orderId)||empty($status))
        {
            echo 500;exit;
        }
        $res = $this->model->updateOrderStatus($status,$orderId);
        if(empty($res))
        {
            echo 500;exit;
        }
        else
        {
            echo 200;exit;
        }
    }

    /**
     * 打包文件并下载
     */
    public function packageZip()
    {
        if(!IS_GET) {echo 500;exit;}
        $orderId = intval(I('orderId'));
        if(empty($orderId))
        {
            echo 500;exit;
        }
        // 打包下载
        $path = getcwd();
        $savePath = $path.'/Public/files/Download';
        if(is_dir($savePath))
        {
            if(!$this->delDirAndFile($savePath))
            {
                $this->error('删除原文件失败');
            }
        }
        mkdir($savePath);
        $saveNmae = C('saveName');
        $order = $this->model->getOrderInfoById($orderId);
        //商品图片
        $goodsInfo = $this->model->getGoodsListByPids(implode(',',explode('-',$order['pid'])));
        //用户图片
        $UI = $this->model->getUserImageList($order['uid']);
        $userImage = unserialize($UI['user_image']);
        foreach($goodsInfo as $k=>$v)
        {
            mkdir($savePath.'/'.$v['pid']);
            if(is_dir($savePath.'/'.$v['pid']))
            {
                copy($path.$v['user_model'],$savePath.'/'.$v['pid'].'/user_good.jpg');
            }
            foreach($userImage as $key=>$value)
            {
                copy($path.$value,$savePath.'/'.$v['pid'].'/'.$saveNmae[$key]);
            }
        }
        $zip = new ZipArchive();
        if($zip->open($savePath.'/'.$orderId.'_'.$order['order_num'].'.zip', ZipArchive::OVERWRITE)=== TRUE)
        {
            $this->dirRead($savePath,$zip);
            $zip->close();
        }

        $scandir = new download($orderId.'_'.$order['order_num'].'.zip',$savePath.'/'); //$save_path zip包文件目录
        $scandir->getfiles();
        //unlink($savePath.'/');
    }

    /**
     * 获取目录下的所有文件
     * @param $dir
     * @param $zip
     */
    public function dirRead($dir,$zip)
    {
        $handler=opendir($dir); //打开当前文件夹由$path指定。
        while(($filename=readdir($handler))!==false){
            if($filename != "." && $filename != ".."){//文件夹文件名字为'.'和‘..’，不要对他们进行操作
                if(is_dir($dir."/".$filename)){// 如果读取的某个对象是文件夹，则递归
                    $this->dirRead($dir."/".$filename, $zip);
                }else{ //将文件加入zip对象
                    $zip->addFile($dir."/".$filename);
                }
            }
        }
        @closedir($dir);
    }

    /**
     * 删除目录及文件
     * @param $dirName
     *
     * @return bool
     */
    public function delDirAndFile( $dirName )
    {
        if ( $handle = opendir( "$dirName" ) ) {
            while ( false !== ( $item = readdir( $handle ) ) ) {
                if ( $item != "." && $item != ".." ) {
                    if ( is_dir( $dirName."/".$item ) ) {
                        $this->delDirAndFile( $dirName."/".$item );
                    } else {
                        if( unlink( $dirName."/".$item ));
                        {
                            continue;
                        }
                    }
                }
            }
            closedir( $handle );
            if(rmdir($dirName))
            return true;
        }
    }

}

/**
 * 下载文件
 *
 */
class download{
    protected $_filename;
    protected $_filepath;
    protected $_filesize;//文件大小
    protected $savepath;//文件大小
    public function __construct($filename,$savepath){
        $this->_filename=$filename;
        $this->_filepath=$savepath.$filename;
    }
    //获取文件名
    public function getfilename(){
        return $this->_filename;
    }
    //获取文件路径（包含文件名）
    public function getfilepath(){
        return $this->_filepath;
    }
    //获取文件大小
    public function getfilesize(){
        return $this->_filesize=number_format(filesize($this->_filepath)/(1024*1024),2);//去小数点后两位
    }
    //下载文件的功能
    public function getfiles(){
        //检查文件是否存在
        if (file_exists($this->_filepath)){
            //打开文件
            $file = fopen($this->_filepath,"r");
            //返回的文件类型
            Header("Content-type: application/octet-stream");
            //按照字节大小返回
            Header("Accept-Ranges: bytes");
            //返回文件的大小
            Header("Accept-Length: ".filesize($this->_filepath));
            //这里对客户端的弹出对话框，对应的文件名
            Header("Content-Disposition: attachment; filename=".$this->_filename);
            //修改之前，一次性将数据传输给客户端
            echo fread($file, filesize($this->_filepath));
            //修改之后，一次只传输1024个字节的数据给客户端
            //向客户端回送数据
            $buffer=1024;//
            //判断文件是否读完
            while (!feof($file)) {
                //将文件读入内存
                $file_data=fread($file,$buffer);
                //每次向客户端回送1024个字节的数据
                echo $file_data;
            }
            fclose($file);
        }else {
            echo "<script>alert('对不起,您要下载的文件不存在');</script>";
        }
    }
}