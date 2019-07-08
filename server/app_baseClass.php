<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once('utilities/ConnectionsHandler.php');
include_once('utilities/QueryHandler.php');
include_once('utilities/DataHandler.php');
include_once('utilities/Utilities.php');
class app_baseClass extends ConnectionsHandler {
    use DataHandler;
    use Utilities;

    public  $tableName   	 = "";
    public  $fieldSel    	 = "";
    public  $limitOffset 	 = "";
    public  $returnDebug 	 = "";
    public  $oneEqualOne 	 = "";
    public  $condtionArr 	 = array();
    private $sqlQry          = "";
    public  $orderby		 = "";
    public  $orderprecedence = "";
    private $lastInsertedID  = "";
    private $Qres            = false;
    private $errLine         = "";
    private $errFile         = "";
    private $errMsg          = "";
    public  $returnVal       = array();
    private $conn            = null;


    public function __construct() {
        $this->doCons();
    }


    private function doCons() {
        //$this->conn = $this->do_connections("localhost", "cornerstonelife_ieslifehmrs", "root", "");
        $this->conn = $this->do_connections("localhost", "taskticketmanijae", "root", "");
    }


    public function fetchData() {
       
       /*//CREATE
       $saveObj = new QueryHandler("users_details", $this->conn); 
       $saveObj->init_save()
       ->add_value("names", "Apetu Test")
       ->add_value("address", "1234, Avenue")
       ->save_record();**/
        //unset($saveObj);
		
		//READ
		/*$qryObj = new QueryHandler("hmrs_attendance", $this->conn); 
       $qryObj->select_records("hmrs_attendance.regdate, hmrs_attendance.staffid, hmrs_persdet.surname, hmrs_persdet.othnames")
	   ->inner_join("hmrs_persdet", "hmrs_attendance.staffid", "hmrs_persdet.staffid")
	   ->where_column("hmrs_attendance.regdate")->is_greater_than_or_equal_to("2019-05-01")
	   ->and_column("hmrs_attendance.regdate")->is_less_than_or_equal_to("2019-05-31")
	   ->order_by("hmrs_attendance.regdate");
       $qryObj->execute_sql();
	   $result = $qryObj->result;
       $this->debug_array($qryObj->curr_sql, $result);**/


        //UPDATE
        /*$saveObj = new QueryHandler("users_details", $this->conn); 
        $saveObj->init_upate()
        ->update_value("names", "Apetu Gideon")
        ->update_value("address", "1234, house address")
        ->update_value("email", "test@gmail.com")
        ->where_column("users_details_id")->greater_than(11)
        ->and_column("names")->is_in(['Array','Apetu Test'])
        ->execute_update();*/

        //DELECT
        /*$delObj = new QueryHandler("users_details", $this->conn); 
        $delObj->init_delete()
        ->where_column("users_details_id")->greater_than(10)
        ->and_column("names")->is_in(['Array','Apetu Test'])
        ->execute_delete();*/

        //TESTING 
        //NEW THING
        
        $cr8TabObj = new QueryHandler("users_men", $this->conn);
        $cr8TabObj->create_update_table()
        ->int_value('nameid', 11, ['nullabe'=>true,'unique'=>true]);
     
        /*echo "<pre>desc"; 
        print_r($delObj);
        echo "</pre>";**/
        //var_dump($saveObj->save_param_value);
    }
}
$myObj = new app_baseClass();
$myObj->fetchData();
