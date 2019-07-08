<?php
trait pregetTrait {
    public function getFilters($fldname,$fldkey) {
        if ($fldkey == "MUL") {
            $tabname = substr($fldname,0,-3);
            $pfk_rec = $this->primary_foreign_keys($this->action_class,$fldname,'','');
            $reftab  = ($pfk_rec[0]['referenced_table_name'])  ?? "";
            $refcol  = ($pfk_rec[0]['referenced_column_name']) ?? "";
            if ($this->action_class == $reftab) {
                //
            } else {
                $this->fldsel .= ($this->fldsel == "") ? "{$reftab}.{$refcol}, {$reftab}.names AS {$refcol}_desc" : ", {$reftab}.{$refcol}, {$reftab}.names AS {$refcol}_desc";
                //$this->fldsel .= ($this->fldsel == "") ? "{$reftab}.{$refcol}, {$reftab}.{$refcol} AS {$refcol}_desc" : ", {$reftab}.{$refcol}, {$reftab}.{$refcol} AS {$refcol}_desc";
                if (!in_array($reftab,$this->reftab_arr)) {
                    $this->tabjoin_arr[] = " {$reftab} ON {$this->action_class}.{$fldname} = {$reftab}.{$refcol}";
                    $this->reftab_arr[] = $reftab;
                }
                if ($this->$fldname != "" && substr($fldname,-11) != "___password")  $this->filter_arr[] = array("AND","{$reftab}.{$refcol}","=",$this->$fldname);
            }
        } else {
            $this->fldsel .= ($this->fldsel == "") ? "{$this->action_class}.{$fldname}" : ", {$this->action_class}.{$fldname}";
            if ($this->$fldname != "" && substr($fldname,-11) != "___password")  $this->filter_arr[] = array("AND","{$this->action_class}.{$fldname}","=",$this->$fldname);
        }
    }


    public function init_getFilters() {
        if (!empty($this->tab_desc)) {
            $c = 0;
            $fldnamarr = array('mail_onsave','mail_onget','mail_onupdate','mail_ondelete');
            foreach ($this->tab_desc as $key => $value) {
                $fldname = $value['Field'] ?? "";
                if (in_array($fldname,$fldnamarr)) continue;
                $fldkey  = $value['Key']   ?? "";
                if ((!isset($this->currdata['created_by'])) && ($fldname == "created_by")) continue;
                if ((!isset($this->currdata['modified_by'])) && ($fldname == "modified_by")) continue;
                $this->getFilters($fldname,$fldkey);
                $c += 1;
            }
        }
        $this->additional_filters();
    }
}
