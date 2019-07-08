<?php
include_once('TableUpdate.php');
class QueryHandler {
    use TableUpdate;

    public  $curr_sql       = "";
    public  $save_sql       = "";
    private $update_sql     = "";
    private $delete_sql     = "";
    private $table_action   = "";
    private $cr8_tabSql     = "";
    private $upd8_tabSql    = "";
    private $col_name       = "";
    private $curr_rel       = "";
    private $curr_val       = "";
    public  $paramBind      = array();
    private $clust_xst      = false;
    private $tObj           = "";
    public  $mycolname      = "";
    public  $mycolrel       = "";
    public  $set_cluster    = "N";
    public  $close_cluster  = "N";
    private $operator       = "";
    private $curr_comp      = "";
	private $qry_orderd     = false;
	private $conn           = null;
    public  $result         = array();
    public  $qresult        = false;
    public  $updresult      = false;

    public $error_line      = ""; 
	public $error_file      = "";
    public $error_mess      = "";
    public $lastInsertedID  = "";
    
    public $save_param_name = array();
    public $save_name_value = array();
    public $update_name_value = array();


    const IS_AND        = 'IS_AND';
    const IS_OR         = 'IS_OR';
    const IS_LIKE       = 'IS_LIKE';
    const IS_IN         = 'IS_IN';
    const IS_GTOET      = 'IS_GTOET';   //GREATER THAN OR EQUAL TO
    const IS_LTOET      = "IS_LTOET";   //LESS THAN OR EQUAL TO
    const IS_GT         = "IS_GT";      //GREATER THAN
    const IS_LT         = "IS_LT";      //LESS THAN
    const IS_EQUAL      = "IS_EQUAL";   //EQUAL
    const NOT_EQUAL     = "NOT_EQUAL";  //NOT EQUAL
    const NOT_LIKE      = "NOT_LIKE";
    const ALL_LIKE      = "ALL_LIKE";
    const FIRST_LIKE    = "FIRST_LIKE";
    const SECOND_LIKE   = "SECOND_LIKE";
    const LAST_LIKE     = "LAST_LIKE";
    const FIRST_LAST    = "FIRST_LAST";
    const NOT_IN        = "NOT_IN";
    const BETWEEN       = "BETWEEN";
    const NOT_BETWEEN   = "NOT_BETWEEN";


    public function __construct($tabObj, $connObj) {
        $this->tObj = $tabObj;
		$this->conn = $connObj;
    }


    public function select_records($selections="*") {
        $this->curr_sql = "SELECT $selections FROM $this->tObj ";
        return $this;
    } 
    
    
    private function do_join($tab, $col_one, $col_two, $joinType="") {
        $this->curr_sql .= "\n$joinType JOIN $tab ON ($col_one = $col_two) ";
    }
    
    public function left_join($tab, $col_one, $col_two) {
        $this->do_join($tab, $col_one, $col_two, "LEFT");
        return $this;
    }
    
    
    public function inner_join($tab, $col_one, $col_two) {
        $this->do_join($tab, $col_one, $col_two, "INNER");
        return $this;
    }
    
    
    public function right_join($tab, $col_one, $col_two) {
        $this->do_join($tab, $col_one, $col_two, "RIGHT");
        return $this;
    }


    public function where_column($col_name) {
        $this->col_name = $col_name;
        $this->curr_sql .= "\nWHERE (";
        return $this;
    }
    
    
    public function and_compound() {
        $this->curr_sql .= "\nAND (";
        $this->curr_comp = "AND";
        return $this;
    }
    
    public function or_compound() {
        $this->curr_sql .= "\nOR (";
        $this->curr_comp = "OR";
        return $this;
    }
    
    public function end_compound($is_comp = "Y") {
        $this->curr_sql .= ")";
        return $this;
    }


    public function and_column($col_name) {
        $this->col_name = $col_name;
        $this->operator = "AND";
        if ($this->curr_comp != "") {
            $this->operator   = "";
            $this->curr_comp  = "";
        }
        return $this;
    }


