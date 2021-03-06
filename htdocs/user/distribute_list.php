<?php
if (!defined('USER_PATH')) exit();
require_once "config.inc.php";
require_once CMS_ROOT . '/include/api/distribute.class.php';
require_once CMS_ROOT . '/include/api/user.class.php';
require_once CMS_ROOT . '/include/helper/page.class.php';

//分页初始化
$p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($p < 1) $p = 1;
//每页显示个数
$pageSize = 10;

$level = isset($_GET['level']) ? $_GET['level'] : 1;    //分销商等级  1、2、3级 

$transfer = ['Biz_Account' => $BizAccount, 'pageSize' => $pageSize, 'level' => $level];
$result = distribute::getDistribute($transfer, $p);

if (isset($result['errorCode']) && $result['errorCode'] != 0) {
    $total = 0;
    $totalPage = 1;
    $distributes = [];
} else {
    $total = $result['totalCount'];
    $totalPage = ceil($result['totalCount'] / $pageSize);
    $distributes = $result['data'];

    //获取对应的会员信息
    $res_user = user::getUser(['Biz_Account' => $BizAccount]);
    $user_list = [];
    if (isset($res_user['errorCode']) && $res_user['errorCode'] == 0 && count($res_user['data']) > 0) {
        foreach ($res_user['data'] as $k => $v) {
            $user_list[$v['User_ID']] = [
                'User_No' => $v['User_No'],
                'User_Mobile' => $v['User_Mobile'],
                'User_Name' => $v['User_Name'],
                'User_NickName' => $v['User_NickName'],
            ];
        }
    }
}

//分页
$page = new page();
$page->set($pageSize, $total, $p);

$infolist = [];
if (count($distributes) > 0) {
    foreach ($distributes as $row) {
        $row['level'] = $level;
        if ($row['Shop_Logo'] == '') {
            $row['Shop_Logo'] = rtrim(B2C_URL, '/').'/static/user/images/zh.png';
        } else {
            $row['Shop_Logo'] = rtrim(IMG_SERVER, '/') . $row['Shop_Logo'];
        }

        $row['User_No'] = isset($user_list[$row['User_ID']]) ? $user_list[$row['User_ID']]['User_No'] : '';
        $row['User_Mobile'] = isset($user_list[$row['User_ID']]) && $user_list[$row['User_ID']]['User_Mobile'] != '' ? $user_list[$row['User_ID']]['User_Mobile'] : '暂无手机号';
        $row['User_NickName'] = isset($user_list[$row['User_ID']]) && $user_list[$row['User_ID']]['User_NickName'] != '' ? $user_list[$row['User_ID']]['User_NickName'] : '暂无昵称';
        $infolist[] = $row;
    }
}

$return = [
    'page' => [
        'pagesize' => count($infolist),
        'hasNextPage' => (count($infolist) >= $pageSize) ? 'true' : 'false',
        'total' => $total,
    ],
    'data' => $infolist,
];

if (isset($_POST['ajax']) && $_POST['ajax'] == 1) {
    echo json_encode($return);
    exit;
}

