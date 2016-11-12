<?php
/*edit in 20160317*/
$rmenu["weixin"] = array(
	'weixin' => '常用功能',
	'account' => '系统首页',
	//'attention_reply' => '首次关注设置',
	//'menu' => '自定义菜单设置',
	//'keyword_reply' => '关键词回复设置',
	//'token_set' => '微信接口配置',
	//'shopping' => '支付方式管理',	
	'profile' => '修改密码',
	'guide' => '操作指南'
);

// $rmenu["material"] = array(
// 	'material' => '素材管理',
// 	'index' => '图文消息管理',
// 	'url' => '自定义URL管理',
// 	'sysurl' => '系统URL查询'
// );

 $rmenu['basic'] = array(
 	'basic' => '商城设置',
 	'setting/config' => '基本设置',	
//    'setting/other_config' => '积分设置',		
// 	'setting/skin' => '风格设置',
// 	'setting/home' => '首页设置',
// 	'setting/menu_config' => '菜单配置',
// 	'setting/third_login' => '三方登录',
 );

$rmenu['product']=array(
	'product' => '产品管理',
	'products' => '产品列表',
	'category' => '产品分类',
    //'commit' => '产品评论',
	'commision_setting' => '佣金设置'
);
// $rmenu['active']=array(
//     'active' => '活动管理',
//     'type' => '活动类型',
//     'active_add' => '发起活动',
//     'active_list' => '活动列表',

// );

// $rmenusub['act_active']=array(
//     'active_add' => '活动添加',
//     'active_edit' => '活动编辑',
//     'active' => '活动浏览',
//     'biz_active'=>'商家浏览',
//     'active_view'=>'商家产品浏览',
//     'type_add' => '类型添加',
//     'type_edit' => '类型编辑',
// );

$rmenu['biz']=array(
	'biz' => '商家管理',
	'index' => '商家列表',
	//'group' => '商家分组',
	'apply_config' => '入驻描述设置',
//'reg_config' => '注册页面设置',
    'apply_other' => '年费设置',
	'apply' => '入驻资质审核列表',
    'authpay' => '入驻支付列表',
    'chargepay' => '续费支付列表',
    'bond_back' => '保证金退款',
    'announce' => '公告管理',
    //'article_man' => '文章管理',
	'send_push' => '推送消息',
	'msg_push' => '推送消息管理'
);
$rmenusub['biz_send_push'] = [
	'send' => '推送处理'
];

// $rmenu["order"] = array(
// 	'order' => '订单管理',
// 	'orders' => '订单管理'
// );

// $rmenu["backup"] = array(
// 	'backup' => '退货单管理',
// 	'backups' => '退货单管理'
// );

// $rmenu["distribute"] = array(
// 	'distribute' => '分销管理',
// 	'setting' => '模块设置',
// 	'account' => '分销账号管理',
// 	'record' => '分销记录',
// 	'agent' => '代理商',
// 	'shareholder' => '股东',
//     'salesman' => '业务员管理',
// 	'withdraw' => '提现管理',
// 	'order_history' => '购买级别订单'
// );

$rmenu["article"] = array(
	'article' => '文章发布',
	'articles' => '文章管理',
	'articles_category' => '分类管理'
);

// $rmenu["user"] = array(
// 	'user' => '会员中心',
// 	'config' => '基本设置',
// 	'user_list' => '会员管理',
// 	'card_config' => '会员卡设置',
// 	'business_password' => '商家密码设置',
// 	'message' => '消息发布管理',	
// );

// $rmenu["kf"] = array(
// 	'kf' => '在线客服',
// 	'config' => '客服设置',
// 	'weixin_config' => '微信客服设置'
// );


// $rmenu["stores"] = array(
// 	'stores' => '门店定位',
// 	'config' => '基本设置',
// 	'index' => '门店管理'
// );

// $rmenu["financial"] = array(
// 	'financial' => '财务结算',
// 	'sales_record' => '销售记录',
// 	'payment' => '付款单'
// );

// $rmenu["pcsite"] = array(
// 	'pcsite' => 'PC站点管理',
//         'index'=>'首页设置'
// );

// $rmenu["buy_record"] = array(
// 	'buy_record'=>'购买记录',
// 	'renewal_record'=>'购买记录'
// );

