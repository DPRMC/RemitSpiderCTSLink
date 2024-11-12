<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

use Carbon\Carbon;

class PropertyFactory extends AbstractTabFactory {

    protected array $firstColumnValidTextValues = [];


    protected array $replacementHeaders = [];


    public function parse( array $rows, array &$cleanHeadersByProperty, string $sheetName, array $existingCleanRows = [], string $debugFilename = NULL ): array {
        $this->sheetName = $sheetName;

        $this->_setLocalHeaders( $rows,
                                 $this->firstColumnValidTextValues,
                                 $sheetName,
                                 $debugFilename );

        $this->_setParsedRows( $rows, $sheetName, $existingCleanRows );

        $this->cleanRows = $this->_removeInvalidRows( $this->cleanRows );

        return $this->cleanRows;
    }


    protected function _setParsedRows( array $allRows, string $sheetName = NULL, array $existingRows = [] ): void {
        $this->cleanRows = $existingRows;
        foreach ( $allRows as $rowNumber => $row ):
            $newRow = [];
            foreach ( $row as $columnNumber => $value ):
                $newRow[ $this->localHeaders[ $columnNumber ] ] = trim( $value ?? '' );
            endforeach;

            $newRow[ 'document_id' ] = $this->documentId;

            $this->cleanRows[] = $newRow;
        endforeach;
    }

    protected function _setLocalHeaders( array $allRows, array $firstColumnValidTextValues = [], string $debugSheetName = NULL, string $debugFilename = NULL ): void {


        $this->localHeaders = [ 'transaction_id',
                                'loan_id',
                                'prospectus_loan_id',
                                'property_id',
                                'distribution_date',
                                'cross_collateralized_loan_grouping',
                                'property_name',
                                'property_address',
                                'property_city',
                                'property_state',
                                'property_zip_code',
                                'property_county',
                                'property_type',
                                'year_built',
                                'year_last_renovated',
                                'current_net_rentable_square_feet_fka_net_square_feet_at_contribution',
                                'current_number_of_units_beds_rooms_number_of_units_beds_rooms_at_contribution',
                                'property_status',
                                'allocated_percentage_of_loan_at_contribution',
                                'current_allocated_percentage',
                                'current_allocated_ending_scheduled_loan_amount',
                                'ground_lease_y_n_s',
                                'empty_field_fka_other_escrow_reserve_balances',
                                'most_recent_valuation_date',
                                'most_recent_value',
                                'date_asset_expected_to_be_resolved_or_foreclosed',
                                'foreclosure_start_date',
                                'reo_date',
                                'most_recent_physical_occupancy',
                                'most_recent_occupancy_as_of_date',
                                'date_lease_rollover_review',
                                'pct._sq._feet_expiring_1_12_months',
                                'pct._sq._feet_expiring_13_24_months',
                                'pct._sq._feet_expiring_25_36_months',
                                'pct._sq._feet_expiring_37_48_months',
                                'pct._sq._feet_expiring_49+_months',
                                'largest_tenant',
                                'square_feet_of_largest_tenant',
                                'second_largest_tenant',
                                'square_feet_of_second_2nd_largest_tenant',
                                'third_largest_tenant',
                                'square_feet_of_third_3rd_largest_tenant',
                                'fiscal_year_end_month',
                                'contribution_financials_as_of_date',
                                'revenue_at_contribution',
                                'operating_expenses_at_contribution',
                                'noi_at_contribution',
                                'dscr_noi_at_contribution',
                                'valuation_amount_at_contribution',
                                'valuation_date_at_contribution',
                                'physical_occupancy_at_contribution',
                                'date_of_last_inspection',
                                'preceding_fiscal_year_financial_as_of_date',
                                'preceding_fiscal_year_revenue',
                                'preceding_fiscal_year_operating_expenses',
                                'preceding_fiscal_year_noi',
                                'preceding_fiscal_year_debt_service_amount',
                                'preceding_fiscal_year_dscr_noi',
                                'preceding_fiscal_year_physical_occupancy',
                                'second_preceding_fiscal_year_financial_as_of_date',
                                'second_preceding_fiscal_year_revenue',
                                'second_preceding_fiscal_year_operating_expenses',
                                'second_preceding_fiscal_year_noi',
                                'second_preceding_fiscal_year_debt_service_amount',
                                'second_preceding_fiscal_year_dscr_noi',
                                'second_preceding_fiscal_year_physical_occupancy',
                                'property_collateral_contribution_date',
                                'most_recent_revenue',
                                'most_recent_operating_expenses',
                                'most_recent_noi',
                                'most_recent_debt_service_amount',
                                'most_recent_dscr_noi',
                                'most_recent_financial_as_of_start_date',
                                'most_recent_financial_as_of_end_date',
                                'most_recent_financial_indicator',
                                'ncf_at_contribution',
                                'dscr_ncf_at_contribution',
                                'preceding_fiscal_year_ncf',
                                'preceding_fiscal_year_dscr_ncf',
                                'second_preceding_fiscal_year_ncf',
                                'second_preceding_fiscal_year_dscr_ncf',
                                'most_recent_ncf',
                                'most_recent_dscr_ncf',
                                'noi_ncf_indicator',
                                'deferred_maintenance_flag_y_n',
                                'date_of_lease_expiration_of_largest_tenant',
                                'date_of_lease_expiration_of_second_2nd_largest_tenant',
                                'date_of_lease_expiration_of_third_3rd_largest_tenant',
                                'property_condition',
                                'most_recent_valuation_source',
                                'credit_tenant_lease_y_n',
                                'fourth_largest_tenant',
                                'square_feet_of_fourth_4th_largest_tenant',
                                'fifth_largest_tenant',
                                'square_feet_of_fifth_5th_largest_tenant',
                                'date_of_lease_expiration_of_fourth_4th_largest_tenant',
                                'date_of_lease_expiration_of_fifth_5th_largest_tenant', ];
    }


    /**
     * @param array $rows
     *
     * @return array
     */
    protected function _removeInvalidRows( array $rows = [] ): array {
        $validRows = [];
        foreach ( $rows as $rowNumber => $row ):

            // Doc ID: 6784728 had malformed rows in the _property tab.
            // Skip those invalid rows.
            if ( $this->_hasAllValidOrEmptyDates( $row ) ):
                $validRows[] = $row;
            else:
                continue; // Skip this row
            endif;

        endforeach;

        return $validRows;
    }


    /**
     * @param array $row
     *
     * @return bool
     */
    protected function _hasAllValidOrEmptyDates( array $row ): bool {
        $fieldsToCheck = [ 'distribution_date',
                           'most_recent_occupancy_as_of_date',
                           'most_recent_valuation_date', ];
        foreach ( $fieldsToCheck as $fieldToCheck ):
            $value = trim( $row[ $fieldToCheck ] );
            if ( !empty( $row[ $fieldToCheck ] ) ):
                try {
                    Carbon::parse( $row[ $fieldToCheck ] );
                } catch ( \Exception $exception ) {
                    return FALSE;
                }
            endif;
        endforeach;
        return TRUE;
    }


}