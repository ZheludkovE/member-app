<?php

class GrievanceStatus implements JsonSerializable {

    private $mId;
    private $mStatusName;

    public function __construct($id, $statusName) {
        $this->mId = $id;
        $this->mStatusName = $statusName;
    }

    function getId() {
        return (int) $this->mId;
    }

    function getStatusName() {
        return $this->mStatusName;
    }

    function setId($id) {
        $this->mId=$id;
    }

    function setStatusName($name) {
        $this->mStatusName = $name;
    }

    public function jsonSerialize() {
        return ['id' => (int) $this->mId, 'status_name' => $this->mStatusName];
    }

    public function setFromJsonArray($jsonArray) {
        $settingOk = true;
        foreach ($jsonArray as $key => $value) {
            switch ($key) {
                case 'id':
                    $this->mId = $value;
                    break;
                case 'status_name':
                    $this->mStatusName = $value;
                    break;
                default:
                 
                    break;
            }
            
        }

        return $settingOk;
    }

}
