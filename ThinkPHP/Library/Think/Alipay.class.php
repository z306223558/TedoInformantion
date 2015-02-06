<?php
namespace Think;
// +----------------------------------------------------------------------
// | ThinkPHP Alipay 支付宝 扩展类 默认担保交易
// +----------------------------------------------------------------------
// | 本接口诞生目的是将支付宝与用ThinkPHP写的项目进行订单的付款和发货信息同步对接
// +----------------------------------------------------------------------
// | 没有什么技术含量，只是将支付宝本身的接口中独立的类和函数全部调整到本类中
// +----------------------------------------------------------------------
// | 所以要问我与支付宝本身的接口有什么不同的话，那我只能说，就是独立的交叉引用类和一个整合类的区别而已，甚至绝大部分注释都是原接口的，JUST COPY AND PASTE！
// | 只有，第508行，是基于项目实际经验的修改，修改说明看 507行 注释，本着低碳环保的精神，这里不重复
// +----------------------------------------------------------------------
// | Author: lttest <不要来找我@天朝.com>
// +----------------------------------------------------------------------
// $Version$  V0.01    2012-03-10
class Alipay {
    protected $alipay_config = array(
        //合作身份者id，以2088开头的16位纯数字
        'partner'     => '8888888888888888',
        //安全检验码，以数字和字母组成的32位字符
        'key'         => 'zctx3n19s2l1cdrwbsb5n9dbccdou52o',
        //签约支付宝账号或卖家支付宝帐户
        'seller_email'=> 'xxxxxxxx@hotmail.com',
        //页面跳转同步通知页面路径，要用 http://格式的完整路径，不允许加?id=123这类自定义参数
        //return_url的域名不能写成http://localhost/trade_create_by_buyer_php_utf8/return_url.php ，否则会导致return_url执行无效
        'return_url'  => 'http://127.0.0.1/ali/return_url.php',
        //服务器异步通知页面路径，要用 http://格式的完整路径，不允许加?id=123这类自定义参数
        'notify_url'  => 'http://www.xxx.com/trade_create_by_buyer_php_utf8/notify_url.php',
        //签名方式 不需修改
        'sign_type'   => 'MD5',
        //字符编码格式 目前支持 gbk 或 utf-8
        'input_charset'=>'utf-8',
        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        'transport'   => 'https'
    );

    /*
    /=**************************请求参数*************************=/
    //必填参数//

    $out_trade_no		= date('Ymdhis');		//请与贵网站订单系统中的唯一订单号匹配
    $subject			= $_POST['subject'];	//订单名称，显示在支付宝收银台里的“商品名称”里，显示在支付宝的交易管理的“商品名称”的列表里。
    $body				= $_POST['alibody'];	//订单描述、订单详细、订单备注，显示在支付宝收银台里的“商品描述”里
    $price				= $_POST['total_fee'];	//订单总金额，显示在支付宝收银台里的“应付总额”里

    $logistics_fee		= "0.00";				//物流费用，即运费。
    $logistics_type		= "EXPRESS";			//物流类型，三个值可选：EXPRESS（快递）、POST（平邮）、EMS（EMS）
    $logistics_payment	= "SELLER_PAY";			//物流支付方式，两个值可选：SELLER_PAY（卖家承担运费）、BUYER_PAY（买家承担运费）

    $quantity			= "1";					//商品数量，建议默认为1，不改变值，把一次交易看成是一次下订单而非购买一件商品。

    //选填参数//

    //买家收货信息（推荐作为必填）
    //该功能作用在于买家已经在商户网站的下单流程中填过一次收货信息，而不需要买家在支付宝的付款流程中再次填写收货信息。
    //若要使用该功能，请至少保证receive_name、receive_address有值
    //收货信息格式请严格按照姓名、地址、邮编、电话、手机的格式填写
    $receive_name		= "收货人姓名";			//收货人姓名，如：张三
    $receive_address	= "收货人地址";			//收货人地址，如：XX省XXX市XXX区XXX路XXX小区XXX栋XXX单元XXX号
    $receive_zip		= "123456";				//收货人邮编，如：123456
    $receive_phone		= "0571-81234567";		//收货人电话号码，如：0571-81234567
    $receive_mobile		= "13312341234";		//收货人手机号码，如：13312341234

    //网站商品的展示地址，不允许加?id=123这类自定义参数
    $show_url			= "http://www.xxx.com/myorder.php";
    */
    protected $parameter = array(
        "service"		=> "create_partner_trade_by_buyer",
    	"payment_type"	=> "1"
    );
    //支付宝网关地址(新) 2012-03-10 更新
    protected $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';
    //HTTPS形式消息验证地址
    protected $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
    //HTTP形式消息验证地址
    protected $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';

