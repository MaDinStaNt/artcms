<?php
/**
 * @package Art-cms
 */
/**
 */
require_once('debuginfo.php');
/**
 * Base class for all objects, allows access to $this->DebugInfo object
 * @package Art-cms
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