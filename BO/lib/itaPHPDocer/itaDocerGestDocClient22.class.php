<?php

/**
 *
 * Classe per collegamento ws DOCER 22 - Servizio Gestione Documentale
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPDocer
 * @author     
 * @copyright  1987-2017 Italsoft srl
 * @license
 * @version    19.10.2017
 * @link
 * @see
 * @since
 * */
require_once(ITA_LIB_PATH . '/itaPHPDocer/itaDocerClientSuper.class.php');
require_once(ITA_LIB_PATH . '/itaPHPDocer/itaDocerGestDocClientInterface.php');
require_once (ITA_LIB_PATH . '/itaException/ItaException.php');

class itaDocerGestDocClient22 extends itaDocerClientSuper implements itaDocerGestDocClientInterface {

    const PRECONDITION_MISSING_TOKEN = " Parametro token non valorizzato";

    protected function init() {
        $this->namespace = 'http://webservices.docer.kdm.it';
        $this->namespaces = array(
            'web' => 'http://webservices.docer.kdm.it',
            'xop' => 'http://www.w3.org/2004/08/xop/include'
        );
    }

    public function ws_getDocumentTypesByAOO($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['COD_ENTE'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'COD_ENTE' non valorizzato");
        }
        if (!isset($param['COD_AOO'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'COD_AOO' non valorizzato");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $codiceEnteSoapval = new soapval('web:codiceEnte', 'web:codiceEnte', $param['COD_ENTE'], false, false);
        $codiceAOOSoapval = new soapval('web:codiceAOO', 'web:codiceAOO', $param['COD_AOO'], false, false);
        $param = $tokenSoapval->serialize("literal") . $codiceEnteSoapval->serialize("literal") . $codiceAOOSoapval->serialize("literal");
        return $this->ws_call('getDocumentTypesByAOO', $param, 'web:');
    }

    public function ws_getEnte($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['COD_ENTE'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'COD_ENTE' non valorizzato");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $codiceEnteSoapval = new soapval('web:codiceEnte', 'web:codiceEnte', $param['COD_ENTE'], false, false);
        $param = $tokenSoapval->serialize("literal") . $codiceEnteSoapval->serialize("literal");
        return $this->ws_call('getEnte', $param, 'web:');
    }

    public function ws_createEnte($param) {

        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['ENTEINFO']) || !is_array($param['ENTEINFO'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'ENTEINFO' non valorizzato correttamente");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);

        foreach ($param['ENTEINFO'] as $enteInfo) {
            $groupOptionsSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $enteInfo['key'], false, false),
                new soapval("xsd:value", "xsd:value", $enteInfo['value'], false, false)
            );
            $enteInfoSoapval = new soapval('web:enteInfo', 'web:enteInfo', $groupOptionsSoapvalArr, false, false);
            $enti[] = $enteInfoSoapval;
        }

        $param = $tokenSoapval->serialize("literal");
        foreach ($enti as $ente) {
            $param .= $ente->serialize("literal");
        }

        return $this->ws_call('createEnte', $param, 'web:');
    }

    public function ws_updateEnte($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['COD_ENTE'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'COD_ENTE' non valorizzato correttamente");
        }
        if (!isset($param['ENTEINFO']) || !is_array($param['ENTEINFO'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'ENTEINFO' non valorizzato correttamente");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $enteId = new soapval('web:codiceEnte', 'web:codiceEnte', $param['ENTEINFO'], false, false);

        foreach ($param['ENTEINFO'] as $enteInfo) {
            $enteOptionsSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $enteInfo['key'], false, false),
                new soapval("xsd:value", "xsd:value", $enteInfo['value'], false, false)
            );
            $enteInfoSoapval = new soapval('web:enteInfo', 'web:enteInfo', $enteOptionsSoapvalArr, false, false);
            $enti[] = $enteInfoSoapval;
        }

        $param = $tokenSoapval->serialize("literal");
        $param .= $enteId->serialize("literal");
        foreach ($enti as $ente) {
            $param .= $ente->serialize("literal");
        }

        return $this->ws_call('updateEnte', $param, 'web:');
    }

    public function ws_deleteEnte($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['COD_ENTE'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'COD_ENTE' non valorizzato correttamente");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $enteId = new soapval('web:codiceEnte', 'web:codiceEnte', $param['COD_ENTE'], false, false);

        $enteOptionsSoapvalArr = array(
            new soapval("xsd:key", "xsd:key", "ENABLED", false, false),
            new soapval("xsd:value", "xsd:value", 0, false, false)
        );
        $entiInfoSoapval = new soapval('web:enteInfo', 'web:enteInfo', $enteOptionsSoapvalArr, false, false);
        $enti[] = $entiInfoSoapval;

        $param = $tokenSoapval->serialize("literal");
        $param .= $enteId->serialize("literal");
        foreach ($enti as $ente) {
            $param .= $ente->serialize("literal");
        }

        return $this->ws_call('updateEnte', $param, 'web:');
    }

    public function ws_getAOO($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['AOOID']) || !is_array($param['AOOID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'AOOID' non valorizzato correttamente");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);

        $aooId = array();
        foreach ($param['AOOID'] as $id) {
            $aooIdSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $id['key'], false, false),
                new soapval("xsd:value", "xsd:value", $id['value'], false, false)
            );
            $aooIdSoapval = new soapval('web:aooId', 'web:aooId', $aooIdSoapvalArr, false, false);
            $aooId[] = $aooIdSoapval;
        }

        $param = $tokenSoapval->serialize("literal");
        foreach ($aooId as $id) {
            $param .= $id->serialize("literal");
        }

        return $this->ws_call('getAOO', $param, 'web:');
    }

    public function ws_createGroup($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['GROUPINFO']) || !is_array($param['GROUPINFO'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'GROUPINFO' non valorizzato correttamente");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);

        $groups = array();
        foreach ($param['GROUPINFO'] as $groupInfo) {
            $groupOptionsSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $groupInfo['key'], false, false),
                new soapval("xsd:value", "xsd:value", $groupInfo['value'], false, false)
            );
            $groupInfoSoapval = new soapval('web:groupInfo', 'web:groupInfo', $groupOptionsSoapvalArr, false, false);
            $groups[] = $groupInfoSoapval;
        }

        $param = $tokenSoapval->serialize("literal");
        foreach ($groups as $group) {
            $param .= $group->serialize("literal");
        }

        return $this->ws_call('createGroup', $param, 'web:');
    }

    public function ws_getGroup($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['GROUPID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'GROUPID' non valorizzato correttamente");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $groupId = new soapval('web:groupId', 'web:groupId', $param['GROUPID'], false, false);

        $param = $tokenSoapval->serialize("literal");
        $param .= $groupId->serialize("literal");

        return $this->ws_call('getGroup', $param, 'web:');
    }

    public function ws_updateGroup($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['GROUPID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'GROUPID' non valorizzato correttamente");
        }
        if (!isset($param['GROUPINFO']) || !is_array($param['GROUPINFO'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'GROUPINFO' non valorizzato correttamente");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $groupId = new soapval('web:groupId', 'web:groupId', $param['GROUPID'], false, false);

        foreach ($param['GROUPINFO'] as $groupInfo) {
            $groupOptionsSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $groupInfo['key'], false, false),
                new soapval("xsd:value", "xsd:value", $groupInfo['value'], false, false)
            );
            $groupInfoSoapval = new soapval('web:groupInfo', 'web:groupInfo', $groupOptionsSoapvalArr, false, false);
            $groups[] = $groupInfoSoapval;
        }

        $param = $tokenSoapval->serialize("literal");
        $param .= $groupId->serialize("literal");
        foreach ($groups as $group) {
            $param .= $group->serialize("literal");
        }

        return $this->ws_call('updateGroup', $param, 'web:');
    }

    public function ws_deleteGroup($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['GROUPID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'GROUPID' non valorizzato correttamente");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $groupId = new soapval('web:groupId', 'web:groupId', $param['GROUPID'], false, false);

        $userOptionsSoapvalArr = array(
            new soapval("xsd:key", "xsd:key", "ENABLED", false, false),
            new soapval("xsd:value", "xsd:value", 0, false, false)
        );
        $groupInfoSoapval = new soapval('web:groupInfo', 'web:groupInfo', $userOptionsSoapvalArr, false, false);
        $groups[] = $groupInfoSoapval;

        $param = $tokenSoapval->serialize("literal");
        $param .= $groupId->serialize("literal");
        foreach ($groups as $group) {
            $param .= $group->serialize("literal");
        }

        return $this->ws_call('updateGroup', $param, 'web:');
    }

    public function ws_createUser($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['USERINFO']) || !is_array($param['USERINFO'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'USERINFO' non valorizzato correttamente");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);

        $users = array();
        foreach ($param['USERINFO'] as $userInfo) {
            $userOptionsSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $userInfo['key'], false, false),
                new soapval("xsd:value", "xsd:value", $userInfo['value'], false, false)
            );
            $userInfoSoapval = new soapval('web:userInfo', 'web:userInfo', $userOptionsSoapvalArr, false, false);
            $users[] = $userInfoSoapval;
        }

        $param = $tokenSoapval->serialize("literal");
        foreach ($users as $user) {
            $param .= $user->serialize("literal");
        }

        return $this->ws_call('createUser', $param, 'web:');
    }

    public function ws_getUser($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['USERID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'USERID' non valorizzato correttamente");
        }
        $this->clearAttachments();
		
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $userId = new soapval('web:userId', 'web:userId', $param['USERID'], false, false);
        $param = $tokenSoapval->serialize("literal");
        $param .= $userId->serialize("literal");

        return $this->ws_call('getUser', $param, 'web:');
    }

    public function ws_updateUser($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['USERID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'USERID' non valorizzato correttamente");
        }
        if (!isset($param['USERINFO']) || !is_array($param['USERINFO'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'USERINFO' non valorizzato correttamente");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $userId = new soapval('web:userId', 'web:userId', $param['USERID'], false, false);

        foreach ($param['USERINFO'] as $userInfo) {
            $userOptionsSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $userInfo['key'], false, false),
                new soapval("xsd:value", "xsd:value", $userInfo['value'], false, false)
            );
            $userInfoSoapval = new soapval('web:userInfo', 'web:userInfo', $userOptionsSoapvalArr, false, false);
            $users[] = $userInfoSoapval;
        }

        $param = $tokenSoapval->serialize("literal");
        $param .= $userId->serialize("literal");
        foreach ($users as $user) {
            $param .= $user->serialize("literal");
        }

        return $this->ws_call('updateUser', $param, 'web:');
    }

    public function ws_deleteUser($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['USERID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'USERID' non valorizzato correttamente");
        }
        $this->clearAttachments();
		
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $userId = new soapval('web:userId', 'web:userId', $param['USERID'], false, false);

        $userOptionsSoapvalArr = array(
            new soapval("xsd:key", "xsd:key", "ENABLED", false, false),
            new soapval("xsd:value", "xsd:value", 0, false, false)
        );
        $userInfoSoapval = new soapval('web:userInfo', 'web:userInfo', $userOptionsSoapvalArr, false, false);
        $users[] = $userInfoSoapval;

        $param = $tokenSoapval->serialize("literal");
        $param .= $userId->serialize("literal");
        foreach ($users as $user) {
            $param .= $user->serialize("literal");
        }

        return $this->ws_call('updateUser', $param, 'web:');
    }

    public function ws_setUsersOfGroup($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['GROUPID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'GROUPID' non valorizzato correttamente");
        }
        if (!isset($param['USERS']) || !is_array($param['USERS'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'USERS' non valorizzato correttamente");
        }
        $this->clearAttachments();
        
        $users = $param['USERS'];

        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $groupIdSoapval = new soapval('web:groupId', 'web:groupId', $param['GROUPID'], false, false);
        $users = array();
        foreach ($users as $user) {
            //Verifica l'esistenza di un utente 
            $testIfExist = $this->ws_getUser(array("TOKEN" => $param['TOKEN'], "USERID" => $user));
            if ($testIfExist && $this->getResult()) {
                $userSoapval = new soapval('web:users', 'web:users', $user, false, false);
                $users[] = $userSoapval;
            }
        }

        $param = $tokenSoapval->serialize("literal") . $groupIdSoapval->serialize("literal");
        foreach ($users as $user) {
            $param .= $user->serialize("literal");
        }

        return $this->ws_call('setUsersOfGroup', $param, 'web:');
    }
    
    public function ws_setGroupsOfUser($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['USERID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'USERID' non valorizzato correttamente");
        }
        if (!isset($param['GROUPS']) || !is_array($param['GROUPS'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'GROUPS' non valorizzato correttamente");
        }
        $groups = $param['GROUPS'];

        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $userIdSoapval = new soapval('web:userId', 'web:userId', $param['USERID'], false, false);
        $groups = array();
        foreach ($groups as $group) {
            //Verifica l'esistenza di un gruppo 
            $testIfExist = $this->ws_getGroup(array("TOKEN" => $param['TOKEN'], "GROUPID" => $group));
            if ($testIfExist && $this->getResult()) {
                $groupSoapval = new soapval('web:groups', 'web:groups', $group, false, false);
                $groups[] = $groupSoapval;
            }
        }

        $param = $tokenSoapval->serialize("literal") . $userIdSoapval->serialize("literal");
        foreach ($groups as $group) {
            $param .= $group->serialize("literal");
        }

        return $this->ws_call('setGroupsOfUser', $param, 'web:');        
    }
    
    public function ws_getUsersOfGroup($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['GROUPID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'GROUPID' non valorizzato correttamente");
        }
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $groupId = new soapval('web:groupId', 'web:groupId', $param['GROUPID'], false, false);

        $param = $tokenSoapval->serialize("literal");
        $param .= $groupId->serialize("literal");

        return $this->ws_call('getUsersOfGroup', $param, 'web:');
    }
    
    public function ws_getGroupsOfUser($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['USERID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'USERID' non valorizzato correttamente");
        }
        $this->clearAttachments();
		
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $userId = new soapval('web:userId', 'web:userId', $param['USERID'], false, false);

        $param = $tokenSoapval->serialize("literal");
        $param .= $userId->serialize("literal");

        return $this->ws_call('getGroupsOfUser', $param, 'web:');
    }
    
    public function ws_updateUsersOfGroup($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['GROUPID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'GROUPID' non valorizzato correttamente");
        }
        if (!isset($param['USERTOADD']) || !isset($param['USERTOREMOVE'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Almeno uno dei parametri 'USERTOADD' e 'USERTOREMOVE' deve essere valorizzato");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $groupIdSoap = new soapval('web:groupId', 'web:groupId', $param['GROUPID'], false, false);
        $usersToAdd = array();
        foreach ($param['USERSTOADD'] as $userToAdd) {
            //Verifica l'esistenza di un utente da aggiungere 
            $testIfExist = $this->ws_getUser(array("TOKEN" => $param['TOKEN'], "USERID" => $userToAdd));

            if ($testIfExist && $this->getResult()) {
                $userToAddSoapval = new soapval('web:usersToAdd', 'web:usersToAdd', $userToAdd, false, false);
                $usersToAdd[] = $userToAddSoapval;
            }
        }
        $usersToRemove = array();
        foreach ($param['USESRTOREMOVE'] as $userToRemove) {

            $testIfExist = $this->ws_getUser(array("TOKEN" => $param['TOKEN'], "USERID" => $userToRemove));
            if ($testIfExist && $this->getResult()) {
                $userToRemoveSoapval = new soapval('web:usersToRemove', 'web:usersToRemove', $userToRemove, false, false);
                $usersToRemove[] = $userToRemoveSoapval;
            }
        }

        $param = $tokenSoapval->serialize("literal");
        $param .= $groupIdSoap->serialize("literal");

        foreach ($usersToAdd as $userToAdd) {
            $param .= $userToAdd->serialize("literal");
        }
        foreach ($usersToRemove as $userToRemove) {
            $param .= $userToRemove->serialize("literal");
        }
        return $this->ws_call('updateUsersOfGroup', $param, 'web:');
    }
    
    public function ws_updateGroupsOfUser($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['USERID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'GROUPID' non valorizzato correttamente");
        }
        if (!isset($param['GROUPSTOADD']) || !isset($param['GROUPSTOREMOVE'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Almeno uno dei parametri 'GROUPSTOADD' e 'GROUPSTOREMOVE' deve essere valorizzato");
        }
        $this->clearAttachments();
		
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $userIdSoap = new soapval('web:userId', 'web:userId', $param['USERID'], false, false);
        $groupsToAdd = array();
        foreach ($param['GROUPSTOADD'] as $groupToAdd) {
            //Verifica l'esistenza di un gruppo da aggiungere 
            $testIfExist = $this->ws_getGroup(array("TOKEN" => $param['TOKEN'], "GROUPID" => $groupToAdd));

            if ($testIfExist && $this->getResult()) {
                $groupToAddSoapval = new soapval('web:groupsToAdd', 'web:groupsToAdd', $groupToAdd, false, false);
                $groupsToAdd[] = $groupToAddSoapval;
            }
        }
        $groupsToRemove = array();
        foreach ($param['GROUPSTOREMOVE'] as $groupToRemove) {

            $testIfExist = $this->ws_getGroup(array("TOKEN" => $param['TOKEN'], "GROUPID" => $groupToRemove));
            if ($testIfExist && $this->getResult()) {
                $groupToRemoveSoapval = new soapval('web:groupsToRemove', 'web:groupsToRemove', $groupToRemove, false, false);
                $groupsToRemove[] = $groupToRemoveSoapval;
            }
        }

        $param = $tokenSoapval->serialize("literal");
        $param .= $userIdSoap->serialize("literal");

        foreach ($groupsToAdd as $groupToAdd) {
            $param .= $groupToAdd->serialize("literal");
        }
        foreach ($groupsToRemove as $groupToRemove) {
            $param .= $groupToRemove->serialize("literal");
        }
        return $this->ws_call('updateGroupsOfUser', $param, 'web:');
    }
    
    public function ws_createDocument($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['FILE'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'FILE' non valorizzato correttamente");
        }
        if (!isset($param['METADATA']) || !is_array($param['METADATA'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'METADATA' non valorizzato correttamente");
        }

        $cid = md5(uniqid(time()));
        $this->clearAttachments();
        $this->addAttachment(array(
            'data' => null, // Se passo null, legge il contenuto del file automaticamente
            'filename' => $param['FILE'],
            'contenttype' => 'application/octet-stream',
            'cid' => $cid
        ));

        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $fileSoapvalXopInclude = new soapval('xop:Include', 'xop:Include', '', false, false, array('href' => "cid:$cid"));
        $fileSoapval = new soapval('web:file', 'web:file', $fileSoapvalXopInclude, false, false);

        $metadata = array();
        foreach ($param['METADATA'] as $meta) {
            $metaSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $meta['key'], false, false),
                new soapval("xsd:value", "xsd:value", $meta['value'], false, false)
            );
            $metaSoapval = new soapval('web:metadata', 'web:metadata', $metaSoapvalArr, false, false);
            $metadata[] = $metaSoapval;
        }

        $param = $tokenSoapval->serialize("literal");
        foreach ($metadata as $meta) {
            $param .= $meta->serialize("literal");
        }
        $param .= $fileSoapval->serialize("literal");

        return $this->ws_call('createDocument', $param, 'web:');
    }

    public function ws_updateProfileDocument($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['DOCID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'DOCID' non valorizzato correttamente");
        }
        if (!isset($param['METADATA']) || !is_array($param['METADATA'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'METADATA' non valorizzato correttamente");
        }
        $this->clearAttachments();
        

        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $docIdSoapval = new soapval('web:docId', 'web:docId', $param['DOCID'], false, false);

        $metadata = array();
        foreach ($param['METADATA'] as $meta) {
            $metaSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $meta['key'], false, false),
                new soapval("xsd:value", "xsd:value", $meta['value'], false, false)
            );
            $metaSoapval = new soapval('web:metadata', 'web:metadata', $metaSoapvalArr, false, false);
            $metadata[] = $metaSoapval;
        }

        $param = $tokenSoapval->serialize("literal") . $docIdSoapval->serialize("literal");
        foreach ($metadata as $meta) {
            $param .= $meta->serialize("literal");
        }

        return $this->ws_call('updateProfileDocument', $param, 'web:');
    }
    
    public function ws_getProfileDocument($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['DOCID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'DOCID' non valorizzato correttamente");
        }
        $this->clearAttachments();

        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $docIdSoapval = new soapval('web:docId', 'web:docId', $param['DOCID'], false, false);
        
        $param = $tokenSoapval->serialize("literal") . $docIdSoapval->serialize("literal");

        return $this->ws_call('getProfileDocument', $param, 'web:');
    }
    
    public function ws_searchDocuments($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['MAXROWS'])) {
            $param['MAXROWS'] = -1;
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $maxRowSoapval = new soapval('web:maxRows', 'web:maxRows', $param['MAXROWS'], false, false);

        $searchCriteria = array();
        foreach ($param['SEARCHCRITERIA'] as $criteria) {
            $searchCriteriaSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $criteria['key'], false, false),
                new soapval("xsd:value", "xsd:value", $criteria['value'], false, false)
            );
            $searchCriteriaSoapval = new soapval('web:searchCriteria', 'web:searchCriteria', $searchCriteriaSoapvalArr, false, false);
            $searchCriteria[] = $searchCriteriaSoapval;
        }

        $param = $tokenSoapval->serialize("literal");
        foreach ($searchCriteria as $criteria) {
            $param .= $criteria->serialize("literal");
        }
        $param .= $maxRowSoapval->serialize("literal");

        return $this->ws_call('searchDocuments', $param, 'web:');
    }

    public function ws_searchAnagrafiche($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $typeSoapval = new soapval('web:type', 'web:type', $param['TYPE'], false, false);

        $searchCriteria = array();
        foreach ($param['SEARCHCRITERIA'] as $criteria) {
            $searchCriteriaSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $criteria['key'], false, false),
                new soapval("xsd:value", "xsd:value", $criteria['value'], false, false)
            );
            $searchCriteriaSoapval = new soapval('web:searchCriteria', 'web:searchCriteria', $searchCriteriaSoapvalArr, false, false);
            $searchCriteria[] = $searchCriteriaSoapval;
        }

        $param = $tokenSoapval->serialize("literal") . $typeSoapval->serialize("literal");
        foreach ($searchCriteria as $criteria) {
            $param .= $criteria->serialize("literal");
        }

        return $this->ws_call('searchAnagrafiche', $param, 'web:');
    }

    public function ws_setACLDocument($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['DOCID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'DOCID' non valorizzato correttamente");
        }
        if (!isset($param['ACLS']) || !is_array($param['ACLS'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'ACLS' non valorizzato correttamente");
        }
        $this->clearAttachments();
        

        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $docId = new soapval('web:docId', 'web:docId', $param['DOCID'], false, false);

        $permission = array();
        foreach ($param['ACLS'] as $acl) {
            $searchAclsSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $acl['key'], false, false),
                new soapval("xsd:value", "xsd:value", $acl['value'], false, false)
            );
            $aclsSoapval = new soapval('web:acls', 'web:acls', $searchAclsSoapvalArr, false, false);
            $permissions[] = $aclsSoapval;
        }

        $param = $tokenSoapval->serialize("literal") . $docId->serialize("literal");

        foreach ($permissions as $permission) {
            $param .= $permission->serialize("literal");
        }
        return $this->ws_call('setACLDocument', $param, 'web:');
    }

    public function ws_getACLDocument($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['DOCID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'DOCID' non valorizzato correttamente");
        }
        $this->clearAttachments();
		
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $docId = new soapval('web:docId', 'web:docId', $param['DOCID'], false, false);

        $param = $tokenSoapval->serialize("literal") . $docId->serialize("literal");

        return $this->ws_call('getACLDocument', $param, 'web:');
    }

    public function ws_createFascicolo($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['FASCICOLOINFO']) || !is_array($param['FASCICOLOINFO'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'FASCICOLOINFO' non valorizzato correttamente");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);

        $fascicoli = array();
        foreach ($param['FASCICOLOINFO'] as $fascicoloInfo) {
            $fascicoloOptionsSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $fascicoloInfo['key'], false, false),
                new soapval("xsd:value", "xsd:value", $fascicoloInfo['value'], false, false)
            );
            $fascicoloInfoSoapval = new soapval('web:fascicoloInfo', 'web:fascicoloInfo', $fascicoloOptionsSoapvalArr, false, false);
            $fascicoli[] = $fascicoloInfoSoapval;
        }

        $param = $tokenSoapval->serialize("literal");
        foreach ($fascicoli as $fascicolo) {
            $param .= $fascicolo->serialize("literal");
        }

        return $this->ws_call('createFascicolo', $param, 'web:');
    }

    public function ws_getFascicolo($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['FASCICOLOID']) || !is_array($param['FASCICOLOID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'FASCICOLOID' non valorizzato correttamente");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);

        $fascicoli = array();
        foreach ($param['FASCICOLOID'] as $fascicolo) {
            $fascicoloIdSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $fascicolo['key'], false, false),
                new soapval("xsd:value", "xsd:value", $fascicolo['value'], false, false)
            );
            $fascicoloIdSoapval = new soapval('web:fascicoloId', 'web:fascicoloId', $fascicoloIdSoapvalArr, false, false);
            $fascicoli[] = $fascicoloIdSoapval;
        }

        $param = $tokenSoapval->serialize("literal");
        foreach ($fascicoli as $fascicolo) {
            $param .= $fascicolo->serialize("literal");
        }

        return $this->ws_call('getFascicolo', $param, 'web:');
    }

    public function ws_updateFascicolo($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['FASCICOLOID']) || !is_array($param['FASCICOLOID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'FASCICOLOID' non valorizzato correttamente");
        }
        if (!isset($param['FASCICOLOINFO']) || !is_array($param['FASCICOLOINFO'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'FASCICOLOINFO' non valorizzato correttamente");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);

        foreach ($param['FASCICOLOID'] as $fascicoloId) {
            $fascicoloIdOptionsSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $fascicoloId['key'], false, false),
                new soapval("xsd:value", "xsd:value", $fascicoloId['value'], false, false)
            );
            $fascicoloIdSoapval = new soapval('web:fascicoloId', 'web:fascicoloId', $fascicoloIdOptionsSoapvalArr, false, false);
            $fascicoliId[] = $fascicoloIdSoapval;
        }

        foreach ($param['FASCICOLOINFO'] as $fascicoloInfo) {
            $fascicoloInfoOptionsSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $fascicoloInfo['key'], false, false),
                new soapval("xsd:value", "xsd:value", $fascicoloInfo['value'], false, false)
            );
            $fascicoloInfoSoapval = new soapval('web:fascicoloInfo', 'web:fascicoloInfo', $fascicoloInfoOptionsSoapvalArr, false, false);
            $fascicoliInfo[] = $fascicoloInfoSoapval;
        }

        $param = $tokenSoapval->serialize("literal");


        foreach ($fascicoliId as $fascicoloId) {
            $param .= $fascicoloId->serialize("literal");
        }


        foreach ($fascicoliInfo as $fascicoloInfo) {
            $param .= $fascicoloInfo->serialize("literal");
        }

        return $this->ws_call('updateFascicolo', $param, 'web:');
    }

    public function ws_deleteFascicolo($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['FASCICOLOID']) || !is_array($param['FASCICOLOID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'FASCICOLOID' non valorizzato correttamente");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);

        foreach ($param['FASCICOLOID'] as $fascicoloId) {
            $fascicoloIdOptionsSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $fascicoloId['key'], false, false),
                new soapval("xsd:value", "xsd:value", $fascicoloId['value'], false, false)
            );
            $fascicoloIdSoapval = new soapval('web:fascicoloId', 'web:fascicoloId', $fascicoloIdOptionsSoapvalArr, false, false);
            $fascicoliId[] = $fascicoloIdSoapval;
        }

        $fascicoloInfoOptionsSoapvalArr = array(
            new soapval("xsd:key", "xsd:key", "ENABLED", false, false),
            new soapval("xsd:value", "xsd:value", 0, false, false)
        );
        $fascicoloInfoSoapval = new soapval('web:fascicoloInfo', 'web:fascicoloInfo', $fascicoloInfoOptionsSoapvalArr, false, false);
        $fascicoliinfo[] = $fascicoloInfoSoapval;

        $param = $tokenSoapval->serialize("literal");

        foreach ($fascicoliId as $fascicoloId) {
            $param .= $fascicoloId->serialize("literal");
        }

        foreach ($fascicoliinfo as $fascicoloinfo) {
            $param .= $fascicoloinfo->serialize("literal");
        }

        return $this->ws_call('updateFascicolo', $param, 'web:');
    }

    public function ws_getACLFascicolo($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['FASCICOLOID']) || !is_array($param['FASCICOLOID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'FASCICOLOID' non valorizzato correttamente");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        foreach ($param['FASCICOLOID'] as $fascicoloId) {
            $fascicoloIdOptionsSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $fascicoloId['key'], false, false),
                new soapval("xsd:value", "xsd:value", $fascicoloId['value'], false, false)
            );
            $fascicoloIdSoapval = new soapval('web:fascicoloId', 'web:fascicoloId', $fascicoloIdOptionsSoapvalArr, false, false);
            $fascicoliId[] = $fascicoloIdSoapval;
        }
        $param = $tokenSoapval->serialize("literal");
        foreach ($fascicoliId as $fascicoloId) {
            $param .= $fascicoloId->serialize("literal");
        }
        return $this->ws_call('getACLFscicolo', $param, 'web:');
    }

    public function ws_setACLFascicolo($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['FASCICOLOID']) || !is_array($param['FASCICOLOID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'FASCICOLOID' non valorizzato correttamente");
        }
        if (!isset($param['ACLS']) || !is_array($param['ACLS'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'ACLS' non valorizzato correttamente");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);

        foreach ($param['FASCICOLOID'] as $fascicoloId) {
            $fascicoloIdOptionsSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $fascicoloId['key'], false, false),
                new soapval("xsd:value", "xsd:value", $fascicoloId['value'], false, false)
            );
            $fascicoloIdSoapval = new soapval('web:fascicoloId', 'web:fascicoloId', $fascicoloIdOptionsSoapvalArr, false, false);
            $fascicoliId[] = $fascicoloIdSoapval;
        }

        $permission = array();
        foreach ($param['ACLS'] as $acl) {
            $searchAclsSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $acl['key'], false, false),
                new soapval("xsd:value", "xsd:value", $acl['value'], false, false)
            );
            $aclsSoapval = new soapval('web:acls', 'web:acls', $searchAclsSoapvalArr, false, false);
            $permissions[] = $aclsSoapval;
        }

        $param = $tokenSoapval->serialize("literal");

        foreach ($fascicoliId as $fascicoloId) {
            $param .= $fascicoloId->serialize("literal");
        }

        foreach ($permissions as $permission) {
            $param .= $permission->serialize("literal");
        }
        return $this->ws_call('setACLFacsicolo', $param, 'web:');
    }

    public function ws_protocollaDocumento($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['DOCID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'DOCID' non valorizzato correttamente");
        }

        if (!isset($param['METADATA']) || !is_array($param['METADATA'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'METADATA' non valorizzato correttamente");
        }
        $this->clearAttachments();
        

        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $docId = new soapval('web:docId', 'web:docId', $param['DOCID'], false, false);


        foreach ($param['METADATA'] as $metadata) {
            $searchMetadataSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $metadata['key'], false, false),
                new soapval("xsd:value", "xsd:value", $metadata['value'], false, false)
            );
            $metadatiSoapval = new soapval('web:metadata', 'web:metadata', $searchMetadataSoapvalArr, false, false);
            $meta[] = $metadatiSoapval;
        }

        $param = $tokenSoapval->serialize("literal") . $docId->serialize("literal");

        foreach ($meta as $i) {
            $param .= $i->serialize("literal");
        }
        return $this->ws_call('protocollaDocumento', $param, 'web:');
    }

    public function ws_createTitolario($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['TITOLARIOINFO']) || !is_array($param['TITOLARIOINFO'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'TITOLARIOINFO' non valorizzato correttamente");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);

        $titolari = array();
        foreach ($param['TITOLARIOINFO'] as $titolarioInfo) {
            $groupOptionsSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $titolarioInfo['key'], false, false),
                new soapval("xsd:value", "xsd:value", $titolarioInfo['value'], false, false)
            );
            $titolarioInfoSoapval = new soapval('web:titolarioInfo', 'web:titolarioInfo', $groupOptionsSoapvalArr, false, false);
            $titolari[] = $titolarioInfoSoapval;
        }

        $param = $tokenSoapval->serialize("literal");
        foreach ($titolari as $titolario) {
            $param .= $titolario->serialize("literal");
        }

        return $this->ws_call('createTitolario', $param, 'web:');
    }

    public function ws_getTitolario($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['TITOLARIOID']) || !is_array($param['TITOLARIOID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'TITOLARIOID' non valorizzato correttamente");
        }
        $this->clearAttachments();
        

        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $titolari = array();
        foreach ($param['TITOLARIOID'] as $titolario) {
            $titolarioIdSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $titolario['key'], false, false),
                new soapval("xsd:value", "xsd:value", $titolario['value'], false, false)
            );
            $titolarioIdSoapval = new soapval('web:titolarioId', 'web:titolarioId', $titolarioIdSoapvalArr, false, false);
            $titolari[] = $titolarioIdSoapval;
        }

        $param = $tokenSoapval->serialize("literal");
        foreach ($titolari as $titolario) {
            $param .= $titolario->serialize("literal");
        }

        return $this->ws_call('getTitolario', $param, 'web:');
    }