    public function __construct($config){
        if( is_array($config)){
            foreach( $config as $key=>$val){
                $this->setConfig($key,$val);
            }
        }
        $aliapy_config = $this->alipay_config;
        $p = array(
        		"partner"		=> trim($aliapy_config['partner']),
        		"_input_charset"=> trim(strtolower($aliapy_config['input_charset'])),
        		"seller_email"	=> trim($aliapy_config['seller_email']),
        		"return_url"	=> trim($aliapy_config['return_url']),
        		"notify_url"	=> trim($aliapy_config['notify_url']),

        		"out_trade_no"	=> date('Ymdhis'),
        		"subject"		=> '订单名称',
        		"body"			=> '订单描述',
        		"price"			=> '订单金额',
        		"quantity"		=> '1',

        		"logistics_fee"		=> '0.00',
        		"logistics_type"	=> 'EXPRESS',
        		"logistics_payment"	=> 'BUYER_PAY',

        		"receive_name"		=> '收货人姓名',
        		"receive_address"	=> '收货人地址',
        		"receive_zip"		=> '123456',
        		"receive_phone"		=> '0571-81234567',
        		"receive_mobile"	=> '13312341234',

        		"show_url"		=> 'http://'.$_SERVER['HTTP_HOST']
        );
        $this->parameter = array_merge($this->parameter,$p);
    }

    /**
     * 支付宝接口调度
     * 默认担保交易接口
     */
    function do_pay($service){
        switch( $service)
        {
            case 1 :
                $this->setPara('service','trade_create_by_buyer');
                break;
            default :
                //$this->setPara('service','create_partner_trade_by_buyer');
        }
        $para_temp = $this->parameter;
        $button_name = "确认";
      		//生成表单提交HTML文本信息
      	$html_text = $this->buildForm($para_temp, $this->alipay_gateway_new, "get", $button_name,$this->alipay_config);

      	return $html_text;
    }

    /**
     * 支付宝确认发货接口
     * ===============
     * @return 获取支付宝的返回XML处理结果
     */
    function do_sendgoods(){
        $p = array(
            //支付宝交易号。它是登陆支付宝网站在交易管理中查询得到，一般以8位日期开头的纯数字（如：20100419XXXXXXXXXX）
        	"trade_no"			=> $_POST['trade_no'],
            //物流公司名称
        	"logistics_name"	=> $_POST['logistics_name'],
            //物流发货单号
        	"invoice_no"		=> $_POST['invoice_no'],
            //物流发货时的运输类型，三个值可选：POST（平邮）、EXPRESS（快递）、EMS（EMS）
        	"transport_type"	=> $_POST['transport_type']
        );
        $this->parameter = array_merge($this->parameter,$p);
        $this->setPara('service','send_goods_confirm_by_platform');
        $html_text = $this->sendPostInfo($this->parameter, $this->alipay_gateway_new, $this->alipay_config);
        return $html_text;
    }

    /**
     * 设置支付宝CONFIG参数
     * @param $name
     * @param $value
     */
    function setConfig($name,$value) {
        if(isset($this->alipay_config[$name])) {
            $this->alipay_config[$name] = $value;
        }
    }

    /**
     * 设置支付宝表单数据
     * @param $name
     * @param $value
     */
    function setPara($name,$value) {
        if(isset($this->parameter[$name])) {
            $this->parameter[$name] = $value;
        }
    }

    /**
     * 构造标准双接口
     * @param $para_temp 请求参数数组
     * @return 表单提交HTML信息
     */
   	function trade_create_by_buyer($para_temp) {
   		//设置按钮名称
   		$button_name = "确认";
   		//生成表单提交HTML文本信息
   		$html_text = $this->buildForm($para_temp, $this->alipay_gateway_new, "get", $button_name,$this->alipay_config);

   		return $html_text;
   	}

    /**
     * 构造担保交易接口
     * @param $para_temp 请求参数数组
     * @return 表单提交HTML信息
     */
   	function create_partner_trade_by_buyer($para_temp) {
   		//设置按钮名称
   		$button_name = "确认";
   		//生成表单提交HTML文本信息
   		$html_text = $this->buildForm($para_temp, $this->alipay_gateway_new, "get", $button_name,$this->alipay_config);

   		return $html_text;
   	}

