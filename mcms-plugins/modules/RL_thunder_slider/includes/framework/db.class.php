<?php
/**
 * @author    MandarinCMS <info@jiiworks.net>
 * @link      http://www.jiiworks.net/
 * @copyright 2015 MandarinCMS
 */

if( !defined( 'BASED_TREE_URI') ) exit();

class ThunderSliderDB{
	
	private $lastRowID;
	
	/**
	 * 
	 * constructor - set database object
	 */
	public function __construct(){
	}

	/**
	 * 
	 * throw error
	 */
	private function throwError($message,$code=-1){
		ThunderSliderFunctions::throwError($message,$code);
	}
	
	//------------------------------------------------------------
	// validate for errors
	private function checkForErrors($prefix = ""){
		global $mcmsdb;
		
		if($mcmsdb->last_error !== ''){
			$query = $mcmsdb->last_query;
			$message = $mcmsdb->last_error;
			
			if($prefix) $message = $prefix.' - <b>'.$message.'</b>';
			if($query) $message .=  '<br>---<br> Query: ' . esc_attr($query);
			
			$this->throwError($message);
		}
	}
	
	
	/**
	 * 
	 * insert variables to some table
	 */
	public function insert($table,$arrItems){
		global $mcmsdb;
		
		$mcmsdb->insert($table, $arrItems);
		$this->checkForErrors("Insert query error");
		
		$this->lastRowID = $mcmsdb->insert_id;
		
		return($this->lastRowID);
	}
	
	/**
	 * 
	 * get last insert id
	 */
	public function getLastInsertID(){
		global $mcmsdb;
		
		$this->lastRowID = $mcmsdb->insert_id;
		return($this->lastRowID);			
	}
	
	
	/**
	 * 
	 * delete rows
	 */
	public function delete($table,$where){
		global $mcmsdb;
		
		ThunderSliderFunctions::validateNotEmpty($table,"table name");
		ThunderSliderFunctions::validateNotEmpty($where,"where");
		
		$query = "delete from $table where $where";
		
		$mcmsdb->query($query);
		
		$this->checkForErrors("Delete query error");
	}
	
	
	/**
	 * 
	 * run some sql query
	 */
	public function runSql($query){
		global $mcmsdb;
		
		$mcmsdb->query($query);			
		$this->checkForErrors("Regular query error");
	}
	
	
	/**
	 * 
	 * run some sql query
	 */
	public function runSqlR($query){
		global $mcmsdb;
		
		$return = $mcmsdb->get_results($query, ARRAY_A);
		
		return $return;
	}
	
	
	/**
	 * 
	 * insert variables to some table
	 */
	public function update($table,$arrItems,$where){
		global $mcmsdb;
		
		$response = $mcmsdb->update($table, $arrItems, $where);
		
		return($mcmsdb->num_rows);
	}
	
	
	/**
	 * 
	 * get data array from the database
	 * 
	 */
	public function fetch($tableName,$where="",$orderField="",$groupByField="",$sqlAddon=""){
		global $mcmsdb;
		
		$query = "select * from $tableName";
		if($where) $query .= " where $where";
		if($orderField) $query .= " order by $orderField";
		if($groupByField) $query .= " group by $groupByField";
		if($sqlAddon) $query .= " ".$sqlAddon;
		
		$response = $mcmsdb->get_results($query,ARRAY_A);
		
		$this->checkForErrors("fetch");
		
		return($response);
	}
	
	/**
	 * 
	 * fetch only one item. if not found - throw error
	 */
	public function fetchSingle($tableName,$where="",$orderField="",$groupByField="",$sqlAddon=""){
		$response = $this->fetch($tableName, $where, $orderField, $groupByField, $sqlAddon);
		
		if(empty($response))
			$this->throwError("Record not found");
		$record = $response[0];
		return($record);
	}
	
	
	/**
	 * prepare statement to avoid sql injections
	 */
	public function prepare($query, $array){
		global $mcmsdb;
		
		$query = $mcmsdb->prepare($query, $array);
		
		return($query);
	}
	
}

/**
 * old classname extends new one (old classnames will be obsolete soon)
 * @since: 5.0
 **/
class UniteDBRev extends ThunderSliderDB {}
?>