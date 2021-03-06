<?php
require_once ($_SERVER["DOCUMENT_ROOT"] . '/Framework/Conn.php');
require_once ($_SERVER["DOCUMENT_ROOT"] . '/include/helper/flow.php');

if(isset($_POST['action']) && $_POST['action'] == 'ajax')
{
    if(!isset($_SESSION["Users_ID"])){
        $Data=array(
            'status' => 0,
            'url' => '/biz/login.php'
        );
        die(json_encode($Data));
    }
    $users_id = $_SESSION["Users_ID"];
    $biz_id = $_POST['Biz_ID'];
    $res = $DB->GetRs("biz","Biz_ID,Biz_Flag,Biz_PayConfig","WHERE Biz_ID='{$biz_id}'");
    if($res && !empty($res['Biz_PayConfig'])){
        $Data=array(
            'status' => 1,
            'config' => $res['Biz_PayConfig']
        );
        die(json_encode($Data,JSON_UNESCAPED_UNICODE));
    }
    $Data=array(
        'status' => 0,
        'msg' => '结算配置没有设置',
        'config' => $res['Biz_PayConfig']
    );
    die(json_encode($Data,JSON_UNESCAPED_UNICODE));
    exit;
}

if ($_POST) {
    if(!isset($_POST["BizID"])){
        echo '<script language="javascript">alert("无效的BIZ_ID!");history.back();</script>';
        exit;
    }
	$BizRs = $DB->GetRS('biz','Users_ID,UserID',"where Users_ID='".$_SESSION["Users_ID"]."' and BIZ_ID=".$_POST["BizID"]);
	if(empty($BizRs['UserID'])){
		echo '<script language="javascript">alert("该商家没有绑定前台会员,暂不能结款!");history.back();</script>';
		exit;
	}
    $DB->showErr = false;
    if (empty($_SESSION["Users_Account"])) {
        header("location:/member/login.php");
    }
    
    require_once ($_SERVER["DOCUMENT_ROOT"] . '/include/helper/balance.class.php');
    $balance = new balance($DB, $_SESSION["Users_ID"]);
    $Time = empty($_POST["Time"]) ? array(
        time(),
        time()
    ) : explode(" - ", $_POST["Time"]);
    $StartTime = strtotime($Time[0]);
    $EndTime = strtotime($Time[1]);
    $condition = "where Biz_ID=" . $_POST["BizID"] . " and Users_ID='" . $_SESSION["Users_ID"] . "' and Record_CreateTime>=" . $StartTime . " and Record_CreateTime<=" . $EndTime . " and Record_Status=0";
    $paymentinfo = $balance->create_payment($condition);
    if (!$paymentinfo || $paymentinfo["products_num"] == 0) {
        echo '<script language="javascript">alert("暂无结算数据");history.back();</script>';
        exit();
    }
    $createtime = time();
    $Data = array(
        "FromTime" => $StartTime,
        "EndTime" => $EndTime,
        "Payment_Type" => $_POST['PaymentID'],
        "Amount" => $paymentinfo["alltotal"],
        "Diff" => $paymentinfo["cash"],
        "Web" => $paymentinfo["web"] - $paymentinfo["bonus"],
        "Bonus" => $paymentinfo["bonus"],
        "Total" => $paymentinfo["supplytotal"],
        "CreateTime" => $createtime,
        "Biz_ID" => $_POST["BizID"],
        "Users_ID" => $_SESSION["Users_ID"],
        "Status" => 3
    );
    switch ($_POST['PaymentID']) {
        case 1:
            {
                $Data['OpenID'] = $_POST["OpenID"];
                break;
            }
        case 2:
            {
                $Data['aliPayNo'] = $_POST["aliPayNo"];
                $Data['aliPayName'] = $_POST["aliPayName"];
                break;
            }
        case 3:
            {
                $Data['Bank'] = $_POST["Bank"];
                $Data['BankNo'] = $_POST["BankNo"];
                $Data['BankName'] = $_POST["BankName"];
                $Data['BankMobile'] = $_POST["BankMobile"];
                break;
            }
    }
    
    $Flag = $DB->Add("shop_sales_payment", $Data);
    $paymentid = $DB->insert_id();
    if ($Flag) {
        $DB->Set("shop_sales_record", array(
            "Payment_ID" => $paymentid,
            "Record_Status"=>3
        ), $condition);
        $Payment_Sn = $createtime . $paymentid;
        $DB->Set("shop_sales_payment", array(
            "Payment_Sn" => $Payment_Sn
        ), "where Payment_ID=" . $paymentid);
        echo '<script language="javascript">alert("生成成功");window.location.href="payment_detail.php?paymentid=' . $paymentid . '";</script>';
        exit();
    } else {
        echo '<script language="javascript">alert("生成失败！");history.back();</script>';
        exit();
    }
}



