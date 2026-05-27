<?php
function productbadges_uninstall()
{
    $db = Db::getInstance();
    $tables = ['pb_product_badge', 'pb_badge_lang', 'pb_badge', 'pb_badge_shop'];
    foreach ($tables as $table) {
        $db->execute('DROP TABLE IF EXISTS `'. _DB_PREFIX_ . $table .'`');
    }
    return true;
}
