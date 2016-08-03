<?php
$time = time();

$sql = "SELECT a.Users_ID,a.Active_ID,a.Type_ID,a.Active_Name,a.MaxGoodsCount,a.IndexBizGoodsCount,a.IndexShowGoodsCount,a.MaxBizCount,t.module,T.Type_Name FROM active AS a LEFT JOIN active_type AS t ON a.Type_ID = t.Type_ID WHERE a.Users_ID='{$UsersID}' AND a.starttime<={$time} AND a.stoptime>{$time} AND a.Status = 1";
$result = $DB->query($sql);
$activelist = $DB->toArray($result);
$goodslist = [];

foreach ($activelist as $k => $v)
{
    $IndexShowGoodsCount = $v['IndexShowGoodsCount'];
    $IndexBizGoodsCount = $v['IndexBizGoodsCount'];
    $indexBizLength = intval($IndexShowGoodsCount/$IndexBizGoodsCount);
    $goodslist[$k]['Users_ID'] = $v['Users_ID'];
    $goodslist[$k]['Active_ID'] = $v['Active_ID'];
    $goodslist[$k]['Type_ID'] = $v['Type_ID'];
    $goodslist[$k]['Active_Name'] = $v['Active_Name'];
    $goodslist[$k]['module'] = $v['module'];
    //获取指定数目的商家
    $sql = "SELECT IndexConfig FROM biz_active WHERE Users_ID='{$v['Users_ID']}' AND Active_ID={$v['Active_ID']} AND Status=2 ORDER BY addtime ASC Limit 0,{$indexBizLength} ";
    $res = $DB->query($sql);
    $plist = $DB->toArray($res);
    $listGoods = "";
    foreach($plist as $key => $value)
    {
        $listGoods .=$value['IndexConfig'].',';
    }
    $goodslist[$k]['goodsNum'] = 0;
    $listGoods = trim($listGoods,',');
    if($listGoods){
        $fields = "";
        $tablename = "";

        switch($v['module'])
        {
            case 'pintuan':
            {
                $fields = "starttime,Products_JSON,products_IsNew,products_IsRecommend,products_IsHot,Is_Draw,Products_ID,Products_Name,stoptime,Products_Sales,Products_PriceT,Products_PriceD,people_num";
                $tablename = "{$v['module']}_products";
                break;
            }
            case 'cloud':
            {
                $fields = "Users_ID,Products_Name,Products_PriceX,Products_PriceY,Products_JSON,Products_Distributes,Products_IsNew,Products_IsRecommend,Products_IsHot,Products_Weight,Products_Qrcode,Biz_ID";
                $tablename = "{$v['module']}_products";
                break;
            }
            default:
            {
                $fields = "starttime,Products_JSON,products_IsNew,products_IsRecommend,products_IsHot,Is_Draw,Products_ID,Products_Name,stoptime,Products_Sales,Products_PriceT,Products_PriceD,people_num";
                $tablename = "pintuan_products";
                break;
            }
        }
        
        $sql = "SELECT {$fields} FROM `{$tablename}` WHERE Users_ID='{$UsersID}' AND Products_ID IN ({$listGoods}) LIMIT 0,{$v['IndexShowGoodsCount']}";
        $result = $DB->query($sql);
        $list = $DB->toArray($result);
        $goodslist[$k]['goods']=$list;
        $goodslist[$k]['goodsNum'] = count($list);
    }
}