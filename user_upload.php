<?php
(PHP_SAPI !== 'cli' || isset($_SERVER['REMOTE_ADDR'])) && die('This is command line only script. Thanks.');

/** .
* PHP script
* Executed from the command line only
* Accepts a CSV file as input and processes the CSV file
* Parsed file data is inserted into a MySQL database
* 
*
* Script runs only on cli
*/
class PHPCli{


    // Class properties
    private $dbCli;
    private $shortOptions;
    private $longOptions;
    private $cliOptions;
    private $invalidEmails;
    private $errorRecords;
    private $DATABASE;


    /**
     * Cli parameters
     */
    public function __construct()
    {
        $this->shortOptions  = '';
        $this->shortOptions .= 'u:';    // MySQL Username
        $this->shortOptions .= 'p::';    // MySQL Password
        $this->shortOptions .= 'h:';    // MySQL Host
        
        $this->longOptions  = [
            'file:',           // CSV File
            'create_table',   // Create MySQL Table 'users'
            'dry_run',        // Dry Run Script
            'help',           // Help
        ];
        
    }

    /**
     * DB Connection
     */
    public function db_connection( $SERVER, $USER, $PASSWORD ): bool
    {
        $DBConfig = include( 'config/db.php' );
        $this->DATABASE = $DBConfig['DATABASE'];
        
        $this->dbCli = new mysqli( $SERVER, $USER, $PASSWORD );

        // Checking host Connection
        if ( $this->dbCli->connect_error ):
            
            throw new Exception( "Host connection failed: %s\n", $this->dbCli->connect_error );

        endif;

        // Create new database
        $dbSQL = "CREATE DATABASE IF NOT EXISTS $this->DATABASE";

        if( $this->dbCli->query($dbSQL) === FALSE ):
            
            throw new Exception( "Error: %s\n", $this->dbCli->error );

        endif;

        return TRUE;

    }


     /**
     * Close DB Connection
     */
    public function close_db_connection()
    {
        $this->dbCli->close();
    }


    /**
     * Extract cli options
     */
    public function cli_options(): array
    {

        $this->cliOptions = getopt( $this->shortOptions, $this->longOptions );
        return $this->cliOptions;

    }

    /**
     * Create new database table if not exists
     */
    public function create_table()
    {

        $dbTable = "CREATE TABLE IF NOT EXISTS $this->DATABASE.users(id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, email VARCHAR(50) NOT NULL UNIQUE)";
        if ( ! $this->dbCli->query($dbTable) ):

            throw new Exception( "Table not created: ".$this->dbCli->error );

        endif;        

    }

    /**
     * Process CSV file and insert data into database table
     */
    public function process_csv(string $csvFile): array
    {

        if( ($csvHandle = fopen( 'assets/files/' . $csvFile, "r")) !== FALSE ):

            while( ($csvData = fgetcsv($csvHandle, 500, ",")) !== FALSE ):

                if( ! empty($csvData[2]) && filter_var($csvData[2], FILTER_VALIDATE_EMAIL) ):

                    $firstName  = mb_convert_case(trim($this->dbCli->real_escape_string($csvData[0])), MB_CASE_TITLE);
                    $lastName   = mb_convert_case(trim($this->dbCli->real_escape_string($csvData[1])), MB_CASE_TITLE);
                    $email      = strtolower(trim($this->dbCli->real_escape_string($csvData[2])));
                    
                    if( ! isset($this->cliOptions['dry_run']) ):

                        $dbInsert = "INSERT INTO $this->DATABASE.users (first_name, last_name, email) VALUES ('$firstName', '$lastName', '$email');";
                        if ( $this->dbCli->query($dbInsert) === FALSE ):

                            $this->errorRecords .= "Error: $email not inserted: ".$this->dbCli->error . PHP_EOL;

                        endif;
                    
                    endif;    

                else:

                    $this->invalidEmails .= $csvData[2] . PHP_EOL;

                endif;

            endwhile;

            return [
                $this->errorRecords, 
                $this->invalidEmails
            ];

            fclose( $csvHandle );

        endif;
        
    }

    /**
     * Cli help instructions
     */
    public function cli_help()
    {

        echo <<<'HELP'
        The PHP script should include these command line options (directives):
        --file          [csv file name] – this is the name of the CSV to be parsed
        --create_table  this will cause the MySQL users table to be built (and no further action will be taken)
        --dry_run       this will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered
        -u              MySQL username
        -p              MySQL password
        -h              MySQL host
        --help          which will output the above list of directives with details
        HELP;

    }

}



// Starting point
try {

    // Class object
    $cliObj = new PHPCli();

    //Extract cli options
    $cliOptions = $cliObj->cli_options();

    // Cli help
    if( isset($cliOptions['help']) && $cliOptions['help'] === FALSE ):

        $cliObj->cli_help();

    endif;

    // Create database connection
    if(
        ( isset($cliOptions['u']) && $cliOptions['u'] !== FALSE ) &&
        ( isset($cliOptions['p']) ) && 
        ( isset($cliOptions['h']) && $cliOptions['h'] !== FALSE )
    ):
    
        $dbConnectionStatus = $cliObj->db_connection( $cliOptions['h'], $cliOptions['u'], $cliOptions['p'] );

    endif;

    if( isset($dbConnectionStatus) && $dbConnectionStatus === TRUE):

        // Create table
        if( isset($cliOptions['create_table']) && $cliOptions['create_table'] === FALSE ):
            $cliObj->create_table();
        endif;

        // Parse csv file
        if( isset($cliOptions['file']) && $cliOptions['file'] !== '' ):
            $csvErrors = $cliObj->process_csv($cliOptions['file']);

            // Display intersion error
            if( isset($csvErrors[0]) and $csvErrors !== '' ):

                echo 'Records errors:' . PHP_EOL;
                echo $csvErrors[0];

            endif;

            // Display list of invalid emails
            if( isset($csvErrors[1]) and $csvErrors !== '' ):

                echo 'Invalid emails:' . PHP_EOL;
                echo $csvErrors[1];

            endif;    

        endif;

        // Close DB connection
        $cliObj->close_db_connection();

    endif;

} catch ( Exception $e ) {

    echo 'Caught exception: ',  $e->getMessage(), '\n';

}
