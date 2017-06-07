<?php

namespace WS\ReduceMigrations\Console\Pear;

define('CONSOLE_TABLE_HORIZONTAL_RULE', 1);
define('CONSOLE_TABLE_ALIGN_LEFT', -1);
define('CONSOLE_TABLE_ALIGN_CENTER', 0);
define('CONSOLE_TABLE_ALIGN_RIGHT', 1);
define('CONSOLE_TABLE_BORDER_ASCII', -1);

class ConsoleTable
{
    /**
     * @var array
     */
    private $headers = array();

    /**
     * @var array
     */
    private $data = array();

    /**
     * @var integer
     */
    private $maxCols = 0;

    /**
     * @var integer
     */
    private $maxRows = 0;

    /**
     * @var array
     */
    private $settingsCellLengths = array();

    /**
     * @var array
     */
    private $cellLengths = array();

    /**
     * @var array
     */
    private $rowHeights = array();

    /**
     * @var integer
     */
    private $padding = 1;

    /**
     * @var array
     */
    private $filters = array();

    /**
     * @var array
     */
    private $calculateTotals;

    /**
     * @var array
     */
    private $colAlign = array();

    /**
     * @var integer
     */
    private $defaultAlign;

    /**
     * @var string
     */
    private $charset = 'utf-8';

    /**
     * @var array
     */
    private $border = array(
        'intersection' => '+',
        'horizontal' => '-',
        'vertical' => '|',
    );

    /**
     * @var array
     */
    private $borderVisibility = array(
        'top'    => true,
        'right'  => true,
        'bottom' => true,
        'left'   => true,
        'inner'  => true
    );

    /**
     * Constructor.
     *
     * @param int $align Default alignment. One of
     *                         CONSOLE_TABLE_ALIGN_LEFT,
     *                         CONSOLE_TABLE_ALIGN_CENTER or
     *                         CONSOLE_TABLE_ALIGN_RIGHT.
     * @param int|string $border The character used for table borders or
     *                         CONSOLE_TABLE_BORDER_ASCII.
     * @param integer $padding How many spaces to use to pad the table.
     * @param string $charset A charset supported by the mbstring PHP
     *                         extension.
     */
    public function __construct($align = CONSOLE_TABLE_ALIGN_LEFT,
                         $border = CONSOLE_TABLE_BORDER_ASCII, $padding = 1,
                         $charset = null) {
        $this->defaultAlign = $align;
        $this->setBorder($border);
        $this->padding      = $padding;
        if (!empty($charset)) {
            $this->setCharset($charset);
        }
    }

    /**
     * Converts an array to a table.
     *
     * @param array   $headers      Headers for the table.
     * @param array   $data         A two dimensional array with the table
     *                              data.
     * @param boolean $returnObject Whether to return the Console_Table object
     *                              instead of the rendered table.
     *
     * @return ConsoleTable|string  A Console_Table object or the generated
     *                               table.
     */
    public static function fromArray($headers, $data, $returnObject = false) {
        if (!is_array($headers) || !is_array($data)) {
            return false;
        }

        $table = new ConsoleTable();
        $table->setCharset(LANG_CHARSET);

        $table->setHeaders($headers);

        foreach ($data as $row) {
            $table->addRow($row);
        }

        return $returnObject ? $table : $table->getTable();
    }

    /**
     * Adds a filter to a column.
     *
     * Filters are standard PHP callbacks which are run on the data before
     * table generation is performed. Filters are applied in the order they
     * are added. The callback function must accept a single argument, which
     * is a single table cell.
     *
     * @param integer $col       Column to apply filter to.
     * @param mixed   &$callback PHP callback to apply.
     *
     * @return void
     */
    public function addFilter($col, &$callback) {
        $this->filters[] = array($col, &$callback);
    }

