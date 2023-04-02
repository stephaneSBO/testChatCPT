<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */



use Jeemate\Action\JeeAction;
use jeemate\Action\JeeScenario;
use jeemate\API\APIGeoLoc;
use jeemate\API\APIParam;

use jeemate\API\APIOther;
use jeemate\API\APIEdit;
use jeemate\API\APIFile;
use jeemate\API\APIJeedom;
use jeemate\API\APISensor;
use jeemate\API\APIInfo;

use jeemate\API\APIUtils;
use jeemate\Core\CorePhone;
use jeemate\Core\CoreLog;

require_once dirname(__FILE__) . '/../../core/php/jeemate.inc.php';

$time_start = microtime(true);

if (user::isBan()) {
        header("Status: 404 Not Found");
        header('HTTP/1.0 404 Not Found');
        $_SERVER['REDIRECT_STATUS'] = 404;
        echo "<h1>404 Not Found</h1>";
        echo "The page that you have requested could not be found.";
        die();
}

http_response_code(200);
header('Content-Type: application/json');

$content = file_get_contents('php://input');
$API = new APIUtils($content);

if (!$API->access()) {
        $result = array(
                "code" => "11",
                "error" => "APIKEY",
        );
        echo json_encode($API->sendReponse($result));
        CoreLog::add('api', 'debug', "--------------------- Fin Requête : " .  $API->method . ", Exécution : ----------------------------------------------");
        die;
}

$API->setData('APIStartTime', $time_start);