// $rmenu['employee'] = array(
// 	'employee'=>'员工管理',
// 	'emplist'=>'员工管理',
// 	'emp_add'=>'添加员工',
// 	'charlist'=>'角色管理',
// 	'char_add'=>'创建角色'
// );

// $rmenu["weicuxiao"] = array(
// 	'guanggao' => '广告中心'
// );

//其他权限
// $rmenu["html"] = array(
// 	'html'=>'素材库',
// 	'material'=>'素材库',
// 	'spread'=>'推广技巧'
// );

// $rmenu['update'] = array(
// 	'update' => '系统升级',
// 	'index' => '系统升级',
// );

$rmenu["appmanager"] = [
	'appupload' => 'APP更新',
	'appmanage' => 'APP更新管理',
	'appedit' => 'APP编辑'
];
//不在右键菜单内的,系统首页
// $rmenusub["wei_account"] = array(
// 	'ajax' => 'ajax文件'
// );

//自定义菜单设置
// $rmenusub["wei_menu"] = array(
// 	'menu_add' => '添加菜单',
// 	'menu_edit' => '修改菜单',
// 	'auth_set' => '微信授权配置'
// );
// //关键词回复设置
// $rmenusub["wei_keyword_reply"] = array(
// 	'keyword_edit' => '添加关键字'
// );
// //运费与支付
// $rmenusub["wei_shopping"] = array(
// 	'recieve' => '收货地址'
// );
//运费管理
// $rmenusub["wei_shipping"] = array(
// 	'shipping_template' => '快递模版管理',
// 	'printtemplate' => '运单模版',
// 	'printtemplate_edit' => '运单模版修改',
// 	'printtemplate_set' => '运单模版设计',
// 	'printtemplate_view' => '运单模版预览',
// 	'printtemplate_add' => '运单模版添加',
// 	'shipping_template_add' => '运费模版添加',
// 	'shipping_template_edit' => '运费模版修改',
// 	'ajax' => 'ajax文件'
// );
// //图文消息管理
// $rmenusub["mat_index"] = array(
// 	'add' => '单图文',
// 	'madd' => '多图文',
// 	'edit' => '单图文修改',
// 	'medit' => '多图文修改'
// );
//商城设置/基本设置
// $rmenusub["bas_setting/config"] = array(
// 	'setting/sms_add' => '短信购买',
// 	'setting/sms_record' => '短信购买记录',
// 	'setting/send_record' => '短信购买发送记录'
// );
// //产品列表
 $rmenusub["pro_products"] = array(
// 	'products_add' => '添加产品',
// 	'output' => '导出产品',
 	'products_edit' => '修改产品',
// 	'virtual_card_select' => '虚拟卡选择',
// 	'commit' => '产品评论'
 );
//产品分类管理
$rmenusub["pro_category"] = array(
	'category_add' => '添加分类',
	'category_edit' => '修改分类'
);
//产品属性管理
// $rmenusub["pro_shop_attr"] = array(
// 	'shop_attr_add' => '添加属性',
// 	'shop_attr_edit' => '修改属性'
// );
// //产品类型管理
// $rmenusub["pro_product_type"] = array(
// 	'product_type_add' => '添加类型',
// 	'product_type_edit' => '修改类型'
// );
//虚拟卡密管理
// $rmenusub["pro_virtual_card"] = array(
// 	'virtual_card_add' => '添加卡密',
// 	'virtual_card_edit' => '修改卡密',
// 	'virtual_card_type' => '卡密类型列表',
// 	'virtual_card_type_add' => '添加卡密类型',
// 	'virtual_card_type_edit' => '修改卡密类型'
// );
//商家列表
$rmenusub["biz_index"] = array(
	'add' => '添加商家',
	'edit' => '修改商家'
);
//商家分组
$rmenusub["biz_group"] = array(
	'group_add' => '添加商家分组',
	'group_edit' => '修改商家分组'
);
//文章管理
$rmenusub["biz_article_man"] = array(
	'article_add' => '添加文章',
	'article_edit' => '修改文章',
        'articlecate_man' => '分类管理',
        'articles_category_add' => '分类增加',
        'articles_category_edit' => '分类修改'
);
$rmenusub["biz_apply_config"] = array(
	'apply_other' => '年费设置',
	 
);
$rmenusub["biz_bond_back"] = array(
	'back_detail' => '保证金退款查看',	 
);
$rmenusub["biz_apply"] = array(
	'apply_detail' => '资质查看',	 
);
$rmenusub["biz_announce"] = array(	 
	'announce_add' => '添加公告',	 
	'announce_edit' => '编辑公告',
	'announce_category' => '分类管理',
	'announce_category_add' => '添加分类',
	'announce_category_edit' => '编辑分类',
);

