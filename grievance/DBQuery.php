<?php

require_once 'Model/GrievanceNature.php';
require_once 'Model/GrievanceRemedy.php';
require_once 'Model/GrievanceStep.php';
require_once 'Model/GrievanceStatus.php';
require_once 'Model/GrievanceStepStatus.php';
require_once 'Model/GrievanceStepRelation.php';
require_once 'Model/GrievanceLog.php';
require_once 'Model/Grievance.php';
require_once 'Model/Member.php';
require_once 'Model/User.php';
require_once 'Model/GrievanceDocument.php';
require_once 'Model/GrievanceInfoRequest.php';
require_once 'Model/GrievanceAlerts.php';
require_once 'Model/GrievanceInfoRequestOptions.php';
require_once 'CreateGrievanceForms.php';
require 'ErrorCodes.php';

class DBQuery {

    protected $dbConnection;
    private $pageLimit = 15;
    private $step1Id = 1;
    private $step2Id = 2;
    private $step3Id = 3;
    private $stepArbId = 4;
    private $statusNeedReviewId = 10000;
    private $statusAutPromoteId = 10001;

    public function __construct($conn) {
        try {
            $this->dbConnection = $conn;
        } catch (PDOException $e) {
            //http_response_code(500);
            die();
        }
    }

    public function getDateTime() {
        $dateTime = null;
        try {
            $sdate = $this->dbConnection->prepare('SELECT NOW();');
            $sdate->execute();
            $result = $sdate->fetchAll();

            $dateTime = strtotime($result[0][0]);
        } catch (PDOException $e) {
            // http_response_code(500);
            die();
        }

        return $dateTime;
    }

    public function getControlTime() {
        $seconds = null;
        try {
            $sdate = $this->dbConnection->prepare('SELECT `value` FROM `metadata` WHERE metakey = "controlTime"');
            $sdate->execute();
            $result = $sdate->fetchAll();

            $seconds = ($result[0][0]);
        } catch (PDOException $e) {
            //http_response_code(500);
            die();
        }

        return $seconds;
    }

    public function getSystemUserId() {
        $id = null;
        try {
            $sdate = $this->dbConnection->prepare('SELECT `value` FROM `metadata` WHERE metakey = "systemUserId"');
            $sdate->execute();
            $result = $sdate->fetchAll();

            $id = ($result[0][0]);
        } catch (PDOException $e) {
            //http_response_code(500);
            die();
        }

        return $id;
    }

    public function getServerApiUrl() {
        $url = null;
        try {
            $sdate = $this->dbConnection->prepare('SELECT `value` FROM `metadata` WHERE metakey = "serverApiUrl"');
            $sdate->execute();
            $result = $sdate->fetchAll();

            $url = ($result[0][0]);
        } catch (PDOException $e) {
            //http_response_code(500);
            die();
        }

        return $url;
    }

    public function getGrievanceNature() {
        $grievancesNature = array();

        try {

            $stmt = $this->dbConnection->prepare('SELECT id, name FROM `grievance_nature`');
            $stmt->execute();
            $result = $stmt->fetchAll();
            foreach ($result as $row) {
                array_push($grievancesNature, new GrievanceNature($row['id'], $row['name']));
            }
        } catch (PDOException $e) {
            //http_response_code(500);
            die();
        }

        return $grievancesNature;
    }

    public function getInfoRequestOptions() {

        $grievanceInfoReqFirst = array();
        $grievanceInfoReqSecond = array();
        try {
            $stmt = $this->dbConnection->prepare('SELECT `id`, `option_name`, `first_part` FROM `grievance_info_request`');
            $stmt->execute();
            $result = $stmt->fetchAll();
            foreach ($result as $row) {
                if ($row['first_part'] == 1) {
                    array_push($grievanceInfoReqFirst, new GrievanceInfoRequest($row['id'], $row['option_name']));
                } else {
                    array_push($grievanceInfoReqSecond, new GrievanceInfoRequest($row['id'], $row['option_name']));
                }
            }
            $grievanceInfoReq = new GrievanceInfoRequestOptions($grievanceInfoReqFirst, $grievanceInfoReqSecond);
        } catch (PDOException $e) {
            //http_response_code(500);
            die();
        }

        return $grievanceInfoReq;
    }

    public function getGrievanceLog($grievanceId, $paramArray, &$messagecode, &$messagestatus, &$messagetext) {
        $grievancesLogs = array();
        $messagecode = 200;
        $messagestatus = "";
        $messagetext = "";
        $pageFilter = null;
        foreach ($paramArray as $key => $value) {
            switch ($key) {
                case 'page':
                    $pageFilter = $value;
                    break;
                default:
                    break;
            }
        }

        if ($pageFilter != null) {
            try {
                $total = $this->pageLimit * ($pageFilter - 1);
                $sqlQuery = "SELECT `id`, `step_id`,`title`, `status_id`, `date_time`, `description`, `member_id`, `user_id` FROM `grievance_log` WHERE grievance_id = :value1 LIMIT $this->pageLimit OFFSET $total";
                $stmt->bindParam(':value1', $grievanceId);
                $stmt = $this->dbConnection->prepare($sqlQuery);

                $stmt->execute();

                $result = $stmt->fetchAll();

                foreach ($result as $row) {

                    $step = $this->getGrievanceStepById($row['step_id']);
                    $user = $this->getUserById($row['user_id']);
                    $status = $this->getGrievanceStatusById($row['status_id']);

                    $steward = $this->getMemberById($row['member_id']);
                    array_push($grievancesLogs, new GrievanceLog($grievanceId, $row['id'], $step, $status, $steward, $row['title'], $user, date("M d, Y H:i:s", strtotime($row['date_time'])), $row['description']));
                }
            } catch (PDOException $e) {
                $messagecode=500;
                //http_response_code(500);
                die();
            }
        } else {
            $messagecode = 400;
            $messagestatus = "Error";
            $messagetext = "Page parameter is requiered";
        }
        return $grievancesLogs;
    }

   
    
    
    public function getGrievanceSteps() {
        $grievancesSteps = array();
        try {
            $stmt = $this->dbConnection->prepare('SELECT `id`, `description`, `step_order` FROM `grievance_step` ORDER BY `step_order` ASC');
            $stmt->execute();
            $result = $stmt->fetchAll();
            foreach ($result as $row) {
                array_push($grievancesSteps, new GrievanceStep($row['id'], $row['step_order'], $row['description']));
            }
        } catch (PDOException $e) {
            //http_response_code(500);
            die();
        }

        return $grievancesSteps;
    }

    public function getGrievanceNatureById($id) {
        $grievanceNature = null;
        try {

            $stmt = $this->dbConnection->prepare('SELECT `id`, `name` FROM `grievance_nature` WHERE `id` = :id');
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetchAll();
                $grievanceNature = new GrievanceNature($result[0]['id'], $result[0]['name']);
            }
        } catch (PDOException $e) {
            //http_response_code(500);
            die();
        }

