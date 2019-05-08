# WeixinPay
微信支付SDK整理，支持命名空间调用，包括原生支付（扫码支付），H5支付，公众号支付等 


# 环境依赖

PHP 5 >= 5.3.0, PHP 7

# 目录及文件说明
```
weixinpay
    ├─cert  证书存放目录
    ├─lib   支付相关类
    │      CLogFileHandler.php
    │      Log.php
    │      PayNotifyCallBack.php
    │      WxPayApi.php
    │      WxPayBizPayUrl.php
    │      WxPayCloseOrder.php
    │      WxPayConfig.php
    │      WxPayConfigInterface.php
    │      WxPayDataBase.php
    │      WxPayDataBaseSignMd5.php
    │      WxPayDownloadBill.php
    │      WxPayException.php
    │      WxPayJsApiPay.php
    │      WxPayJsPay.php
    │      WxPayMicroPay.php
    │      WxPayNotify.php
    │      WxPayNotifyReply.php
    │      WxPayNotifyResults.php
    │      WxPayOrderQuery.php
    │      WxPayRefund.php
    │      WxPayRefundQuery.php
    │      WxPayReport.php
    │      WxPayResults.php
    │      WxPayReverse.php
    │      WxPayShortUrl.php
    │      WxPayUnifiedOrder.php    
    └─logs  日志目录
        ├─notify 
        ├─pay
        └─query   
```      

# 注意事项
```
1. 开启日志调试时，需要给log目录写入权限。
2.需要开启CURL服务、SSL服务。
3.命名空间默认在myapp，根据实际情况自行调整。
```
# 配置说明
```
lib目录下的WxPayConfig类中，填写参数配置,包括MCHID，APPID ，KEY ，APPSECRET ，证书路径等，详情见官方说明。
```
# 使用
```
文件WeixinPay.php中声明了WeixinPay类，这里只封装了调用扫码支付，jsapi，支付成功回调，以及订单查询等方法提供参考。
1.扫码支付模式2
WeixinPay::nativeModel2($data);
2.订单查询
WeixinPay::queryorder($transaction_id);
```