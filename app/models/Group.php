<?php
/**
 * Class Group
 * @author: Raysmond
 */

class Group extends Data
{
    public $groupCreator;
    public $category;
    public $id, $creator, $categoryId, $name, $memberCount, $createdTime, $intro,$picture;

    const ENTITY_TYPE = 2;
    const PICTURE_PATH = '/files/images/groups/';

    public static $labels = array(
        "id" => "ID",
        "creator" => "Creator",
        "categoryId" => "Category",
        "name" => "Name",
        "memberCount" => "Member count",
        "createdTime" => "Create time",
        "intro" => "Introduction",
        "picture"=>'Picture'
    );

    public static $defaults = array('picture'=>'files/images/default_pic.png');

    public function __construct()
    {
        $option = array(
            "key" => "id",
            "table" => "groups",
            "columns" => array(
                "id" => "gro_id",
                "creator" => "gro_creator",
                "categoryId" => "cat_id",
                "name" => "gro_name",
                "memberCount" => "gro_member_count",
                "createdTime" => "gro_created_time",
                "intro" => "gro_intro",
                "picture"=>'gro_picture'
            )
        );
        parent::init($option);
    }

    public function load($id = null)
    {
        $result = parent::load($id);
        if ($result !== null) {
            $this->groupCreator = new User();
            $this->groupCreator->id = $this->creator;
            $this->groupUsers = array();
            $this->category = new Category();
            $this->category->id = $this->categoryId;
            return $this;
        } else {
            return null;
        }

    }

    public static function countTopics($groupId){
        $topic = new Topic();
        $topic->groupId = $groupId;
        return $topic->count();
    }

    public function groupUsers($limit=0, $orderby=null, $order='ASC'){
        $groupUsers = new GroupUser();
        $groupUsers->groupId = $this->id;
        $groupUsers = $groupUsers->find(0,$limit,array('key'=>$orderby,'order'=>$order));
        $result = array();
        foreach($groupUsers as $row){
            $user = new User();
            $user->load($row->userId);
            array_push($result,$user);
        }
        return $result;
    }

    public function setDefaults(){
        if(!isset($this->memberCount))
            $this->memberCount = 1;
        date_default_timezone_set(Rays::app()->getTimeZone());
        $this->createdTime = date('Y-m-d H:i:s');
    }

    public function buildGroup($groupName,$categoryId,$introduction,$creatorId,$picture=''){
        $this->setDefaults();
        $this->name = $groupName;
        $this->categoryId = $categoryId;
        $this->intro = $introduction;
        $this->creator = $creatorId;
        if($picture!='')
            $this->picture = $picture;
        $id = $this->insert();
        $group = new Group();
        $group->id = $id;
        $group->load();

        $groupUser = new GroupUser();
        $groupUser->groupId = $group->id;
        $groupUser->userId = $group->creator;
        date_default_timezone_set(Rays::app()->getTimeZone());
        $groupUser->joinTime = date('Y-m-d H:i:s');
        $groupUser->status = 1;
        $groupUser->insert();

        return $group;
    }

    public function uploadPicture($fileTag)
    {
        $uploadPath = Rays::app()->getBaseDir() . '/../' . self::PICTURE_PATH;
        $picName = 'group_' . $this->id . RUploadHelper::get_extension($_FILES[$fileTag]['name']);
        $upload = new RUploadHelper(array('file_name' => $picName, 'upload_path' => $uploadPath));

        $upload->upload($fileTag);

        if ($upload->error != '') {
            return $upload->error;
        } else {
            $this->picture = "files/images/groups/" . $upload->file_name;
            $this->update();
            RImageHelper::updateStyle($this->picture,static::getPicOptions());
            return true;
        }
    }

    public function increaseCounter(){
        if(isset($this->id)){
            $counter = new Counter();
            $counter->increaseCounter($this->id,self::ENTITY_TYPE);
            return $counter;
        }
        return null;
    }

    public function deleteGroup()
    {
        if(isset($this->id)&&$this->id!=''){
            $groupUsers = new GroupUser();
            $groupUsers->groupId = $this->id;

            $topics = new Topic();
            $topics->groupId = $this->id;
            $_topics = $topics->find();
            $comment = new Comment();
            foreach($_topics as $topic){
                $sql = "delete from {$comment->table} where {$comment->columns['topicId']} = {$topic->id}";
                Data::executeSQL($sql);
            }
            $sql = "delete from {$topics->table} where {$topics->columns['groupId']} = {$this->id}";
            Data::executeSQL($sql);

            $sql = "delete from {$groupUsers->table} where {$groupUsers->columns['groupId']} = {$this->id}";
            Data::executeSQL($sql);
            $friends = new FriendsGroup();
            $sql = "delete from {$friends->table} where {$friends->columns['groupId1']} = {$this->id} or {$friends->columns['groupId2']} = {$this->id} ";
            Data::executeSQL($sql);

            $this->delete();

            $counter = new Counter();
            $counter = $counter->loadCounter($this->id,self::ENTITY_TYPE);
            if($counter!=null)
                $counter->delete();

            return true;
        }
        else
            return false;
    }

