<?php
include_once('app_baseClass.php');
include_once('crudEngine.php');
class Entrance extends app_baseClass {
    private $proc_action  = false;
    private $user_status  = 0;
    private $user_token   = "";
    private $statusmess   = "";
    private $actionUrl    = "";
    private $userIDtab    = "";
    private $user_access  = false;
    private $is_accessright_driven = false;
    private $return_arr   = array();
    private $curr_action  = "";


    function __construct() {
        $this->pfka  = $this->CURR_USER_ID();
        $this->initVariables();
    }


    private function initVariables() {
        $this->currdata       = $_POST ?? $_GET ?? $_REQUEST ?? array();
        $this->action_class   = $this->currdata['action_class']   ?? "";
        $this->action_module  = $this->currdata['action_module']  ?? "";
        $this->action_method  = $this->currdata['action_method']  ?? $this->currdata['user_module'] ?? "";
        $this->actual_action  = $this->currdata['actual_action']  ?? "";
        $this->userToken      = $this->currdata['userToken']      ??  "";
        $this->authExempt     = $this->currdata['authExempt']     ?? array();
        $this->is_accessright_driven = $this->currdata['is_accessright_driven'] ?? false;
    }


    public function initAction() {
        try {
            if ($this->userToken != "" && $this->action_module == "" && $this->action_class == ""){
                $this->initUserValidate();
            } else {
                $this->getActionUrl();
                if (file_exists($this->actionUrl)) {
                    include_once($this->actionUrl);
                } else {
                    $this->statusmess .= "Could not resolve action URL!";
                }

                $action_cls   = "proc_".$this->action_class."_cls";
                if ($this->userToken == "") {
                    //$this->return_arr['B4LOGIN'] = "this->userToken== $this->userToken";
                    if (in_array($this->action_method, $this->authExempt)) {
                        $this->proc_action = true;
                    } else {
                        $this->proc_action = false;
                        $this->statusmess .= "Could not resolve your Identity and the Intended Action !";
                    }
                } else {
                    $this->initUserValidate();
                    //$this->return_arr['code_block2'] = "this->user_status== $this->user_status";
                    if ($this->user_status) $this->proc_action = true;
                    if (($this->proc_action) && ($this->is_accessright_driven)) {
                        $this->user_access = $this->getAccessRight();
                        if (!$this->user_access) {
                            $this->proc_action = false;
                            $this->statusmess .= "You do not have the access right to perform this action !";
                        }
                    }
                }
                $exp = ($this->userToken != "" && $this->action_module == "" && $this->action_class == "") ? false : true;
                if (($this->proc_action) && ($exp)) {
                    $actionObj = new $action_cls() ?? null;
                    $curr_action = $this->action_method;
                    $this->return_arr = $actionObj->$curr_action() ?? array();
                }

                $this->return_arr['userStatus'] = $this->user_status;
                $this->return_arr['userToken']  = $this->user_token;
            }
        } catch (Exception $e) {

        }
        return $this->return_arr;
    }


    public function getAccessRight() {
        //
    }


    private function getActionUrl() {//Check if a url is sent before setting this as that should overwrite this.
        $this->actionUrl   = "../";
        $this->actionUrl  .= $this->action_module."/".$this->action_class."/proc_".$this->action_class."_cls.php";
    }


    public function getUserIDtab() {
      $this->userIDtab = substr(($this->pfka[0]['referenced_column_name']),0,-3);
    }


    public function initUserValidate($justValidate=false) {
        $user_qry   = "SELECT * FROM users_details WHERE user_token='$this->userToken'";
        //$this->return_arr['validateQry'] = "$user_qry";
        //$this->return_arr['userToken2'] = "$this->userToken";
        $user_rec   = $this->getQueryRec($user_qry);
        $out_arr    = array();
        if (!empty($user_rec)) {
            //$this->return_arr['userToken3'] = "$this->userToken";
            $user_id   = $user_rec[0]['users_details_id']  ?? "";
            $last_seen = $user_rec[0]['last_seen'] ?? "";
            $is_verify = (password_verify($last_seen."{$user_id}", $this->userToken)) ?? false;
            if ($is_verify) {
                $user_details       = $this->tokenRecycle($user_id, "users_details",'users_details_id');
                $this->user_status  = $user_details['userStatus'] ?? 0;
                $this->user_token   = $user_details['userToken']  ?? "";

                /**if ($this->actual_action == "getdrops") {
                    $this->user_status  = 1;
                    $this->user_token   = $this->userToken;
                } else {
                    $user_details = $this->tokenRecycle($user_id, "users_details",'users_details_id');
                    $this->user_status  = $user_details['userStatus'] ?? 0;
                    $this->user_token   = $user_details['userToken']  ?? "";
                }**/
            } else {
                $this->user_status  = 0;
                $this->user_token   = "FAILED";
            }
        }
    }
}
