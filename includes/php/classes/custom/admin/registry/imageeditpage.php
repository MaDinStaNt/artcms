<?
require_once(CUSTOM_CLASSES_PATH . 'admin/mastereditpage.php');

class CImageEditPage extends CMasterEditPage
{
    /**
     * The table name.
     *
     * @var array
     */
    protected $_table = 'image';
    
    protected $_module = 'Images';

    function CImageEditPage(&$app, $template)
	{
		$this->IsSecure = true;
		parent::CMasterEditPage($app, $template);
		$this->Images = $this->Application->get_module('Images');
	}

	function on_page_init()
	{
		parent::on_page_init();
		CValidator::add('title', VRT_TEXT, false, 0, 255);
		CValidator::add('system_key', VRT_REGEXP, false, '/^[a-z0-9-_]{1,64}$/');
		CValidator::add('path', VRT_REGEXP, false, '/^[a-z0-9_\/\{\}]{1,255}$/');
	}

	function parse_data()
	{
		if (!parent::parse_data())
			return false;
			
		$this->bind_image_sizes();
		
		return true;
	}
	
	function bind_image_sizes() {
        $query = "SELECT id,
			image_width,
			image_height,
			thumbnail_method
            FROM %prefix%image_size
            WHERE image_id = ".$this->id;

        require_once(BASE_CLASSES_PATH . 'controls/dbnavigator.php'); // base application class
        $nav = new DBNavigator('image_size', $query, array('image_width', 'image_height', 'thumbnail_method'), 'id', false);
        $nav->set_row_clickaction($this->Application->get_module('Navi')->getUri().".image_size_edit&image_id={$this->id}&id=");
        $nav->set_table('image_size');
        
        $header_num = $nav->add_header('image_width');
        $nav->headers[$header_num]->set_title($this->Application->Localizer->get_string('width'));
        $nav->headers[$header_num]->set_width = "34%";
        
        $header_num = $nav->add_header('image_height');
        $nav->headers[$header_num]->set_title($this->Application->Localizer->get_string('height'));
        $nav->headers[$header_num]->set_width = "33%";
        
        $header_num = $nav->add_header('thumbnail_method');
        $nav->headers[$header_num]->set_title($this->Application->Localizer->get_string('thumbnail_method'));
        $nav->headers[$header_num]->set_width = "33%";

        if($nav->size > 0)
        	$this->tv['remove_btn_show'] = true;
        else
        	$this->tv['remove_btn_show'] = false;
	}
	
	public function on_image_submit($action) {
		switch ($action) {
			case 'add_image_size':
				$this->Application->CurrentPage->internalRedirect($this->Application->Navi->getUri('./image_size_edit/', true) . 'image_id=' . $this->id);
			break;
			case 'delete_selected_objects':
				$ids = InGetPost("image_size_res", array());
	            $where="WHERE 1=1";
	            if (strlen($ids) > 0 && $ids !== '[]') {
            		$where .= " AND id in ({$ids}) ";
	                $sql = 'DELETE FROM %prefix%image_size ' . $where;
	                if($this->Application->DataBase->select_custom_sql($sql)) {
	                	$this->tv['_info'][] = $this->Application->Localizer->get_string('objects_deleted');
	                } 
	                else $this->tv['_errors'][] = $this->Application->Localizer->get_string('internal_error');
	           } 
	           else $this->tv['_info'][] = $this->Application->Localizer->get_string('noitem_selected');
			break;
			case 'close':
				$this->Application->CurrentPage->internalRedirect($this->Application->Navi->getUri('parent', false));
			break;
			default:
				if (CValidator::validate_input()) {
					if ($this->id) {
						if ($this->Images->update_image($this->id, $this->tv)) {
							$this->tv['_info'] = $this->Application->Localizer->get_string('object_updated');
							//$this->tv['_return_to'] =  $this->Application->Navi->getUri('parent', false);
						}
						else {
							$this->tv['_errors'] = $this->Images->get_last_error();
						}
					}
					else {
						if ($this->tv['id'] = $this->Images->add_image($this->tv)) {
							$this->tv['_info'] = $this->Application->Localizer->get_string('object_added');
							$this->tv['_return_to'] =  $this->Application->Navi->getUri('this').'&id='.$this->tv['id'];
						}
						else {
							$this->tv['_errors'] = $this->Images->get_last_error();
						}
					}
				}
				else {
					$this->tv['_errors'] = CValidator::get_errors();
				}
			break;
		}
		$this->bind_image_sizes();
	}
}
?>