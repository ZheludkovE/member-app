<?php

class GrievanceNature implements JsonSerializable {

    private $mId;
    private $mName;

    public function __construct($id, $name) {
        $this->mId = $id;
        $this->mName = $name;
    }

    function getId() {
        return (int)$this->mId;
    }

    function getName() {
        return $this->mName;
    }

    public function jsonSerialize() {
        return ['id' => (int)$this->mId, 'name' => $this->mName];
    }
    
    public function setFromJsonArray($jsonArray) {
        $settingOk = false;
        foreach ($jsonArray as $key => $value) {
            switch ($key) {
                case 'id':
                    $settingOk=true;
                    $this->mId= (int)$value;    
                    break;
                case 'name':
                    $this->mName= $value;    
                    break;
                default:
                    break;
            }
            
        }
        return $settingOk;
    }


}
