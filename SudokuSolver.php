<?php

class SudokuSolver {

    private $sudokuInput;
    private $sudokuMatrix;
    private $sudokuBlocks;

    /**
     * SudokuResolver constructor.
     *
     * @param array $sudokuInput - array of sudoku puzzle cells where empty fields marked by 0 (zero):
     *                           e.g.: [0, 0, 2, ... , 3, 2, 0]
     */
    public function __construct(array $sudokuInput) {
        $this->sudokuInput = $sudokuInput;
        $this->sudokuBlocks = [[0, 1, 2], [3, 4, 5], [6, 7, 8]];
        $this->sudokuMatrix = [[], [], [], [], [], [], [], [], []];
    }

    public static function output($sudokuArray): void {

        for ($i = 0; $i < 81; $i++) {
            echo "{$sudokuArray[$i]} ";

            if ($i !== 0 && $i % 27 === 26) {
                echo "<br/><br/>";
                continue;
            }

            if ($i !== 0 && $i % 9 === 8) {
                echo "<br/>";
                continue;
            }

            if ($i !== 0 && $i % 3 === 2) {
                echo "&nbsp;&nbsp;&nbsp;";
            }
        }
    }

    /**
     * @return array|string
     */
    public function resolve() {
        return ($error = $this->checkInput()) ? $error : $this->process();
    }

    private function checkInput(): ?string {
        return (count($this->sudokuInput) !== (9 * 9)) ? 'Input array could not be converted to sudoku matrix. Array length should be 81 elements long.' : null;
    }

    private function process(): array {
        $this->fillMatrix();
        $this->checkVariants();

        return call_user_func_array('array_merge', $this->sudokuMatrix);
    }

    private function fillMatrix(): void {

        for ($row = 0; $row < 9; $row++) {

            for ($column = 0; $column < 9; $column++) {
                $this->sudokuMatrix[$row][$column] = current($this->sudokuInput);
                next($this->sudokuInput);
            }
        }
    }

    /**
     * @param int $zeros - recursion flag
     */
    private function checkVariants($zeros = 0): void {

        for ($rowBlock = 0; $rowBlock < 3; $rowBlock++) {

            for ($columnBlock = 0; $columnBlock < 3; $columnBlock++) {
                $this->checkPossibilities(['row' => $this->sudokuBlocks[$rowBlock], 'col' => $this->sudokuBlocks[$columnBlock]]);
            }
        }

        if (($currentZeros = $this->countMatrixZeros()) !== $zeros) {
            $this->checkVariants($currentZeros);
        }
    }

    /**
     * @param array $b - array with indexes of current matrix block 3x3 for parsing
     */
    private function checkPossibilities(array $b): void {

        foreach ($b['row'] as $r) {

            foreach ($b['col'] as $c) {

                if ($this->sudokuMatrix[$r][$c] === 0) {
                    $possibleVariants = $this->excludeBlockItems($b);
                    $possibleVariants = $this->excludeRowColumnItems($r, $possibleVariants);
                    $possibleVariants = $this->excludeRowColumnItems($c, $possibleVariants, false);

                    if (count($possibleVariants) > 1) {
                        $this->checkPossibilitiesDeep($b, $r, $c, $possibleVariants);
                    } else {
                        $this->fillMatrixCell($r, $c, $possibleVariants);
                    }
                }
            }
        }
    }

    /**
     * @param array $b - array with indexes of current matrix block 3x3 for parsing
     * @param int   $r - current row index
     * @param int   $c - current column index
     * @param array $v - array of current variants
     */
    private function checkPossibilitiesDeep(array $b, int $r, int $c, array $v): void {

        foreach ($v as $variant) {

            if (
                $this->sudokuMatrix[$r][$c] === 0 &&
                ($upperBottomBlocks = $this->checkNeighbourBlocks($b, $variant, $r, $c)) !== false &&
                ($leftRightBlocks = $this->checkNeighbourBlocks($b, $variant, $r, $c, true)) !== false &&
                !$this->checkNeighbourBlockLines($b, $upperBottomBlocks, $variant, $r, $c, true) &&
                $this->checkNeighbourBlockLines($b, $leftRightBlocks, $variant, $r, $c)
            ) {
                break;
            }
        }
    }

