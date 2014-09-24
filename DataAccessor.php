<?php
/**
 * DataAccessor
 * Give you easy access to database.
 * This class requires MySQL Connector.
 *
 * If you make model class, extends this class.
 *
 * @version Release 1.1
 *
 * * How to use
 *
 * 1.create instance
 * $mysql = new MySQLConnector(
 *     array(
 *         'server'=>'xxxxx',
 *      'db_name'=>'xxxxxx',
 *         'db_user'=>'xxxxxx',
 *         'password'=>'xxxxxx',
 *         'character_code'=>'utf8'
 *     )
 * );
 * $table = new DataAccessor($mysql);
 * $table->setTableName('table_name');
 *
 * 2. get data
 * # get all data in table
 * $datalist = $table->get();
 *
 * # get only one data - 'one' 
 * $data = $table->get('one');
 *
 * # get count of data - 'count'
 * $data = $table->get('count');
 *
 * # select
 * $conditions = array(
 *   'columns'=>array('id', 'date'),
 *   'where'=>array('date'),
 *   'values'=>array($date),
 *   'limit'=>5,
 *   'order_by'=>array('id', 'DESC')
 * );
 * $table->select($conditions);
 * $datalist = $table->get();
 *
 * * other select options
 * 'join' - 'join'=>' table1 LEFT JOIN table2 ON table1.key = table2.key '
 * 'group_by' - 'group_by'=>'column1'
 * 'having' - 'having'=>'column2 > 0'
 * 'offset' - 'offset'=>10
 *
 * # no quote
 * If you want to execute query without quote,
 * write "column_name:nq" in "columns".
 * If value is LAST_INSERT_ID(), NOW() etc,
 * it can work.
 *
 * # SQL
 * $conditions = array(
 *   'query'=>'select * from table where date = ?',
 *   'values'=>array('2010-12-24')
 * );
 * $table->select($conditions);
 * $datalist = $table->get();
 *
 * # insert
 * $data = array(
 *   'columns'=>array('type', 'name', 'calorie', 'number'),
 *   'values'=>array('etc', 'test', 100, 1) 
 * );
 * $table->insert($data);
 * $table->save();
 *
 * # update
 * $data = array(
 *   'columns'=>array('type', 'name', 'calorie', 'number'),
 *   'where'=>array('eat_id'),
 *   'values'=>array('etc', 'test', 50, 1, 258)
 * );
 * $table->update($date);
 * $table->save();
 *
 * # delete
 * $conditions = array(
 *   'where'=>array('eat_id'),
 *   'values'=>array(258)
 * );
 * $table->delete($conditions);
 * $table->save();
 *
 * # method chain 
 *  For example you can execute 2 queries smartly with transaction!
 *
 *  $insert = array(
 *      'columns'=>array('hoge', 'name', 'calorie', 'number'),
 *      'values'=>array(123, 'test', 100, 1)
 *      );
 *  $insert2 = array(
 *      'columns'=>array('hoge', 'name:nq', 'calorie', 'number'),
 *      'values'=>array(124, 'LAST_INSERT_ID()', 100, 1)
 *      );
 *  $table->insert($insert)->insert($insert2)->save();
 *
 *  Of course you can execute more than 2 queries!
 */

class DataAccessor {
    protected $table_name;
    protected $dbh;
    private $where;
    private $columns;
    private $join;
    private $order_by;
    private $limit;
    private $offset;
    private $having;
    private $group_by;
    private $query;
    private $placeholders;
    private $array_columns;
    private $SQL_type;
    private $result;
    private $values;

    /**
     * __construct 
     * 
     * @param DataConnector $dbh 
     * @access public
     * @return void
     */
    public function __construct( DataConnector $dbh ){
        $this->dbh = $dbh;
    }

    /**
     * setTableName 
     * 
     * @param mixed $table_name 
     * @access public
     * @return void
     */
    public function setTableName($table_name){
        $this->table_name = $table_name;
        return $this;
    }

