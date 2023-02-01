<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itareqProtocolloArrivo
 *
 * @author Mario Mazza <mario.mazza@italsoft.eu>
 */
class itaARSSTrasforms {

    const TYPE_CANONICAL_WITH_COMMENT = 'CANONICAL_WITH_COMMENT';
    const TYPE_CANONICAL_OMIT_COMMENT = 'CANONICAL_OMIT_COMMENT';
    const TYPE_BASE64 = 'TYPE_BASE64';
    const TYPE_XPATH2_INTERSECT = 'TYPE_XPATH2_INTERSECT';
    const TYPE_XPATH2_SUBTRACT = 'TYPE_XPATH2_SUBTRACT';
    const TYPE_XPATH2_UNION = 'TYPE_XPATH2_UNION';
    const TYPE_XSLT = 'TYPE_XSLT';
    
    public $type;
    public $value;

    function getType() {
        return $this->type;
    }

    function getValue() {
        return $this->value;
    }

    function setType($type) {
        $this->type = $type;
    }

    function setValue($value) {
        $this->value = $value;
    }


}
