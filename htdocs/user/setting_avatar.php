<?php
if (!defined('USER_PATH')) exit();

require_once CMS_ROOT . '/include/api/shopconfig.class.php';

$inajax = isset($_GET['inajax']) ? (int)$_GET['inajax'] : 0;

if ($inajax == 1) {
    $do = isset($_GET['do']) ? $_GET['do'] : '';

    if ($do == 'wechat') {
        $Users_WechatAccount = isset($_POST['Users_WechatAccount']) ? $_POST['Users_WechatAccount'] : 0;

        $data = [
            'Biz_Account' => $BizAccount,
            'usersData' => [
                'Users_WechatAccount' => $Users_WechatAccount,
            ],
        ];
        
        $result = shopconfig::updatecolumn($data);

        echo json_encode($result);
    }

    exit();
}else if (isset($_POST['do']) && $_POST['do'] == 'uploadFile') {
    $imagepath = trim($_POST['data']);
    $url = IMG_SERVER."user/lib/upload.php";
	$result = curlInterFace($url,"post",[
        'data' => $imagepath,
        'act' => 'uploadFile',
        'Users_Account' => $BizAccount,
        'filepath' => '../../uploadfiles/avatar',
    ]);
    if($result['errorCode']===0){
        $data = [
            'Biz_Account' => $BizAccount,
            'configData' => [
                'ShopLogo' =>$result['msg']
            ]
        ];

        $result = shopconfig::updatecolumn($data);
        echo json_encode($result);
        exit;
    }
    
    echo json_encode($result);exit;
}

//获取配置信息
$data = [
    'Biz_Account' => $BizAccount,
];
$result = shopconfig::getConfig($data);
$config = $result['data'];

?>
<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />  
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">  
<meta name="app-mobile-web-app-capable" content="yes">
<title>店铺头像</title>
</head>
<link href="../static/user/css/product.css" type="text/css" rel="stylesheet">
<link href="../static/user/css/font-awesome.min.css" type="text/css" rel="stylesheet">
<script type="text/javascript" src="../static/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="../static/user/js/jquery.uploadView.js"></script>
<script type="text/javascript" src="../static/js/plugin/layer_mobile/layer.js"></script>
<body>
<div class="w">
	<div class="back_x">
    	<a href="javascript:history.back()" class="l"><i class="fa  fa-angle-left fa-2x" aria-hidden="true"></i></a>店铺头像
    </div>
    <div class="blank10"></div>
	<div class="pic_add">
    	<div class="ccc js_uploadBox">
        	<img src="<?=$config['ShopLogo']?IMG_SERVER.$config['ShopLogo']:'' ?>" class="showimg">
            <span>修改图片</span>
            <input type="file" class="js_upFile" style="position:absolute; top:180px; left:0; height:34px; filter:alpha(opacity:0);opacity: 0;width:100%; cursor:pointer;" name="upthumb" />
            <input type="hidden" id="image_files" name="image_files" value="">
            <input type="hidden" name="ShopLogo" value="">
            <input type="hidden" id="Users_WechatAccount" name="Users_WechatAccount" value="">
        </div> 
	</div>
</div>
<script type="text/javascript">
$(function(){
    $(".btnsubmit").click(function(){
        var Users_WechatAccount = $("#Users_WechatAccount").val();
        $.post("?act=setting_wechat&inajax=1&do=wechat", {Users_WechatAccount:Users_WechatAccount}, function(json){
            if(json.errorCode == '0') {
                layer.open({content:json.msg, time:2, end:function() {
                    history.back();
                }});
            }
        },'json')
    });
    $(".js_upFile").uploadView({
            uploadBox: '.js_uploadBox',//设置上传框容器
            showBox : '.js_showBox',//设置显示预览图片的容器
            width : 220, //预览图片的宽度，单位px
            height : 120, //预览图片的高度，单位px
            allowType: ["gif", "jpeg", "jpg", "bmp", "png"], //允许上传图片的类型
            maxSize :10, //允许上传图片的最大尺寸，单位M
            success:function(e){
                $(".showimg").attr("src",$("#image_files").val());
                $.ajax({
                    type:"POST",
                    url:"?act=setting_avatar",
                    data:{"do":"uploadFile", "data":$("#image_files").val()},
                    dataType:"json",
                    success:function(data){
                        if (data.errorCode == 0) {
                             $("input[name=ShopLogo]").val(data.msg);
                        } else {
                            layer.open({
                                content: data.msg
                                ,btn: '我知道了'
                            });
                        }
                    }
                });
            }
        });
})
</script>

</body>
</html>
