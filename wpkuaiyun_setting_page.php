<?php
/**
 *  插件设置页面
 * User: zdl25
 * Date: 2019/3/15
 * Time: 17:43
 */
function wpkuaiyun_setting_page() {
    // 如果当前用户权限不足
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient privileges!');
    }

	$wpkuaiyun_options = get_option('wpkuaiyun_options');

    if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce']) && !empty($_POST)) {
        if($_POST['type'] == 'info_set') {

            foreach ($wpkuaiyun_options as $k => $v) {
                if ($k =='no_local_file') {
                    $wpkuaiyun_options[$k] = (isset($_POST[$k])) ? 'true' : 'false';
                } else if ($k != 'token') {
                    $wpkuaiyun_options[$k] = (isset($_POST[$k])) ? sanitize_text_field(trim(stripslashes($_POST[$k]))) : '';
                }
            }

            // 不管结果变没变，有提交则直接以提交的数据 更新 wpkuaiyun_options
            update_option('wpkuaiyun_options', $wpkuaiyun_options);

            # 更新另外两个wp自带的上传相关属性的值
            # 替换 upload_path 的值
            $upload_path = sanitize_option('upload_path', trim(trim(stripslashes($_POST['upload_path'])), '/'));
            update_option('upload_path', ($upload_path == '') ? ('wp-content/uploads') : ($upload_path));

            # 替换 upload_url_path 的值
            update_option('upload_url_path', esc_url_raw(trim(trim(stripslashes($_POST['upload_url_path']))), '/'));

?>
    <div class="updated" style="font-size: 25px;color: red; margin-top: 20px;font-weight: bold;"><p><strong>设置已保存！</strong></p></div>

<?php

    }
}

?>

<style type="text/css">
   table {
    border-collapse: collapse;
}

