<?php

/**
 * BenchMark{ 
 * 
 * @package 
 * @version 1.0
 * @license 
 */
class BenchMark{

    /**
     * store myself
     * 
     * @var object
     * @access private
     */
    private $singleton;
    /**
     * start time
     * 
     * @var int
     * @access public
     */
    public $start_time;
    /**
     * end time 
     * 
     * @var int
     * @access public
     */
    public $end_time;

    /**
     * Do not use 
     * 
     * @access private
     * @return void
     */
    private function __construct(){
    }

    /**
     * get instance 
     * 
     * @access public
     * @return object
     */
    public function getInstance(){
        if(!is_object(BenchMark::$singleton)){
            BenchMark::$singleton = new BenchMark();
        }
        return BenchMark::$singleton;
    }

    /**
     * start 
     * 
     * @access public
     * @return void
     */
    public function start(){
        $this->start_time = getTime();
    }

    /**
     * end 
     * 
     * @access public
     * @return void
     */
    public function end(){
        $this->end_time = getTime();
    }

    /**
     * get 
     * 
     * @access public
     * @return array results
     */
    public function get(){
        return $this->makeResult();
    }

    /**
     * getMomoryInfo 
     * 
     * @access public
     * @return memory info
     */
    public function getMomoryInfo(){
        return memory_get_usage();
    }

    /**
     * makeResulet 
     * 
     * @access private
     * @return array results
     */
    private function makeResulet(){
        $result[] = $this->start_time;
        $result[] = $this->end_time;
        $result[] = $this->getPeakMemoryInfo();
        $result[] = $this->getMomoryInfo();
        return $result;
    }

    /**
     * getPeakMemoryInfo 
     * 
     * @access private
     * @return memory info
     */
    private function getPeakMemoryInfo(){
        return memory_get_peak_usage();
    }

    /**
     * get time 
     * 
     * @access private
     * @return void
     */
    private function getTime(){
        return microtime(true);
    }
}
