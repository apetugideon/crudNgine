<?php
trait crudTrait {
    public function init_saveRoutine() {
        if (($this->priKey == "") && (!isset($this->input_arr['date_created'])))  $this->input_arr['date_created']  = date('Y-m-d');
        if (($this->priKey == "") && (!isset($this->input_arr['date_modified']))) $this->input_arr['date_modified'] = date('Y-m-d');
        if (($this->priKey != "") && (!isset($this->input_arr['date_modified']))) $this->input_arr['date_modified'] = date('Y-m-d');
        return $this->init_doSave();
    }


    private function init_doSave() {
        if ($this->priKey == "") $this->init_preSaveValidations();//NEW RECORDS VALIDATIONS
        if ($this->proc == true) {
            $this->before_save();
            $saveObj = new DbHandlers();
            if (isset($this->input_arr[$this->priIndx]) && ($this->priKey == "")) {
                unset($this->input_arr[$this->priIndx]);
            }
            //$this->dbgarr('input_arr', $this->input_arr, true);
            $actionres = ($this->priKey == "") ? $saveObj->saveRec($this->input_arr,$this->action_class,true)
                                               : $saveObj->updateRec($this->input_arr,$this->action_class,'',true);

            //$this->dbgarr('$actionres', $actionres, true);
            if ($actionres['actRes']) {
                $action_refid = ($this->priKey == "") ? $actionres['resArr']['lastIns'] : $this->priKey;
                $act      = ($this->priKey == "") ? "save" : "update";
                $actn     = ($this->priKey == "") ? "Record Insertion" : "Record Update";
                $actdesc  = ($this->priKey == "") ? "Record Insertion into {$this->action_class}" : "Record Update in {$this->action_class}";
                $this->insertAuditElement($actn,$actdesc,$this->module,$this->smodule,$this->action_class,$action_refid,$act,$this->created_by);
                if ($this->priKey == "") {
                    $this->lstInsertedID = $actionres['resArr']['lastIns'];
                    $this->after_save();
                } else {
                    $this->after_update();
                }
                $this->stat_flag = 1;
                $this->stat_msg = "Action Successful";
                $this->extradatas = array();
                $this->__basicControlParam();
            } else {
                $this->stat_flag = 0;
                $this->stat_msg = "Action Failed";
                $this->extradatas = array();
                $this->__basicControlParam();
            }
        } else {
            $this->stat_flag = 0; $this->stat_msg = $this->statMsg; $this->extradatas = array();
            $this->__basicControlParam();
        }
        return $this->returnArr;
    }


    public function init_getRecs() {
        $startx   = microtime(true);
        $getParam = array();
        $this->init_getFilters();

        if (method_exists($this, 'before_get')) {
            if ($this->action_method == "get_project_details") {
                $actarr = array('getdrops','getLists');
                if (in_array($this->actual_action, $actarr)) {
                    $this->before_get();
                }
            } else {
                $this->before_get();
            }
        }

        $paramArr['tableName']    = $this->action_class;
        $paramArr['fldsel']       = $this->fldsel;
        $paramArr['limitOffset']  = $this->limitOffset;
        $paramArr['oneEqualsOne'] = false;
        $paramArr['orderby']      = $this->orderby;
        $paramArr['tabjoin_arr']  = $this->tabjoin_arr;

        if ($this->proc == true) {
          $this->retarr = $this->readRecs($this->filter_arr, $paramArr, true);
          //$this->dbgarr('retarr', $this->retarr, true);
        }
        $this->after_get();
        unset($paramArr['limitOffset']);
        $allrec  = $this->readRecs($this->filter_arr,$paramArr,true);
        $this->rec_cnt = (!empty($allrec['resArr']) && ($this->actual_action == "getLists")) ? count($allrec['resArr']) : 0;

        $this->__basicControlParam();
        $endx      = microtime(true);
        $timespent = $endx - $startx;
        $this->returnArr['timespent'] = $timespent;
        $this->returnArr['totrec']    = "{$this->rec_cnt} |||| {$this->limitOffset}";
        return $this->returnArr;
    }


    public function init_deleteRecs() {
        $this->before_delete();
        if ($this->proc == true) {
            $actionres = $this->deleteRec($this->input_arr,$this->action_class,true);
            if ($actionres['actRes']) {
                $this->after_delete();
                $action_refid = ($this->priKey == "") ? $actionres['resArr']['lastIns'] : $this->priKey;
                $act      = "delete";
                $actn     = "Record Deletion";
                $actdesc  = "Record Deletion from {$this->action_class}";
                $this->insertAuditElement($actn,$actdesc,$this->module,$this->smodule,$this->action_class,$action_refid,$act,$this->created_by);
                $this->stat_flag = 1; $this->stat_msg = "Successfully Deleted!"; $this->extradatas = array();
                $this->__basicControlParam();
            } else {
                $this->stat_flag = 0; $this->stat_msg = "Deletion Failed"; $this->extradatas = array();
                $this->__basicControlParam();
            }
        } else {
            $this->stat_flag = 0; $this->stat_msg = "Deletion Failed!"; $this->extradatas = array();
            $this->__basicControlParam();
        }
        return $this->returnArr;
    }
}
