<?php
require 'DBQuery.php';
require '../db.php';
require 'ActionResponse.php';

class GrievanceAction extends ActionResponse {
    
    private $query;

    public function __construct() {
        $this->query = new DBQuery(getDB());
    }

    function getGrievancesSteps() {

        if (isset($_GET['id'])) {
            $this->response(401, ErrorCodes::$E0100['error_code'], ErrorCodes::$E0100['text']);
        } else {
            if (isset(getallheaders()['Member-Auth-Token'])) {
                $member = $this->query->getMemberByToken(getallheaders()['Member-Auth-Token']);
            }
            if (isset(getallheaders()['Admin-Auth-Token'])) {
                $user = $this->query->getUserByToken(getallheaders()['Admin-Auth-Token']);
            }
            if ($member != null || $user != null) {
                $grivanceS = $this->query->getGrievanceSteps();
                echo json_encode($grivanceS, JSON_PRETTY_PRINT);
            } else {
                $this->response(400, "Error", "Invalid token");
            }
        }
    }

    function getGrievancesNature() {

        if (isset($_GET['id'])) {
            $this->response(401, ErrorCodes::$E0100['error_code'], ErrorCodes::$E0100['text']);
        } else {
            if (isset(getallheaders()['Member-Auth-Token'])) {
                $member = $this->query->getMemberByToken(getallheaders()['Member-Auth-Token']);
            }
            if (isset(getallheaders()['Admin-Auth-Token'])) {
                $user = $this->query->getUserByToken(getallheaders()['Admin-Auth-Token']);
            }
            if ($member != null || $user != null) {
                $grivanceN = $this->query->getGrievanceNature();
                echo json_encode($grivanceN, JSON_PRETTY_PRINT);
            } else {
                $this->response(400, "Error", "Invalid token");
            }
        }
    }

    function getGrievancesRemedy() {
        if (isset($_GET['id'])) {
            $this->response(401, ErrorCodes::$E0100['error_code'], ErrorCodes::$E0100['text']);
        } else {
            if (isset(getallheaders()['Member-Auth-Token'])) {
                $member = $this->query->getMemberByToken(getallheaders()['Member-Auth-Token']);
            }
            if (isset(getallheaders()['Admin-Auth-Token'])) {
                $user = $this->query->getUserByToken(getallheaders()['Admin-Auth-Token']);
            }
            if ($member != null || $user != null) {
                $grivanceR = $this->query->getGrievanceRemedy();
                echo json_encode($grivanceR, JSON_PRETTY_PRINT);
            } else {
                $this->response(400, "Error", "Invalid token");
            }
        }
    }

    function getGrievance() {
        if (isset($_GET['id'])) {
            if ($_GET['id'] == 'filter') {
                $this->response(401, ErrorCodes::$E0100['error_code'], ErrorCodes::$E0100['text']);
            } else {
                if (isset(getallheaders()['Member-Auth-Token'])) {
                    $member = $this->query->getMemberByToken(getallheaders()['Member-Auth-Token']);
                }
                if (isset(getallheaders()['Admin-Auth-Token'])) {
                    $user = $this->query->getUserByToken(getallheaders()['Admin-Auth-Token']);
                }
                if ($member != null || $user != null) {
                    $grievance = $this->query->getControlGrievanceById($_GET['id'], $member, $user, $messagecode, $messagestatus, $messagetext);
                    if ($grievance != null) {
              
                    if ($messagecode == 200) {
                        echo json_encode($grievance, JSON_PRETTY_PRINT);
                    } else {
                        $this->response($messagecode, $messagestatus, $messagetext);
                    }
                    } else {
                        $this->response(400, "Error", "Unexpected Grievance Id");
                    }
                } else {
                    $this->response(400, "Error", "Invalid token");
                }
            }
        } else {
            $this->response(401, ErrorCodes::$E0101['error_code'], ErrorCodes::$E0101['text']);
        }
    }

