<?php

namespace LOW\Prestashop;

class ModuleHelper extends \Module
{

    const ADD = 'a';
    const REMOVE = 'd';
    const UPDATE = 'u';


    public $dataValueCompatibility = array();
    public $path;
    public $html;
    public $_html = '';
    public $name_config = '';
    public $config_form = array();
    protected $_customField = array();
    public $configDisplayName = 'Configuration du thème';
    public $configDescription = 'Permet de personnaliser le thème';
    public $controllers = array();
    public $folderImg = '';
    public $rewriteControllers = array(
//		'jeuxconcours' => array(
//			'meta_lang' => array(
//				1 => 'jeux-concours'
//			),
//			'theme' => array(
//				'left' => false,
//				'right' => false,
//			)
//
//		),
    );
    protected $_tabController = array(
//        [
//            'title' => 'WebService Groups',
//            'class_name' => 'AdminWebserviceGroup',
//            'show' => true,
//            'parentClassName' => 'AdminAdvancedParameters',
//            'position' => 10
//        ],
    );

    protected $_hook = array(
//            'displayFooter',
//            'displayHome',
//            'actionProductUpdate',
//            'displayAdminProductsExtra',
//            'displayProductExemplaire',
//            'displayProductTabContent',
    );

    protected $_hook_other_module = array(
//        'blocksocial_mod' => array(
//            'displayFooterTheme' => array(
//                'position' => 1
//            )
//        ),
//        'blocknewsletter' => array(
//            'displayFooterTheme' => array(
//                'position' => 2
//            )
//        ),
//        'blocklayered' => array(
//            'displayLayered' => array(
//                'position' => 1
//            )
//        ),

    );


    protected $_unregister_hook_other_module = array(
//        'blocksocial_mod' => array(
//            'displayFooterTheme' => array(
//                'position' => 1
//            )
//        ),
//        'blocknewsletter' => array(
//            'displayFooterTheme' => array(
//                'position' => 2
//            )
//        ),
//        'blocklayered' => array(
//            'displayLayered' => array(
//                'position' => 1
//            )
//        ),

    );

    protected $_override = array(
//            'classes/webservice/WebserviceKeyGroup.php',
//            'classes/webservice/WebserviceRequestGroup.php',
    );


    public static function getName()
    {
        return strtolower(get_called_class());
    }

    public static function getNameUpper()
    {
        return strtoupper(self::getName());
    }


    public function __construct()
    {
        $this->name = self::getName();
        $this->tab = 'front_office_features';
        $this->version = '1.0';
        $this->author = 'Damien TUPINIER';
        $this->bootstrap = true;
        $this->need_instance = 0;
        $this->displayName = $this->l($this->configDisplayName);
        $this->description = $this->l($this->configDescription);
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        parent::__construct();


        $this->_html = '';
        $this->path = _PS_MODULE_DIR_ . $this->name . DIRECTORY_SEPARATOR;
        $this->confirmUninstall = $this->l('Êtes-vous sûr(e) de vouloir désinstaller le module?');
        $this->name_config = strtoupper($this->name);

    }

    public function addConfigForm($form)
    {
        array_push($this->config_form, $form);
    }


    public function migrateUp()
    {
        \Configuration::updateValue($this->name_config . '_ACTIVE', 1);

        $result = $this->migrateUpBdd();


        return true;
    }

    public function migrateUpBdd()
    {
        //        $result = \Db::getInstance()->execute('
//				CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'webservice_account_group` (
//				`id_webservice_account` INT UNSIGNED NOT NULL,
//				`id_group` int(10) unsigned NOT NULL
//			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;'
//        );
    }

    public function migrateDownBdd()
    {
//        \Db::getInstance()->execute('DROP TABLE `' . _DB_PREFIX_ . 'webservice_account_group`;');
//        \Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'webservice_account` SET `class_name`="WebserviceRequest" WHERE `class_name` = "WebserviceRequestGroup";');
    }

    public function migrateDown()
    {

        $result = $this->migrateDownBdd();
        return true;
    }


    public function install()
    {

        return parent::install()
            && $this->overrideSetup(self::ADD)
            && $this->migrateUp()
            && $this->controllerSetup(self::ADD)
            && $this->installTab()
            && $this->hookSetup(self::ADD)
            && $this->tabSetup(self::ADD);
    }

