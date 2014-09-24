<?php
/**
 * class FileAccessor
 *
 * This class I/O class for file.
 *
 * @version Release: 1.0
 */
class FileAccessor{
    private $filename;
    private $filepath;
    private $contents;
    private $delimiter = '/';

    /**
     * constructor
     * @param $filename target file name include path to file.
     */
    public function __construct($filename=null){
        if($filename) $this->setFilename($filename);
    }

    /**
     * You can set data for write file.
     * If you need append mode, set true to 2nd arg.
     * @param $contents data
     * @param $append   flag for append mode
     */
    public function set($contents, $append=null){
        if($append){
            $this->contents .= $this->arrayToLine($contents);
        }else{
            $this->contents = $this->arrayToLine($contents);
        }
    }

    /**
     * setFilename
     * @param $filename file name
     **/
    public function setFilename($filename){
        $this->filepath = $this->getFilePath($filename);
        $this->filename = $filename;
    }

    /**
     * You can append data.
     * this method is alias of "set(data, true)".
     * @param $contents data
     */
    public function append($contents){
        $this->set($contents, true);
    }

    /**
     * This method returns data from file.
     * @param $array If you need array data from file, set true.
     */
    public function get($array=true){
        $data = file_get_contents($this->filename, FILE_TEXT, null);
        $data = rtrim($data, "\n");
        return ($array)? $this->lineToArray($data) : $data;
    }

    /**
     * This method execute save to file and delete file.
     * @param  $append append flag for write file.
     * @return If success write to file, this method return true.
     */
    public function save($append){
        if($this->is_delete) return unlink($this->filename);
        if(!$this->existDirectory()) $this->makeDirectory();
        $result = ($append)? 
            file_put_contents($this->filename, $this->contents, FILE_APPEND):
            file_put_contents($this->filename, $this->contents);
        $this->delete();
        return $result;
    }

    /**
     * This method readies to delete file.
     */
    public function flush(){
        $this->is_delete = true;
    }

    /**
     * This method splits path and file name from arg.
     * @param  $filename target file name include path to file.
     * @return path to taget file.
     */
  private function getFilePath($filename){
      $path_array = explode($this->delimiter, $filename);
      array_pop($path_array);
      return implode($this->delimiter, $path_array);
  }

    /**
     * This method checkes directory.
     * @return If exist target directory, this method returns true.
     */
  private function existDirectory(){
      return is_dir($this->filepath);
  }

    /**
     * This method create directory.
     */
  private function makeDirectory(){
      return mkdir($this->filepath);
  }

    /**
     * This method delete contents in member variable.
     */
    private function delete(){
        unset($this->contents);
    }

    /**
     * This method converts array to line.
     * @param $array data
     */
    private function arrayToLine(array $array){
        return (is_array($array))? implode("\n", $array) : $array;
    }

    /**
     * This method converts line to array.
     * @param $data data
     */
    private function lineToArray($data){
        return explode("\n", $data);
    }    
}
