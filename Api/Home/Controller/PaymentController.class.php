<?php
namespace Home\Controller;
use Home\Model\PaymentModel;
use Think\Controller;
class PaymentController extends Controller
{
    /**
     * HTTPS形式消息验证地址
     */
    var $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
    /**
     * HTTP形式消息验证地址
     */
    var $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';

    private $result = array();

    private $model = NULL;

    private $data = NULL;

    public function __construct()
    {
        parent::__construct();
        $this->result['status'] = 0;
        $this->result['message'] = '非法操作';
        $this->result['emessage'] = 'Illegal operation';
        $this->model = new PaymentModel();
    }

    public function index()
    {
        $this->R($this->result);
    }

    public function privateKaySign()
    {
        if(!IS_POST) $this->R($this->result);
        $signStr = $_POST['sign'];
        if(empty($signStr))
        {
            $this->result['message'] = '缺少必要信息';
            $this->result['emessage'] = 'The necessary information can not be empty';
            $this->R($this->result);
        }
        $private_key_path = C('alipay_config.private_key_path');
        $private_key_path = './Api/Home/Conf/'.$private_key_path;
        $sign = $this->rsaSign($signStr,$private_key_path);
        $this->result['status'] = 1;
        $this->result['message'] = 'OK';
        $this->result['emessage'] = 'OK';
        $this->result['sign'] = urlencode($sign);
        $this->R($this->result);
    }

