<?php

require_once('../../mainfile.php');

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

$group_id = $_GET['group'] ?? 0;

$GLOBALS['xoopsOption']['template_main'] = 'xfmod_patindex.html';
    include('../../header.php');
    $xoopsTpl->assign('feedback', $feedback);

    $sql_groups = $xoopsDB->query('SELECT * FROM ' . $xoopsDB->prefix('xf_groups') . ' ORDER BY group_name ASC');
    $GROUPS_ids = util_result_column_to_array($sql_groups, 0);
    $GROUPS_val = util_result_column_to_array($sql_groups, 1);

    $xoopsTpl->assign('project_box', html_build_select_box_from_arrays($GROUPS_ids, $GROUPS_val, 'group_id', $group_id, false));

    $xoopsTpl->assign('helptitel', _XF_PAT_HELPTITEL);
    $xoopsTpl->assign('helptext1', _XF_PAT_HELPTEXT1);
    $xoopsTpl->assign('choicetitel', _XF_PAT_CHOICE);
    $xoopsTpl->assign('choice', _XF_PAT_CHOICEBOX);

    include('../../footer.php');