//文章管理
$rmenusub["art_articles"] = array(
	'article_add' => '添加文章',
	'article_edit' => '修改文章'
);
//分类管理
$rmenusub["art_articles_category"] = array(
	'articles_category_add' => '添加文章分类',
	'articles_category_edit' => '修改文章分类'
);
//门店管理
// $rmenusub["sto_index"] = array(
// 	'add' => '新增门店',
// 	'edit' => '门店编辑'
// );
//订单管理
// $rmenusub["ord_orders"] = array(
// 	'virtual_orders_view' => '订单详情',
// 	'virtual_orders' => '消费认证',
// 	'orders_send' => '发货',
// 	'orders_confirm' => '确认订单',
// 	'send_print' => '打印发货单',
// 	'order_print' => '详情页面打印订单'
// );
//退货单管理
// $rmenusub["bac_backups"] = array(
// 	'orders_view' => '相关订单',
// 	'back_view' => '查看详情'
// );
//模块设置
// $rmenusub["dis_setting"] = array(
// 	'ajax' => 'ajax文件',
// 	'level' => '弹出级别',
// 	'level_edit' => '弹出级别修改',
// 	'level_add' => '弹出级别增加',
// 	'setting_withdraw' => '提现设置',
// 	'setting_other' => '其它设置',
// 	'setting_protitle' => '爵位设置',
// 	'setting_distribute' => '分销首页设置',
// 	'levelview' => '分销级别详情'
// );
//业务员管理
// $rmenusub["dis_salesman"] = array(
// 	 'salesman_config' => '业务员设置'
// );


//分销账号管理
// $rmenusub["dis_account"] = array(
// 	'account_posterity' => '下属'
// );
//代理商
// $rmenusub["dis_agent"] = array(
// 	'agent_list' => '代理商列表',
// 	'agent_orders' => '代理商订单',
// 	'agent_orders_view' => '代理订单详情',
// 	'agent_orders_confirm' => '代理订单审核'	
// );
// //股东
// $rmenusub["dis_shareholder"] = array(
// 	'sha_list' => '股东列表',
// 	'sha_orders' => '股东订单列表',
// 	'sha_orders_view' => '股东订单详情',
// 	'sha_orders_confirm' => '股东订单审核'	
// );
// //提现管理
// $rmenusub["dis_withdraw"] = array(
// 	'withdraw_method' => '提现管理',
// 	'withdraw_method_add' => '提现添加',
// 	'withdraw_method_edit' => '提现修改'
// );
//购买级别订单
// $rmenusub["dis_order_history"] = array(
// 	'commsion' => '佣金记录'
// );
// //付款单
// $rmenusub["fin_payment"] = array(
// 	'payment_add' => '生成付款单',
// 	'payment_detail' => '生成付款单详情'
// );
// //基本设置
// $rmenusub["use_config"] = array(
// 	'lbs' => '一键导航'
// );
//会员管理
// $rmenusub["use_user_list"] = array(
// 	'user_view' => '会员查看',
// 	'user_level' => '会员等级设置',
// 	'user_profile' => '会员注册资料',
// 	'card_benefits' => '会员权利说明',
// 	'card_benefits_add' => '会员权利说明添加内容',
// 	'card_benefits_edit' => '会员权利说明修改内容',
// 	'gift_orders_view' => '查看详情'
// );
// //优惠劵管理
// $rmenusub["use_coupon_list"] = array(
// 	'coupon_config' => '优惠劵设置',
// 	'coupon_list_logs' => '优惠劵使用记录'
// );
// //礼品兑换管理
// $rmenusub["use_gift_orders"] = array(
// 	'gift' => '礼品管理',
// 	'gift_add' => '礼品添加',
// 	'gift_edit' => '礼品修改'
// );
//消息发布管理
$rmenusub["use_message"] = array(
	'message_add' => '添加消息',
	'message_edit' => '修改消息'
);
//员工管理
// $rmenusub["emp_emplist"] = array(
// 	'emp_edit' => '员工修改',
// 	'emp_add' => '员工添加'
// );
// //角色管理
// $rmenusub["emp_charlist"] = array(
// 	'char_edit' => '角色修改'
// );


