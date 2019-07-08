<?php
trait postgetTrait {
    private function init_returnArr($reta,$j) {
        if (!empty($this->tab_desc)) {
            $this->returnSpecified();
            foreach ($this->tab_desc as $ky=>$val) {
                $this->resolveReturnArr($j,$reta,$val);
                $Field    = $val['Field'];
                $passdesc = (substr($Field,-11) == "___password") ? $Field."__asLoginAuth" : "";
                $passFld  = (($passdesc != "") && (substr($Field,-11) == "___password")) ? $Field : "";

                $fstCond = (($this->actIndx) && (in_array($this->actIndx, $this->index_arr))) ?? false;
                $secCond = (($this->fmtIndx) && (in_array($this->fmtIndx, $this->fmtindexarr))) ?? false;

                if ($fstCond) $this->returnArr[$j][$this->actIndx] = $this->actVal;
                if ($secCond) $this->returnArr[$j][$this->fmtIndx] = $this->fmtVal;
                if (($passFld != "") && isset($this->returnArr[$j][$passFld])) unset($this->returnArr[$j][$passFld]);

                $this->additional_retvals();
            }
        }
    }


    public function resolveReturnArr($j,$reta,$val) {
        $fldname  = $val['Field'];
        $valType  = explode("(",$val['Type'])[0];
        $is_null  = $val['Null'];
        $valueKey = $val['Key'];
        $defVal   = $val['Default'];

        //ACTUAL
        $this->actIndx  = $fldname;
        $this->actVal   = $reta[0][$this->actIndx] ?? $reta[$this->actIndx] ?? "";
        if ($valueKey == "MUL") {
            $this->fmtIndx = "{$this->actIndx}_desc";
            $this->fmtVal  = $reta[0][$this->fmtIndx] ?? $reta[$this->fmtIndx] ?? "";
        } else {
            if (substr($fldname,-11) == "___password") {
                $rawindx = "{$fldname}raw";
                $this->fmtIndx = "is_{$this->actIndx}";
                if (isset($this->$rawindx) && isset($this->actVal)) {
                    $this->fmtVal  = (password_verify($this->$rawindx, $this->actVal)) ?? false;
                }
            } else {
                if ($valType == "date" || $valType == "datetime") {
                    $dtflag  = false;
                    if (($valType == "date")) $dtflag  = true;
                    $this->fmtIndx = "{$this->actIndx}_fmt";
                    $this->fmtVal  = ($this->actVal != "") ? $this->renderDate($this->actVal,$dtflag) : "";
                } elseif (($valType == "int" || $valType == "float" || $valType == "double") && ($valueKey != "PRI")) {
                    $this->fmtIndx = "{$this->actIndx}_fmt";
                    $this->actVal  = ($this->actVal) ?? 0;
                    $this->fmtVal  = number_format($this->actVal,2);
                }
            }
        }
    }

}