    /**
     * get - getter for select
     * @access public
     * @param result type 
     *  'one' - get first row of result cache.
     *  'count' - get count of result cache.
     * @return result
     */
    public function get($type=null){
        $this->doSelect($type);
        return ($type == 'count')?
             $this->result[0]['count'] : $this->result;
    }

    /**
     * set - setter 
     * @access public
     * @param target method name and args
     * @return $this (for method chain) 
     */
    public function set(){
        $args = func_get_args();
        $method = array_shift($args);
        $this->result = call_user_func_array(array($this, $method), $args);
        return $this;
    }

    /**
     * save - save insert and update
     * @access public
     */
    public function save(){
        return $this->execute();
    }

    /**
     * select
     * @access public
     * @param array conditions
     */
    public function select($conditions=null){
        $this->SQL_type = 'select';
        if($conditions) $this->setConditions($conditions);
        return $this;
    }

    private function doSelect($type=null){
        $sql = $this->makeSelect($type);
        if($this->values){
            $this->setSQL($sql);
            $this->setExecuteValues();
            $this->execute();
        }else{
            $this->execute($sql);
        }
        $this->getData();
    }

    /**
     * makeSelect - Make SQL for select
     * @access private 
     * @param str $type ('one' or 'count')
     * @return $sql SQL query
     */
    private function makeSelect($type=null){
        if($this->query) return $this->query;

        $sql =  'SELECT ';
        $sql .= ($type == 'count')? 'count(*) AS count' : (($this->columns)? $this->columns : '*');
        $sql .= ' FROM ';
        $sql .= ($this->join)? $this->join : $this->table_name;
        if($this->where) $sql .= $this->where;
        if($this->group_by) $sql .= $this->group_by;
        if($this->having) $sql .= $this->having;
        if($this->order_by) $sql .= $this->order_by;
        if($type == 'one' || $this->limit) $sql .= ($type == 'one')? ' LIMIT 1' : $this->limit;
        if($this->offset) $sql .= $this->offset;
        return $sql;
    }

    /**
     * insert
     * @access public
     * @param array $conditions
     * @return $this
     */
    public function insert(array $conditions=null){
        $this->SQL_type = 'insert';
        if($conditions) $this->setConditions($conditions);
        $this->setSQL($this->makeInsert());
        $this->setExecuteValues();
        return $this;
    }

    /**
     * makeInsert 
     * 
     * @access private
     * @return void
     */
    private function makeInsert(){
        $sql = 'INSERT INTO ';
        $sql .= $this->table_name;
        $sql .= ' ('.$this->columns.') ';
        $sql .= ' VALUES ';
        $this->placeholders = $this->makePlaceholdersSeparateComma($this->array_columns);
        $sql .= ' ('.$this->placeholders.') ';
        if($this->where) $sql .= $this->where;
        return $sql;
    }

    /**
     * update 
     * @access public
     * @param array $conditions
     * @return $this
     */
    public function update(array $conditions=null){
        $this->SQL_type = 'update';
        if($conditions) $this->setConditions($conditions);
        $this->setSQL($this->makeUpdate());
        $this->setExecuteValues();
        return $this;
    }

    /**
     * makeUpdate 
     * 
     * @access private
     * @return void
     */
    private function makeUpdate(){
        $sql = 'UPDATE ';
        $sql .= $this->table_name;
        $sql .= ' SET ';
        $sql .= $this->columns;
        if($this->where) $sql .= $this->where;
        return $sql;
    }

    /**
     * delete
     * @access public
     * @param array @conditions
     * @return $this
     */
    public function delete($conditions=null){
        $this->SQL_type = 'delete';
        if($conditions) $this->setConditions($conditions);
        $this->setSQL($this->makeDelete());
        $this->setExecuteValues();
        return $this;
    }