    /**
     * Check if variant exists in upper/bottom or left/right blocks regarding to current block,
     * if true we can set this variant in current row-column crossing.
     *
     * @param array $b     - array with indexes of current matrix block 3x3
     * @param int   $v     - current variant for cell
     * @param int   $r     - current row index
     * @param int   $c     - current column index
     * @param bool  $isRow - if is TRUE check will go by row, otherwise - column
     *
     * @return array|null - array with neighbour blocks or null if variant have been accepted
     */
    private function checkNeighbourBlocks(array $b, int $v, int $r, int $c, bool $isRow = false): ?array {
        $neighbourIndexes = [0, 1, 2];
        $currentBlockRowOrColumnIndex = array_search($b[($isRow) ? 'col' : 'row'], $this->sudokuBlocks);

        unset($neighbourIndexes[$currentBlockRowOrColumnIndex]);
        $neighbourIndexes = array_values($neighbourIndexes);

        $firstNeighbourBlock = [
            'row' => ($isRow) ? $b['row'] : $this->sudokuBlocks[$neighbourIndexes[0]],
            'col' => ($isRow) ? $this->sudokuBlocks[$neighbourIndexes[0]] : $b['col']
        ];
        $secondNeighbourBlock = [
            'row' => ($isRow) ? $b['row'] : $this->sudokuBlocks[$neighbourIndexes[1]],
            'col' => ($isRow) ? $this->sudokuBlocks[$neighbourIndexes[1]] : $b['col']
        ];

        $firstNeighbourBlockItems = $this->getBlockItems($firstNeighbourBlock);
        $secondNeighbourBlockItems = $this->getBlockItems($secondNeighbourBlock);

        if (array_search($v, $firstNeighbourBlockItems) !== false && array_search($v, $secondNeighbourBlockItems) !== false) {
            $check = [];

            foreach ($b[($isRow) ? 'col' : 'row'] as $line) {
                $crossing = ($isRow) ? [$r, $line] : [$line, $c];

                if ($this->sudokuMatrix[$crossing[0]][$crossing[1]] === 0) {
                    $check[] = $this->checkVariantInBlockLines($b, $v, ($isRow) ? $r : $c, !$isRow);
                }
            }

            if (!in_array(false, $check)) {
                $this->fillMatrixCell($r, $c, [$v]);

                return null;
            }
        }

        return [$firstNeighbourBlock, $secondNeighbourBlock];
    }

    /**
     * @param array $b     - array with indexes of current matrix block 3x3
     * @param array $n     - array with neighbour blocks
     * @param int   $v     - current variant for cell
     * @param int   $r     - current row index
     * @param int   $c     - current column index
     * @param bool  $isRow - if is TRUE check will go by row, otherwise - column
     *
     * @return bool
     */
    private function checkNeighbourBlockLines(array $b, array $n, int $v, int $r, int $c, bool $isRow = false): bool {
        $firstNeighbourBlockItems = $this->getBlockItems($n[0]);
        $secondNeighbourBlockItems = $this->getBlockItems($n[1]);

        if (array_search($v, $firstNeighbourBlockItems) !== false) {
            $firstNeighbourCheck = true;
        } else {
            $firstNeighbourCheck = $this->checkVariantInBlockLines($n[0], $v, ($isRow) ? $c : $r, $isRow);
        }

        if (array_search($v, $secondNeighbourBlockItems) !== false) {
            $secondNeighbourCheck = true;
        } else {
            $secondNeighbourCheck = $this->checkVariantInBlockLines($n[1], $v, ($isRow) ? $c : $r, $isRow);
        }

        if ($firstNeighbourCheck && $secondNeighbourCheck) {
            $currentBlockLines = [];
            $currentBlockLines[($isRow) ? 'row' : 'col'] = array_diff($b[($isRow) ? 'row' : 'col'], [($isRow) ? $r : $c]);
            $check = $this->checkVariantInBlockLines($currentBlockLines, $v, ($isRow) ? $c : $r, $isRow, false);

            if ($check) {
                $this->fillMatrixCell($r, $c, [$v]);

                return true;
            }
        }

        return false;
    }

