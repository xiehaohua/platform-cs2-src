<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');

$pinConfig = $DB->GetRs('pintuan_config', '*', "where Users_ID = '{$UsersID}'");
$rsConfig = array_merge($rsConfig, $pinConfig);
$ActiveID     = isset($_GET['ActiveID']) && $_GET['ActiveID']?$_GET['ActiveID']:0;
if(!$ActiveID){
    sendAlert("ActiveID不存在");
}
$sql = "SELECT a.Users_ID,a.Active_ID,a.MaxBizCount,a.ListShowGoodsCount,a.BizShowGoodsCount FROM active AS a LEFT JOIN active_type AS t ON a.Type_ID=t.Type_ID WHERE a.Users_ID='{$UsersID}' AND t.module='pintuan' AND a.Active_ID={$ActiveID} ";
$result = $DB->query($sql);
$rsActive = $DB->fetch_assoc($result);
if(empty($rsActive)) {
    sendAlert("活动不存在");
}

$bizCount = $rsActive['BizShowGoodsCount'];
//获取幻灯片列表
if (! empty($rsConfig['banner_img'])) {
    $t = json_decode($rsConfig['banner_img'], true);
    if ($t && $t[0]) {
        $banner_img = array_filter(json_decode($rsConfig['banner_img'], true));
        $banner_url = array_filter(json_decode($rsConfig['banner_url'], true));
        $slider = array();
        for ($i = 0, $len = count($banner_img); $i < $len; $i ++) {
            $slider[$i]['img'] = $banner_img[$i];
            if (isset($banner_url[$i])) {
                $slider[$i]['url'] = $banner_url[$i];
            }
        }
    }
}
$rsBiz = $DB->GetRs("biz","Biz_Account,Biz_Name","WHERE Users_ID='{$UsersID}' AND Biz_ID={$BizID}");
if(empty($rsBiz)){
  sendAlert("商家店铺不存在");
}
$shopname = isset($rsBiz['Biz_Name']) && $rsBiz['Biz_Name']?$rsBiz['Biz_Name']:"";
$result = $DB->Get("biz_active","ListConfig,IndexConfig,Biz_ID,Active_ID","WHERE Users_ID='{$UsersID}' AND Active_ID={$ActiveID} AND Status=2 LIMIT 0,{$rsActive['MaxBizCount']}");
$activelist = $DB->toArray($result);
if(empty($activelist)){
    sendAlert("没有商家参与相关活动");
}
$indexGoods = "";
$listGoods = "";
foreach ($activelist as $k => $v)
{
    $indexGoods .= $v['IndexConfig'].',';
    $listGoods .= $v['ListConfig'].',';
}
$listGoods = trim($listGoods,',');
$indexGoods = trim($indexGoods,',');
$listGoods_temp = explode(",",$listGoods);
$indexGoods_temp = explode(",",$indexGoods);
$dis_temp = array_diff($listGoods_temp,$indexGoods_temp);
$dis_temp = implode($dis_temp,',');
$listGoods = $indexGoods.','.$dis_temp;
$listGoods = trim($listGoods,',');
$totalInfo = $DB->GetRs("pintuan_products","count(*) as total","WHERE Users_ID='{$UsersID}'  AND Biz_ID={$BizID} AND Products_ID IN ({$listGoods})");
$pagesize = 3;

$totalPage = $totalInfo['total']%$pagesize == 0?$totalInfo['total']/$pagesize:intval($totalInfo['total']/$pagesize)+1;

