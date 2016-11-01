<?php
require_once "config.inc.php";
$cateid = isset($_GET['id'])?$_GET['id']:0;
if($cateid){
    $result = $DB->Get("shop_articles", "Article_ID,Article_Title,Article_CreateTime,Article_Status", "WHERE Category_ID='{$cateid}' AND Article_Status = 1 ORDER BY Article_ID DESC");
    $rsArticleList = $DB->toArray($result);
    foreach($rsArticleList as $k => $v){
        $v['Article_CreateTime'] = date("Y-m-d",$v['Article_CreateTime']);
        $rsArticleList[$k] = $v;
    }
}

?>


<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />  
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">  
<meta name="app-mobile-web-app-capable" content="yes">
<title>学习中心</title>
</head>
<link href="../static/user/css/product.css" type="text/css" rel="stylesheet">
<link href="../static/user/css/font-awesome.min.css" type="text/css" rel="stylesheet">
<script type="text/javascript" src="../static/js/jquery-1.7.2.min.js"></script>
<body>
<div class="w">
	<div class="back_x">
    	<a class="l" href='<?=isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/user/admin.php?act=store'?>'><i class="fa  fa-angle-left fa-2x" aria-hidden="true"></i></a>学习中心
    </div>
    <div class="learn_list">
    	<ul>
            <?php
            if(!empty($rsArticleList)){  
                foreach($rsArticleList as $k => $v){         
            ?>
        	<li>
            	<a href='?act=learn_detail&id=<?=$v['Article_ID'] ?>'><?=$v['Article_Title'] ?><p><?=$v['Article_CreateTime'] ?></p></a>
            </li>
            <?php
                }
             } 
            ?>
        </ul>
    </div>
</div>
</body>
</html>