    public function or_column(string $col_name) : object {
        $this->col_name = $col_name;
        $this->operator = "OR";
        if ($this->curr_comp != "") {
            $this->operator   = "";
            $this->curr_comp  = "";
        }
        return $this;
    }


    public function is_like(string $curr_val) : object {
        $this->curr_val = htmlspecialchars(strip_tags($curr_val));
        $this->curr_rel = self::IS_LIKE;
        $this->resolve_condition();
        return $this;
    }


    public function is_in(array $curr_val) : object {
        $this->curr_val = $curr_val;
        $this->curr_rel = self::IS_IN;
        $this->resolve_condition();
        return $this;
    }


    public function is_greater_than_or_equal_to(string $curr_val) : object {
        $this->curr_val = $curr_val;
        $this->curr_rel = self::IS_GTOET;
        $this->resolve_condition();
        return $this;
    }


    public function is_less_than_or_equal_to(string $curr_val) : object {
        $this->curr_val = $curr_val;
        $this->curr_rel = self::IS_LTOET;
        $this->resolve_condition();
        return $this;
    }


    public function greater_than(string $curr_val) : object {
        $this->curr_val = $curr_val;
        $this->curr_rel = self::IS_GT;
        $this->resolve_condition();
        return $this;
    }


    public function less_than(string $curr_val) : object {
        $this->curr_val = $curr_val;
        $this->curr_rel = self::IS_LT;
        $this->resolve_condition();
        return $this;
    }


    public function is_equal_to(string $curr_val) : object {
        $this->curr_val = $curr_val;
        $this->curr_rel = self::IS_EQUAL;
        $this->resolve_condition();
        return $this;
    }


    public function is_not_equal_to(string $curr_val) : object {
        $this->curr_val = $curr_val;
        $this->curr_rel = self::NOT_EQUAL;
        $this->resolve_condition();
        return $this;
    }


    public function is_not_like(string $curr_val) : object {
        $this->curr_val = $curr_val;
        $this->curr_rel = self::NOT_LIKE;
        $this->resolve_condition();
        return $this;
    }


    public function all_like(array $curr_val) : object {
        $this->curr_val = $curr_val;
        $this->curr_rel = self::ALL_LIKE;
        $this->resolve_condition();
        return $this;
    }


    public function first_letter_is(string $curr_val) : object {
        $this->curr_val = $curr_val;
        $this->curr_rel = self::FIRST_LIKE;
        $this->resolve_condition();
        return $this;
    }


    public function second_letter_is(string $curr_val) : object {
        $this->curr_val = $curr_val;
        $this->curr_rel = self::SECOND_LIKE;
        $this->resolve_condition();
        return $this;
    }


    public function last_letter_is(string $curr_val) : object {
        $this->curr_val = $curr_val;
        $this->curr_rel = self::LAST_LIKE;
        $this->resolve_condition();
        return $this;
    }


    public function first_last_letters_are(array $curr_val) : object {
        $this->curr_val = $curr_val;
        $this->curr_rel = self::FIRST_LAST;
        $this->resolve_condition();
        return $this;
    }


    public function is_not_in(array $curr_val) : object {
        $this->curr_val = $curr_val;
        $this->curr_rel = self::NOT_IN;
        $this->resolve_condition();
        return $this;
    }


    public function is_between(array $curr_val) : object {
        $this->curr_val = $curr_val;
        $this->curr_rel = self::BETWEEN;
        $this->resolve_condition();
        return $this;
    }


    public function is_not_between(array $curr_val) : object {
        $this->curr_val = $curr_val;
        $this->curr_rel = self::NOT_BETWEEN;
        $this->resolve_condition();
        return $this;
    }