    /**
     * Sets the charset of the provided table data.
     *
     * @param string $charset A charset supported by the mbstring PHP
     *                        extension.
     *
     * @return void
     */
    public function setCharset($charset) {
        $locale = setlocale(LC_CTYPE, 0);
        setlocale(LC_CTYPE, 'en_US');
        $this->charset = strtolower($charset);
        setlocale(LC_CTYPE, $locale);
    }

    /**
     * Set the table border settings
     *
     * Border definition modes:
     * - CONSOLE_TABLE_BORDER_ASCII: Default border with +, - and |
     * - array with keys "intersection", "horizontal" and "vertical"
     * - single character string that sets all three of the array keys
     *
     * @param mixed $border Border definition
     *
     * @return void
     * @see $border
     */
    public function setBorder($border) {
        $intersection = $horizontal = $vertical = '';
        if ($border === CONSOLE_TABLE_BORDER_ASCII) {
            $intersection = '+';
            $horizontal = '-';
            $vertical = '|';
        } else if (is_string($border)) {
            $intersection = $horizontal = $vertical = $border;
        } else if ($border == '') {
            $intersection = $horizontal = $vertical = '';
        } else {
            extract($border);
        }

        $this->border = array(
            'intersection' => $intersection,
            'horizontal' => $horizontal,
            'vertical' => $vertical,
        );
    }

    /**
     * Set which borders shall be shown.
     *
     * @param array $visibility Visibility settings.
     *                          Allowed keys: left, right, top, bottom, inner
     *
     * @return void
     * @see    $borderVisibility
     */
    public function setBorderVisibility($visibility) {
        $this->borderVisibility = array_merge(
            $this->borderVisibility,
            array_intersect_key(
                $visibility,
                $this->borderVisibility
            )
        );
    }

    /**
     * Sets the alignment for the columns.
     *
     * @param integer $col_id The column number.
     * @param integer $align  Alignment to set for this column. One of
     *                        CONSOLE_TABLE_ALIGN_LEFT
     *                        CONSOLE_TABLE_ALIGN_CENTER
     *                        CONSOLE_TABLE_ALIGN_RIGHT.
     *
     * @return void
     */
    public function setAlign($col_id, $align = CONSOLE_TABLE_ALIGN_LEFT) {
        switch ($align) {
        case CONSOLE_TABLE_ALIGN_CENTER:
            $pad = STR_PAD_BOTH;
            break;
        case CONSOLE_TABLE_ALIGN_RIGHT:
            $pad = STR_PAD_LEFT;
            break;
        default:
            $pad = STR_PAD_RIGHT;
            break;
        }
        $this->colAlign[$col_id] = $pad;
    }

    /**
     * Specifies which columns are to have totals calculated for them and
     * added as a new row at the bottom.
     *
     * @param array $cols Array of column numbers (starting with 0).
     *
     * @return void
     */
    public function calculateTotalsFor($cols) {
        $this->calculateTotals = $cols;
    }

    /**
     * Sets the headers for the columns.
     *
     * @param array $headers The column headers.
     *
     * @return void
     */
    public function setHeaders($headers) {
        $this->headers = array(array_values($headers));
        $this->updateRowsCols($headers);
    }

    /**
     * Adds a row to the table.
     *
     * @param array   $row    The row data to add.
     * @param boolean $append Whether to append or prepend the row.
     *
     * @return void
     */
    public function addRow($row, $append = true) {
        if ($append) {
            $this->data[] = array_values($row);
        } else {
            array_unshift($this->data, array_values($row));
        }

        $this->updateRowsCols($row);
    }

    /**
     * Inserts a row after a given row number in the table.
     *
     * If $row_id is not given it will prepend the row.
     *
     * @param array   $row    The data to insert.
     * @param integer $row_id Row number to insert before.
     *
     * @return void
     */
    public function insertRow($row, $row_id = 0) {
        array_splice($this->data, $row_id, 0, array($row));

        $this->updateRowsCols($row);
    }

