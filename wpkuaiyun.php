<?php
/**1
Plugin Name: WordPress Kuaiyun
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: WordPress同步附件到景安快云对象存储插件。
Version: 0.1
Author: VgZ
Author URI: http://URI_Of_The_Plugin_Author作者地址
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
