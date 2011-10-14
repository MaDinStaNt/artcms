<?
function date_2_rus($date_odbc, $return_format = 0)
{
    /*2008-07-24 23:50:34*/
    /*$return_format 0-all 1-date*/
    /*30 Окт 2008 21:24*/
    $date_odbc_arr = explode(" ", $date_odbc);
    $date_arr = explode("-", $date_odbc_arr[0]);
    $time_arr = explode(":", $date_odbc_arr[1]);
    if ($date_arr[1] == "01") $month = "Янв";
    if ($date_arr[1] == "02") $month = "Фев";
    if ($date_arr[1] == "03") $month = "Мар";
    if ($date_arr[1] == "04") $month = "Апр";
    if ($date_arr[1] == "05") $month = "Май";
    if ($date_arr[1] == "06") $month = "�?юн";
    if ($date_arr[1] == "07") $month = "�?юл";
    if ($date_arr[1] == "08") $month = "Авг";
    if ($date_arr[1] == "09") $month = "Сен";
    if ($date_arr[1] == "10") $month = "Окт";
    if ($date_arr[1] == "11") $month = "Ноя";
    if ($date_arr[1] == "12") $month = "Дек";

    if ($return_format == 0)
    {
    	return $date_arr[2] . " " . $month . " " . $date_arr[0] . " " . $time_arr[0] . ":" . $time_arr[1];
    }
    elseif ($return_format == 1)
    {
    	return $date_arr[2] . " " . $month . " " . $date_arr[0];
    }
}

function format_name($first, $middle, $last)
{
        $out = '';
        if ($first != '') $out .= $first;
        if ($middle != '') $out .= ( ($out!='')?(' '):('') ) . $middle;
        if ($last != '')$out .= ( ($out!='')?(' '):('') ) . $last;
        return $out;
}
/**
 * formats credit card number
 * @return string
 */
function format_gen($gen, $use_html_chars = true)
{
        require_once(BASE_CLASSES_PATH . 'components/credit_card.php');
        $gen = CCreditCard::get_normalize_cc($gen);
        if (strlen($gen)<4)
                return 'invalid';
        $char = ($use_html_chars)?('&times;'):('x');
        $c = explode("\r\n", chunk_split(str_repeat($char, strlen($gen)-4), 4*strlen($char)));
        array_pop($c);
        $c[] = substr($gen, -4);
        return implode(($use_html_chars)?('&ndash;'):('-'), $c);
}
/**
 * Format dates & intervals
 *
 * @param array ODBC $dates
 * @param string $format
 * @return string
 */
function format_dates_intervals( $dates, $format='d.m.Y' ){
        foreach ( $dates as $v ){
                        $tmp_ar = explode( ' ', $v );
                        if (isset($tmp_ar[0]))
                            $ard = explode( '-', $tmp_ar[0] );
                        else
                            $ard[0] = $ard[1] = $ard[2] = 0;
                        if (isset($tmp_ar[1]))
                            $art = explode( ':', $tmp_ar[1] );
                        else
                            $art[0] = $art[1] = $art[2] = 0;
                        $d = mktime( $art[0], $art[1], $art[2], $ard[1], $ard[2], $ard[0] );
                        $dar[] = $d;
                }
                sort( $dar );
                $res = array();
                $i = 0;
                echo $day;
                while ( count( $dar ) > 0 ){
                        while(  $i < count( $dar )  ){
                                $ar = getdate( $dar[$i] );
                                if ( mktime( 0, 0, 0, $ar['mon'], $ar['mday']+1, $ar['year'] ) == $dar[$i+1] )
                                        $i++;
                                else {
                                        $i++;
                                        break;
                                }
                        }
                        if ( $i > 1 )
                                $res[] = date( $format, $dar[0] ) . ' - ' . date( $format,$dar[$i-1] );
                        else
                                $res[] = date( $format, $dar[0] );
                        $ar = array();
                        while ( $i < count( $dar ) ){
                                $ar[] = $dar[$i];
                                $i++;
                        }
                        $dar = $ar;
                        $i = 0;
                }
                $s =implode( ', ', $res );
                return $s;
}

function to_odbc_date($date) {
	if (strlen($date) > 0) {
		$date_arr = explode("/", $date);
		return $date_arr[2]."-".$date_arr[1]."-".$date_arr[0];
	}
	else return $date;
}

function from_odbc_date($date) {
	if (strlen($date) > 0) {
		$date_arr = explode("-", $date);
		return $date_arr[2]."/".$date_arr[1]."/".$date_arr[0];
	}
	else return $date;
}
function prepare_uri_from_title($title) {
	$title = strtolower($title);
	$title = trim($title);
	$title = str_replace('.','',$title);
	$title = str_replace(' ','-',$title);
	$title = str_replace('-','-',$title);
	$title = str_replace('"','',$title);
	$title = str_replace('$','',$title);
	$title = str_replace('%','',$title);
	$title = str_replace('^','',$title);
	$title = str_replace('\'','-',$title);
	$title = str_replace('&','-and-',$title);
	$title = str_replace('&amp;','-and-',$title);
	$title = str_replace('?','',$title);
	//echo $title;
	$title = preg_replace("/\W+/si","-", $title);
	//echo $title[strlen($title)-1];
	while ($title[strlen($title)-1] == '-') {
		$title = substr($title, 0, strlen($title)-1);
	}
	//echo $title;
	//exit;
	return $title;
}

?>