    /**
     * Adds a column to the table.
     *
     * @param array   $col_data The data of the column.
     * @param integer $col_id   The column index to populate.
     * @param integer $row_id   If starting row is not zero, specify it here.
     *
     * @return void
     */
    public function addCol($col_data, $col_id = 0, $row_id = 0) {
        foreach ($col_data as $col_cell) {
            $this->data[$row_id++][$col_id] = $col_cell;
        }

        $this->updateRowsCols();
        $this->maxCols = max($this->maxCols, $col_id + 1);
    }

    /**
     * Adds data to the table.
     *
     * @param array   $data   A two dimensional array with the table data.
     * @param integer $col_id Starting column number.
     * @param integer $row_id Starting row number.
     *
     * @return void
     */
    public function addData($data, $col_id = 0, $row_id = 0) {
        foreach ($data as $row) {
            if ($row === CONSOLE_TABLE_HORIZONTAL_RULE) {
                $this->data[$row_id] = CONSOLE_TABLE_HORIZONTAL_RULE;
                $row_id++;
                continue;
            }
            $starting_col = $col_id;
            foreach ($row as $cell) {
                $this->data[$row_id][$starting_col++] = $cell;
            }
            $this->updateRowsCols();
            $this->maxCols = max($this->maxCols, $starting_col);
            $row_id++;
        }
    }

    /**
     * Adds a horizontal seperator to the table.
     *
     * @return void
     */
    public function addSeparator() {
        $this->data[] = CONSOLE_TABLE_HORIZONTAL_RULE;
    }

    /**
     * Returns the generated table.
     *
     * @return string  The generated table.
     */
    public function getTable() {
        $this->applyFilters();
        $this->calculateTotals();
        $this->validateTable();

        return $this->buildTable();
    }

    public function setCellsLength($arLength) {
        $arLength = array_values($arLength);
        foreach ($arLength as $k => $colLen) {
            $this->settingsCellLengths[$k] = $colLen;
        }
    }

    public function getCellLength($cNum) {
        return $this->settingsCellLengths[$cNum] ?: $this->cellLengths[$cNum];
    }

    /**
     * Calculates totals for columns.
     *
     * @return void
     */
    private function calculateTotals() {
        if (empty($this->calculateTotals)) {
            return;
        }

        $this->addSeparator();

        $totals = array();
        foreach ($this->data as $row) {
            if (is_array($row)) {
                foreach ($this->calculateTotals as $columnID) {
                    $totals[$columnID] += $row[$columnID];
                }
            }
        }

        $this->data[] = $totals;
        $this->updateRowsCols();
    }

    /**
     * Applies any column filters to the data.
     *
     * @return void
     */
    private function applyFilters() {
        if (empty($this->filters)) {
            return;
        }

        foreach ($this->filters as $filter) {
            $column   = $filter[0];
            $callback = $filter[1];

            foreach ($this->data as $row_id => $row_data) {
                if ($row_data !== CONSOLE_TABLE_HORIZONTAL_RULE) {
                    $this->data[$row_id][$column] =
                        call_user_func($callback, $row_data[$column]);
                }
            }
        }
    }

    /**
     * Ensures that column and row counts are correct.
     *
     * @return void
     */
    private function validateTable() {
        if (!empty($this->headers)) {
            $this->calculateRowHeight(-1, $this->headers[0]);
        }

        for ($i = 0; $i < $this->maxRows; $i++) {
            for ($j = 0; $j < $this->maxCols; $j++) {
                if (!isset($this->data[$i][$j]) &&
                    (!isset($this->data[$i]) ||
                     $this->data[$i] !== CONSOLE_TABLE_HORIZONTAL_RULE)) {
                    $this->data[$i][$j] = '';
                }

            }
            $this->calculateRowHeight($i, $this->data[$i]);

            if ($this->data[$i] !== CONSOLE_TABLE_HORIZONTAL_RULE) {
                 ksort($this->data[$i]);
            }

        }

        $this->splitMultilineRows();

        // Update cell lengths.
        for ($i = 0; $i < count($this->headers); $i++) {
            $this->calculateCellLengths($this->headers[$i]);
        }
        for ($i = 0; $i < $this->maxRows; $i++) {
            $this->calculateCellLengths($this->data[$i]);
        }

        ksort($this->data);
    }

