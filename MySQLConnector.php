<?php
/**
 * MySQL Connector
 * Database access class for MySQL
 *
 * @version Release 1.1
 */

class MySQLConnector {
    
    /**
     * @var $DbServer host name
     * @var $DbName database name
     * @var $DbUser user name
     * @var $DbPass database password
     * @var $ConnectID connection ID
     * @var $isTransaction transaction flag
     * @var $ErrMsg error messages
     * @var $Result result of query
     * @var $DbChar character code
     * @var $isDebugMode debug mode flag (displays SQL when error occur)
     * @var $isConnecting connect flag
     * @var $SQL SQL
     * @var $Value value
     * @var $EscapedSQL escaped sql
     * @var $ExeSQL executed sql
     * @var $singleton obcect
   */
    private $DbServer;
    private $DbName;
    private $DbUser;
    private $DbPass;
    private $ConnectID;
    private $isTransaction;
    private $ErrMsg = array();
    private $Result;
    private $DbChar;
    private $isDebugMode;
    private $isConnecting;
    private $SQL;
    private $Value;
    private $EscapedSQL;
    private $ExeSQL;
    private static $singleton;

    /**
     * Do not use 
     * 
     * @param array $config 
     * @access private
     * @return void
     */
    private function __construct(array $config) {
        extract($config);
        $this->setDBConfig($server, $db_name, $db_user, $password, $character_code, $is_debug_mode);
    }

    /**
     * Use this method instead of constructor 
     * 
     * @param array $config 
     * @static
     * @access public
     * @return obcect
     */
    public static function getInstance(array $config){
        if (!is_object(MySQLConnector::$singleton)) {
            MySQLConnector::$singleton = new MySQLConnector($config);
        }
        return MySQLConnector::$singleton;
    }

    /**
     * DB setting and reset member variable.
     * @param $server server name
     * @param $db_name database name
     * @param $db_user user name
     * @param $password database password
     * @param $character_code character code
     * @param $is_debug_mode debug mode flag
     */
    public function setDBConfig($server, $db_name, $db_user, $password, $character_code, $is_debug_mode = False) {
        $this->DbServer = $server;
        $this->DbName = $db_name;
        $this->DbUser = $db_user;
        $this->DbPass = $password;
        $this->DbChar = $character_code;
        $this->isDebugMode = $is_debug_mode;
        $this->ConnectID = False;
        $this->isTransaction = False;
        $this->isConnecting = False;
    }

    /**
     * destructer
     * close database connection and destruct instance.
     */
    public function __destruct()  {
        if ($this->ConnectID !== False) {
            if( $this->isTransaction ){
                $this->RollBackTrans();
            }
            @mysql_close($this->ConnectID);
            $this->ConnectID = False;
        }
        unset( $this );
    }

    /**
     * database connect and get connection ID
     * @return true(connect success) or false(connect fail)
     */
    public function doConnect() {
        $this->ConnectID = @mysql_connect($this->DbServer, $this->DbUser, $this->DbPass);
        $SelectError = $this->selectDb($this->DbName);
        $setChar = mysql_set_charset($this->DbChar);
        if($this->ConnectID == False || $SelectError == False || $setChar == False){
            return False;
        }else{ 
            $this->isConnecting = True;
            return True;
        }
    }

    /**
     * select database
     * @return true(success) or false(fail)
     */
    public function selectDb($DbName){
        $result = @mysql_select_db( $DbName );
        return ($result === False)? False : True;
    }

    /**
     * start trnsaction
     */
    public function beginTransaction(){
        $this->execute( "BEGIN" );
        $this->isTransaction = True;
    }

    /**
     * execute commit
     */
    public function commitTransaction(){
        if( $this->isTransaction ){
            $this->execute( "COMMIT" );
            $this->isTransaction = False;
        }
    }

    /**
     * execute rollback
     */
    public function rollBackTransaction(){
        if( $this->isTransaction ){
            $this->execute( "ROLLBACK" );
            $this->isTransaction = False;
        }
    }

    /**
     * set SQL
     * If you want to set value to SQL, you need write ? to there.
     */
    public function setSQL($SQL){
        if(is_array($SQL)){
            $this->SQL = (is_array($this->SQL))? array_merge($this->SQL, $SQL) : $SQL;
        }else{
            $this->SQL[] = $SQL;
        }
        return $this;
    }

    /**
     * you can get SQL
     */    
    public function getSQL(){
        return $this->SQL;
    }

    /**
     * set value to SQL
     * @param $val value(s)
     */
    public function setValue($val){
        if(is_array(current($val))){
            $this->Value = (is_array($this->Value))? array_merge($this->Value, $val) : $val;
        }else{
            $this->Value[] = $val;
        }
        return $this;
    }

    public function getValue(){
        return $this->Value;
    }

