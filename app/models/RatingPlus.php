<?php
/**
 * Created by PhpStorm.
 * User: Raysmond
 * Date: 13-11-25
 * Time: PM12:45
 */

class RatingPlus
{
    public $entityType = null, $entityId = null, $userId = 0,$host;
    private $_rating = null;
    private $_ratingId = null;

    private $_counter = null;

    const VALUE_TYPE = 'integer';
    const TAG = "plus";
    const VALUE = 1;

    public function __construct($entityType, $entityId, $userId = 0, $host = "")
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->userId = $userId;
        $this->host = $host;
    }

    public function rate()
    {
        if ($this->check()) {
            $plus = new Rating();
            $plus->entityId = $this->entityId;
            $plus->entityType = $this->entityType;
            $plus->userId = $this->userId;
            $plus->host = $this->host;
            $plus->valueType = self::VALUE_TYPE;
            $plus->value = self::VALUE;
            $plus->tag = self::TAG;
            if(count($plus->find())==0){
                $ratingId = $plus->insert();
                if ($ratingId !== null && is_numeric($ratingId)) {
                    $this->_ratingId = $ratingId;
                    $this->updatePlusCounter();
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Plus counter for every plus rating
     */
    private function updatePlusCounter(){
        $counter = new RatingStatistic();
        $counter->entityType = $this->entityType;
        $counter->entityId = $this->entityId;
        $counter->type = 'count';
        $counter->valueType = self::VALUE_TYPE;
        $counter->tag = self::TAG;
        $result = $counter->find();
        if(count($result)===0){
            $counter->value = 1;
            $id = $counter->insert();
            $counter->id = $id;
        }
        else{
            $counter = $result[0];
            $counter->value++;
            $counter->timestamp = date('Y-m-d H:i:s');
            $counter->update();
        }
        $this->_counter = $counter;
    }

    public function getCounter(){
        if($this->_counter===null){
            $counter = new RatingStatistic();
            $counter->entityType = $this->entityType;
            $counter->entityId = $this->entityId;
            $counter->type = 'count';
            $counter->valueType = self::VALUE_TYPE;
            $counter->tag = self::TAG;
            $result = $counter->find();
            if(count($result)!==0){
                $this->_counter = $result[0];
            }
        }
        return $this->_counter;
    }

    public function getRating()
    {
        if ($this->_ratingId !== null && $this->_rating === null) {
            $this->_rating = new Rating();
            $this->_rating->id = $this->_ratingId;
            $result = $this->_rating->load();
            if ($result !== null) {
                $this->_rating = $result;
            } else {
                $this->_rating = null;
            }
        }
        return $this->_rating;
    }

    private function check()
    {
        if (isset($this->entityType) && isset($this->entityId)) {
            if (is_numeric($this->entityId) && is_numeric($this->entityType) && is_numeric($this->userId))
                return true;
        }
        return false;
    }
} 