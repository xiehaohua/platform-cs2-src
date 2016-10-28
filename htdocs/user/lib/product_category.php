<?php
//获取商家分类保证金
$bizVerifyData = $rsBiz['bizData'];

//获取平台分类
$b2cCategory = product_category::get_all_category();

//获取商家自己的分类
$res = product_category::getDev401firstCate($BizAccount);
$Category = [];
if (isset($res['errorCode']) && $res['errorCode'] == 0) {
    foreach ($res['cateData'] as $k => $v) {
        $result = product_category::getDev401SecondCate(['Biz_Account' => $BizAccount, 'firstCateID' => $v['Category_ID']]);
        if (isset($result['errorCode']) && $result['errorCode'] == 0 && isset($result['cateData']) && count($result['cateData']) > 0) {
            $res['cateData'][$k]['child'] = $result['cateData'];
        } else {
            unset($res['cateData'][$k]);
        }
    }
    $Category = $res['cateData'];
} else {
    echo '<script>layer.open({content: "分类获取失败", shadeClose: false, btn: "确定", yes: function(){history.back();}});</script>';
    exit;
}

?>

<!-- 隐藏的平台分类列表 -->
<div id="cate_b2c" style="display:none;">
    <div class="select_containers">
        请选择一级分类：
        <select name="b2c_firstCate" id="b2c_firstCate" style="font-size:15px; width:150px; height:30px; border:1px solid #ccc;">
            <option value="0">请选择一级分类</option>
            <?php
                if (!empty($b2cCategory)) {
                    foreach ($b2cCategory as $k => $v) {
                        //分类下无子分类的不显示  以及分类无保证金
                        if (isset($v['child']) && count($v['child']) > 0) {
                            echo '<option value="' . $v['Category_ID'].'">' . $v['Category_Name'] . '</option>';
                        }
                    }
                }
            ?>
        </select><br/>
        请选择二级分类：
        <select name="b2c_secondCate" id="b2c_secondCate" style="font-size:15px; width:150px; height:30px; border:1px solid #ccc;">
            <option value="0">请选择二级分类</option>
        </select>
        <?php
            if (!empty($b2cCategory)) {
                foreach ($b2cCategory as $k => $v) {
                    //未达到分类保证金，和分类下无子分类的不显示
                    if (isset($v['child']) && count($v['child']) > 0) {
                        echo '<div class="first_cate_'.$v['Category_ID'].'" style="display:none;">';
                        foreach ($v['child'] as $kk => $vv) {
                            if ($bizVerifyData['bond_free'] >= $vv['Category_Bond']) {
                                echo '<option value="' . $vv['Category_ID'].'">' . $vv['Category_Name'] . '</option>';
                            }
                        }
                        echo '</div>';
                    }
                }
            }
        ?>
    </div>
</div>

<!-- 隐藏的401分类列表 -->
<div id="cate" style="display:none;">
    <div class="select_containers">
        请选择一级分类：
        <select name="firstCate" id="firstCate" style="font-size:15px; width:150px; height:30px; border:1px solid #ccc;">
            <option value="0">请选择一级分类</option>
            <?php
                if (!empty($Category)) {
                    foreach ($Category as $k => $v) {
                        echo '<option value="' . $v['Category_ID'].'">' . $v['Category_Name'] . '</option>';
                    }
                }
            ?>
        </select><br/>
        请选择二级分类：
        <select name="secondCate" id="secondCate" style="font-size:15px; width:150px; height:30px; border:1px solid #ccc;">
            <option value="0">请选择二级分类</option>
        </select>
        <?php
            if (!empty($Category)) {
                foreach ($Category as $k => $v) {
                    //无子分类的不显示
                    if (isset($v['child']) && count($v['child']) > 0) {
                        echo '<div class="first_cate_'.$v['Category_ID'].'" style="display:none;">';
                        foreach ($v['child'] as $kk => $vv) {
                            echo '<option value="' . $vv['Category_ID'].'">' . $vv['Category_Name'] . '</option>';
                        }
                        echo '</div>';
                    }
                }
            }
        ?>
    </div>
</div>