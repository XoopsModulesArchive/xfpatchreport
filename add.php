<?php

use Xmf\Request;

require_once '../../mainfile.php';
require_once XOOPS_ROOT_PATH . '/class/module.textsanitizer.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/pre.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/project_summary.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/tracker/Artifact.class';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/tracker/ArtifactHtml.class';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/tracker/ArtifactFile.class';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/tracker/ArtifactFileHtml.class';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/tracker/ArtifactType.class';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/tracker/ArtifactTypeHtml.class';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/tracker/ArtifactGroup.class';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/tracker/ArtifactCategory.class';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/tracker/ArtifactCanned.class';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/tracker/ArtifactResolution.class';

$group_id = $_POST['group_id'] ?? 0;
$func = $_POST['func'] ?? 0;

include('../../header.php');
$GLOBALS['xoopsOption']['template_main'] = 'xfmod_patadd.html';
$group = &group_get_object($group_id);
if (!$group || !is_object($group) || $group->isError()) {
    redirect_header(XOOPS_URL . '/', 2, 'ERROR<br>No Group');

    exit;
}

$sql = 'SELECT group_artifact_id'
    . ' FROM ' . $xoopsDB->prefix('xf_artifact_group_list')
    . ' WHERE group_id=' . $group_id;

    $result = $xoopsDB->query($sql);
$atid = unofficial_getDBResult($result, 0, 'group_artifact_id');
if (101 == $atid) {
    redirect_header(XOOPS_URL . '/', 2, 'ERROR<br>No Patch Tracker');
}

 $atid += 2;

$ts = MyTextSanitizer::getInstance();

$ath = new ArtifactTypeHtml($group, $atid);
$header = $ath->header();

//meta tag information
$metaTitle = ' Patch Submit - ' . $group->getPublicName();
$metaKeywords = project_getmetakeywords($group_id);
$metaDescription = str_replace('"', '&quot;', strip_tags($group->getDescription()));

$xoopsTpl->assign('xoops_pagetitle', $metaTitle);
$xoopsTpl->assign('xoops_meta_keywords', $metaKeywords);
$xoopsTpl->assign('xoops_meta_description', $metaDescription);

//project nav information
$xoopsTpl->assign('project_title', $header['title']);

