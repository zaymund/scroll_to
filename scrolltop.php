<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class scrolltop extends Module
{
	const PREFIX = 'belvg_scrolltop_';

	const DEFAULT_IMG = 'up.png';

	const USER_IMG = 'userUp.png';

	protected $_scrollParams = array(
		'startline' => 100,
		'scrollduration' => 1000,
		'fadeinduration' => 500,
		'fadeoutduration' => 100,
		'width' => 50,
		'height' => 50,
		'offsetx' => 25,
		'offsety' => 25,
	);

	protected function _setScrollParam($param, $value)
	{
        $val = abs((int)$value);
		return Configuration::updateValue(scrolltop::PREFIX . $param, $val);
	}

	protected function _getScrollParam($param)
	{
		return Configuration::get(scrolltop::PREFIX . $param);
	}

    public function __construct()
    {
        $this->name = "scrolltop";
        $this->tab = "front_office_features";
        $this->version = "1.0.0";
        $this->author = "BelVG";
        parent::__construct();
        $this->displayName = $this->l("Scroll Top");
        $this->description = $this->l("Scroll Top");
    }

    public function install()
    {
		foreach ($this->_scrollParams as $param => $value) {
			$this->_setScrollParam($param, $value);
		}

        return (parent::install() and $this->registerHook('footer'));
    }

    public function uninstall()
    {
        $shops = Shop::getShops(false);
        foreach ($shops as $shop) {
            $file = dirname(__FILE__) . '/img/' . $shop['id_shop'] . scrolltop::USER_IMG;
            if (file_exists($file)) {
                unlink($file);
            }
        }
        return (parent::uninstall() and $this->unregisterHook('footer'));
    }

    public function hookFooter($params)
    {
		$_params = array();
		foreach ($this->_scrollParams as $param => $val) {
			$_params[$param] = $this->_getScrollParam($param);
		}

		$_params['image'] = $this->getImage();
		$this->smarty->assign('scrollParams', $_params);
		return $this->display(__file__, 'tpl/scrolltop.tpl');
    }

    protected function _getUserImg()
    {
        return $this->context->shop->id . scrolltop::USER_IMG;
    }

	protected function postProcess()
	{
		$errors = '';
		if (Tools::isSubmit('deleteImage')) {
			if (!file_exists(dirname(__FILE__) . '/img/' . $this->_getUserImg())) {
				$errors .= $this->displayError($this->l('This action cannot be taken.'));
			} else {
				unlink(dirname(__FILE__) . '/img/' . $this->_getUserImg());
				Tools::redirectAdmin('index.php?tab=AdminModules&conf=4&configure=' . $this->name . '&token=' . Tools::getAdminToken('AdminModules' . (int)(Tab::getIdFromClassName('AdminModules')) . (int)$this->context->employee->id));
			}
		}

		if (Tools::isSubmit('submitUpdateScrolltop')) {
			if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
				if ($error = ImageManager::validateUpload($_FILES['image'])) {
					$errors .= $this->displayError($error);
				} elseif (!ImageManager::resize($_FILES['image']['tmp_name'], dirname(__FILE__) . '/img/' . $this->_getUserImg())) {
					$errors .= $this->displayError($this->l('An error occurred during the image upload.'));
				}
			}
			foreach ($this->_scrollParams as $param => $value) {
				if ($val = (int)Tools::getValue($param)) {
					$this->_setScrollParam($param, $val);
				} else {
					$errors .= $this->displayError($this->l('"' . $param . '" is not valid.'));
				}
			}

			if (!$errors) {
				Tools::redirectAdmin('index.php?tab=AdminModules&conf=4&configure=' . $this->name . '&token=' . Tools::getAdminToken('AdminModules' . (int)(Tab::getIdFromClassName('AdminModules')) . (int)$this->context->employee->id));
			}
		}

		$this->_html .= $errors;
	}

	public function getImage()
	{
		if ($this->isImageUploaded()) {
			return $this->_path . 'img/' . $this->_getUserImg();
		}
		return $this->_path . 'img/' . scrolltop::DEFAULT_IMG;
	}

	protected function isImageUploaded()
	{
		if (file_exists(dirname(__FILE__).'/img/' . $this->_getUserImg())) {
			return true;
		}
		return false;
	}

	protected function initForm()
	{
		$helper = new HelperForm();
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->identifier = $this->identifier;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		$helper->title = $this->displayName . ' ' . $this->l('Configuration');
		$helper->submit_action = 'submitUpdateScrolltop';

		$this->fields_form[0]['form'] = array(
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Start Line'),
					'name' => 'startline',
					'size' => 64,
					'hint' => $this->l('Default: ') .
						$this->_scrollParams['startline'],
				),
				array(
					'type' => 'text',
					'label' => $this->l('Scroll Duration'),
					'name' => 'scrollduration',
					'size' => 64,
					'hint' => $this->l('Default: ') .
						$this->_scrollParams['scrollduration'],
				),
				array(
					'type' => 'text',
					'label' => $this->l('FadeIn Duration'),
					'name' => 'fadeinduration',
					'size' => 64,
					'hint' => $this->l('Default: ') .
						$this->_scrollParams['fadeinduration'],
				),
				array(
					'type' => 'text',
					'label' => $this->l('FadeOut Duration'),
					'name' => 'fadeoutduration',
					'size' => 64,
					'hint' => $this->l('Default: ') .
						$this->_scrollParams['fadeoutduration'],
				),
				array(
					'type' => 'text',
					'label' => $this->l('Width'),
					'name' => 'width',
					'size' => 64,
					'hint' => $this->l('Default: ') .
						$this->_scrollParams['width'],
				),
				array(
					'type' => 'text',
					'label' => $this->l('Height'),
					'name' => 'height',
					'size' => 64,
					'hint' => $this->l('Default: ') .
						$this->_scrollParams['height'],
				),
				array(
					'type' => 'text',
					'label' => $this->l('Offset X'),
					'name' => 'offsetx',
					'size' => 64,
					'hint' => $this->l('Default: ') .
						$this->_scrollParams['offsetx'],
				),
				array(
					'type' => 'text',
					'label' => $this->l('Offset Y'),
					'name' => 'offsety',
					'size' => 64,
					'hint' => $this->l('Default: ') .
						$this->_scrollParams['offsety'],
				),
				array(
					'type' => 'file',
					'label' => $this->l('Image'),
					'name' => 'image',
					'display_image' => $this->isImageUploaded(),
					'hint' => $this->l('Default: <img src=' .
						$this->_path . 'img/' . scrolltop::DEFAULT_IMG . ' height="50"/>'),
				),
			),
			'submit' => array(
				'name' => $helper->submit_action,
				'title' => $this->l('   Save   '),
				'class' => 'button'
			),
		);
		return $helper;
	}

    public function getContent()
	{
		$this->postProcess();
		$helper = $this->initForm();

		foreach($this->fields_form[0]['form']['input'] as $input) {
			if ($input['name'] != 'image') {
				$helper->fields_value[$input['name']] = $this->_getScrollParam($input['name']);
			}
		}

        if ($this->isImageUploaded()) {
    		$helper->fields_value['image'] = '<img src="' . $this->getImage() . '" height="50"/>';
    		if ($helper->fields_value['image']) {
    			$helper->fields_value['size'] = filesize(dirname(__FILE__) . '/img/' . $this->_getUserImg()) / 1000;
    		}
        }

        if (!is_writeable(dirname(__FILE__) . '/img/')) {
            $this->_html .= $this->displayError($this->l('Directory "modules/scrolltop/img" is not writeable!'));
        }

		$this->_html .= $helper->generateForm($this->fields_form);
		return $this->_html;
	}
}
?>