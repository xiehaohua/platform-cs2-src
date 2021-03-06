<?php
class pay_order{
	var $db;
	var $orderid;

	function __construct($DB,$orderid){
		$this->db = $DB;
		$this->orderid = $orderid;
	}
	
	private function get_order($orderid){
		$r = $this->db->GetRs("user_order","*","where Order_ID=".$orderid);
		return $r;
	}
	
	private function get_user($userid){
		$r = $this->db->GetRs("user","*","where User_ID=".$userid);
		return $r;
	}
	
	private function get_products($pid){
		$r = $this->db->GetRs("shop_products","*","where Products_ID=".$pid);
		return $r;
	}
	
	private function get_shopconfig($usersid){
		$r = $this->db->GetRs("shop_config","*","where Users_ID='".$usersid."'");
		$r1 = $this->db->GetRs("distribute_config","*","where Users_ID='".$usersid."'");
		$r = array_merge($r,$r1);
		return $r;
	}
	
	private function update_order($orderid,$data){
		$this->db->Set("user_order",$data,"where Order_ID=".$orderid);
	}
	
	private function pay_orders($orderid){
		
		$rsOrder = $this->get_order($orderid);
		
		if(!$rsOrder){
			return array("status"=>0,"msg"=>"订单不存在");
		}
		
		$url = '/api/'.$rsOrder["Users_ID"].'/'.$rsOrder["Order_Type"].'/member/status/'.$rsOrder["Order_Status"].'/';
		
		if($rsOrder["Order_Status"]<>1){
			return array("status"=>1,"url"=>$url);
		}
		
		$rsUser = $this->get_user($rsOrder["User_ID"]);
		
		//更新订单状态
		$Data = array(
			"Order_Status" => 2
		);
		$this->update_order($orderid, $Data);
		
		if(strpos($rsOrder["Order_Type"],'zhongchou')>-1){
			$url = '/api/'.$rsOrder["Users_ID"].'/zhongchou/orders/';
			return array("status"=>1,"url"=>$url);
		}elseif($rsOrder["Order_Type"]=="kanjia"){
			$url = '/api/'.$rsOrder["Users_ID"].'/user/kanjia_order/status/2';
			return array("status"=>1,"url"=>$url);
		}
		
		//积分抵用
		$Flag_b = TRUE;
		if($rsOrder["Integral_Consumption"] > 0 ){
			$Flag_b = change_user_integral($rsOrder["Users_ID"],$rsOrder["User_ID"],$rsOrder["Integral_Consumption"],'reduce','积分抵用消耗积分');
		}
		
		//更改分销账号记录状态,置为已付款
		$Flag_c = Dis_Account_Record::changeStatusByOrderID($orderid,1);

		handle_products_count($rsOrder["Users_ID"],$rsOrder);
		
		if($Flag_b&&$Flag_c){
			$isvirtual = $rsOrder["Order_IsVirtual"];
			$url = '/api/'.$rsOrder["Users_ID"].'/'.$rsOrder["Order_Type"].'/member/status/2/';
			$rsConfig=$this->get_shopconfig($rsOrder["Users_ID"]);				
			$CartList = json_decode(htmlspecialchars_decode($rsOrder["Order_CartList"]), true);
			
			//获取订单中的产品id集
			$productids = array_keys($CartList);
			$is_distribute = $rsUser["Is_Distribute"];//是否分销商标识
			
			//分销商级别数组			
			$level_data = get_dis_level($this->db,$rsConfig['Users_ID']);
			
			if($is_distribute==0){//不是分销商，判定成为分销商条件
				$LevelID = 0;//分销商级别
				if($rsConfig["Distribute_Type"]==2){//购买商品
					$LevelID = get_user_distribute_level_buy($level_data,$rsConfig,$productids);
				}elseif($rsConfig["Distribute_Type"]==1){//消费金额
					$LevelID = get_user_distribute_level_cost($level_data,$rsConfig,$rsOrder['Order_TotalPrice']);
				}
				
				if($LevelID >= 1){
					$is_distribute = 1;
					$f = create_distribute_acccount($rsConfig, $rsUser, $LevelID, '', 1);
				}
			}else{//是分销商判定可提现条件
				$tixian = 0;
				$rsAccount = $this->db->GetRs("distribute_account","User_ID,Enable_Tixian,Account_ID,Is_Dongjie,Is_Delete,Users_ID,Level_ID","where Users_ID='".$rsOrder["Users_ID"]."' and User_ID=".$rsOrder["User_ID"]);
				if($rsAccount){
					$is_distribute = 1;
					$account_data = array();
					if($rsAccount["Enable_Tixian"]==0){
						if($rsConfig["Withdraw_Type"] == 0){
							$tixian = 1;
						}elseif($rsConfig["Withdraw_Type"] == 2){
							$arr_temp = explode("|",$rsConfig["Withdraw_Limit"]);
							if($arr_temp[0]==0){
								$tixian = 1;
							}else{
								if(!empty($arr_temp[1])){
									$productsid = explode(",",$arr_temp[1]);
									foreach($productsid as $id){
										if(!empty($CartList[$id])){
											$tixian = 1;
											break;
										}
									}
								}
							}
						}
						if($tixian==1){
							$account_data['Enable_Tixian'] = 1;
						}
					}
					
					//判定升级级别
					$LevelID = get_user_distribute_level_upgrade($level_data,$rsConfig,$productids);
					if($LevelID>$rsAccount['Level_ID']){
						$account_data['Level_ID'] = $LevelID;
					}
					
					if(!empty($account_data)){
						$this->db->Set("distribute_account",$account_data,"where Users_ID='".$rsOrder["Users_ID"]."' and Account_ID=".$rsAccount["Account_ID"]);
					}
					
					if($rsConfig["Fuxiao_Open"]==1 && $rsAccount["Is_Delete"]==0){//开启复销功能
						distribute_fuxiao_return_action($this->db,$rsConfig["Fuxiao_Rules"],$rsAccount,$rsUser["User_OpenID"]);//是否达到复销要求并处理
					}
				}
			}
			
			if($is_distribute == 1 && $rsUser["Owner_Id"]>0 && $rsConfig["Fanben_Open"]==1 && $rsConfig["Fanben_Type"]==1 && $rsConfig["Fanben_Limit"]){//返本规则开启，下级限制开启，限制条件设置，会员是分销商，有推荐人
				$productids = array_keys($CartList);
				$arr_temp = explode(',',$rsConfig["Fanben_Limit"]);
				$condition = "";
				foreach($productids as $pid){
					if(!in_array($pid,$arr_temp)){
						continue;
					}
					$str_temp = '{"'.$pid.'":';
					$condition = $condition ? " or Order_CartList like '%".$str_temp."%'" : "Order_CartList like '%".$str_temp."%'";
				}
				if(!empty($condition)){
					$condition = "where Order_Status>1 and Order_ID<>".$orderid." and User_ID=".$rsOrder["User_ID"]." and (".$condition.")";
					
					$r_temp = $this->db->GetRs("user_order","count(Order_ID) as num",$condition);
					if($r_temp["num"]==0){
						$Fanben = $rsConfig["Fanben_Rules"] ? json_decode($rsConfig['Fanben_Rules'],true) : array();
						deal_distribute_fanben($Fanben, $rsUser["Owner_Id"]);
					}
				}
			}
			
			$confirm_code = '';
			if($rsOrder["Order_IsVirtual"]==1){
				if($rsOrder["Order_IsRecieve"]==1){
			
					Order::observe(new OrderObserver());
					$order = Order::find($orderid);
					$Flag = $order->confirmReceive();
					
					$url="/api/".$rsOrder["Users_ID"]."/".$rsOrder["Order_Type"]."/member/status/4/";
				
				}else{
					$confirm_code = get_virtual_confirm_code($rsOrder["Users_ID"]);
					$Data = array('Order_Code'=>$confirm_code);
					$this->update_order($orderid,$Data);
				}
			}
			
			$setting = $this->db->GetRs("setting","sms_enabled","where id=1");
			if($rsConfig["SendSms"]==1 && $setting["sms_enabled"]==1){
				/*
				if($rsConfig["MobilePhone"]){
					$sms_mess = '您的商品有订单付款，订单号'.$orderid.'请及时查看！';
					send_sms($rsConfig["MobilePhone"], $sms_mess, $rsOrder["Users_ID"]);
				}
				*/
				$rsBiz = $this->db->GetRs('biz','Biz_SmsPhone','where Biz_ID='.$rsOrder['Biz_ID']);
				if($rsBiz["Biz_SmsPhone"]){
					$sms_mess = '您的商品有订单付款，订单号'.$orderid.'请及时查看！';
					send_sms($rsBiz["Biz_SmsPhone"], $sms_mess, $rsOrder["Users_ID"]);
				}
				if($rsOrder["Order_IsVirtual"]==1 && $rsOrder["Order_IsRecieve"]==0){
					$sms_mess = '您已成功购买商品，订单号'.$orderid.'，消费券码为 '.$confirm_code;
					send_sms($rsOrder["Address_Mobile"], $sms_mess, $rsOrder["Users_ID"]);
				}
			}
			
			require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/weixin_message.class.php');
			$weixin_message = new weixin_message($this->db,$rsOrder["Users_ID"],$rsOrder["User_ID"]);
			$weixin_message->sendorder($rsOrder["Order_TotalPrice"],$orderid);
			return array("status"=>1,"url"=>$url);
		}else{
			return array("status"=>0,"msg"=>"订单支付失败");
		}
	}
	
