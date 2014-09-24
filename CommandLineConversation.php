<?php
/**
 * CommandLineConversation 
 * 
 * @package 
 * @version $id$
 */
class CommandLineConversation {
    private $question;
    private $answer_max_length = 4096;

    /**
     * __construct 
     * 
     * @param bool $is_mac 
     * @access public
     * @return void
     */
    public function __construct($is_mac=false){
        if($is_mac) ini_set('auto_detect_line_endings', 'on');
    }

    /**
     * set 
     * 
     * @param mixed $label 
     * @param mixed $text 
     * @param mixed $answer_max_length 
     * @access public
     * @return void
     */
    public function set($label, $text, $answer_max_length=null){
        $this->question["$label"]['text'] = $text;
        $this->question["$label"]['answer_length'] =
          ($answer_max_length)? $answer_length : $this->answer_max_length;
        return $this;
    }

    /**
     * get 
     * 
     * @param mixed $label 
     * @access public
     * @return void
     */
    public function get($label=null){
        if(is_null($label)) return $this->question;
        return (array_key_exists($label, $this->question))?
            $this->question["$label"] : null;
    }

    /**
     * getAnswer 
     * 
     * @param mixed $label 
     * @access public
     * @return void
     */
    public function getAnswer($label){
        return $this->question["$label"]['answer'];
    }

    /**
     * ask 
     * 
     * @param mixed $label 
     * @access public
     * @return void
     */
    public function ask($label){
        echo $this->question["$label"]['text']."\n";
        $this->question["$label"]['answer'] =
            $this->input($this->question["$label"]['answer_length']);
        return $this;
    }

    /**
     * input 
     * 
     * @param mixed $length 
     * @access private
     * @return void
     */
    private function input($length=null){
        $input = fgets(STDIN, $length);
        return rtrim($input);
    }
}