    public function execute_sql() : void {
		try {
			if (!$this->qry_orderd) $this->curr_sql .= ")";
			$stmt = $this->conn->prepare($this->curr_sql);
			$Qres = $stmt->execute($this->paramBind);
		} catch (PDOException $e) {
			$this->error_line  =  $e->getLine(); 
			$this->error_file  =  $e->getFile();
			$this->error_mess  =  $e->getMessage();
		}
        $this->result = ($Qres) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : array();
    }


    public function group_by(string $orderby = "") : object {
        if ($orderby != "") {
            $this->curr_sql .= "GROUP BY $orderby ";
        }
        return $this;
    }
    
    
    public function having_column(string $colname = "") : object {
        if ($colname != "") $this->curr_sql .= "HAVING $colname ";
        return $this;
    }


    public function order_by(string $orderby="", string $ord="") : object {
        if ($orderby != "") {
			$this->curr_sql .= ")";
			$this->qry_orderd= true;
            $this->curr_sql .= " ORDER BY $orderby ";
            if (($orderby != "") && ($ord != "")) {
                $this->curr_sql .= "$ord ";
            }
        }
        return $this;
    }


    public function limit_offset(string $limit="", string $offset="") : object {
        if ($limit != "") $this->curr_sql .= "LIMIT $limit";
        if (($limit != "") && ($offset != "")) $this->curr_sql .= "OFFSET $offset";
        return $this;
    }


    private function resolve_condition() : void {
        if (($this->col_name != "") && ($this->curr_rel != "")) {  
            if ($this->operator != "") $this->curr_sql .= " {$this->operator} ";
            $this->curr_sql .= "(" . $this->col_name . $this->resolve_rel() . ")";
        }
    }


    private function resolve_rel() : string {
        $outStr = "";
        switch (trim(strtoupper($this->curr_rel))) {
            case self::IS_GTOET :
            $outStr = " >= ?";
            $this->paramBind[] = "$this->curr_val";
            break;


            case self::IS_LTOET :
            $outStr = " <= ?";
            $this->paramBind[] = "$this->curr_val";
            break;


            case self::IS_GT :
            $outStr = " > ?";
            $this->paramBind[] = "$this->curr_val";
            break;


            case self::IS_LT :
            $outStr = " < ?";
            $this->paramBind[] = "$this->curr_val";
            break;


            case self::IS_EQUAL :
            $outStr = " = ?";
            $this->paramBind[] = "$this->curr_val";
            break;


            case self::NOT_EQUAL :
            $outStr = " != ?";
            $this->paramBind[] = "$this->curr_val";
            break;


            case self::IS_LIKE :
            $outStr = " LIKE ?";
            $this->paramBind[] = "$this->curr_val";
            break;


            case self::NOT_LIKE :
            $outStr = " NOT LIKE ?";
            $this->paramBind[] = "$this->curr_val";
            break;


            case self::ALL_LIKE :
            $outStr = " LIKE ?";
            $this->paramBind[] = "%$this->curr_val%";
            break;


            case self::FIRST_LIKE :
            $outStr = " LIKE ?";
            $this->paramBind[] = "$this->curr_val%";
            break;


            case self::SECOND_LIKE :
            $outStr = " LIKE ?";
            $this->paramBind[] = "_$this->curr_val%";
            break;


            case self::LAST_LIKE :
            $outStr = " LIKE ?";
            $this->paramBind[] = "%$this->curr_val";
            break;


            case self::FIRST_LAST :
            $outStr = " LIKE ? % ?";
            $this->paramBind[] = array("{$this->curr_val[0]}","{$this->curr_val[1]}");
            break;


            case self::IS_IN :
            $in_values = "";
            if (is_array($this->curr_val)) {
                $in_values = " (".str_pad('',count($this->curr_val)*2-1,'?,').")";
                for ($t=0; $t<count($this->curr_val); $t+=1) {
                    $this->paramBind[] = htmlspecialchars(strip_tags($this->curr_val[$t]));
                }
            }
            $outStr = " IN $in_values";
            break;


            case self::NOT_IN :
            $in_values = "";
            if (is_array($this->curr_val)) {
                $in_values = " (".str_pad('',count($this->curr_val)*2-1,'?,').")";
                for ($t=0; $t<count($this->curr_val); $t+=1) {
                    $this->paramBind[] = htmlspecialchars(strip_tags($this->curr_val[$t]));
                }
            }
            $outStr = " NOT IN $in_values";
            break;


            case self::BETWEEN :
            $outStr = " BETWEEN ? AND ?";
            $this->paramBind[] = array("{$this->curr_val[0]}", "{$this->curr_val[1]}");
            break;


            case self::NOT_BETWEEN :
            $outStr = " NOT BETWEEN ? AND ?";
            $this->paramBind[] = array("{$this->curr_val[0]}", "{$this->curr_val[1]}");
            break;


            default :
            $outStr = "";
        }
        return $outStr;
    }
    
    
    public function init_save() : object {
        return $this;
    }


