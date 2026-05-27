<?php
function productbadges_install()
{
    $db = Db::getInstance();
    $queries = [];

    $queries[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."pb_badge` (
        `id_badge` int(11) NOT NULL AUTO_INCREMENT,
        `bg_color` varchar(32) NOT NULL DEFAULT '#ff0000',
        `text_color` varchar(32) NOT NULL DEFAULT '#ffffff',
        `position` enum('left','right') NOT NULL DEFAULT 'left',
        `active` tinyint(1) NOT NULL DEFAULT '1',
        PRIMARY KEY (`id_badge`)
    ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8";

    $queries[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."pb_badge_lang` (
        `id_badge` int(11) NOT NULL,
        `id_lang` int(11) NOT NULL,
        `text` varchar(255) NOT NULL,
        PRIMARY KEY (`id_badge`,`id_lang`)
    ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8";

    $queries[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."pb_product_badge` (
        `id_product` int(11) NOT NULL,
        `id_badge` int(11) NOT NULL,
        PRIMARY KEY (`id_product`,`id_badge`)
    ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8";

    $queries[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."pb_badge_shop` (
        `id_badge` int(11) NOT NULL,
        `id_shop` int(11) NOT NULL,
        PRIMARY KEY (`id_badge`,`id_shop`)
    ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8";

    foreach ($queries as $sql) {
        if (!$db->execute($sql)) {
            return false;
        }
    }

    // If multishop is active, associate existing badges to all shops by default
    if (Shop::isFeatureActive()) {
        $shops = $db->executeS('SELECT id_shop FROM `'._DB_PREFIX_.'shop`');
        $badges = $db->executeS('SELECT id_badge FROM `'._DB_PREFIX_.'pb_badge`');
        if ($shops && $badges) {
            foreach ($badges as $b) {
                foreach ($shops as $s) {
                    $db->insert('pb_badge_shop', ['id_badge' => (int)$b['id_badge'], 'id_shop' => (int)$s['id_shop']]);
                }
            }
        }
    }

    return true;
}
