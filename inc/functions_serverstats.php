<?php
/**
 * MyBB 1.8
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/about/license
 *
 */

/**
 * @param int    $is_install
 * @param string $prev_version
 * @param string $current_version
 * @param string $charset
 *
 * @return array
 */
function build_server_stats($is_install=1, $prev_version='', $current_version='', $charset='')
{
	$info = array();

	// Is this an upgrade or an install?
	if($is_install == 1)
	{
		$info['is_install'] = 1;
	}
	else
	{
		$info['is_install'] = 0;
	}

	// If we are upgrading....
	if($info['is_install'] == 0)
	{
		// What was the previous version?
		$info['prev_version'] = $prev_version;
	}

	// What's our current version?
	$info['current_version'] = $current_version;

	// What is our current charset?
	$info['charset'] = $charset;

	// Parse phpinfo into array
	$phpinfo = parse_php_info();

	// PHP Version
	$info['phpversion'] = phpversion();

	// MySQL Version
	$info['mysql'] = 0;
	if(isset($phpinfo['mysql']['Client API version']))
	{
		$info['mysql'] = $phpinfo['mysql']['Client API version'];
	}

	// PostgreSQL Version
	$info['pgsql'] = 0;
	if(isset($phpinfo['pgsql']['PostgreSQL(libpq) Version']))
	{
		$info['pgsql'] = $phpinfo['pgsql']['PostgreSQL(libpq) Version'];
	}

	// SQLite Version
	$info['sqlite'] = 0;
	if(isset($phpinfo['sqlite']['SQLite Library']))
	{
		$info['sqlite'] = $phpinfo['sqlite']['SQLite Library'];
	}

	// Iconv Library Extension Version
	$info['iconvlib'] = 0;
	if(isset($phpinfo['iconv']['iconv implementation'], $phpinfo['iconv']['iconv library version']))
	{
		$info['iconvlib'] = html_entity_decode($phpinfo['iconv']['iconv implementation'])."|".$phpinfo['iconv']['iconv library version'];
	}

	// Check GD & Version
	$info['gd'] = 0;
	if(isset($phpinfo['gd']['GD Version']))
	{
		$info['gd'] = $phpinfo['gd']['GD Version'];
	}

	// CGI Mode
	$sapi_type = php_sapi_name();

	$info['cgimode'] = 0;
	if(strpos($sapi_type, 'cgi') !== false)
	{
		$info['cgimode'] = 1;
	}

	// Server Software
	$info['server_software'] = $_SERVER['SERVER_SOFTWARE'];

	// Allow url fopen php.ini setting
	$info['allow_url_fopen'] = 0;
	if(ini_get('safe_mode') == 0 && ini_get('allow_url_fopen'))
	{
		$info['allow_url_fopen'] = 1;
	}

	// Check classes, extensions, php info, functions, and php ini settings
	$check = array(
		'classes' => array(
			'dom' => array('bitwise' => 1, 'title' => 'DOMElement'),
			'soap' => array('bitwise' => 2, 'title' => 'SoapClient'),
			'xmlwriter' => array('bitwise' => 4, 'title' => 'XMLWriter'),
			'imagemagick' => array('bitwise' => 8, 'title' => 'Imagick'),
		),

		'extensions' => array(
			'zendopt' => array('bitwise' => 1, 'title' => 'Zend Optimizer'),
			'xcache' => array('bitwise' => 2, 'title' => 'XCache'),
			'eaccelerator' => array('bitwise' => 4, 'title' => 'eAccelerator'),
			'ioncube' => array('bitwise' => 8, 'title' => 'ionCube Loader'),
			'PDO' => array('bitwise' => 16, 'title' => 'PDO'),
			'pdo_mysql' => array('bitwise' => 32, 'title' => 'pdo_mysql'),
			'pdo_pgsql' => array('bitwise' => 64, 'title' => 'pdo_pgsql'),
			'pdo_sqlite' => array('bitwise' => 128, 'title' => 'pdo_sqlite'),
			'pdo_oci' => array('bitwise' => 256, 'title' => 'pdo_oci'),
			'pdo_odbc' => array('bitwise' => 512, 'title' => 'pdo_odbc'),
		),

		'phpinfo' => array(
			'zlib' => array('bitwise' => 1, 'title' => 'zlib'),
			'mbstring' => array('bitwise' => 2, 'title' => 'mbstring'),
			'exif' => array('bitwise' => 4, 'title' => 'exif'),
		),

		'functions' => array(
			'sockets' => array('bitwise' => 1, 'title' => 'fsockopen'),
			'mcrypt' => array('bitwise' => 2, 'title' => 'mcrypt_encrypt'),
			'simplexml' => array('bitwise' => 4, 'title' => 'simplexml_load_string'),
			'ldap' => array('bitwise' => 8, 'title' => 'ldap_connect'),
			'mysqli' => array('bitwise' => 16, 'title' => 'mysqli_connect'),
			'imap' => array('bitwise' => 32, 'title' => 'imap_open'),
			'ftp' => array('bitwise' => 64, 'title' => 'ftp_login'),
			'pspell' => array('bitwise' => 128, 'title' => 'pspell_new'),
			'apc' => array('bitwise' => 256, 'title' => 'apc_cache_info'),
			'curl' => array('bitwise' => 512, 'title' => 'curl_init'),
			'iconv' => array('bitwise' => 1024, 'title' => 'iconv'),
		),

		'php_ini' => array(
			'post_max_size' => 'post_max_size',
			'upload_max_filesize' => 'upload_max_filesize',
			'safe_mode' => 'safe_mode',
		),
	);

	foreach($check as $cat_name => $category)
	{
		foreach($category as $name => $what)
		{
			if(!isset($info[$cat_name]))
			{
				$info[$cat_name] = 0;
			}
			switch($cat_name)
			{
				case "classes":
					if(class_exists($what['title']))
					{
						$info[$cat_name] |= $what['bitwise'];
					}
					break;
				case "extensions":
					if(extension_loaded($what['title']))
					{
						$info[$cat_name] |= $what['bitwise'];
					}
					break;
				case "phpinfo":
					if(array_key_exists($what['title'], $phpinfo))
					{
						$info[$cat_name] |= $what['bitwise'];
					}
					break;
				case "functions":
					if(function_exists($what['title']))
					{
						$info[$cat_name] |= $what['bitwise'];
					}
					break;
				case "php_ini":
					if(ini_get($what) != 0)
					{
						$info[$name] = ini_get($what);
					}
					else
					{
						$info[$name] = 0;
					}
					break;
			}
		}
	}

	// Host URL & hostname
	// Dropped fetching host details since v.1.8.16 as http://www.whoishostingthis.com API seems to be down and this info is not required by MyBB.
	$info['hosturl'] = $info['hostname'] = "unknown/local";
	if($_SERVER['HTTP_HOST'] == 'localhost')
	{	
		$info['hosturl'] = $info['hostname'] = "localhost";	
	}

	if(isset($_SERVER['HTTP_USER_AGENT']))
	{
		$info['useragent'] = $_SERVER['HTTP_USER_AGENT'];
	}

	// We need a unique ID for the host so hash it to keep it private and send it over
	$id = $_SERVER['HTTP_HOST'].time();

	if(function_exists('sha1'))
	{
		$info['clientid'] = sha1($id);
	}
	else
	{
		$info['clientid'] = md5($id);
	}

	$string = "";
	$amp = "";
	foreach($info as $key => $value)
	{
		$string .= $amp.$key."=".urlencode($value);
		$amp = "&amp;";
	}

	$server_stats_url = 'https://community.mybb.com/server_stats.php?'.$string;

	$return = array();
	$return['info_sent_success'] = false;
	if(fetch_remote_file($server_stats_url) !== false)
	{
		$return['info_sent_success'] = true;
	}
	$return['info_image'] = "<img src='".$server_stats_url."&amp;img=1' />";
	$return['info_get_string'] = $string;

	return $return;
}