    /**
     * makeDelete 
     * 
     * @access private
     * @return void
     */
    private function makeDelete(){
        $sql = 'DELETE FROM ';
        $sql .= $this->table_name;
        if($this->where) $sql .= $this->where;
        return $sql;
    }

    // Where

    /**
     * makeWhere 
     * 
     * @param mixed $conditions 
     * @access private
     * @return void
     */
    private function makeWhere($conditions){
        $this->where .= $this->makePlaceholdersSeparateKey($conditions, 'AND');
    }

    // Common

    /**
     * execute 
     * 
     * @param mixed $sql 
     * @access private
     * @return void
     */
    private function execute($sql=null){
        $this->dbh->execute($sql);
    }

    /**
     * getData 
     * 
     * @access private
     * @return void
     */
    private function getData(){
        $this->result = $this->dbh->getData();
    }

    /**
     * makeColumnsSeparateComma 
     * 
     * @param mixed $columns 
     * @access private
     * @return void
     */
    private function makeColumnsSeparateComma($columns){
        return (is_array($columns))? implode(', ', $columns) : $columns;
    }

    /**
     * makePlaceholdersSeparateComma 
     * 
     * @param mixed $columns 
     * @access private
     * @return void
     */
    private function makePlaceholdersSeparateComma($columns){
        $is_nq = implode('', $this->checkColumnMode($columns, 'nq'));
        $placeholders = str_replace('1', ' ?,', $is_nq);
        $placeholders = str_replace('0', " '?',", $placeholders);
        return substr($placeholders, 0, -1);
    }

    /**
     * removeColumnOption 
     * 
     * @param mixed $values 
     * @access private
     * @return void
     */
    private function removeColumnOption($values){
        if(!is_array($values)){
            $array_values = explode(',', $values);
            $values = array_map('trim', $array_values);
        }
        foreach($values as $key => $val){
            $keys = explode(':', $val);
            $result[] = $keys[0];
        }
        return $result;
    }

    /**
     * checkColumnMode 
     * 
     * @param mixed $columns 
     * @param mixed $mode 
     * @access private
     * @return void
     */
    private function checkColumnMode($columns, $mode){
        foreach($columns as $key => $val){
            $keys = explode(':', $val);
            if(array_key_exists(1, $keys) && $keys[1] == $mode){ 
                $is_match[] = '1';
            }else{
                $is_match[] = '0';
            }
        }
        return $is_match;
    }

    /**
     * makePlaceholderWithEqual 
     * 
     * @param mixed $column 
     * @param mixed $mode 
     * @access private
     * @return void
     */
    private function makePlaceholderWithEqual($column, $mode){
        $placeholder = $column;
        $placeholder .= ($mode=='1')? ' = ? ' : " = '?' ";
        return $placeholder;
    }

    /**
     * makePlaceholdersSeparateKey 
     * 
     * @param mixed $keys 
     * @param mixed $add_str 
     * @access private
     * @return void
     */
    private function makePlaceholdersSeparateKey($keys, $add_str){
        $mode = $this->checkColumnMode($keys, 'nq');
        $placeholders = array_map(array($this, 'makePlaceholderWithEqual'), $keys, $mode);
        $separater = $add_str. ' ';
        return implode($separater, $placeholders);
    }

    // Setter
    // values, columns, join, where, order_by, limit, offset, update

    /**
     * setConditions 
     * 
     * @param mixed $conditions 
     * @access private
     * @return void
     */
    private function setConditions($conditions){
        $this->clearConditions();
        if(is_array($conditions)) 
            array_map(array($this, 'setCondition'), array_keys($conditions), array_values($conditions));
    }    

    /**
     * clearConditions 
     * 
     * @access private
     * @return void
     */
    private function clearConditions(){
        $this->where = null;
        $this->columns = null;
        $this->join = null;
        $this->order_by = null;
        $this->limit = null;
        $this->offset = null;
        $this->having = null;
        $this->group_by = null;
        $this->values = null;
    }

