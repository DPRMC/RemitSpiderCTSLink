<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

class CFSRFactory extends AbstractTabFactory {

    protected array $firstColumnValidTextValues = [ 'Trans ID', 'Trans' ];


    protected array $replacementHeaders = [
        'most_recent_financial_information_occ_as_of_date'                                                            => 'most_recent_financial_information_occup_as_of_date',
        'most_recent_financial_information_percent_occ'                                                               => 'most_recent_financial_information_physical_occup_percent',
        'at_contribution_information_base_year_$_noi_ncf'                                                             => 'at_contribution_information_base_year_noi_ncf',
        'sec_prec_fy_operating_info_normalized_$_noi_ncf'                                                             => 'sec_prec_fy_operating_info_normalized_noi_ncf',
        'prec_fy_operating_info_normalized_$_noi_ncf'                                                                 => 'prec_fy_operating_info_normalized_noi_ncf',
        'most_recent_financial_information_normalized_$_noi_ncf'                                                      => 'most_recent_financial_information_normalized_noi_ncf',
        'most_recent_financial_information_fs_start_date'                                                             => 'most_recent_financial_information_financial_as_of_start_date',
        'most_recent_financial_information_fs_end_date'                                                               => 'most_recent_financial_information_financial_as_of_end_date',
        'most_recent_financial_information_normalized_total_revenue'                                                  => 'most_recent_financial_information_normalized_revenue',
        'most_recent_financial_information_normalized_normalized_revenue'                                             => 'most_recent_financial_information_normalized_revenue', //20250501:mdd

        // SUPER KLUDGE
        //10:at_contribution_information_base_year_at_contribution_information_base_year_financials_as_of_date
        //15:sec_prec_fy_operating_info_as_of_second_preceding_fy_operating_information_as_of_financials_as_of_date
        //20:prec_fy_operating_info_as_of_preceding_fy_operating_information_as_of_financials_as_of_date
        //32:net_change_preceding_and_base_year_preceding_and_base_year_percent_occup
        'at_contribution_information_base_year_at_contribution_information_base_year_financials_as_of_date'           => 'at_contribution_information_base_year_financials_as_of_date',
        'sec_prec_fy_operating_info_as_of_second_preceding_fy_operating_information_as_of_financials_as_of_date'      => 'sec_prec_fy_operating_info_as_of_financials_as_of_date',
        'prec_fy_operating_info_as_of_preceding_fy_operating_information_as_of_financials_as_of_date'                 => 'preceding_fy_operating_information_as_of_financials_as_of_date',
        'net_change_preceding_and_base_year_preceding_and_base_year_percent_occup'                                    => 'preceding_and_base_year_percent_occup',

        // 5863085
        // 10:at_contribution_information_base_year_original_underwriting_information_base_year_financial_info_as_of_date
        // 15:sec_prec_fy_operating_info_as_of_nd_preceding_annual_operating_information_as_of_financial_info_as_of_date
        // 25:most_recent_financial_information_most_recent_financial_information_*normalized_or_actual_fs_start_date
        // 32:net_change_preceding_and_base_year_preceding_and_basis_percent_occ
        'at_contribution_information_base_year_original_underwriting_information_base_year_financial_info_as_of_date' => 'at_contribution_information_base_year_financials_as_of_date',
        'sec_prec_fy_operating_info_as_of_nd_preceding_annual_operating_information_as_of_financial_info_as_of_date'  => 'sec_prec_fy_operating_info_as_of_financials_as_of_date',
        'most_recent_financial_information_most_recent_financial_information_*normalized_or_actual_fs_start_date'     => 'most_recent_financial_information_financial_as_of_start_date',
        'net_change_preceding_and_base_year_preceding_and_basis_percent_occ'                                          => 'preceding_and_base_year_percent_occup',

        // 6750574
        // most_recent_financial_information_most_recent_financial_information_normalized_financial_as_of_start_date
        'at_contribution_information_base_year_original_underwriting_information_base_year_financial_info_as_of_date' => 'at_contribution_information_base_year_financials_as_of_date',
        'most_recent_financial_information_most_recent_financial_information_normalized_financial_as_of_start_date'   => 'most_recent_financial_information_financial_as_of_start_date',
    ];


