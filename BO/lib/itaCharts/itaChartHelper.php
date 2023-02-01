<?php
class itaChartHelper {
    public static function objectRender($obj){
        $return = '';
        if(is_array($obj) || $obj instanceof stdClass){
            $return .= (is_array($obj) ? '[' : '{');

            $start = true;
            foreach($obj as $key=>$value){
                ($start ? $start = false : $return .= ', ');
                
                $return .= (is_int($key) ? '' : $key.':');
                if($value instanceof  itaChartsCustomString){
                    $return .= $value->render();
                }
                elseif(is_array($value) || $value instanceof stdClass){
                    $return .= self::objectRender($value);
                }
                else{
                    $return .= json_encode($value);
                }
            }
            
            $return .= (is_array($obj) ? ']' : '}');
        }
        else{
            $return = json_encode($value);
        }
        
        return $return;
    }
}

class itaChartsCustomString{
    private $data;
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    public function render() {
        return $this->data;
    }
    
    public function __toString() {
        return $this->render();
    }
}
?>
