<?php

/**
 * ArrayToTextTable
 *
 * Display arrays in terminal
 *
 * @author      Mathieu Viossat <mathieu@viossat.fr>
 * @copyright   Copyright (c) 2015 Mathieu Viossat
 * @license     http://opensource.org/licenses/MIT
 * @link        https://github.com/MathieuViossat/arraytotexttable
 */

namespace MathieuViossat\Util;

use \Zend\Text\Table\Decorator\Unicode;

class ArrayToTextTable {

    const AlignLeft   = STR_PAD_RIGHT;
    const AlignCenter = STR_PAD_BOTH;
    const AlignRight  = STR_PAD_LEFT;

    protected $table;
    protected $keys;
    protected $widths;
    protected $decorator;
    protected $indentation;
    protected $upperKeys;
    protected $keysAlignment;
    protected $valuesAlignment;

    public function __construct($table) {
        $this->table = $table;

        $this->keys = [];
        foreach ($this->table as $row)
            $this->keys = array_merge($this->keys, array_keys($row));
        $this->keys = array_unique($this->keys);

        foreach ($this->keys as $key)
            $this->setWidth($key, $key);

        foreach ($this->table as $row)
            foreach ($row as $columnKey => $columnValue)
                $this->setWidth($columnKey, $columnValue);

        $this->setDecorator(new \Zend\Text\Table\Decorator\Unicode())
            ->setIndentation('')
            ->setUpperKeys(true)
            ->setKeysAlignment(self::AlignCenter)
            ->setValuesAlignment(self::AlignLeft);
    }

    public function getTable() {
        $i = $this->indentation;
        $d = $this->decorator;

        $table = $i . $this->line($d->getTopLeft(), $d->getHorizontal(), $d->getHorizontalDown(), $d->getTopRight()) . PHP_EOL;

        $keysRow = array_combine($this->keys, $this->keys);
        if ($this->upperKeys)
            $keysRow = array_map('mb_strtoupper', $keysRow);
        $table .= $i . $this->row($keysRow, $this->keysAlignment) . PHP_EOL;

        $table .= $i . $this->line($d->getVerticalRight(), $d->getHorizontal(), $d->getCross(), $d->getVerticalLeft()) . PHP_EOL;

        foreach ($this->table as $row)
            $table .= $i . $this->row($row, $this->valuesAlignment) . PHP_EOL;

        $table .= $i . $this->line($d->getBottomLeft(), $d->getHorizontal(), $d->getHorizontalUp(), $d->getBottomRight()) . PHP_EOL;

        return $table;
    }

    public function getDecorator() {
        return $this->decorator;
    }

    public function getIndentation() {
        return $this->indentation;
    }

    public function getUpperKeys() {
        return $this->upperKeys;
    }

    public function getKeysAlignment() {
        return $this->keysAlignment;
    }

    public function getValuesAlignment() {
        return $this->valuesAlignment;
    }

    public function setDecorator($decorator) {
        $this->decorator = $decorator;
        return $this;
    }

    public function setIndentation($indentation) {
        $this->indentation = $indentation;
        return $this;
    }

    public function setUpperKeys($upperKeys) {
        $this->upperKeys = $upperKeys;
        return $this;
    }

    public function setKeysAlignment($keysAlignment) {
        $this->keysAlignment = $keysAlignment;
        return $this;
    }

    public function setValuesAlignment($valuesAlignment) {
        $this->valuesAlignment = $valuesAlignment;
        return $this;
    }

    protected function line($left, $horizontal, $link, $right) {
        $line = $left;
        foreach ($this->keys as $key)
            $line .= str_repeat($horizontal, $this->widths[$key]+2) . $link;
        return mb_substr($line, 0, -1) . $right;
    }

    protected function row($row, $alignment) {
        $line = $this->decorator->getVertical();
        foreach ($this->keys as $key) {
            $value = isset($row[$key]) ? $row[$key] : '';
            $line .= ' ' . static::mb_str_pad($value, $this->widths[$key], ' ', $alignment) . ' ' . $this->decorator->getVertical();
        }
        return $line;
    }

    protected function setWidth($key, $value) {
        if (!isset($this->widths[$key]))
            $this->widths[$key] = 0;

        $width = mb_strlen($value);
        if ($width > $this->widths[$key])
            $this->widths[$key] = $width;
    }

    protected static function mb_str_pad($input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT, $encoding = null) {
        if ($encoding === null)
            $encoding = mb_internal_encoding();

        $diff = strlen($input) - mb_strlen($input, $encoding);
        return str_pad($input, $pad_length + $diff, $pad_string, $pad_type);
    }

}