        return $grievanceNature;
    }

    public function getGrievanceStepById($id) {
        $grievanceStep = new GrievanceStep();
        try {
            $stmt = $this->dbConnection->prepare('SELECT id, description, step_order FROM `grievance_step` WHERE id = :id');
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetchAll();
                $grievanceStep->setId($result[0]['id']);
                $grievanceStep->setStepName($result[0]['description']);
                $grievanceStep->setOrder($result[0]['step_order']);
            }
        } catch (PDOException $e) {
            //http_response_code(500);
            die();
        }

        return $grievanceStep;
    }

    public function getGrievanceRemedy() {
        $grievancesRemedy = array();

        try {

            $stmt = $this->dbConnection->prepare('SELECT id, name FROM `grievance_remedy`');
            $stmt->execute();
            $result = $stmt->fetchAll();
            foreach ($result as $row) {
                array_push($grievancesRemedy, new GrievanceRemedy($row['id'], $row['name']));
            }
        } catch (PDOException $e) {
            //http_response_code(500);
            die();
        }

        return $grievancesRemedy;
    }

    public function getGrievanceRemedyById($id) {
        $grievanceRemedy = null;
        try {

            $stmt = $this->dbConnection->prepare('SELECT `id`, `name` FROM `grievance_remedy` WHERE `id` = :id');
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetchAll();
                $grievanceRemedy = new GrievanceNature($result[0]['id'], $result[0]['name']);
            }
        } catch (PDOException $e) {
            //http_response_code(500);
            die();
        }

        return $grievanceRemedy;
    }

    public function getGrievanceStatusById($id) {
        $grievanceStatus = new GrievanceStatus();
        try {

            $stmt = $this->dbConnection->prepare('SELECT id, name FROM `grievance_status` WHERE  `id` = :id');
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetchAll();
                $grievanceStatus->setId($result[0]['id']);
                $grievanceStatus->setStatusName($result[0]['name']);
            }
        } catch (PDOException $e) {
            //http_response_code(500);
            die();
        }

        return $grievanceStatus;
    }



    function getGrievancesStepsRelation($grievanceId) {
        $grievancesStepRelation = array();
        try {

            $stmt = $this->dbConnection->prepare('SELECT `grievanceid`, `stepid`, `steward_id`, `user_id`, `dateofgrievancemeeting`, `nameofdesignee`, `nameofcompanyrepresentative`, `nameofotherspresent`, `wasinformationrequestedinwritingbydesignee`, `wasinformationprovidedbycompanyrep`, `didcompanyrepprovidewrittenresponse`, `statusid`, `written_response_date` FROM `grievance_rel_grievance_step` WHERE grievanceid= :value1');
            $stmt->bindParam(':value1', $grievanceId);
            $stmt->execute();
            $result = $stmt->fetchAll();
            foreach ($result as $row) {
                $step = $this->getGrievanceStepById($row['stepid']);
                $status = $this->getGrievanceStatusById($row['statusid']);

                if (!empty($row['steward_id'])) {
                    $steward = $this->getMemberById($row['steward_id']);
                } else {
                    $steward = null;
                }
                if (!empty($row['user_id'])) {
                    $user = $this->getUserById($row['user_id']);
                } else {
                    $user = null;
                }

                $responseDate = null;
                if (($row['written_response_date']) != 0) {
                    $responseDate = date("M d, Y H:i:s", strtotime($row['written_response_date']));
                }

                $meetingDate = null;
                if (($row['dateofgrievancemeeting']) != 0) {
                    $meetingDate = date("M d, Y H:i:s", strtotime($row['dateofgrievancemeeting']));
                }


                array_push($grievancesStepRelation, new GrievanceStepRelation($step, $status, $steward, $user, $meetingDate, $row['nameofdesignee'], $row['nameofcompanyrepresentative'], $row['nameofotherspresent'], $row['wasinformationrequestedinwritingbydesignee'], $row['wasinformationprovidedbycompanyrep'], $row['didcompanyrepprovidewrittenresponse'], $responseDate));
            }
        } catch (PDOException $e) {
            //http_response_code(500);
            die();
        }
        return $grievancesStepRelation;
    }

    public function getGrievanceStepStatusByStep($stepId) {
        $grievancesStepStatus = array();

        try {

            $stmt = $this->dbConnection->prepare('SELECT sp.id stepid, sp.description stepdescription, sp.step_order steporder, st.id statusid, st.name statusname, rss.mandatory mandatory, rss.closestep closestep, rss.promotestep promotestep, rss.required_data required_data, rss.status_order status_order FROM `grievance_step` sp, `grievance_status` st, `grievance_rel_step_status` rss WHERE rss.stepid = sp.id and rss.statusid= st.id and rss.stepid = :stepId ORDER BY sp.step_order ASC, rss.status_order ASC');
            $stmt->bindParam(':stepId', $stepId);
            $stmt->execute();
            $result = $stmt->fetchAll();
            foreach ($result as $row) {
                $step = new GrievanceStep($row['stepid'], $row['steporder'], $row['stepdescription']);
                $status = new GrievanceStatus($row['statusid'], $row['statusname']);
                array_push($grievancesStepStatus, new GrievanceStepStatus($step, $status, $row['mandatory'], $row['closestep'], $row['promotestep'], $row['required_data'], $row['status_order']));
            }
        } catch (PDOException $e) {
            //http_response_code(500);
            die();
        }

        return $grievancesStepStatus;
    }

    public function getGrievanceFilterStepStatusByStep($stepId) {
        $grievancesStepStatus = array();

        try {

            $stmt = $this->dbConnection->prepare('SELECT sp.id stepid, sp.description stepdescription, sp.step_order steporder, st.id statusid, st.name statusname, rss.mandatory mandatory, rss.closestep closestep, rss.promotestep promotestep, rss.required_data required_data, rss.status_order status_order FROM `grievance_step` sp, `grievance_status` st, `grievance_rel_step_status` rss WHERE rss.stepid = sp.id and rss.statusid= st.id and rss.stepid = :stepId and rss.status_order > 1 ORDER BY sp.step_order ASC, rss.status_order ASC');
            $stmt->bindParam(':stepId', $stepId);
            $stmt->execute();
            $result = $stmt->fetchAll();
            foreach ($result as $row) {
                $step = new GrievanceStep($row['stepid'], $row['steporder'], $row['stepdescription']);
                $status = new GrievanceStatus($row['statusid'], $row['statusname']);
                array_push($grievancesStepStatus, new GrievanceStepStatus($step, $status, $row['mandatory'], $row['closestep'], $row['promotestep'], $row['required_data'], $row['status_order']));
            }
        } catch (PDOException $e) {
            //http_response_code(500);
            die();
        }

        return $grievancesStepStatus;
    }

    //This method update grievance information. The steward assigne don't be updated. The steward must be updated from the update step assigned
    public function updateGrievanceData($grievanceId, $jsonArray, $memberLog, $userLog, &$messagecode, &$messagestatus, &$messagetext, $signatureFile) {

        $messagecode = 200;
        $messagestatus = "";
        $messagetext = "";
        $grievance = $this->getGrievanceById($grievanceId);
        if ($grievance != null) {
            if ($grievance->setFromJsonArray($jsonArray)) {
                $remedy = null;
                $nature = null;
                if (!empty($grievance->getGrievanceRemedy())) {
                    $remedy = $this->getGrievanceRemedyById($grievance->getGrievanceRemedy()->getId());
                }
                if (!empty($grievance->getGrievanceNature())) {
                    $nature = $this->getGrievanceNatureById($grievance->getGrievanceNature()->getId());
                }
                if (empty($signatureFile)) {
                    $signatureFile = $grievance->getSignatureFile();
                }

                $grievance->setGrievanceNature($nature);
                $grievance->setGrievanceRemedy($remedy);
                $grievance = $this->updateGrievanceInDB($grievance, $signatureFile, $memberLog, $userLog);
//                try {
//
//                    $sqlString = "UPDATE `grievance` SET `clause_of_contract_violated`= :value3,`member_address_1`=:value4,`member_address_2`= :value5,`member_bureau`= :value6,`member_city`= :value7,`member_department`= :value8,`member_email`= :value9,`member_phone`= :value10,`member_state`= :value11,`member_supervisor`=:value12,`member_title`= :value13,`member_work_location`= :value14,`member_zip_code`= :value15,`nature_of_grievance_id`= :value16,`nature_of_grievance_other`= :value17, `original_date_of_grievance`= :value18, `remedy_id`=:value19,`remedy_other`=:value20, `signature_file` = :value23 WHERE `id`= :value1";
//                    $this->dbConnection->beginTransaction();
//                    $stmt = $this->dbConnection->prepare($sqlString);
//                    $stmt->bindParam(':value1', $grievanceId);
//
//                    $stmt->bindParam(':value3', $grievance->getClauseOfContractViolated());
//                    $stmt->bindParam(':value4', $grievance->getAddress1());
//                    $stmt->bindParam(':value5', $grievance->getAddress2());
//                    $stmt->bindParam(':value6', $grievance->getBureau());
//                    $stmt->bindParam(':value7', $grievance->getCity());
//                    $stmt->bindParam(':value8', $grievance->getDepartment());
//                    $stmt->bindParam(':value9', $grievance->getEmail());
//                    $stmt->bindParam(':value10', $grievance->getPhone());
//
//                    $stmt->bindParam(':value11', $grievance->getState());
//                    $stmt->bindParam(':value12', $grievance->getSupervisor());
//                    $stmt->bindParam(':value13', $grievance->getTitle());
//                    $stmt->bindParam(':value14', $grievance->getWorkLocation());
//                    $stmt->bindParam(':value15', $grievance->getZipCode());
//                    if (!empty($grievance->getGrievanceNature())) {
//                        $stmt->bindParam(':value16', $grievance->getGrievanceNature()->getId());
//                    } else {
//                        $stmt->bindParam(':value16', $grievance->getGrievanceNature());
//                    }
//
//                    $stmt->bindParam(':value17', $grievance->getNatureOfGrivanceOther());
//
//                    $dateValue = date("Y-m-d H:i:s", strtotime($grievance->getOriginalDateOfGrievance()));
//                    $stmt->bindParam(':value18', $dateValue);
//
//                    if (!empty($grievance->getGrievanceRemedy())) {
//                        $stmt->bindParam(':value19', $grievance->getGrievanceRemedy()->getId());
//                    } else {
//                        $stmt->bindParam(':value19', $grievance->getGrievanceRemedy());
//                    }
//
//                    $stmt->bindParam(':value20', $grievance->getRemedyOther());
//
//                    $stmt->bindParam(':value23', $signatureFile);
//                    $stmt->execute();
//
//                    $dateOfLog = $this->getDateTime();
//
//                    $lastStep = count($grievance->getSteps()) - 1;
//
//                    $step = $grievance->getSteps()[$lastStep]->getStep();
//                    $status = $grievance->getSteps()[$lastStep]->getStatus();
//
//                    $grievanceLog = new GrievanceLog($grievanceId, 0, $step, $status, $memberLog, "Update Grievance", $userLog, $dateOfLog, "Grievance info was updated");
//                    $this->insertLog($grievanceLog);
//
//                    $this->dbConnection->commit();
//
//                    $grievance = $this->getGrievanceById($grievanceId);
//                } catch (PDOException $e) {
//
//                    $this->dbConnection->rollback();
//                    $messagecode = 500;
//                    $grievance = null;
//                    //http_response_code(500);
//                    die();
//                }
                if ($grievance==null)
                {
                    $messagecode = 500;
                }
            } else {
                $messagecode = 400;
                $messagestatus = "Error";
                $messagetext = "Unexpected Json value";
            }
        } else {
            $messagecode = 400;
            $messagestatus = "Error";
            $messagetext = "Invlaid Grievance Id";
        }

        return $grievance;
    }
    
    
    public function updateGrievanceInDB($grievance, $signatureFile, $memberLog, $userLog)
    {
        try {

                    $sqlString = "UPDATE `grievance` SET `clause_of_contract_violated`= :value3,`member_address_1`=:value4,`member_address_2`= :value5,`member_bureau`= :value6,`member_city`= :value7,`member_department`= :value8,`member_email`= :value9,`member_phone`= :value10,`member_state`= :value11,`member_supervisor`=:value12,`member_title`= :value13,`member_work_location`= :value14,`member_zip_code`= :value15,`nature_of_grievance_id`= :value16,`nature_of_grievance_other`= :value17, `original_date_of_grievance`= :value18, `remedy_id`=:value19,`remedy_other`=:value20, `signature_file` = :value23 WHERE `id`= :value1";
                    $this->dbConnection->beginTransaction();
                    $stmt = $this->dbConnection->prepare($sqlString);
                    $stmt->bindParam(':value1', $grievance->getId());

                    $stmt->bindParam(':value3', $grievance->getClauseOfContractViolated());
                    $stmt->bindParam(':value4', $grievance->getAddress1());
                    $stmt->bindParam(':value5', $grievance->getAddress2());
                    $stmt->bindParam(':value6', $grievance->getBureau());
                    $stmt->bindParam(':value7', $grievance->getCity());
                    $stmt->bindParam(':value8', $grievance->getDepartment());
                    $stmt->bindParam(':value9', $grievance->getEmail());
                    $stmt->bindParam(':value10', $grievance->getPhone());

                    $stmt->bindParam(':value11', $grievance->getState());
                    $stmt->bindParam(':value12', $grievance->getSupervisor());
                    $stmt->bindParam(':value13', $grievance->getTitle());
                    $stmt->bindParam(':value14', $grievance->getWorkLocation());
                    $stmt->bindParam(':value15', $grievance->getZipCode());
                    if (!empty($grievance->getGrievanceNature())) {
                        $stmt->bindParam(':value16', $grievance->getGrievanceNature()->getId());
                    } else {
                        $stmt->bindParam(':value16', $grievance->getGrievanceNature());
                    }

                    $stmt->bindParam(':value17', $grievance->getNatureOfGrivanceOther());

                    $dateValue = date("Y-m-d H:i:s", strtotime($grievance->getOriginalDateOfGrievance()));
                    $stmt->bindParam(':value18', $dateValue);

                    if (!empty($grievance->getGrievanceRemedy())) {
                        $stmt->bindParam(':value19', $grievance->getGrievanceRemedy()->getId());
                    } else {
                        $stmt->bindParam(':value19', $grievance->getGrievanceRemedy());
                    }

                    $stmt->bindParam(':value20', $grievance->getRemedyOther());

                    if (empty($signatureFile)) {
                         $signatureFile = $grievance->getSignatureFile();
                    }
   
                    $stmt->bindParam(':value23', $signatureFile);
                    $stmt->execute();

                    $dateOfLog = $this->getDateTime();

                    $lastStep = count($grievance->getSteps()) - 1;

                    $step = $grievance->getSteps()[$lastStep]->getStep();
                    $status = $grievance->getSteps()[$lastStep]->getStatus();

                    $grievanceLog = new GrievanceLog($grievance->getId(), 0, $step, $status, $memberLog, "Update Grievance", $userLog, $dateOfLog, "Grievance info was updated");
                    $this->insertLog($grievanceLog);

                    $this->dbConnection->commit();

                    return $this->getGrievanceById($grievance->getId());
                } catch (PDOException $e) {

                    $this->dbConnection->rollback();
                    //http_response_code(500);
                    die();
                }
                
                return null;
    }

    public function getControlGrievanceById($grievanceId, $member, $user, &$messageCode, &$messagestatus, &$messagetext) {
        $messageCode = 200;
        $messagestatus = "";
        $messagetext = "";

        $grievance = $this->getGrievanceById($grievanceId);
        if ($grievance != null) {
            if (!($user != null && $user->getRole() < 3)) {

                if ($user != null) {
                    $tot = count($grievance->getSteps());
                    if ($tot == 1) {
                        $messageCode = 401;
                        $messagestatus = "Error";
                        $messagetext = "Invalid Credential to open Grievance details";
                    } else if ($tot == 2 && $grievance->getSteps()[1]->getUser()->getId() != $user->getId()) {
                        $messageCode = 401;
                        $messagestatus = "Error";
                        $messagetext = "Invalid Credential to open Grievance details";
                    } else if ($tot = 3 && ($grievance->getSteps()[1]->getUser()->getId() != $user->getId() && $grievance->getSteps()[2]->getUser()->getId() != $user->getId())) {
                        $messageCode = 401;
                        $messagestatus = "Error";
                        $messagetext = "Invalid Credential to open Grievance details";
                    } else if ($tot = 4 && ($grievance->getSteps()[1]->getUser()->getId() != $user->getId() && $grievance->getSteps()[2]->getUser()->getId() != $user->getId() && $grievance->getSteps()[3]->getUser()->getId() != $user->getId())) {
                        $messageCode = 401;
                        $messagestatus = "Error";
                        $messagetext = "Invalid Credential to open Grievance details";
                    }
                } else if ($member != null) {
                    if (!(($grievance->getMember()->getMemberId() == $member->getMemberId()) || $grievance->getSteward()->getMemberId() == $member->getMemberId())) {
                        $messageCode = 401;
                        $messagestatus = "Error";
                        $messagetext = "Invalid Credential to open Grievance details";
                    }
                }
            }
        }

        return $grievance;
    }

    public function getGrievanceById($grievanceId) {

        $grievance = null;
        $sqlQuery = "SELECT g.id, g.member_id, g.clause_of_contract_violated, g.member_address_1, g.member_address_2, g.member_bureau, g.member_city, g.member_department, g.member_email, g.member_phone, g.member_state, g.member_supervisor, g.member_title, g.member_work_location, g.member_zip_code, g.nature_of_grievance_id, g.nature_of_grievance_other, g.original_date_of_grievance, g.remedy_id, g.remedy_other, g.steward_id, g.control_date, g.submission_date, g.signature_file FROM grievance g WHERE g.id = $grievanceId;";

        $stmt = $this->dbConnection->prepare($sqlQuery);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetchAll();
            $member = $this->getMemberById($row[0]['member_id']);
            $steward = $this->getMemberById($row[0]['steward_id']);
            $grievanceNature = $this->getGrievanceNatureById($row[0]['nature_of_grievance_id']);
            $grievanceRemedy = $this->getGrievanceRemedyById($row[0]['remedy_id']);
            $originalDate = strtotime($row[0]['original_date_of_grievance']);
            $controlDate = null;
            if (($row[0]['control_date']) > 0) {
                $controlDate = date("M d, Y H:i:s", strtotime($row[0]['control_date']));
            }
            $submissionDate = strtotime($row[0]['submission_date']);
            $steps = $this->getGrievancesStepsRelation($row[0]['id']);
            $grievance = new Grievance($row[0]['id'], $member, $row[0]['clause_of_contract_violated'], $row[0]['member_address_1'], $row[0]['member_address_2'], $row[0]['member_bureau'], $row[0]['member_city'], $row[0]['member_department'], $row[0]['member_email'], $row[0]['member_phone'], $row[0]['member_state'], $row[0]['member_supervisor'], $row[0]['member_title'], $row[0]['member_work_location'], $row[0]['member_zip_code'], $grievanceNature, $row[0]['nature_of_grievance_other'], date("M d, Y H:i:s", $originalDate), $grievanceRemedy, $row[0]['remedy_other'], $steps, $steward, $controlDate, date("M d, Y H:i:s", $submissionDate), $row[0]['signature_file']);
        }
        return $grievance;
    }