    /**
     * setCondition 
     * 
     * @param mixed $name 
     * @param mixed $value 
     * @access private
     * @return void
     */
    private function setCondition($name, $value){
        $method = 'set'.$this->getUpperCamelCase($name);
        $this->$method($value);
    }

    /**
     * getUpperCamelCase 
     * 
     * @param mixed $name 
     * @access private
     * @return void
     */
    private function getUpperCamelCase($name){
        $splited_underbar_value = explode("_", $name);
        $array_names = array_map("ucfirst", $splited_underbar_value);
        return implode('', $array_names);
    }

    /**
     * setSQL 
     * 
     * @param mixed $SQL 
     * @access private
     * @return void
     */
    private function setSQL($SQL){
        $this->dbh->setSQL($SQL);
    }

    /**
     * setExecuteValues 
     * 
     * @access private
     * @return void
     */
    private function setExecuteValues(){
        $this->dbh->setValue($this->values);
    }

    /**
     * setWhere 
     * 
     * @param mixed $where 
     * @access private
     * @return void
     */
    private function setWhere($where){
        $this->where = ' WHERE ';
        if(is_array($where)){
            $this->where .= $this->makeWhere($where);
        }else{
            $this->where .= $where;
        }
    }

    /**
     * setJoin 
     * 
     * @param mixed $join 
     * @access private
     * @return void
     */
    private function setJoin($join){
        $this->join = $join;
    }

    /**
     * setColumns 
     * 
     * @param mixed $columns 
     * @param mixed $type 
     * @access private
     * @return void
     */
    private function setColumns($columns, $type=null){
        $this->array_columns = $columns;
        $columns = $this->removeColumnOption($columns);
        $this->columns = ($this->SQL_type == 'update')?
         $this->makePlaceholdersSeparateKey($columns, ',') :
         $this->makeColumnsSeparateComma($columns);
    }

    /**
     * setValues 
     * 
     * @param mixed $values 
     * @access private
     * @return void
     */
    private function setValues($values){
        $this->values = $values;
    }

    /**
     * setHaving 
     * 
     * @param mixed $having 
     * @access private
     * @return void
     */
    private function setHaving($having){
        $this->having = ' HAVING '.$having;
    }

    /**
     * setGroupBy 
     * 
     * @param mixed $group_by 
     * @access private
     * @return void
     */
    private function setGroupBy($group_by){
        $this->group_by = ' GROUP BY '.$group_by;
    }

    /**
     * setOrderBy 
     * 
     * @param mixed $order_by 
     * @access private
     * @return void
     */
    private function setOrderBy($order_by){
        $this->order_by = ' ORDER BY ';
        $this->order_by .= (is_array($order_by))?
             implode(', ', $this->deleteAscDesc($order_by)).' '.$this->getSort($order_by) : $order_by;
    }

    /**
     * getSort 
     * 
     * @param array $order_by 
     * @access private
     * @return void
     */
    private function getSort(array $order_by){
        return (in_array('DESC', $order_by))? 'DESC' : 'ASC';
    }

    /**
     * deleteAscDesc 
     * 
     * @param array $order_by 
     * @access private
     * @return void
     */
    private function deleteAscDesc(array $order_by){
        $key = (in_array('ASC', $order_by))? array_search('ASC', $order_by) :
         ((in_array('DESC', $order_by))? array_search('DESC', $order_by) : false);
        if($key) unset($order_by["$key"]);
        return $order_by;
    }

    /**
     * setLimit 
     * 
     * @param mixed $limit 
     * @access private
     * @return void
     */
    private function setLimit($limit){
        $this->limit = ' LIMIT '.$limit;
    }

    /**
     * setOffset 
     * 
     * @param mixed $offset 
     * @access private
     * @return void
     */
    private function setOffset($offset){
        $this->offset = ' OFFSET '.$offset;
    }

    /**
     * setQuery 
     * 
     * @param mixed $query 
     * @access private
     * @return void
     */
    private function setQuery($query){
        $this->query = $query;
    }
}