    public static function inviteFriends($groupId,$user,$invitees=array(),$invitationMsg){
        $group = new Group();
        $group->load($groupId);
        foreach ($invitees as $friendId) {
            $censor = new Censor();
            $censor->joinGroupApplication($friendId, $group->id);

            $msg = new Message();
            $content = RHtmlHelper::linkAction('user', $user->name, 'view', $user->id)
                . ' invited you to join group '
                . RHtmlHelper::linkAction('group', $group->name, 'detail', $group->id)
                . '&nbsp;&nbsp;'
                . RHtmlHelper::linkAction('group', 'Accept invitation', 'acceptInvite', $censor->id, array('class' => 'btn btn-xs btn-info'))
                . '</br>'
                . $invitationMsg;
            $content = RHtmlHelper::encode($content);
            $msg->sendMsg('group', $user->id, $friendId, 'new group invitation', $content);

        }
    }

    public function findAll($start,$pageSize,$order=array(),$assignment=array(),$like=array()){
        $creator = new User();
        $category = new Category();
        $sql = "SELECT ".
            "groups.{$this->columns['id']} AS group_id ".
            ",groups.{$this->columns['name']} AS group_name ".
            ",groups.{$this->columns['creator']} AS group_creator_id ".
            ",groups.{$this->columns['memberCount']} AS group_member_count ".
            ",groups.{$this->columns['picture']} AS group_picture ".
            ",groups.{$this->columns['createdTime']} AS group_created_time ".
            ",groups.{$this->columns['categoryId']} AS group_category_id ".
            ",group_creator.{$creator->columns['name']} AS creator_name ".
            ",group_category.{$category->columns['name']} AS category_name ".
            "FROM {$this->table} AS groups ";
        $sql.="LEFT JOIN {$creator->table} AS group_creator ON group_creator.{$creator->columns['id']}=groups.{$this->columns['creator']} ";
        $sql.="LEFT JOIN {$category->table} AS group_category ON group_category.{$category->columns['id']}=groups.{$this->columns['categoryId']} ";

        $where = " where 1 = 1 ";
        foreach ($this->columns as $objCol => $dbCol) {
            if ($this->$objCol) {
                $where .= " and $dbCol = '{$this->$objCol}'";
            }
        }

        if(!empty($assignment))
        {
            foreach ($assignment as $objCol => $value) {
                if(is_array($value)){
                    $where .= " and " . $this->columns[$objCol] . " in (";
                    $count_value = count($value);
                    $count_cur = 0;
                    foreach($value as $val){
                        $where .= $val;
                        if($count_cur++<$count_value-1){
                            $where .= ',';
                        }
                        else $where .= ')';
                    }
                }
                else $where .= " and " . $this->columns[$objCol] ." = $value";
            }
        }

        if(!empty($like))
        {
            $where.=" and (";
            $first = true;
            foreach($like as $val)
            {
                if(isset($val['key'])&&isset($val['value'])&&isset($this->columns[$val['key']]))
                {
                    if(!$first) $where.=" or ";
                    $where.= "  ".$this->columns[$val['key']]." like '%".$val['value']."%' ";
                    $first = false;
                }
            }
            $where.=" ) ";
        }

        $sql.=$where;

        if(!empty($order)){
            if(isset($order['key'])&&isset($this->columns[$order['key']])){
                if(isset($order['order'])&&strcasecmp($order['order'],'desc')==0){
                    $sql.=" ORDER BY {$this->columns[$order['key']]} DESC ";
                }
                else{
                    $sql.=" ORDER BY {$this->columns[$order['key']]} ASC ";
                }
            }
        }
        $sql.="LIMIT {$start},{$pageSize}";
        $result = self::db_query($sql);
        return $result;
    }

    /**
     * Recommend all selected groups to every selected users
     * @param $groups
     * @param $users
     */
    public static function recommendGroups($groups,$users,$words = ''){
        foreach ($users as $userId) {
            $html = '<div class="row recommend-groups">';
            foreach ($groups as $groupId) {
                $group = new Group();
                $group = $group->load($groupId);
                if (null != $group) {
                    $censor = new Censor();
                    $censor = $censor->joinGroupApplication($userId, $group->id);
                    $html .= '<div class="col-lg-3 recommend-group-item" style="padding: 5px;">';
                    if (!isset($group->picture) || $group->picture == '') $group->picture = Group::$defaults['picture'];
                    $html .= RHtmlHelper::showImage($group->picture, $group->name);
                    $html .= '<br/>' . RHtmlHelper::linkAction('group', $group->name, 'detail', $group->id);
                    $html .= '<br/>' . RHtmlHelper::linkAction('group', 'Accept', 'accept', $censor->id, array('class' => 'btn btn-xs btn-success'));
                    $html .= '</div>';
                }
            }
            $html .= '</div>';
            $html .= '<div class="recommend-content">'.RHtmlHelper::encode($words).'</div>';
            $msg = new Message();
            $msg->sendMsg('system', 0, $userId, 'Groups recommendation', $html, date('Y-m-d H:i:s'));
        }
    }

    public static function getPicOptions(){
        return array(
            'path' => 'files/images/styles',
            'name'=>'groups',
            'width'=>200,
            'height'=>200
        );
    }
}
