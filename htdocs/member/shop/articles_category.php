<?php 
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
if(isset($_GET["action"])){
	if($_GET["action"]=="del"){
		$r = $DB->GetRs("shop_articles","count(*) as num","where Users_ID='".$_SESSION["Users_ID"]."' and Category_ID=".$_GET["CategoryID"]);
		if($r["num"]>0){
			echo '<script language="javascript">alert("该分类下有文章,请勿删除");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
		}else{
			$Flag=$DB->Del("shop_articles_category","Users_ID='".$_SESSION["Users_ID"]."' and Category_ID=".$_GET["CategoryID"]);
			if($Flag){
				echo '<script language="javascript">alert("删除成功");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
			}else{
				echo '<script language="javascript">alert("删除失败");history.back();</script>';
			}
			exit;
		}
	}
}

$DB->get("shop_articles_category","*","where Users_ID='".$_SESSION["Users_ID"]."' order by Category_Index asc");
$arr = $DB->toArray();
$list = [];
foreach($arr as $k=>$v){
    if($v['Category_ParentID']==0){
        $list[$k]['base'] = $v;
    }
}

foreach ($list as $k => $v){
    foreach($arr as $key => $val){
        if($v['base']['Category_ID']==$val['Category_ParentID']){
            $list[$k]['child'][] = $val;
        }
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href="/static/style.css" rel="stylesheet" type="text/css" />
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/shop.js'></script>
    <div class="r_nav">
      <ul>
        <li><a href="articles.php">文章管理</a></li>
		<li class="cur"><a href="articles_category.php">分类管理</a></li>
      </ul>
    </div>
    <div id="products" class="r_con_wrap"> 
      <div class="category">
        <div class="control_btn"><a href="articles_category_add.php" class="btn_green btn_w_120">添加分类</a></div>
        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="mytable">
          <tr bgcolor="#f5f5f5">
            <td width="50" align="center"><strong>排序</strong></td>
            <td align="center"><strong>类别名称</strong></td>
            <td width="60" align="center"><strong>操作</strong></td>
          </tr>
          <?php
		    if(!empty($list)){
            $html = "";
            $t = "";
            $i = 1;
            foreach ($list as $k => $v){
         ?>
         <tr onMouseOver="this.bgColor='#D8EDF4';" onMouseOut="this.bgColor='';">
            <td><?php echo $i++; ?></td>
            <td><?php echo $v['base']["Category_Name"]; ?></td>
            <td align="center">
			<?php if($v['base']["Category_Type"] == '单页'){?>
                <a href="<?php echo 'http://'.$_SERVER['HTTP_HOST']?>/pc.php/shop/article/content/id/<?php echo $v['base']["Category_ID"]; ?>/UsersID/<?php echo $_SESSION['Users_ID']?>" title="浏览" target="_blank"><img src="/static/member/images/ico/view.gif" align="absmiddle" /></a>
			<?php }?>
			<a href="articles_category_edit.php?CategoryID=<?php echo $v['base']["Category_ID"]; ?>" title="修改"><img src="/static/member/images/ico/mod.gif" align="absmiddle" /></a> <a href="?action=del&CategoryID=<?php echo $v['base']["Category_ID"]; ?>" title="删除" onClick="if(!confirm('删除后不可恢复，继续吗？')){return false};"><img src="/static/member/images/ico/del.gif" align="absmiddle" /></a></td>
          </tr>
         <?php
                if(!empty($v['child'])){
                    foreach ($v['child'] as $key => $val){
         ?>
         <tr onMouseOver="this.bgColor='#D8EDF4';" onMouseOut="this.bgColor='';">
            <td><?php echo $i++; ?></td>
            <td>——<?php echo $val["Category_Name"]; ?></td>
            <td align="center">
			<?php if($val["Category_Type"] == '单页'){?>
                <a href="<?php echo 'http://'.$_SERVER['HTTP_HOST']?>/pc.php/shop/article/content/id/<?php echo $val["Category_ID"]; ?>/UsersID/<?php echo $_SESSION['Users_ID']?>" title="浏览" target="_blank"><img src="/static/member/images/ico/view.gif" align="absmiddle" /></a>
			<?php }?>
			<a href="articles_category_edit.php?CategoryID=<?php echo $val["Category_ID"]; ?>" title="修改"><img src="/static/member/images/ico/mod.gif" align="absmiddle" /></a> <a href="?action=del&CategoryID=<?php echo $val["Category_ID"]; ?>" title="删除" onClick="if(!confirm('删除后不可恢复，继续吗？')){return false};"><img src="/static/member/images/ico/del.gif" align="absmiddle" /></a></td>
          </tr>
         <?php               
                    }
                }
            }
        }
		  ?>
        </table>
        <div class="clear"></div>
      </div>
    </div>
  </div>
</div>
</body>
</html>