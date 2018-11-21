# Sudoku Solver Class

Tested with https://www.websudoku.com/ on Hard level with no luck on Evil. Class do not have logic to predict possible number in cell (future plans). 


_Test Cases:_

First

$caseOne = [3, 0, 6, 5, 0, 8, 4, 0, 0, 5, 2, 0, 0, 0, 0, 0, 0, 0, 0, 8, 7, 0, 0, 0, 0, 3, 1, 0, 0, 3, 0, 1, 0, 0, 8, 0, 9, 0, 0, 8, 6, 3, 0, 0, 5, 0, 5, 0, 0, 9, 0, 6, 0, 0, 1, 3, 0, 0, 0, 0, 2, 5, 0, 0, 0, 0, 0, 0, 0, 0, 7, 4, 0, 0, 5, 2, 0, 6, 3, 0, 0];

$ssOne = new SudokuSolver($caseOne);

SudokuResolver::output($ssOne->resolve());

    Input:
    3 0 6    5 0 8    4 0 0
    5 2 0    0 0 0    0 0 0
    0 8 7    0 0 0    0 3 1
    
    0 0 3    0 1 0    0 8 0
    9 0 0    8 6 3    0 0 5
    0 5 0    0 9 0    6 0 0
    
    1 3 0    0 0 0    2 5 0
    0 0 0    0 0 0    0 7 4
    0 0 5    2 0 6    3 0 0 

    Output:
    3 1 6    5 7 8    4 9 2
    5 2 9    1 3 4    7 6 8
    4 8 7    6 2 9    5 3 1
    
    2 6 3    4 1 5    9 8 7
    9 7 4    8 6 3    1 2 5
    8 5 1    7 9 2    6 4 3
    
    1 3 8    9 4 7    2 5 6
    6 9 2    3 5 1    8 7 4
    7 4 5    2 8 6    3 1 9 

Second

$caseTwo = [0, 0, 0, 9, 4, 0, 0, 0, 7, 0, 5, 0, 6, 2, 0, 9, 0, 0, 6, 0, 0, 0, 0, 0, 1, 3, 0, 0, 7, 0, 0, 6, 2, 0, 0, 0, 0, 0, 0, 1, 0, 5, 0, 0, 0, 0, 0, 0, 4, 7, 0, 0, 2, 0, 0, 2, 7, 0, 0, 0, 0, 0, 9, 0, 0, 5, 0, 8, 6, 0, 4, 0, 8, 0, 0, 0, 9, 7, 0, 0, 0];

$ssTwo = new SudokuSolver($caseTwo);

SudokuResolver::output($ssTwo->resolve());

    Input:
    0 0 0    9 4 0    0 0 7
    0 5 0    6 2 0    9 0 0
    6 0 0    0 0 0    1 3 0
    
    0 7 0    0 6 2    0 0 0
    0 0 0    1 0 5    0 0 0
    0 0 0    4 7 0    0 2 0
    
    0 2 7    0 0 0    0 0 9
    0 0 5    0 8 6    0 4 0
    8 0 0    0 9 7    0 0 0


    Output:
    2 3 8    9 4 1    6 5 7
    7 5 1    6 2 3    9 8 4
    6 4 9    7 5 8    1 3 2
    
    4 7 3    8 6 2    5 9 1
    9 8 2    1 3 5    4 7 6
    5 1 6    4 7 9    3 2 8
    
    3 2 7    5 1 4    8 6 9
    1 9 5    2 8 6    7 4 3
    8 6 4    3 9 7    2 1 5 