/**
* parser_php_info
* Function to get and parse the list of PHP info into a usuable array
*
* @return Array An array of all the extensions installed in PHP
*/
function parse_php_info()
{
	ob_start();
	phpinfo(INFO_MODULES);
	$phpinfo_html = ob_get_contents();
	ob_end_clean();

	$phpinfo_html = strip_tags($phpinfo_html, "<h2><th><td>");
	$phpinfo_html = preg_replace("#<th[^>]*>([^<]+)<\/th>#", "<info>$1</info>", $phpinfo_html);
	$phpinfo_html = preg_replace("#<td[^>]*>([^<]+)<\/td>#", "<info>$1</info>", $phpinfo_html);
	$phpinfo_html = preg_split("#(<h2[^>]*>[^<]+<\/h2>)#", $phpinfo_html, -1, PREG_SPLIT_DELIM_CAPTURE);
	$modules = array();

	for($i=1; $i < count($phpinfo_html); $i++)
	{
		if(preg_match("#<h2[^>]*>([^<]+)<\/h2>#", $phpinfo_html[$i], $match))
		{
			$name = trim($match[1]);
			$tmp2 = explode("\n", $phpinfo_html[$i+1]);
			foreach($tmp2 as $one)
			{
				$pat = '<info>([^<]+)<\/info>';
				$pat3 = "/$pat\s*$pat\s*$pat/";
				$pat2 = "/$pat\s*$pat/";

				// 3 columns
				if(preg_match($pat3, $one, $match))
				{
					$modules[$name][trim($match[1])] = array(trim($match[2]), trim($match[3]));
				}
				// 2 columns
				else if(preg_match($pat2, $one, $match))
				{
					$modules[$name][trim($match[1])] = trim($match[2]);
				}
			}
		}
	}
	return $modules;
}

