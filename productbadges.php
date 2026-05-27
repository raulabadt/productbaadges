<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class ProductBadges extends Module
{
    public function __construct()
    {
        $this->name = 'productbadges';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Candidate';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Product Badges');
        $this->description = $this->l('Manage reusable visual badges for products.');
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        // Register hooks
        if (!$this->registerHook('displayProductAdditionalInfo') ||
            !$this->registerHook('displayProductListReviews') ||
            !$this->registerHook('displayHeader')) {
            return false;
        }

        // Install DB
        include_once dirname(__FILE__).'/sql/install.php';
        if (!productbadges_install()) {
            return false;
        }

        // Default configuration
        Configuration::updateValue('PRODUCTBADGES_ENABLED', 1);
        Configuration::updateValue('PRODUCTBADGES_SHOW_IN_LISTS', 1);
        Configuration::updateValue('PRODUCTBADGES_SHOW_IN_PRODUCT', 1);
        Configuration::updateValue('PRODUCTBADGES_MAX_VISIBLE', 3);

        // Register admin tab
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminProductBadges';
        $tab->name = [];
        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = $this->l('Product Badges');
        }
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminParentModulesSf');
        $tab->module = $this->name;
        if (!$tab->add()) {
            return false;
        }

        // If multishop is enabled, associate module tables with shops where needed
        if (Shop::isFeatureActive()) {
            // Associate badges table to shops for context-aware behavior
            // This is a light association: we create entries in pb_badge_shop when a badge is created.
            // No DB schema changes required here beyond pb_badge_shop which was created in install.
        }

        return true;
    }

    public function uninstall()
    {
        // Uninstall DB
        include_once dirname(__FILE__).'/sql/uninstall.php';
        productbadges_uninstall();

        // Remove configuration
        Configuration::deleteByName('PRODUCTBADGES_ENABLED');
        Configuration::deleteByName('PRODUCTBADGES_SHOW_IN_LISTS');
        Configuration::deleteByName('PRODUCTBADGES_SHOW_IN_PRODUCT');
        Configuration::deleteByName('PRODUCTBADGES_MAX_VISIBLE');

        // Remove admin tab
        $id_tab = (int)Tab::getIdFromClassName('AdminProductBadges');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            $tab->delete();
        }

        if (!parent::uninstall()) {
            return false;
        }
        return true;
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitProductBadgesConfig')) {
            $enabled = (int)Tools::getValue('PRODUCTBADGES_ENABLED');
            $show_lists = (int)Tools::getValue('PRODUCTBADGES_SHOW_IN_LISTS');
            $show_product = (int)Tools::getValue('PRODUCTBADGES_SHOW_IN_PRODUCT');
            $max = (int)Tools::getValue('PRODUCTBADGES_MAX_VISIBLE');

            Configuration::updateValue('PRODUCTBADGES_ENABLED', $enabled);
            Configuration::updateValue('PRODUCTBADGES_SHOW_IN_LISTS', $show_lists);
            Configuration::updateValue('PRODUCTBADGES_SHOW_IN_PRODUCT', $show_product);
            Configuration::updateValue('PRODUCTBADGES_MAX_VISIBLE', max(0, $max));

            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }

        // Build configuration form
        $fields_form[0]['form'] = [
            'legend' => ['title' => $this->l('Settings')],
            'input' => [
                ['type' => 'switch', 'label' => $this->l('Enable module'), 'name' => 'PRODUCTBADGES_ENABLED', 'values' => [['id' => 'on', 'value' => 1, 'label' => $this->l('Yes')], ['id' => 'off', 'value' => 0, 'label' => $this->l('No')]]],
                ['type' => 'switch', 'label' => $this->l('Show in lists'), 'name' => 'PRODUCTBADGES_SHOW_IN_LISTS', 'values' => [['id' => 'on', 'value' => 1, 'label' => $this->l('Yes')], ['id' => 'off', 'value' => 0, 'label' => $this->l('No')]]],
                ['type' => 'switch', 'label' => $this->l('Show in product page'), 'name' => 'PRODUCTBADGES_SHOW_IN_PRODUCT', 'values' => [['id' => 'on', 'value' => 1, 'label' => $this->l('Yes')], ['id' => 'off', 'value' => 0, 'label' => $this->l('No')]]],
                ['type' => 'text', 'label' => $this->l('Max badges visible'), 'name' => 'PRODUCTBADGES_MAX_VISIBLE', 'size' => 3, 'desc' => $this->l('Maximum number of badges shown per product')],
            ],
            'submit' => ['title' => $this->l('Save')],
        ];

        $helper = new HelperForm();
        $helper->show_cancel_button = false;
        $helper->module = $this;
        $helper->submit_action = 'submitProductBadgesConfig';
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->fields_value['PRODUCTBADGES_ENABLED'] = Configuration::get('PRODUCTBADGES_ENABLED');
        $helper->fields_value['PRODUCTBADGES_SHOW_IN_LISTS'] = Configuration::get('PRODUCTBADGES_SHOW_IN_LISTS');
        $helper->fields_value['PRODUCTBADGES_SHOW_IN_PRODUCT'] = Configuration::get('PRODUCTBADGES_SHOW_IN_PRODUCT');
        $helper->fields_value['PRODUCTBADGES_MAX_VISIBLE'] = Configuration::get('PRODUCTBADGES_MAX_VISIBLE');

        $output .= $helper->generateForm($fields_form);

        // Link to badge manager
        $output .= '<p><a class="btn btn-default" href="'.htmlentities($this->context->link->getAdminLink('AdminProductBadges')).'">'.$this->l('Manage badges').'</a></p>';

        return $output;
    }

    public function hookDisplayHeader($params)
    {
        // Load assets only on likely pages where badges are shown
        $php_self = isset($this->context->controller->php_self) ? $this->context->controller->php_self : '';
        $allowed = ['product', 'category', 'search', 'index'];
        if (in_array($php_self, $allowed)) {
            $this->context->controller->addCSS($this->_path.'views/css/productbadges.css', 'all');
            $this->context->controller->addJS($this->_path.'views/js/productbadges.js');
        }
    }

    // Hooks for product display: these are simple and theme-dependent
    public function hookDisplayProductAdditionalInfo($params)
    {
        if (!Configuration::get('PRODUCTBADGES_ENABLED') || !Configuration::get('PRODUCTBADGES_SHOW_IN_PRODUCT')) {
            return '';
        }
        return $this->renderBadgesForProduct($params['product']);
    }

    public function hookDisplayProductListReviews($params)
    {
        if (!Configuration::get('PRODUCTBADGES_ENABLED') || !Configuration::get('PRODUCTBADGES_SHOW_IN_LISTS')) {
            return '';
        }
        return $this->renderBadgesForProduct($params['product']);
    }

    protected function renderBadgesForProduct($product)
    {
        $id_product = 0;
        if (is_object($product) && isset($product->id)) {
            $id_product = (int)$product->id;
        } elseif (is_array($product)) {
            if (isset($product['id_product'])) {
                $id_product = (int)$product['id_product'];
            } elseif (isset($product['id'])) {
                $id_product = (int)$product['id'];
            }
        }

        if (!$id_product) {
            return '';
        }
        // Build base query to fetch badges assigned to the product, with language
        $sql = 'SELECT b.id_badge, bl.text, b.bg_color, b.text_color, b.position
            FROM `'._DB_PREFIX_.'pb_badge` b
            LEFT JOIN `'._DB_PREFIX_.'pb_badge_lang` bl ON (b.id_badge = bl.id_badge AND bl.id_lang = '.(int)$this->context->language->id.')
            INNER JOIN `'._DB_PREFIX_.'pb_product_badge` pb ON (b.id_badge = pb.id_badge)';

        // If multishop is active, join with pb_badge_shop and filter by current shop id
        if (Shop::isFeatureActive() && isset($this->context->shop) && isset($this->context->shop->id) && (int)$this->context->shop->id) {
            $sql .= ' INNER JOIN `'._DB_PREFIX_.'pb_badge_shop` bs ON (b.id_badge = bs.id_badge)';
            $sql .= ' WHERE pb.id_product = '.(int)$id_product.' AND b.active = 1 AND bs.id_shop = '.(int)$this->context->shop->id;
        } else {
            $sql .= ' WHERE pb.id_product = '.(int)$id_product.' AND b.active = 1';
        }
        $sql .= ' ORDER BY b.`position` ASC';

        $badges = Db::getInstance()->executeS($sql);

        // Apply max visible limit
        $max = (int)Configuration::get('PRODUCTBADGES_MAX_VISIBLE');
        if ($max > 0) {
            $badges = array_slice($badges, 0, $max);
        }

        $this->context->smarty->assign(['badges' => $badges]);
        return $this->display(__FILE__, 'views/templates/hook/product_badges.tpl');
    }
}