    public function uninstall()
    {
        return parent::uninstall()
            && $this->migrateDown()
            && $this->controllerSetup(self::REMOVE)
            && $this->hookSetup(self::REMOVE)
            && $this->tabSetup(self::REMOVE)
            && $this->uninstallTab()
            && $this->overrideSetup(self::REMOVE);

    }


    private function tabSetup($action)
    {
        $pass = true;

        if ($action == self::ADD) {

            if (version_compare(_PS_VERSION_, '1.6.0', '<')) {

            } else {
                // On récupère l'id de l'onglet principale, ici le menu "Module"
                //$id_parent = \Tab::getIdFromClassName('AdminTools');
                // On récrée le nouvel onglet

                $tabModule = $this->_tabController;

                $id_parent = 0;
                $first = true;

                foreach ($tabModule as $tab) {
                    /** @var Tab $adminTab */
                    $adminTab = new \Tab();
                    // On ajoute pour chaque langue un libellé de menu (optionnel)
                    $languages = \Language::getLanguages(true);
                    $adminTab->name = array();
                    foreach ($languages as $lang) {
                        $adminTab->name[$lang['id_lang']] = (('fr' == $lang['iso_code']) ? $tab['title'] : $tab['title']);
                    }


                    // On spécifie la class du controller (sans le mot clé controller)

                    $adminTab->class_name = $tab['class_name'];
                    // On spécifié également le nom du module
                    $adminTab->module = $this->name;

                    if (isset($tab['parentClassName'])) {
                        if ($id_tab_byclass = \PrestaShopBundle\Entity\Repository\TabRepository::findOneIdByClassName($tab['parentClassName'])) {
                            $adminTab->id_parent = $id_tab_byclass;
                        } else {
                            $adminTab->id_parent = $id_parent;
                        }

                    } else {
                        // On indique l'id du menu principal
                        $adminTab->id_parent = $id_parent;
                    }


                    // On l'active
                    $adminTab->active = $tab['show'];
                    // Et on indique de le placer en dernière position
                    $adminTab->position = \Tab::getNbTabs($id_parent);

                    $adminTab->save();
                    if ($first) {
                        $id_parent = $adminTab->id;
                        $first = false;
                        if ($tab['position']) {
                            $adminTab->updatePosition(true, (int)$tab['position']);
                        }
                    }
                    // On enregistre ensuite l'id de l'onglet pour pouvoir le désintaller automatiquement
                    $tabModuleIds = unserialize(\Configuration::get('ADMIN_TAB_MODULE_' . $this->name_config));
                    $tabModuleIds[] = $adminTab->id;
                    \Configuration::updateValue('ADMIN_TAB_MODULE_' . $this->name_config, serialize($tabModuleIds));
                }
            }


        }

        if ($action == self::REMOVE) {

            if (version_compare(_PS_VERSION_, '1.6.0', '<')) {

            } else {
                // On récupère l'id de l'onglet
                $adminTabIds = unserialize(\Configuration::get('ADMIN_TAB_MODULE_' . $this->name_config));

                if ($adminTabIds) {
                    foreach ($adminTabIds as $adminTabId) {
                        // On vérifie qu'il n'est pas déjà déinstallé
                        if (\Tab::existsInDatabase($adminTabId, \Tab::$definition['table'])) {
                            // On l'instancie...
                            $adminTab = new \Tab($adminTabId);
                            // Puis on le supprime
                            if (!$adminTab->delete())
                                return false;
                        }
                    }
                }


                // Et enfin on supprime la configuration qui n'est plus utile
                return \Configuration::deleteByName('ADMIN_TAB_MODULE_' . $this->name_config);
            }


        }

        return ($pass);
    }