$result = FALSE;
try {
        switch ($API->method) {
                        // Set Function
                case 'setObject':
                        $result = APIEdit::setObject($API);
                        break;
                case 'setObjectImage':
                        $result = APIEdit::setObjectImage($API);
                        break;
                case 'setName':
                        $result = APIEdit::setName($API);
                        break;
                case 'setSensors':
                        $result = APISensor::setAllSensor($API);
                        break;
                case 'setGeofences':
                        $by = 'app';
                        $geofences = $API->param(APIParam::$DATA);
                        $result = APIGeoLoc::setGeofence($geofences, $by);
                        break;
                case 'updateStats':
                        $result = APIOther::updateStats($API);
                        break;
                case 'pluginListPlugin':
                        $result = APIJeedom::pluginListPlugin($API);
                        break;
                case 'updatePhone':
                        $result = CorePhone::phoneFromAPI($API);
                        break;
                case 'eventCMD':
                        $result = APIJeedom::eventCMD($API);
                        break;
                case 'setMediaNotifAutomation':
                        $result = JeeScenario::mediaNotifListener($API);
                        break;
                case 'setEqlogicConfiguration':
                        $result = APIOther::setEqlogicConfiguration($API);
                        break;
                case 'setDisplayConfiguration':
                        $result = APIOther::setDisplayConfiguration($API);
                        break;
                case 'setBatteryTime':
                        $result = APIOther::setBatteryTime($API);
                        break;

                        // Get Function
                case 'initAppService':
                        APISensor::setAllSensor($API);
                        APIOther::updateStats($API);
                        $result = APIOther::initAppService($API);
                        break;
                case 'getinstall':
                        $result = APIOther::getInstall($API);
                        break;
                case 'getStates':
                        $result = APIOther::getStates($API);
                        break;
                case 'getSummaryDef':
                        $result = APIOther::getSummaryDef();
                        break;
                        /*              case 'getRights':
                        if ($API->checkIsSet(APIParam::$HASH)) {
                                $result = APIOther::getRights($API);
                        }
                        else {
                                $API->setError(5, "A parameter is missing in the request");
                        }
                        break;*/
                case 'getDesign':
                        $result = APIOther::getDesign();
                        break;
                case 'getView':
                        $result = APIOther::getView();
                        break;
                case 'getAllVariable':
                        $result = APIFile::getAllVariable();
                        break;
                case 'getBackup':
                        $result = APIFile::getBackup($API);
                        break;
                case 'getAllBackup':
                        $result = APIFile::getAllBackup($API);
                        break;
                case 'deleteBackup':
                        $backupID = $API->param(APIParam::$BACKUPID);
                        $result = APIFile::removeBackup($backupID);
                        break;
                case 'setGlobalBackup':
                        $result = APIFile::setGlobalBackup($API);
                        break;
                case 'getObjects':
                        $result = APIOther::getObjects();
                        break;
                case 'getAllEqLogicByType':
                        $result = APIOther::getAllEqLogicByType($API);
                        break;
                case 'eqLogicbyType':
                        $result = APIJeedom::eqLogicbyType($API);
                        break;
                case 'eqLogicSave':
                        $result = APIJeedom::eqLogicSave($API);
                        break;
                case 'cmdbyEqLogicId':
                        $result = APIJeedom::cmdbyEqLogicId($API);
                        break;
                case 'cmdGetHistory':
                        $result = APIJeedom::cmdGetHistory($API);
                        break;
                case 'eqLogicFullById':
                        $result = APIJeedom::eqLogicFullById($API);
                        break;
                case 'getTimeline':
                        $result = APIJeedom::getTimeLine();
                        break;
                case 'deleteTimeline':
                        $result = APIJeedom::deleteTimeline($API);
                        break;
                case 'createJeedomBackup':
                        $result = APIJeedom::createJeedomBackup();
                        break;
                case 'geoloc':
                        CoreLog::add('APIGeoLoc', 'info', "[" . $API->eqLogicID . '] Event GeoAPP  : ' . json_encode($API->inputJson));
                        $result = APIGeoLoc::dispatchEvent($API);
                        break;
                case 'getTools':
                        $result = APIInfo::getInfo($API);
                        break;
                case 'pluginConfig':
                        $result = APIJeedom::getPluginConfig($API);
                        break;
                case 'fullEqLogicsByRoomID':
                        $result = APIOther::getAllEqLogicByRoomID($API);
                        break;
                case 'getCameraSnapshot':
                        $result = APIOther::getCameraSnapshot($API);
                        break;
                case 'getAdGuardStats':
                        $result = APIOther::getAdGuardStats($API);
                        break;
                case 'getImages':
                        $result = APIFile::getImages();
                        break;
                case 'delMultiImages':
                        $result = APIFile::delMultiImages($API);
                        break;
                case 'getGeoFence':
                        $result = APIGeoLoc::getAllGeofence();
                        break;
                case 'removeGeoFence':
                        $zonesID = $API->param(APIParam::$ZONEID);
                        $result = APIGeoLoc::removeGeoFence($zonesID);
                        break;
                case 'jeemateInverted':
                        $result = APIJeedom::jeemateInverted($API);
                        break;
                        // Autres Functions
                case 'healthCounters':
                        APIOther::cleanAsk();
                        $result = APIJeedom::healthCounters();
                        break;
                case 'updateJeedomPlugins':
                        $result = APIJeedom::updateJeedomPlugins($API);
                        break;
                case 'logList':
                        $result = log::liste();
                        break;
                case 'logListByName':
                        $result = APIJeedom::logListByName($API);
                        break;
                case 'clearAllMessages':
                        $result = message::removeAll();
                        break;
                case 'clearMessage':
                        $result = APIJeedom::clearMessage($API);
                        break;
                case 'createCmd':
                        $result = APIJeedom::createCmd($API);
                        break;
                case 'deleteCmd':
                        $result = APIJeedom::deleteCmd($API);
                        break;
                case 'clearAllLogs':
                        $result = log::removeAll();
                        break;
                case 'clearLog':
                        $result = APIJeedom::removeLog($API);
                        break;
                case 'deamonStop':
                        $result = APIJeedom::stopDeamon($API);
                        break;
                case 'deamonStart':
                        $result = APIJeedom::startDeamon($API);
                        break;
                case 'changeAutoMode':
                        $result = APIJeedom::changeAutoMode($API);
                        break;
                case 'dependancyInstall':
                        $result = APIJeedom::dependancyInstall($API);
                        break;
                case 'dependancyInstallTime':
                        $result = APIJeedom::dependancyInstallTime($API);
                        break;
                case 'deleteScene':
                        $result = APIEdit::deleteScene($API);
                        break;
                case 'market':
                        $result = config::byKey("market::username");
                        break;
                case 'marketAccount':
                        $result = APIOther::marketAccount($API);
                        break;
                case 'askReply':
                        $result = APIOther::askReply($API);
                        break;
                case 'isOK':
                        $result = APIOther::isOK();
                        CoreLog::add('APIOther', 'debug', "isOK :" . $result);
                        break;
                case 'version':
                        $result = APIOther::version();
                        break;
                case 'execMultiCmd':
                        $Action = new JeeAction($API);
                        $result = $Action->execMultiCmd($API);
                        break;
                case 'execCmd':
                        $Action = new JeeAction($API);
                        $result = $Action->execCmd($API);
                        break;
                case 'scenarioImport':
                        $result = APIJeedom::scenarioImport($API);
                        break;
                case 'scenarioChangeState':
                        $result = APIJeedom::changeState($API);
                        break;
                case 'tryToReply':
                        $result = APIJeedom::tryToReply($API);
                        break;
                case 'zwavejsStartInclusion':
                        $result = APIOther::zwavejsStartInclusion($API);
                        break;
                case 'zwavejsStartSecuredInclusionS0':
                        $result = APIOther::zwavejsStartSecuredInclusionS0($API);
                        break;
                case 'zwavejsStartSecuredInclusionS2':
                        $result = APIOther::zwavejsStartSecuredInclusionS2($API);
                        break;
                case 'zwavejsStartExclusion':
                        $result = APIOther::zwavejsStartExclusion($API);
                        break;
                case 'zwavejsStopInclusion':
                        $result = APIOther::zwavejsStopInclusion($API);
                        break;
                case 'zwavejsStopExclusion':
                        $result = APIOther::zwavejsStopExclusion($API);
                        break;
                case 'zwavejsGetInclusionState':
                        $result = APIOther::zwavejsGetInclusionState($API);
                        break;
                case 'zwavejsRefreshNeighbors':
                        $result = APIOther::zwavejsRefreshNeighbors($API);
                        break;
                case 'zwavejsBeginHealingNetwork':
                        $result = APIOther::zwavejsBeginHealingNetwork($API);
                        break;
                case 'zwavejsStopHealingNetwork':
                        $result = APIOther::zwavejsStopHealingNetwork($API);
                        break;
                case 'zwavejsNamingAction':
                        $result = APIOther::zwavejsNamingAction($API);
                        break;
                case 'zwavejsSoftReset':
                        $result = APIOther::zwavejsSoftReset($API);
                        break;
                case 'zwavejsSynchroniser':
                        $result = APIOther::zwavejsSynchroniser($API);
                        break;
                case 'zwavejsGetNodes':
                        CoreLog::add('api', 'info', 'zwavejsGetNodes::Enter');
                        if ($API->checkIsSet(APIParam::$DEAMONAUTOMODE)) {
                                $result = APIOther::zwavejsGetNodes($API);
                        } else {
                                CoreLog::add('api', 'error', 'zwavejsGetNodes::error');
                                $API->setError(11, "zwavejsGetNodes : A parameter is missing in the request");
                        }
                        break;
                case 'zwavejsGetFile':
                        CoreLog::add('api', 'info', 'zwavejsGetFile::Enter');
                        if ($API->checkIsSet(APIParam::$TYPE) && $API->checkIsSet(APIParam::$NODE)) {
                                CoreLog::add('api', 'info', 'zwavejsGetFile::call APIOther');
                                $result = APIOther::zwavejsGetFile($API);
                        } else {
                                CoreLog::add('api', 'error', 'zwavejsGetFile::error');
                                $API->setError(12, "zwavejsGetFile : A parameter is missing in the request");
                        }
                        break;
                case 'zwavejsGetAll':
                        $result = APIOther::zwavejsGetAll($API);
                        break;
                case 'zwavejsGetHealth': // HTML Health page
                        $result = APIOther::zwavejsGetHealth($API);
                        break;
                case 'zwavejsGetMobileHealth': // mobile HTML Health page
                        $result = APIOther::zwavejsGetMobileHealth($API);
                        break;
                case 'zwavejsGetNodeInfos': // Node information
                        if ($API->checkIsSet(APIParam::$ID) || $API->checkIsSet(APIParam::$LOGICALID)) {
                                $result = APIOther::zwavejsGetNodeInfos($API);
                        } else {
                                $API->setError(13, "zwavejsGetNodeInfos : A parameter is missing in the request");
                        }
                        break;
                case 'zwavejsGetNodeValues': // Node values
                        if ($API->checkIsSet(APIParam::$NODE)) {
                                $result = APIOther::zwavejsGetNodeValues($API);
                        } else {
                                $API->setError(14, "zwavejsGetNodeValues : A parameter is missing in the request");
                        }
                        break;
                case 'zwavejsGetInfos':
                        $result = APIOther::zwavejsGetInfos($API);
                        break;
                case 'zwavejsGetNodeInterview':
                        if ($API->checkIsSet(APIParam::$ID) || $API->checkIsSet(APIParam::$LOGICALID)) {
                                $result = APIOther::zwavejsGetNodeInterview($API);
                        } else {
                                $API->setError(15, "zwavejsGetNodeValues : A parameter is missing in the request");
                        }
                        break;
                case 'zwavejsPing':
                        if ($API->checkIsSet(APIParam::$ID) || $API->checkIsSet(APIParam::$LOGICALID)) {
                                $result = APIOther::zwavejsPing($API);
                        } else {
                                $API->setError(16, "zwavejsPing : A parameter is missing in the request");
                        }
                        break;
                case 'pairingEditEqlogic':
                        $result = APIOther::pairingEditEqlogic($API);
                        break;
                case 'zigbee2mqttGetAllEquipements':
                        $result = APIOther::zigbee2mqttGetAllEquipements($API);
                        break;
                case 'zigbee2mqttGetAllEquipementsForInclusion':
                        $result = APIOther::zigbee2mqttGetAllEquipementsForInclusion($API);
                        break;
                case 'zigbee2mqttStartInclusion':
                        if ($API->checkIsSet(APIParam::$LOGICALID) || $API->checkIsSet(APIParam::$TIMER)) {
                                $result = APIOther::zigbee2mqttStartInclusion($API);
                        }
                        break;
                case 'zigbee2mqttStopInclusion':
                        $result = APIOther::zigbee2mqttStopInclusion($API);
                        break;
                case 'zigbee2mqttGetInclusionState':
                        $result = APIOther::zigbee2mqttGetInclusionState($API);
                        break;
                case 'zigbee2mqttSynchronise':
                        $result = APIOther::zigbee2mqttSynchronise($API);
                        break;
                case 'zigbee2mqttSynchroniseCommands':
                        $result = APIOther::zigbee2mqttSynchroniseAndOrderCommands($API);
                        break;
                case 'deconzGetGateways':
                        $result = APIOther::deconzGetGateways();
                        break;
                case 'deconzGetAllInfos':
                        $result = APIOther::deconzGetAllInfos();
                        break;
                case 'deconzGetAllLights':
                        $result = APIOther::deconzGetAllLights();
                        break;
                case 'deconzGetAllSensors':
                        $result = APIOther::deconzGetAllSensors();
                        break;
                case 'deconzGetAllEquipements':
                        $result = APIOther::deconzGetAllEquipements();
                        break;
                case 'deconzGetAllGroups':
                        $result = APIOther::deconzGetAllGroups();
                        break;
                case 'deconzGetNeighbours':
                        $result = APIOther::deconzGetNeighbours($API);
                        break;
                case 'deconzGetFullState':
                        $result = APIOther::deconzGetFullState($API);
                        break;
                case 'deconzStartInclusion':
                        $result = APIOther::deconzStartInclusion($API);
                        break;
                case 'deconzStopInclusion':
                        $result = APIOther::deconzStopInclusion($API);
                        break;
                case 'zigbeeGetGateways':
                        $result = APIOther::zigbeeGetGateways();
                        break;
                case 'zigbeeGetAllEquipements':
                        $result = APIOther::zigbeeGetAllEquipements();
                        break;
                case 'zigbeeStartInclusion':
                        $result = APIOther::zigbeeStartInclusion($API);
                        break;
                case 'zigbeeNetworkMap':
                        $result = APIOther::zigbeeNetworkMap($API);
                        break;
                case 'zigbeeGroupAll':
                        $result = APIOther::zigbeeGroupAll($API);
                        break;
                case 'googlecastStartInclusion':
                        $result = APIOther::googlecastStartInclusion($API);
                        break;
                case 'googlecastStopInclusion':
                        $result = APIOther::googlecastStopInclusion($API);
                        break;
                case 'bleaStartInclusion':
                        $result = APIOther::bleaStartInclusion($API);
                        break;
                case 'bleaStopInclusion':
                        $result = APIOther::bleaStopInclusion($API);
                        break;
                case 'bleaGetAllTypes':
                        $result = APIOther::bleaGetAllTypes($API);
                        break;
                case 'bleaGetAllowAllInclusion':
                        $result = APIOther::bleaGetAllowAllInclusion($API);
                        break;
                case 'bleaSetAllowAllInclusion':
                        $result = APIOther::bleaSetAllowAllInclusion($API);
                        break;
                case 'bleaDeleteUnknownDevices':
                        $result = APIOther::bleaDeleteUnknownDevices($API);
                        break;
                case 'rfxcomStartInclusion':
                        $result = APIOther::rfxcomStartInclusion($API);
                        break;
                case 'rfxcomStopInclusion':
                        $result = APIOther::rfxcomStopInclusion($API);
                        break;
                case 'aTVremoteStartInclusion':
                        $result = APIOther::aTVremoteStartInclusion($API);
                        break;

                case 'consoById':
                        $result = APIOther::consoById($API);
                        break;
                        // get IPS conf from data/ips_configuration.json
                case 'getIPSConfiguration':
                        $result = APIOther::getIPSConfiguration($API);
                        break;
                        // store IPS conf in data/ips_configuration.json
                        // if IPS microservice is running it will automatically refresh conf
                case 'setIPSConfiguration':
                        $result = APIOther::setIPSConfiguration($API);
                        break;
                        // set command info "Room Location"
                case 'setIPSRoomLocation':
                        $result = APIOther::setIPSRoomLocation($API);
                        break;
                        // set command info "Closest edge" (aka anchor/beacon)
                case 'setIPSClosestEdge':
                        $result = APIOther::setIPSClosestEdge($API);
                        break;
                        // start IPS service
                case 'startIPSservice':
                        $result = APIOther::startIPSservice($API);
                        break;
                        // stop IPS service
                case 'stopIPSservice':
                        $result = APIOther::stopIPSservice($API);
                        break;
                        // updateIPSDatas
                case 'updateIPSDatas':
                        $result = APIOther::updateIPSDatas($API);
                        break;
                case 'setAllGlobalSummary':
                        $result = APIEdit::setAllGlobalSummary($API);
                        break;
                case 'setGlobalSummary':
                        $result = APIEdit::setGlobalSummary($API);
                        break;
                case 'getCmdSummary':
                        $result = APIEdit::getCmdSummary($API);
                        break;
                case 'getAllCmdSummaryByRoom':
                        $result = APIEdit::getAllCmdSummaryByRoom($API);
                        break;
                case 'setAllCmdSummaryByRoom':
                        $result = APIEdit::setAllCmdSummaryByRoom($API);
                        break;
                case 'setNewCmdSummary':
                        $result = APIEdit::setNewCmdSummary($API);
                        break;
                case 'upsertSummary':
                        $result = APIEdit::upsertSummary($API);
                        break;
                case 'deleteSummary':
                        $result = APIEdit::deleteSummary($API);
                        break;

                default:
                        CoreLog::add('apiError', 'error', 'Methode : ' . $API->method);
                        CoreLog::add('apiError', 'error', 'JeeMate API method is not valid! Or Not implemented ! : ' . $API->eqLogicID);
                        break;
        }
} catch (Exception $e) {
        $API->setError(6, "Exception Error : " . $e);
}

echo json_encode($API->sendReponse($result));

$time_end = microtime(true);
$diff = $time_end - $time_start;
CoreLog::add('api', 'debug', "--------------------- Fin Requête : " .  $API->method . ", Exécution : " . $diff . " ----------------------------------------------");
die();