    function postGrievanceLogs($paramArray) {
        if (isset($_GET['id'])) {
            if ($_GET['id'] == 'filter') {
                $this->response(401, ErrorCodes::$E0100['error_code'], ErrorCodes::$E0100['text']);
            } else {
                if (isset(getallheaders()['Member-Auth-Token'])) {
                    $member = $this->query->getMemberByToken(getallheaders()['Member-Auth-Token']);
                }
                if (isset(getallheaders()['Admin-Auth-Token'])) {
                    $user = $this->query->getUserByToken(getallheaders()['Admin-Auth-Token']);
                }
                if ($member != null || $user != null) {
                    $grievanceLogs = $this->query->getGrievanceLog($_GET['id'], $paramArray, $messagecode, $messagestatus, $messagetext);

                    if ($messagecode == 200) {
                        echo json_encode($grievanceLogs, JSON_PRETTY_PRINT);
                    } else {
                        $this->response($messagecode, $messagestatus, $messagetext);
                    }
                } else {
                    $this->response(400, "Error", "Invalid token");
                }
            }
        } else {
            $this->response(401, ErrorCodes::$E0101['error_code'], ErrorCodes::$E0101['text']);
        }
    }

    function postGrievanceDocument() {
        if (isset($_GET['id'])) {
            if ($_GET['id'] == 'filter') {
                $this->response(401, ErrorCodes::$E0100['error_code'], ErrorCodes::$E0100['text']);
            } else {
                if (isset(getallheaders()['Member-Auth-Token']) || isset(getallheaders()['Admin-Auth-Token'])) {
                    $member = null;
                    $user = null;
                    if (isset(getallheaders()['Member-Auth-Token'])) {
                        $member = $this->query->getMemberByToken(getallheaders()['Member-Auth-Token']);
                    }
                    if (isset(getallheaders()['Admin-Auth-Token'])) {
                        $user = $this->query->getUserByToken(getallheaders()['Admin-Auth-Token']);
                    }
                    if ($member != null || $user != null) {

                        if (is_uploaded_file($_FILES['file_Data']['tmp_name'])) {
                            $theFile = file_get_contents($_FILES['file_Data']['tmp_name']);
                            $fileName = $_FILES['file_Data']['name'];
                            $fileSize = $_FILES['file_Data']['size'];
                            $fileExtension = $_FILES['file_Data']['type'];

                            $grievance = $this->query->getGrievanceById($_GET['id']);

                            if ($grievance != null) {
                                $grievanceDocuments = $this->query->insertDocument($grievance, $theFile, $fileName, $fileSize, $fileExtension, $member, $user, null, null);
                                if ($grievanceDocuments != null) {
                                    echo json_encode($grievanceDocuments, JSON_PRETTY_PRINT);
                                } else {
                                    $this->response(400, "Error", "Upload file error");
                                }
                            } else {
                                $this->response(400, "Error", "Invalid Grievance Id");
                            }
                        } else {
                            $this->response(400, "Error", "Invalid file");
                        }
                    } else {
                        $this->response(400, "Error", "Invalid token");
                    }
                } else {
                    $this->response(400, "Error", "Token is required");
                }

                //$grievanceLogs = $this->query->getGrievanceLog($_GET['id'], $paramArray, $messagecode, $messagestatus, $messagetext);
            }
        } else {
            $this->response(401, ErrorCodes::$E0101['error_code'], ErrorCodes::$E0101['text']);
        }
    }

    function getStepStatus() {

        if (isset($_GET['id'])) {
            if ($_GET['id'] == 'filter') {
                $this->response(401, ErrorCodes::$E0100['error_code'], ErrorCodes::$E0100['text']);
            } else {
                if (isset(getallheaders()['Member-Auth-Token'])) {
                    $member = $this->query->getMemberByToken(getallheaders()['Member-Auth-Token']);
                }
                if (isset(getallheaders()['Admin-Auth-Token'])) {
                    $user = $this->query->getUserByToken(getallheaders()['Admin-Auth-Token']);
                }
                if ($member != null || $user != null) {
                    $grievanceStepStatus = $this->query->getGrievanceFilterStepStatusByStep($_GET['id']);
                    if (sizeof($grievanceStepStatus) > 0) {
                        echo json_encode($grievanceStepStatus, JSON_PRETTY_PRINT);
                    } else {
                        $this->response(400, "Error", "Unexpected Step Id");
                    }
                } else {
                    $this->response(400, "Error", "Invalid token");
                }
            }
        } else {
            $this->response(401, ErrorCodes::$E0101['error_code'], ErrorCodes::$E0101['text']);
        }
    }