    private function controllerSetup($action)
    {

        if ($action == self::ADD) {

            $id_lang = $this->context->language->id;

            foreach ($this->controllers as $controller) {
                $name = 'module-' . self::getName() . '-' . $controller;
                /** @var MetaCore $meta */
                $metaReq = \MetaCore::getMetaByPage($name, $id_lang);

                if ($metaReq) {
                    $meta = new \MetaCore($metaReq['id_meta']);

                    foreach ($this->rewriteControllers[$controller]['meta_lang'] as $id_lang => $meta_lang) {
                        $meta->url_rewrite[$id_lang] = $meta_lang['url_rewrite'];

                        if (isset($meta_lang['title'])) {
                            $meta->title[$id_lang] = $meta_lang['title'];
                        }

                        if (isset($meta_lang['description'])) {
                            $meta->description[$id_lang] = $meta_lang['description'];
                        }

                        if (isset($meta_lang['keywords'])) {
                            $meta->keywords[$id_lang] = $meta_lang['keywords'];
                        }
                    }

                } else {
                    $meta = new \MetaCore();
                    $meta->page = $name;
                    $meta->configurable = 1;

                    $meta->url_rewrite = array();
                    $meta->title = array();
                    $meta->description = array();
                    $meta->keywords = array();

                    foreach ($this->rewriteControllers[$controller]['meta_lang'] as $id_lang => $meta_lang) {
                        $meta->url_rewrite[$id_lang] = $meta_lang['url_rewrite'];

                        if (isset($meta_lang['title'])) {
                            $meta->title[$id_lang] = $meta_lang['title'];
                        }

                        if (isset($meta_lang['description'])) {
                            $meta->description[$id_lang] = $meta_lang['description'];
                        }

                        if (isset($meta_lang['keywords'])) {
                            $meta->keywords[$id_lang] = $meta_lang['keywords'];
                        }
                    }


                }

                $meta->save();

            }
        }

        if ($action == self::REMOVE) {

            $id_lang = $this->context->language->id;

            foreach ($this->controllers as $controller) {
                $name = 'module-' . self::getName() . '-' . $controller;
                /** @var MetaCore $meta */
                $metaReq = \MetaCore::getMetaByPage($name, $id_lang);

                if ($metaReq) {
                    $meta = new \MetaCore($metaReq['id_meta']);
                    $meta->delete();
                }

            }

        }

        return true;
    }


    /**
     * Set up hooks
     *
     * @param string $action
     *
     * @return bool
     */
    private function hookSetup($action)
    {
        $expected_hooks = $this->_hook;

        if (version_compare(_PS_VERSION_, '1.6.0', '<')) {
            include_once _PS_ROOT_DIR_ . '/classes/Hook.php';
            $hookClass = new HookCore();

        } else {
            $hookClass = new \Hook();
        }

        $pass = true;

        if ($action == self::ADD) {
            foreach ($expected_hooks as $expected_hook => $expected_hook_data) {
                if (!$this->registerHook($expected_hook)) {
                    $pass = false;
                }

                if (isset($expected_hook_data['position'])) {
                    if (version_compare(_PS_VERSION_, '1.6.0', '<')) {
                        $id_hook = $hookClass::get($expected_hook);
                    } else {
                        $id_hook = $hookClass::getIdByName($expected_hook);
                    }

                    $this->updatePosition($id_hook, 0, $expected_hook_data['position']);
                }
            }

            if (count($this->_hook_other_module)) {
                foreach ($this->_hook_other_module as $module_name => $module_hook) {

                    $module = \Module::getInstanceByName($module_name);

                    if ($module) {


                        foreach ($module_hook as $expected_hook => $expected_hook_data) {

                            if (!$module->registerHook($expected_hook)) {
                                $pass = false;
                            }

                            if (isset($expected_hook_data['position'])) {

                                if (version_compare(_PS_VERSION_, '1.6.0', '<')) {
                                    $id_hook = $hookClass::get($expected_hook);
                                } else {
                                    $id_hook = $hookClass::getIdByName($expected_hook);
                                }

                                $module->updatePosition($id_hook, 0, $expected_hook_data['position']);
                            }

                        }
                    }

                }
            }

            if (count($this->_unregister_hook_other_module)) {
                foreach ($this->_unregister_hook_other_module as $module_name => $module_hook) {

                    $module = \Module::getInstanceByName($module_name);

                    if ($module) {
                        foreach ($module_hook as $expected_hook => $expected_hook_data) {
                            if (!$module->unregisterHook($expected_hook)) {
                                $pass = false;
                            }
                        }
                    }

                }
            }


        }

        if ($action == self::REMOVE) {
            foreach ($expected_hooks as $expected_hook => $expected_hook_data) {
                if (!$this->unregisterHook($expected_hook)) {
                    $pass = false;
                }
            }
            foreach ($this->_hook_other_module as $module_name => $module_hook) {

                $module = \Module::getInstanceByName($module_name);
                if ($module) {
                    foreach ($module_hook as $expected_hook => $expected_hook_data) {
                        if (!$module->unregisterHook($expected_hook)) {
                            $pass = false;
                        }
                    }
                }
            }

        }

        return ($pass);
    }