	public function make_pay(){
		if(strpos($this->orderid,"PRE")>-1){
			
			$pre_order = $this->db->GetRs("user_pre_order","orderids","where pre_sn='".$this->orderid."'");
			$orderids = explode(",",$pre_order["orderids"]);
			foreach($orderids as $orderid){
				if(!$orderid){
					continue;
				}
				$data = $this->pay_orders($orderid);
			}
			$this->db->Set("user_pre_order",array("status"=>2),"where pre_sn='".$this->orderid."'");
		}else{
			$data = $this->pay_orders($this->orderid);
		}
		return $data;
	}
	
	public function get_pay_info(){
		if(strpos($this->orderid,"PRE")>-1){
			$pre_order = $this->db->GetRs("user_pre_order","*","where pre_sn='".$this->orderid."'");
			$data = array(
				"out_trade_no"=>$this->orderid,
				"subject"=>"微商城在线付款，订单编号:".$pre_order["orderids"],
				"total_fee"=>$pre_order["total"]
			);
		}else{
			$orderinfo = $this->get_order($this->orderid);
			if(!strpos($orderinfo["Order_Type"],'zhongchou')){
				if($orderinfo["Order_Type"]=='weicbd'){
					$pay_subject = "微商圈在线付款，订单编号:".$this->orderid;
				}elseif($orderinfo["Order_Type"]=='kanjia'){
					$pay_subject = "微砍价在线付款，订单编号:".$this->orderid;
				}else{
					$pay_subject = "微商城在线付款，订单编号:".$this->orderid;
				}
			}else{
				$pay_subject = "微众筹在线付款，订单编号:".$this->orderid;
			}
			$data = array(
				"out_trade_no"=>$orderinfo["Order_CreateTime"].$this->orderid,
				"subject"=>$pay_subject,
				"total_fee"=>$orderinfo["Order_TotalPrice"]
			);
		}
		return $data;
	}
	
