<?php
class ConnectionsHandler {
	private $host 		= "";
	private $dbname 	= "";
	private $user 		= "";
	private $pass 		= "";
	
    public function __constructor() {}
	
	private function resolve_conn_params(string $host, string $dbname, string $user, string $pass) : void { //VALIDATE DB PARAMS HERE
		$this->host 	= $host;
		$this->dbname 	= $dbname;
		$this->user 	= $user;
		$this->pass 	= $pass;
	}

	public function do_connections(string $host, string $dbname, string $user, string $pass) { //Parameter from service environmental variables
		$ds_conn = null;
		$this->resolve_conn_params($host, $dbname, $user, $pass);
		try {
			$ds_conn = new PDO("mysql:host={$this->host};dbname={$this->dbname}", $this->user, $this->pass, array(
				PDO::ATTR_PERSISTENT => true
			));
			$ds_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $e) {
			$this->error_line  =  $e->getLine(); 
			$this->error_file  =  $e->getFile();
			$this->error_mess  =  $e->getMessage();
            $this->log_dsError();
		}
		return $ds_conn;
	}
}
