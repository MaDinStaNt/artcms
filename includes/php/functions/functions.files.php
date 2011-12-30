<?
/**
 * @package Art-cms
 */
/*
--------------------------------------------------------------------------------
Filesystem functions v 1.0

history:
        v 1.0.0 - created (AT)
--------------------------------------------------------------------------------
*/
/**
 */
function deldir($dir){
        if (!is_dir($dir)) return;
        $current_dir = @opendir($dir);
        while($entryname = @readdir($current_dir))
                if (@is_dir($dir.'/'.$entryname) && ($entryname != '.') && ($entryname!='..') )
                        deldir($dir.'/'.$entryname);
                elseif ($entryname != ('.') && ($entryname!='..') )
                        @unlink($dir.'/'.$entryname);
        @closedir($current_dir);
        @rmdir($dir);
}

function deldirex($dir){
	exec("sudo rm -fr ".str_replace(' ', '\ ', $dir), $out, $retutn);
}

function movedir($dir_from, $dir_to){
	exec("sudo mv -f ".$dir_from." ".$dir_to, $out, $retutn);
}


function dirtoutf8($dir){
        if (!is_dir($dir)) return;
        $current_dir = @opendir($dir);
        while($entryname = @readdir($current_dir))
                if (@is_dir($dir.'/'.$entryname) && ($entryname != '.') && ($entryname!='..') )
                        dirtoutf8($dir.'/'.$entryname);
                elseif ($entryname != ('.') && ($entryname!='..') )
                        echo($dir.'/'.$entryname);
        @closedir($current_dir);
        @rmdir($dir);
}

function _dir_copy($oldname, $newname)
{
    if(is_file($oldname)){
        $perms = fileperms($oldname);
            return copy($oldname, $newname) && chmod($newname, $perms);
        }
        elseif(is_dir($oldname)){
        return dir_copy($oldname, $newname);
        }
        else{
            //die("Cannot copy file: $oldname");
        }
}

function dir_copy($oldname, $newname)
{
    if(!is_dir($oldname)) return false;
        if(!is_dir($newname)) @mkdir($newname, 0777);
        $dir = opendir($oldname);
    $res = true;
        while($file = readdir($dir)){
            if($file == "." || $file == "..") continue;
        $res = _dir_copy("$oldname/$file", "$newname/$file");
        if ( $res !== true ) {echox("$oldname/$file"); break;}
        }
        closedir($dir);
    return $res;
}

function image_name($name, $name_old, &$data, $uniqname = true){

    if (isset($GLOBALS['import_running'])) {return $data[$name];}

        if ( InGetPost( $name.'_del', '' ) == 1) {
        $data[$name] = '';
    }else{
            if (!isset($data[$name]) || $data[$name] == '' ) {
                    if (is_object($name_old))
                            $name_old = !$name_old->eof() ? $name_old->get_field($name) : '';
                    elseif (is_array($name_old))
                            $name_old = (isset($name_old['$name'])) ? $name_old['$name'] : '';

            $data[$name] = $name_old;
                        $data[$name.'_old'] = true;
        }else{
                if ($uniqname){
                    $mark = microtime();
                    $mark = substr($mark,11,11).substr($mark,2,6);
                    $ext = strrchr($data[$name], '.');
                    $data[$name] = $mark.(($ext===false)?'':$ext);
                }
        }
    }
    //echox($data[$name]);
    return $data[$name];
}

function image_process($name, $name_old, $path, &$data){

            if (isset($GLOBALS['import_running'])) return true;

            if (is_object($name_old))
                $name_old = !$name_old->eof() ? $name_old->get_field($name) : '';
            elseif (is_array($name_old))
                $name_old = (isset($name_old['$name'])) ? $name_old['$name'] : $name_old;

                if (!isset($data[$name.'_old']) || $data[$name.'_old'] !== true ) {
                if ( $name_old != '' && file_exists( $GLOBALS['FilePath'].$path.$name_old ) ){
                     chmod($GLOBALS['FilePath'].$path.$name_old, 0777);
                     @unlink ($GLOBALS['FilePath'].$path.$name_old );
                }
                if (isset($data[$name]) && $data[$name] != '' ) {
                    $fh = fopen($GLOBALS['FilePath'].$path.$data[$name] , 'wb');
                    fwrite($fh,  $data[$name.'_file_content'] );
                    fclose($fh);
                    chmod($GLOBALS['FilePath'].$path.$data[$name], 0777);
                }
        }

}

