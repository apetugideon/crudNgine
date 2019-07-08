<?php
 trait Utilities {
    
    //ERROR LOG
    public function log_dsError() : bool {
		$doneWriting = false;
        try {
			$fileName 	 = "errorlog.txt";
			$message  	 = "{$this->error_mess} \n On Line {$this->error_line} Of the File : {$this->error_file}\n\n";
            $doneWriting = $this->writeToFile($message, $fileName);
        } catch (Exception $e) {
			$this->error_line  =  $e->getLine(); 
			$this->error_file  =  $e->getFile();
			$this->error_mess  =  $e->getMessage();
            $this->log_dsError();
        }
		return $doneWriting;
    }


    public function writeToFile(string $fileContent="", string $fileName="") : bool {
		$to_return = false;
        if (($fileContent != "") && ($fileName != "")) {
			try {
				$fileHandle  = fopen($fileName, "a");
				$to_return   = (bool) fwrite($fileHandle, $fileContent);
				if ($to_return) {
					fclose($fileHandle);
				} 
			} catch (Exception $e) {
				$this->error_line  =  $e->getLine(); 
				$this->error_file  =  $e->getFile();
				$this->error_mess  =  $e->getMessage();
				$this->log_dsError();
			}
		} 
		return $to_return;
    }
	
	public function debug_array($desc, $inn_arr, $vd=false) {
		echo "<pre>$desc";
		if (!$vd) print_r($inn_arr); 
		if ($vd) var_dump($inn_arr); 
		echo "</pre>";
	}

}