    public function add_value(string $colname, string $colvalue) : object {
        $this->save_param_name[] = $colname;
        $this->save_name_value[$colname] = htmlspecialchars(strip_tags($colvalue));
        return $this;
    }


    public function save_record() : object {
        try {
            $inn_query       = implode(", ", $this->save_param_name);
            $valu_qry        = ":" . implode(", :", $this->save_param_name);
            $this->save_sql .= "INSERT INTO {$this->tObj} ($inn_query) ";
            $this->save_sql .= " VALUES ($valu_qry) ";
            $stmt = $this->conn->prepare($this->save_sql);
            foreach ($this->save_name_value as $ky=>$each_elm) {
                $stmt->bindValue(":$ky", $each_elm);
            }
            $this->qresult     =  $stmt->execute();
        } catch (Exception $e) {
            $this->error_line  =  $e->getLine(); 
			$this->error_file  =  $e->getFile();
			$this->error_mess  =  $e->getMessage();
        }
        $this->lastInsertedID  =  $this->conn->lastInsertId();
        return $this;
    }
    
    
    public function init_upate() : object {
        return $this;
    }


	public function update_value(string $colname, string $colvalue) : object {
        if ($this->curr_sql == "") {
            $this->curr_sql .= "$colname = ?";
        } else {
            $this->curr_sql .= ",\n$colname = ?";
        }
        $this->paramBind[] = htmlspecialchars(strip_tags($colvalue));
        return $this;
    }


    public function execute_update() : object {
        try {
            $this->update_sql .= "UPDATE {$this->tObj} SET {$this->curr_sql}) ";
            $stmt = $this->conn->prepare($this->update_sql);
            $this->updresult   = $stmt->execute($this->paramBind);
        } catch (Exception $e) {
            $this->error_line  =  $e->getLine(); 
			$this->error_file  =  $e->getFile();
			$this->error_mess  =  $e->getMessage();
        }
        return $this;
    }


    function init_delete() : object {
        $this->delete_sql = "DELETE FROM {$this->tObj} ";
        return $this;
    }


    public function execute_delete() : void {
        if ($this->curr_sql != "") {
            try {
                $this->delete_sql .= "{$this->curr_sql}) ";
                $stmt = $this->conn->prepare($this->delete_sql);
                $this->updresult   = $stmt->execute($this->paramBind);
            } catch (Exception $e) {
                $this->error_line  =  $e->getLine(); 
                $this->error_file  =  $e->getFile();
                $this->error_mess  =  $e->getMessage();
            }
        } else {
            $this->action_error = "\nData can only be deleted base on certain condiiton";
        }
    }


    //TO DESCRIBE A TABLE
    public function desc_table() {
        if ($this->tObj != "") {
            try {
                $stmt   = $this->conn->prepare("DESCRIBE {$this->tObj}");
                return ($stmt->execute()) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : array();
            } catch (Exception $e) {
                $this->error_line  =  $e->getLine(); 
                $this->error_file  =  $e->getFile();
                $this->error_mess  =  $e->getMessage();
            }
        }
    }
}
