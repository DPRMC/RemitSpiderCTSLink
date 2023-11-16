<?php

namespace DPRMC\RemitSpiderCTSLink\Helpers\Generic;

use Carbon\Carbon;
use DPRMC\RemitSpiderCTSLink\Helpers\AbstractHelper;
use DPRMC\RemitSpiderCTSLink\Helpers\Debug;
use DPRMC\RemitSpiderCTSLink\Models\CTSLinkShelf;
use DPRMC\RemitSpiderCTSLink\RemitSpiderCTSLink;
use HeadlessChromium\Page;
use Illuminate\Support\Collection;


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
            $found = preg_match_all( self::REGEX_DATE_PATTERN, $lowercaseCurrentCycle, $matches );
            if ( 1 !== $found ):
                $this->currentCycle        = '';
                $this->revisedCurrentCycle = '';
            endif;

            if ( count( $matches[0] ) > 2 ):
                throw new \Exception( "There were more than two dates found in the REVISED current cycle field. I have never seen more than 2 dates there." );
            endif;

            $collection = collect( [] );
            $datesFound = $matches[0];
            foreach ( $datesFound as $stringDate ):
                $collection->push( Carbon::parse( $stringDate ) );
            endforeach;

            $sortedDates = $collection->sortBy( function ( $carbonDate, $key ) {
                return $carbonDate->timestamp;
            } );

            /**
             * @var Collection $sortedDates;
             */
            $this->currentCycle        = $sortedDates->shift()->format( 'n/j/Y' );

            if($sortedDates->isNotEmpty()):
                $this->revisedCurrentCycle = $sortedDates->shift()->format( 'n/j/Y' );
            endif;
        endif;
    }
}