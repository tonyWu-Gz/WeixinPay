<?php

namespace myapp;

use myapp\weixinpay\lib\WxPayUnifiedOrder;
use myapp\weixinpay\lib\WxPayConfig;
use myapp\weixinpay\lib\WxPayApi;
use myapp\weixinpay\lib\WxPayJsPay;
use myapp\weixinpay\lib\CLogFileHandler;
use myapp\weixinpay\lib\Log;
use myapp\weixinpay\lib\WxPayOrderQuery;
use myapp\weixinpay\lib\PayNotifyCallBack;

/**
 * 微信支付接口封装
 *
 * @author gz.tony@foxmail.com
 */
class WeixinPay {


    /**
     * 微信扫码支付模式二
     * 商户后台系统先调用微信支付的统一下单接口，
     * 微信后台系统返回链接参数code_url，
     * 商户后台系统将code_url值生成二维码图片，
     * 用户使用微信客户端扫码后发起支付。
     * 注意：code_url有效期为2小时，过期后扫码不能再发起支付。
     * https://pay.weixin.qq.com/wiki/doc/api/native.php?chapter=9_1
     * @param array $data device_info，body商品描述，detail商品详情，attach附加数据，trade_no订单号，total_fee标价金额(单位分)，notify_url通知地址，product_id商品ID
     * @return string 支付二维码url
     */
    public static function nativeModel2($data = ['device_info' => 'WEB', 'body' => '', 'detail' => '', 'attach' => '', 'trade_no' => '', 'total_fee' => 0, 'notify_url' => '', 'product_id' => 0]) {
        $logHandler = new CLogFileHandler(__DIR__ . '/weixinpay/logs/pay/' . date('Y-m') . '.log');        //初始化日志
        Log::Init($logHandler, 15);
        $input = new WxPayUnifiedOrder(); //统一下单输入对象
        $input->SetDevice_info($data['device_info']);
        $input->SetDetail($data['detail']); //商品详情（某某模块开通1年费用）
        $input->SetBody($data['body']); //商品描述(某某模块开通)
        $input->SetAttach($data['attach']); //附加数据
        $input->SetOut_trade_no($data['trade_no']); //商户订单号
        $input->SetTotal_fee($data['total_fee'] * 100); //标价金额(单位分)
        $input->SetTime_start(date("YmdHis")); //交易起始时间
        $input->SetTime_expire(date("YmdHis", time() + 600)); //交易结束时间
        $input->SetNotify_url($data['notify_url']); //通知地址
        $input->SetTrade_type("NATIVE"); //交易类型:JSAPI -JSAPI支付 NATIVE -Native支付 APP -APP支付
        $input->SetProduct_id($data['product_id']); //商品ID
        try {
            $config = new WxPayConfig();
            $result = WxPayApi::unifiedOrder($config, $input);
            //return $result["code_url"];
            return $result;
        } catch (Exception $e) {
            Log::ERROR(json_encode($e));
            return false;
        }
    }

    /**
     * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html
     * @param type $data
     * @return boolean
     */
    public static function jsApi($data = ['device_info' => 'WEB', 'body' => '', 'detail' => '', 'attach' => '', 'trade_no' => '', 'total_fee' => 0, 'notify_url' => '', 'product_tag' => 0]) {
        $logHandler = new CLogFileHandler(__DIR__ . '/weixinpay/logs/pay/' . date('Y-m') . '.log');        //初始化日志
        $log = Log::Init($logHandler, 15);
        try {
            $WxPayJsPay = new WxPayJsPay();
            $input = new WxPayUnifiedOrder();
            $openid = $WxPayJsPay->GetOpenid();
            $input->SetBody($data['body']);
//            $input->SetDetail($data['detail']); //商品详情
            $input->SetAttach($data['attach']);
            $input->SetOut_trade_no($data['trade_no']);
            $input->SetTotal_fee($data['total_fee']* 100);
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $input->SetGoods_tag($data['product_tag']);
            $input->SetNotify_url($data['notify_url']);
            //$input->SetSignType('MD5');
            $input->SetTrade_type("JSAPI");
            $input->SetOpenid($openid);
            $config = new WxPayConfig();
            $order = WxPayApi::unifiedOrder($config, $input);
            Log::DEBUG(json_encode($order)); //统一下单支付单信息
            return ['order'=>$order,'jsApiParameters' => $WxPayJsPay->GetJsApiParameters($order), 'editAddress' => $WxPayJsPay->GetEditAddressParameters()];
        } catch (Exception $e) {
            Log::ERROR(json_encode($e));
            return false;
        }
    }

    /**
     * 回复通知调用
     * https://pay.weixin.qq.com/wiki/doc/api/native.php?chapter=9_7&index=8
     * @param type $needSign
     */
    public static function replyNotify($needSign = true) {
        $logHandler = new CLogFileHandler(__DIR__ . '/weixinpay/logs/notify/' . date('Y-m') . '.log');        //初始化日志
        Log::Init($logHandler, 15);
        $config = new WxPayConfig();
        Log::DEBUG("begin notify!");
        $notify = new PayNotifyCallBack();
        $notify->Handle($config, $needSign);
    }

    /**
     * 微信支付订单的查询，商户可以通过查询订单接口主动查询订单状态，完成下一步的业务逻辑
     * https://pay.weixin.qq.com/wiki/doc/api/native.php?chapter=9_2
     * @param type $transaction_id 微信的订单号 优先使用 
     * @param type $out_trade_no 商户订单号
     * @return boolean
     */
    public static function queryorder($transaction_id = null, $out_trade_no = null) {
        if (is_null($transaction_id) && is_null($out_trade_no)) {
            return false;
        }
        $input = new WxPayOrderQuery();
        if ($transaction_id) {
            $input->SetTransaction_id($transaction_id);
        } else {
            $input->SetOut_trade_no($out_trade_no);
        }
        $logHandler = new CLogFileHandler(__DIR__ . '/weixinpay/logs/query/' . date('Y-m') . '.log');        //初始化日志
        Log::Init($logHandler, 15);
        $config = new WxPayConfig();
        $result = WxPayApi::orderQuery($config, $input);
        Log::DEBUG("query:" . json_encode($result));
        if (array_key_exists("return_code", $result) && array_key_exists("result_code", $result) && $result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS") {
            return $result;
        }
        return false;
    }

}