    /**
     * 构造确认发货接口
     * @param $para_temp 请求参数数组
     * @return 获取支付宝的返回XML处理结果
     */
   	function send_goods_confirm_by_platform($para_temp) {

   		//获取支付宝的返回XML处理结果

   	}

    /**
     * 用于防钓鱼，调用接口query_timestamp来获取时间戳的处理函数
   	 * 注意：该功能PHP5环境及以上支持，因此必须服务器、本地电脑中装有支持DOMDocument、SSL的PHP配置环境。建议本地调试时使用PHP开发软件
     * return 时间戳字符串
   	 */
   	function query_timestamp() {
   		$url = $this->alipay_gateway_new."service=query_timestamp&partner=".trim($this->alipay_config['partner']);
   		$encrypt_key = "";

   		$doc = new DOMDocument();
   		$doc->load($url);
   		$itemEncrypt_key = $doc->getElementsByTagName( "encrypt_key" );
   		$encrypt_key = $itemEncrypt_key->item(0)->nodeValue;

   		return $encrypt_key;
   	}

    /**
     * 构造支付宝其他接口
     * @param $para_temp 请求参数数组
     * @return 表单提交HTML信息/支付宝返回XML处理结果
     */
   	function alipay_interface($para_temp) {
   		//获取远程数据
   		$alipaySubmit = new AlipaySubmit();
   		$html_text = "";
   		//请根据不同的接口特性，选择一种请求方式
   		//1.构造表单提交HTML数据:（$method可赋值为get或post）
   		//$alipaySubmit->buildForm($para_temp, $this->alipay_gateway, "get", $button_name,$this->aliapy_config);
   		//2.构造模拟远程HTTP的POST请求，获取支付宝的返回XML处理结果:
   		//注意：若要使用远程HTTP获取数据，必须开通SSL服务，该服务请找到php.ini配置文件设置开启，建议与您的网络管理员联系解决。
   		//$alipaySubmit->sendPostInfo($para_temp, $this->alipay_gateway, $this->aliapy_config);
   		return $html_text;
   	}

    /**
     * 构造提交表单HTML数据
     * @param $para_temp 请求参数数组
     * @param $gateway 网关地址
     * @param $method 提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
     * @return 提交表单HTML文本
     */
	function buildForm($para_temp, $gateway, $method, $button_name, $aliapy_config) {
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp,$aliapy_config);