    /**
     * reset SQL and value
     */
    public function clear(){
        $this->SQL = null;
        $this->Value = null;
    }

    /**
     * create SQL from SQL(incude place holder) and value
     * @param $SQL SQL
     * @param $value values
     * $SQL created SQL
     */
    private function makeSQL($SQL, $value){
        if($SQL && $value){
            $val = $this->escapeValue($value);
            if(is_array($val)){
                if(in_array(false, $this->checkEncodingValues($val), true)) die('mb encoding error!');
                $arraySQL = explode('?', $SQL);
                $SQL_and_val = $this->makeAlternateArray($arraySQL, $val);
                $SQL = implode('', $SQL_and_val);
            }else{
                if($this->checkEncodingValues($val) === false) die('mb encoding error!');
                $SQL = str_replace('?', $val, $SQL);
            }
            return $SQL;
        }else{
            return false;
        }
    }

    /**
     * this method checks mb encoding(control checkEncoding method)
     * @return bool true or false
     */
    private function checkEncodingValues($value){
        $is_valid = is_array($value)?
            array_map(array($this, 'checkEncoding'), $value) :
            $this->checkEncoding($value);
        return $is_valid;
    }

    /**
     * this method checks mb encoding
     * @return bool true or false
     */
    private function checkEncoding($value){
        return mb_check_encoding($value, $this->DbChar);
    }


    /**
     * this method overwrites value to array1 from array2.
     * @param $array1 target array
     * @param $array2 array
     * @return overwrited array
     */
    private function makeAlternateArray($array1, $array2){
        $result = array();
        foreach($array1 as $key1 => $val1){
            $result[] = $val1;
            if(array_key_exists($key1, $array2)) $result[] = $array2["$key1"];
        }
        return $result;
    }

    /**
     * this method creates SQL
     * @return created SQL
     */
    private function createSQL(){
        if(is_array($this->SQL)){
            if(count($this->SQL) != count($this->Value)) die('Error:Not equal SQL '.count($this->SQL).'- value'.count($this->Value));
            $created_SQL = array_map(array($this, 'makeSQL'), $this->SQL, $this->Value);
        }else{
            $created_SQL = $this->makeSQL($this->SQL, $this->Value);
        }
        return $created_SQL;
    }

    /**
     * this method execute escape (use mysql_real_escape_string)
     * @return escaped value
     */
    private function escapeValue( $value ){
        if(!$this->isConnecting) $this->DoConnect();
        $value = is_array($value) ?
            array_map( array($this,'escapeValue'), $value ) :
            mysql_real_escape_string($value);
        return $value;
    }

    /**
     * this method controls execute process.
     * @param @SQL SQL
     * @return true(false) / false(fail)
     */
    public function execute( $SQL=NULL ){
        if(!$SQL) $SQL = $this->createSQL();
        if(!$this->isConnecting) $this->DoConnect();

        $this->Result = $this->executeWithTransaction($SQL);
        $this->clear();
        return ($this->Result === False)? False : True;
    }

    /**
     * this method execute transaction in execute SQL
     * @return execute result
     */
    private function executeWithTransaction($SQL){
        if(is_array($SQL)){
            if(count($SQL) > 1){
                $this->beginTransaction();
                foreach($SQL as $execute_sql){
                    if(!$this->doExecute($execute_sql)){
                        $this->rollBackTransaction();
                        return false;
                    }
                }
                $this->commitTransaction();
                return true;
            }else{
                return $this->doExecute(current($SQL));
            }
        }
        return $this->doExecute($SQL);
    }

    /**
     * execute SQL
     * @param $strSQL SQL
     * @return result
     */
    private function doExecute( $strSQL ){
        $this->ExeSQL = $strSQL;
        $result = ($this->isDebugMode)? mysql_query( $strSQL ) : @mysql_query( $strSQL );
        return $result;
    }

    /**
     * this method returns error messerge.
     * @return error message(s)
     */
    public function getError(){
        return mysql_error();
    }

    /**
     * this method returns executed SQL.
     * @return executed SQL
     */
    public function getExecuteSQL(){
        return $this->ExeSQL;
    }

    /**
     * this method returns number of data(s).
     * @return data count
     */
    public function getDataCount(){
        return mysql_numrows($this->Result);
    }

    /**
     * this method returns result of executed query.
     * @return data of executed query
     */
    public function getData() {
        if($this->Result){
            while($data[] = mysql_fetch_assoc($this->Result));
            array_pop($data);
            @mysql_free_result($this->Result);
            return $data;
        }
        return false;
    }

    /**
     * This method checks whether target table has already existed.
     * @param $table_name target table name
     * @return true(exists) or false(not exists)
     */
    public function existsTable($table_name){
        $this->setSQL('SHOW TABLES');
        $this->execute();
        return (!array_search($table_name, $this->getData()) === false)? true : false;
    }
}
