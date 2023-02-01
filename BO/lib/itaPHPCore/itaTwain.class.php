<?php

/**
 *
 * Classe gestione parametri twain
 *
 *  * PHP Version 5
 *
 * @category   CORE
 * @package    itaPHPCore
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>* 
 * @copyright  1987-2013 Italsoft snc
 * @license 
 * @version    10.09.2013
 * @link
 * @see
 * @since
 * @deprecated
 **/

class itaTwain {

    private $capsArray = array();
    private $deviceToDriverArray = array(
        "MicroREI" => "microrei",
        "K2s full features" => "twainendorser",
        "Generic" => "twaingeneric"
    );
    private $driverType;

    /* Generic Constants */

    const TWON_ARRAY = 3;
    const TWON_ENUMERATION = 4;
    const TWON_ONEVALUE = 5;
    const TWON_RANGE = 6;
    const TWON_ICONID = 962;
    const TWON_DSMID = 461;
    const TWON_DSMCODEID = 63;
    const TWON_DONTCARE8 = '0xff';
    const TWON_DONTCARE16 = '0xffff';
    const TWON_DONTCARE32 = '0xffffffff';

    /* Flags  used in TW_MEMORY structure. */
    const TWMF_APPOWNS = '0x0001';
    const TWMF_DSMOWNS = '0x0002';
    const TWMF_DSOWNS = '0x0004';
    const TWMF_POINTER = '0x0008';
    const TWMF_HANDLE = '0x0010';
    const TWTY_INT8 = '0x0000';
    const TWTY_INT16 = '0x0001';
    const TWTY_INT32 = '0x0002';
    const TWTY_UINT8 = '0x0003';
    const TWTY_UINT16 = '0x0004';
    const TWTY_UINT32 = '0x0005';
    const TWTY_BOOL = '0x0006';
    const TWTY_FIX32 = '0x0007';
    const TWTY_FRAME = '0x0008';
    const TWTY_STR32 = '0x0009';
    const TWTY_STR64 = '0x000a';
    const TWTY_STR128 = '0x000b';
    const TWTY_STR255 = '0x000c';
    const TWTY_HANDLE = '0x000f';


    /* Capability Constants */

    /* STANDARD TWAIN CAPS */
    const CAP_PRINTER = '0x1026';
    const CAP_PRINTERENABLED = '0x1027';
    const CAP_PRINTERMODE = '0x1029';
    const CAP_PRINTERSTRING = '0x102a';
    const CAP_FEEDERORDER = '0x102e';

    /* MICROREI TWAIN CUSTOM CAPS */
    const CAP_MICROREI_PRINTERSTRING = '0x8002';
    const CAP_MICROREI_PRINTERPROTID = '0x8001';
    const CAP_MICROREI_PRINTERPOSITION = '0x800a';
    const CAP_MICROREI_PRINTERDENSITY = '0X8009';


    /* CAPS VALUES Constants */

    /* CAP_PRINTERMODE values */
    const TWPM_SINGLESTRING = 0;
    const TWPM_MULTISTRING = 1;
    const TWPM_COMPOUNDSTRING = 2;

    /* CAP_PRINTER values */
    const TWPR_IMPRINTERTOPBEFORE = 0;
    const TWPR_IMPRINTERTOPAFTER = 1;
    const TWPR_IMPRINTERBOTTOMBEFORE = 2;
    const TWPR_IMPRINTERBOTTOMAFTER = 3;
    const TWPR_ENDORSERTOPBEFORE = 4;
    const TWPR_ENDORSERTOPAFTER = 5;
    const TWPR_ENDORSERBOTTOMBEFORE = 6;
    const TWPR_ENDORSERBOTTOMAFTER = 7;

    /* CAP_FEEDERORDER values */
    const TWFO_FIRSTPAGEFIRST = 0;
    const TWFO_LASTPAGEFIRST = 1;


    /* BOOLEAN values */
    const VALUE_TRUE = 1;
    CONST VALUE_FALSE = 0;

    function __construct($device) {
        $this->driverType = $this->getDriverTypeFromDevice($device);
        $this->resetCapsArray();
    }

    public function getDriverType() {
        return $this->driverType;
    }

    public function getCapsArray() {
        return $this->capsArray;
    }

    public function resetCapsArray() {
        $this->capsArray = array();
    }

    private function getDriverTypeFromDevice($device) {
        if (isset($this->deviceToDriverArray[$device])) {
            return $this->deviceToDriverArray[$device];
        } else {
            return $this->deviceToDriverArray['Generic'];
        }
    }

    public function setCap($arrayParams) {
        $this->capsArray[] = $arrayParams;
    }

    public function setCapPrinter($dataValue) {
        $this->capsArray[] =
                array(
                    'capability' => self::CAP_PRINTER,
                    'valuetype' => self::TWTY_UINT16,
                    'datatype' => self::TWON_ONEVALUE,
                    'containertype' => self::VALUE_FALSE,
                    'datavalue' => $dataValue
        );
    }

    public function setCapPrinterEnabled($dataValue = self::VALUE_FALSE) {
        $this->capsArray[] =
                array(
                    'capability' => self::CAP_PRINTERENABLED,
                    'valuetype' => self::TWTY_BOOL,
                    'datatype' => self::TWON_ONEVALUE,
                    'containertype' => self::VALUE_FALSE,
                    'datavalue' => $dataValue
        );
    }

    public function setCapPrinterMode($dataValue = self::TWPM_SINGLESTRING) {
        $this->capsArray[] =
                array(
                    'capability' => self::CAP_PRINTERMODE,
                    'valuetype' => self::TWTY_UINT16,
                    'datatype' => self::TWON_ONEVALUE,
                    'containertype' => self::VALUE_FALSE,
                    'datavalue' => $dataValue
        );
    }

    public function setCapPrinterString($dataValue = '') {
        if ($this->driverType == "microrei") {
            $capability = self::CAP_MICROREI_PRINTERSTRING;
            $dataValue = "\t" . $dataValue;
        } else {
            $capability = self::CAP_PRINTERSTRING;
        }
        $this->capsArray[] =
                array(
                    'capability' => $capability,
                    'valuetype' => self::TWTY_STR255,
                    'datatype' => self::TWON_ONEVALUE,
                    'containertype' => self::VALUE_TRUE,
                    'datavalue' => $dataValue
        );
    }

}

?>