    function getInfoRequestOptions() {
        if (isset($_GET['id'])) {
            $this->response(401, ErrorCodes::$E0100['error_code'], ErrorCodes::$E0100['text']);
        } else {
            if (isset(getallheaders()['Member-Auth-Token'])) {
                $member = $this->query->getMemberByToken(getallheaders()['Member-Auth-Token']);
            }
            if (isset(getallheaders()['Admin-Auth-Token'])) {
                $user = $this->query->getUserByToken(getallheaders()['Admin-Auth-Token']);
            }

            if ($member != null || $user != null) {
                $grivanceInfoReq = $this->query->getInfoRequestOptions();
                echo json_encode($grivanceInfoReq, JSON_PRETTY_PRINT);
            } else {
                $this->response(400, "Error", "Invalid token");
            }
        }
    }

    function getInfoRequestForm() {
        
        if (isset($_GET['id'])) {
            if ($_GET['id'] == 'filter') {
                $this->response(401, ErrorCodes::$E0100['error_code'], ErrorCodes::$E0100['text']);
            } else {
                if (isset(getallheaders()['Member-Auth-Token']) || isset(getallheaders()['Admin-Auth-Token'])) {
                    $member = null;
                    $user = null;
                    if (isset(getallheaders()['Member-Auth-Token'])) {
                        $member = $this->query->getMemberByToken(getallheaders()['Member-Auth-Token']);
                    }
                    if (isset(getallheaders()['Admin-Auth-Token'])) {
                        $user = $this->query->getUserByToken(getallheaders()['Admin-Auth-Token']);
                    }
                    if ($member != null || $user != null) {

                        if (is_uploaded_file($_FILES['signature_file']['tmp_name'])) {
                            $signatureFile = file_get_contents($_FILES['signature_file']['tmp_name']);
                            $fileExtension = $_FILES['signature_file']['type'];

                            if ($fileExtension == "image/png") {
                                if (isset($_POST['to_name']) && isset($_POST['header_date']) && isset($_POST['designee_name']) && isset($_POST['designee_id']) && isset($_POST['re_mail']) && isset($_POST['company_prefix']) && isset($_POST['info_by_date']) && isset($_POST['options'])) {
                                    $paramArray = json_decode($_POST['options'], true);
                                    if (is_array($paramArray)) {

                                        $infoList = array();
                                        $infoByDate = $_POST['info_by_date'];
                                        $toName = $_POST['to_name'];
                                        $designeeName = $_POST['designee_name'];
                                        $reMail = $_POST['re_mail'];
                                        $designeeId = $_POST['designee_id'];
                                        $companyPrefix = $_POST['company_prefix'];
                                        $fromDate = $_POST['header_date'];
                                        $otherText = null;
                                        if (isset($_POST['other_text'])) {
                                            $otherText = $_POST['other_text'];
                                        }


                                        foreach ($paramArray as $infoRequest) {
                                            $info = new GrievanceInfoRequest();
                                            $info->setFromJsonArray($infoRequest);
                                            array_push($infoList, $info);
                                        }

                                        $validDateBegin = date('Y-m-d', strtotime("01/01/2000"));
                                        $dateOfInfoReqToFile = date('Y-m-d', strtotime($fromDate));
                                        $dateToInfoByDate = date('Y-m-d', strtotime($infoByDate));
                                        if (($dateOfInfoReqToFile > $validDateBegin) && ($dateToInfoByDate > $validDateBegin)) {

                                            $doc = $this->query->getInfoRequestForm($_GET['id'], $infoList, $member, $user, $toName, $fromDate, $designeeName, $designeeId, $reMail, $companyPrefix, $infoByDate, $signatureFile, $otherText);
                                            if ($doc != null) {
                                                echo json_encode($doc, JSON_PRETTY_PRINT);
                                            } else {
                                                $this->response(400, "Error", "Invlid Grievance Id");
                                            }
                                        } else {
                                            $this->response(400, "Error", "Invalid Date");
                                        }
                                    } else {
                                        $this->response(400, "Error", "Invalid Json");
                                    }
                                } else {
                                    $this->response(400, "Error", "Incomplete requiereds parameters set");
                                }
                            } else {
                                $this->response(400, "Error", "Invalid File type, received type: $fileExtension");
                            }
                        } else {
                            $this->response(400, "Error", "Signature File is requiered");
                        }
                    } else {
                        $this->response(400, "Error", "Invalid token");
                    }
                } else {
                    $this->response(400, "Error", "Token is required");
                }
            }
        } else {
            $this->response(401, ErrorCodes::$E0101['error_code'], ErrorCodes::$E0101['text']);
        }
    }

