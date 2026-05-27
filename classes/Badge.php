<?php
class Badge extends ObjectModel
{
    public $id_badge;
    public $bg_color;
    public $text_color;
    public $position = 'left';
    public $active = 1;
    public $text;

    public static $definition = [
        'table' => 'pb_badge',
        'primary' => 'id_badge',
        'multilang' => true,
        'fields' => [
            // Colors should be validated as hex color strings
            'bg_color' => ['type' => self::TYPE_STRING, 'validate' => 'isColor', 'size' => 32],
            'text_color' => ['type' => self::TYPE_STRING, 'validate' => 'isColor', 'size' => 32],
            // Position must be left/right
            'position' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 6],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            // Text should be cleaned/validated server-side; `isCleanHtml` allows limited HTML,
            // but we will sanitize in controller (strip tags) so `isGenericName` is acceptable.
            'text' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255],
        ],
    ];
}
