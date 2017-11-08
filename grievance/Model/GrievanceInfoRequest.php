<?php
class GrievanceInfoRequest implements JsonSerializable {

   
    private $mId;
    private $mName;
    
    public function __construct($id, $optionName) {
        $this->mName=$optionName;
        $this->mId = $id;
    }
    
    public function getId()
    {
        return $this->mId;
    }
    
    public function getName()
    {
        return $this->mName;
    }
    
    
     public function jsonSerialize() {
        return ['option_id' => (int)$this->mId, 'name' => $this->mName];
    }
    
     public function setFromJsonArray($jsonArray) {
        $settingOk = true;
        foreach ($jsonArray as $key => $value) {
            switch ($key) {
                case 'option_id':
                    $this->mId = $value;
                    break;
                case 'name':
                    $this->mName = $value;
                    break;
                default:
                   
                    break;
            }
           
        }

        return $settingOk;
    }
    
}