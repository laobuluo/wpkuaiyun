<?php
/**
Plugin Name: wpkuaiyun
Plugin URI: https://www.laobuluo.com/2341.html
Description: WordPress同步图片、媒体附件内容远程至景安快云对象存储中，实现网站数据与静态资源分离，提高网站加载速度。可以设置将静态文件同步在本地和对象存储，也可以只存储在对象存储中。这个插件至适合景安快云对象存储。【如果需要腾讯云COS、阿里云OSS 插件可以到我们网站下载对应插件】站长互助QQ群： <a href="https://jq.qq.com/?_wv=1027&k=5gBE7Pt" target="_blank"> <font color="red">594467847</font></a>
Version: 0.1
Author: 老部落（BY:老赵）
Author URI: https://www.laobuluo.com
*/

require_once 'wpkuaiyun_actions.php';


# 插件 activation 函数当一个插件在 WordPress 中”activated(启用)”时被触发。
register_activation_hook(__FILE__, 'wpkuaiyun_set_options');

# 避免上传插件/主题被同步到对象存储
if (substr_count($_SERVER['REQUEST_URI'], '/update.php') <= 0) {
	add_filter('wp_handle_upload', 'wpkuaiyun_upload_attachments');
	add_filter('wp_generate_attachment_metadata', 'wpkuaiyun_upload_thumbs');
}

# 删除文件时触发删除远端文件，该删除会默认删除缩略图
add_action('delete_attachment', 'wpkuaiyun_delete_remote_attachment');

# 添加插件设置菜单
add_action('admin_menu', 'wpkuaiyun_add_setting_page');