	public function deal_distribute_order($method=0,$payid=''){
		$Method = array('余额支付','微支付','支付宝');
		$Active = array('购买','升级');	
		
		$rsOrder = $this->db->GetRs('distribute_order','*','where Order_ID='.$this->orderid);
		if(!$rsOrder){
			return array("status"=>0,"msg"=>"订单不存在");
		}
		
		if($rsOrder['Order_Status']<>1){
			return array("status"=>0,"msg"=>"该订单不是待付款状态");
		}
		
		$level_data = get_dis_level($this->db,$rsOrder['Users_ID']);
		if(empty($level_data[$rsOrder['Level_ID']])){
			return array("status"=>0,"msg"=>"该分销级别不存在");
		}
		
		$rsUser = $this->get_user($rsOrder['User_ID']);	
		
		$Flag = true;
		
		if($method==0){//余额支付
			//增加资金流水
			$Data = array(
				'Users_ID' => $rsOrder['Users_ID'],
				'User_ID' => $rsOrder['User_ID'],
				'Type' => 0,
				'Amount' => $rsOrder['Order_TotalPrice'],
				'Total' => $rsUser ['User_Money'] - $rsOrder['Order_TotalPrice'],
				'Note' => $Active[$rsOrder['Order_Type']]."成为".$rsOrder['Level_Name']."支出 -" . $rsOrder['Order_TotalPrice'],
				'CreateTime' => time () 		
			);
			$Add = $this->db->Add('user_money_record', $Data);
			$Flag = $Flag && $Add;
		}
		
		//更新用户信息
		$user_data = array(				
			'User_Money'=>$rsUser['User_Money'] - $rsOrder['Order_TotalPrice'],
			'Is_Distribute'=>1
		);
		$Set = $this->db->Set('user',$user_data,"where User_ID=".$rsOrder['User_ID']);
		$Flag = $Flag && $Set;
		
		//更改订单信息
		$order_data = array(
			'Order_Status'=>4,
			'Order_PayTime'=>time(),
			'Order_PaymentMethod'=>$Method[$method],
			'Order_PayID'=>$payid
		);
		$Set= $this->db->Set('distribute_order',$order_data,'where Order_ID='.$this->orderid);
		$Flag = $Flag && $Set;
		
		//分销商处理
		$rsConfig = shop_config($rsOrder['Users_ID']);
		//分销相关设置
		$dis_config = dis_config($rsOrder['Users_ID']);
		//合并参数
		$rsConfig = array_merge($rsConfig,$dis_config);
		
		if($rsOrder['Order_Type']==0){//购买成为分销商
			$flag = create_distribute_acccount($rsConfig, $rsUser, $rsOrder['Level_ID'], '', 1);
		}else{//分销商升级
			$flag = $this->db->Set('distribute_account','Level_ID='.$rsOrder['Level_ID'],'where User_ID='.$rsOrder['User_ID']);
		}
		if($Flag){
			if($rsUser['Owner_Id']>0){//佣金处理
				//佣金设置
				$bonus_list = array();
				
				$level_data[$rsOrder['Level_ID']]['Level_Distributes'] = $rsOrder['Order_Type']==0 ? json_decode($level_data[$rsOrder['Level_ID']]['Level_Distributes'], true) : json_decode($rsOrder['UpgradeDistributes'], true);
				
				foreach($level_data as $key=>$levelinfo){
					$bonus_list[$key] = array_values($level_data[$rsOrder['Level_ID']]['Level_Distributes']);
				}
				$DisAccount =  Dis_Account::Multiwhere(array('Users_ID'=>$rsOrder['Users_ID'],'User_ID'=>$rsUser['Owner_Id']))
						 ->first();
				$ancestors = $DisAccount->getAncestorIds($rsConfig['Dis_Level'],0);
				array_push($ancestors,$rsUser['Owner_Id']);
				$ancestors = array_reverse($ancestors);
				$ancestors = count($ancestors)>3 ? array_slice($ancestors,0,3) : $ancestors;
				$ancestors_meet = get_distribute_balance_userids($rsOrder['Users_ID'],$ancestors,$bonus_list,1);
				$sql_insert = '';
				$sql_update = '';
				$where_update = array();
				$account_list = array();
				$this->db->Get('distribute_account','Account_ID,balance,User_ID','where User_ID in('.implode(',',$ancestors).')');
				while($r = $this->db->fetch_assoc()){
					$account_list[$r['User_ID']] = array('Account_ID'=>$r['Account_ID'],'balance'=>$r['balance']);
				}
				foreach($ancestors as $k=>$val){
					if(!empty($ancestors_meet[$val]) && !empty($account_list[$val])){
						if($ancestors_meet[$val]['status']==1){//正常
							$money = $ancestors_meet[$val]['bonus'];
							$description = ($k+1).'级下属'.$Active[$rsOrder['Order_Type']].''.$rsOrder['Level_Name'].'获得佣金';
						}else{
							$money = 0;
							$description = $ancestors_meet[$val]['msg'];
						}
						$sql_insert .= ',("'.$rsOrder['Users_ID'].'",'.$this->orderid.','.$val.','.($k+1).','.$money.',"'.$description.'",1,'.$rsUser['Owner_Id'].','.time().','.$rsOrder['User_ID'].','.$rsOrder['Order_TotalPrice'].')';
						$sql_update .= '
						WHEN '.$account_list[$val]['Account_ID'].' THEN '.($account_list[$val]['balance']+$money);
						$where_update[] = $account_list[$val]['Account_ID'];
					}else{
						continue;
					}
				}
				
				if($sql_insert){
					$sql_insert = 'insert into distribute_order_record(Users_ID,Order_ID,User_ID,level,Record_Money,Record_Description,Record_Status,Owner_ID,Record_CreateTime,Buyer_ID,Price) values'.substr($sql_insert,1);
					$this->db->query($sql_insert);
					
					$sql_update = '
						UPDATE distribute_account
							SET balance = CASE Account_ID'.$sql_update.'        
							END
						WHERE Account_ID IN ('.implode(',',$where_update).')';
					$this->db->query($sql_update);
				}
			}
			return array('status'=>1);
		}else{
			return array('status'=>0,'msg'=>'订单支付成功');
		}
	}
	