if(IS_AJAX){
    $time = time();
    $page = isset($_POST['page']) && $_POST['page']?$_POST['page']:1;
    $sort = isset($_POST['sort']) && $_POST['sort']?$_POST['sort']:1;
    $offset = ($page-1)*$pagesize;
    $method = isset($_POST['sortmethod']) && $_POST['sortmethod']?$_POST['sortmethod']:'asc';
    $order = ["Products_ID {$method}","Products_CreateTime {$method}","Products_Sales {$method}","Products_PriceT {$method}","Products_Index {$method}"];
    $fields = "starttime,Products_CreateTime,Users_ID,Products_JSON,products_IsNew,products_IsRecommend,products_IsHot,Is_Draw,Products_ID,Products_Index,Products_Name,stoptime,Products_Sales,Products_PriceT,Products_PriceD,people_num";
    
    $sql = "SELECT {$fields} FROM (SELECT {$fields} FROM `pintuan_products` WHERE Users_ID='{$UsersID}' AND Biz_ID = {$BizID} AND Products_ID IN ({$listGoods})
     LIMIT {$bizCount}) as t ".
    ($sort?"ORDER BY {$order[$sort]}":"ORDER BY field(Products_ID,{$listGoods})")." LIMIT {$offset},{$pagesize}";
    $result = $DB->query($sql);
    $list = [];
    if($result){
        $list = $DB->toArray($result);
        if(!empty($list))
        {
            foreach($list as $k => $v)
            {
                $image = json_decode($v['Products_JSON'], true);
                $path = $image['ImgPath']['0'];
                $list[$k]['imgpath'] = $path;
                if ($v['products_IsNew'] == 1) {
                    // 新品
                    $list[$k]['Tie'] = "1";
                } elseif ($v['products_IsRecommend'] == 1) {
                    // 促销
                    $list[$k]['Tie'] = "6";
                } elseif ($v['products_IsHot'] == 1) {
                    // 热销
                    $list[$k]['Tie'] = "2";
                }
                if ($v['Is_Draw'] == 0) {
                    $list[$k]['Draw'] = 1;
                } elseif ($v['Is_Draw'] == 1) {
                    $list[$k]['Draw'] = 0;
                }
                $list[$k]['Products_Name'] = sub_str($v['Products_Name'], 16, false);
                if($v['starttime']>$time){
                    $lasttime = $v['starttime'] - time();
                    $hour = intval($lasttime/3600)>0?intval($lasttime/3600):0;
                    $minute = intval(($lasttime-360*$hour)/60)?intval(($lasttime-360*$hour)/60):0;
                    $minute = intval($minute/60);
                    $list[$k]['buttonTitle'] = json_encode(['status'=>0, 'data'=>['hour'=>$hour,'minute'=>$minute]]);
                }else if ($v['stoptime']<$time){
                    $list[$k]['buttonTitle'] = json_encode(['status' =>-1 ]);
                }else{
                    $list[$k]['buttonTitle'] = json_encode(['status' =>1 ]);
                }
            }
            die(json_encode([ 'status'=>1 ,'data' => $list ], JSON_UNESCAPED_UNICODE));
        }
    }
    die(json_encode([ 'status'=>0 ,'data' => $list ], JSON_UNESCAPED_UNICODE));
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?="{$shopname}的拼团商城" ?>
</title>
<link href="/static/api/pintuan/css/css.css" rel="stylesheet" type="text/css">
<link href="/static/api/pintuan/css/style11.css" rel="stylesheet" type="text/css">
<script src="/static/api/pintuan/js/jquery.min.js"></script>
<script src="/static/api/pintuan/js/responsiveslides.min.js"></script>
<script src="/static/api/pintuan/js/common.js"></script>
<style>
.sort ul li {
    font-size: 13px;
    width: 23.5%;
    float:left;
    margin-bottom: 10px;
    background: #f61d4b;
    height: 30px;
    text-align:center;
    line-height: 30px;
    color: #fff;
    cursor:pointer;
    font-family: '微软雅黑'; 
  }
  .sort ul li:nth-child(2){
      float:left;
      margin-right:5px;
  }
  .sort ul li:nth-child(3){
      float:left;
      margin-right:5px;
  }
  .sort ul li:nth-child(1){
      float:left;
      margin-right:5px;
  }
  .sort ul li:nth-child(4){
      float:left;
  }