    /**
     * I need a custom method here since the header values are in more than one row.
     *
     * @param array       $allRows
     * @param array       $firstColumnValidTextValues
     * @param string|NULL $debugSheetName
     * @param string|NULL $debugFilename
     *
     * @return void
     */
    protected function _setLocalHeaders( array $allRows, array $firstColumnValidTextValues = [], string $debugSheetName = NULL, string $debugFilename = NULL ): void {
        $headerRow = [];
        foreach ( $allRows as $i => $row ):
            if ( empty( $row[ 0 ] ) ):
                continue;
            endif;

            $trimmedValue = trim( $row[ 0 ] );

            if ( in_array( $trimmedValue, $firstColumnValidTextValues ) ):

                // Check for a two column header
                $secondRowHeader = strtolower( trim( $allRows[ $i + 1 ][ 0 ] ) ); // Might be ID, but a "valid row" value would be WFCM 2016-C32
                if ( 'id' == $secondRowHeader ):
                    $this->headerRowIndex = $i + 1; // Used in other methods of this class.
                    $headerRow            = $row;   // But keep the top row that contains most of the header data.
                    break;
                endif;

                // Else this is just a regular one row header
                $this->headerRowIndex = $i;                                       // Used in other methods of this class.
                $headerRow            = $row;
                break;
            endif;
        endforeach;


        $headerRow = $this->_consolidateMultipleHeaderRowsUsingKeywords( $allRows );


        $cleanHeaders = [];


// These are the headers in the two rows above the actual header row.
// They are here for easy copy paste when I was building the below array.
//        'at_contribution_information';
//        'base_year';
//        'second_preceding_fy_operating_information';
//        'as of';
//        'normalized';
//        'preceding_fy_operating_information';
//        'as of';
//        'normalized';
//        'most_recent_financial_information';
//        'normalized';
//        'net_change';
//        'preceding_and_base_year';

        $prefix = [
            10 => 'at_contribution_information' . '_' . 'base_year' . '_',
            11 => 'at_contribution_information' . '_' . 'base_year' . '_',
            12 => 'at_contribution_information' . '_' . 'base_year' . '_',
            13 => 'at_contribution_information' . '_' . 'base_year' . '_',
            14 => 'at_contribution_information' . '_' . 'base_year' . '_',

            15 => 'sec_prec_fy_operating_info' . '_' . 'as_of' . '_', // secondary_preceding
            16 => 'sec_prec_fy_operating_info' . '_' . 'normalized' . '_',
            17 => 'sec_prec_fy_operating_info' . '_' . 'normalized' . '_',
            18 => 'sec_prec_fy_operating_info' . '_' . 'normalized' . '_',
            19 => 'sec_prec_fy_operating_info' . '_' . 'normalized' . '_',

            20 => 'prec_fy_operating_info' . '_' . 'as_of' . '_', // preceding_fy_operating_information
            21 => 'prec_fy_operating_info' . '_' . 'normalized' . '_',
            22 => 'prec_fy_operating_info' . '_' . 'normalized' . '_',
            23 => 'prec_fy_operating_info' . '_' . 'normalized' . '_',
            24 => 'prec_fy_operating_info' . '_' . 'normalized' . '_',

            25 => 'most_recent_financial_information' . '_',
            26 => 'most_recent_financial_information' . '_',
            27 => 'most_recent_financial_information' . '_',
            28 => 'most_recent_financial_information' . '_',

            29 => 'most_recent_financial_information' . '_' . 'normalized' . '_',
            30 => 'most_recent_financial_information' . '_' . 'normalized' . '_',
            31 => 'most_recent_financial_information' . '_' . 'normalized' . '_',

            32 => 'net_change' . '_' . 'preceding_and_base_year' . '_',
            33 => 'net_change' . '_' . 'preceding_and_base_year' . '_',
            34 => 'net_change' . '_' . 'preceding_and_base_year' . '_',
        ];


        foreach ( $headerRow as $i => $header ):
            // Avoid deprecation warning about passing null to strtolower.
            if ( empty( $header ) ):
                continue;
            endif;

            $cleanHeaders[ $i ] = $this->cleanHeaderValue( $header );

            if ( isset( $prefix[ $i ] ) ):
                $cleanHeaders[ $i ] = $prefix[ $i ] . $cleanHeaders[ $i ];
            endif;
        endforeach;

        $cleanHeaders = $this->_applyReplacementHeaders( $cleanHeaders );

        $this->localHeaders = $cleanHeaders;
    }


