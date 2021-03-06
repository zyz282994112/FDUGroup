<?php
/**
 * Message Model class file.
 * @author: songrenchu
 */

class Censor extends Data{
    public $type, $sender, $related;
    public $id,$typeId,$firstId,$secondId,$content,$sendTime,$status;

    const UNPROCESS = 1;
    const PASS = 2;
    const DENY = 3;

    public function __construct()
    {
        $options = array(
            'key'=>'id',
            'table'=>'censor',
            'columns'=>array(
                'id'=>'csr_id',
                'typeId'=>'csr_type_id',
                'firstId'=>'csr_first_id',
                'secondId'=>'csr_second_id',
                'content'=>'csr_content',
                'sendTime'=>'csr_send_time',
                'status'=>'csr_status',
            )
        );
        parent::init($options);
    }

    public function load($id=null)
    {
        $result = parent::load($id);
        if ($result !== null) {
            $this->type = new CensorType();
            $this->type->typeId = $this->typeId;
            $this->type->load();

            $this->sender = new User();
            $this->sender->id = $this->firstId;
            switch ($this->type->typeName) {
                case 'add_friend':
                    $this->related = new User();
                    $this->related = $this->secondId;
                    break;
                case 'apply_vip':
                    $this->related = null;
                    break;
                case 'join_group':
                    $this->related = new Group();
                    $this->related = $this->secondId;
                    break;
                case 'post_ads':
                    /* TODO */
                    break;
            }
            return $this;
        } else {
            return null;
        }
    }

    public function getTypeIdbyTypeName($typeName) {
        $this->typeId = new CensorType();
        $this->typeId->typeName = $typeName;
        $this->typeId = $this->typeId->find()[0]->typeId;
    }

    public function postApplication($typeName,$firstId,$secondId = null,$content = null,$sendTime=null,$status=self::UNPROCESS)
    {
        $this->getTypeIdbyTypeName($typeName);

        $this->firstId = $firstId;
        $this->secondId = $secondId;
        $this->content = $content;
        $this->sendTime = $sendTime;
        $this->status = $status;
        if(!isset($this->sendTime)||$this->sendTime==''){
            date_default_timezone_set(Rays::app()->getTimeZone());
            $this->sendTime = date('Y-m-d H:i:s');;
        }
        $_id = $this->insert();
        $this->load($_id);
    }

    public function passCensor($censorId = null) {
        if ($censorId !== null) {
            $this->id = $censorId;
            $this->load();
        }
        $this->status = self::PASS;
        $this->update();
        return $this;
    }

    public function failCensor($censorId = null) {
        if ($censorId !== null) {
            $this->id = $censorId;
            $this->load();
        }
        $this->status = self::DENY;
        $this->update();
        return $this;
    }

    public function addFriendApplication($userFrom, $userTo) {      //add friend application
        $this->postApplication('add_friend', $userFrom, $userTo);
        return $this;
    }

    public function addFriendExist($userFrom, $userTo) {    //add friend request id if exist, or null is not exist
        $this->getTypeIdbyTypeName('add_friend');
        $this->firstId = $userFrom;
        $this->secondId = $userTo;
        $this->status = self::UNPROCESS;
        $result = $this->find();
        return count($result) == 0 ? null : $result[0]->id;
    }

    public function joinGroupApplication($userId, $groupId) {       //join group application
        $this->postApplication('join_group', $userId, $groupId);
        return $this;
    }

    public function joinGroupExist($userId, $groupId) {    //join group request id if exist, or null is not exist
        $this->getTypeIdbyTypeName('join_group');
        $this->firstId = $userId;
        $this->secondId = $groupId;
        $this->status = self::UNPROCESS;
        $result = $this->find();
        return count($result) == 0 ? null : $result[0]->id;
    }

    public function applyVIPApplication($userId, $content) {      //apply for VIP
        $this->postApplication('apply_vip',$userId, null, $content);
        return $this;
    }

    public function applyVIPExist($userId) {
        $this->getTypeIdbyTypeName('apply_vip');
        $this->firstId = $userId;
        $this->status = self::UNPROCESS;
        $result = $this->find();
        return count($result) == 0 ? null : $result[0]->id;
    }

    public function joinGroupInvitationApplication($userId, $groupId) {  //Invite to join Group
        $this->postApplication('join_group_invite', $userId, $groupId);
        return $this;
    }

    public function joinGroupInvitationExist($userId, $groupId) {
        $this->getTypeIdbyTypeName('join_group_invite');
        $this->firstId = $userId;
        $this->secondId = $groupId;
        $this->status = self::UNPROCESS;
        $result = $this->find();
        return count($result) == 0 ? null : $result[0]->id;
    }
}