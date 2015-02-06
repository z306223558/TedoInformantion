<?php

/**
 * 订单数据模型
 */

namespace Think\Pay;

class PayVo {

        protected $_orderNo;
        protected $_fee;
        protected $_title;
        protected $_body;
        protected $_callback;
        protected $_url;
        protected $_param;

        /**
         * 设置订单号
         * @param string $order_no
         * @return \Think\Pay\PayVo
         */
        public function setOrderNo($order_no) {
                $this->_orderNo = $order_no;
                return $this;
        }

        /**
         * 设置商品价格
         * @param int $fee
         * @return \Think\Pay\PayVo
         */
        public function setFee($fee) {
                $this->_fee = $fee;
                return $this;
        }

        /**
         * 设置商品名称
         * @param string $title
         * @return \Think\Pay\PayVo
         */
        public function setTitle($title) {
                $this->_title = $title;
                return $this;
        }

        /**
         * 设置商品描述
         * @param string $body
         * @return \Think\Pay\PayVo
         */
        public function setBody($body) {
                $this->_body = $body;
                return $this;
        }

        /**
         * 设置支付完成后的后续操作接口
         * @param string $callback
         * @return \Think\Pay\PayVo
         */
        public function setCallback($callback) {
                $this->_callback = $callback;
                return $this;
        }

        /**
         * 设置支付完成后的跳转地址
         * @param string $url
         * @return \Think\Pay\PayVo
         */
        public function setUrl($url) {
                $this->_url = $url;
                return $this;
        }

        /**
         * 设置订单的额外参数
         * @param mixed $param
         * @return \Think\Pay\PayVo
         */
        public function setParam($param) {
                $this->_param = $param;
                return $this;
        }

        /**
         * 获取订单号
         * @return string
         */
        public function getOrderNo() {
                return $this->_orderNo;
        }

        /**
         * 获取商品价格
         * @return int
         */
        public function getFee() {
                return $this->_fee;
        }

        /**
         * 获取商品名称
         * @return string
         */
        public function getTitle() {
                return $this->_title;
        }

        /**
         * 获取支付完成后的后续操作接口
         * @return string
         */
        public function getCallback() {
                return $this->_callback;
        }

        /**
         * 获取支付完成后的跳转地址
         * @return string
         */
        public function getUrl() {
                return $this->_url;
        }

        /**
         * 获取商品描述
         * @return string
         */
        public function getBody() {
                return $this->_body;
        }

        /**
         * 获取订单的额外参数
         * @return mixed
         */
        public function getParam() {
                return $this->_param;
        }

}