    protected function _removeInvalidRows( array $rows = [] ): array {
        return $rows;
    }


    protected function _setParsedRows( array $allRows, string $sheetName = NULL, array $existingRows = [] ): void {
        $this->cleanRows = $existingRows;

        $validRows = $this->_getRowsToBeParsed( $allRows );

        foreach ( $validRows as $i => $validRow ):
            $newCleanRow = [];

            foreach ( $this->localHeaders as $j => $header ):
                $data                   = trim( $validRow[ $j ] ?? '' );
                $newCleanRow[ $header ] = $data;
            endforeach;

            // Some tabs leave the date off.
            // So set a placeholder of NULL for now, and I will "borrow" the date from another tab.
            if ( $this->date ):
                $newCleanRow[ 'date' ] = $this->date->toDateString();
            else:
                $newCleanRow[ 'date' ] = NULL;
            endif;

            $newCleanRow[ 'document_id' ] = $this->documentId;

            // KLUDGE
            // I have empty rows coming in, and I dont want to bother finding out why.
            // Only the date is showing up since I added it above.
            if ( sizeof( $newCleanRow ) == 1 ):
                continue;
            endif;

            $this->cleanRows[] = $newCleanRow;
        endforeach;


        // REMOVE THE TOTAL ROW
        foreach ( $this->cleanRows as $k => $cleanRow ):
            if (
                isset( $cleanRow[ 'trans_id' ] ) &&
                str_contains( strtolower( $cleanRow[ 'trans_id' ] ), 'total' )
            ):
                unset( $this->cleanRows[ $k ] );
            endif;
        endforeach;

        $this->cleanRows = $this->_removeInvalidRows( $this->cleanRows );

        $this->cleanRows = array_values( $this->cleanRows ); // Reindex the array so the indexes are sequential again.
    }


    /**
     * @param array $allRows
     *
     * @return array
     * @throws \DPRMC\RemitSpiderCTSLink\Exceptions\NoDataInTabException
     */
    protected function _getRowsToBeParsed( array $allRows ): array {
        $firstBlankRowIndex = 0;

        $firstRowOfDataIndex = $this->_getFirstRowOfDataIfItExists( $allRows );
        $totalNumRows        = count( $allRows );

        $possibleDataRows = $this->_removeJunkRowsBetweenHeaderAndData( $firstRowOfDataIndex, $allRows );

        foreach ( $possibleDataRows as $i => $possibleDataRow ):
            if ( empty( $possibleDataRow[ 0 ] ) ):
                $firstBlankRowIndex = $i;
                break;
            endif;
        endforeach;

        $numRows = count( $possibleDataRows ) - $firstBlankRowIndex;

        $slicedRows = array_slice( $possibleDataRows, 0, $firstBlankRowIndex );

        return $slicedRows;
    }


    /**
     * @param int   $firstRowOfDataIndex
     * @param array $allRows
     *
     * @return array Possible data rows with junk rows that existed between the header row and the first valid row of data have been removed.
     */
    protected function _removeJunkRowsBetweenHeaderAndData( int   $firstRowOfDataIndex,
                                                            array $allRows ): array {
        $totalNumRows     = count( $allRows );
        $length           = $totalNumRows - $firstRowOfDataIndex;
        $possibleDataRows = array_slice( $allRows, $firstRowOfDataIndex, $length );
        $possibleDataRows = array_values( $possibleDataRows );

        foreach ( $possibleDataRows as $i => $possibleDataRow ):
            if ( $this->_rowContainsDisqualifyingString( $possibleDataRow ) ):
                unset( $possibleDataRows[ $i ] );
                unset( $possibleDataRows[ $i + 1 ] );
                break;
            endif;
        endforeach;

        $possibleDataRows = array_values( $possibleDataRows );

        return $possibleDataRows;
    }


    /**
     * There are patterns. There are certain strings that exist in a row, that guarantee
     * that the row is an invalid row.
     *
     * @param array $row
     *
     * @return bool
     */
    protected function _rowContainsDisqualifyingString( array $row ): bool {
        $disqualifiedStrings = [
            'CPTranID',
        ];
        foreach ( $row as $i => $value ):
            foreach ( $disqualifiedStrings as $disqualifiedString ):
                if ( str_contains( $value, $disqualifiedString ) ):
                    return TRUE;
                endif;
            endforeach;
        endforeach;
        return FALSE;
    }

}