<?php
/**
 * @package LLA.Base
 */
/**
 */
require_once('debuginfo.php');
/**
 * Base class for all objects, allows access to $this->DebugInfo object
 * @package LLA.Base
 */
class CObject {
	/**
	 * @access protected
	 */
	var	$DebugInfo;
	/**
	 * Constructor
	 */
	function CObject() {
		$this->DebugInfo = &$GLOBALS['GlobalDebugInfo'];
	}
}
?>