    /**
     * @param array $b                - array with indexes of current matrix block 3x3
     * @param int   $v                - current variant for cell
     * @param int   $l                - row or column index
     * @param bool  $isRow            - if is TRUE check will go by row, otherwise - column
     * @param bool  $isNeighbourBlock - if is TRUE return will be for neighbour blocks, otherwise - current
     *
     * @return bool
     */
    private function checkVariantInBlockLines(array $b, int $v, int $l, bool $isRow, bool $isNeighbourBlock = true): bool {
        $check = [];
        $checkCount = 0;

        foreach ($b[($isRow) ? 'row' : 'col'] as $line) {
            $crossing = ($isRow) ? [$line, $l] : [$l, $line];
            $cell = $this->sudokuMatrix[$crossing[0]][$crossing[1]];

            if ($cell === 0) {
                $checkCount++;
                $check[] = array_search($v, $this->getLineItems($line, $isRow)) !== false ? 1 : 0;
            }
        }

        $check = array_filter($check);

        if (!$isNeighbourBlock) {
            return ($checkCount === count($check));
        }

        return ($checkCount > 0 && $checkCount === count($check));
    }

    /**
     * @param array $b - array with indexes of matrix block 3x3 for parsing
     *
     * @return array - array of existing numbers in block 3x3
     */
    private function getBlockItems(array $b): array {
        $blockItems = [];

        foreach ($b['row'] as $r) {

            foreach ($b['col'] as $c) {
                $cell = $this->sudokuMatrix[$r][$c];

                if ($cell !== 0) {
                    $blockItems[] = $cell;
                }
            }
        }

        return $blockItems;
    }

    /**
     * @param int  $l     - row or column index
     * @param bool $isRow - if is TRUE check will go by row, otherwise - column
     *
     * @return array
     */
    private function getLineItems(int $l, bool $isRow): array {
        $lineItems = [];
        $lines = range(0, 8);

        foreach ($lines as $line) {
            $crossing = ($isRow) ? [$l, $line] : [$line, $l];
            $cell = $this->sudokuMatrix[$crossing[0]][$crossing[1]];

            if ($cell !== 0) {
                $lineItems[] = $cell;
            }
        }

        return $lineItems;
    }

    /**
     * @param array $b - array with indexes of matrix block 3x3 for parsing
     *
     * @return array - array of variants after exclusion
     */
    private function excludeBlockItems(array $b): array {
        $v = range(1, 9);

        foreach ($b['row'] as $row) {

            foreach ($b['col'] as $column) {
                $cell = $this->sudokuMatrix[$row][$column];

                if ($cell !== 0 && ($i = array_search($cell, $v)) !== false) {
                    unset($v[$i]);
                    $v = array_values($v);
                }
            }
        }

        return $v;
    }

    /**
     * @param int   $l - row or column index for parsing
     * @param array $v     - current variants to exclude from
     * @param bool  $isRow - if is TRUE check will go by row, otherwise - column
     *
     * @return array - array of variants after exclusion
     */
    private function excludeRowColumnItems(int $l, array $v, bool $isRow = true): array {
        $lines = range(0, 8);

        foreach ($lines as $line) {
            $crossing = ($isRow) ? [$l, $line] : [$line, $l];
            $cell = $this->sudokuMatrix[$crossing[0]][$crossing[1]];

            if ($cell !== 0 && ($i = array_search($cell, $v)) !== false) {
                unset($v[$i]);
                $v = array_values($v);
            }
        }

        return $v;
    }

    /**
     * @param int   $r - row index
     * @param int   $c - column index
     * @param array $v - one item array with matrix cell correct number
     */
    private function fillMatrixCell(int $r, int $c, array $v): void {

        if (count($v) === 1) {
            $this->sudokuMatrix[$r][$c] = $v[0];
        }
    }

    /**
     * @return int
     */
    private function countMatrixZeros(): int {
        $zeros = 0;

        for ($row = 0; $row < 9; $row++) {

            for ($column = 0; $column < 9; $column++) {

                if ($this->sudokuMatrix[$row][$column] === 0) {
                    $zeros++;
                }
            }
        }

        return $zeros;
    }
}
