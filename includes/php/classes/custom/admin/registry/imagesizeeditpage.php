<?
require_once(CUSTOM_CLASSES_PATH . 'admin/mastereditpage.php');

class CImageSizeEditPage extends CMasterEditPage
{
    /**
     * The table name.
     *
     * @var array
     */
    protected $_table = 'image_size';
    
    protected $_module = 'Images';

    function CImageSizeEditPage(&$app, $template)
	{
		$this->IsSecure = true;
		parent::CMasterEditPage($app, $template);
		$this->Images = $this->Application->get_module('Images');
	}

	function on_page_init()
	{
		$this->tv['image_id'] = InGet('image_id');
		parent::on_page_init();
		CValidator::add('image_id', VRT_TEXT, false, 0, 255);
		CValidator::add('system_key', VRT_REGEXP, false, '/^[a-z0-9-_]{1,64}$/');
		CValidator::add('image_width', VRT_NUMBER, false, 1, 10000);
		CValidator::add('image_height', VRT_NUMBER, false, 1, 10000);
		CValidator::add('thumbnail_method', VRT_NUMBER, false, 0, 5);
		CInput::set_select_data('thumbnail_method', array(THUMBNAIL_METHOD_SCALE_MAX => 'SCALE MAX', THUMBNAIL_METHOD_SCALE_MIN => 'SCALE MIN', THUMBNAIL_METHOD_CROP => 'CROP', THUMBNAIL_METHOD_FIT => 'FIT'));
	}

	function parse_data()
	{
		if (!parent::parse_data())
			return false;
		
		return true;
	}
	
	function on_image_size_submit($action) {
		
		if ($action == 'close') {
			$this->Application->CurrentPage->internalRedirect($this->Application->Navi->getUri('parent', false));
		}
		else {
			if (CValidator::validate_input()) {
				if ($this->id) {
					if ($this->Images->update_image_size($this->id, $this->tv)) {
						$this->tv['_info'] = $this->Localizer->get_string('object_updated');
						$this->tv['_return_to'] =  $this->Application->Navi->getUri('parent', false);
					}
					else {
						$this->tv['_errors'] = $this->Hotel->get_last_error();
					}
				}
				else {
					if ($this->tv['id'] = $this->Images->add_image_size($this->tv)) {
						$this->tv['_info'] = $this->Localizer->get_string('object_added');
						$this->tv['_return_to'] =  $this->Application->Navi->getUri('parent', false);
					}
					else {
						$this->tv['_errors'] = $this->Hotel->get_last_error();
					}
				}
			}
			else {
				$this->tv['_errors'] = CValidator::get_errors();
			}
			$this->bind_data();
		}
	}
}
?>