<?php

if (!defined('_PS_VERSION_'))
    exit;

class CleanTalkAntiSpam extends Module {

    public function __construct() {
        $this->name = 'cleantalkantispam';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Officience';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('CleanTalk Anti-spam');
        $this->description = $this->l('Protect PrestaShop from spambots by CleanTalk. Block email, IP address, Stop word in block list in CleanTalk.');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install() {
        return parent::install();
    }

    /*public function hookFooter($params) {
        $key = "";
        $this->smarty->assign('key', $key);
        return $this->display(__FILE__, 'contacts-fields.tpl');
    }*/
    
    public function installOverrides()
    {
        $path_class = _PS_MODULE_DIR_.$this->name.
            DIRECTORY_SEPARATOR.'override'.
            DIRECTORY_SEPARATOR.'classes'.
            DIRECTORY_SEPARATOR;
        $path_controller = _PS_MODULE_DIR_.$this->name.
            DIRECTORY_SEPARATOR.'override'.
            DIRECTORY_SEPARATOR.'controllers'.
            DIRECTORY_SEPARATOR.'front'.
            DIRECTORY_SEPARATOR;
        
        $path_module = _PS_MODULE_DIR_.$this->name.
            DIRECTORY_SEPARATOR.'override'.
            DIRECTORY_SEPARATOR.'modules'.
            DIRECTORY_SEPARATOR;
        
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            // create foler contactform in override foler and add file
            if (!file_exists(_PS_OVERRIDE_DIR_.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'contactform')) {
                mkdir(_PS_OVERRIDE_DIR_.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'contactform', 0777, true);
            }
            copy($path_module.'contactform'.DIRECTORY_SEPARATOR.'contactform17.php', _PS_OVERRIDE_DIR_.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'contactform'.DIRECTORY_SEPARATOR.'contactform.php');
            // create class CustomerFormClass in override foler and add file
            if (!file_exists(_PS_OVERRIDE_DIR_.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'form')) {
                mkdir(_PS_OVERRIDE_DIR_.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'form', 0777, true);
            }
            copy($path_class.'form'.DIRECTORY_SEPARATOR.'CustomerForm.php', _PS_OVERRIDE_DIR_.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'form'.DIRECTORY_SEPARATOR.'CustomerForm.php');
        } elseif (version_compare(_PS_VERSION_, '1.6.0.0', '>=') && version_compare(_PS_VERSION_, '1.7.0.0', '<') ) {
            copy($path_controller.'ContactController16.php', $path_controller.'ContactController.php');
        }
        return parent::installOverrides();
    }

    public function uninstallOverrides(){
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            // delete contactform module override
            $path_contactform_folder = _PS_OVERRIDE_DIR_.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'contactform';
            if (file_exists($path_contactform_folder.DIRECTORY_SEPARATOR.'contactform.php')) {
                unlink($path_contactform_folder.DIRECTORY_SEPARATOR.'contactform.php');
                rmdir($path_contactform_folder);
            }
            // delete CustomerForm class override
            $path_customerform_folder = _PS_OVERRIDE_DIR_.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'form';
            if (file_exists($path_customerform_folder.DIRECTORY_SEPARATOR.'CustomerForm.php')) {
                unlink($path_customerform_folder.DIRECTORY_SEPARATOR.'CustomerForm.php');
            }
            // delete CustomerForm class override
            $path_validate_folder = _PS_OVERRIDE_DIR_.DIRECTORY_SEPARATOR.'classes';
            if (file_exists($path_validate_folder.DIRECTORY_SEPARATOR.'Validate.php')) {
                unlink($path_validate_folder.DIRECTORY_SEPARATOR.'Validate.php');
            }
        }
        return parent::uninstallOverrides();
    }

    public function getContent() {
        $output = null;

        if (Tools::isSubmit('submitAccessKey')) {
            $access_key = strval(Tools::getValue('access_key'));
            if (!$access_key || empty($access_key)){
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            }
            else {
                Configuration::updateValue('access_key', $access_key);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }
        return $output . $this->displayForm();
    }

    public function displayForm() {
        // Get default language
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('CleanTalk Settings'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Access Key'),
                    'name' => 'access_key',
                    'size' => 100,
                    'required' => true
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            )
        );

        $helper = new HelperForm();
        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submitAccessKey';
        // Load current value
        $helper->fields_value['access_key'] = Configuration::get('access_key');

        return $helper->generateForm($fields_form);
    }
}
