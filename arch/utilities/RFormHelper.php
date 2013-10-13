<?php
/**
 * RFormHelper class files
 * @author: Raysmond
 */

class RFormHelper
{

    public static function openForm($action = '', $attributes = '')
    {
        if ($action && strpos("://", $action) === false) {
            $action = RHtmlHelper::siteUrl($action);
        }
        $defaults = array('method' => 'post');

        $form = '<form action="' . $action . '" ' . self::parseAttributes($attributes, $defaults) . ">";

        return $form;
    }

    public static function endForm()
    {
        return '</form>';
    }

    public static function input($data = '', $value = '')
    {
        $name = self::setName($data);
        $value = self::setValue($value, $name);
        $defaults = array('type' => 'text', 'name' => $name, 'value' => $value);
        return '<input ' . self::parseAttributes($data, $defaults) . ' />';
    }

    public static function hidden($data = '', $value = '')
    {
        $defaults = array('type' => 'hidden', 'name' => (is_array($data) ? '' : $data), 'value' => $value);
        return '<input ' . self::parseAttributes($data, $defaults) . ' />';
    }

    public static function label($text = '', $forId = '', $attributes = '')
    {
        $html = '<label ' . (($forId != '') ? 'for="' . $forId . '" ' : '');
        return $html . self::parseAttributes($attributes) . '>' . RHtmlHelper::encode($text) . '</label>';
    }

    public static function button($data = '', $value = '')
    {
        $defaults = array('name' => (is_array($data) ? '' : $data), 'value' => $value);
        return '<input ' . self::parseAttributes($data, $defaults) . ' />';
    }

    public static function textarea($data = '', $value = '')
    {

    }

    private static function parseAttributes($attributes, $defaults = array())
    {
        if (is_array($attributes)) {
            foreach ($defaults as $key => $val) {
                if (isset($attributes[$key]))
                    $defaults[$key] = $attributes[$key];
                unset($attributes[$key]);
            }
            if (count($attributes) > 0)
                $defaults = array_merge($defaults, $attributes);
        }
        $html = '';
        foreach ($defaults as $key => $val) {
            $html .= $key . '="' . RHtmlHelper::encode($val) . '" ';
        }
        return $html;
    }

    private static function setValue($value = '', $key = '')
    {
        if (is_array($value) && isset($value[$key]))
            return $value[$key];
        else return is_array($value) ? '' : $value;
    }

    private static function setName($name = '')
    {
        return self::setValue($name, 'name');
    }
}