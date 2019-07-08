<?php
  trait DataHandler {

    //TO EXECUTE ANY QUERY PASSED TO IT
    public function executeQuery($in_query) {
        try {
            if ($in_query != "") {
                $stmt       = $this->conn->prepare($in_query);
                $this->Qres = $stmt->execute();
            }
        } catch (Exception $e) {
            $this->error_line = $e->getLine();
            $this->errFile = $e->getFile();
            $this->errMsg  = $e->getMessage();
            $this->log_dsError();
        }
        return $this->Qres;
    }

    //TO DESCRIBE A TABLE
    public function getThisRec($query) {
        try {
            if ($query != "") {
                $stmt       = $this->conn->prepare($query);
                $stmt->execute();
                $each_rec   = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return $each_rec;
            }
        } catch (Exception $e) {
            $this->error_line = $e->getLine();
            $this->errFile = $e->getFile();
            $this->errMsg  = $e->getMessage();
            $this->errorLogg();
        }
    }


  }
?>
