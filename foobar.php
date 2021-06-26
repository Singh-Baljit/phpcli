<?php
(PHP_SAPI !== 'cli' || isset($_SERVER['REMOTE_ADDR'])) && die('This is command line only script. Thanks.');

/** .
* PHP script Foobar
* 
*
* Script runs only on cli
*/
class Foobar{

    /**
     * Foobar logic
     */
    public function logic_test()
    {
        $output = '';
        for( $numbers = 1; $numbers <= 100; $numbers ++ ):

            if( ($numbers % 3) === 0 && ($numbers % 5) === 0 ):
                $output .= 'foobar' . PHP_EOL;
            elseif( ($numbers % 3) === 0 ):
                $output .= 'foo' . PHP_EOL;
            elseif( ($numbers % 5) === 0 ):
                $output .= 'bar' . PHP_EOL;
            else:
                $output .= $numbers . PHP_EOL;
            endif;    
            
        endfor;

        echo $output;
        
    }

}



// Starting point
try {

    // Class object
    $foobarObj = new Foobar();

    $foobarObj->logic_test();

} catch ( Exception $e ) {

    echo 'Caught exception: ',  $e->getMessage(), '\n';

}