    public function ws_udpateTitolario($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['TITOLARIOID']) || !is_array($param['TITOLARIOID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'TITOLARIOID' non valorizzato correttamente");
        }
        if (!isset($param['TITOLARIOINFO']) || !is_array($param['TITOLARIOINFO'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'TITOLARIOINFO' non valorizzato correttamente");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);

        foreach ($param['TITOLARIOID'] as $titolarioId) {
            $fascicoloIdOptionsSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $titolarioId['key'], false, false),
                new soapval("xsd:value", "xsd:value", $titolarioId['value'], false, false)
            );
            $titolarioIdSoapval = new soapval('web:titolarioId', 'web:titolarioId', $fascicoloIdOptionsSoapvalArr, false, false);
            $titolariId[] = $titolarioIdSoapval;
        }

        foreach ($param['TITOLARIINFO'] as $titolariInfo) {
            $fascicoloInfoOptionsSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $titolariInfo['key'], false, false),
                new soapval("xsd:value", "xsd:value", $titolariInfo['value'], false, false)
            );
            $titolariInfoSoapval = new soapval('web:titolarioInfo', 'web:titolarioInfo', $fascicoloInfoOptionsSoapvalArr, false, false);
            $titolariInfo[] = $titolariInfoSoapval;
        }

        $param = $tokenSoapval->serialize("literal");

