<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class cwbLibPagoPaUtils{
    static function valorized(&$value){
        return isSet($value) && (trim($value) != '' || (is_array($value) && !empty($value)));
    }
}