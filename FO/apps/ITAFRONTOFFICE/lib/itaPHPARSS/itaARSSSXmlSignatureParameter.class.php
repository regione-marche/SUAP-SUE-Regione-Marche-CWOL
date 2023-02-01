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
class itaARSSXmlSignatureParameter {

    const TYPE_XMLENVELOPED = 'XMLENVELOPED';
    const TYPE_XMLENVELOPING = 'XMLENVELOPING';
    const TYPE_XMLDETACHED_INTERNAL = 'XMLDETACHED_INTERNAL';
    const CANONICALIZEDTYPE_ALGO_ID_C14N11_OMIT_COMMENTS = 'ALGO_ID_C14N11_OMIT_COMMENTS';
    const CANONICALIZEDTYPE_ALGO_ID_C14N11_WITH_COMMENTS = 'ALGO_ID_C14N11_WITH_COMMENTS';

    public $type;
    public $transforms;
    public $canonicalizedType;

    function getType() {
        return $this->type;
    }

    function getTransforms() {
        return $this->transforms;
    }

    function getCanonicalizedType() {
        return $this->canonicalizedType;
    }

    function setType($type) {
        $this->type = $type;
    }

    function setTransforms($transforms) {
        $this->transforms = $transforms;
    }

    function setCanonicalizedType($canonicalizedType) {
        $this->canonicalizedType = $canonicalizedType;
    }


}
