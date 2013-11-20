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
        parent::load($id);
        $this->groupCreator = new User();
        $this->groupCreator->id = $this->creator;
        $this->groupUsers = array();
        $this->category = new Category();
        $this->category->id = $this->categoryId;
    }

    public function groupUsers($limit=0, $orderby='', $order='ASC'){
        $groupusers = new GroupUser();
        $groupusers->groupId = $this->id;
        $groupusers = $groupusers->find(0,$limit,array('key'=>$orderby,'order'=>$order));
        $result = array();
        foreach($groupusers as $row){
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
            return true;
        }
        else
            return false;
    }

    public function findAll($start,$pageSize,$order=array()){
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
        if(!empty($order)){
            if(isset($order['key'])&&isset($this->columns[$order['key']])){
                if(isset($order['order'])&&strcasecmp($order['order'],'desc')){
                    $sql.=" ORDER BY {$this->columns[$order['key']]} DESC ";
                }
                else{
                    $sql.=" ORDER BY {$this->columns[$order['key']]} ASC ";
                }
            }
        }
        $sql.="LIMIT {$start},{$pageSize}";
        //$result = self::db_query($sql);
        //echo $sql;
        //return array();
        $result = self::db_query($sql);
        return $result;
    }
}
