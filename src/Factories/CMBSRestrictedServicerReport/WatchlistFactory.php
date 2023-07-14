<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;


/**
 * FYI, the parse function works perfectly. 2023-07-12:mdd
 */
class WatchlistFactory extends AbstractTabFactory {
    protected array $firstColumnValidTextValues = [ 'Trans ID', 'Trans Id', 'Trans' ];

    protected function _removeInvalidRows( array $rows = [] ): array {
        $validRows = [];
        foreach ( $rows as $i => $row ):

            $numNullCells           = 0;
            $nonIntegerFound        = FALSE;
            $numStartingWithLetterL = 0;
            foreach ( $row as $j => $value ):

                if ( empty( $value ) ):
                    $numNullCells++;
                endif;

                if (  isset($value) && !is_integer( (string)$value ) ):
                    $nonIntegerFound = TRUE;
                endif;

                if ( str_starts_with( strtoupper( $value ?? '' ), 'L' ) ):
                    $numStartingWithLetterL++;
                endif;
            endforeach;

            if ( $numNullCells > 8 ):
                continue;
            endif;

            // array:22 [
            //  0 => 1
            //  1 => 2
            //  2 => 3
            //  3 => 4
            //  4 => 5
            //  5 => 6
            //  6 => 7
            //  7 => 8
            //  8 => 9
            //  9 => 10
            //  10 => 11
            //  11 => 12
            //  12 => 13
            //  13 => 14
            //  14 => 15
            //  15 => 16
            //  16 => 17
            //  17 => 18
            //  18 => 19
            //  19 => 20
            //  20 => 21
            //  21 => 22
            //]
            if (! $nonIntegerFound ):
                continue;
            endif;

            // array:23 [
            //  0 => "L1"
            //  1 => "L2"
            //  2 => "L3"
            //  3 => "S4"
            //  4 => "S55"
            //  5 => "S61"
            //  6 => "S57"
            //  7 => "S58"
            //  8 => "L105"
            //  9 => "L7"
            //  10 => "L8"
            //  11 => "L11"
            //  12 => "L56/L93"
            //  13 => "L58"
            //  14 => "L70/L97"
            //  15 => "L72"
            //  16 => "L73"
            //  17 => null
            //  18 => null
            //  19 => null
            //  20 => "L71, P29"
            //  21 => "P30"
            //  22 => null
            //]
            if ( $numStartingWithLetterL > 7 ):
                continue;
            endif;


            $validRows[] = $row;
            // dump( $row );
        endforeach;

        return $validRows;
    }
}