if ('postadd' == $func) {
    $ah = new ArtifactHtml($ath);

    if (!$ah || !is_object($ah)) {
        redirect_header(Request::getString('HTTP_REFERER', '', 'SERVER'), 2, 'ERROR<br>Artifact could not be created');

        exit;
    }  

    if (empty($user_email)) {
        $user_email = false;
    } else {
        if (!checkEmail($user_email)) {
            redirect_header($GLOBALS['HTTP_REFERER'], 2, _XF_PAT_INVALIDMAIL);

            exit;
        }
    }

    if (!$ah->create($category_id, $artifact_group_id, $summary, $details, $assigned_to, $priority, $user_email)) {
        redirect_header($GLOBALS['HTTP_REFERER'], 2, $ah->getErrorMessage());

        exit;
    }  

    //

    //    Attach file to this Artifact.

    //

    if ($add_file) {
        $afh = new ArtifactFileHtml($ah);

        if (!$afh || !is_object($afh)) {
            $feedback .= 'Could Not Create File Object';
        } else {
            if (!$afh->upload($input_file, $input_file_name, $input_file_type, $file_description)) {
                $feedback .= ' Could Not Attach File to Item: ' . $afh->getErrorMessage() . '<br>';
            }
        }
    }

    $feedback .= ' ' . _XF_PAT_ITEMCREATED . ' ';

    include(XOOPS_ROOT_PATH . '/modules/xfmod/cronjobs/update_artifact.php');

    include(XOOPS_URL . '/index.php');

    exit();
}

    $content = '
	<P>';
    /*
        Show the free-form text submitted by the project admin
    */

    $content .= '<P>
	<FORM ACTION="' . $PHP_SELF . '?group_id=' . $group_id . '&atid=' . $ath->getID() . '" METHOD="POST" enctype="multipart/form-data">
	<INPUT TYPE="HIDDEN" NAME="func" VALUE="postadd">
    <INPUT TYPE="HIDDEN" NAME="group_id" VALUE="' . $group_id . '">
	<TABLE>
	<TR><TD VALIGN="TOP"><B>' . _XF_PAT_CATEGORY . ':</B><BR>';

        $content .= $ath->categoryBox('category_id');
        $content .= '&nbsp;<A HREF="' . XOOPS_URL . '/modules/xfmod/tracker/admin/?group_id=' . $group_id . '&atid=' . $ath->getID() . '&add_cat=1">(' . _XF_PAT_ADMINSMALL . ')</A>';
        $content .= '</TD><TD><B>' . _XF_PAT_GROUP . ':</B><BR>';
        $content .= $ath->artifactGroupBox('artifact_group_id');
        $content .= '&nbsp;<A HREF="' . XOOPS_URL . '/modules/xfmod/tracker/admin/?group_id=' . $group_id . '&atid=' . $ath->getID() . '&add_group=1">(' . _XF_PAT_ADMINSMALL . ')</A>';

    $content .= '</TD></TR>';

    if ($ath->userIsAdmin()) {
        $content .= '<TR><TD><B>' . _XF_G_ASSIGNEDTO . ':</B><BR>';

        $content .= $ath->technicianBox('assigned_to');

        $content .= '&nbsp;<A HREF="' . XOOPS_URL . '/modules/xfmod/tracker/admin/?group_id=' . $group_id . '&atid=' . $ath->getID() . '&update_users=1">(' . _XF_PAT_ADMINSMALL . ')</A>';

        $content .= '</TD><TD><B>' . _XF_G_PRIORITY . ':</B><BR>';

        $content .= build_priority_select_box('priority');

        $content .= '</TD></TR>';
    }

    $content .= '<TR><TD COLSPAN="2"><B>' . _XF_PAT_SUMMARY . ':</B><BR>
		<INPUT TYPE="TEXT" NAME="summary" SIZE="50" MAXLENGTH="70">
	</TD></TR>

	<TR><TD COLSPAN="2">
		<B>' . _XF_PAT_DETAILEDDESCRIPTION . ':</B>
		<P>
		<TEXTAREA NAME="details" ROWS="30" COLS="55" WRAP="HARD"></TEXTAREA>
	</TD></TR>

	<TR><TD COLSPAN="2">';

    if (!$xoopsUser) {
        // Make sure to use Novell Login instead of regular

        //$url = XOOPS_URL."/novelllogin.php?ref=".$PHP_SELF;

        foreach ($_GET as $key => $value) {
            $url .= '&' . $key;

            if (mb_strlen($key) > 0) {
                $url .= '=' . $value;
            }
        }

        $content .= "<h4><FONT COLOR='RED'>" . _XF_PAT_PLEASE
             . " <a href='" . XOOPS_URL . '/user.php?xoops_redirect=' . $_SERVER['PHP_SELF'] . '?' . urlencode($_SERVER['QUERY_STRING']) . "'>" . _XF_PAT_LOGIN . '</A></FONT></h4><BR>'
             . _XF_PAT_IFCANNOTLOGIN . ':<P>'
             . "<INPUT TYPE='TEXT' NAME='user_email' SIZE='30' MAXLENGTH='35'>";
    }

        $content .= '<P>
		<H4><FONT COLOR=RED>' . _XF_PAT_DONOTENTERPASSWORDS . '</FONT></H4>
		<P>
	</TD></TR>

	<TR><TD COLSPAN=2>
		<B>' . _XF_PAT_CHECKTOUPLOAD . ':</B> <input type="checkbox" name="add_file" VALUE="1">
		<P>
		<input type="file" name="input_file" size="30">
		<P>
		<B>' . _XF_PAT_FILEDESCRIPTION . ':</B><BR>
		<input type="text" name="file_description" size="40" maxlength="255">
		<P>
	</TD><TR>

	<TR><TD COLSPAN=2>
		<INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="' . _XF_G_SUBMIT . '">
		</FORM>
		<P>
	</TD></TR>

	</TABLE>';

$xoopsTpl->assign('content', $content);

include('../../footer.php');