    /**
     * Splits multiline rows into many smaller one-line rows.
     *
     * @return void
     */
    private function splitMultilineRows() {
        ksort($this->data);
        $sections          = array(&$this->headers, &$this->data);
        $max_rows          = array(count($this->headers), $this->maxRows);
        $row_height_offset = array(-1, 0);

        for ($s = 0; $s <= 1; $s++) {
            $inserted = 0;
            $new_data = $sections[$s];

            for ($i = 0; $i < $max_rows[$s]; $i++) {
                // Process only rows that have many lines.
                $height = $this->rowHeights[$i + $row_height_offset[$s]];
                if ($height > 1) {
                    // Split column data into one-liners.
                    $split = array();
                    for ($j = 0; $j < $this->maxCols; $j++) {
                        $split[$j] = preg_split('/\r?\n|\r/',
                                                $sections[$s][$i][$j]);
                    }

                    $new_rows = array();
                    // Construct new 'virtual' rows - insert empty strings for
                    // columns that have less lines that the highest one.
                    for ($i2 = 0; $i2 < $height; $i2++) {
                        for ($j = 0; $j < $this->maxCols; $j++) {
                            $new_rows[$i2][$j] = !isset($split[$j][$i2])
                                ? ''
                                : $split[$j][$i2];
                        }
                    }

                    // Replace current row with smaller rows.  $inserted is
                    // used to take account of bigger array because of already
                    // inserted rows.
                    array_splice($new_data, $i + $inserted, 1, $new_rows);
                    $inserted += count($new_rows) - 1;
                }
            }

            // Has the data been modified?
            if ($inserted > 0) {
                $sections[$s] = $new_data;
                $this->updateRowsCols();
            }
        }
    }

    /**
     * Builds the table.
     *
     * @return string  The generated table string.
     */
    private function buildTable() {
        if (!count($this->data)) {
            return '';
        }

        $vertical = $this->border['vertical'];
        $separator = $this->getSeparator();

        $return = array();
        for ($i = 0; $i < count($this->data); $i++) {
            for ($j = 0; $j < count($this->data[$i]); $j++) {
                if ($this->strlen($this->data[$i][$j]) > $this->getCellLength($j)) {
                    $this->data[$i][$j] = $this->substr($this->data[$i][$j], 0, $this->getCellLength($j) - 2);
                    $this->data[$i][$j] .= "..";
                }
                if ($this->data[$i] !== CONSOLE_TABLE_HORIZONTAL_RULE &&
                    $this->strlen($this->data[$i][$j]) <
                    $this->getCellLength($j)) {
                    $this->data[$i][$j] = $this->strpad($this->data[$i][$j],
                                                          $this->getCellLength($j),
                                                          ' ',
                                                          $this->colAlign[$j]);
                }
            }

            if ($this->data[$i] !== CONSOLE_TABLE_HORIZONTAL_RULE) {
                $row_begin = $this->borderVisibility['left']
                    ? $vertical . str_repeat(' ', $this->padding)
                    : '';
                $row_end = $this->borderVisibility['right']
                    ? str_repeat(' ', $this->padding) . $vertical
                    : '';
                $implode_char = str_repeat(' ', $this->padding) . $vertical
                    . str_repeat(' ', $this->padding);
                $return[]     = $row_begin
                    . implode($implode_char, $this->data[$i]) . $row_end;
            } elseif (!empty($separator)) {
                $return[] = $separator;
            }
        }

        $return = implode(PHP_EOL, $return);
        if (!empty($separator)) {
            if ($this->borderVisibility['inner']) {
                $return = $separator . PHP_EOL . $return;
            }
            if ($this->borderVisibility['bottom']) {
                $return .= PHP_EOL . $separator;
            }
        }
        $return .= PHP_EOL;

        if (!empty($this->headers)) {
            $return = $this->getHeaderLine() .  PHP_EOL . $return;
        }

        return $return;
    }

