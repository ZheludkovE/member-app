<?php

class GrievanceStep implements JsonSerializable {

    private $mId;
    private $mOrder;
    private $mStepName;

    public function __construct($id, $order, $stepName) {
        $this->mId = $id;
        $this->mOrder = $order;
        $this->mStepName = $stepName;
    }

    function getId() {
        return (int)$this->mId;
    }

    function getStepName() {
        return $this->mStepName;
    }

    function getOrder() {
        return (int)$this->mOrder;
    }
    
    function setId($id) {
        $this->mId = $id;
    }

    function setStepName($stepName) {
        $this->mStepName = $stepName;
    }

    function setOrder($order) {
       $this->mOrder = $order;
    }

    public function jsonSerialize() {
        return ['id' => (int)$this->mId, 'order' => (int)$this->mOrder, 'step_name' => $this->mStepName];
    }
    
     public function setFromJsonArray($jsonArray) {
        $settingOk = true;
        foreach ($jsonArray as $key => $value) {
            switch ($key) {
                case 'id':
                    $this->mId = $value;
                    break;
                case 'order':
                    $this->mOrder = $value;
                    break;
                case 'step_name':
                    $this->mStepName = $value;
                    break;
                default:
                   
                    break;
            }
            
        }

        return $settingOk;
    }

}