    function getGeneralForm() {
        if (isset($_GET['id'])) {
            if ($_GET['id'] == 'filter') {
                $this->response(401, ErrorCodes::$E0100['error_code'], ErrorCodes::$E0100['text']);
            } else {
                if (isset(getallheaders()['Member-Auth-Token']) || isset(getallheaders()['Admin-Auth-Token'])) {
                    $member = null;
                    $user = null;
                    if (isset(getallheaders()['Member-Auth-Token'])) {
                        $member = $this->query->getMemberByToken(getallheaders()['Member-Auth-Token']);
                    }
                    if (isset(getallheaders()['Admin-Auth-Token'])) {
                        $user = $this->query->getUserByToken(getallheaders()['Admin-Auth-Token']);
                    }
                    if ($member != null || $user != null) {
                        $doc = $this->query->getGeneralForm($_GET['id'], $member, $user);
                        if ($doc != null) {
                            echo json_encode($doc, JSON_PRETTY_PRINT);
                        } else {
                            $this->response(400, "Error", "Unexpected Grievance Id");
                        }
                    } else {
                        $this->response(400, "Error", "Invalid token");
                    }
                } else {
                    $this->response(400, "Error", "Token is required");
                }
            }
        } else {

            $this->response(401, ErrorCodes::$E0101['error_code'], ErrorCodes::$E0101['text']);
        }
    }

    function updateSteps($paramArray) {

        if (isset($_GET['id'])) {
            if ($_GET['id'] == 'filter') {
                $this->response(401, ErrorCodes::$E0100['error_code'], ErrorCodes::$E0100['text']);
            } else {
                $memberLog = null;
                $userLog = null;
                if (isset(getallheaders()['Member-Auth-Token'])) {
                    $memberLog = $this->query->getMemberByToken(getallheaders()['Member-Auth-Token']);
                }
                if (isset(getallheaders()['Admin-Auth-Token'])) {
                    $userLog = $this->query->getUserByToken(getallheaders()['Admin-Auth-Token']);
                }
                if ($userLog != null || $memberLog != null) {
                    $updatedGrievance = $this->query->updateGrievanceStep($_GET['id'], $paramArray, $memberLog, $userLog, $messagecode, $messagestatus, $messagetext);
                    if ($updatedGrievance != null) {
                        echo json_encode($updatedGrievance, JSON_PRETTY_PRINT);
                    } else {
                        $this->response($messagecode, $messagestatus, $messagetext);
                    }
                } else {

                    $this->response(400, "Error", "Invalid token");
                }
            }
        } else {
            $this->response(401, ErrorCodes::$E0101['error_code'], ErrorCodes::$E0101['text']);
        }
    }

    function deleteGrievanceStep() {

        if (isset($_GET['id'])) {
            if ($_GET['id'] == 'filter') {
                $this->response(401, ErrorCodes::$E0100['error_code'], ErrorCodes::$E0100['text']);
            } else {
            
                $userLog = null;
           
                if (isset(getallheaders()['Admin-Auth-Token'])) {
                    $userLog = $this->query->getUserByToken(getallheaders()['Admin-Auth-Token']);
                }
                
                if ($userLog != null && $userLog->getRole() < 3) {
                    $deleteGrievanceStep = $this->query->deleteGrievanceStep($_GET['id'], $userLog, $messagecode, $messagestatus, $messagetext);
                    if ($deleteGrievanceStep != null) {
                        echo json_encode($deleteGrievanceStep, JSON_PRETTY_PRINT);
                    } else {
                        $this->response($messagecode, $messagestatus, $messagetext);
                    }
                } else {
                    $this->response(400, "Error", "Invalid Token");
                }
            }
        } else {
            $this->response(401, ErrorCodes::$E0101['error_code'], ErrorCodes::$E0101['text']);
        }
    }

