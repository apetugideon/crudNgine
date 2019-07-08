<?php
include_once('crudTrait.php');
include_once('presaveTrait.php');
include_once('postsaveTrait.php');
include_once('pregetTrait.php');
include_once('postgetTrait.php');

trait crudEngine {
    use crudTrait;
    use presaveTrait;
    use postsaveTrait;
    use pregetTrait;
    use postgetTrait;

    private function init_coreVariables() {
        $this->currdata = $_POST ?? $_GET ?? $_REQUEST ?? array();
        $this->action_indexs  = array('action_class','action_method','actual_action','action_module','authExempt','is_accessright_driven');

        for ($j=0; $j<count($this->action_indexs); $j+=1) {
            $cur_indx = $this->action_indexs[$j];
            if ($cur_indx == "authExempt" || $cur_indx == "is_accessright_driven") continue;
            $this->$cur_indx = $this->currdata[$cur_indx] ?? "";
        }

        $this->priKey = "";
        if ($this->action_class != "") $this->tab_desc = $this->desc_table($this->action_class);
        if (!empty($this->tab_desc)) $this->priIndx = $this->tabKeys($this->tab_desc,'PRI');
        $this->init_classProperties();
        if ($this->actual_action == "getLists") {
            if ($this->limitOffset == "ALL") $this->limitOffset  = "";
        }
        $this->fields2validate();
        $this->module_resolve();
    }


    private function initial_propertiesval() {
        if (!empty($this->tab_desc)) {
            $this->dbtabscols = array();
            foreach ($this->tab_desc as $key => $value) {
                $field = $value['Field'];
                $this->dbtabscols[] = $field;
                $this->$field = "";
            }
        }
    }


    private function init_classProperties() {
        $this->limitOffset = $this->currdata['limitOffset'] ?? "10";
        if (!empty($this->currdata)) {
            $user_rfdet   = $this->CURR_USER_ID();
            $this->initial_propertiesval();
            $user_rfindx  = $user_rfdet[0]['referenced_column_name']  ?? "";
            foreach ($this->currdata as $key => $value) {
                if (!in_array($key, $this->action_indexs)) {
                    if ($key == $user_rfindx) {
                        $this->created_by  = $value;
                        $this->modified_by = $value;
                        $this->input_arr['created_by']  = $this->created_by;
                        $this->input_arr['modified_by'] = $this->modified_by;
                        if ($this->priIndx == $key) $this->priKey = $value;
                    } elseif ($key != $user_rfindx) {
                        foreach ($this->tab_desc as $ky => $val) {
                            $fldName    = $val['Field'];
                            $valueType  = explode("(", $val['Type'])[0];
                            $is_null    = $val['Null'];
                            $valueKey   = $val['Key'];
                            $defaultVal = $val['Default'];
                            if ($key == $fldName) {
                                $this->$key = $this->resolveSanitizeInputVal($fldName,$value,$valueType,$is_null,$valueKey,$defaultVal);
                                if ((substr($key,-11) == "___password")) {
                                    $rawindx = "{$key}raw";
                                    $this->$rawindx = $this->$key;
                                    $this->$key = password_hash($this->$key, PASSWORD_DEFAULT);
                                    $this->input_arr[$key] = $this->$key;
                                } else {
                                    $this->input_arr[$key] = $this->$key;
                                }
                                if ($this->priIndx == $key) $this->priKey = $this->$key;
                            }
                        }
                    }
                }
                if ($this->priIndx == $key && $this->priKey == "") $this->priKey = $value;
            }
            //$this->extend_class_propertiesval();
            $this->aggregateInputArrWithDbTab();
        }
    }


    private function aggregateInputArrWithDbTab() {
        foreach ($this->input_arr as $key=>$value) {
            if (!in_array($key, $this->dbtabscols)) {
                unset($this->input_arr[$key]);
            }
        }
    }


    private function resolveSanitizeInputVal($name,$value,$type,$is_null,$key,$defval) {
        $newval = "";
        if ($type == "char") {
            if ($value == "on") {
                $newval = "Y";
            } elseif ($value == "off") {
                $newval = "N";
            } elseif ($value != "" && $value != "off" && $value != "on") {
                $newval = $value;
            } elseif ($value == "" && $defval != "") {
                $newval = $defval;
            }
        } elseif ($type == "varchar" || $type == "mediumtext" || $type == "tinytext" || $type == "text") {
            if ($value != "") {
                $newval = $value;
            } elseif ($value == "" && $defval != "") {
                $newval = $defval;
            }
        } elseif ($type == "int" || $type == "float" || $type == "double") {
            if ($value != "") {
                $newval = str_replace(',','',$value);
            } elseif ($value == "" && $defval != "") {
                $newval = $defval;
            } elseif ($value == "" && $defval == "" && $is_null == "NO") {
                $newval = 0;
            }
        } elseif ($type == "date" || $type == "datetime") {
            if ($value != "") {
                $newval = $value;
            } elseif ($value == "" && $defval != "") {
                $newval = $defval;
            }
        }
        return $newval;
    }


