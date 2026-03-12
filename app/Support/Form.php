<?php

namespace App\Support;

use Illuminate\Contracts\Support\Arrayable;
use Traversable;

class Form
{
    /**
     * Open a form tag and append hidden method / csrf fields when needed.
     */
    public static function open(array $options = []): string
    {
        $method = strtoupper((string) ($options['method'] ?? 'POST'));
        $action = static::resolveAction($options);
        $files = (bool) ($options['files'] ?? false);

        unset($options['method'], $options['url'], $options['route'], $options['action'], $options['files']);

        $spoofMethod = null;
        if (!in_array($method, ['GET', 'POST'], true)) {
            $spoofMethod = $method;
            $method = 'POST';
        }

        $attributes = array_merge(['method' => strtolower($method), 'action' => $action], $options);
        if ($files) {
            $attributes['enctype'] = 'multipart/form-data';
        }

        $html = '<form' . static::attributes($attributes) . '>';

        if ($spoofMethod !== null) {
            $html .= static::hidden('_method', $spoofMethod);
        }

        if ($method !== 'GET') {
            $html .= static::hidden('_token', csrf_token());
        }

        return $html;
    }

    public static function close(): string
    {
        return '</form>';
    }

    public static function label(string $name, ?string $value = null, array $options = []): string
    {
        $options['for'] = $options['for'] ?? $name;
        $text = $value ?? ucwords(str_replace('_', ' ', $name));

        return '<label' . static::attributes($options) . '>' . e($text) . '</label>';
    }

    public static function text(string $name, $value = null, array $options = []): string
    {
        $value = static::resolveValue($name, $value);

        return static::input('text', $name, $value, $options);
    }

    public static function textarea(string $name, $value = null, array $options = []): string
    {
        $value = static::resolveValue($name, $value);
        $options['name'] = $name;
        $options['id'] = $options['id'] ?? $name;

        return '<textarea' . static::attributes($options) . '>' . e((string) $value) . '</textarea>';
    }

    public static function hidden(string $name, $value = null, array $options = []): string
    {
        return static::input('hidden', $name, $value, $options);
    }

    public static function checkbox(string $name, $value = 1, bool $checked = false, array $options = []): string
    {
        if ($value === null) {
            $value = 1;
        }

        $oldKey = str_ends_with($name, '[]') ? substr($name, 0, -2) : $name;
        $hasOldInput = session()->hasOldInput($oldKey);

        if ($hasOldInput) {
            $oldValue = old($oldKey);
            $checked = static::isCheckboxCheckedFromOld($oldValue, $value);
        }

        if ($checked) {
            $options['checked'] = true;
        }

        return static::input('checkbox', $name, $value, $options);
    }

    public static function select(string $name, $list = [], $selected = null, array $options = []): string
    {
        $list = static::normalizeSelectList($list);
        $selected = $selected ?? old($name);
        $options['name'] = $name;
        $options['id'] = $options['id'] ?? $name;

        $isMultiple = isset($options['multiple']) && (bool) $options['multiple'];
        if ($isMultiple && substr($name, -2) !== '[]') {
            $options['name'] = $name . '[]';
        }

        $html = '<select' . static::attributes($options) . '>';

        foreach ($list as $value => $display) {
            if (is_array($display)) {
                $html .= '<optgroup label="' . e((string) $value) . '">';
                foreach ($display as $groupValue => $groupDisplay) {
                    $html .= static::option($groupValue, $groupDisplay, $selected);
                }
                $html .= '</optgroup>';
                continue;
            }

            $html .= static::option($value, $display, $selected);
        }

        $html .= '</select>';

        return $html;
    }

    public static function submit(string $value = 'Submit', array $options = []): string
    {
        $attributes = array_merge(['type' => 'submit', 'value' => $value], $options);

        return '<input' . static::attributes($attributes) . '>';
    }

    public static function button(string $value = null, array $options = []): string
    {
        $text = $value ?? '';
        $options['type'] = $options['type'] ?? 'button';

        return '<button' . static::attributes($options) . '>' . $text . '</button>';
    }

    private static function option($value, $display, $selected): string
    {
        $attributes = ['value' => (string) $value];
        if (static::isSelected($value, $selected)) {
            $attributes['selected'] = true;
        }

        return '<option' . static::attributes($attributes) . '>' . e((string) $display) . '</option>';
    }

    private static function input(string $type, string $name, $value, array $options = []): string
    {
        if (!array_key_exists('id', $options) && substr($name, -2) !== '[]') {
            $options['id'] = $name;
        }

        $attributes = array_merge(
            ['type' => $type, 'name' => $name, 'value' => (string) ($value ?? '')],
            $options
        );

        return '<input' . static::attributes($attributes) . '>';
    }

    private static function resolveAction(array $options): string
    {
        if (array_key_exists('url', $options)) {
            return (string) $options['url'];
        }

        if (array_key_exists('route', $options)) {
            $route = $options['route'];
            if (is_array($route)) {
                $name = array_shift($route);

                return route((string) $name, $route);
            }

            return route((string) $route);
        }

        if (array_key_exists('action', $options)) {
            $action = $options['action'];
            if (is_array($action)) {
                $name = array_shift($action);

                return action((string) $name, $action);
            }

            return action((string) $action);
        }

        return '';
    }

    private static function resolveValue(string $name, $value): string
    {
        if ($value !== null) {
            return (string) $value;
        }

        $old = old($name);

        return $old === null ? '' : (string) $old;
    }

    private static function isSelected($value, $selected): bool
    {
        if (is_array($selected)) {
            return in_array((string) $value, array_map('strval', $selected), true);
        }

        if ($selected === null) {
            return false;
        }

        return (string) $value === (string) $selected;
    }

    private static function isCheckboxCheckedFromOld($oldValue, $value): bool
    {
        if (is_array($oldValue)) {
            return in_array((string) $value, array_map('strval', $oldValue), true);
        }

        if (is_bool($oldValue)) {
            return $oldValue;
        }

        if ($oldValue === null) {
            return false;
        }

        $oldString = strtolower((string) $oldValue);

        if ((string) $value === (string) $oldValue) {
            return true;
        }

        return in_array($oldString, ['1', 'true', 'on', 'yes'], true);
    }

    private static function attributes(array $attributes): string
    {
        $compiled = '';
        foreach ($attributes as $key => $value) {
            if (is_int($key) || $value === null || $value === false) {
                continue;
            }

            if ($value === true) {
                $compiled .= ' ' . e((string) $key);
                continue;
            }

            $compiled .= ' ' . e((string) $key) . '="' . e((string) $value) . '"';
        }

        return $compiled;
    }

    private static function normalizeSelectList($list): array
    {
        if ($list instanceof Arrayable) {
            $list = $list->toArray();
        } elseif ($list instanceof Traversable) {
            $list = iterator_to_array($list);
        }

        if (!is_array($list)) {
            return [];
        }

        foreach ($list as $value => $display) {
            if ($display instanceof Arrayable) {
                $list[$value] = $display->toArray();
                continue;
            }

            if ($display instanceof Traversable) {
                $list[$value] = iterator_to_array($display);
            }
        }

        return $list;
    }
}