    function updateGrievance() {

        if (isset($_GET['id'])) {
            if ($_GET['id'] == 'filter') {
                $this->response(401, ErrorCodes::$E0100['error_code'], ErrorCodes::$E0100['text']);
            } else {
                $memberLog = null;
                $userLog = null;
                $theFile = null;
                $fileExtension = null;

                if (isset(getallheaders()['Member-Auth-Token'])) {
                    $memberLog = $this->query->getMemberByToken(getallheaders()['Member-Auth-Token']);
                }
                if (isset(getallheaders()['Admin-Auth-Token'])) {
                    $userLog = $this->query->getUserByToken(getallheaders()['Admin-Auth-Token']);
                }
                if (is_uploaded_file($_FILES['signature_file']['tmp_name'])) {
                    $theFile = file_get_contents($_FILES['signature_file']['tmp_name']);
                    $fileExtension = $_FILES['signature_file']['type'];
                }
                if ($memberLog != null || $userLog != null) {
                    if ($fileExtension == null || $fileExtension == "image/png") {
                        if (isset($_POST['json_info'])) {
                            $paramArray = json_decode($_POST['json_info'], true);
                            if (is_array($paramArray)) {

                                $updatedGrievance = $this->query->updateGrievanceData($_GET['id'], $paramArray, $memberLog, $userLog, $messagecode, $messagestatus, $messagetext, $theFile);
                                if ($updatedGrievance != null) {
                                    echo json_encode($updatedGrievance, JSON_PRETTY_PRINT);
                                } else {
                                    $this->response($messagecode, $messagestatus, $messagetext);
                                }
                            } else {
                                $this->response(400, "Error", "Invalid Json");
                            }
                        } else {
                            $this->response(400, "Error", "Grievace info is requierd");
                        }
                    } else {
                        $this->response(400, "Error", "Invalid File type, received type: $fileExtension");
                    }
                } else {
                    $this->response(400, "Error", "Invalid Token");
                }
            }
        } else {
            $this->response(401, ErrorCodes::$E0101['error_code'], ErrorCodes::$E0101['text']);
        }
    }

    function postGrievance() {

        if (isset($_GET['id'])) {
            if ($_GET['id'] == 'filter') {
                $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
                if (strcasecmp($contentType, 'application/x-www-form-urlencoded') == 0) {
                    parse_str(file_get_contents("php://input"), $post);
                    $paramArray = json_decode(json_encode($post), true);

                    if (!is_array($paramArray)) {
                        response(400, "Error", "Invalid Json");
                    } else {

                        $member = null;
                        $user = null;
                        if (isset(getallheaders()['Member-Auth-Token'])) {
                            $member = $this->query->getMemberByToken(getallheaders()['Member-Auth-Token']);
                        }
                        if (isset(getallheaders()['Admin-Auth-Token'])) {
                            $user = $this->query->getUserByToken(getallheaders()['Admin-Auth-Token']);
                        }
                        if ($member != null || $user != null) {
                            $grivance = $this->query->getGrievance($paramArray, $messagecode, $messagestatus, $messagetext, $member, $user);
                            if ($messagecode == 200) {
                                echo json_encode($grivance, JSON_PRETTY_PRINT);
                            } else {
                                $this->response($messagecode, $messagestatus, $messagetext);
                            }
                        } else {

                            $this->response(400, "Error", "Invalid token");
                        }
                    }
                } else {
                    response(400, "Error", "Unexpected ContentType");
                }
            } else {
                $this->response(401, ErrorCodes::$E0100['error_code'], ErrorCodes::$E0100['text']);
            }
        } else if (isset(getallheaders()['Member-Auth-Token'])) {
            $member = $this->query->getMemberByToken(getallheaders()['Member-Auth-Token']);
            if ($member != null) {
                if (is_uploaded_file($_FILES['signature_file']['tmp_name'])) {
                    $theFile = file_get_contents($_FILES['signature_file']['tmp_name']);
                    $fileExtension = $_FILES['signature_file']['type'];

                    if ($fileExtension == "image/png") {
                        if (isset($_POST['json_info'])) {
                            $paramArray = json_decode($_POST['json_info'], true);
                            if (is_array($paramArray)) {
                                
                                if ($this->query->createGrievance($member, $grievance, $theFile, $paramArray, $messagecode, $messagestatus, $messagetext)) {
                                    echo json_encode($grievance, JSON_PRETTY_PRINT);
                                } else {
                                    $this->response($messagecode, $messagestatus, $messagetext);
                                }
                            } else {
                                $this->response(400, "Error", "Invalid Json");
                            }
                        } else {
                            $this->response(400, "Error", "Grievace info is requierd");
                        }
                    } else {
                        $this->response(400, "Error", "Invalid File type, received type: $fileExtension");
                    }
                } else {
                    $this->response(400, "Error", "Signature File is requiered");
                }
            } else {
                $this->response(400, "Error", "Member Invalid token");
            }
        } else {
            $this->response(400, "Error", "Token is requiered");
        }
    }

