<?php
/**
 * Description of ResourceProperty
 *
 * @author michele
 */
class ResourceProperty {
    private $name = "";
    private $value = "";

    private $properties = array();

    /** Creates a new instance of ResourceProperty */
    function __construct($name,$value=null){
        $this->name = $name;
        $this->value = $value;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $this->value = $value;
    }

    public function getProperties() {
        return $this->properties;
    }

    public function setProperties($properties) {
        $this->properties = $properties;
    }

}
?>
