<?
define('EMAIL_CRLF', "\r\n");

class CUtils // use as namespace
{
    // ------------------------------------------------------------
    function send_email($from, $to, $subject, $message, $tv = null, $add_headers = null, $attachments = null)
    {
        global $app;
        global $AdministratorEmail;
        global $SiteUrl;

        $real_tv = (!is_null($tv))?($tv):($app->tv);
        $real_from = (!is_null($from))?( preg_match('/@/', $from)?($from):($real_tv[$from]) ):($AdministratorEmail);
        $real_to = (!is_null($to))?( preg_match('/@/', $to)?($to):($real_tv[$to]) ):($AdministratorEmail);

        $email_template = $message;
        $email_message_enabled = true;
		$email_message_template = $email_template;
        $email_message_template = CTemplate::parse_string($email_message_template, $real_tv);
        $HeadersText = '';
        if (is_null($attachments))
        {
            $HeadersText .= 'MIME-Version: 1.0' . EMAIL_CRLF;
            $HeadersText .= 'Content-type: text/html; charset=utf-8' . EMAIL_CRLF;
        }
        else
        {
            $HeadersText .= 'MIME-Version: 1.0' . EMAIL_CRLF;
            $HeadersText .= 'Content-Type: multipart/mixed; charset=utf-8; boundary="EMailMessageSplitter"' . EMAIL_CRLF;

            $attachs = '';
            foreach ($attachments->attachments as $idx => $obj)
            {
                $attachs .= '--EMailMessageSplitter' . EMAIL_CRLF;
                $attachs .= 'Content-Type: ' . $obj->type . ';' . EMAIL_CRLF;
                //$attachs .= 'name="' . $obj->file_name . '"' . EMAIL_CRLF;
                $attachs .= 'Content-Transfer-Encoding: base64' . EMAIL_CRLF;
                $attachs .= 'Content-ID: <'.$obj->file_name.'>' . EMAIL_CRLF;
                if ($obj->file_name != '')
                        $attachs .= 'Content-Disposition: attachment; filename="'.$obj->file_name.'"' . EMAIL_CRLF;

                $attachs .= EMAIL_CRLF;
                $attachs .= base64_encode($obj->content);
                $attachs .= EMAIL_CRLF;
            }

            $email_message_template = '--EMailMessageSplitter' . EMAIL_CRLF . 'Content-type: text/html; charset=utf-8' . EMAIL_CRLF . EMAIL_CRLF . $email_message_template . $attachs . '--EMailMessageSplitter--' . EMAIL_CRLF;
        }

        if (preg_match('/</', $real_from))
            $HeadersText .= 'From: ' . $real_from;
        else
            $HeadersText .= 'From: "' . $real_from . '" <' . $real_from . '>';
        if (is_array($add_headers))
            foreach ($add_headers as $k => $v)
                $HeadersText .= EMAIL_CRLF . $v;

        if ($email_message_enabled){
            $GLOBALS['GlobalDebugInfo']->Write("SENDMAIL: $real_from | $real_to | $subject ");
            $result = true;
            $emails = explode(';', $real_to);
            for ($i=0; $i<count($emails); $i++)
            {
                $email_address = trim($emails[$i]);
                if (strlen($email_address) > 0) {
                	$result &= @mail($email_address, $subject, $email_message_template, $HeadersText);
                }
            }
            return $result;
        }else
        	return FALSE;
	
    } // end of send_email

    function entitiesToString($str)
    {
        if (trim(strval($str)) == '') return '';
		$s = array('/&rsquo;/i', '/&ldquo;/i', '/&rdquo;/i');
	
		$r = array('\'', '"', '"');
	
		$str = preg_replace($s, $r, strval($str));
	
		if (@function_exists('html_entity_decode'))
                $str = @html_entity_decode($str);
        else
        {
            $trans_tbl = get_html_translation_table(HTML_ENTITIES);
            $trans_tbl = array_flip($trans_tbl);
            $str = strtr($str, $trans_tbl);
        }
        return $str;
    }
}

class CAttachments
{
    var $attachments;

    function CAttachments()
    {
        $this->attachments = array();
    }

    function AddAttachment(&$attach)
    {
        $this->attachments[] = $attach;
    }
}

class CAttachment
{
    var $file_name;
    var $content;
    var $type;

    function CAttachment()
    {
            $this->content = '';
            $this->type = '';
    }

    function LoadFile($file_name, $type, $new_name = null)
    {
        $fh = @fopen($file_name, 'rb');
        if ($fh)
        {
            $pos = strrpos($file_name, '/');
            if ($pos === false)
                $pos = strrpos($file_name, '\\');
        	if (is_null($new_name))
	            $this->file_name = substr($file_name, $pos+1);
        	else
				$this->file_name = $new_name;
			$this->content = fread($fh, filesize($file_name));
			$this->type = $type;
			fclose($fh);
			return true;
		}
        return false;
    }

    function LoadString($file_name, $file_content, $type)
    {
        $this->file_name = $file_name;
        $this->content = $file_content;
        $this->type = $type;
    }
}


?>