    /**
     * RSA签名
     * @param $data 待签名数据
     * @param $private_key_path 商户私钥文件路径
     * return 签名结果
     */
    private function rsaSign($data, $private_key_path) {
        $priKey = file_get_contents($private_key_path);
        $res = openssl_get_privatekey($priKey);
        openssl_sign($data, $sign, $res);
        openssl_free_key($res);
        //base64编码
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * 异步通知页面
     */
    public function notify_url()
    {
        $verify_result = $this->verifyNotify();
        if(!empty($verify_result))
        {
            //验证成功
            //商城订单号
            $out_trade_no = $_POST['out_trade_no'];
            //支付宝交易号
            $trade_no = $_POST['trade_no'];
            //交易状态
            $trade_status = $_POST['trade_status'];
            if($trade_status == 'TRADE_SUCCESS')
            {
                //判断该笔订单是否在商户网站中已经做过处理，这表示订单的彻底完结
                //只存在一种情况，即交易完成。订单不可操作
                $res = $this->model->getOrderInfo($out_trade_no);
                if(empty($res))
                {
                    $this->logNotify(date('Y-m-d H:i',time())." 返回的订单号有误，数据库不存在该订单\n");
                }
                if(empty($res['pay_info']))
                {
                    $result = $this->model->updateOrderPayInfo($res['orderId'],$trade_no);
                }else
                {
                    $result = $this->model->updateOrderPayInfo($res['orderId'],$trade_no,1);
                }

                if(!empty($result))
                {
                    $this->logNotify(date('Y-m-d H:i',time())." 操作订单成功，订单号为：".$out_trade_no."\n");
                }
                else
                {
                    $this->logNotify(date('Y-m-d H:i',time())." 操作订单失败，订单号为：".$out_trade_no."\n");
                }
            }
            echo "success";
        }
        else
        {
            $this->logNotify(date('Y-m-d H:i',time())." 订单返回信息出错\n");
            echo "fail";
        }
    }

    /**
     * 即时支付返回接口
     */
    public function payReturn()
    {
        if(!IS_POST) $this->R($this->result);
        $status = intval(I('status'));
        $orderId = intval(I('orderId'));
        if(empty($orderId))
        {
            $this->result['message'] = '必要信息不能为空';
            $this->result['emessage'] = 'The necessary information can not be empty';
            $this->R($this->result);
        }
        $res = $this->model->getOrderInfoById($orderId);
        if(empty($res))
        {
            $this->result['message'] = '订单信息不存在';
            $this->result['emessage'] = 'The order information is not exsit';
            $this->R($this->result);
        }
        $result = $this->model->updateOrderPayStatus($res['orderId'],$status);
        if(empty($result))
        {
            $this->result['message'] = '更新信息失败';
            $this->result['emessage'] = 'Updata status fail';
            $this->R($this->result);
        }
        $this->logNotify(date('Y-m-d H:i',time())." 即时操作订单成功，订单号为：".$orderId."\n");
        $this->result['status'] = 1;
        $this->result['message'] = '支付成功';
        $this->result['emessage'] = 'pay success';
        $this->R($this->result);
    }

    private function verifyNotify()
    {
        if(empty($_POST))
        {
            return false;
        }
        else
        {
            //生成签名结果
            $isSign = $this->getSignVeryfy($_POST, $_POST["sign"]);
            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            $responseTxt = 'true';
            if (!empty($_POST["notify_id"]))
            {
                $responseTxt = $this->getResponse($_POST["notify_id"]);
            }
            //写日志记录
			if ($isSign)
            {
				$isSignStr = 'true';
			}
			else
            {
				$isSignStr = 'false';
			}
            if (preg_match("/true$/i",$responseTxt) && $isSignStr) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 针对return_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
    function verifyReturn(){
        if(empty($_GET))
        {//判断POST来的数组是否为空
            return false;
        }
        else
        {
            //生成签名结果
            $isSign = $this->getSignVeryfy($_GET, $_GET["sign"]);
            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            $responseTxt = 'true';
            if (! empty($_GET["notify_id"]))
            {
                $responseTxt = $this->getResponse($_GET["notify_id"]);
            }

            //写日志记录
            if ($isSign) {
                $isSignStr = 'true';
            }
            else {
                $isSignStr = 'false';
            }
            $log_text = "responseTxt=".$responseTxt."\n return_url_log:isSign=".$isSignStr.",";
            $log_text = $log_text.$this->createLinkString($_GET);
            $this->logResult($log_text);

            //验证
            //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
            //isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
            if (preg_match("/true$/i",$responseTxt) && $isSign) {
                return true;
            } else {
                return false;
            }
        }
    }

    private function getSignVeryfy($para_temp, $sign)
    {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);

        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_sort);

        $isSgin = false;
        switch (strtoupper(trim(C('alipay_config.sign_type'))))
        {
            case "RSA" :
                $isSgin = $this->rsaVerify($prestr, trim(C('alipay_config.ali_public_key_path')), $sign);
                break;
            default :
                $isSgin = false;
        }

        return $isSgin;
    }

    /**
     * 除去数组中的空值和签名参数
     * @param $para 签名参数组
     * return 去掉空值与签名参数后的新签名参数组
     */
     private function paraFilter($para) {
        $para_filter = array();
        while (list ($key, $val) = each ($para)) {
            if($key == "sign" || $key == "sign_type" || $val == "")continue;
            else	$para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }

    /**
     * 对数组排序
     * @param $para 排序前的数组
     * return 排序后的数组
     */
    function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    function createLinkstring($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key."=".$val."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);

        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}

        return $arg;
    }

    /**
     * RSA验签
     * @param $data 待签名数据
     * @param $ali_public_key_path 支付宝的公钥文件路径
     * @param $sign 要校对的的签名结果
     * return 验证结果
     */
    function rsaVerify($data, $ali_public_key_path, $sign)  {
        $pubKey = file_get_contents('./Api/Home/Conf/'.$ali_public_key_path);
        $res = openssl_get_publickey($pubKey);
        $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        openssl_free_key($res);
        return $result;
    }

    /**
     * 获取远程服务器ATN结果,验证返回URL
     * @param $notify_id 通知校验ID
     * @return 服务器ATN结果
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
     private function getResponse($notify_id) {
        $transport = strtolower(trim(C('alipay_config.transport')));
        $partner = trim(C('alipay_config.partner'));
        $veryfy_url = '';
        if($transport == 'https') {
            $veryfy_url = $this->https_verify_url;
        }
        else {
            $veryfy_url = $this->http_verify_url;
        }
        $veryfy_url = $veryfy_url."partner=" . $partner . "&notify_id=" . $notify_id;
        $responseTxt = $this->getHttpResponseGET($veryfy_url, C('alipay_config.cacert'));

        return $responseTxt;
    }

    /**
     * 远程获取数据，GET模式
     * 注意：
     * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
     * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
     * @param $url 指定URL完整路径地址
     * @param $cacert_url 指定当前工作目录绝对路径
     * return 远程输出的数据
     */
    function getHttpResponseGET($url,$cacert_url) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
        curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址
        $responseText = curl_exec($curl);
        //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);

        return $responseText;
    }

    /**
     * 写日志，方便测试（看网站需求，也可以改成把记录存入数据库）
     * 注意：服务器需要开通fopen配置
     * @param $word 要写入日志里的文本内容 默认值：空值
     */
    function logResult($word='') {
        $fp = fopen("./log.txt","a");
        flock($fp, LOCK_EX) ;
        fwrite($fp,"执行日期：".strftime("%Y%m%d%H%M%S",time())."\n".$word."\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    function logNotify($word='') {
        $fp = fopen("./log1.txt","a");
        flock($fp, LOCK_EX) ;
        fwrite($fp,"执行日期：".strftime("%Y%m%d%H%M%S",time())."\n".$word."\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}?>
