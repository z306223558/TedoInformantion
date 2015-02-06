<?php
return array(
	//'配置项'=>'配置值'
    '__PUBLIC__' => '/Public/Image/',

    '__HEAD_DECORATIONS__' => '/Public/Image/HeadDecorations/',
    '__BODY_DECORATIONS__' => '/Public/Image/BodyDecorations/',
    '__MODEL_DECORATIONS__' => '/Public/Image/ModelDecorations/',
    '__MODEL_BIG_DECORATIONS__' =>'/Public/Image/ModelDecorations/Big/',
    '__MATERIAL_IMG__' => '/Public/Image/Material/',

    'alipay_config' => array(
        //合作身份者id，以2088开头的16位纯数字
        'partner' => '2088612687344601',
        //商户的私钥（后缀是.pen）文件相对路径
        'private_key_path' => 'key/rsa_private_key.pem',
        //支付宝公钥（后缀是.pen）文件相对路径
        'ali_public_key_path' => 'key/alipay_public_key.pem',
        //签名方式 不需修改
        'sign_type'   => strtoupper('RSA'),
        //字符编码格式 目前支持 gbk 或 utf-8
        'input_charset'  => strtolower('utf-8'),
        //ca证书路径地址，用于curl中ssl校验
        //请保证cacert.pem文件在当前文件夹目录中
        'cacert'  =>   getcwd().'\\cacert.pem',
        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        'transport'   => 'http'
    ),
	    
	'orderStatus' => array(
		'0' => '订单确认',
		'1' => '确认图片',
		'2' => '建模',
		'3' => '打印',
		'4' => '发送',
		'5' => '退货',
		'6' => '换货',
		'7' => '完成'
    )
);