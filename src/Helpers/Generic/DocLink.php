<?php

namespace DPRMC\RemitSpiderCTSLink\Helpers\Generic;

use Carbon\Carbon;
use DPRMC\RemitSpiderCTSLink\Helpers\AbstractHelper;
use DPRMC\RemitSpiderCTSLink\Helpers\Debug;
use DPRMC\RemitSpiderCTSLink\Models\CTSLinkShelf;
use DPRMC\RemitSpiderCTSLink\RemitSpiderCTSLink;
use HeadlessChromium\Page;


class DocLink {

    const REGEX_DATE_PATTERN = '/\d{1,2}\/\d{1,2}\/\d{2,4}/';
    public string $revisedCurrentCycle;

    public function __construct( public string $nameOfFile = '',
                                 public string $currentCycle = '',
                                 public string $nextCycle = '',
                                 public string $nextAvailableDateTime = '',
                                 public string $additionalHistoryHref = '',
                                 public string $href = '',
                                 public bool   $hasAccess = FALSE ) {

        // Deal with a potentially revised date within the current cycle field.
        $this->revisedCurrentCycle = '';
        $lowercaseCurrentCycle     = strtolower( $this->currentCycle );
        // If the word "revised" appears in the current cycle string, then there are most likely two dates present in the field.
        // I can use regex to pull them out.
        if ( str_contains( $lowercaseCurrentCycle, 'revised' ) ):
            $found = preg_match( self::REGEX_DATE_PATTERN, $lowercaseCurrentCycle, $datesFound );
            if ( 1 !== $found ):
                $this->currentCycle        = '';
                $this->revisedCurrentCycle = '';
            endif;

            if ( count( $datesFound ) > 2 ):
                throw new \Exception( "There were more than two dates found in the REVISED current cycle field. I have never seen more than 2 dates there." );
            endif;

            $collection = collect( [] );
            foreach ( $datesFound as $stringDate ):
                $collection->push( Carbon::parse( $stringDate ) );
            endforeach;

            $sorted = $collection->sortBy( function ( $carbonDate, $key ) {
                return $carbonDate->timestamp();
            } );

            $this->currentCycle        = $sorted->shift()->format( 'n/j/Y' );
            $this->revisedCurrentCycle = $sorted->shift()->format( 'n/j/Y' );
        endif;
    }
}