//    private function getStatusOrder($stepId, $statusId) {
//        $order = 0;
//
//        $sqlQuery = "SELECT status_order FROM grievance_rel_step_status WHERE stepid = $stepId and statusid = $statusId;";
//
//        $stmt = $this->dbConnection->prepare($sqlQuery);
//        $stmt->execute();
//        if ($stmt->rowCount() > 0) {
//            $row = $stmt->fetchAll();
//            $order = $row[0][0];
//        }
//        return $order;
//    }
//
    private function updateStepRelation($grievanceStepRelation, $grievanceId) {

        $stepId = $grievanceStepRelation->getStep()->getId();
        $dateateOfGrievanceMeeting = null;
        if (!empty($grievanceStepRelation->getDateOfGrievanceMeeting())) {
            $dateateOfGrievanceMeeting = date("Y-m-d H:i:s", strtotime($grievanceStepRelation->getDateOfGrievanceMeeting()));
        }
        $designee = $grievanceStepRelation->getNameOfDesignee();
        $representative = $grievanceStepRelation->getCompanyRep();
        $otherPresent = $grievanceStepRelation->getOtherPresent();
        $informationReq = 0;
        if ($grievanceStepRelation->getInfoRequestedInWritingByDesignee()) {
            $informationReq = 1;
        }
        $infoProvCom = 0;
        if ($grievanceStepRelation->getInfoProvidedByCompany()) {
            $infoProvCom = 1;
        }
        $companyProvWrite = 0;
        if ($grievanceStepRelation->getCompanyProvideWrittenResponse()) {
            $companyProvWrite = 1;
        }
        $writtenResponseDate = null;
        if ($companyProvWrite == 1 && !(empty($grievanceStepRelation->getWrittenResponseDate()))) {
            $writtenResponseDate = date("Y-m-d H:i:s", strtotime($grievanceStepRelation->getWrittenResponseDate()));
        }
        $statusId = $grievanceStepRelation->getStatus()->getId();

        $sqlString = "UPDATE `grievance_rel_grievance_step` SET `dateofgrievancemeeting`='$dateateOfGrievanceMeeting',`nameofdesignee`= '$designee',`nameofcompanyrepresentative`='$representative',`nameofotherspresent`='$otherPresent',`wasinformationrequestedinwritingbydesignee`=$informationReq,`wasinformationprovidedbycompanyrep`=$infoProvCom,`didcompanyrepprovidewrittenresponse`=$companyProvWrite,`written_response_date`='$writtenResponseDate',`statusid`=$statusId WHERE grievanceid= $grievanceId AND `stepid`=$stepId;";

        $stmt = $this->dbConnection->prepare($sqlString);

        $stmt->execute();
    }

    private function insertLog($grievanceLog) {

        $sqlString = "INSERT INTO `grievance_log`(`grievance_id`, `step_id`, `status_id`, `title`, `date_time`, `description`, `member_id`, `user_id`) VALUES (:value1, :value2,:value3,:value4,:value5, :value6, :value7, :value8)";

        $stmt = $this->dbConnection->prepare($sqlString);

        $stmt->bindParam(':value1', $grievanceLog->getGrievanceId());

        $stmt->bindParam(':value2', $grievanceLog->getStep()->getId());

        $stmt->bindParam(':value3', $grievanceLog->getStatus()->getId());

        $stmt->bindParam(':value4', $grievanceLog->getTitle());

        $dateValue = date("Y-m-d H:i:s", $grievanceLog->getDateTime());
        $stmt->bindParam(':value5', $dateValue);

        $stmt->bindParam(':value6', $grievanceLog->getDescription());

        if ($grievanceLog->getMember() != null) {
            $stmt->bindParam(':value7', $grievanceLog->getMember()->getId());
        } else {
            $stmt->bindParam(':value7', $grievanceLog->getMember());
        }

        if ($grievanceLog->getUser() != null) {
            $stmt->bindParam(':value8', $grievanceLog->getUser()->getId());
        } else {
            $stmt->bindParam(':value8', $grievanceLog->getUser());
        }
        $stmt->execute();
    }

    public function createGrievance($member, &$grievance, $signatureFile, $jsonArray, &$messagecode, &$messagestatus, &$messagetext) {

        $returnValue = false;
//        $steps = array();

        $stewardAux = new Member(0, "", "", "", "", "", null, "");
        $remedyAux = new GrievanceRemedy(0, "");
        $natureAux = new GrievanceNature(0, "");


        $messagecode = 200;
        $messagestatus = "";
        $messagetext = "";

        $grievance = new Grievance(0, $member, "", "", "", "", "", "", "", "", "", "", "", "", "", $natureAux, "", "", $remedyAux, "", null, $stewardAux, null, "", $signatureFile);

        if ($grievance->setFromJsonArray($jsonArray)) {


            $remedy = null;
            $nature = null;
            if (!empty($grievance->getGrievanceRemedy())) {
                $remedy = $this->getGrievanceRemedyById($grievance->getGrievanceRemedy()->getId());
            }
            if (!empty($grievance->getGrievanceNature())) {
                $nature = $this->getGrievanceNatureById($grievance->getGrievanceNature()->getId());
            }
            $steward = $this->getMemberByIdCode($grievance->getSteward()->getMemberId());



            if ($steward != null && strcasecmp($steward->getMemberRol(), 'Steward') == 0) {
                $grievance->setSteward($steward);
                $grievance->setGrievanceNature($nature);
                $grievance->setGrievanceRemedy($remedy);
                $next = $this->getControlTime();
                $todayDate = new DateTime(date("Y-m-d H:i:s", $this->getDateTime()));
                $controlDate = new DateTime($grievance->getOriginalDateOfGrievance());
                $controlDate->add(new DateInterval('PT' . $next . 'S'));
                //25 days
                $creteStepQty = 1;


                if ($todayDate < $controlDate) {
                    $creteStepQty = 1;
                } else {
                    $controlDate->add(new DateInterval('PT' . $next . 'S'));

                    //50 days
                    if ($steward->getReportTo() == null || $todayDate < $controlDate) {
                        $creteStepQty = 2;

                        if ($steward->getReportTo() == null) {
                            $controlDate = null;
                        }
                    } else {
                        $controlDate->add(new DateInterval('PT' . $next . 'S'));
                        //75 days

                        if ($this->getUserById($steward->getReportTo())->getReportTo() == null || $todayDate < $controlDate) {
                            $creteStepQty = 3;

                            if ($this->getUserById($steward->getReportTo())->getReportTo() == null) {
                                $controlDate = null;
                            }
                        } else {

                            $creteStepQty = 4;
                            $controlDate = null;
                        }
                    }
                }

                if ($controlDate != null) {
                    $grievance->setControlDate(date("M d, Y H:i:s", $controlDate->getTimestamp()));
                }



                if ($this->insertGrievance($grievance, $creteStepQty, $controlDate)) {
                    $returnValue = true;
                    $grievance = $this->getGrievanceById($grievance->getId());
                } else {
                    $messagecode = 400;
                    $messagestatus = "Error";
                    $messagetext = "Add Grievance Error";
                }
            } else {
                $messagecode = 400;
                $messagestatus = "Error";
                $messagetext = "Unexpected Steward Id";
            }
        } else {
            $messagecode = 400;
            $messagestatus = "Error";
            $messagetext = "Unexpected Json value";
        }
        return $returnValue;
    }

    public function insertGrievance(&$grievance, $createStepsQty, $controlDate) {

        $insertResult = true;

        $stmt = $this->dbConnection->prepare('INSERT INTO `grievance`(`member_id`, `clause_of_contract_violated`, `member_address_1`, `member_address_2`, `member_bureau`, `member_city`, `member_department`, `member_email`, `member_phone`, `member_state`, `member_supervisor`, `member_title`, `member_work_location`, `member_zip_code`, `nature_of_grievance_id`, `nature_of_grievance_other`, `original_date_of_grievance`, `remedy_id`, `remedy_other`, `steward_id`, `control_date`, `submission_date` , `signature_file`) VALUES (:value1,:value2,:value3,:value4,:value5,:value6,:value7,:value8,:value9,:value10,:value11,:value12,:value13,:value14,:value15,:value16,:value17,:value18,:value19,:value20,:value21,:value22,:value23)');
        $stmt->bindParam(':value1', $grievance->getMember()->getId());

        $stmt->bindParam(':value2', $grievance->getClauseOfContractViolated());
        $stmt->bindParam(':value3', $grievance->getAddress1());
        $stmt->bindParam(':value4', $grievance->getAddress2());
        $stmt->bindParam(':value5', $grievance->getBureau());
        $stmt->bindParam(':value6', $grievance->getCity());
        $stmt->bindParam(':value7', $grievance->getDepartment());
        $stmt->bindParam(':value8', $grievance->getEmail());
        $stmt->bindParam(':value9', $grievance->getPhone());

        $stmt->bindParam(':value10', $grievance->getState());
        $stmt->bindParam(':value11', $grievance->getSupervisor());
        $stmt->bindParam(':value12', $grievance->getTitle());
        $stmt->bindParam(':value13', $grievance->getWorkLocation());
        $stmt->bindParam(':value14', $grievance->getZipCode());

        if (!empty($grievance->getGrievanceNature())) {
            $stmt->bindParam(':value15', $grievance->getGrievanceNature()->getId());
        } else {
            $stmt->bindParam(':value15', $grievance->getGrievanceNature());
        }

        $stmt->bindParam(':value16', $grievance->getNatureOfGrivanceOther());

        $dateValue = date("Y-m-d H:i:s", strtotime($grievance->getOriginalDateOfGrievance()));
        $stmt->bindParam(':value17', $dateValue);


        if (!empty($grievance->getGrievanceRemedy())) {
            $stmt->bindParam(':value18', $grievance->getGrievanceRemedy()->getId());
        } else {
            $stmt->bindParam(':value18', $grievance->getGrievanceRemedy());
        }

        $stmt->bindParam(':value19', $grievance->getRemedyOther());

        $stmt->bindParam(':value20', $grievance->getSteward()->getId());

        $controlDateValue = null;
        if ($grievance->getControlDate() != null) {
            $controlDateValue = date("Y-m-d H:i:s", strtotime($grievance->getControlDate()));
        }

        $stmt->bindParam(':value21', $controlDateValue);
        $stmt->bindParam(':value23', $grievance->getSignatureFile());

        try {
            $this->dbConnection->beginTransaction();
            $sdate = $this->dbConnection->prepare('SELECT NOW();');
            $sdate->execute();
            $result = $sdate->fetchAll();

            $subDate = $result[0][0];
            $grievance->setSubmissionDate($subDate);
            $stmt->bindParam(':value22', $grievance->getSubmissionDate());

            $stmt->execute();


            $lastid = $this->dbConnection->lastInsertId();

            $newStatus = 1;

            if ($createStepsQty == 1) {
                $newStatus = $this->createStep1($lastid, $grievance->getSteward()->getId());
            } else {
                $this->createStepAutPromote($lastid, 1, $grievance->getSteward()->getId());
                if ($createStepsQty == 2) {
                    if ($controlDate != null) {
                        $newStatus = $this->createStep2($lastid, $grievance->getSteward()->getReportTo());
                    } else {
                        $newStatus = $this->createStepNeedReview($lastid, 2);
                    }
                } else {
                    $this->createStepAutPromote($lastid, 2, $grievance->getSteward()->getReportTo());
                    if ($createStepsQty == 3) {

                        if ($controlDate != null) {
                            $newStatus = $this->createStep3($lastid, $this->getUserById($grievance->getSteward()->getReportTo())->getReportTo());
                        } else {

                            $newStatus = $this->createStepNeedReview($lastid, 3);
                        }
                    } else {
                        $this->createStepAutPromote($lastid, 3, $this->getUserById($grievance->getSteward()->getReportTo())->getReportTo());
                        $newStatus = $this->createStepArb($lastid, $this->getUserById($grievance->getSteward()->getReportTo())->getReportTo());
                    }
                }
            }



            $grievance->setId($lastid);
            $grievanceLog = new GrievanceLog($lastid, 0, $this->getGrievanceStepById($createStepsQty), $this->getGrievanceStatusById($newStatus), $grievance->getMember(), "New grievance", null, strtotime($subDate), "A new grievance was added");
            $this->insertLog($grievanceLog);

            $this->dbConnection->commit();
        } catch (PDOException $e) {

            $this->dbConnection->rollback();
            $insertResult = false;
            die();
        }
        return $insertResult;
    }

    public function getGrievance($paramArray, &$messagecode, &$messagestatus, &$messagetext, $member, $user) {
        $grievance = array();
        $messagecode = 200;
        $messagestatus = "";
        $messagetext = "";
        $settingOk = true;
        $pageFilter = null;
        $memberIdFilter = null;
        $memberNameFilter = null;
        $stepIdFilter = null;
        $stewardIdFilter = null;
        $closedFilter = false;


        foreach ($paramArray as $key => $value) {
            switch ($key) {
                case 'member_id':
                    $memberIdFilter = $value;
                    break;
                case 'member_name':
                    $memberNameFilter = $value;
                    break;
                case 'step_id':
                    $stepIdFilter = $value;
                    break;
                case 'closed':
                    $closedFilter = $value;
                    break;
                case 'steward_id':
                    $stewardIdFilter = $value;
                    break;
                case 'page':
                    $pageFilter = $value;
                    break;
                default:
                    $settingOk = false;
                    break;
            }
            if (!$settingOk) {
                break;
            }
        }
        if ($settingOk) {


            if ($memberIdFilter != null && $memberNameFilter != null) {
                $messagecode = 400;
                $messagestatus = "Error";
                $messagetext = "Incompatible parameter settings";
            } else if ($member != null && (($memberIdFilter == null && $stewardIdFilter == null) || (($memberIdFilter != null && $member->getMemberId() != $memberIdFilter) || ($stewardIdFilter != null && $member->getMemberId() != $stewardIdFilter) ) )) {
                $messagecode = 401;
                $messagestatus = ErrorCodes::$E021['error_code'];
                $messagetext = ErrorCodes::$E021['text'];
            } else {

                try {
                    $fromQuery = "FROM grievance g";
                    $whereQuery = "";

                    if ($memberNameFilter != null) {
                        $memberName = explode(' ', $memberNameFilter);
                        if ($whereQuery == "") {
                            $whereQuery .= " WHERE ";
                        } else {
                            $whereQuery .= " AND ";
                        }

                        $whereQuery .= "g.member_id IN (SELECT id from member_data WHERE ";
                        if (count($memberName) > 0) {
                            $whereQuery .= "First_Name LIKE '%$memberName[0]%' ";
                        }
                        if (count($memberName) > 1) {
                            $whereQuery .= "AND Last_Name LIKE '%$memberName[1]%' ";
                        }

                        $whereQuery .= ")";
                    }

                    $fromQuery .= ", grievance_rel_grievance_step st ";
                    if ($whereQuery == "") {
                        $whereQuery .= " WHERE ";
                    } else {
                        $whereQuery .= " AND ";
                    }

                    $whereQuery .= "st.grievanceid = g.id";

                    if ($stepIdFilter != null) {
                        $whereQuery .= " AND st.stepid = $stepIdFilter AND st.grievanceid NOT IN (SELECT grgs.grievanceid FROM grievance_rel_grievance_step grgs, grievance_step gs, grievance_step gs2 WHERE grgs.stepid=gs.id and gs2.id = $stepIdFilter and gs.step_order> gs2.step_order)";
                    }

                    $close = 0;
                    if ($closedFilter == 'true') {
                        $close = 1;
                    }

                    $whereQuery .= " AND st.statusid IN (SELECT grss.statusid FROM grievance_rel_step_status grss WHERE grss.closestep=$close and st.stepid=grss.stepid)";


                    if ($user != null && $user->getRole() > 2) {
                        $userId = $user->getId();
                        $whereQuery .= " AND st.grievanceid IN (SELECT grgs2.grievanceid FROM grievance_rel_grievance_step grgs2 WHERE grgs2.user_id='$userId')";
                    }

                    if ($stewardIdFilter != null) {
                        $fromQuery .= ", member_data stewardData ";
                        if ($whereQuery == "") {
                            $whereQuery .= " WHERE ";
                        } else {
                            $whereQuery .= " AND ";
                        }

                        $whereQuery .= "g.steward_id = stewardData.id AND stewardData.Member_Id = '$stewardIdFilter'";
                    }

                    if ($memberIdFilter != null) {
                        $fromQuery .= ", member_data membD ";
                        if ($whereQuery == "") {
                            $whereQuery .= " WHERE ";
                        } else {
                            $whereQuery .= " AND ";
                        }
                        $whereQuery .= "g.member_id = membD.id AND membD.Member_Id LIKE '%$memberIdFilter%'";
                    }
                    $limitPage = ";";
                    if (!empty($pageFilter)) {
                        $total = $this->pageLimit * ($pageFilter - 1);
                        $limitPage = " LIMIT $this->pageLimit OFFSET $total;";
                    }
                    $sqlQuery = "SELECT DISTINCT g.id, g.member_id, g.clause_of_contract_violated, g.member_address_1, g.member_address_2, g.member_bureau, g.member_city, g.member_department, g.member_email, g.member_phone, g.member_state, g.member_supervisor, g.member_title, g.member_work_location, g.member_zip_code, g.nature_of_grievance_id, g.nature_of_grievance_other, g.original_date_of_grievance, g.remedy_id, g.remedy_other, g.steward_id, g.control_date, g.submission_date $fromQuery $whereQuery ORDER BY g.original_date_of_grievance ASC$limitPage";

                    $stmt = $this->dbConnection->prepare($sqlQuery);
                    $stmt->execute();
                    $result = $stmt->fetchAll();
                    foreach ($result as $row) {

                        $member = $this->getMemberById($row['member_id']);
                        $steward = $this->getMemberById($row['steward_id']);
                        $grievanceNature = null;
                        if (!empty($row['nature_of_grievance_id'])) {
                            $grievanceNature = $this->getGrievanceNatureById($row['nature_of_grievance_id']);
                        }
                        $grievanceRemedy = null;

                        if (!empty($row['remedy_id'])) {
                            $grievanceRemedy = $this->getGrievanceRemedyById($row['remedy_id']);
                        }

                        $originalDate = strtotime($row['original_date_of_grievance']);
                        $submissionDate = strtotime($row['submission_date']);
                        $controlDate = null;
                        if (!empty($row['control_date'])) {
                            $controlDate = date("M d, Y H:i:s", strtotime($row['control_date']));
                        }

                        $steps = $this->getGrievancesStepsRelation($row['id']);
                        array_push($grievance, new Grievance($row['id'], $member, $row['clause_of_contract_violated'], $row['member_address_1'], $row['member_address_2'], $row['member_bureau'], $row['member_city'], $row['member_department'], $row['member_email'], $row['member_phone'], $row['member_state'], $row['member_supervisor'], $row['member_title'], $row['member_work_location'], $row['member_zip_code'], $grievanceNature, $row['nature_of_grievance_other'], date("M d, Y H:i:s", $originalDate), $grievanceRemedy, $row['remedy_other'], $steps, $steward, $controlDate, date("M d, Y H:i:s", $submissionDate)));
                    }
                } catch (PDOException $e) {

                    die();
                }
            }
        } else {
            $messagecode = 400;
            $messagestatus = "Error";
            $messagetext = "Unexpected Parameter";
        }

        return $grievance;
    }

    public function getMemberByToken($token) {
        $member = null;
        try {
            $stmt = $this->dbConnection->prepare('SELECT `id`, `Company_Prefix`,`Emp_No`, `First_Name`, `Last_Name`, `Role`, `Report_To`, `Member_ID` FROM `member_data` WHERE `Member_Auth_Token` = :token');
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetchAll();
                $member = new Member($result[0]['id'], $result[0]['Company_Prefix'], $result[0]['Emp_No'], $result[0]['First_Name'], $result[0]['Last_Name'], $result[0]['Role'], $result[0]['Report_To'], $result[0]['Member_ID']);
            }
        } catch (PDOException $e) {
            //http_response_code(500);
            die();
        }

        return $member;
    }

    public function getMemberById($id) {
        $member = null;
        try {
            $stmt = $this->dbConnection->prepare('SELECT `id`, `Company_Prefix`,`Emp_No`, `First_Name`, `Last_Name`, `Role`, `Report_To`, `Member_ID` FROM `member_data` WHERE `id` = :id');
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetchAll();
                $member = new Member($result[0]['id'], $result[0]['Company_Prefix'], $result[0]['Emp_No'], $result[0]['First_Name'], $result[0]['Last_Name'], $result[0]['Role'], $result[0]['Report_To'], $result[0]['Member_ID']);
            }
        } catch (PDOException $e) {

            //http_response_code(500);
            die();
        }
        return $member;
    }

    public function getMemberByIdCode($memberId) {
        $member = null;
        try {
            $stmt = $this->dbConnection->prepare('SELECT `id`, `Company_Prefix`,`Emp_No`, `First_Name`, `Last_Name`, `Role`, `Report_To`, `Member_ID` FROM `member_data` WHERE `Member_ID` = :id');
            $stmt->bindParam(':id', $memberId);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetchAll();
                $member = new Member($result[0]['id'], $result[0]['Company_Prefix'], $result[0]['Emp_No'], $result[0]['First_Name'], $result[0]['Last_Name'], $result[0]['Role'], $result[0]['Report_To'], $result[0]['Member_ID']);
            }
        } catch (PDOException $e) {

            //http_response_code(500);
            die();
        }
        return $member;
    }

    public function getUserByToken($token) {
        $user = null;
        try {
            $stmt = $this->dbConnection->prepare('SELECT `id`, `fname`, `lname`, `userrole`, `report_to`, `role`, `email` FROM `users` WHERE `access_key` = :token');

            $stmt->bindParam(':token', $token);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetchAll();

                $user = new User($result[0]['id'], $result[0]['fname'], $result[0]['lname'], $result[0]['userrole'], $result[0]['report_to'], $result[0]['role'], $result[0]['email']);
            }
        } catch (PDOException $e) {
            //http_response_code(500);
            die();
        }


        return $user;
    }

    private function checkMandatoryComplete($grievanceId, $stepId, &$mandatoryStatus) {


        $mandatoryStatus = "";
        $mandatoryComplete = true;
        $statusList = $this->getMandatoryList($stepId);

        if (!(empty($statusList))) {
            foreach ($statusList as $statusId) {
                $stmt = $this->dbConnection->prepare('SELECT `id` FROM `grievance_log` WHERE `grievance_id`= :value1 AND `step_id` = :value2 AND `status_id` = :value3');
                $stmt->bindParam(':value1', $grievanceId);
                $stmt->bindParam(':value2', $stepId);
                $stmt->bindParam(':value3', $statusId);
                $stmt->execute();
                if ($stmt->rowCount() == 0) {
                    $mandatoryComplete = false;
                    $mandatoryStatus = $this->getGrievanceStatusById($statusId)->getStatusName();

                    break;
                }
            }
        }

        return $mandatoryComplete;
    }

    private function getMandatoryList($stepId) {

        $statusList = array();
        $stmt = $this->dbConnection->prepare('SELECT `statusid` FROM `grievance_rel_step_status` WHERE `stepid` = :stepId AND `mandatory`= 1 AND `enable`= 1');

        $stmt->bindParam(':stepId', $stepId);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetchAll();
            foreach ($result as $row) {
                array_push($statusList, $row['statusid']);
            }
        }

        return $statusList;
    }

    public function getUserById($userId) {
        $user = null;
        try {
            $stmt = $this->dbConnection->prepare('SELECT `id`, `fname`, `lname`, `userrole`, `report_to`, `role`, `email` FROM `users` WHERE `id` = :userId');
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetchAll();
                $user = new User($result[0]['id'], $result[0]['fname'], $result[0]['lname'], $result[0]['userrole'], $result[0]['report_to'], $result[0]['role'], $result[0]['email']);
            }
        } catch (PDOException $e) {

            //http_response_code(500);
            die();
        }

        return $user;
    }

    public function insertDocument($grievance, $file, $fileName, $fileSize, $fileExtension, $member, $user, $extraTitle, $extraComment) {
        $grievanceDocument = null;

        try {
            $this->dbConnection->beginTransaction();
            $sqlQuery = 'INSERT INTO `grievance_document`(`grievance_id`, `file_name`, `file_extension`, `file_size`, `date`, `member_id`, `file`, `user_id`) VALUES (:value1,:value2, :value3 ,:value4,:value5,:value6,:value7,:value8)';
            $stmt = $this->dbConnection->prepare($sqlQuery);
            $stmt->bindParam(':value1', $grievance->getId());
            $stmt->bindParam(':value2', $fileName);
            $stmt->bindParam(':value3', $fileExtension);
            $stmt->bindParam(':value4', $fileSize);
            $dateOfDoc = $this->getDateTime();
            $stmt->bindParam(':value5', date("Y-m-d H:i:s", $dateOfDoc));
            if ($member != null) {
                $stmt->bindParam(':value6', $member->getId());
            } else {
                $stmt->bindParam(':value6', $member);
            }
            $stmt->bindParam(':value7', $file);
            if ($user != null) {
                $stmt->bindParam(':value8', $user->getId());
            } else {
                $stmt->bindParam(':value8', $user);
            }

            $stmt->execute();
            $lastid = $this->dbConnection->lastInsertId();
            $compleatePath = $this->getDocumentsPath();


            $grievanceDocumet = new GrievanceDocument($lastid, $fileName, $fileSize, date("Y-m-d H:i:s", $dateOfDoc), $member, $user, $compleatePath);

            $lastStep = count($grievance->getSteps()) - 1;

            $step = $grievance->getSteps()[$lastStep]->getStep();
            $status = $grievance->getSteps()[$lastStep]->getStatus();

            $title = "New Document";
            $comment = "The file $fileName was added";
            if (!empty($extraTitle)) {
                $title = $extraTitle;
            }

            if (!empty($extraComment)) {
                $comment = $extraComment;
            }
            $grievanceLog = new GrievanceLog($grievance->getId(), 0, $step, $status, $member, $title, $user, $dateOfDoc, $comment);

            $this->insertLog($grievanceLog);


            $this->dbConnection->commit();
        } catch (PDOException $e) {
            $grievanceDocument = null;
            $this->dbConnection->rollback();
            //http_response_code(500);
            die();
        }

        return $grievanceDocumet;
    }

    public function getDocumentsPath() {
        return $this->getServerApiUrl() . basename(__DIR__) . "/downloadfile.php?id=";
    }

    public function getGrievanceDocuments($grievanceId, $paramArray, &$messagecode, &$messagestatus, &$messagetext) {
        $grievancesDocuments = array();

        $messagecode = 200;
        $messagestatus = "";
        $messagetext = "";
        $pageFilter = null;

        foreach ($paramArray as $key => $value) {
            switch ($key) {
                case 'page':
                    $pageFilter = $value;
                    break;
                default:
                    break;
            }
        }

        if ($pageFilter != null) {

            try {
                $total = $this->pageLimit * ($pageFilter - 1);

                $stmt = $this->dbConnection->prepare('SELECT `id`, `file_name`, `file_size`, `date`, `member_id`, `user_id` FROM `grievance_document` WHERE grievance_id =:value1 LIMIT ' . $this->pageLimit . ' OFFSET ' . $total);
                $stmt->bindParam(':value1', $grievanceId);

                $stmt->execute();
                $result = $stmt->fetchAll();
                foreach ($result as $row) {
                    $user = $this->getUserById($row['user_id']);
                    $member = $this->getMemberById($row['member_id']);
                    $dateTime = date("Y-m-d H:i:s", strtotime($row['date']));
                    $compleatePath = $this->getDocumentsPath();
                    array_push($grievancesDocuments, new GrievanceDocument($row['id'], $row['file_name'], $row['file_size'], $dateTime, $member, $user, $compleatePath));
                }
            } catch (PDOException $e) {
                $messagecode=500;
                //http_response_code(500);
                die();
            }
        } else {
            $messagecode = 400;
            $messagestatus = "Error";
            $messagetext = "Page parameter is requiered";
        }

        return $grievancesDocuments;
    }

      
    
    public function getGrievanceFile($id, &$extension, &$fileName, &$fileSize) {
        //$grievanceFile = null;
        try {
            $sqlQuery = "SELECT `id`, `file_name`, `file_extension`, `file_size`, `file` FROM `grievance_document` WHERE id = $id";
            $stmt = $this->dbConnection->prepare($sqlQuery);
            $stmt->execute();
            $result = $stmt->fetchAll();
            $extension = $result[0]['file_extension'];
            $grievanceFile = $result[0]['file'];
            $fileName = $result[0]['file_name'];
            $fileSize = $result[0]['file_size'];
        } catch (PDOException $e) {
            //http_response_code(500);
            die();
        }

        return $grievanceFile;
    }

    public function getInfoRequestForm($grievanceId, $optionList, $member, $user, $toName, $fromDate, $designeeName, $designeeId, $reMail, $companyPrefix, $infoByDate, $signatureFile, $otherText) {
                  
        $companyName = $this->getCompanyName($companyPrefix);
       
        $dateOfInfoReq = date('m-d-Y-His', $this->getDateTime());

        $dateOfInfoReqToFile = date('M d, Y', strtotime($fromDate));
        
        $dateToInfoByDate = date('M d, Y', strtotime($infoByDate));
        
        $form = new CreateInfoRequestForm($dateOfInfoReqToFile, $toName, $designeeName, $designeeId, $reMail, $dateToInfoByDate, $signatureFile, $otherText);
                 
        $file = $form->createForm($optionList);

        $doc = null;
        $grievance = $this->getGrievanceById($grievanceId);

        if ($grievance != null) {
                $doc = $this->insertDocument($grievance, $file, "Information Request to " . $companyName . " " . $dateOfInfoReq . ".pdf", strlen($file), "application/pdf", $member, $user, "Information Request Form", "New Information was required");
        }
        return $doc;
    }

    public function getCompanyName($companyPrefix) {
        $companyName = "";
        $sqlQuerty = "SELECT `Company_Name`FROM `company` WHERE `ID_Prefix` = :value1";

        $stmt = $this->dbConnection->prepare($sqlQuerty);
        $stmt->bindParam(':value1', $companyPrefix);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetchAll();
            $companyName = $result[0][0];
        }

        return $companyName;
    }

    public function getGeneralForm($grievanceId, $member, $user) {
        $grievance = $this->getGrievanceById($grievanceId);
        $doc = null;
        if ($grievance != null) {
            $create = new CreateGeneralForm(date('d M, Y', $this->getDateTime()), $grievance, $this);
            $file = $create->createForm();
            $doc = $this->insertDocument($grievance, $file, "General-Grievance-Form.pdf", strlen($file), "application/pdf", $member, $user, "Grievance Form", "A new Grievance Form was generated");
        }

        return $doc;
    }

    public function getAlerts($member, $user) {

        $newAssignment = array();
        $fiveDays = array();
        $filter = "";
        if ($member != null) {
            $filter = " AND a.steward_id =" . $member->getId();
        } else {
            $filter = " AND a.user_id =" . $user->getId();
        }
        try {
            $sqlQuerty = "SELECT a.grievanceid, a.stepid FROM grievance_rel_grievance_step a INNER JOIN ( SELECT grievanceid, MAX(stepid) AS MaxStepId FROM grievance_rel_grievance_step GROUP BY grievanceid) groupedgrgs ON a.grievanceid = groupedgrgs.grievanceid AND a.stepid = groupedgrgs.MaxStepId" . $filter;

            $stmt = $this->dbConnection->prepare($sqlQuerty);

            $stmt->execute();
            $result = $stmt->fetchAll();

            $today = $this->getDateTime();

            $isMonday = date('N', $today) == 1;


            $todayControlDate = new DateTime(date("Y-m-d H:i:s", $today));

            foreach ($result as $row) {
                $grievance = $this->getGrievanceById($row['grievanceid']);
                if (!empty($grievance->getControlDate())) {
                    $grievanceControlDate = new DateTime($grievance->getControlDate());
                    $interval = $grievanceControlDate->diff($todayControlDate, true);

                    if ($interval->days <= 5) {
                        array_push($fiveDays, $grievance);
                    }
                }
                $sqlString = "SELECT `grievance_id`, `step_id`, MIN(`date_time`) as time FROM `grievance_log` WHERE `grievance_id` = :value1 AND `step_id` = :value2";
                $stmt = $this->dbConnection->prepare($sqlString);
                $stmt->bindParam(':value1', $grievance->getId());
                $stmt->bindParam(':value2', $row['stepid']);

                $stmt->execute();

                $newAssigResult = $stmt->fetchAll();
                $newAssigControlDate = new DateTime($newAssigResult[0]['time']);

                $interval = $newAssigControlDate->diff($todayControlDate, true);
                if ($interval->days < 1 || ($isMonday && $interval->days < 3)) {
                    array_push($newAssignment, $grievance);
                }
            }
        } catch (PDOException $e) {
            //http_response_code(500);
            die();
        }
        //
        $grievanceAlerts = new GrievanceAlerts($newAssignment, $fiveDays);
        return $grievanceAlerts;
    }

    public function getUnassigned($paramArray) {

        $grievancesUnassigned = array();

        $messagecode = 200;
        $messagestatus = "";
        $messagetext = "";
        $pageFilter = null;
        foreach ($paramArray as $key => $value) {
            switch ($key) {
                case 'page':
                    $pageFilter = $value;
                    break;
                default:
                    break;
            }
        }

        if ($pageFilter != null) {
            try {

                $total = $this->pageLimit * ($pageFilter - 1);
                $sqlQuerty = "SELECT DISTINCT `grievanceid` FROM `grievance_rel_grievance_step` WHERE `steward_id` is null and `user_id` is null LIMIT $this->pageLimit OFFSET $total";

                $stmt = $this->dbConnection->prepare($sqlQuerty);

                $stmt->execute();
                $result = $stmt->fetchAll();

                foreach ($result as $row) {
                    $grievance = $this->getGrievanceById($row['grievanceid']);

                    array_push($grievancesUnassigned, $grievance);
                }
            } catch (PDOException $e) {

                $messagecode = 401;
                $messagestatus = "Error";
                $messagetext = "Invalid parameter value";
                die();
            }
        } else {
            $messagecode = 400;
            $messagestatus = "Error";
            $messagetext = "Page parameter is requiered";
        }
        return $grievancesUnassigned;
    }

    public function automaticUpdate() {

        try {
           
            $this->dbConnection->beginTransaction();
            $newDate = null;
            $fromDate = $this->getDateTime();
            $stringFilter = date("Y-m-d H:i:s", $fromDate);

            $sqlQuery = "SELECT `id` FROM `grievance` WHERE `control_date` < '$stringFilter'";
         
            $stmt = $this->dbConnection->prepare($sqlQuery);
            // $stmt->bindParam(':value1', );
            $stmt->execute();
            $result = $stmt->fetchAll();
             
            foreach ($result as $row) {
               
                $grievanceId = $row['id'];
      
                $nextassigned = null;
                $gsr = $this->getGrievancesStepsRelation($grievanceId);
 
                $tot = count($gsr);
               
                $stRel = $gsr[$tot - 1];
              
                $prevStatus = $this->getGrievanceStatusById($this->statusAutPromoteId);
                $stRel->setStatus($prevStatus);
                
                $this->updateStepRelation($stRel, $grievanceId);
               
        
                if ($tot == $this->step3Id) {
                    $nextassigned = $stRel->getUser()->getId();
                    $nextStatus = $this->createStepArb($grievanceId, $nextassigned);
                } else if ($tot == $this->step1Id) {
                     
                    if ($stRel->getSteward()->getReportTo() != null && $this->getUserById($stRel->getSteward()->getReportTo())!=null ) {
                         
                        $nextassigned =$stRel->getSteward()->getReportTo(); 

                        $nextStatus = $this->createStep2($grievanceId, $nextassigned);
                        
                    }
                } else if ($tot == $this->step2Id ) {
                    
                    if ($stRel->getUser()->getReportTo() != null && $this->getUserById($stRel->getUser()->getReportTo())!=null) {
                        
                        $nextassigned =$stRel->getUser()->getReportTo();
                        $nextStatus = $this->createStep3($grievanceId, $nextassigned);
                    }
                   
                }
                

                if($nextassigned!=null)
                {
                    $next = $this->getControlTime();
                    $controlDate = new DateTime(date("Y-m-d H:i:s", $this->getDateTime()));
                    $controlDate->add(new DateInterval('PT' . $next . 'S'));
                    $newDate = date("Y-m-d H:i:s", $controlDate->getTimestamp()); 
                    
                }
                else
                {
                   
                     $nextStatus = $this->createStepNeedReview($grievanceId, $tot+1);
                   
                }
                
      
                $this->updateGrievanceControlDate($grievanceId, $newDate);
                
                
                $userLog = $this->getUserById($this->getSystemUserId());

                $title = "Automatic Promote";
                $description = "The step was promoted by the system";
                $dateTime = $this->getDateTime();
                $newStep = $this->getGrievanceStepById($tot + 1);
                $newStatus = $this->getGrievanceStatusById($nextStatus);
   
                
                $grievanceLog = new GrievanceLog($grievanceId, 0, $newStep, $newStatus, null, $title, $userLog, $dateTime, $description);
                
                $this->insertLog($grievanceLog);

                
            }

            $this->dbConnection->commit();
        } catch (PDOException $e) {
            $this->dbConnection->rollback();
            //http_response_code(500);
            die();
        }
    }

    public function updateStepAssignment($grievanceId, $jsonArray, $userLog, &$messagecode, &$messagestatus, &$messagetext) {

        $userId = null;
        $stepId = null;
        $messagecode = 200;
        $messagestatus = "";
        $messagetext = "";
        foreach ($jsonArray as $key => $value) {
            switch ($key) {
                case 'userId':
                    $userId = $value;
                    break;
                case 'stepId':
                    $stepId = $value;
                    break;
                case 'memberId':
                    $userId = $value;
                    break;
            }
        }

        if ($userId == null || $stepId == null) {
            $messagecode = 401;
            $messagestatus = "Error";
            $messagetext = "Invalid parameters";
        } else {
            $grievance = $this->getGrievanceById($grievanceId);
            if ($grievance == null) {
                $messagecode = 401;
                $messagestatus = "Error";
                $messagetext = "Invalid grievance Id";
            } else {
                if ($stepId == 1) {
                    $member = $this->getMemberByIdCode($userId);
                    if ($member != null && strcasecmp($member->getMemberRol(), 'Steward') == 0) {
                        try {

                            $stmt = $this->dbConnection->prepare('SELECT `grievanceid`, `stepid` FROM `grievance_rel_grievance_step` WHERE grievanceid= :value1 and stepid = :value2');
                            $stmt->bindParam(':value1', $grievanceId);
                            $stmt->bindParam(':value2', $stepId);

                            $stmt->execute();

                            if ($stmt->rowCount() > 0) {

                                $this->dbConnection->beginTransaction();
                                $sqlQuery = "UPDATE `grievance` SET `steward_id`=:value2 WHERE `id`=:value1";
                                $stmt = $this->dbConnection->prepare($sqlQuery);
                                $stmt->bindParam(':value1', $grievanceId);
                                $stmt->bindParam(':value2', $member->getId());
                                $stmt->execute();

                                $sqlQuery = "UPDATE `grievance_rel_grievance_step` SET `steward_id`=:value1 WHERE `grievanceid`=:value2 AND `stepid`=:value3 ";
                                $stmt = $this->dbConnection->prepare($sqlQuery);
                                $stmt->bindParam(':value1', $member->getId());
                                $stmt->bindParam(':value2', $grievanceId);
                                $stmt->bindParam(':value3', $stepId);

                                $stmt->execute();

                                $step = $grievance->getSteps()[$stepId - 1]->getStep();
                                $status = $grievance->getSteps()[$stepId - 1]->getStatus();
                                $dateOfLog = $this->getDateTime();
                                $grievanceLog = new GrievanceLog($grievanceId, 0, $step, $status, null, "Update Assignment", $userLog, $dateOfLog, "A new steward was assigned to this Grievance");
                                $this->insertLog($grievanceLog);
                                $this->dbConnection->commit();
                            } else {
                                $messagecode = 401;
                                $messagestatus = "Error";
                                $messagetext = "Invalid Grievance step";
                            }
                        } catch (PDOException $e) {

                            $this->dbConnection->rollback();
                            $messagecode = 500;
                            die();
                        }
                    } else {
                        $messagecode = 401;
                        $messagestatus = "Error";
                        $messagetext = "Invalid Steward code";
                    }
                } else if ($stepId < 5) {

                    $user = $this->getUserById($userId);
                    if ($user != null) {

                        try {

                            $stmt = $this->dbConnection->prepare('SELECT `grievanceid`, `stepid`, `statusid` FROM `grievance_rel_grievance_step` WHERE grievanceid= :value1 and stepid = :value2');
                            $stmt->bindParam(':value1', $grievanceId);
                            $stmt->bindParam(':value2', $stepId);

                            $stmt->execute();


                            if ($stmt->rowCount() > 0) {
                                $result = $stmt->fetchAll();
                                $newStatus = $result[0]['statusid'];
                                $this->dbConnection->beginTransaction();
                                if ($newStatus == 10000) {

                                    if ($stepId == 2) {
                                        $newStatus = 9;
                                    } else if ($stepId == 3) {
                                        $newStatus = 11;
                                    }
                                    $grievance->getSteps()[$stepId - 1]->setStatus($this->getGrievanceStatusById($newStatus));
                                    $sqlQuery = "UPDATE `grievance` SET `control_date`=:value2 WHERE `id`=:value1";
                                    $stmt = $this->dbConnection->prepare($sqlQuery);
                                    $stmt->bindParam(':value1', $grievanceId);

                                    $controlDate = new DateTime(date("Y-m-d H:i:s", $this->getDateTime()));
                                    $next = $this->getControlTime();
                                    $controlDate->add(new DateInterval('PT' . $next . 'S'));
                                    $stmt->bindParam(':value2', date("Y-m-d H:i:s", $controlDate->getTimestamp()));
                                    $stmt->execute();
                                }
                                $sqlQuery = "UPDATE `grievance_rel_grievance_step` SET `user_id`=:value1, `statusid`=:value4 WHERE `grievanceid`=:value2 AND `stepid`=:value3 ";
                                $stmt = $this->dbConnection->prepare($sqlQuery);
                                $stmt->bindParam(':value1', $user->getId());
                                $stmt->bindParam(':value2', $grievanceId);
                                $stmt->bindParam(':value3', $stepId);
                                $stmt->bindParam(':value4', $newStatus);

                                $stmt->execute();

                                $step = $grievance->getSteps()[$stepId - 1]->getStep();
                                $status = $grievance->getSteps()[$stepId - 1]->getStatus();
                                $dateOfLog = $this->getDateTime();
                                $grievanceLog = new GrievanceLog($grievanceId, 0, $step, $status, null, "Update Assignment", $userLog, $dateOfLog, "A new user was assigned to this Grievance");
                                $this->insertLog($grievanceLog);
                                $this->dbConnection->commit();
                            } else {
                                $messagecode = 401;
                                $messagestatus = "Error";
                                $messagetext = "Invalid Grievance step";
                            }
                        } catch (PDOException $e) {

                            $this->dbConnection->rollback();
                            $messagecode = 500;
                            die();
                        }
                    } else {
                        $messagecode = 401;
                        $messagestatus = "Error";
                        $messagetext = "Invalid user code";
                    }
                } else {
                    $messagecode = 401;
                    $messagestatus = "Error";
                    $messagetext = "Invalid Step id";
                }
            }
        }
    }