    private function overrideSetup($action)
    {
        $overrides = $this->_override;

        $pass = true;

        if ($action == self::ADD) {
            if (count($overrides)) {
                foreach ($overrides as $override) {
                    $path_override_source = _PS_ROOT_DIR_ . '/override/' . $override;
                    preg_match_all('/(.*)\/.*\.[a-zA-Z]{1,3}$/m', $path_override_source, $matches, PREG_SET_ORDER, 0);

                    $HelperFolder = new \Helper\ScanDir($matches[0][1]);
                    $HelperFolder->createFolder($matches[0][1]);

                    if (
                    !copy(
                        _PS_MODULE_DIR_ . $this->name . '/override/' . $override,
                        _PS_ROOT_DIR_ . '/override/' . $override
                    )
                    ) {
                        $pass = false;
                    }
                }
            }

            try {
                $this->installOverrides();
            } catch (Exception $e) {
                $pass = false;
                var_dump($e);
            }


        }

        if ($action == self::REMOVE) {
            if (count($overrides)) {
                foreach ($overrides as $override) {
                    if (file_exists(_PS_ROOT_DIR_ . '/override/' . $override)) {
                        if (!unlink(_PS_ROOT_DIR_ . '/override/' . $override)) {
                            $pass = false;
                        }
                    }

                }
            }

            try {
                $this->uninstallOverrides();
            } catch (Exception $e) {
                $pass = false;
            }

        }

        $this->clearAllCache();

        return ($pass);
    }


    public function getContent()
    {
        // Sauvegarde des données submit
        $this->addConfigForm($this->getFormConfigurationDev());

        $this->checkSubmit([$this->config_form]);

        $this->context->controller->addCSS($this->_path . 'views/css/back.css');

        $this->_html .= $this->renderForm($this->config_form);
        return $this->_html;

    }


    /**
     * Gestion du submit de la configuration du module
     */

    public function checkSubmit($forms)
    {


        if (\Tools::isSubmit('submitUpdate')) {
            foreach ($forms as $form) {
                $this->setConfigFieldsValues($form);
            }
            $this->clearCacheModule();
            $this->_html .= $this->displayConfirmation($this->l('Settings updated'));

        }

        if (\Tools::isSubmit('resetHook')) {
            $this->hookSetup(self::REMOVE);
            $this->hookSetup(self::ADD);
            $this->_html .= $this->displayConfirmation($this->l('Hook Reset !'));
        }

        if (\Tools::isSubmit('resetBDD')) {

            $this->migrateDown();
            $this->migrateUp();

            $this->_html .= $this->displayConfirmation($this->l('BDD Reset ! '));
        }

        if (\Tools::isSubmit('resetTab')) {

            $this->tabSetup(self::REMOVE);
            $this->tabSetup(self::ADD);

            $this->_html .= $this->displayConfirmation($this->l('Tab Reset ! '));
        }

        if (\Tools::isSubmit('resetOverride')) {

            $this->overrideSetup(self::REMOVE);
            $this->overrideSetup(self::ADD);

            $this->_html .= $this->displayConfirmation($this->l('Override Reset ! '));
        }

        if (\Tools::isSubmit('resetControllerFront')) {

            $this->controllerSetup(self::REMOVE);
            $this->controllerSetup(self::ADD);

            $this->_html .= $this->displayConfirmation($this->l('Controller Front Reset ! '));
        }

        if (\Tools::isSubmit('resetAuthorization')) {

            $this->autorisationSetup(self::REMOVE);
            $this->autorisationSetup(self::ADD);

            $this->_html .= $this->displayConfirmation($this->l('Authorization Reset ! '));
        }


    }

