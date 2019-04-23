<?php
require_once 'wpkuaiyun_api.php';


define( 'WPKUAIYUN_VERSION', '0.1' );
define( 'WPKUAIYUN_MINIMUM_WP_VERSION', '4.0' );  // 最早WP版本
define( 'WPKUAIYUN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );  // 插件路径
define('WPKUAIYUN_BASENAME', plugin_basename(__FILE__));
define('WPKUAIYUN_BASEFOLDER', plugin_basename(dirname(__FILE__)));

// 初始化选项
function wpkuaiyun_set_options() {
	$options = array(
		'bucketName' => "demobkt",  // 用户的空间名称
		'accessKey' => "I1I7CLU4708LI8XQ0ODL",  // 用户秘钥对：开通快云存储时的Access_Key，可在会员中心获取
		'secretKey' => "N41hrYy14gI03RpnZijfezGl/VUtZP3b77vjF6nn",// 用户秘钥对：开通快云存储时的Secret_Key，可在会员中心获取
        'resource' => "VG3OQ81wtrNGANeuq8IdwWxGFK0xdA3X", // API调用来源，可在会员中心点击获取
        'voucher' => "dcb99d196dd2418ec1fdfa7269ee4b81", // 用户通过accesskey和secretkey获取的，可在会员中心点击，以邮件形式获取
		'no_local_file' => "false",  # 不在本地保留备份
        'token' => array(
            'value' => "",  // 用户操作秘钥，是用户调用api接口时必须用到的的秘钥；可以通过API获取
            'express' => '',  // 过期时间
        ),  // 用户操作秘钥，是用户调用api接口时必须用到的的秘钥；可以通过API获取
	);
	if(!get_option('wpkuaiyun_options', False)){
		add_option('wpkuaiyun_options', $options, '', 'yes');
	};
}


/**
 * 删除本地文件
 * @param $file_path : 文件路径
 * @return bool
 */
function wpkuaiyun_delete_local_file($file_path) {
	try {
		# 文件不存在
		if (!@file_exists($file_path)) {
			return TRUE;
		}
		# 删除文件
		if (!@unlink($file_path)) {
			return FALSE;
		}
		return TRUE;
	} catch (Exception $ex) {
		return FALSE;
	}
}


/**
 * 删除附件（包括图片的原图）
 * @param $post_id
 */
function wpkuaiyun_delete_remote_attachment($post_id) {
	$meta = wp_get_attachment_metadata( $post_id );

	if (isset($meta['file'])) {
		// meta['file']的格式为 "2011/12/press_image.jpg"
		$wp_uploads = wp_upload_dir();
		// 示例: [basedir] => C:\path\to\wordpress\wp-content\uploads
		$file_path = $wp_uploads['basedir'] . '/' . $meta['file'];

		// 得到远程路径, get_home_path 示例： "Path: /var/www/htdocs/" or "Path: /var/www/htdocs/wordpress/"
		wpkuaiyun_del_file(str_replace(get_home_path(), '', str_replace("\\", '/', $file_path)));

		if (isset($meta['sizes']) && count($meta['sizes']) > 0) {
			foreach ($meta['sizes'] as $val) {
				$size_file = dirname($file_path) . '/' . $val['file'];
				wpkuaiyun_del_file(str_replace(get_home_path(), '', str_replace("\\", '/', $size_file)));
			}
		}
	}
}


/**
 * 上传附件（包括图片的原图）
 * @param $metadata
 * @return array()
 */
function wpkuaiyun_upload_attachments($metadata) {
	# 生成object在OSS中的存储路径
	if (get_option('upload_path') == '.') {
		//如果含有“./”则去除之
		$metadata['file'] = str_replace("./", '', $metadata['file']);
	}
	# 必须先替换\\, 因为get_home_path的输出格式为 "Path: /var/www/htdocs/" or "Path: /var/www/htdocs/wordpress/"
	$key = str_replace(get_home_path(), '', str_replace("\\", '/', $metadata['file']));;

	# 在本地的存储路径
	$file = get_home_path() . $key;  //早期版本 $metadata['file'] 为相对路径

	# 调用上传函数
	wpkuaiyun_send_file($file, $key);

	return $metadata;
}


/**
 * 上传图片的缩略图
 * @param $metadata
 * @return array
 */
function wpkuaiyun_upload_thumbs($metadata) {
	# 上传所有缩略图
	if (isset($metadata['sizes']) && count($metadata['sizes']) > 0) {

		$wpkuaiyun_options = get_option('wpkuaiyun_options', True);

		# 若不上传缩略图则直接返回
		if (esc_attr($wpkuaiyun_options['no_remote_thumb']) == 'true') {
			return $metadata;
		}

		# 获取上传路径
		$wp_uploads = wp_upload_dir();
		//得到本地文件夹和远端文件夹
		$file_path = $wp_uploads['basedir'] . '/' . dirname($metadata['file']) . '/';
		if (get_option('upload_path') == '.') {
			$file_path = str_replace(get_home_path() . "./", '', str_replace("\\", '/', $file_path));
		} else {
			$file_path = str_replace("\\", '/', $file_path);
		}

		// 文件名可能相同，上传操作时会判断是否存在，如果存在则不会执行上传。
		foreach ($metadata['sizes'] as $val) {
			//生成object在COS中的存储路径
			$key = str_replace(get_home_path(), '', $file_path) . $val['file'];
			//生成本地存储路径
			$file = $file_path . $val['file'];

			//执行上传操作
			wpkuaiyun_send_file($file, $key);

			# 不保存本地文件则删除
			if (esc_attr($wpkuaiyun_options['no_local_file']) == 'true') {
				wpkuaiyun_delete_local_file($file_path . $val['file']);
			}
		}
		// 删除主文件
		if (esc_attr($wpkuaiyun_options['no_local_file']) == 'true') {
			wpkuaiyun_delete_local_file($wp_uploads['basedir'] . '/' . $metadata['file']);
	    }
	}
	
	return $metadata;
}


// 在导航栏“设置”中添加条目
function wpkuaiyun_add_setting_page() {
	if (!function_exists('wpkuaiyun_setting_page')) {
		require_once 'wpkuaiyun_setting_page.php';
	}
	add_menu_page('WPKuaiYun设置', 'WPKuaiYun设置', 'manage_options', __FILE__, 'wpkuaiyun_setting_page');
}