	public function withdraw($UsersID,$UserID,$RecordID,$type='wx_hongbao'){//佣金提现(微信红包、微信转账
	
		$rsUser = $this->db->GetRs("user","User_OpenID","where User_ID=".$UserID);
		if(empty($rsUser["User_OpenID"])){
			return array('status'=>0,'msg'=>'用户未授权');
		}
		
		$rsUsers = $this->db->GetRs("users","Users_ID,Users_WechatAppId,Users_WechatAppSecret","where Users_ID='".$UsersID."'");
		if(empty($rsUsers["Users_WechatAppId"]) || empty($rsUsers["Users_WechatAppSecret"])){
			return array('status'=>0,'msg'=>'系统还未设置AppId和AppSecret');
		}
		
		$rsPay = $this->db->GetRs("users_payconfig","PaymentWxpayPartnerId,PaymentWxpayPartnerKey,PaymentWxpayCert,PaymentWxpayKey","where Users_ID='".$UsersID."'");
		if(empty($rsPay["PaymentWxpayPartnerId"]) || empty($rsPay["PaymentWxpayPartnerKey"]) || empty($rsPay["PaymentWxpayCert"]) || empty($rsPay["PaymentWxpayKey"])){
			return array('status'=>0,'msg'=>'系统支付信息配置不全');
		}
		
		$shop_sonfig = $this->db->GetRs('shop_config','ShopName','where Users_ID="'.$UsersID.'"');
		//必需OrderID无关参数		
		$OrderID=0;
		
		include_once('WxPay.pub.config.php');
		include_once('WxPayPubHelper.php');
		$mch_billno = MCHID.date('YmdHis').rand(1000, 9999);
		$money = strval($rsRecord["Record_Money"]*100);
		if($type=='wx_hongbao'){
			$weixinhongbao = new Wxpay_client_pub();
			//参数设定			
			$weixinhongbao->setParameter("mch_billno",$mch_billno);
			$weixinhongbao->setParameter("send_name",$rsConfig["ShopName"]);//发送者名称
			$weixinhongbao->setParameter("re_openid",$rsUser["User_OpenID"]);
			$weixinhongbao->setParameter("total_amount",$money);
			$weixinhongbao->setParameter("total_num","1");
			$weixinhongbao->setParameter("wishing",$rsConfig["ShopName"]."提现");//红包祝福语
			$weixinhongbao->setParameter("client_ip",getIp());
			$weixinhongbao->setParameter("act_name",$rsConfig["ShopName"]."提现");//活动名称
			$weixinhongbao->setParameter("remark",$rsConfig["ShopName"]."提现");//描述
			$weixinhongbao->url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";
			$weixinhongbao->curl_timeout = 30;
			$return_xml = $weixinhongbao->postXmlSSL_hongbao();
		}elseif($type=='wx_zhuanzhang'){
			$weixinzhuanzhang = new Wxpay_client_pub();
			//参数设定
			$weixinzhuanzhang->setParameter("partner_trade_no",$mch_billno);
			//$weixinzhuanzhang->setParameter("device_info",'');
			$weixinzhuanzhang->setParameter("openid",$rsUser["User_OpenID"]);
			$weixinzhuanzhang->setParameter("check_name",'NO_CHECK');
			//$weixinzhuanzhang->setParameter("re_user_name",'');
			$weixinzhuanzhang->setParameter("amount",$money);
			$weixinzhuanzhang->setParameter("spbill_create_ip",$this->getIp());
			$weixinzhuanzhang->setParameter("desc",$rsConfig["ShopName"]."提现");//描述
			
			$weixinzhuanzhang->url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
			$weixinzhuanzhang->curl_timeout = 30;
			$return_xml = $weixinzhuanzhang->postXmlSSL_zhuanzhang();
		}
		
		$responseObj = simplexml_load_string($return_xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		$return_code = trim($responseObj->return_code);
		$return_msg = trim($responseObj->return_msg);
		if($return_code=='SUCCESS'){
			$result_code = trim($responseObj->result_code);
			if($result_code=='SUCCESS'){
				//处理提现记录
				if($type=='wx_hongbao'){
					$record_data = array(
						"Record_Status"=>1,
						"Record_SendID"=>$mch_billno,
						"Record_SendTime"=>strtotime(trim($responseObj->send_time)),
						"Record_WxID"=>trim($responseObj->send_listid),
						"Record_SendType"=>$type
					);
				}else{
					$record_data = array(
						"Record_Status"=>1,
						"Record_SendID"=>$mch_billno,
						"Record_SendTime"=>strtotime(trim($responseObj->payment_time)),
						"Record_WxID"=>trim($responseObj->payment_no),
						"Record_SendType"=>$type
					);
				}
				$condition = "where Record_ID=".$RecordID;
				$this->db->Set("distribute_withdraw_record",$record_data,$condition);
				return array("status"=>1);
			}else{
				return array("status"=>0,"msg"=>trim($responseObj->err_code_des));
			}
		}else{
			return array("status"=>0,"msg"=>$return_msg);
		}
	}
	
	public function checkhongbao($UsersID,$mch_billno){//佣金提现(微信红包、微信转账
		$rsUsers = $this->db->GetRs("users","Users_ID,Users_WechatAppId,Users_WechatAppSecret","where Users_ID='".$UsersID."'");
		if(empty($rsUsers["Users_WechatAppId"]) || empty($rsUsers["Users_WechatAppSecret"])){
			return 2;
		}
		
		$rsPay = $this->db->GetRs("users_payconfig","PaymentWxpayPartnerId,PaymentWxpayPartnerKey,PaymentWxpayCert,PaymentWxpayKey","where Users_ID='".$UsersID."'");
		if(empty($rsPay["PaymentWxpayPartnerId"]) || empty($rsPay["PaymentWxpayPartnerKey"]) || empty($rsPay["PaymentWxpayCert"]) || empty($rsPay["PaymentWxpayKey"])){
			return 2;
		}
		
		$shop_sonfig = $this->db->GetRs('shop_config','ShopName','where Users_ID="'.$UsersID.'"');
		//必需OrderID无关参数		
		$OrderID=0;
		
		include_once('WxPay.pub.config.php');
		include_once('WxPayPubHelper.php');
		
		
		$weixinhongbao = new Wxpay_client_pub();
		//参数设定			
		$weixinhongbao->setParameter("mch_billno",$mch_billno);
		$weixinhongbao->setParameter("bill_type",'MCHT');		
		$weixinhongbao->url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/gethbinfo";
		$weixinhongbao->curl_timeout = 30;
		$return_xml = $weixinhongbao->postXmlSSL_hongbao();		
		$responseObj = simplexml_load_string($return_xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		$return_code = trim($responseObj->return_code);
		$return_msg = trim($responseObj->return_msg);
		if($return_code=='SUCCESS'){
			$result_code = trim($responseObj->result_code);
			if($result_code=='SUCCESS'){
				//处理查询结果
				$result_status = trim($responseObj->status);
				if($result_status == 'FAILED' || $result_status == 'REFUND'){//红包发放失败或退款
					return 1;
				}else{
					return 2;
				}				
			}else{
				return 2;
			}
		}else{
			return 2;
		}
	}
	
	private function getIp(){
		if (!empty($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP']!='unknown') {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']!='unknown') {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return preg_match('/^\d[\d.]+\d$/', $ip) ? $ip : '';
	}
}
?>