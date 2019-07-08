<?php
trait presaveTrait {
    private function init_preSaveValidations() {
        if (!empty($this->tab_desc)) {
            foreach ($this->tab_desc as $key => $value) {
                $curval = "validate{$value['Field']}" ?? "";
                if (isset($this->$curval) && ($this->$curval) && ($this->proc)) {
                    $curitem = $this->$key ?? "";
                    $this->inputValueValidator($curitem == "", " {$fldname} can not be empty! ");
                }
            }
            $this->additional_validation();
        }
    }


    private function inputValueValidator($condResolve,$if_fail="") {
        if ($condResolve) {
            $this->statMsg = $if_fail;
            $this->proc    = false;
        }
    }
}