function display_perms( $mode )
{
           // From PHP Manual

     /* Determine Type */
     if( $mode & 0x1000 )
        $type='p'; /* FIFO pipe */
     else if( $mode & 0x2000 )
        $type='c'; /* Character special */
     else if( $mode & 0x4000 )
        $type='d'; /* Directory */
     else if( $mode & 0x6000 )
        $type='b'; /* Block special */
     else if( $mode & 0x8000 )
        $type='-'; /* Regular */
     else if( $mode & 0xA000 )
        $type='l'; /* Symbolic Link */
     else if( $mode & 0xC000 )
        $type='s'; /* Socket */
     else
        $type='u'; /* UNKNOWN */
     /* Determine permissions */
     $owner["read"]    = ($mode & 00400) ? 'r' : '-';
     $owner["write"]   = ($mode & 00200) ? 'w' : '-';
     $owner["execute"] = ($mode & 00100) ? 'x' : '-';
     $group["read"]    = ($mode & 00040) ? 'r' : '-';
     $group["write"]   = ($mode & 00020) ? 'w' : '-';
     $group["execute"] = ($mode & 00010) ? 'x' : '-';
     $world["read"]    = ($mode & 00004) ? 'r' : '-';
     $world["write"]   = ($mode & 00002) ? 'w' : '-';
     $world["execute"] = ($mode & 00001) ? 'x' : '-';
     /* Adjust for SUID, SGID and sticky bit */
     if( $mode & 0x800 )
        $owner["execute"] = ($owner['execute']=='x') ? 's' : 'S';
     if( $mode & 0x400 )
        $group["execute"] = ($group['execute']=='x') ? 's' : 'S';
     if( $mode & 0x200 )
        $world["execute"] = ($world['execute']=='x') ? 't' : 'T';
     printf("%1s", $type);
     printf("%1s%1s%1s", $owner['read'], $owner['write'], $owner['execute']);
     printf("%1s%1s%1s", $group['read'], $group['write'], $group['execute']);
     printf("%1s%1s%1s\n", $world['read'], $world['write'], $world['execute']);
}

function get_filesize($file_path)
{
	exec("ls -l \"".$file_path."\"", $arr, $err);
	if (isset($arr[0]))
	{
		$res_arr = explode(" ", $arr[0]);
		return $res_arr[4];
	}
	else return 0;
}

function get_filescount($path) {
	$count = 0;
    $current_dir = @opendir($path);
    while($entryname = @readdir($current_dir)) {
        if ((!is_dir($path.'/'.$entryname)) && $entryname != ('.') && ($entryname!='..') ) {
        	$count++;
        }
    }
    @closedir($current_dir);
    return $count;
}

function set_chown($path) {
	exec("sudo chown -hR classycontent:psacln ".str_replace(' ', '\ ', $path), $out, $retutn);
}

function set_chmod($path) {
	exec("sudo chmod -R 777 ".str_replace(' ', '\ ', $path), $out, $retutn);
}

function makedir($dir) {
	$path = '/';
	$dir_arr = explode('/', $dir);
	if (sizeof($dir_arr) > 0) {
		foreach ($dir_arr as $part) {
			if (strlen($part) > 0) {
				if ($part[1] == ':') {
					$path = $part . '/';
				}
				else {
					$path .= $part . '/';
				}
				if (!file_exists($path)) {
					@mkdir($path);
					set_chown($path);
					set_chmod($path);
				}
			}
		}
	}
}

function dirsize($dir) {
	static $size = 0;
    if (!is_dir($dir)) return;
    $current_dir = @opendir($dir);
    while($entryname = @readdir($current_dir))
            if (@is_dir($dir.'/'.$entryname) && ($entryname != '.') && ($entryname!='..') )
                    dirsize($dir.'/'.$entryname);
            elseif ($entryname != ('.') && ($entryname!='..') )
                    $size = $size + filesize($dir.'/'.$entryname);
    @closedir($current_dir);
    return $size;
}

function unique_filename($file_name) {
    $mark = microtime();
    $mark = substr($mark, 11, 11).substr($mark, 2, 6);
    $ext = strrchr($file_name, '.');
    return $mark.(($ext === false)?'':$ext);
}
?>