<?php
/**
 * class MinimumTemplate
 *
 * This class is just replace tag to value.
 * 
 * @version Release: 1.0
 */

class MinimumTemplate {
    private $values;
    private $template;
    private $left_delimiter = '<{';
    private $right_delimiter = '}>';

    /**
     * constructor
     * @access public
     * @param $template template
     * @param $values values for replace tag
     */
    public function __construct($template=null, $values=null){
        if($template) $this->setTemplate($template);
        if($values) $this->setValues($values);
    }

    /**
     * You can set values in array for replace template tags.
     * @access public
     * @param $values set values by array
     */
    public function setValues(array $values){
        $this->values = $values;
    }

    /**
     * You can set value by scalar
     * @access public
     * @param $key Your target template tag's name
     * @param $val tag of key name will replace by this value.
     */
    public function setValue($key, $val){
        $this->values[$key] = $val;
    }

    /**
     * You can set template
     * @access public
     * @param $template template
     */
    public function setTemplate($template){
        $this->template = $template;
    }

    /**
     * This method execute to display contents
     * @access public
     */ 
    public function display(){
        print $this->createContents();
    }

    /**
     * This method execute to create contents
     * @access private
     * @return Replaced contents
     */
    private function createContents(){
        if($this->values) $this->replaceTemplate();
        return $this->deleteTags();
    }

    /**
     * This method execute to replace tags to values
     * @access private
     */
    private function replaceTemplate(){
        $this->template = strtr($this->template, $this->makeTags($this->values));
    }

    /**
     * This method execute make tag for replace.
     * @access private
     * @param  $values template tag's key
     * @return Template tag
     */
    private function makeTags(array $values){
        foreach($values as $key => $val){
            $result[$this->left_delimiter.'$'.$key.$this->right_delimiter] = $val;
        }
        return $result;
    }

    /**
     * This method delete unnecessary template tags.
     * @access private
     * @return Template without unnecessary template tags.
     */
    private function deleteTags(){
        $tag = '/'.$this->left_delimiter.'(.+?)'.$this->right_delimiter.'/';
        return preg_replace($tag, '', $this->template);
    }
}
