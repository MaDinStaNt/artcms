<?php
/**
 * @package PACATUM.Base
 */
/**
 */
function InPost($VarName, $DefValue = ''){
	return ((isset($_POST[$VarName]))?(utf16parse($_POST[$VarName])):($DefValue));
}
function InGet($VarName, $DefValue = ''){
	return ((isset($_GET[$VarName]))?(utf16parse($_GET[$VarName])):($DefValue));
}
function InUri($VarName, $DefValue = ''){
	global $app;
	if (intval($VarName) > 0) {
		$uri_arr = array_values($app->Router->Variables);
		return ((isset($uri_arr[$VarName - 1]))?(utf16parse($uri_arr[$VarName - 1])):($DefValue));
	}
	else {
		return ((isset($app->Router->Variables[$VarName]))?(utf16parse($app->Router->Variables[$VarName])):($DefValue));
	}
}
function InCookie($VarName, $DefValue = ''){
	return ((isset($_COOKIE[$VarName]))?(($_COOKIE[$VarName])):($DefValue));
}
function InCache($VarName, $DefValue = '', $CacheId = 'common'){
	return ((isset($_SESSION['cache'][$CacheId][$VarName]))?(($_SESSION['cache'][$CacheId][$VarName])):($DefValue));
}
function InGetPost($VarName, $DefValue = ''){
	return ((isset($_GET[$VarName]))?(utf16parse($_GET[$VarName])):((isset($_POST[$VarName]))?(utf16parse($_POST[$VarName])):($DefValue)));
}
function InPostGet($VarName, $DefValue = ''){
	return ((isset($_POST[$VarName]))?(utf16parse($_POST[$VarName])):((isset($_GET[$VarName]))?(utf16parse($_GET[$VarName])):($DefValue)));
}
function InGetPostCache($VarName, $DefValue = '', $CacheId = 'common'){
	return ((isset($_GET[$VarName]))?(utf16parse($_GET[$VarName])):((isset($_POST[$VarName]))?(utf16parse($_POST[$VarName])):((isset($_SESSION['cache'][$CacheId][$VarName]))?(($_SESSION['cache'][$CacheId][$VarName])):($DefValue))));
}
function InPostGetCache($VarName, $DefValue = '', $CacheId = 'common'){
	return ((isset($_POST[$VarName]))?(utf16parse($_POST[$VarName])):((isset($_GET[$VarName]))?(utf16parse($_GET[$VarName])):((isset($_SESSION['cache'][$CacheId][$VarName]))?(($_SESSION['cache'][$CacheId][$VarName])):($DefValue))));
}
?>