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

class ArrayToTextTable {

    const AlignLeft   = STR_PAD_RIGHT;
    const AlignCenter = STR_PAD_BOTH;
    const AlignRight  = STR_PAD_LEFT;

    protected $data;
    protected $keys;
    protected $widths;
    protected $decorator;
    protected $indentation;
    protected $displayKeys;
    protected $upperKeys;
    protected $keysAlignment;
    protected $valuesAlignment;
    protected $formatter;

    public function __construct($data = []) {
        $this->setData($data)
            ->setDecorator(new \Zend\Text\Table\Decorator\Unicode())
            ->setIndentation('')
            ->setDisplayKeys('auto')
            ->setUpperKeys(true)
            ->setKeysAlignment(self::AlignCenter)
            ->setValuesAlignment(self::AlignLeft)
            ->setFormatter(null);
    }

    public function __toString() {
        return $this->getTable();
    }

    public function getTable($data = null) {
        if (!is_null($data))
            $this->setData($data);

        $data = $this->prepare();
        $i = $this->indentation;
        $d = $this->decorator;

        $displayKeys = $this->displayKeys;
        if ($displayKeys === 'auto') {
            $displayKeys = false;
            foreach ($this->keys as $key)
                if (!is_int($key)) {
                    $displayKeys = true;
                    break;
                }
        }

        $table = $i . $this->line($d->getTopLeft(), $d->getHorizontal(), $d->getHorizontalDown(), $d->getTopRight()) . PHP_EOL;

        if ($displayKeys) {
            $keysRow = array_combine($this->keys, $this->keys);
            if ($this->upperKeys)
                $keysRow = array_map('mb_strtoupper', $keysRow);
            $table .= $i . implode(PHP_EOL, $this->row($keysRow, $this->keysAlignment)) . PHP_EOL;

            $table .= $i . $this->line($d->getVerticalRight(), $d->getHorizontal(), $d->getCross(), $d->getVerticalLeft()) . PHP_EOL;
        }

        foreach ($data as $row)
            $table .= $i . implode(PHP_EOL, $this->row($row, $this->valuesAlignment)) . PHP_EOL;

        $table .= $i . $this->line($d->getBottomLeft(), $d->getHorizontal(), $d->getHorizontalUp(), $d->getBottomRight()) . PHP_EOL;

        return $table;
    }

    public function getData() {
        return $this->data;
    }

    public function getDecorator() {
        return $this->decorator;
    }

    public function getIndentation() {
        return $this->indentation;
    }

    public function getDisplayKeys() {
        return $this->displayKeys;
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

    public function getFormatter() {
        return $this->formatter;
    }

    public function setData($data) {
        if (!is_array($data))
            $data = [];

        $arrayData = [];
        foreach ($data as $row) {
            if (is_array($row))
                $arrayData[] = $row;
            else if (is_object($row))
                $arrayData[] = get_object_vars($row);
        }

        $this->data = $arrayData;
        return $this;
    }

    public function setDecorator($decorator) {
        $this->decorator = $decorator;
        return $this;
    }

    public function setIndentation($indentation) {
        $this->indentation = $indentation;
        return $this;
    }

    public function setDisplayKeys($displayKeys) {
        $this->displayKeys = $displayKeys;
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

    public function setFormatter($formatter) {
        $this->formatter = $formatter;
        return $this;
    }

    protected function line($left, $horizontal, $link, $right) {
        $line = $left;
        foreach ($this->keys as $key)
            $line .= str_repeat($horizontal, $this->widths[$key]+2) . $link;

        if (mb_strlen($line) > mb_strlen($left))
            $line = mb_substr($line, 0, -mb_strlen($horizontal));

        return $line . $right;
    }

    protected function row($row, $alignment) {
        $data = [];
        $height = 1;
        foreach ($this->keys as $key) {
            $data[$key] = isset($row[$key]) ? static::valueToLines($row[$key]) : [''];
            $height = max($height, count($data[$key]));
        }

        $rowLines = [];
        for ($i = 0; $i < $height; $i++) {
            $rowLine = [];
            foreach ($data as $key => $value)
                $rowLine[$key] = isset($value[$i]) ? $value[$i] : '';
            $rowLines[] = $this->rowLine($rowLine, $alignment);
        }

        return $rowLines;
    }

    protected function rowLine($row, $alignment) {
        $line = $this->decorator->getVertical();

        foreach ($row as $key => $value)
            $line .= ' ' . static::mb_str_pad($value, $this->widths[$key], ' ', $alignment) . ' ' . $this->decorator->getVertical();

        if (empty($row))
            $line .= $this->decorator->getVertical();

        return $line;
    }

    protected function prepare() {
        $this->keys = [];
        $this->widths = [];

        $data = $this->data;

        if ($this->formatter instanceof \Closure) {
            foreach ($data as &$row)
                array_walk($row, $this->formatter, $this);
            unset($row);
        }

        foreach ($data as $row)
            $this->keys = array_merge($this->keys, array_keys($row));
        $this->keys = array_unique($this->keys);

        foreach ($this->keys as $key)
            $this->setWidth($key, $key);

        foreach ($data as $row)
            foreach ($row as $columnKey => $columnValue)
                $this->setWidth($columnKey, $columnValue);

        return $data;
    }

    protected static function countCJK($string) {
        return preg_match_all('/[\p{Han}\p{Katakana}\p{Hiragana}\p{Hangul}]/u', $string);
    }

    protected function setWidth($key, $value) {
        if (!isset($this->widths[$key]))
            $this->widths[$key] = 0;

        foreach (static::valueToLines($value) as $line) {
            $width = mb_strlen($line) + self::countCJK($line);
            if ($width > $this->widths[$key])
                $this->widths[$key] = $width;
        }
    }

    protected static function valueToLines($value) {
        return explode("\n", $value);
    }

    protected static function mb_str_pad($input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT, $encoding = null) {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;
        $pad_before = $pad_type === STR_PAD_BOTH || $pad_type === STR_PAD_LEFT;
        $pad_after = $pad_type === STR_PAD_BOTH || $pad_type === STR_PAD_RIGHT;
        $pad_length -= mb_strlen($input, $encoding) + self::countCJK($input);
        $target_length = $pad_before && $pad_after ? $pad_length / 2 : $pad_length;

        $repeat_times = ceil($target_length / mb_strlen($pad_string, $encoding));
        $repeated_string = str_repeat($pad_string, max(0, $repeat_times));
        $before = $pad_before ? mb_substr($repeated_string, 0, floor($target_length), $encoding) : '';
        $after = $pad_after ? mb_substr($repeated_string, 0, ceil($target_length), $encoding) : '';

        return $before . $input . $after;
    }

}
