<?php

namespace DPRMC\RemitSpiderCTSLink\Exceptions\DistributionFileExceptions;


/**
 * This exception will get thrown when I can not find the section title I am
 * looking for in the Table of Contents.
 * If you see this exception, then you will need to change the code in
 * CMBSDistributionFileFactory.php
 *  getPageRangeBySection()
 */
class UnableToFindIndexOfLabelException extends \Exception {


    /**
     * @var string The 'name' we are looking for in the table of contents.
     */
    public string $sectionName = '';

    /**
     * @var array All the rows in the table of contents on the first page.
     */
    public array $firstPage = [];

    public function __construct( string      $message = "",
                                 int         $code = 0,
                                 ?\Throwable $previous = NULL,
                                 string      $sectionName = '',
                                 array       $firstPage = []
    ) {
        parent::__construct( $message, $code, $previous );

        $this->sectionName = $sectionName;
        $this->firstPage   = $firstPage;
    }
}