// Create steps.



    private function createStep($grievanceId, $stepId, $userId, $statusId) {
        $nullValue = null;
        $stringEmpty = "";
        $falseValue = false;

     try{

        $sRelGrievStep = $this->dbConnection->prepare('INSERT INTO `grievance_rel_grievance_step`(`grievanceid`, `stepid`, `dateofgrievancemeeting`, `nameofdesignee`, `nameofcompanyrepresentative`, `nameofotherspresent`, `wasinformationrequestedinwritingbydesignee`, `wasinformationprovidedbycompanyrep`, `didcompanyrepprovidewrittenresponse`, `statusid`, `steward_id`, `user_id`, `written_response_date`) VALUES (:value1,:value2,:value3,:value4,:value5,:value6,:value7,:value8,:value9,:value10,:value11,:value12,:value13)');
        $sRelGrievStep->bindParam(':value1', $grievanceId);
        $sRelGrievStep->bindParam(':value2', $stepId);
        $sRelGrievStep->bindParam(':value3', $nullValue);
        $sRelGrievStep->bindParam(':value4', $stringEmpty);
        $sRelGrievStep->bindParam(':value5', $stringEmpty);
        $sRelGrievStep->bindParam(':value6', $stringEmpty);
        $sRelGrievStep->bindParam(':value7', $falseValue);
        $sRelGrievStep->bindParam(':value8', $falseValue);
        $sRelGrievStep->bindParam(':value9', $falseValue);
        $sRelGrievStep->bindParam(':value10', $statusId);
        if ($stepId == 1) {
            $sRelGrievStep->bindParam(':value11', $userId);
            $sRelGrievStep->bindParam(':value12', $nullValue);
        } else {
             
            $sRelGrievStep->bindParam(':value11', $nullValue);
            $sRelGrievStep->bindParam(':value12', $userId);
        }
        
        $sRelGrievStep->bindParam(':value13', $nullValue);
        
        $sRelGrievStep->execute();
     }
     catch (PDOException $e) {

            die();
        }

    }



    private function createStep1($grievanceId, $stewardId) {
        $newStatus = 1;
        $this->createStep($grievanceId, $this->step1Id, $stewardId, $newStatus);
        return $newStatus;
    }

    private function createStep2($grievanceId, $userId) {
        $newStatus = 9;
        $this->createStep($grievanceId, $this->step2Id, $userId, $newStatus);
         
        return $newStatus;
    }

    private function createStep3($grievanceId, $userId) {
        $newStatus = 11;
        $this->createStep($grievanceId, $this->step3Id, $userId, $newStatus);
        return $newStatus;
    }

    private function createStepArb($grievanceId, $userId) {
        $newStatus = 13;
        $this->createStep($grievanceId, $this->stepArbId, $userId, $newStatus);
        return $newStatus;
    }

    private function createStepAutPromote($grievanceId, $stepId, $userId) {
        $newStatus = $this->statusAutPromoteId;
        $this->createStep($grievanceId, $stepId, $userId, $newStatus);
        return $newStatus;
    }

    private function createStepNeedReview($grievanceId, $stepId) {
        $newStatus = $this->statusNeedReviewId;
        $this->createStep($grievanceId, $stepId, null, $newStatus);
        return $newStatus;
    }

    private function isTheLastStep($grievanceId, $stepId) {

        $returnValue = true;
        try {
            $nextStep = ($stepId + 1);
            $stmt = $this->dbConnection->prepare("SELECT `grievanceid`, `stepid` FROM `grievance_rel_grievance_step` WHERE `grievanceid` = :value1 AND `stepid` = :value2");
            $stmt->bindParam(':value1', $grievanceId);
            $stmt->bindParam(':value2', $nextStep);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $returnValue = false;
            }
        } catch (PDOException $e) {

            //http_response_code(500);
            die();
        }

        return $returnValue;
    }

    private function getLastStatusId($grievanceId) {
        $lastStatus = 0;
        try {
            $stmt = $this->dbConnection->prepare("SELECT a.grievanceid, a.stepid, a.statusid FROM grievance_rel_grievance_step a INNER JOIN ( SELECT grievanceid, MAX(stepid) AS MaxStepId FROM grievance_rel_grievance_step GROUP BY grievanceid) groupedgrgs ON a.grievanceid = groupedgrgs.grievanceid AND a.stepid = groupedgrgs.MaxStepId AND a.grievanceid=:value1");
            $stmt->bindParam(':value1', $grievanceId);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetchAll();
                $lastStatus = $result[0]['statusid'];
            }
        } catch (PDOException $e) {

            //http_response_code(500);
            die();
        }
        return $lastStatus;
    }

        private function getStepLastStatusId($grievanceId, $stepId) {
        $lastStatus = 0;
        try {
            $stmt = $this->dbConnection->prepare("SELECT grievanceid, stepid, statusid FROM grievance_rel_grievance_step WHERE grievanceid=:value1 AND stepid = :value2");
            $stmt->bindParam(':value1', $grievanceId);
            $stmt->bindParam(':value2', $stepId);

            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetchAll();
                $lastStatus = $result[0]['statusid'];
            }
        } catch (PDOException $e) {

            //http_response_code(500);
            die();
        }
        return $lastStatus;
    }

    private function updateGrievanceControlDate($grievanceId, $newDate) {

        $sqlString = "UPDATE `grievance` SET `control_date`= :value1 WHERE id= :value2";
        $stmt = $this->dbConnection->prepare($sqlString);
        $stmt->bindParam(':value1', $newDate);
        $stmt->bindParam(':value2', $grievanceId);
        $stmt->execute();
    }

    private function isValidStatus($stepId, $statusId) {
        $isValid = false;
        if(($stepId==$this->step1Id && $statusId==1) || ($stepId==$this->step2Id && $statusId==9) || ($stepId==$this->step3Id && $statusId==11) || ($stepId==$this->stepArbId && $statusId==13))
        {
            $isValid = false;
        }
        else
        {
            $gss = $this->getGrievanceStepStatus($stepId, $statusId);
            if ($gss != null) {
                $isValid = true;
            }
        }
        return $isValid;
    }

    public function getGrievanceStepStatus($stepId, $statusId) {

        $grievancesStepStatus = null;
        try {

            $stmt = $this->dbConnection->prepare("SELECT `stepid`, `statusid`, `enable`, `mandatory`, `closestep`, `promotestep`, `status_order`, `required_data` FROM `grievance_rel_step_status` WHERE stepid= :value1 AND statusid =:value2");
            $stmt->bindParam(':value1', $stepId);
            $stmt->bindParam(':value2', $statusId);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetchAll();
                $step = $this->getGrievanceStepById($result[0]['stepid']); //, $result[0]['steporder'], $result[0]['stepdescription']);
                $status = $this->getGrievanceStatusById($result[0]['statusid']);
                $grievancesStepStatus = new GrievanceStepStatus($step, $status, $result[0]['mandatory'], $result[0]['closestep'], $result[0]['promotestep'], $result[0]['required_data'], $result[0]['status_order']);
            }
        } catch (PDOException $e) {
            //http_response_code(500);
            die();
        }

        return $grievancesStepStatus;
    }

    private function userCanEditStep($grievanceId, $stepId, $member, $user) {
        $edit = false;
        if ($user != null && $user->getRole() < 3) {
            $edit = true;
        } else if ($user != null) {
            $sqlString = "SELECT `grievanceid` FROM `grievance_rel_grievance_step` WHERE grievanceid = :value1 and stepid = :value2 and user_id = :value3";
            $stmt = $this->dbConnection->prepare($sqlString);
            $stmt->bindParam(':value1', $grievanceId);
            $stmt->bindParam(':value2', $stepId);
            $stmt->bindParam(':value3', $user->getId());
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $edit = true;
            }
        } else if ($member != null) {
            $sqlString = "SELECT `grievanceid` FROM `grievance_rel_grievance_step` WHERE grievanceid = :value1 and stepid = :value2 and steward_id = :value3";
            $stmt = $this->dbConnection->prepare($sqlString);
            $stmt->bindParam(':value1', $grievanceId);
            $stmt->bindParam(':value2', $stepId);
            $stmt->bindParam(':value3', $member->getId());
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $edit = true;
            }
        }
        return $edit;
    }

    public function updateGrievanceStep($grievanceId, $jsonArray, $memberLog, $userLog, &$messagecode, &$messagestatus, &$messagetext) {
        $updateStep = false;
        $updateControDate = false;
        $newControlDate = null;
        $messagecode = 200;
        $messagestatus = "";
        $messagetext = "";
        $grievance = $this->getGrievanceById($grievanceId);

        if ($grievance != null) {
            $currentStatusId = $this->getLastStatusId($grievanceId);
            $currentStepId = count($grievance->getSteps());
            //$newStep = null;
            $stepRelation = new GrievanceStepRelation();
            if ($stepRelation->setFromJsonArray($jsonArray)) {

                //The following 4 lines are necesary becasue the json could contains only the step and status ids

                $step = $stepRelation->getStep()->getId();
                $nextStep = $step+1;
                $status = $stepRelation->getStatus()->getId();
                if ($this->isValidStatus($step, $status)) {

                    if ($currentStepId == $this->stepArbId) {
                        $updateStep = true;
                    } else {
                        if ($this->isTheLastStep($grievanceId, $currentStepId)) {
                            if ($this->userCanEditStep($grievanceId, $currentStepId, $memberLog, $userLog)) {
                                if ($this->getLastStatusId($grievanceId) != $this->statusNeedReviewId) {
                                    $newGss = $this->getGrievanceStepStatus($step, $status);
                                    if ($newGss->isCloseStep()) {
                                        $updateStep = true;
                                        $updateControDate = true;
                                    } else {
                                        $curretGss = $this->getGrievanceStepStatus($currentStepId, $currentStatusId);
                                        if ($curretGss->getStatusOrder() == 1) {
                                            if ($newGss->getStatusOrder() == 2) {
                                                $updateStep = true;
                                                $updateControDate = true;
                                                $newControlDate = new DateTime(date("Y-m-d H:i:s", $this->getDateTime()));
                                                $next = $this->getControlTime();
                                                $newControlDate->add(new DateInterval('PT' . $next . 'S'));

                                            } else {
                                                $messagestatus = "Error";
                                                $messagetext = "Invalid new step status";
                                            }
                                        } else {

                                            if ($newGss->isPromoteStep()) {
                                                if ($this->checkMandatoryComplete($grievanceId, $currentStepId, $mandatoryStatus)) {
                                                    if ($nextStep == $this->stepArbId) {
                                                        $updateStep = true;
                                                        $updateControDate = true;
                                                        $newAssigned = $grievance->getSteps()[$currentStepId - 1]->getUser()->getId();
                                                    } else {
                                                        if ($currentStepId == $this->step1Id) {
                                                            $newAssigned = $grievance->getSteps()[$currentStepId - 1]->getSteward()->getReportTo();
                                                        } else {
                                                            $newAssigned = $grievance->getSteps()[$currentStepId - 1]->getUser()->getReportTo();
                                                        }

                                                        if ($newAssigned != null) {
                                                            if ($stepRelation->getCompanyProvideWrittenResponse()) {
                                                                $updateStep = true;
                                                                $updateControDate = true;
                                                                $newControlDate = new DateTime(date("Y-m-d H:i:s", $stepRelation->getWrittenResponseDate()));
                                                                $next = $this->getControlTime();
                                                                $newControlDate->add(new DateInterval('PT' . $next . 'S'));
                                                            } else {
                                                                $updateStep = true;
                                                                $updateControDate = true;
                                                                $newControlDate = new DateTime(date("Y-m-d H:i:s", $grievance->getControlDate()));
                                                                $next = $this->getControlTime();
                                                                $newControlDate->add(new DateInterval('PT' . $next . 'S'));
                                                            }
                                                        } else {
                                                            $messagestatus = "Error";
                                                            $messagetext = "The current step cannot be promoted. The new step would be unassigned";
                                                        }
                                                    }
                                                } else {
                                                    $messagestatus = "Error";
                                                    $messagetext = "The step can not be promoted. The following status is mandatory: " . $mandatoryStatus;
                                                }
                                            } else {

                                                $updateStep = true;
                                            }
                                        }
                                    }
                                } else {
                                    $messagestatus = "Error";
                                    $messagetext = "This grievance has unassigned step";
                                }
                            } else {
                                $messagestatus = "Error";
                                $messagetext = "Invalid credentials to modify this step";
                            }
                        } else {
                            if ($userLog != null && $userLog->getRole() < 3) {
                                $updateStep = true;
                            } else {
                                $messagestatus = "Error";
                                $messagetext = "Invalid credentials to modify this step";
                            }
                        }
                    }

                    if ($updateStep) {
                        try {

                            $this->dbConnection->beginTransaction();
                            $this->updateStepRelation($stepRelation, $grievanceId);

                            if ($updateControDate == true) {
                                if ($newControlDate!=null)
                                {
                                    $this->updateGrievanceControlDate($grievanceId, date("Y-m-d H:i:s", $newControlDate->getTimestamp()));
                                }
                                else
                                {
                                    $this->updateGrievanceControlDate($grievanceId, null);
                                }
                            }


                            $title = "Update Step";
                            $description = "The grievance step was updated";

                            $dateTime = $this->getDateTime();
                            $auxstatus = $this->getGrievanceStatusById($status);
                            $auxstep = $this->getGrievanceStepById($currentStepId);

                            $grievanceLog = new GrievanceLog($grievanceId, 0, $auxstep, $auxstatus, $memberLog, $title, $userLog, $dateTime, $description);

                            $this->insertLog($grievanceLog);

                            if ($newGss!=null && $newGss->isPromoteStep()) {

                                if ($step == $this->step1Id) {
                                    $this->createStep2($grievanceId, $newAssigned);
                                } else if ($step == $this->step2Id) {
                                    $this->createStep3($grievanceId, $newAssigned);
                                } else if ($step == $this->step3Id) {
                                    $this->createStepArb($grievanceId, $newAssigned);
                                }

                                $newTitle = "New Step";
                                $newDescription = "New step was added";
                                $auxstep = $this->getGrievanceStepById($nextStep);
                                $newStepStatus = $this->getGrievanceStepStatusByStep($nextStep);

                                $newStepGrievanceLog = new GrievanceLog($grievanceId, 0, $auxstep, $newStepStatus[0]->getStatus(), $memberLog, $newTitle, $userLog, $dateTime, $newDescription);

                                $this->insertLog($newStepGrievanceLog);

                            }

                            $this->dbConnection->commit();
                        } catch (PDOException $e) {

                            $this->dbConnection->rollback();
                            $messagecode = 500;
                            //http_response_code(500);
                            die();
                        }
                    } else {
                        $messagecode = 401;
                    }
                } else {
                    $messagecode = 401;
                    $messagestatus = "Error";
                    $messagetext = "Invalid new step status";
                }
            } else {
                $messagecode = 400;
                $messagestatus = "Error";
                $messagetext = "Unexpected Json value";
            }
        } else {
            $messagecode = 401;
            $messagestatus = "Error";
            $messagetext = "Invlaid Grievance Id";
        }
        if ($messagecode==200)
        {
            $grievance = $this->getGrievanceById($grievanceId);
        }
        else
        {
            $grievance = null;
        }

        return $grievance;
    }

    public function deleteGrievanceStep($grievanceId, $userLog, &$messagecode, &$messagestatus, &$messagetext) {

        $messagecode = 200;
        $messagestatus = "";
        $messagetext = "";

        $grievance = $this->getGrievanceById($grievanceId);

        if ($grievance != null) {

            $currentStepId = count($grievance->getSteps());
            if ($currentStepId == $this->step1Id) {
                $messagecode = 401;
                $messagestatus = "Error";
                $messagetext = "The Step 1 cannot be deleted";
            } else {
                $previusStep = $currentStepId - 1;
                $previusStatus = $this->getStepLastStatusId($grievanceId, $previusStep);

                try {
                    $this->dbConnection->beginTransaction();

                    $oldStatus = $this->getLastStatusId($grievanceId);

                    $this->deleteStep($grievanceId, $currentStepId);

                    $gsr = $this->getGrievancesStepsRelation($grievanceId);

                    $tot = count($gsr);

                    $stRel = $gsr[$tot - 1];
                    $logDate = null;
                    $proxStatusId = 2;

                    if ($previusStep == $this->step1Id) {
                        $logDate = $this->getStatusStepDate($grievanceId, $previusStep, 2);
                        $proxStatusId = 2;
                    } else if ($previusStep == $this->step2Id) {
                        $logDate = $this->getStatusStepDate($grievanceId, $previusStep, 10);
                        $proxStatusId = 10;
                    } else if ($previusStep == $this->step3Id) {
                        $logDate = $this->getStatusStepDate($grievanceId, $previusStep, 12);
                        $proxStatusId = 12;
                    }
                    if ($logDate != null) {
                        $proxStatus = $this->getGrievanceStatusById($proxStatusId);
                    } else {
                        $proxStatus = $this->getGrievanceStatusById($proxStatusId - 1);
                    }

                    $stRel->setStatus($proxStatus);

                    $next = $this->getControlTime();

                    if ($previusStatus == $this->statusAutPromoteId) {

                        $controlDate = new DateTime(date("Y-m-d H:i:s", $this->getDateTime()));
                        $controlDate->add(new DateInterval('PT' . $next . 'S'));

                    } else {

                        $controlDate = new DateTime($logDate);
                        $controlDate->add(new DateInterval('PT' . $next . 'S'));
                    }

                    $this->updateStepRelation($stRel, $grievanceId);
                    $this->updateGrievanceControlDate($grievanceId, date("Y-m-d H:i:s", $controlDate->getTimestamp()));

                    $deleteTitle = "Deleted Step";
                    $deleteDescription = "The last step was deleted";
                    $dateTime = $this->getDateTime();
                    $auxstep = $this->getGrievanceStepById($currentStepId);
                    $auxStatus = $this->getGrievanceStatusById($oldStatus);
                    $deleteStepGrievanceLog = new GrievanceLog($grievanceId, 0, $auxstep, $auxStatus, null, $deleteTitle, $userLog, $dateTime, $deleteDescription);
                    $this->insertLog($deleteStepGrievanceLog);


                    $newTitle = "Status udpated";
                    $newDescription = "New status was assinged";

                    $newStep = $this->getGrievanceStepById($previusStep);
                    $updateStepGrievanceLog = new GrievanceLog($grievanceId, 0, $newStep, $proxStatus, null, $newTitle, $userLog, $dateTime, $newDescription);
                    $this->insertLog($updateStepGrievanceLog);

                    $this->dbConnection->commit();
                } catch (PDOException $e) {
                    $this->dbConnection->rollback();
                    $messagecode = 500;
                    die();
                }
            }
        } else {
            $messagecode = 401;
            $messagestatus = "Error";
            $messagetext = "Invlaid Grievance Id";
        }
        if ($messagecode == 200) {
            $grievance = $this->getGrievanceById($grievanceId);
        } else {
            $grievance = null;
        }

        return $grievance;
    }

    private function deleteStep($grievanceId, $stepId)
     {
        $sRelGrievStep = $this->dbConnection->prepare("DELETE FROM `grievance_rel_grievance_step` WHERE `grievanceid` = :value1 AND `stepid` = :value2");
        $sRelGrievStep->bindParam(':value1', $grievanceId);
        $sRelGrievStep->bindParam(':value2', $stepId);
        $sRelGrievStep->execute();

     }


      public function getStatusStepDate($grievanceId, $stepId, $statusId) {

        $dateTime = null;
        $sqlQuery = "SELECT `step_id`, `status_id`, `date_time` FROM `grievance_log` WHERE grievance_id = $grievanceId AND step_id = $stepId AND status_id = $statusId";
        $stmt = $this->dbConnection->prepare($sqlQuery);

        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetchAll();
            $dateTime = $result[0]['date_time'];
        }
        return $dateTime;
    }

    //Auxiliar to web

    public function getAllDocuments($grievanceId) {
        $grievancesDocuments = array();

        try {

            $stmt = $this->dbConnection->prepare('SELECT `id`, `file_name`, `file_size`, `date`, `member_id`, `user_id` FROM `grievance_document` WHERE grievance_id =:value1');
            $stmt->bindParam(':value1', $grievanceId);

            $stmt->execute();
            $result = $stmt->fetchAll();
            foreach ($result as $row) {
                $user = $this->getUserById($row['user_id']);
                $member = $this->getMemberById($row['member_id']);
                $dateTime = date("Y-m-d H:i:s", strtotime($row['date']));
                $compleatePath = $this->getDocumentsPath();
                array_push($grievancesDocuments, new GrievanceDocument($row['id'], $row['file_name'], $row['file_size'], $dateTime, $member, $user, $compleatePath));
            }
        } catch (PDOException $e) {

            die();
        }


        return $grievancesDocuments;
    }

    public function getAllLogs($grievanceId) {
        $grievancesLogs = array();

        try {

            $sqlQuery = "SELECT `id`, `step_id`,`title`, `status_id`, `date_time`, `description`, `member_id`, `user_id` FROM `grievance_log` WHERE grievance_id = $grievanceId";
            $stmt = $this->dbConnection->prepare($sqlQuery);
            $stmt->execute();

            $result = $stmt->fetchAll();

            foreach ($result as $row) {

                $step = $this->getGrievanceStepById($row['step_id']);
                $user = $this->getUserById($row['user_id']);
                $status = $this->getGrievanceStatusById($row['status_id']);

                $steward = $this->getMemberById($row['member_id']);
                array_push($grievancesLogs, new GrievanceLog($grievanceId, $row['id'], $step, $status, $steward, $row['title'], $user, date("M d, Y H:i:s", strtotime($row['date_time'])), $row['description']));
            }
        } catch (PDOException $e) {

            die();
        }

        return $grievancesLogs;
    }

    public function getStewardList() {
        $stewardList = array();
        
        try {
            $stmt = $this->dbConnection->prepare("SELECT `id`, `Company_Prefix`,`Emp_No`, `First_Name`, `Last_Name`, `Role`, `Report_To`, `Member_ID` FROM `member_data` WHERE `Role` = 'Steward'");
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetchAll();
                foreach ($result as $row) {
                    array_push($stewardList, new Member($row['id'], $row['Company_Prefix'], $row['Emp_No'], $row['First_Name'], $row['Last_Name'], $row['Role'], $row['Report_To'], $row['Member_ID']));
                }
            }
        } catch (PDOException $e) {

            //http_response_code(500);
            die();
        }


        return $stewardList;
    }
    
    public function getAgentList() {
        $userList = array();
        
        try {
            
            $stmt = $this->dbConnection->prepare("SELECT `id`, `fname`, `lname`, `userrole`, `report_to`, `role`, `email` FROM `users`  WHERE `userrole` = 'agent'");
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetchAll();
                foreach ($result as $row) {
                    array_push($userList, new User($row['id'], $row['fname'], $row['lname'], $row['userrole'], $row['report_to'], $row['role'], $row['email']));
                }
            }
        } catch (PDOException $e) {

            //http_response_code(500);
            die();
        }

        return $userList;

    }
    
        public function getSeniorAgentList() {
        $userList = array();
        
        try {
            
            $stmt = $this->dbConnection->prepare("SELECT `id`, `fname`, `lname`, `userrole`, `report_to`, `role`, `email` FROM `users`  WHERE `userrole` = 'senioragent'");
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetchAll();
                foreach ($result as $row) {
                     array_push($userList, new User($row['id'], $row['fname'], $row['lname'], $row['userrole'], $row['report_to'], $row['role'], $row['email']));
                }
            }
        } catch (PDOException $e) {

            //http_response_code(500);
            die();
        }

        return $userList;

    }

    
}