table, td, th {border: 1px solid #cccccc;padding:5px;}
.buttoncss {background-color: #4CAF50; 
    border: none;cursor:pointer;
    color: white;
    padding: 15px 22px;
    text-align: center;
    text-decoration: none;
    display: inline-block;border-radius: 5px;
    font-size: 12px;font-weight: bold;
}
.buttoncss:hover {
    background-color: #008CBA;
    color: white;
}
input{border: 1px solid #ccc;padding: 5px 0px;border-radius: 3px;padding-left:5px;}

</style>
<div class="wrap" style="margin: 10px;">
    <h2>WordPress + 景安快云对象存储设置</h2>
    <hr/>    
        <p>这个插件可实现WordPress上传静态资源（图片、附件）同步至景安快云对象存储中，实现静态资源分离，这样提高服务器的访问速度。</p>
        <p>插件官方网站： <a href="https://www.laobuluo.com" target="_blank">老部落</a> / <a href="https://www.laobuluo.com/2341.html" target="_blank">WPKuaiYun插件发布页面</a> / 站长交流QQ群： <a href="https://jq.qq.com/?_wv=1027&k=5gBE7Pt" target="_blank"> <font color="red">594467847</font></a>（网站运营及互联网创业交流）/ <a href="https://www.laobuluo.com/kuaiyun/" target="_blank">景安快云服务器云产品优惠</a></p>
                 
      <hr/>
    <form name="form1" method="post" action="<?php echo wp_nonce_url('./admin.php?page=' . WPKUAIYUN_BASEFOLDER . '/wpkuaiyun_actions.php'); ?>">
        <table class="form-table">
            <tr>
                 <td style="text-align:right;">
                    <b>空间名称：</b>
                </td>
                <td>
                    <input type="text" name="bucketName" value="<?php echo esc_attr($wpkuaiyun_options['bucketName']); ?>" size="50"
                           placeholder="BucketName"/>

                    <p>我们需要先到景安开通对象存储且新建空间： <a href="https://www.laobuluo.com/goto/zzidc.com" target="_blank">景安官方开通对象存储</a> 创建
                        <code>空间名称</code> 。示例: laobuluo</p>
                </td>
            </tr>
            <tr>
                <td style="text-align:right;">
                    <b>Resource 来源参数：</b>
                </td>
                <td>
                    <input type="text" name="resource" value="<?php echo esc_attr($wpkuaiyun_options['resource']); ?>" size="50"
                           placeholder="resource"/>
                    <p>API调用来源，可在会员中心->对象存储->获取Key值->获取resource（来源），点击获取。</p>
                </td>
            </tr>
            <tr>
                 <td style="text-align:right;">
                    <b>Voucher 设置（获取凭证）：</b>
                </td>
                <td>
                    <input type="text" name="voucher" value="<?php echo esc_attr($wpkuaiyun_options['voucher']); ?>" size="50"
                           placeholder="voucher"/>
                    <p>用户通过accesskey和secretkey获取的，可在会员中心->对象存储->获取Key值->
                        获取凭证，以邮件形式获取。会发送邮件到我们景安账户邮箱。</p>
                </td>
            </tr>
            <tr>
                 <td style="text-align:right;">
                    <b>Access_key：</b>
                </td>
                <td>
                    <input type="text" name="accessKey" value="<?php echo esc_attr($wpkuaiyun_options['accessKey']); ?>" size="50" placeholder="accessKey"/>
                    <p>用户秘钥对：开通快云存储时的Access_Key，可在会员中心->对象存储->获取Key值，获取。</p>
                </td>
            </tr>
            <tr>
                 <td style="text-align:right;">
                    <b>Secret_key：</b>
                </td>
                <td>
                    <input type="text" name="secretKey" value="<?php echo esc_attr($wpkuaiyun_options['secretKey']); ?>" size="50" placeholder="secretKey"/>
                    <p>用户秘钥对：开通快云存储时的Secret_Key，可在会员中心->对象存储->获取Key值，获取</p>
                </td>
            </tr>
            <tr>
                 <td style="text-align:right;">
                    <b>不在本地保留备份：</b>
                </td>
                <td>
                    <input type="checkbox"
                           name="no_local_file" <?php if (esc_attr($wpkuaiyun_options['no_local_file']) == 'true') {
						echo 'checked="TRUE"';
					}
					?> />

                   <p>如果我们只需要将图片等静态文件上传放置对象存储中，则勾选；如果我们本地和对象存储都存储，那就不勾选。</p>
                </td>
            </tr>
            <tr>
                 <td style="text-align:right;">
                    <b>URL前缀/本地文件夹：</b>

                </td>
                <td>
                    <input type="text" name="upload_url_path" value="<?php echo esc_url(get_option('upload_url_path')); ?>" size="50"
                           placeholder="请输入URL前缀"/>

                    <p><b>注意：</b></p>
                    <p>1. 请到对象存储中绑定该域名。（景安对象存储必须绑定域名且需要白名单设置才可以）</p>
                    <p>2. 对象存储中的存放路径（即“文件夹”）与上述 <code>本地文件夹</code> 中定义的路径是相同的（出于方便切换考虑）。</p>
                    <p>3. 示范：http://zzidc.laobuluo.com(我们绑定对象存储的域名)/wp-content/uploads（与上面本地文件夹一致的文件夹路径） 。 <br> <font color="red"><b>注意！注意！注意：</b></font> 一定要用后面的本地文件夹尾巴，比如 <font color="red"><b>wp-content/uploads</b></font> </p>
                    <p><font color="red"><b>举个例子：</b></font> <font color="blue"><b>http://zzidc.laobuluo.com/wp-content/uploads</b></font></p>
                </td>
            </tr>
            <tr>
                <td style="text-align:right;">
                    <b>本地文件夹：</b>
                </td>
                <td>
                    <input type="text" name="upload_path" value="<?php echo esc_attr(get_option('upload_path')); ?>" size="50"
                           placeholder="请输入上传文件夹"/>

                     <p>1. 附件在服务器上相对于WordPress根目录的存储位置，例如： <code>wp-content/uploads</code> （注意不要以“/”开头和结尾）。</p>
                    <p>2. 示范：<code>wp-content/uploads</code></p>
                    <br> <font color="red"><b>注意！注意！注意：</b></font> 这里的本地文件夹默认输入 <font color="red"><b>wp-content/uploads</b></font> ，需要和上面尾巴一致。</p>
                </td>
            </tr>
            
            <tr>
                <th>
                   
                </th>
                <td><input type="submit" name="submit" value="保存设置" class="buttoncss"/></td>
            </tr>
        </table>
        <input type="hidden" name="type" value="info_set">
    </form>
</div>
<?php
}
?>