        foreach ($titolariId as $titolarioId) {
            $param .= $titolarioId->serialize("literal");
        }

        foreach ($titolariInfo as $titolarioInfo) {
            $param .= $titolarioInfo->serialize("literal");
        }

        return $this->ws_call('updateTitolario', $param, 'web:');
    }

    public function ws_deleteTitolario($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['TITOLARIOID']) || !is_array($param['TITOLARIOID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'TITOLARIOID' non valorizzato correttamente");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);

        foreach ($param['TITOLARIOID'] as $titolarioId) {
            $fascicoloIdOptionsSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $titolarioId['key'], false, false),
                new soapval("xsd:value", "xsd:value", $titolarioId['value'], false, false)
            );
            $titolarioIdSoapval = new soapval('web:titolarioId', 'web:titolarioId', $fascicoloIdOptionsSoapvalArr, false, false);
            $titolariId[] = $titolarioIdSoapval;
        }

        $titolarioInfoOptionsSoapvalArr = array(
            new soapval("xsd:key", "xsd:key", "ENABLED", false, false),
            new soapval("xsd:value", "xsd:value", 0, false, false)
        );
        $titolariInfoSoapval = new soapval('web:titolarioInfo', 'web:titolarioInfo', $titolarioInfoOptionsSoapvalArr, false, false);
        $titolariInfo[] = $titolariInfoSoapval;

        $param = $tokenSoapval->serialize("literal");

        foreach ($titolariId as $titolarioId) {
            $param .= $titolarioId->serialize("literal");
        }

        foreach ($titolariInfo as $titolarioInfo) {
            $param .= $titolarioInfo->serialize("literal");
        }

        return $this->ws_call('updateTitolario', $param, 'web:');
    }

    public function ws_fascicolaDocumento($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['DOCID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'DOCID' non valorizzato correttamente");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $docIdSoapval = new soapval('web:docId', 'web:docId', $param['DOCID'], false, false);

        $metadata = array();
        foreach ($param['METADATA'] as $meta) {
            $metaSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $meta['key'], false, false),
                new soapval("xsd:value", "xsd:value", $meta['value'], false, false)
            );
            $metaSoapval = new soapval('web:metadata', 'web:metadata', $metaSoapvalArr, false, false);
            $metadata[] = $metaSoapval;
        }

        $param = $tokenSoapval->serialize("literal") . $docIdSoapval->serialize("literal");
        foreach ($metadata as $meta) {
            $param .= $meta->serialize("literal");
        }

        return $this->ws_call('fascicolaDocumento', $param, 'web:');
    }

    public function ws_registraDocumento($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['DOCID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'DOCID' non valorizzato correttamente");
        }
        $this->clearAttachments();
        
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $docIdSoapval = new soapval('web:docId', 'web:docId', $param['DOCID'], false, false);

        $metadata = array();
        foreach ($param['METADATA'] as $meta) {
            $metaSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $meta['key'], false, false),
                new soapval("xsd:value", "xsd:value", $meta['value'], false, false)
            );
            $metaSoapval = new soapval('web:metadata', 'web:metadata', $metaSoapvalArr, false, false);
            $metadata[] = $metaSoapval;
        }

        $param = $tokenSoapval->serialize("literal") . $docIdSoapval->serialize("literal");
        foreach ($metadata as $meta) {
            $param .= $meta->serialize("literal");
        }
        
        return $this->ws_call('registraDocumento', $param, 'web:');
    }


    public function ws_downloadDocument($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['DOCID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'DOCID' non valorizzato correttamente");
        }
        $this->clearAttachments();

        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $docIdSoapval = new soapval('web:docId', 'web:docId', $param['DOCID'], false, false);
        
        $param = $tokenSoapval->serialize("literal") . $docIdSoapval->serialize("literal");
        return $this->ws_call('downloadDocument', $param, 'web:');
    }

    public function ws_createAnagraficaCustom($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }     
        $this->clearAttachments();
		
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);        

        $customInfo = array();
        foreach ($param['CUSTOMINFO'] as $meta) {
            $metaSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $meta['key'], false, false),
                new soapval("xsd:value", "xsd:value", $meta['value'], false, false)
            );
            $metaSoapval = new soapval('web:customInfo', 'web:customInfo', $metaSoapvalArr, false, false);
            $customInfo[] = $metaSoapval;
        }

        $param = $tokenSoapval->serialize("literal");
        foreach ($customInfo as $meta) {
            $param .= $meta->serialize("literal");
        }

        return $this->ws_call('createAnagraficaCustom', $param, 'web:');
    }

    public function ws_updateAnagraficaCustom($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        $this->clearAttachments();
		
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);        

        // customId
        $customId = array();
        foreach ($param['CUSTOMID'] as $meta) {
            $metaSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $meta['key'], false, false),
                new soapval("xsd:value", "xsd:value", $meta['value'], false, false)
            );
            $metaSoapval = new soapval('web:customId', 'web:customId', $metaSoapvalArr, false, false);
            $customId[] = $metaSoapval;
        }
        
        // customInfo
        $customInfo = array();
        foreach ($param['CUSTOMINFO'] as $meta) {
            $metaSoapvalArr = array(
                new soapval("xsd:key", "xsd:key", $meta['key'], false, false),
                new soapval("xsd:value", "xsd:value", $meta['value'], false, false)
            );
            $metaSoapval = new soapval('web:customInfo', 'web:customInfo', $metaSoapvalArr, false, false);
            $customInfo[] = $metaSoapval;
        }

        $param = $tokenSoapval->serialize("literal");
        foreach ($customId as $meta) {
            $param .= $meta->serialize("literal");
        }
        foreach ($customInfo as $meta) {
            $param .= $meta->serialize("literal");
        }

        return $this->ws_call('updateAnagraficaCustom', $param, 'web:');
    }

    public function ws_addRelated($param) {
        if (!isset($param['TOKEN'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + self::PRECONDITION_MISSING_TOKEN);
        }
        if (!isset($param['DOCID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, __FUNCTION__ + "Parametro 'DOCID' non valorizzato correttamente");
        }
        $this->clearAttachments();
		
        $tokenSoapval = new soapval('web:token', 'web:token', $param['TOKEN'], false, false);
        $docIdSoapval = new soapval('web:docId', 'web:docId', $param['DOCID'], false, false);

        $related = array();
        foreach ($param['RELATED'] as $rel) {            
            $relatedSoapval = new soapval('web:related', 'web:related', $rel, false, false);
            $related[] = $relatedSoapval;
        }

        $param = $tokenSoapval->serialize("literal") . $docIdSoapval->serialize("literal");
        foreach ($related as $rel) {
            $param .= $rel->serialize("literal");
        }

        return $this->ws_call('addRelated', $param, 'web:');
    }
}

?>
