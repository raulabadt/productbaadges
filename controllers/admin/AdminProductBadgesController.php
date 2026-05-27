<?php
require_once dirname(__FILE__).'/../../productbadges.php';
require_once dirname(__FILE__).'/../../classes/Badge.php';

class AdminProductBadgesController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'pb_badge';
        $this->className = 'Badge';
        $this->lang = true;

        parent::__construct();

        $this->fields_list = [
            'id_badge' => ['title' => $this->l('ID'), 'width' => 30],
            'text' => ['title' => $this->l('Text'), 'filter_key' => 'b!text', 'width' => 140],
            'bg_color' => ['title' => $this->l('Background'), 'width' => 80],
            'text_color' => ['title' => $this->l('Text color'), 'width' => 80],
            'position' => ['title' => $this->l('Position'), 'width' => 60],
            'active' => ['title' => $this->l('Active'), 'active' => 'status', 'type' => 'bool', 'width' => 40],
        ];
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        return parent::renderList();
    }

    public function renderForm()
    {
        $obj = $this->loadObject(true);

        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Badge'),
            ],
            'input' => [
                ['type' => 'text', 'label' => $this->l('Text'), 'name' => 'text', 'lang' => true, 'required' => true],
                ['type' => 'color', 'label' => $this->l('Background color'), 'name' => 'bg_color', 'required' => true],
                ['type' => 'color', 'label' => $this->l('Text color'), 'name' => 'text_color', 'required' => true],
                ['type' => 'select', 'label' => $this->l('Position'), 'name' => 'position', 'options' => ['query' => [['id' => 'left', 'name' => $this->l('Left')], ['id' => 'right', 'name' => $this->l('Right')]], 'id' => 'id', 'name' => 'name']],
                ['type' => 'switch', 'label' => $this->l('Active'), 'name' => 'active', 'values' => [['id' => 'active_on','value' => 1,'label' => $this->l('Enabled')],['id' => 'active_off','value'=>0,'label'=>$this->l('Disabled')]]],
            ],
            'submit' => ['title' => $this->l('Save')],
        ];

        // Load assigned products for this badge
        $assigned = [];
        if ($obj && $obj->id) {
            $rows = Db::getInstance()->executeS('SELECT id_product FROM `'._DB_PREFIX_.'pb_product_badge` WHERE id_badge='.(int)$obj->id);
            if ($rows) {
                foreach ($rows as $r) {
                    $assigned[] = (int)$r['id_product'];
                }
            }
        }
        $this->fields_value['assigned_products'] = implode(',', $assigned);

        // Add a textarea to manage associations by product IDs (simple approach)
        $this->fields_form['input'][] = ['type' => 'textarea', 'label' => $this->l('Assigned product IDs'), 'name' => 'assigned_products', 'cols' => 60, 'rows' => 3, 'desc' => $this->l('Comma separated product IDs to associate with this badge')];

        return parent::renderForm();
    }

    public function postProcess()
    {
        // Sanitize and validate submitted values before saving the ObjectModel.
        // This enforces allowed values for position and basic color format, and
        // strips tags from multilingual text fields to avoid HTML injection.
        if (Tools::isSubmit('submitAdd'.$this->table)) {
            // Position must be 'left' or 'right'
            $position = Tools::getValue('position');
            if ($position !== 'left' && $position !== 'right') {
                $_POST['position'] = 'left';
            }

            // Validate colors (hex #rgb or #rrggbb). Fallback to sensible defaults.
            $bg_color = Tools::getValue('bg_color');
            $text_color = Tools::getValue('text_color');
            $colorPattern = '/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/';
            if (!is_string($bg_color) || !preg_match($colorPattern, $bg_color)) {
                $_POST['bg_color'] = '#ff0000';
            }
            if (!is_string($text_color) || !preg_match($colorPattern, $text_color)) {
                $_POST['text_color'] = '#ffffff';
            }

            // Sanitize multilingual text fields (strip tags). Parent will use
            // $_POST values when creating/updating the ObjectModel.
            $text = Tools::getValue('text');
            if (is_array($text)) {
                foreach ($text as $id_lang => $t) {
                    $_POST['text'][(int)$id_lang] = strip_tags((string)$t);
                }
            } else {
                $_POST['text'] = strip_tags((string)$text);
            }
        }

        // Let the parent handle saving the badge now that inputs are sanitized
        parent::postProcess();

        // After save, process assigned products if present
        if (Tools::isSubmit('assigned_products')) {
            $id_badge = (int)Tools::getValue('id_badge');
            if (!$id_badge && $this->object && $this->object->id) {
                $id_badge = (int)$this->object->id;
            }
            if ($id_badge) {
                $raw = trim(Tools::getValue('assigned_products'));
                $ids = [];
                if ($raw !== '') {
                    $parts = preg_split('/[,\s]+/', $raw);
                    foreach ($parts as $p) {
                        $n = (int)$p;
                        if ($n > 0) {
                            $ids[] = $n;
                        }
                    }
                }

                // Delete existing
                Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'pb_product_badge` WHERE id_badge='.(int)$id_badge);
                // Insert new
                foreach ($ids as $id_product) {
                    Db::getInstance()->insert('pb_product_badge', ['id_product' => (int)$id_product, 'id_badge' => (int)$id_badge]);
                }
            }
        }

        // Multistore associations are handled by the Badge ObjectModel (add/update/delete)
    }
}
