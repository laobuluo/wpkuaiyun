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

	$wpkuaiyun_options = get_option('wpkuaiyun_options', True);

    if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce']) && !empty($_POST)) {
        if($_POST['type'] == 'info_set') {

            foreach ($wpkuaiyun_options as $k => $v) {
                if ($k =='no_local_file') {
                    $wpkuaiyun_options[$k] = (isset($_POST[$k])) ? 'true' : 'false';
                } else {
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
    <div class="updated"><p><strong>设置已保存！</strong></p></div>

<?php

    }
}

?>


<div class="wrap" style="margin: 10px;">
    <h2>WP KUAIYUN 对象存储</h2>
    <form name="form1" method="post" action="<?php echo wp_nonce_url('./admin.php?page=' . WPKUAIYUN_BASEFOLDER . '/wpkuaiyun_actions.php'); ?>">
        <table class="form-table">
            <tr>
                <th>
                    <legend>Bucket名称</legend>
                </th>
                <td>
                    <input type="text" name="bucketName" value="<?php echo esc_attr($wpkuaiyun_options['bucketName']); ?>" size="50"
                           placeholder="BucketName"/>

                    <p>请先访问 <a href="https://oss.console.aliyun.com/overview" target="_blank">阿里云OSS控制台</a> 创建
                        <code>Bucket</code> ，再填写以上内容。示例: itbulu</p>
                </td>
            </tr>
            <tr>
                <th>
                    <legend>Resource 设置</legend>
                </th>
                <td>
                    <input type="text" name="resource" value="<?php echo esc_attr($wpkuaiyun_options['resource']); ?>" size="50"
                           placeholder="resource"/>
                    <p>API调用来源，可在会员中心->对象存储->获取Key值->获取resource（来源），点击获取。</p>
                </td>
            </tr>
            <tr>
                <th>
                    <legend>Voucher 设置</legend>
                </th>
                <td>
                    <input type="text" name="voucher" value="<?php echo esc_attr($wpkuaiyun_options['voucher']); ?>" size="50"
                           placeholder="voucher"/>
                    <p>用户通过accesskey和secretkey获取的，可在会员中心->对象存储->获取Key值->
                        获取凭证，以邮件形式获取。</p>
                </td>
            </tr>
            <tr>
                <th>
                    <legend>AccessKey</legend>
                </th>
                <td>
                    <input type="text" name="accessKey" value="<?php echo esc_attr($wpkuaiyun_options['accessKey']); ?>" size="50" placeholder="accessKey"/>
                    <p>用户秘钥对：开通快云存储时的Access_Key，可在会员中心->对象存储->获取Key值，获取。</p>
                </td>
            </tr>
            <tr>
                <th>
                    <legend>SecretKey</legend>
                </th>
                <td>
                    <input type="text" name="secretKey" value="<?php echo esc_attr($wpkuaiyun_options['secretKey']); ?>" size="50" placeholder="secretKey"/>
                    <p>用户秘钥对：开通快云存储时的Secret_Key，可在会员中心->对象存储->获取Key值，获取</p>
                </td>
            </tr>
            <tr>
                <th>
                    <legend>不在本地保留备份</legend>
                </th>
                <td>
                    <input type="checkbox"
                           name="no_local_file" <?php if (esc_attr($wpkuaiyun_options['no_local_file']) == 'true') {
						echo 'checked="TRUE"';
					}
					?> />

                    <p>建议不勾选</p>
                </td>
            </tr>
            <tr>
                <th>
                    <legend>本地文件夹：</legend>
                </th>
                <td>
                    <input type="text" name="upload_path" value="<?php echo esc_attr(get_option('upload_path')); ?>" size="50"
                           placeholder="请输入上传文件夹"/>

                    <p>附件在服务器上相对于WordPress根目录的存储位置，例如： <code>wp-content/uploads</code> （注意不要以“/”开头和结尾），根目录请输入<code>.</code>。</p>
                </td>
            </tr>
            <tr>
                <th>
                    <legend>URL前缀：</legend>
                </th>
                <td>
                    <input type="text" name="upload_url_path" value="<?php echo esc_url(get_option('upload_url_path')); ?>" size="50"
                           placeholder="请输入URL前缀"/>

                    <p><b>注意：</b></p>

                    <p>1）URL前缀的格式为 <code>{http或https}://{bucket}.{外网EndPoint}</code> （“本地文件夹”为 <code>.</code> 时），或者 <code>http://{cos域名}/{本地文件夹}</code>
                        ，“本地文件夹”务必与上面保持一致（结尾无 <code>/</code> ）。</p>

                    <p>2）对象存储中的存放路径（即“文件夹”）与上述 <code>本地文件夹</code> 中定义的路径是相同的（出于方便切换考虑）。</p>

                    <p>3）如果需要使用 <code>独立域名</code> ，直接将 <code>{bucket}.{外网EndPoint}</code> 替换为 <code>您的独立域名</code> ，并在对象存储->域名管理里面<code>绑定该域名</code>。</p>
                </td>
            </tr>
            <tr>
                <th>
                    <legend>更新选项</legend>
                </th>
                <td><input type="submit" name="submit" value="更新"/></td>
            </tr>
        </table>
        <input type="hidden" name="type" value="info_set">
    </form>
</div>
<?php
}
?>