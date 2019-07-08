<?php
  /**
  * DocUploadHandler
  * A Document Upload Handler
  * @access public
  */
  class DocUploadHandler {
    private $destdir   = "";
    private $allowsize = 10000000000;
    private $filename  = "";
    private $filetype  = "";
    private $tempname  = "";
    private $filerror  = "";
    private $filesize  = "";
    private $curfile   = "";
    public  $uplStatus = array();
    private $fileTo    = "";

    function __construct () {
      //$this->fileTo = "../../uploaded/videos";
    }

    private function doUpload($pos) {
      $temp = explode(".", $this->filename);
      $extension = end($temp);
      if ($this->filesize < $this->allowsize) {
        if ($this->filerror > 0) {
          $ret[$pos]['msg']     = "Return Code: " . $this->filerror . "<br>";
          $ret[$pos]['upstat']  = 0;
          $ret[$pos]['fname']   = '';
          $ret[$pos]['ftype']   = '';
        } else {
          move_uploaded_file($this->tempname,$this->fileTo ."/". $this->filename);
          $ret[$pos]['msg']     = "Stored in: " . $this->destdir . $this->filename;
          $ret[$pos]['fname']   = $this->filename;
          $ret[$pos]['upstat']  = 1;
          $ret[$pos]['ftype']   = $this->filetype;
          /**if (file_exists($this->destdir . $this->filename)) {
            $ret[$pos]['msg']     = $this->filename . " already exists. ";
            $ret[$pos]['fname']   = $this->filename;
            $ret[$pos]['upstat']  = 0;
            $ret[$pos]['ftype']   = $this->filetype;
          } else {
            move_uploaded_file($this->tempname,$this->fileTo ."/". $this->filename);
            $ret[$pos]['msg']     = "Stored in: " . $this->destdir . $this->filename;
            $ret[$pos]['fname']   = $this->filename;
            $ret[$pos]['upstat']  = 1;
            $ret[$pos]['ftype']   = $this->filetype;
          }**/
        }
      } else {
        $ret[$pos]['msg']     = "Invalid file";
        $ret[$pos]['fname']   = '';
        $ret[$pos]['upstat']  = 0;
        $ret[$pos]['ftype']   = '';
      }
      return $ret;
    }

    public function procFiles2($comparr,$fileTo) {
      $ciarr    = array();
      $this->fileTo = $fileTo;
      for ($r=0;$r<count($comparr);$r+=1) {
        $this->filename = $comparr[$r][0];
        $this->filetype = $comparr[$r][1];
        $this->tempname = $comparr[$r][2];
        $this->filerror = $comparr[$r][3];
        $this->filesize = $comparr[$r][4];
        $this->curfile  = $this->filename;
        if ($this->filename != "" && $this->filetype != "" && $this->filesize > 0) {
          $ciarr[$r]= $this->doUpload($r);
        }
      }
      if (!empty($ciarr)) {
        foreach ($ciarr as $thisupl) {
          foreach ($thisupl as $thisu) { $this->uplStatus[] = $thisu; }
        }
      } else {
        $ciarr    = array();
      }

      //print_r($ciarr);
      return $this->uplStatus;
    }

    public function procFiles() {
      if (is_array($_FILES["docfile"])) {
        $comparr  = array_map(null,$_FILES['docfile']['name'],$_FILES['docfile']['type'],$_FILES['docfile']['tmp_name'],$_FILES['docfile']['error'],$_FILES['docfile']['size']);
        $ciarr    = array();
        for ($r=0;$r<count($comparr);$r+=1) {
          $this->filename = $comparr[$r][0];
          $this->filetype = $comparr[$r][1];
          $this->tempname = $comparr[$r][2];
          $this->filerror = $comparr[$r][3];
          $this->filesize = $comparr[$r][4];
          $this->curfile  = $this->filename;
          $ciarr[$r]= $this->doUpload($r);
        }
        foreach ($ciarr as $thisupl) {
          foreach ($thisupl as $thisu) { $this->uplStatus[] = $thisu; }
        }
      } else {
        $this->filename = $_FILES['docfile']['name'];
        $this->filetype = $_FILES['docfile']['type'];
        $this->tempname = $_FILES['docfile']['tmp_name'];
        $this->filerror = $_FILES['docfile']['error'];
        $this->filesize = $_FILES['docfile']['size'];
        $this->curfile  = $this->filename;
        $this->uplStatus= $this->doUpload(0);
      }
      return $this->uplStatus;
    }
  }
