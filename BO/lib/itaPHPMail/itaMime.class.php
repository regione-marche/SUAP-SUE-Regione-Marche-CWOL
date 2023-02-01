<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itaMime
 *
 * @author michele
 */
require_once (ITA_LIB_PATH . '/QXml/QXml.class.php');
require_once(ITA_LIB_PATH . '/mimeparser/rfc822_addresses.php');
require_once(ITA_LIB_PATH . '/mimeparser/mime_parser.php');

class itaMime {

    public static $lastExitCode;
    public static $lastMessage;

    /**
     * 
     * @param type $mailFile            File in formato eml da decodificare
     * @param type $decode_bodies       1 = Estrae le varie parti del messaggio 0 = Analisi dei soli headers       
     * @param type $saveBody            Path dove salvare le parti estratte dal documento, necessario per grandi messagi e abbinato a $decode_bodies se vale auto la path è automatica
     * @param type $SkipBody            1 = non analizza il body del messaggio, utile quando si esegue una semplice lista
     * @return boolean|array            Ritorna false se ci sono problemi oppure unarray così organizzato:\n
     *                                  ['Message-Id'] = Id del messaggio nel server
     *                                  [Type] = Tipo body principale
     *                                  [Description] = Descrizione del tipo
     *                                  [Encoding] = codifica dei caratteri
     *                                  [DataFile] => Path dove è salvato il body vedi $saveBody
     *                                  [Alternative] => Body alternativo struttura come body 
     *                                  [Attachments] => Array che elenca gli allegati Struttura:
     *                                      [Type] = Tipo file
     *                                      [Description] = descrizione
     *                                      [DataFile] = Path dove è salvato il body vedi $saveBody
     *                                      [FileName] = nome originale del file
     *                                      [FileDisposition] = attachment/inline
     *                                  [Signature] = Array che descrive la firma del messaggio la struttura dell'array è come un'allegato
     *                                  [Subject] = Oggetto
     *                                  [Date] = data messaggio nel formato Wed, 26 Sep 2012 18:05:46 +0200
     *                                  [FromAddress] = indirizzo mail mittente
     *                                  [FromName] = Name esposto del mittente
     *                                  [To] = Array dei destinatari
     *                                  [Return-path] = indirizzo
     *                                  [Reply-to] = indirizzo
     *
     */
    public static function parseMail($mailFile, $decode_bodies = 1, $saveBody = "", $SkipBody = 0) {
        $results = array();
        $mime = new mime_parser_class;
        $mime->mbox = 0;
        $mime->decode_bodies = $decode_bodies;
        $mime->ignore_syntax_errors = 1;
        $mime->track_lines = 1;

        $parameters = array();
        $parameters['File'] = $mailFile;
        $parameters['SkipBody'] = $SkipBody;
        if ($saveBody) {
            $parameters['SaveBody'] = $saveBody;
        }
        if (!$mime->Decode($parameters, $decoded)) {
            self::$lastExitCode = -1;
            self::$lastMessage = 'Decodifica dati mail fallita: ' . $mime->error;
            return false;
        } else {
            $results['Message-Id'] = $decoded[0]['Headers']['message-id:'];
            $results['Message-Id'] = substr($results['Message-Id'], 1, strlen($results['Message-Id']) - 2);
            if ($mime->Analyze($decoded[0], $analyzed)) {
                $results = array_merge($results, $analyzed);
                if (isset($results['Related'])) {
                    if (!isset($results['Attachments'])) {
                        $results['Attachments'] = $results['Related'];
                    } else {
                        foreach ($results['Related'] as $attachmentRelated) {
                            $results['Attachments'][] = $attachmentRelated;
                        }
                    }
                }
                $results['FromAddress'] = $results['From'][0]['address'];
                if (isset($results['From'][0]['name'])) {
                    $results['FromName'] = $results['From'][0]['name'];
                } else {
                    $results['FromName'] = "";
                }
                /*
                 * Ridondanza del corpo come allegato quando file disposition=attachment altrimenti posso perdere eventiali file da elaborare
                 */
                if (strtolower($results['FileDisposition']) == 'attachment') {
                    $results['Attachments'][] = array(
                        'Type' => $results['Type'],
                        'Description' => $results['Description'],
                        'DataFile' => $results['DataFile'],
                        'FileName' => $results['FileName'],
                        'FileDisposition' => $results['FileDisposition'],
                    );

                    /*
                     * Se l'attachment non è visualizzabile svuoto il corpo
                     */
                    if ($results['Type'] != 'message' && $results['Type'] != 'text' && $results['Type'] != 'html') {
                        $retw = file_put_contents($saveBody . '/message', '');
                        if ($retw === false) {
                            self::$lastExitCode = -1;
                            self::$lastMessage = 'Salvataggio body mail fallito.';
                            return false;
                        }
                        $results['DataFile'] = $saveBody . '/message';
                        $results['FileName'] = 'message.html';
                        $results['Type'] = 'text';
                    }
                }

                /*
                 * Se il corpo è html, valido il suo contenuto
                 */
                if ($results['Type'] == 'html') {
                    $htmlMessage = trim(isset($results['DataFile']) ? file_get_contents($results['DataFile']) : $results['Data']);
                    if (strpos($htmlMessage, '<!DOCTYPE') !== 0 && strpos($htmlMessage, '<html') !== 0) {
                        /*
                         * Se non valido, imposto il contenuto come text
                         */
                        $results['Type'] = 'text';
                    }
                }

                if ($results['FileDisposition'] == 'inline') {
                    /*
                     * Controllo il corpo diverso da text o message, allora è un allegato ed aggiunto corpo fittizio vuoto.
                     */
                    if ($results['Type'] != 'message' && $results['Type'] != 'text') {
                        $AttachmentsBody = array();
                        $AttachmentsBody['Type'] = $results['Type'];
                        $AttachmentsBody['Description'] = $results['Description'];
                        $AttachmentsBody['Encoding'] = $results['Encoding'];
                        $AttachmentsBody['DataFile'] = $results['DataFile'];
                        $AttachmentsBody['FileName'] = $results['FileName'];
                        $AttachmentsBody['FileDisposition'] = $results['FileDisposition'];
                        $results['Attachments'][] = $AttachmentsBody;
                        // 
                        $retw = file_put_contents($saveBody . '/message', '');
                        if ($retw === false) {
                            self::$lastExitCode = -1;
                            self::$lastMessage = 'Salvataggio in-line body mail fallito.';
                            return false;
                        }
                        $results['DataFile'] = $saveBody . '/message';
                        $results['FileName'] = 'message.html';
                        $results['Type'] = 'text';
                    }
                }

                /*
                 * Gestione email di notifica di tipo 'delivery-status'.
                 */
                if ($results['Type'] === 'delivery-status') {
                    $dataFile = itaLib::getAppsTempPath() . '/' . basename($mailFile, '.eml') . '-response';
                    $retw = file_put_contents($dataFile, $results['Response']);
                    if ($retw === false || $retw == 0) {
                        self::$lastExitCode = -1;
                        self::$lastMessage = 'Salvataggio delibery status mail fallito.';
                        return false;
                    }
                    $results['DataFile'] = $dataFile;
                }

                return $results;
            } else {
                self::$lastExitCode = -1;
                self::$lastMessage = 'Analisi Contenuto Mail Fallito: ' . $mime->error;
                return false;
            }
        }
    }

}

?>
