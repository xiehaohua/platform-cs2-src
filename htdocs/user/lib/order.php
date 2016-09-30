<?php
if (!defined('USER_PATH')) exit();
require_once CMS_ROOT . "/user/config.inc.php";

require_once CMS_ROOT . '/include/api/ImplOrder.class.php';
require_once CMS_ROOT . '/include/helper/page.class.php';

//获取订单
function getOrders($Biz_Account, $page = 1, $orderID = '', $order_status){
    $transfer = ['Biz_Account' => $Biz_Account, 'pageSize' => 2, 'Order_ID' => $orderID];
    $res = ImplOrder::getOrders($transfer, $page);
    return $res;
}

//获取订单详情
function getOrderDetail($Biz_Account,$orderID){
    $transfer = ['Biz_Account' => $Biz_Account, 'pageSize' => 1, 'Order_ID' => $orderID];
    $res = ImplOrder::getOrders($transfer);
    return $res;
}

if (isset($_GET['act']) && $_GET['act'] == 'order_confirm') {     //获取待处理订单
    $res = getOrders($BizAccount, isset($_GET['page']) ? $_GET['page'] : 1, isset($_POST['Order_ID']) ? (int)$_POST['Order_ID'] : '', 0)['data'];
    foreach ($res as $k => $v) {
        $resArr[$v['Order_Status']][] = $v;
    }

} elseif (isset($_GET['act']) && $_GET['act'] == 'order_unpaid') {     //获取未付款订单
    $res = getOrders($BizAccount, isset($_GET['page']) ? $_GET['page'] : 1, isset($_POST['Order_ID']) ? (int)$_POST['Order_ID'] : '', 1)['data'];
    foreach ($res as $k => $v) {
        $resArr[$v['Order_Status']][] = $v;
    }

} elseif (isset($_GET['act']) && $_GET['act'] == 'order_paid') {     //获取已付款订单
    $res = getOrders($BizAccount, isset($_GET['page']) ? $_GET['page'] : 1, isset($_POST['Order_ID']) ? (int)$_POST['Order_ID'] : '', 2)['data'];
    foreach ($res as $k => $v) {
        $resArr[$v['Order_Status']][] = $v;
    }

} elseif (isset($_GET['act']) && $_GET['act'] == 'order_delivered') {     //获取已发货订单
    $res = getOrders($BizAccount, isset($_GET['page']) ? $_GET['page'] : 1, isset($_POST['Order_ID']) ? (int)$_POST['Order_ID'] : '', 3)['data'];
    foreach ($res as $k => $v) {
        $resArr[$v['Order_Status']][] = $v;
    }

} elseif (isset($_GET['act']) && $_GET['act'] == 'order_completed') {     //获取已完成订单
    $res = getOrders($BizAccount, isset($_GET['page']) ? $_GET['page'] : 1, isset($_POST['Order_ID']) ? (int)$_POST['Order_ID'] : '', 4)['data'];
    foreach ($res as $k => $v) {
        $resArr[$v['Order_Status']][] = $v;
    }

} elseif (isset($_GET['act']) && $_GET['act'] == 'order_details') {
    if (isset($_GET['orderid'])) {
        $Order_ID = $_GET['orderid'];
        $res = getOrderDetail($_SESSION['Biz_Account'],$_GET['orderid']);
        if ($res['errorCode'] == 0) {
            $orderDetail = $res['data'][0];
            //收货地址
            $area_json = read_file($_SERVER["DOCUMENT_ROOT"].'/data/area.js');
            $area_array = json_decode($area_json,TRUE);
            $province_list = $area_array[0];
            $Province = '';
            if(!empty($orderDetail['Address_Province'])){
                $Province = $province_list[$orderDetail['Address_Province']].',';
            }
            $City = '';
            if(!empty($orderDetail['Address_City'])){
                $City = $area_array['0,'.$orderDetail['Address_Province']][$orderDetail['Address_City']].',';
            }

            $Area = '';
            if(!empty($orderDetail['Address_Area'])){
                $Area = $area_array['0,'.$orderDetail['Address_Province'].','.$orderDetail['Address_City']][$orderDetail['Address_Area']];
            }


            $Shipping=json_decode(htmlspecialchars_decode($orderDetail["Order_Shipping"]),true);
        } else {
            echo "对不起,未找到此订单号";exit;
        }
    } else {
        echo "缺少必须的参数";
        exit;
    }
}