    /**
     * Creates a horizontal separator for header separation and table
     * start/end etc.
     *
     * @return string  The horizontal separator.
     */
    private function getSeparator() {
        if (!$this->border) {
            return;
        }

        $horizontal = $this->border['horizontal'];
        $intersection = $this->border['intersection'];

        $return = array();
        foreach (array_keys($this->cellLengths) as $cl) {
            $return[] = str_repeat($horizontal, $this->getCellLength($cl));
        }

        $row_begin = $this->borderVisibility['left']
            ? $intersection . str_repeat($horizontal, $this->padding)
            : '';
        $row_end = $this->borderVisibility['right']
            ? str_repeat($horizontal, $this->padding) . $intersection
            : '';
        $implode_char = str_repeat($horizontal, $this->padding) . $intersection
            . str_repeat($horizontal, $this->padding);

        return $row_begin . implode($implode_char, $return) . $row_end;
    }

    /**
     * Returns the header line for the table.
     *
     * @return string  The header line of the table.
     */
    private function getHeaderLine() {
        // Make sure column count is correct
        for ($j = 0; $j < count($this->headers); $j++) {
            for ($i = 0; $i < $this->maxCols; $i++) {
                if (!isset($this->headers[$j][$i])) {
                    $this->headers[$j][$i] = '';
                }
            }
        }

        for ($j = 0; $j < count($this->headers); $j++) {
            for ($i = 0; $i < count($this->headers[$j]); $i++) {
                if ($this->strlen($this->headers[$j][$i]) <
                    $this->getCellLength($i)) {
                    $this->headers[$j][$i] =
                        $this->strpad($this->headers[$j][$i],
                                       $this->getCellLength($i),
                                       ' ',
                                       $this->colAlign[$i]);
                }
            }
        }

        $vertical = $this->border['vertical'];
        $row_begin = $this->borderVisibility['left']
            ? $vertical . str_repeat(' ', $this->padding)
            : '';
        $row_end = $this->borderVisibility['right']
            ? str_repeat(' ', $this->padding) . $vertical
            : '';
        $implode_char = str_repeat(' ', $this->padding) . $vertical
            . str_repeat(' ', $this->padding);

        $separator = $this->getSeparator();
        if (!empty($separator) && $this->borderVisibility['top']) {
            $return[] = $separator;
        }
        for ($j = 0; $j < count($this->headers); $j++) {
            $return[] = $row_begin
                . implode($implode_char, $this->headers[$j]) . $row_end;
        }

        return implode(PHP_EOL, $return);
    }

    /**
     * Updates values for maximum columns and rows.
     *
     * @param array $rowdata Data array of a single row.
     *
     * @return void
     */
    private function updateRowsCols($rowdata = null) {
        // Update maximum columns.
        $this->maxCols = max($this->maxCols, count($rowdata));

        // Update maximum rows.
        ksort($this->data);
        $keys = array_keys($this->data);
        $this->maxRows = end($keys) + 1;

        switch ($this->defaultAlign) {
            case CONSOLE_TABLE_ALIGN_CENTER:
                $pad = STR_PAD_BOTH;
                break;
            case CONSOLE_TABLE_ALIGN_RIGHT:
                $pad = STR_PAD_LEFT;
                break;
            default:
                $pad = STR_PAD_RIGHT;
                break;
        }

        // Set default column alignments
        for ($i = 0; $i < $this->maxCols; $i++) {
            if (!isset($this->colAlign[$i])) {
                $this->colAlign[$i] = $pad;
            }
        }
    }

    /**
     * Calculates the maximum length for each column of a row.
     *
     * @param array $row The row data.
     *
     * @return void
     */
    private function calculateCellLengths($row) {
        for ($i = 0; $i < count($row); $i++) {
            if (!isset($this->cellLengths[$i])) {
                $this->cellLengths[$i] = 0;
            }
            $this->cellLengths[$i] = max($this->cellLengths[$i],
                                           $this->strlen($row[$i]));
        }
    }

