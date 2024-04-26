<?php

namespace DPRMC\RemitSpiderCTSLink\Factories;



trait HeaderTrait {

    /**
     * @param string $header
     * @return string
     */
    public function cleanHeaderValue( string $header ): string {
        $newHeader = $header;
        $newHeader = trim( $newHeader );
        $newHeader = strtolower( $header );
        $newHeader = str_replace( 'yyyymmdd', '', $newHeader );
        $newHeader = str_replace( 'as of', 'as_of', $newHeader );// _as of_

        $newHeader = str_replace( ' ', '_', $newHeader );
        $newHeader = str_replace( "\n", '_', $newHeader );
        $newHeader = str_replace( "(s)", '', $newHeader );
        $newHeader = str_replace( "_(", '_', $newHeader );
        $newHeader = str_replace( ")", '', $newHeader );
        $newHeader = str_replace( '/', '_', $newHeader );
        $newHeader = str_replace( '_-_', '_', $newHeader );
        $newHeader = str_replace( '___', '_', $newHeader );
        $newHeader = str_replace( '__', '_', $newHeader );
        $newHeader = str_replace( '--', '_', $newHeader );
        $newHeader = str_replace( '%', 'percent', $newHeader );

        $newHeader = ltrim( $newHeader, '_' );
        $newHeader = ltrim( $newHeader, '(1_' );
        $newHeader = ltrim( $newHeader, '2_' );

        $newHeader = rtrim( $newHeader, '_1' );
        $newHeader = rtrim( $newHeader, '_$' );

        $newHeader = str_replace( '_$_', '_', $newHeader );
        $newHeader = str_replace( 'p&i', 'p_and_i', $newHeader );

        $newHeader = str_replace( '?_r_n', '', $newHeader ); // is_it_still_recoverable_or_nonrecoverable?_r_n
        $newHeader = str_replace( ',_', '_', $newHeader );   // if_nonrecoverable_advances_reimbursed_from_principal,_realized_loss_amount

        $newHeader = str_replace( 'non-recoverable', 'non_recoverable', $newHeader ); // wodra_deemed_non-recoverable_date
        $newHeader = str_replace( 'wodra_deemed_non-_recoverable_date', 'wodra_deemed_non_recoverable_date', $newHeader ); //

        $newHeader = str_replace( 'workout_strategy*', 'workout_strategy', $newHeader ); // workout_strategy*

        $newHeader = str_replace( 'total_t&i_advance_outstanding',
                                  'total_t_and_i_advance_outstanding',
                                  $newHeader );// workout_strategy*

        $newHeader = str_replace( 'reimburse-ment',
                                  'reimbursement_date',
                                  $newHeader ); // servicer_info_initial_reimburse-ment_date

        // most_recent_financial_information_normalized_$_noi_ncf


        // LPU
        $newHeader = str_replace( "rec'd",
                                  'received',
                                  $newHeader );

        $newHeader = str_replace( "occ'y",
                                  'occupancy',
                                  $newHeader );

        $newHeader = str_replace( "add'l",
                                  'additional',
                                  $newHeader );

        $newHeader = str_replace( "fin'l",
                                  'financial',
                                  $newHeader );

        $newHeader = str_replace( "_&_",
                                  '_and_',
                                  $newHeader );

        $newHeader = str_replace( "_t&i_",
                                  '_t_and_i_',
                                  $newHeader );

        $newHeader = str_replace( "non-cash_",
                                  'non_cash_',
                                  $newHeader );

        // Too long.
        // if_nonrecoverable_advances_reimbursed_from_principal_realized_loss_amount
        $newHeader = str_replace( 'if_nonrecoverable_advances_reimbursed_from_principal_realized_loss_amount',
                                  'if_nonrec_adv_reimb_from_prin_realized_loss_amount',
                                  $newHeader ); //if_nonrecoverable_advances_reimbursed_from_principal_realized_loss_amount


        // 2024-04-26:mdd
        $newHeader = str_replace( "comments-_hlmfclr",
                                  'comments_hlmfclr',
                                  $newHeader );

        //
        return $newHeader;
    }


    /**
     * @param string $number
     * @return float|null
     */
    public function formatNumber( string $number ): ?float {
        $number = trim( $number );
        if ( empty( $number ) ):
            return NULL;
        endif;
        $number = str_replace( ',', '', $number );
        return (float)$number;
    }


    /**
     * @param string $number
     * @return float|null
     */
    public function formatPercent( string $number ): ?float {
        $number = str_replace( '%', '', $number );
        $number = trim( $number );
        if ( empty( $number ) ):
            return NULL;
        endif;

        $asPercent = (float)$number / 100;

        return $asPercent;
    }


}