?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet'
	type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
</head>

<body>
	<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
	<style type="text/css">
body, html {
	background: url(/static/member/images/main/main-bg.jpg) left top fixed
		no-repeat;
}
</style>
	<div id="iframe_page">
		<div class="iframe_content">
			<link href='/static/member/css/shop.css' rel='stylesheet'
				type='text/css' />
			<script type='text/javascript' src='/static/member/js/payment.js'></script>
			<div class="r_nav">
				<ul>
					<li><a href="sales_record.php">销售记录</a></li>
					<li class="cur"><a href="payment.php">付款单</a></li>
				</ul>
			</div>
			<div id="payment" class="r_con_wrap">
				<link href='/static/js/plugin/operamasks/operamasks-ui.css'
					rel='stylesheet' type='text/css' />
				<script type='text/javascript'
					src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
				<script type='text/javascript'
					src='/static/js/plugin/daterangepicker/moment_min.js'></script>
				<link href='/static/js/plugin/daterangepicker/daterangepicker.css'
					rel='stylesheet' type='text/css' />
				<script type='text/javascript'
					src='/static/js/plugin/daterangepicker/daterangepicker.js'></script>
				<script language="javascript">$(document).ready(payment.payment_edit_init);</script>
				<form id="payment_form" class="r_con_form" method="post" action="?">
					<div class="rows">
						<label>商家</label> <span class="input time"> 
						<select name='BizID' notnull>
                          <?php
                        $DB->get("biz", "*", "where Users_ID='" . $_SESSION["Users_ID"] . "'");
                        while ($value = $DB->fetch_assoc()) {
                            echo '<option value="' . $value["Biz_ID"] . '">' . $value["Biz_Name"] . '</option>';
                        }
                        ?>
        				</select>&nbsp; <font class="fc_red">*</font></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<?php $pay = getPayConfig($_SESSION["Users_ID"], true); ?>
						<label>付款方式</label> <span class="input time"> <select
							name='PaymentID'>
          				<?php $count = 0; ?>
                  		<?php foreach ($pay as $key => $value): ?>
                  		<?php
                        $selected = $count == 0 ? "selected" : "";
                        $count ++;
                        ?>
                  		<option value="<?=$value['ID'] ?>" <?=$selected ?>><?=$value['Name'] ?></option>
                  		<?php endforeach;?>
        		  		<option value="3">银行转账</option>
						</select>&nbsp; <font class="fc_red">*</font></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>结算时间</label> <span class="input time"> <input name="Time"
							type="text"
							value="<?php echo date("Y-m-01 00:00:00") ?> - <?php echo date("Y-m-d H:i:s",strtotime("+7 day")) ?>"
							class="form_input" size="40" readonly notnull /> <font
							class="fc_red">*</font> <span class="tips">需要结算的销售记录的时间段</span></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>微信识别码</label> <span class="input"> <input name="OpenID"
							value="" type="text" class="form_input" size="40" maxlength="100"
							notnull> <font class="fc_red">*</font> <span class="tips">商家微信OpenID</span></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>银行类型</label> <span class="input"> <input name="Bank"
							value="" type="text" class="form_input" size="40" maxlength="100"
							notnull> <font class="fc_red">*</font> <span class="tips">如交通银行，***分行</span></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>银行卡号</label> <span class="input"> <input name="BankNo"
							value="" type="text" class="form_input" size="40" maxlength="100"
							notnull> <font class="fc_red">*</font></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>收款人</label> <span class="input"> <input name="BankName"
							value="" type="text" class="form_input" size="40" maxlength="100"
							notnull> <font class="fc_red">*</font></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>付款账户</label> <span class="input"> <input name="aliPayNo"
							value="" type="text" class="form_input" size="40" maxlength="100"
							notnull> <font class="fc_red">*</font></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>付款账户名</label> <span class="input"> <input name="aliPayName"
							value="" type="text" class="form_input" size="40" maxlength="100"
							notnull> <font class="fc_red">*</font></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>收款人手机</label> <span class="input"> <input name="BankMobile"
							value="" type="text" class="form_input" size="40" maxlength="100"
							notnull> <font class="fc_red">*</font></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label></label> <span class="input"> <input type="submit"
							class="btn_green" value="一键生成" name="submit_btn"></span>
						<div class="clear"></div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<script>
		$(function(){
			getConfig();
			$("select[name='BizID']").change(function(){
				getConfig();
			});
		});

		function getConfig()
		{
			var BizID = $("select[name='BizID']").val();
			$.post("/member/shop/payment_add.php",{action:'ajax',Biz_ID:BizID},function(data){
				if(data.status==0){
					alert(data.msg);
				}else if(data.status==1){	//成功获取并更改openid
					
					var config = eval('('+data.config+')');
					var PaymentID = config.PaymentID;

					if(PaymentID==1){
						var openid = config.config.OpenID;
						$("input[name='OpenID']").val(openid);
					}else if(PaymentID==2){
						var aliPayNo = config.config.aliPayNo;
						var aliPayName = config.config.aliPayName;
						$("input[name='aliPayNo']").val(aliPayNo);
						$("input[name='aliPayName']").val(aliPayName);
					}else{
						var Bank = config.config.Bank;
						var BankNo = config.config.BankNo;
						var BankName = config.config.BankName;
						var BankMobile = config.config.BankMobile;
						$("input[name='Bank']").val(Bank);
						$("input[name='BankNo']").val(BankNo);
						$("input[name='BankName']").val(BankName);
						$("input[name='BankMobile']").val(BankMobile);
					}
					
				}
			},"json");
		}
	</script>
	<script>