		$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$gateway."_input_charset=".trim(strtolower($aliapy_config['input_charset']))."' method='".$method."'>";
		while (list ($key, $val) = each ($para)) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }

		//submit按钮控件请不要含有name属性
        $sHtml = $sHtml."<input type='submit' value='".$button_name."'></form>";

		$sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";

		return $sHtml;
	}

    /**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @param $aliapy_config 基本配置信息数组
     * @return 要请求的参数数组
     */
   	function buildRequestPara($para_temp,$aliapy_config) {
   		//除去待签名参数数组中的空值和签名参数
   		$para_filter = $this->paraFilter($para_temp);

   		//对待签名参数数组排序
   		$para_sort = $this->argSort($para_filter);

   		//生成签名结果
   		$mysign = $this->buildMysign($para_sort, trim($aliapy_config['key']), strtoupper(trim($aliapy_config['sign_type'])));

   		//签名结果与签名方式加入请求提交参数组中
   		$para_sort['sign'] = $mysign;
   		$para_sort['sign_type'] = strtoupper(trim($aliapy_config['sign_type']));

   		return $para_sort;
   	}

    /**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
   	 * @param $aliapy_config 基本配置信息数组
     * @return 要请求的参数数组字符串
     */
   	function buildRequestParaToString($para_temp,$aliapy_config) {
   		//待请求参数数组
   		$para = $this->buildRequestPara($para_temp,$aliapy_config);
   		//把参数组中所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对参数值做urlencode编码
   		$request_data = $this->createLinkstringUrlencode($para);
   		return $request_data;
   	}

    /**
     * 构造模拟远程HTTP的POST请求，获取支付宝的返回XML处理结果
   	 * 注意：该功能PHP5环境及以上支持，因此必须服务器、本地电脑中装有支持DOMDocument、SSL的PHP配置环境。建议本地调试时使用PHP开发软件
     * @param $para_temp 请求参数数组
     * @param $gateway 网关地址
   	 * @param $aliapy_config 基本配置信息数组
     * @return 支付宝返回XML处理结果
     */
   	function sendPostInfo($para_temp, $gateway, $aliapy_config) {
   		$xml_str = '';

   		//待请求参数数组字符串
   		$request_data = $this->buildRequestParaToString($para_temp,$aliapy_config);
   		//请求的url完整链接
   		$url = $gateway . $request_data;
   		//远程获取数据
   		$xml_data = $this->getHttpResponse($url,trim(strtolower($aliapy_config['input_charset'])));
   		//解析XML
   		$doc = new DOMDocument();
   		$doc->loadXML($xml_data);

   		return $doc;
   	}


    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
    function verifyNotify(){
   		if(empty($_POST)) {//判断POST来的数组是否为空
   			return false;
   		}
   		else {
   			//生成签名结果
   			$mysign = $this->getMysign($_POST);
   			//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
   			$responseTxt = 'true';
   			if (! empty($_POST["notify_id"])) {$responseTxt = $this->getResponse($_POST["notify_id"]);}

   			//写日志记录
   			//$log_text = "responseTxt=".$responseTxt."\n notify_url_log:sign=".$_POST["sign"]."&mysign=".$mysign.",";
   			//$log_text = $log_text.createLinkString($_POST);
   			//logResult($log_text);

   			//验证
   			//$responseTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
   			//mysign与sign不等，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
   			if (preg_match("/true$/i",$responseTxt) && $mysign == $_POST["sign"]) {
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
		if(empty($_GET)) {//判断POST来的数组是否为空
			return false;
		}
		else {
			//生成签名结果
			$mysign = $this->getMysign($_GET);
			//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
			$responseTxt = 'true';
			if (! empty($_GET["notify_id"])) $responseTxt = $this->getResponse($_GET["notify_id"]);

			//写日志记录
			//$log_text = "responseTxt=".$responseTxt."\n notify_url_log:sign=".$_GET["sign"]."&mysign=".$mysign.",";
			//$log_text = $log_text.$this->createLinkString($_GET);
			//$this->logResult($log_text);

			//验证
			//$responseTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
			//mysign与sign不等，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
			if (preg_match("/true$/i",$responseTxt) && $mysign == $_GET["sign"]) {
				return true;
			} else {
				return false;
			}
		}
	}

    /**
     * 根据反馈回来的信息，生成签名结果
     * @param $para_temp 通知返回来的参数数组
     * @return 生成的签名结果
     */
	function getMysign($para_temp) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = $this->paraFilter($para_temp);

		//对待签名参数数组排序
		$para_sort = $this->argSort($para_filter);

		//生成签名结果
		$mysign = $this->buildMysign($para_sort, trim($this->alipay_config['key']), strtoupper(trim($this->alipay_config['sign_type'])));

		return $mysign;
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
	function getResponse($notify_id) {
		$transport = strtolower(trim($this->alipay_config['transport']));
		$partner = trim($this->alipay_config['partner']);
		$veryfy_url = '';
		if($transport == 'https') {
			$veryfy_url = $this->https_verify_url;
		}
		else {
			$veryfy_url = $this->http_verify_url;
		}
		$veryfy_url = $veryfy_url.'partner=' . $partner . '&notify_id=' . $notify_id;
		$responseTxt = $this->getHttpResponse($veryfy_url);

		return $responseTxt;
	}





    /**
     * 生成签名结果
     * @param $sort_para 要签名的数组
     * @param $key 支付宝交易安全校验码
     * @param $sign_type 签名类型 默认值：MD5
     * return 签名结果字符串
     */
    function buildMysign($sort_para,$key,$sign_type = "MD5") {
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($sort_para);
        //把拼接后的字符串再与安全校验码直接连接起来
        $prestr = $prestr.$key;
        //把最终的字符串签名，获得签名结果
        $mysgin = $this->sign($prestr,$sign_type);
        return $mysgin;
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
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    function createLinkstringUrlencode($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key."=".urlencode($val)."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);

        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}

        return $arg;
    }
    /**
     * 除去数组中的空值和签名参数
     * @param $para 签名参数组
     * return 去掉空值与签名参数后的新签名参数组
     */
    function paraFilter($para) {
        $para_filter = array();
        while (list ($key, $val) = each ($para)) {
			//2013-02-19,由于TP开启路由后添加了 _URL_ 的请求参数导致 ALI 签名认证出错，这里添加排除
            if($key == "sign" || $key == "sign_type" || $key == '_URL_' || $val == "")continue;
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
     * 签名字符串
     * @param $prestr 需要签名的字符串
     * @param $sign_type 签名类型 默认值：MD5
     * return 签名结果
     */
    function sign($prestr,$sign_type='MD5') {
        $sign='';
        if($sign_type == 'MD5') {
            $sign = md5($prestr);
        }elseif($sign_type =='DSA') {
            //DSA 签名方法待后续开发
            die("DSA 签名方法待后续开发，请先使用MD5签名方式");
        }else {
            die("支付宝暂不支持".$sign_type."类型的签名方式");
        }
        return $sign;
    }
    /**
     * 写日志，方便测试（看网站需求，也可以改成把记录存入数据库）
     * 注意：服务器需要开通fopen配置
     * @param $word 要写入日志里的文本内容 默认值：空值
     */
    function logResult($word='') {
        $fp = fopen("log.txt","a");
        flock($fp, LOCK_EX) ;
        fwrite($fp,"执行日期：".strftime("%Y%m%d%H%M%S",time())."\n".$word."\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * 远程获取数据
     * 注意：该函数的功能可以用curl来实现和代替。curl需自行编写。
     * $url 指定URL完整路径地址
     * @param $input_charset 编码格式。默认值：空值
     * @param $time_out 超时时间。默认值：60
     * return 远程输出的数据
     */
    function getHttpResponse($url, $input_charset = '', $time_out = "60") {
        $urlarr     = parse_url($url);
        $errno      = "";
        $errstr     = "";
        $transports = "";
        $responseText = "";
        if($urlarr["scheme"] == "https") {
            $transports = "ssl://";
            $urlarr["port"] = "443";
        } else {
            $transports = "tcp://";
            $urlarr["port"] = "80";
        }
        $fp=@fsockopen($transports . $urlarr['host'],$urlarr['port'],$errno,$errstr,$time_out);
        if(!$fp) {
            die("ERROR: $errno - $errstr<br />\n");
        } else {
            if (trim($input_charset) == '') {
                fputs($fp, "POST ".$urlarr["path"]." HTTP/1.1\r\n");
            }
            else {
                fputs($fp, "POST ".$urlarr["path"].'?_input_charset='.$input_charset." HTTP/1.1\r\n");
            }
            fputs($fp, "Host: ".$urlarr["host"]."\r\n");
            fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
            fputs($fp, "Content-length: ".strlen($urlarr["query"])."\r\n");
            fputs($fp, "Connection: close\r\n\r\n");
            fputs($fp, $urlarr["query"] . "\r\n\r\n");
            while(!feof($fp)) {
                $responseText .= @fgets($fp, 1024);
            }
            fclose($fp);
            $responseText = trim(stristr($responseText,"\r\n\r\n"),"\r\n");

            return $responseText;
        }
    }
    /**
     * 实现多种字符编码方式
     * @param $input 需要编码的字符串
     * @param $_output_charset 输出的编码格式
     * @param $_input_charset 输入的编码格式
     * return 编码后的字符串
     */
    function charsetEncode($input,$_output_charset ,$_input_charset) {
        $output = "";
        if(!isset($_output_charset) )$_output_charset  = $_input_charset;
        if($_input_charset == $_output_charset || $input ==null ) {
            $output = $input;
        } elseif (function_exists("mb_convert_encoding")) {
            $output = mb_convert_encoding($input,$_output_charset,$_input_charset);
        } elseif(function_exists("iconv")) {
            $output = iconv($_input_charset,$_output_charset,$input);
        } else die("sorry, you have no libs support for charset change.");
        return $output;
    }
    /**
     * 实现多种字符解码方式
     * @param $input 需要解码的字符串
     * @param $_output_charset 输出的解码格式
     * @param $_input_charset 输入的解码格式
     * return 解码后的字符串
     */
    function charsetDecode($input,$_input_charset ,$_output_charset) {
        $output = "";
        if(!isset($_input_charset) )$_input_charset  = $_input_charset ;
        if($_input_charset == $_output_charset || $input ==null ) {
            $output = $input;
        } elseif (function_exists("mb_convert_encoding")) {
            $output = mb_convert_encoding($input,$_output_charset,$_input_charset);
        } elseif(function_exists("iconv")) {
            $output = iconv($_input_charset,$_output_charset,$input);
        } else die("sorry, you have no libs support for charset changes.");
        return $output;
    }
}