<?php
/**
 * @package Art-cms
 */
if (!defined('__GLOBAL_DEBUG_INFO_INCLUDED')) {
/**
 * Class connected with debug information
 * Use this class through the $DebugInfo member of CObject class, ie:<br>
 *     $this->DebugInfo->Write('string to append in debug log', level of this string - smaller level means greater priority)
 *
 * history:<br>
 *
 * @package Art-cms
 * @version 1.0
 */
class CDebugInfo {
        /**
         * @access private
         * @var array
         */
        var $DebugString = array();
        /**
         * Constructor
         */
        function CDebugInfo() {}
        /**
         * Writes a string to output debug information
         * @param string $str
         * @param integer $dbg_lvl
         * @access public
         *
         * @uses get_formatted_microtime()
         */
        function Write($str, $dbg_lvl = 255) {
                if ($dbg_lvl <= $GLOBALS['DebugLevel'])
                        $this->DebugString[] = (get_formatted_microtime()-$GLOBALS['_t1']).' ' . $str;
        }
        /**
         * Outputs debug information
         * @access public
         *
         * @uses get_formatted_microtime()
        */
        function OutPut() {
                if (!$GLOBALS['DebugLevel']) return;
                $_t2 = get_formatted_microtime();
                echo '<div style="background-color: white; color: black;clear:both;"><hr>';
                echo 'Execution time: '.($_t2-$GLOBALS['_t1']).BR;
                echo '<b>SQL Debug '.((count($this->DebugString) > 20) ? "<span style=\"color:red;\">" : "<span style=\"color:green;\">").'('.count($this->DebugString).' queries)</span>:</b><br />';
                for ($i=0; $i<count($this->DebugString); $i++)
                        echo ($this->DebugString[$i]).'<br />';
                
                
                echo '<b>_POST Variables:</b><br />';
                foreach ($_POST as $k => $v) if (strcasecmp($k, '_it_fs') != 0) echo htmlspecialchars($k) . ': ' . $this->_output_value($v) . '<br />';
                echo '<b>_GET Variables:</b><br />';
                foreach ($_GET as $k => $v) echo htmlspecialchars($k) . ': ' . $this->_output_value($v) . '<br />';
				echo '<b>_URI Variables:</b><br />';
				if (isset($GLOBALS['app']->Router->Variables)) {
					foreach ($GLOBALS['app']->Router->Variables as $k => $v) echo htmlspecialchars($k) . ': ' . $this->_output_value($v) . '<br />';
					echo '<b>Current Page:</b><br />';
	                echo 'template: ' . $GLOBALS['app']->Router->CurrentPage['template'] . '<br />';
	                echo 'class: ' . $GLOBALS['app']->Router->CurrentPage['class'] . '<br />';
	                echo 'php_path: ' . $GLOBALS['app']->Router->CurrentPage['php_path'] . '<br />';
	                echo 'tpl_path: ' . $GLOBALS['app']->Router->CurrentPage['tpl_path'] . '<br />';
					echo '<b>_SESSION Variables:</b><br />';
				}

				
                echo htmlspecialchars(session_name()) . ': ';
                print_r(session_id());
                echo '<br />';

                foreach ($_SESSION as $k => $v) {
                        echo '<pre>' . htmlspecialchars($k) . ': ';
                        print_r($v);
                        echo '</pre>';
                }
                echo '<b>_COOKIE Variables:</b><br />';
                foreach ($_COOKIE as $k => $v) echo htmlspecialchars($k) . ': ' . $this->_output_value($v) . '<br />';
                echo '<b>_FILES Variables:</b><br />';
                foreach ($_FILES as $k => $v) {
                        echo '<pre>' . htmlspecialchars($k) . ': ';
                        print_r($v);
                        echo '</pre>';
                }
                /*if (is_object($GLOBALS['app']->CurrentPage)) {
                        echo '<b>Controls:</b><br />';
                        foreach ($GLOBALS['app']->CurrentPage->m_Controls as $k => $v) echo htmlspecialchars(get_class($GLOBALS['app']->CurrentPage->m_Controls[$k])) . '<br />';
                }*/
                echo '<b>_SERVER Variables:</b><br />';
                foreach ($_SERVER as $k => $v) echo htmlspecialchars($k) . ': ' . $this->_output_value($v) . '<br />';
                echo '</div>';
        }
        /**
         * @access private
         */
        function _output_value($v)
        {
                if (is_array($v))
                {
                        $s = '';
                        foreach($v as $idx => $vlv)
                                $s .= (($s!='')?(','):('Array(')).$this->_output_value($vlv);
                        $s .= ')';
                        return $s;
                }
                else
                        return htmlspecialchars($v);
        }
}
define('__GLOBAL_DEBUG_INFO_INCLUDED', true);
$GLOBALS['GlobalDebugInfo'] = new CDebugInfo();
}
else
        system_die('__GLOBAL_DEBUG_INFO_INCLUDED');
?>