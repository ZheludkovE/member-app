<?php
header('Content-Type: application/JSON');
require 'GrievanceAction.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET'://query
        getResponse();
        break;
    case 'POST'://insert
        postResponse();
        break;
    case 'PATCH'://update
        updateResponse();
        break;
     case 'DELETE'://update
        deleteResponse();
        break;
    default:
        echo 'METHOD NOT SUPPORTED';
        break;
}

function getResponse() {

    $response = new GrievanceAction();
    switch ($_GET['action']) {
        case "grievance":
            $response->getGrievance();
            break;
        case "grievanceSteps":
            $response->getGrievancesSteps();
            break;
        case "grievanceNature":
            $response->getGrievancesNature();
            break;
        case "grievanceRemedy":
            $response->getGrievancesRemedy();
            break;
        case "grievanceStepStatus":
            $response->getStepStatus();
            break;
        case "grievanceInfoRequest":
            $response->getInfoRequestOptions();
            break;
        case "grievanceGeneralForm":
            $response->getGeneralForm();
            break;
        case "grievanceAlerts":
            $response->getAlerts();
            break;

        default:
            response(400, "Error", "Unexpected action");
            break;
    }
    $response = null;
}

function postResponse() {
    $response = new GrievanceAction();
    switch ($_GET['action']) {
        case "grievance":
            $response->postGrievance();
            break;
         case "grievanceUnassigned":
             if (checkDataParameter($paramArray)) {
                $response->postUnassigned($paramArray);
            }
            break;
        case "grievanceData":
            $response->updateGrievance();
            break;
        case "grievanceLogs":
            if (checkDataParameter($paramArray)) {
                $response->postGrievanceLogs($paramArray);
            }
            break;
        case "grievanceDocuments":
            $response->postGrievanceDocument();
            break;
        case "grievanceAttachments":
            if (checkDataParameter($paramArray)) {
                $response->postAttachments($paramArray);
            }
            break;
        case "grievanceInfoRequestForm":
            $response->getInfoRequestForm();
            break;
        default:
            response(400, "Error", "Unexpected action");
            break;
    }
    $response = null;
}

function updateResponse() {
    $response = new GrievanceAction();
    switch ($_GET['action']) {
        case "grievanceSteps":
            if (checkDataParameter($paramArray)) {
                $response->updateSteps($paramArray);
            }
            break;
        case "grievanceStepsAssignment":
            if (checkDataParameter($paramArray)) {
                $response->updateAssignment($paramArray);
            }
            break;
        default:
            response(400, "Error", "Unexpected action");
            break;
    }
    $response = null;
}

function deleteResponse() {
    $response = new GrievanceAction();
    switch ($_GET['action']) {
        case "grievanceSteps":
            $response->deleteGrievanceStep();
            break;
        default:
            response(400, "Error", "Unexpected action");
            break;
    }
    $response = null;
}

function checkDataParameter(&$decoded) {

    $validParam = true;
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    if (strcasecmp($contentType, 'application/json') == 0 || strcasecmp($contentType, 'application/json; charset=UTF-8') == 0) {
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);

        if (!is_array($decoded)) {
            $validParam = false;
            response(400, "Error", "Invalid Json");
        }
    } else if (strcasecmp($contentType, 'application/x-www-form-urlencoded') == 0) {
        parse_str(file_get_contents("php://input"), $post);
        $decoded = json_decode(json_encode($post), true);
        if (!is_array($decoded)) {
            $validParam = false;
            response(400, "Error", "Invalid parameters");
        }
    } else {
        $validParam = false;
        response(400, "Error", "Unexpected ContentType");
    }
    return $validParam;
}

function response($code = 200, $status = "", $message = "") {
    http_response_code($code);
    if ($code != 200 && !empty($status) && !empty($message)) {
        $response = array("error_code" => $status, "text" => $message);
        echo json_encode($response, JSON_PRETTY_PRINT);
    }
}
