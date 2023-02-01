<?php

/**
 *
 *
 * PHP Version 5
 *
 * @category   
 * @package    Factory proDocReader
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    19.09.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proDocReader.class.php';

class proDocReaderProGes extends proDocReader {

    private $proLib;
    private $praLib;
    private $rowidAnadoc;

    function __construct($rowidAnadoc) {
        $this->setRowidAnadoc($rowidAnadoc);
        $this->proLib = new proLib();
        $this->praLib = new praLib();
    }

    public function getRowidAnadoc() {
        return $this->rowidAnadoc;
    }

    public function setRowidAnadoc($rowidAnadoc) {
        $this->rowidAnadoc = $rowidAnadoc;
    }

    /**
     * GetFilePathSorg
     */
    public function GetFilePathSorg($rowidAnadoc) {
        $Anadoc_rec = $this->proLib->GetAnadoc($rowidAnadoc, 'rowid');
        if (!$Anadoc_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Anadoc non trovato.");
            return false;
        }
        $rowidClasse = $Anadoc_rec['DOCRELCHIAVE'];
        /*
         * Lettura PASDOC
         */
        $pasDoc_rec = $this->praLib->GetPasdoc($rowidClasse, 'ROWID');
        $pramPath = $this->praLib->SetDirectoryPratiche(substr($pasDoc_rec['PASKEY'], 0, 4), $pasDoc_rec['PASKEY'], "PROGES");
        return $pramPath . '/' . $pasDoc_rec['PASFIL'];
    }

   
    /**
     * Restituisce la path del'allegato puntato dal record ANADOC

     * @param type $rowidAnadoc
     * @param type $getBinary
     * @param type $returnBase64
     * @return $forceGetFileServer boolean ** Verrà rimosso una volta completata la procedura di marcature dinamiche.
     */
    public function GetDocPath($rowidAnadoc, $getBinary = false, $returnBase64 = false, $forceGetFileServer = false, $anadocSave = false) {
        if (!$anadocSave) {
            $Anadoc_rec = $this->proLib->GetAnadoc($rowidAnadoc, 'rowid');
        } else {
            $Anadoc_rec = $this->proLib->GetAnadocSave($rowidAnadoc, 'rowid');
        }
        if (!$Anadoc_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura binario. Lettura di Anadoc Fallita.");
            return false;
        }
        if ($getBinary) {
            $binary = $this->GetDocBinary($rowidAnadoc, $returnBase64);
            if ($binary === false) {
                return false;
            }
        }
        $filePathSorg = $this->GetFilePathSorg($Anadoc_rec['ROWID']);
        if (!is_file($filePathSorg) || !is_readable($filePathSorg)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura file. Lettura allegato Fallita.");
            return false;
        }
        return array('DOCPATH' => $filePathSorg, 'DOCNAME' => $Anadoc_rec['DOCNAME'], 'BINARY' => $binary);
    }

    /**
     * Restituisce il binario dato un anadoc
     * 
     * @param type $rowidAnadoc
     * @param type $returnBase64
     * @return boolean
     */
    public function GetDocBinary($rowidAnadoc, $returnBase64 = false) {
        $Anadoc_rec = $this->proLib->GetAnadoc($rowidAnadoc, 'rowid');
        if (!$Anadoc_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura binario. Lettura di Anadoc Fallita.");
            return false;
        }
        $filePathSorg = $this->GetFilePathSorg($rowidAnadoc);

        /* Controllo se occorre copiare il file */
        /* Controllo se deve tornare il base64 del file */
        if ($returnBase64 === true) {
            $base64 = base64_encode(file_get_contents($filePathSorg));
            if (!$base64) {
                $this->setErrCode(-1);
                $this->setErrMessage("Copia Allegato. Errore nella lettura del file binario.");
                return false;
            }
            return $base64;
        }
        return file_get_contents($filePathSorg);
    }

    public function GetHashDocAllegato($rowidAnadoc, $hashType = 'sha256') {
        $Anadoc_rec = $this->proLib->GetAnadoc($rowidAnadoc, 'rowid');
        if (!$Anadoc_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Copia Allegato. Lettura di Anadoc Fallita.");
            return false;
        }
        $hashFile = hash($hashType, $this->GetDocBinary($rowidAnadoc));

        return $hashFile;
    }

    public function OpenDocAllegato($rowidAnadoc, $force_download = false, $utf8decode = false, $headers = true) {
        $DocAllegato = $this->GetDocPath($rowidAnadoc, true);
        if ($DocAllegato === false) {
            return false;
        }
        Out::openDocument(utiDownload::getUrl($DocAllegato['DOCNAME'], $DocAllegato['DOCPATH'], $force_download, $utf8decode, $headers));
        return true;
    }

    /**
     * Funzione per copiare un file allegato del protocollo
     * 
     * @param type $rowidAnadoc
     * @param type $filePathDest
     * @param type $createTemporaryDest
     * @return boolean
     */
    public function CopiaDocAllegato($rowidAnadoc, $filePathDest = '', $createTemporaryDest = false) {
        $Anadoc_rec = $this->proLib->GetAnadoc($rowidAnadoc, 'rowid');
        if (!$Anadoc_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Copia Allegato. Lettura di Anadoc Fallita.");
            return false;
        }
        if ($createTemporaryDest) {
            $subPath = "proDocAllegato-work-" . itaLib::getRandBaseName();
            $tempPath = itaLib::createAppsTempPath($subPath);
            $ext = pathinfo($Anadoc_rec['DOCFIL'], PATHINFO_EXTENSION);
            if ($ext == '') {
                $filePathDest = $tempPath . '/' . $Anadoc_rec['DOCFIL'] . '.' . pathinfo($Anadoc_rec['DOCNAME'], PATHINFO_EXTENSION);
            } else {
                $filePathDest = $tempPath . '/' . $Anadoc_rec['DOCFIL'];
            }
        }
        /*
         * Lettura tramite DocPath
         */
        $DocAllegato = $this->GetDocPath($rowidAnadoc);
        $filePathSorg = $DocAllegato['DOCPATH'];
        /* Controllo se occorre copiare il file */
        if (!@copy($filePathSorg, $filePathDest)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Copia Allegato. Errore durante la copia del file nell'ambiente temporaneo di lavoro.");
            return false;
        }

        return $filePathDest;
    }

}
