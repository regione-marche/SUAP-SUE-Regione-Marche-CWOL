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
class itaARSSPdfSignApparence {

    private $image;
    private $imageBin;
    private $imageOnly;
    private $leftx;
    private $lefty;
    private $location;
    private $page;
    private $reason;
    private $rightx;
    private $righty;
    private $testo;
    private $bScaleFont;
    private $bShowDateTime;
    private $resizeMode;
    private $preservePDFA;

    function getImage() {
        return $this->image;
    }

    function getImageBin() {
        return $this->imageBin;
    }

    function getImageOnly() {
        return $this->imageOnly;
    }

    function getLeftx() {
        return $this->leftx;
    }

    function getLefty() {
        return $this->lefty;
    }

    function getLocation() {
        return $this->location;
    }

    function getPage() {
        return $this->page;
    }

    function getReason() {
        return $this->reason;
    }

    function getRightx() {
        return $this->rightx;
    }

    function getRighty() {
        return $this->righty;
    }

    function getTesto() {
        return $this->testo;
    }

    function getBScaleFont() {
        return $this->bScaleFont;
    }

    function getBShowDateTime() {
        return $this->bShowDateTime;
    }

    function getResizeMode() {
        return $this->resizeMode;
    }

    function setImage($image) {
        $this->image = $image;
    }

    function setImageBin($imageBin) {
        $this->imageBin = $imageBin;
    }

    function setImageOnly($imageOnly) {
        $this->imageOnly = $imageOnly;
    }

    function setLeftx($leftx) {
        $this->leftx = $leftx;
    }

    function setLefty($lefty) {
        $this->lefty = $lefty;
    }

    function setLocation($location) {
        $this->location = $location;
    }

    function setPage($page) {
        $this->page = $page;
    }

    function setReason($reason) {
        $this->reason = $reason;
    }

    function setRightx($rightx) {
        $this->rightx = $rightx;
    }

    function setRighty($righty) {
        $this->righty = $righty;
    }

    function setTesto($testo) {
        $this->testo = $testo;
    }

    function setBScaleFont($bScaleFont) {
        $this->bScaleFont = $bScaleFont;
    }

    function setBShowDateTime($bShowDateTime) {
        $this->bShowDateTime = $bShowDateTime;
    }

    function setResizeMode($resizeMode) {
        $this->resizeMode = $resizeMode;
    }

}
