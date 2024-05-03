<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

class CFSRFactory extends AbstractTabFactory {

    protected array $firstColumnValidTextValues = [ 'Trans ID', 'Trans' ];


    protected array $replacementHeaders = [
        'most_recent_financial_information_occ_as_of_date'           => 'most_recent_financial_information_occup_as_of_date',
        'most_recent_financial_information_percent_occ'              => 'most_recent_financial_information_physical_occup_percent',
        'at_contribution_information_base_year_$_noi_ncf'            => 'at_contribution_information_base_year_noi_ncf',
        'sec_prec_fy_operating_info_normalized_$_noi_ncf'            => 'sec_prec_fy_operating_info_normalized_noi_ncf',
        'prec_fy_operating_info_normalized_$_noi_ncf'                => 'prec_fy_operating_info_normalized_noi_ncf',
        'most_recent_financial_information_normalized_$_noi_ncf'     => 'most_recent_financial_information_normalized_noi_ncf',
        'most_recent_financial_information_fs_start_date'            => 'most_recent_financial_information_financial_as_of_start_date',
        'most_recent_financial_information_fs_end_date'              => 'most_recent_financial_information_financial_as_of_end_date',
        'most_recent_financial_information_normalized_total_revenue' => 'most_recent_financial_information_normalized_revenue',
    ];


    /**
     * I need a custom method here since the header values are in more than one row.
     * @param array $allRows
     * @param array $firstColumnValidTextValues
     * @return void
     */
    protected function _setLocalHeaders( array $allRows, array $firstColumnValidTextValues = [], string $debugSheetName=null ): void {
        $headerRow = [];
        foreach ( $allRows as $i => $row ):
            if ( empty( $row[ 0 ] ) ):
                continue;
            endif;

            $trimmedValue = trim( $row[ 0 ] );

            if ( in_array( $trimmedValue, $firstColumnValidTextValues ) ):

                // Check for a two column header
                $secondRowHeader = strtolower(trim($allRows[$i+1][0])); // Might be ID, but a "valid row" value would be WFCM 2016-C32
                if('id' == $secondRowHeader):
                    $this->headerRowIndex = $i+1; // Used in other methods of this class.
                    $headerRow            = $row; // But keep the top row that contains most of the header data.
                    break;
                endif;

                // Else this is just a regular one row header
                $this->headerRowIndex = $i; // Used in other methods of this class.
                $headerRow            = $row;
                break;
            endif;
        endforeach;

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
}