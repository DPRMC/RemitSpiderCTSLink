<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

class AdvanceRecoveryFactory extends AbstractTabFactory {
    // The new line is intentional in the Trans ID string below.
    protected array $firstColumnValidTextValues = [ 'Tran ID',
                                                    'Trans ID',
                                                    'Trans
ID' ];


//comments-_advance_recovery

    protected array $replacementHeaders = [
        'comments-_advance_recovery'         => 'comments_advance_recovery',
        'wodra_deemed_non-_recoverable_date' => 'wodra_deemed_non_recoverable_date',

    ];


    /**
     * I need a custom method here since the header values are in more than one row.
     *
     * @param array       $allRows
     * @param array       $firstColumnValidTextValues
     * @param string|null $debugSheetName
     * @param string|null $debugFilename *
     *
     * @return void
     */
    protected function _setLocalHeaders( array $allRows, array $firstColumnValidTextValues = [], string $debugSheetName = NULL, string $debugFilename = NULL): void {
        $headerRow = [];
        foreach ( $allRows as $i => $row ):
            if ( empty( $row[ 0 ] ) ):
                continue;
            endif;

            $trimmedValue = trim( $row[ 0 ] );

            if ( in_array( $trimmedValue, $firstColumnValidTextValues ) ):
                $this->headerRowIndex = $i; // Used in other methods of this class.
                $headerRow            = $row;
                break;
            endif;
        endforeach;

        $cleanHeaders = [];


// These are the headers in the two rows above the actual header row.
// They are here for easy copy paste when I was building the below array.
        // servicer_information
        //  unreimbursed_advances
        //      initial_amount
        //      advance_interest
        //  reimbursed_advances
        //      principal_collections
        //          current
        //          cumulative
        //      interest_collections
        //          current
        //          cumulative

        $prefix = [
            6 => 'servicer_info' . '_' . 'unreimbursed_adv' . '_', // servicer_information unreimbursed_advances
            7 => 'servicer_info' . '_' . 'unreimbursed_adv' . '_',
            8 => 'servicer_info' . '_',

            9  => 'servicer_info' . '_' . 'reimbursed_adv' . '_' . 'principal_collections' . '_', // reimbursed_advances
            10 => 'servicer_info' . '_' . 'reimbursed_adv' . '_' . 'principal_collections' . '_',
            11 => 'servicer_info' . '_' . 'reimbursed_adv' . '_' . 'interest_collections' . '_',
            12 => 'servicer_info' . '_' . 'reimbursed_adv' . '_' . 'interest_collections' . '_',
            13 => 'servicer_info' . '_',

            15 => 'borrower_info' . '_', // borrower_information. Shortened down for consistency.
            16 => 'borrower_info' . '_',
            17 => 'borrower_info' . '_',
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

        $this->localHeaders = $cleanHeaders;
    }

    protected function _removeInvalidRows( array $rows = [] ): array {
        return $rows;
    }
}