    public function autorisationSetup($actionSetup, $name = '')
    {

        $name = empty($name) ? $this->name : $name;
        if ($actionSetup == self::ADD) {
            // Permissions management
            foreach (['CREATE', 'READ', 'UPDATE', 'DELETE'] as $action) {
                $slug = 'ROLE_MOD_MODULE_' . strtoupper($name) . '_' . $action;

                Db::getInstance()->execute(
                    'INSERT INTO `' . _DB_PREFIX_ . 'authorization_role` (`slug`) VALUES ("' . $slug . '")'
                );

                Db::getInstance()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'module_access` (`id_profile`, `id_authorization_role`) (
                    SELECT id_profile, "' . Db::getInstance()->Insert_ID() . '"
                    FROM ' . _DB_PREFIX_ . 'access a
                    LEFT JOIN `' . _DB_PREFIX_ . 'authorization_role` r
                    ON r.id_authorization_role = a.id_authorization_role
                    WHERE r.slug = "ROLE_MOD_TAB_ADMINMODULESSF_' . $action . '"
            )');
            }
        } else {
            // Permissions management
            foreach (['CREATE', 'READ', 'UPDATE', 'DELETE'] as $action) {
                $slug = 'ROLE_MOD_MODULE_' . strtoupper($name) . '_' . $action;

                $rowAuth = Db::getInstance()->getValue('SELECT `id_authorization_role` FROM `' . _DB_PREFIX_ . 'authorization_role` WHERE `slug` ="' . $slug . '"');

                if ($rowAuth) {
                    Db::getInstance()->execute(
                        'DELETE FROM `' . _DB_PREFIX_ . 'module_access` WHERE `id_authorization_role` ="' . $rowAuth . '"'
                    );

                    Db::getInstance()->execute(
                        'DELETE FROM `' . _DB_PREFIX_ . 'authorization_role` WHERE `slug` ="' . $slug . '"'
                    );
                }

            }
        }
    }

    public function uploadFile($name)
    {

        $fileName = $name;

        if (isset($_FILES[$fileName]) && isset($_FILES[$fileName]['tmp_name'])) {

            $handle = new \Helper\UploadHelper();
            $handle->upload($_FILES[$fileName], 'fr_FR');

            if ($handle->uploaded) {

                $handle->file_new_name_body = $handle->file_src_name_body . uniqid();
                $handle->mime_check = true;
                $handle->allowed = array(
                    'image/*',
                );

                $handle->process(_PS_IMG_DIR_ . self::getName() . DIRECTORY_SEPARATOR);

                if ($handle->processed) {

                    $fileLines = file($handle->file_dst_pathname);

                    $href = str_replace(_PS_ROOT_DIR_ . '/', '', $handle->file_dst_pathname);
                    $href = $this->context->shop->getBaseURI() . $href;
                    $href = str_replace(DIRECTORY_SEPARATOR, '/', $href);

                    try {
                        \Configuration::updateValue($name, $href);
                    } catch (PrestaShopException $e) {

                    }

                    $handle->clean();
                } else {

                }
            }
        } else {

        }

    }

    /**
     * Retourne les valeurs du formulaire
     *
     * @return bool
     */
    public function setConfigFieldsValues($formData)
    {

        $languages = \Language::getLanguages(false);
        foreach ($formData as $form) {
            foreach ($form['form']['input'] as $input) {

                if (isset($input['lang']) && $input['lang']) {
                    $valueLang = array();
                    /** @var LanguageCore $lang */
                    foreach ($languages as $lang) {
                        if (\Tools::getValue($input['name'] . '_' . $lang['id_lang']) !== false) {
                            $valueLang[$lang['id_lang']] = \Tools::getValue($input['name'] . '_' . $lang['id_lang']);
                        }
                    }
                    \Configuration::updateValue($input['name'], $valueLang, true);
                } else {
                    if ($this->isCustomField($input['type'])) {
                        $this->updateDataCustomField($input);
                    } elseif ($input['type'] == 'file') {
                        $this->uploadFile($input['name']);
                    } else {
                        if (\Tools::getValue($input['name']) !== false) {
                            if (isset($input['data_type']) && $input['data_type'] == 'array') {
                                $value = implode(',', \Tools::getValue($input['name']));
                            } else {
                                $value = \Tools::getValue($input['name']);
                            }

                            \Configuration::updateValue($input['name'], $value, true);

                        }
                    }
                }

            }
        }

        return true;
    }

    /**
     * Retourne le tableau pour construire le formulaire
     *
     * @return array
     */
    public function getFormConfigurationDev()
    {

        return array(
            'form' => array(
                'tab_name' => 'configdev_tab',
                'legend' => array(
                    'title' => $this->l('Developper Mode'),
                    'icon' => 'icon-code',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Activer le module'),
                        'name' => self::getNameUpper() . '_ACTIVE',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'html',
                        'label' => $this->l('Dev'),
                        'name' => 'dev',
                        'html_content' => $this->getHtmlButtonDev(),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

    }


    /**
     * @param $name
     *
     * @return string
     */
    public static function getConfigName($name)
    {
        return self::getNameUpper() . '_' . strtoupper($name);
    }

    public static function getConfig($name, $id_lang = null)
    {
        return \Configuration::get(self::getConfigName($name), $id_lang);
    }

    /**
     * Retourne les valeurs du formulaire
     *
     * @return array
     */
    public function getConfigFieldsValues($formData)
    {
        $data = array();
        $languages = \Language::getLanguages(false);
        foreach ($formData as $form) {
            foreach ($form['form']['input'] as $input) {

                switch (true) {
                    case (isset($input['lang']) && $input['lang']):
                        $data[$input['name']] = [];
                        /** @var LanguageCore $lang */
                        foreach ($languages as $lang) {
                            $data[$input['name']][$lang['id_lang']] = \Configuration::get($input['name'], $lang['id_lang']);
                        }
                        break;
                    case (isset($input['data_type']) && $input['data_type'] == 'array'):
                        $data[$input['name'] . (isset($input['multiple']) && $input['multiple'] ? '[]' : '')] = explode(',', \Configuration::get($input['name']));
                        break;
                    case $this->isCustomField($input['type']):
                        $data[$input['name']] = $this->setDataCustomField($input);
                        break;
                    default :
                        $data[$input['name']] = \Configuration::get($input['name']);
                        break;
                }

            }
        }

        return $data;
    }

    public function getConfigFormValues(&$formData)
    {
        $data = array();
        $languages = \Language::getLanguages(false);
        foreach ($formData as &$form) {
            foreach ($form['form']['input'] as &$input) {

                switch (true) {
                    case $input['type'] == 'categories':
                        $input['tree']['selected_categories'] = explode(',', \Configuration::get($input['name']));
                        break;
                    case (isset($input['data_type']) && $input['data_type'] == 'array'):
                        $data[$input['name']] = explode(',', \Configuration::get($input['name']));
                        break;
                    case $this->isCustomField($input['type']):
                        $data[$input['name']] = $this->setDataCustomField($input);
                        break;
                    default :
                        $data[$input['name']] = \Configuration::get($input['name']);
                        break;
                }

            }
        }

        return $data;
    }

    /**
     * Permet de sauvegarder les valeurs des inputs Custom si besoin
     *
     * @param array $input
     *
     * @return array|string
     */
    protected function updateDataCustomField($input)
    {
        switch ($input['type']) {
            default:
                return '';
                break;
        }
    }

    /**
     * Permet de setter les valeurs des inputs Custom si besoin
     *
     * @param array $input
     *
     * @return array|string
     */
    protected function setDataCustomField($input)
    {
        switch ($input['type']) {
            default:
                return '';
                break;
        }

        return '';
    }

    /**
     * Permet de savoir si c'est un type d'input custom ou pas
     *
     * @param string $fieldType
     *
     * @return bool
     */
    protected function isCustomField($fieldType = 'text')
    {
        return in_array($fieldType, $this->_customField);
    }


    public function renderForm($form)
    {

        $this->dataValueCompatibility = $this->getConfigFormValues($form);

        /** @var HelperFormCore $helper */
        $helper = new \HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->table = $this->table;
        $lang = new \Language((int)\Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = \Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? \Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitUpdate';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = \Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues($form),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );


        $formHtml = $helper->generateForm($form);


        return $formHtml;
    }


    public function getHtmlButtonDev()
    {
        return '
<div class="row">
    <div class="col-md-12">
        <button class="btn btn-default" type="submit" name="resetHook">Reset Hook</button>
        <button class="btn btn-default" type="submit" name="resetBDD">Reset BDD</button>
        <button class="btn btn-default" type="submit" name="resetTab">Reset Tab</button>
        <button class="btn btn-default" type="submit" name="resetOverride">Reset Override</button>
        <button class="btn btn-default" type="submit" name="resetControllerFront">Reset Controller Front</button>
        <button class="btn btn-default" type="submit" name="resetAuthorization">Reset Authorisation</button>
    </div>
</div>';
    }


    public function clearCacheModule()
    {
        //$this->_clearCache('views/templates/front/hookDisplayFooter.tpl');
    }

    public function clearAllCache()
    {

        if (method_exists('Tools', 'clearSmartyCache')) {
            \Tools::clearSmartyCache();
        }

        if (method_exists('Tools', 'clearXMLCache')) {
            \Tools::clearXMLCache();
        }

        if (method_exists('Media', 'clearCache')) {
            \Media::clearCache();
        }

        if (method_exists('Tools', 'generateIndex')) {
            \Tools::clearXMLCache();
        }


    }


}