$(function(){
	call();
	$("select[name='PaymentID']").change(function(){
		call();
		getConfig();
	});
	$("input[name='submit_btn']").submit(function(){
		var paymentid = parseInt($("select[name='PaymentID']").val());
		if(paymentid===1){
			var openid = $("input[name='OpenID']").val();
			
			if(openid=="" || openid==null){
				alert("微信识别码OpenID 不能为空");
				return false;
			}
		} else if (paymentid === 2){
			var aliPayNo = $("input[name='aliPayNo']").val(),
				aliPayName = $("input[name='aliPayName']").val();
			
			if ( aliPayNo ==="" || aliPayNo ===null ){
				alert("支付宝帐号不能为空");
				return false;
			}
			if ( aliPayName ==="" || aliPayName ===null ){
				alert("支付宝账户名不能为空");
				return false;
			}
		} else if (paymentid === 3){
			var Bank = $("input[name='Bank']").val(),
				BankNo = $("input[name='BankNo']").val(),
    			BankName = $("input[name='BankName']").val(),
    			BankMobile = $("input[name='BankMobile']").val();
			
			if ( Bank ==="" || Bank === null ){
				alert("银行类型不能为空");
				return false;
			}
			if ( BankNo ==="" || BankNo === null ){
				alert("银行卡号不能为空");
				return false;
			}  
			if ( BankName ==="" || BankName === null ){
				alert("收款人不能为空");
				return false;
			} 
			if ( BankMobile ==="" || BankMobile === null ){
				alert("收款人手机不能为空");
				return false;
			}   
		}
	});
});


function call()
{
	var paymentid = parseInt($("select[name='PaymentID']").val());
	switch(paymentid)
	{
    	case 1:		//微信支付
    	{
    		$("input[name='aliPayNo']").parent().parent().hide();
    		$("input[name='aliPayName']").parent().parent().hide();
    		$("input[name='OpenID']").parent().parent().show();
    		$("input[name='Bank']").parent().parent().hide();
    		$("input[name='BankNo']").parent().parent().hide();
    		$("input[name='BankName']").parent().parent().hide();
    		$("input[name='BankMobile']").parent().parent().hide();
    		break;
    	}
    	case 2:		//支付宝支付
    	{
    		$("input[name='aliPayNo']").parent().parent().show();
    		$("input[name='aliPayName']").parent().parent().show();
    		$("input[name='OpenID']").parent().parent().hide();
    		$("input[name='Bank']").parent().parent().hide();
    		$("input[name='BankName']").parent().parent().hide();
    		$("input[name='BankNo']").parent().parent().hide();
    		$("input[name='BankMobile']").parent().parent().hide();
    		break;
    	}
    	case 3:
    	{
    		$("input[name='aliPayNo']").parent().parent().hide();
    		$("input[name='aliPayName']").parent().parent().hide();
    		$("input[name='OpenID']").parent().parent().hide();
    		$("input[name='Bank']").parent().parent().show();
    		$("input[name='BankNo']").parent().parent().show();
    		$("input[name='BankName']").parent().parent().show();
    		$("input[name='BankMobile']").parent().parent().show();
    		break;
    	}
	}
}
</script>
</body>
</html>