    /**
     * Calculates the maximum height for all columns of a row.
     *
     * @param integer $row_number The row number.
     * @param array   $row        The row data.
     *
     * @return void
     */
    private function calculateRowHeight($row_number, $row) {
        if (!isset($this->rowHeights[$row_number])) {
            $this->rowHeights[$row_number] = 1;
        }

        // Do not process horizontal rule rows.
        if ($row === CONSOLE_TABLE_HORIZONTAL_RULE) {
            return;
        }

        for ($i = 0, $c = count($row); $i < $c; ++$i) {
            $lines                           = preg_split('/\r?\n|\r/', $row[$i]);
            $this->rowHeights[$row_number] = max($this->rowHeights[$row_number],
                                                   count($lines));
        }
    }

    /**
     * Returns the character length of a string.
     *
     * @param string $str A multibyte or singlebyte string.
     *
     * @return integer  The string length.
     */
    private function strlen($str) {
        static $mbstring;

        // Cache expensive function_exists() calls.
        if (!isset($mbstring)) {
            $mbstring = function_exists('mb_strlen');
        }

        if ($mbstring) {
            return mb_strlen($str, $this->charset);
        }

        return strlen($str);
    }

    /**
     * Returns part of a string.
     *
     * @param string  $string The string to be converted.
     * @param integer $start  The part's start position, zero based.
     * @param integer $length The part's length.
     *
     * @return string  The string's part.
     */
    private function substr($string, $start, $length = null) {
        static $mbstring;

        // Cache expensive function_exists() calls.
        if (!isset($mbstring)) {
            $mbstring = function_exists('mb_substr');
        }

        if (is_null($length)) {
            $length = $this->strlen($string);
        }
        if ($mbstring) {
            $ret = @mb_substr($string, $start, $length, $this->charset);
            if (!empty($ret)) {
                return $ret;
            }
        }
        return substr($string, $start, $length);
    }

    /**
     * Returns a string padded to a certain length with another string.
     *
     * This method behaves exactly like str_pad but is multibyte safe.
     *
     * @param string  $input  The string to be padded.
     * @param integer $length The length of the resulting string.
     * @param string  $pad    The string to pad the input string with. Must
     *                        be in the same charset like the input string.
     * @param const   $type   The padding type. One of STR_PAD_LEFT,
     *                        STR_PAD_RIGHT, or STR_PAD_BOTH.
     *
     * @return string  The padded string.
     */
    private function strpad($input, $length, $pad = ' ', $type = STR_PAD_RIGHT) {
        $mbLength  = $this->strlen($input);
        $sbLength  = strlen($input);
        $padLength = $this->strlen($pad);

        /* Return if we already have the length. */
        if ($mbLength >= $length) {
            return $input;
        }

        /* Shortcut for single byte strings. */
        if ($mbLength == $sbLength && $padLength == strlen($pad)) {
            return str_pad($input, $length, $pad, $type);
        }

        switch ($type) {
            case STR_PAD_LEFT:
                $left   = $length - $mbLength;
                $output = $this->substr(str_repeat($pad, ceil($left / $padLength)),
                                         0, $left) . $input;
                break;
            case STR_PAD_BOTH:
                $left   = floor(($length - $mbLength) / 2);
                $right  = ceil(($length - $mbLength) / 2);
                $output = $this->substr(str_repeat($pad, ceil($left / $padLength)),
                                         0, $left) .
                    $input .
                    $this->substr(str_repeat($pad, ceil($right / $padLength)),
                                   0, $right);
                break;
            case STR_PAD_RIGHT:
                $right  = $length - $mbLength;
                $output = $input .
                    $this->substr(str_repeat($pad, ceil($right / $padLength)),
                                   0, $right);
                break;
        }

        return $output;
    }

}