    private function __basicControlParam() {
        $this->returnArr['stat_flag']     = $this->stat_flag;
        $this->returnArr['stat_msg']      = $this->statMsg ?? "";
        $this->returnArr['extradatas']    = array();
        $this->returnArr['totrec']        = 0;//$rec_cnt;
        $this->returnArr['action_method'] = $this->action_method;
        $this->returnArr['actual_action'] = $this->actual_action;
        $this->returnArr['action_class']  = $this->action_class;
        $this->returnArr['action_module'] = $this->action_module;
    }


    private function module_resolve() {
        $this->module   = "";
        $this->smodule  = "";
        if ($this->action_module != "") {
            $mod_det = explode("/",$this->action_module);
            if (!empty($mod_det)) {
                if (isset($mod_det[0])) $this->module  = $mod_det[0];
                if (isset($mod_det[1])) $this->smodule = $mod_det[1];
            }
        }
    }


    private function dbgarr($desc, $arrs, $activate=false) {
        if ($activate) {
            echo "<pre>{$desc}";
            print_r($arrs);
            echo "</pre>";
        }
    }


    public function renderDate($indate="0000-00-00 00:00:00",$dateonly=false) {
        if ($indate != "0000-00-00 00:00:00") {
            $splitdate = explode(" ",$indate);
            $date = (isset($splitdate[0])) ? $splitdate[0] : "0000-00-00";
            $time = (isset($splitdate[1])) ? $splitdate[1] : "00:00:00";

            $year = "";
            $mont = "";
            $days = "";
            if ($date != "0000-00-00") {
                $datecomp = explode("-",date_format(date_create($date),"Y-M-d"));
                $year = $datecomp[0];
                $mont = $datecomp[1];
                $days = $datecomp[2];
            }

            $hour = "";
            $min  = "";
            $secs = "";
            if ($time != "00:00:00") {
                $timecomp = explode(":",$time);
                $hour = $timecomp[0];
                $min  = $timecomp[1];
                $secs = $timecomp[2];
            }

            $time_desc = ($hour != "" && $min != "") ? "$hour:$min" : ""; // && $hour != "00" && $min != "00"
            $date_desc = ($mont != "" && $days != "" && $year != "") ? "$mont $days, $year" : "";

            if ($dateonly) {
                return html_entity_decode("$mont $days, $year");
            } else {
                return html_entity_decode("$time_desc &nbsp;&nbsp; $date_desc");
            }
        }
    }


    public function countDescription($valIn=0) {
        $valOut   = 0;
        $num_desc = "";
        if ($valIn >= 1000000000 && $valIn <= 9999999999) {
            $valOut = round(($valIn/1000000000),6);
            $num_desc = "b";
        } elseif ($valIn >= 1000000 && $valIn <= 999999999) {
            $valOut = round(($valIn/1000000),6);
            $num_desc = "m";
        } elseif ($valIn >= 1000 && $valIn <= 999999) {
            $valOut = round(($valIn/1000),6);
            $num_desc = "k";
        } elseif ($valIn >= 1 && $valIn <= 999) {
            $valOut = $valIn;
        }

        $valOutdesc = "";
        $valsplit   = explode(".",$valOut);
        if (is_float($valOut) && (isset($valsplit[1])) && ($valsplit[1] > 0)) {
            $valOutdesc .= "+ ";
        }
        $valOutdesc .= $valsplit[0].$num_desc;
        return $valOutdesc;
    }


    public function insertAuditElement($aud_names='',$aud_desc='',$mod='',$sub_mod='',$interf='',$act_refid='',$act='',$actn_by='') {
        $inn_arr = array();
        if ($aud_names != "") $inn_arr['names']         = $aud_names;
        if ($aud_desc != "")  $inn_arr['description']   = $aud_desc;
        if ($mod != "")       $inn_arr['module']        = $mod;
        if ($sub_mod != "")   $inn_arr['submodule']     = $sub_mod;
        if ($interf != "")    $inn_arr['interface']     = $interf;
        if ($act_refid != "") $inn_arr['refid']         = $act_refid;
        if ($act != "")       $inn_arr['actions']       = $act;
        if ($actn_by != "")   $inn_arr['created_by']    = $actn_by;
        if ($actn_by != "")   $inn_arr['modified_by']   = $actn_by;

        $actdate                    = date('Y-m-d');
        $inn_arr['action_datetime'] = date('Y-m-d H:s:i');
        $inn_arr['date_created']    = $actdate;
        $inn_arr['date_modified']   = $actdate;
        $inn_arr['is_status']       = "Y";

        $audObj  = new DbHandlers();
        $aud_res = $audObj->saveRec($inn_arr,"auditlog",true);
        //$this->dbgarr('$aud_res', $aud_res, true);
        return $aud_res;
    }
}
