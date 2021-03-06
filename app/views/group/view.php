<?php
/**
 * show my groups
 * Author: Guo Junshi
 * Date: 13-10-14
 * Time: 下午1:53
 */

    echo RHtmlHelper::linkAction('group','Build my group','build',null,array('class'=>'btn btn-success'));

    echo "<br/><br/>";

    if($data == null){
        echo "<p>You have not joint any groups!</p>";
        return null;
    }

$count = 0;
echo '<div class="row">';
foreach($data as $group){

    echo '<div class="col-6 col-sm-6 col-lg-4" style="height: 190px;">';
    echo "<div class='panel panel-default' style='height: 170px;'>";
    echo "<div class='panel-heading'>";
    if(isset($group->picture)&&$group->picture!=''){
        //echo RHtmlHelper::showImage($group->picture,$group->name,array('style'=>'height:32px;'));
    }
    echo RHtmlHelper::linkAction('group', $group->name, 'detail', $group->id);
    echo "</div>";

    echo "<div class='panel-body'>";
    echo $group->memberCount." members";
    $content = strip_tags(RHtmlHelper::decode($group->intro));
    if(mb_strlen($content)>70){
        echo '<p>'.mb_substr($content,0,70,"UTF-8").'...</p>';
    }
    else echo '<p>'.($content).'</p>';
    if(Rays::app()->getLoginUser()->id!=$group->creator)
    echo RHtmlHelper::link(
        'Exit group','Exit group',RHtmlHelper::siteUrl("group/exit/".$group->id),
        array(
            'class'=>'btn btn-xs btn-danger',
            'style'=>'position:absolute;top:140px;right:120px;',
            'onclick'=>'javascript:confirmExit('.$group->id.')',
        )
    );

    echo RHtmlHelper::linkAction('group','View details','detail',$group->id
    ,array('class'=>'btn btn-xs btn-info','style'=>'position:absolute;top:140px;right:30px;'));

    echo "</div></div>";
    echo "</div>";

}
echo '</div>';

?>
<div class="alert alert-block alert-danger fade ">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h4>Quit group confirm!</h4>
    <p>
        This action cannot be undo! Are you going to quit the group right now?
    </p>
    <p>
        <span id="quit_link" style="display: none;"><?php echo Rays::app()->getBaseUrl()."/group/exit/" ?></span>
        <a id="alert-quit-group" class="btn btn-danger" href="#">Yes, quit now</a> <a class="btn btn-default" href="#">Cancel</a>
    </p>
</div>

<script>
    function confirmExit(groupId){
        if(groupId!=''){
            $('#alert-quit-group').attr('href',$('#quit_link').text() + groupId);
            //$('.alert').addClass('in').removeClass('fade');
        }
    }
    $(document).ready(function() {
        $('.alert').bind('close.bs.alert', function () {
            //$('.alert').removeClass('in').removeClass('fade');
        });
    });
</script>
