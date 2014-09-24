<?php
/**
 * Display image class
 *
 * @version 1.0
 */
class Image {

    /**
     * filename 
     * 
     * @var mixed
     * @access private
     */
	private $filename;
    /**
     * header 
     * 
     * @var string
     * @access private
     */
	private $header = 'Content-type: image/jpeg';
    /**
     * dummy_data 
     * 
     * @var string
     * @access private
     */
	private $dummy_data = 'R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';

	/**
	 * constructor
	 * @access public
	 */
	public function __construct($filename=null){
		$this->filename = $filename;
	}

	/**
	 * Set file name
	 * @access public
	 * @param string $filename image file path and name
	 */
	public function setFilename($filename){
		$this->filename = $filename;
	}

	/**
	 * Display image file with header.
	 * @access public
	 */
	public function get(){
		$this->displayHeader();
		readfile($this->filename);
	}

	/**
	 * get dummy
	 * @access public
	 * @return binary dummy image file (1*1 gif)
	 */
	public function getDummy(){
		$this->displayHeader();
		print base64_decode($this->dummy_data);
	}

	/**
	 * display HTML header for image
	 * @access private
	 */
	public function displayHeader(){
		header($this->header); 
	}

}