    function postAttachments($paramArray) {

        if (isset($_GET['id'])) {
            if ($_GET['id'] == 'filter') {
                $this->response(401, ErrorCodes::$E0100['error_code'], ErrorCodes::$E0100['text']);
            } else {
                if (isset(getallheaders()['Member-Auth-Token']) || isset(getallheaders()['Admin-Auth-Token'])) {
                    $member = null;
                    $user = null;
                    if (isset(getallheaders()['Member-Auth-Token'])) {
                        $member = $this->query->getMemberByToken(getallheaders()['Member-Auth-Token']);
                    }
                    if (isset(getallheaders()['Admin-Auth-Token'])) {
                        $user = $this->query->getUserByToken(getallheaders()['Admin-Auth-Token']);
                    }
                    if ($member != null || $user != null) {
                        $grievanceDocs = $this->query->getGrievanceDocuments($_GET['id'], $paramArray, $messagecode, $messagestatus, $messagetext);
                        if ($messagecode == 200) {
                            echo json_encode($grievanceDocs, JSON_PRETTY_PRINT);
                        } else {
                            $this->response($messagecode, $messagestatus, $messagetext);
                        }
                    } else {
                        $this->response(400, "Error", "Invalid token");
                    }
                } else {
                    $this->response(400, "Error", "Token is required");
                }
            }
        } else {
            $this->response(401, ErrorCodes::$E0101['error_code'], ErrorCodes::$E0101['text']);
        }
    }

    function getAlerts() {
        if (isset($_GET['id'])) {
            $this->response(401, ErrorCodes::$E0100['error_code'], ErrorCodes::$E0100['text']);
        } else {
            if (isset(getallheaders()['Member-Auth-Token']) || isset(getallheaders()['Admin-Auth-Token'])) {
                $member = null;
                $user = null;
                if (isset(getallheaders()['Member-Auth-Token'])) {
                    $member = $this->query->getMemberByToken(getallheaders()['Member-Auth-Token']);
                }
                if (isset(getallheaders()['Admin-Auth-Token'])) {
                    $user = $this->query->getUserByToken(getallheaders()['Admin-Auth-Token']);
                }
                if ($member != null || $user != null) {
                    $grievances = $this->query->getAlerts($member, $user);
                    echo json_encode($grievances, JSON_PRETTY_PRINT);
                } else {
                    $this->response(400, "Error", "Invalid token");
                }
            } else {
                $this->response(400, "Error", "Token is required");
            }
        }
    }

    function postUnassigned($paramArray) {
        if (isset($_GET['id'])) {
            $this->response(401, ErrorCodes::$E0100['error_code'], ErrorCodes::$E0100['text']);
        } else {
            if (isset(getallheaders()['Admin-Auth-Token'])) {
                $user = $this->query->getUserByToken(getallheaders()['Admin-Auth-Token']);

                if ($user != null) {
                    $grievances = $this->query->getUnassigned($paramArray);
                    echo json_encode($grievances, JSON_PRETTY_PRINT);
                } else {
                    $this->response(400, "Error", "Invalid Admin token");
                }
            } else {
                $this->response(400, "Error", "Token is required");
            }
        }
    }
    
    
    function updateAssignment($paramArray) {

        if (isset($_GET['id'])) {
            if ($_GET['id'] == 'filter') {
                $this->response(401, ErrorCodes::$E0100['error_code'], ErrorCodes::$E0100['text']);
            } else {

                $userLog = null;
                if (isset(getallheaders()['Admin-Auth-Token'])) {
                    $userLog = $this->query->getUserByToken(getallheaders()['Admin-Auth-Token']);
               }
                if ($userLog != null && $userLog->getRole() < 3) {
                    $this->query->updateStepAssignment($_GET['id'], $paramArray, $userLog, $messagecode, $messagestatus, $messagetext);
                    if ($messagecode == 200) {
                        $grievance= $this->query->getGrievanceById($_GET['id']);
                        echo json_encode($grievance, JSON_PRETTY_PRINT);
                    } else {
                        $this->response($messagecode, $messagestatus, $messagetext);
                    }
                } else {
                    $this->response(400, "Error", "Invalid Admin token");
                }
            }
        } else {
            $this->response(401, ErrorCodes::$E0101['error_code'], ErrorCodes::$E0101['text']);
        }
    }

}