?>
<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />  
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">  
<meta name="app-mobile-web-app-capable" content="yes">
<title>分销管理</title>
</head>
<link href="../static/user/css/product.css" type="text/css" rel="stylesheet">
<link href="../static/user/css/font-awesome.min.css" type="text/css" rel="stylesheet">
<script type="text/javascript" src="../static/user/js/jquery-1.8.3.min.js"></script>
<script type="text/javascript" src="../static/js/template.js"></script>
<script type="text/javascript" src="../static/user/js/jquery.SuperSlide.2.1.1.js"></script>
<body>
<div class="w">
	<div class="back_x">
    	<a href="?act=store" class="l"><i class="fa  fa-angle-left fa-2x" aria-hidden="true"></i></a>分销管理
    </div>
    <div class="slideTxtBox">
		<div class="hd distribute_x">
			<ul>
                <a href="?act=distribute_list&level=1"><li class="<?php if(isset($level) && $level == 1) { echo 'on'; } ?>">一级分销商</li></a>
                <a href="?act=distribute_list&level=2"><li class="<?php if(isset($level) && $level == 2) { echo 'on'; } ?>">二级分销商</li></a>
                <a href="?act=distribute_list&level=3"><li class="<?php if(isset($level) && $level == 3) { echo 'on'; } ?>">三级分销商</li></a>
            </ul>
		</div>
		<div class="bd">
        	<div class="user_ls">
                <ul class="distributeList">
                    <?php
                    if (isset($infolist) && count($infolist) > 0) {
                        foreach ($infolist as $k => $v) {
                    ?>
                    <li><a href="?act=distribute_detail&distributeid=<?php echo $v['Account_ID']; ?>&level=<?php echo $v['level']; ?>">
                        <span class="l"><img src="<?php echo $v['Shop_Logo']; ?>"></span>
                        <span class="infor_x l" style="text-align:left"><?php echo $v['User_NickName']; ?><p>手机号：<?php echo $v['User_Mobile']; ?></p></span>
                        <span class="r"><i class="fa  fa-angle-right fa-2x" aria-hidden="true"></i></span>
                        <div class="clear"></div>
                    </a></li>
                    <?php
                        }
                    } else {echo '<li style="text-align:center;color:#666;">暂无分销商</li>';}
                    ?>
                </ul>
            </div>
		</div>
	</div>
</div>
<div class="clear"></div>
<!-- 点击加载更多 -->
<script id="distribute-row" type="text/html">
{{each data as v i}}
    <li><a href="?act=distribute_detail&distributeid={{v.Account_ID}}&level={{v.level}}">
        <span class="l"><img src="{{v.Shop_Logo}}"></span>
        <span class="infor_x l" style="text-align:left">{{v.User_NickName}}<p>手机号：{{v.User_Mobile}}</p></span>
        <span class="r"><i class="fa  fa-angle-right fa-2x" aria-hidden="true"></i></span>
        <div class="clear"></div>
    </a></li>
{{/each}}
</script>
<style>
#pagemore{clear:both;text-align:center;  color:#666; padding-top: 5px; padding-bottom:5px;}
#pagemore a{ height:30px; line-height:30px; text-align:center;display:block; background-color:#ddd; border-radius: 2px;}
</style>
<div id="pagemore">
<?php
    if (isset($infolist) && count($infolist) > 0) {
        if ($return['page']['hasNextPage'] == 'true') {
            echo '<a href="javascript:;" data-next-pageno="2">点击加载更多...</a>';    
        } else {
            echo '已经没有了...';
        }
    }
?>
</div>
</body>
</html>
<script type="text/javascript">
    $(function(){
        //加载更多
        var last_pageno = 1;
        $("#pagemore a").click(function(){
            var totalPage = <?php echo $totalPage;?>;
            var pageno = $(this).attr('data-next-pageno');
            var url = 'admin.php?act=distribute_list&level=' + <?php echo $level; ?> + '&p=' + pageno;

            //防止一页多次加载
            if (pageno == last_pageno) {
                return false;
            } else {
                last_pageno = pageno;
            }

            var nextPageno = parseInt(pageno);
            if (nextPageno > totalPage) {
                $("#pagemore").html('已经没有了...');
                return true;
            }

            $.post(url, {ajax: 1}, function(json){
                if (parseInt(json.page.pagesize) > 0) {
                    var html = template('distribute-row', json);
                    $("ul.distributeList").append(html);
                }
                if (json.page.hasNextPage == 'true') {
                    $("#pagemore a").attr('data-next-pageno', nextPageno + 1);
                } else {
                    $("#pagemore").html('已经没有了...');
                }
            },'json')
        });

        //瀑布流加载翻页
        $(window).bind('scroll',function () {
            // 当滚动到最底部以上100像素时， 加载新内容
            if ($(document).height() - $(this).scrollTop() - $(this).height() < 100) {
                //已无数据可加载
                if ($("#pagemore").html() == '已经没有了...') {
                    return false;
                } else {
                    //模拟点击
                    $("#pagemore a").trigger('click');
                }
            }
        });
        
    });
</script>