</style>
</head>
<body>
	<div class="w">
    <?php 
        $referUrl = "/api/{$UsersID}/shop/";
        $headTitle = "{$shopname}的拼团商城";
        include_once("top.php");     
    ?>
		<!-- 代码 开始 -->
		<div class="device">
			<a class="arrow-left" href="#"></a> <a class="arrow-right" href="#"></a>
			<div class="swiper-container">
				<div class="swiper-wrapper">
                  <?php
                  if (! empty($slider)) {
                    foreach ($slider as $ks => $ress) {
                        if (isset($ress['url']) && $ress['url']) {
                  ?>        
                	<div class="swiper-slide">
            			<a href="<?php echo $ress['url'];?>"><img src="<?php echo $ress['img'];?>"> </a>
            		</div>
                    <?php
                        } else {
                    ?>
                    <div class="swiper-slide">
            			<img src="<?php echo $ress['img'];?>">
            		</div>
                    <?php
                        }
                    }
                  }
                  ?>
                  </div>
			</div>
			<div class="pagination"></div>
		</div>
		<script src="/static/api/pintuan/js/idangerous.swiper.min.js"></script>
		<script>
          var mySwiper = new Swiper('.swiper-container',{
            pagination: '.pagination',
            loop:true,
            grabCursor: true,
            paginationClickable: true
          })
          $('.arrow-left').on('click', function(e){
            e.preventDefault();
            mySwiper.swipePrev();
          })
          $('.arrow-right').on('click', function(e){
            e.preventDefault();
            mySwiper.swipeNext();
          })
        </script>
		<!-- 代码部分end -->
		<!-- 代码 结束 -->
		<div class="sort">
			<ul>
				<li sort="1" method="asc">时间<span>↑↓</span></li>
				<li sort="2" method="asc">销量<span>↑↓</span></li>
				<li sort="3" method="asc">价格<span>↑↓</span></li>
				<li sort="4" method="asc">综合<span>↑↓</span></li>
			</ul>
		</div>
		<div class="clear"></div>
		<script src="/static/api/pintuan/js/dropload.min.js"></script>
    <div id="container"></div>
    <script>
		$(function(){
          var url = "/api/<?=$UsersID ?>/pintuan/biz/<?=$BizID ?>/act_<?=$ActiveID ?>/";
          var page = 1;
          var sort = 1;
          var method="asc";
          var marrow = "↑";
          var totalPage = <?=$totalPage?>;
          sessionStorage.setItem("<?=$UsersID ?>BizListSort", sort);
          sessionStorage.setItem("<?=$UsersID ?>ListMethod", method);
          sessionStorage.setItem("<?=$UsersID ?>BizcurrentPage", page);
          getContainer(url,page,sort,method);
          $(".sort ul li").click(function(){
            method = sessionStorage.getItem("<?=$UsersID ?>ListMethod");
            if(method=="asc"){
                method = "desc";
                marrow = "↓↑";
            }else{
                method = "asc";
                marrow = "↑↓";
            }
            $(this).attr("method",method);
            $(this).find("span").text(marrow);
            sort = $(this).attr("sort");
            method = $(this).attr("method");
          
            sessionStorage.setItem("<?=$UsersID ?>BizListSort", sort);
            sessionStorage.setItem("<?=$UsersID ?>BizcurrentPage", page);
            sessionStorage.setItem("<?=$UsersID ?>ListMethod", method);
            $("#container").empty();
            getContainer(url,page,sort,method);
          });
          $("#container").dropload({
              domUp : {
              domClass   : 'dropload-up',
              domRefresh : '<div class="dropload-refresh">下拉刷新</div>',
              domUpdate  : '<div class="dropload-update">释放更新</div>',
              domLoad    : '<div class="dropload-load"></div>'
              },
              domDown : {
                  domClass   : 'dropload-down',
                  domRefresh : '<div class="dropload-refresh">上拉加载更多</div>',
                  domUpdate  : '<div class="dropload-update">释放加载</div>',
                  domLoad    : '<div class="dropload-load"></div>'
              },
              loadUpFn : function(me){
                  /**
                  page = sessionStorage.getItem("<?=$UsersID ?>BizcurrentPage")?sessionStorage.getItem("<?=$UsersID ?>BizcurrentPage"):page;
                  sort = sessionStorage.getItem("<?=$UsersID ?>BizListSort")?sessionStorage.getItem("<?=$UsersID ?>BizListSort"):1;
                  method = sessionStorage.getItem("<?=$UsersID ?>ListMethod")?sessionStorage.getItem("<?=$UsersID ?>ListMethod"):'asc';
                  if(page>1){
                    page--;
                    sessionStorage.setItem("<?=$UsersID ?>BizListSort", sort);
                    sessionStorage.setItem("<?=$UsersID ?>BizcurrentPage", page);
                    getContainer(url,page,sort,method); 
                  }
                  me.resetload();
                  **/
              },
              loadDownFn : function(me){
                  page = sessionStorage.getItem("<?=$UsersID ?>BizcurrentPage")?sessionStorage.getItem("<?=$UsersID ?>BizcurrentPage"):page;
                  sort = sessionStorage.getItem("<?=$UsersID ?>BizListSort")?sessionStorage.getItem("<?=$UsersID ?>BizListSort"):1;
                  method = sessionStorage.getItem("<?=$UsersID ?>ListMethod")?sessionStorage.getItem("<?=$UsersID ?>ListMethod"):'asc';
                  if(page<=totalPage){
                    page++;
                    sessionStorage.setItem("<?=$UsersID ?>BizListSort", sort);
                    sessionStorage.setItem("<?=$UsersID ?>BizcurrentPage", page);
                    getContainer(url,page,sort,method); 
                  } 
                  me.resetload();
              }
        });
		});
    </script>
		<?php include 'bottom.php';?>
	</div>
</body>
</html>