// /*edit拼团20160423--start--*/
// $rmenu["pintuan"] = array(
//     'pintuan'=>'拼团',
//     'config' => '基本设置',
//     'home' => '首页设置',
//     'products' => '产品管理',
//     'cate' => '拼团分类管理',
//     'orders' => '订单管理',
//     'comment' => '评论管理',
//     'virtual_orders' => '商家认证',
//     'virtual_card' => '虚拟卡浏览',
//     'aword' => '抽奖统计',
//     'awordConfig'=>'计划任务配置'
// );
// $rmenusub["pin_virtual_card"] = array(
//     'virtual_card_add' => '添加卡密',
//     'virtual_card_edit' => '修改卡密',
//     'virtual_card_type' => '卡密类型列表',
//     'virtual_card_type_add' => '添加卡密类型',
//     'virtual_card_type_edit' => '修改卡密类型',
//     'virtual_card_select' => '卡密绑定',
    
// );
// $rmenusub['pin_cate']=array(
//     'cate_add'=>'拼团分类添加',
//     'cate_edit'=>'拼团分类修改',
//     'product_add'=>'拼团商品添加',
//     'product_edit'=>'拼团商品修改',
//     'product_newadd'=>'商城商品添加'
    
// );
// $rmenusub["pin_orders"] = array(
//     'orders_view' => '订单详情',
//     'orders_send' => '发货',
//     'orders_confirm' => '确认订单',
//     'send_print' => '打印发货单',
//     'order_print' => '详情页面打印订单',
//     'output'=>"导出",
//     'ajax'=>"ajax处理文件"
// );

/*edit拼团20160423--end--*/
/* 云购配置开始 */
// $rmenu["cloud"] = array(
// 	'cloud'=>'云购物',
//   'config'=>'基本设置',
// 	'products'=>'产品列表',	 
// 	'category'=>'商品分类',	
// 	'slide_list'=>'首页幻灯片',
// 	'orders'=>'订单明细管理',
// 	'shipping_orders'=>'商品领取管理',
// );
// //产品列表
// $rmenusub["clo_products"] = array(
// 	'products_add' => '添加产品',
// 	'products_edit' => '修改产品',
// 	'products_detail_list' => '查看往期',
// 	'buyrecords' => '购买详细'
// );
// //商品分类
// $rmenusub["clo_category"] = array(
// 	'category_add' => '添加分类',
// 	'category_edit' => '修改分类'
// );
// //首页幻灯
// $rmenusub["clo_slide_list"] = array(
// 	'slide_add' => '添加幻灯',
// 	'slide_edit' => '修改幻灯'
// );
// //订单明细管理
// $rmenusub["clo_orders"] = array(
// 	'virtual_orders' => '消费认证',
// 	'virtual_orders_view'=>'订单明细查看'
// );
// //商品领取管理
// $rmenusub["clo_shipping_orders"] = array(
// 	'shipping_orders_view'=>'商品领取详情',
// 	'shipping_orders_send'=>'商品领取发货',
// 	'shipping_orders_recieve' => '批量收货'
// );
// /* 云购配置结束 */

// $rmenu["zhongchou"] = array(
// 	'zhongchou'=>'微众筹',
// 	'config'=>'基本设置',
// 	'project'=>'项目管理'
// );

// //项目管理
// $rmenusub["zho_project"] = array(
// 	'project_add' => '添加项目',
// 	'project_edit' => '修改项目',
// 	'prize' => '设置',
// 	'users' => '查看',
// 	'prize_add' => '添加赠品',
// 	'prize_edit' => '修改赠品'
// );

//易极付批量转账
$rmenu["yijipay"] = array(
	'yijipay' => '批量转账',
	'trans_yijipay_batch' => '批量转账',
  'mypay' => '我的钱包'
);
?>