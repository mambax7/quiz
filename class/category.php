<?php

/**
 * ****************************************************************************
 * xquiz - MODULE FOR XOOPS
 * Copyright (c) Mojtaba Jamali of persian xoops project (http://www.irxoops.org/)
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright          XOOPS Project (https://xoops.org)
 * @license            http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package            xquiz
 * @author             Mojtaba Jamali(jamali.mojtaba@gmail.com)
 * @version            $Id$
 *
 * Version : $Id:
 * ****************************************************************************
 */
include_once XOOPS_ROOT_PATH . '/class/xoopstree.php';

class QuizCategory extends XoopsTree
{
    /** @var string table with parent-child structure */
    public $table;
    /** @var string name of unique id for records in table $table */
    public $id;
    /** @var string name of parent id used in table $table */
    public $pid;
    /** @var string specifies the order of query results */
    public $order;
    /** @var string name of a field in table $table which will be used when  selection box and paths are generated */
    public $title;
    /** @var object an instance of the database object */
    public $db;

    /** constructor of class XoopsTree
     * Sets the names of table, unique id, and parent id
     * @param string $table_name Name of table containing the parent-child structure
     * @param string $id_name    Name of the unique id field in the table
     * @param Name   $pid_name   of the parent id field in the table
     */
    public function __construct($table_name, $id_name, $pid_name)
    {
        //$this->db = Database::getInstance();
        $this->db    = XoopsDatabaseFactory::getDatabaseConnection();
        $this->table = $table_name;
        $this->id    = $id_name;
        $this->pid   = $pid_name;
    }

    /** Returns an array of first child objects for a given id($sel_id)
     * @param int    $sel_id
     * @param string $order Sort field for the list
     * @return array $arr
     */

    /** Returns an array of ALL parent ids for a given id($sel_id)
     * @param int    $sel_id
     * @param string $order
     * @param array  $idarray
     * @return array $idarray
     */
    public function getAllParentId($sel_id, $order = '', $idarray = [])
    {
        $sel_id = (int)$sel_id;
        $sql    = 'SELECT ' . $this->pid . ' FROM ' . $this->table . ' WHERE ' . $this->id . '="' . $sel_id . '"';
        if ('' != $order) {
            $sql .= ' ORDER BY ' . $order;
        }
        $result = $this->db->query($sql);
        [$r_id] = $this->db->fetchRow($result);
        if (0 == $r_id) {
            return $idarray;
        }
        array_push($idarray, $r_id);
        $idarray = $this->getAllParentId($r_id, $order, $idarray);
        return $idarray;
    }

    /** Makes a nicely ordered selection box
     * @param string $title     Field containing the items to display in the list
     * @param string $order     Sort order of the options
     * @param int    $preset_id is used to specify a preselected item
     * @param int    $none      set to 1 to add an option with value 0
     * @param string $sel_name  Name of the select element
     * @param string $onchange  Action to take when the selection is changed
     * @param int    $se
     */
    public function makeMySelBox($title, $order = '', $preset_id = 0, $none = 0, $sel_name = '', $onchange = '', $se = 0)
    {
        if ('' == $sel_name) {
            $sel_name = $this->id;
        }
        echo "<select name='" . $sel_name . "'";
        if ('' != $onchange) {
            echo " onchange='" . $onchange . "'";
        }
        echo ">\n";
        $sql = 'SELECT ' . $this->id . ', ' . $title . ' FROM ' . $this->table . ' WHERE ' . $this->pid . "='0'";
        if ('' != $order) {
            $sql .= " ORDER BY $order";
        }
        $result = $this->db->query($sql);
        if ($none) {
            $val = (0 == $se) ? 0 : -1;
            echo "<option value='$val'>----------</option>\n";
        }
        while (list($catid, $name) = $this->db->fetchRow($result)) {
            $sel = '';
            if ($catid == $preset_id) {
                $sel = " selected='selected'";
            }
            echo "<option value='$catid'$sel>$name</option>\n";
            $sel = '';
            $arr = $this->getChildTreeArray($catid, $order);
            foreach ($arr as $option) {
                $option['prefix'] = str_replace('.', '--', $option['prefix']);
                $catpath          = $option['prefix'] . '&nbsp;' . $option[$title];
                if ($option[$this->id] == $preset_id) {
                    $sel = " selected='selected'";
                }
                echo "<option value='" . $option[$this->id] . "'$sel>$catpath</option>\n";
                $sel = '';
            }
        }
        echo "</select>\n";
    }

    /**
     * @param int    $sel_id
     * @param string $order
     * @param array  $parray
     * @return array $parray
     */
    public function getAllChild($sel_id = 0, $order = '', $parray = [])
    {
        $sel_id = (int)$sel_id;
        $sql    = 'SELECT * FROM ' . $this->table . ' WHERE ' . $this->pid . '="' . $sel_id . '"';
        if ('' != $order) {
            $sql .= ' ORDER BY ' . $order;
        }
        $result = $this->db->query($sql);
        $count  = $this->db->getRowsNum($result);
        if (0 == $count) {
            return $parray;
        }
        while ($row = $this->db->fetchArray($result)) {
            array_push($parray, $row);
            $parray = $this->getAllChild($row[$this->id], $order, $parray);
        }
        return $parray;
    }

    /**
     * @param int    $sel_id
     * @param string $order
     * @param array  $parray
     * @param string $r_prefix
     * @return array $parray
     */
    public function getChildTreeArray($sel_id = 0, $order = '', $parray = [], $r_prefix = '')
    {
        $sel_id = (int)$sel_id;
        $sql    = 'SELECT * FROM ' . $this->table . ' WHERE ' . $this->pid . '="' . $sel_id . '"';
        if ('' != $order) {
            $sql .= ' ORDER BY ' . $order;
        }
        $result = $this->db->query($sql);
        $count  = $this->db->getRowsNum($result);
        if (0 == $count) {
            return $parray;
        }
        while ($row = $this->db->fetchArray($result)) {
            $row['prefix'] = $r_prefix . '.';
            array_push($parray, $row);
            $parray = $this->getChildTreeArray($row[$this->id], $order, $parray, $row['prefix']);
        }
        return $parray;
    }

    public function getList($eu, $limit, $order = 0)
    {
        $listCategory = [];
        $q            = 1;
        $query        = ' SELECT * FROM ' . $this->table;
        if ('' != $order) {
            $query .= ' ORDER BY ' . $order;
        }
        $query .= ' LIMIT ' . $eu . ' , ' . $limit;

        $query = $this->db->query($query);
        while ($myrow = $this->db->fetchArray($query)) {
            $listCategory[$q]['cid']         = $myrow['cid'];
            $listCategory[$q]['pid']         = $myrow['pid'];
            $listCategory[$q]['title']       = $myrow['title'];
            $listCategory[$q]['imgurl']      = $myrow['imgurl'];
            $listCategory[$q]['weight']      = $myrow['weight'];
            $listCategory[$q]['description'] = $myrow['description'];
            $q++;
        }
        return $listCategory;
    }

    public function getNumberList()
    {
        $query = ' SELECT * FROM ' . $this->table;
        $query = $this->db->query($query);
        $myrow = $this->db->fetchArray($query);
        return count($myrow);;
    }

    public function getCategory($cid)
    {
        $sel_id = (int)$cid;
        $arr    = [];
        $sql    = 'SELECT * FROM ' . $this->table . ' WHERE ' . $this->id . '=' . $sel_id . '';
        $result = $this->db->query($sql);
        $count  = $this->db->getRowsNum($result);
        if (0 == $count) {
            return $arr;
        }
        while ($myrow = $this->db->fetchArray($result)) {
            array_push($arr, $myrow);
        }
        return $arr;
    }

    public function categoryPid($sel_id)
    {
        global $xoopsDB;
        $sql    = 'SELECT pid FROM ' . $this->table . ' WHERE ' . $this->id . '=' . $sel_id . '';
        $result = $this->db->query($sql);
        $myrow  = $xoopsDB->fetchArray($result);
        return $myrow['pid'];
    }

    #region retrive QuizCategory from database
    public static function retriveCategory($eId)
    {
        global $xoopsDB;
        $query = $xoopsDB->query('SELECT cid,title FROM ' . $xoopsDB->prefix('xquiz_categories') . " WHERE cid = '$eId'");
        $myrow = $xoopsDB->fetchArray($query);
        return $myrow;
    }
    #endregion
    #region retrive permited category from database
    public function getPermChildArray($sel_id = 0, $order = '', $parray = [], $r_prefix = '')
    {
        global $xoopsUser, $module_id;
        $myts   = MyTextSanitizer::getInstance();
        $sel_id = (int)$sel_id;
        $sql    = 'SELECT * FROM ' . $this->table . ' WHERE ' . $this->pid . '="' . $sel_id . '"';
        if ('' != $order) {
            $sql .= ' ORDER BY ' . $order;
        }
        $result = $this->db->query($sql);
        $count  = $this->db->getRowsNum($result);
        if (0 == $count) {
            return $parray;
        }
        $perm_name = 'quiz_view';
        if ($xoopsUser) {
            $groups = $xoopsUser->getGroups();
        } else {
            $groups = XOOPS_GROUP_ANONYMOUS;
        }
        $gperm_handler = xoops_getHandler('groupperm');

        while ($row = $this->db->fetchArray($result)) {
            $row['prefix'] = $r_prefix . '.';
            if (!$gperm_handler->checkRight($perm_name, $row['cid'], $groups, $module_id)) {
                continue;
            }
            $row['description'] = $myts->previewTarea($row['description'], 1, 1, 1, 1, 1);
            array_push($parray, $row);
        }
        return $parray;
    }

    #endregion

    public static function addCategory($title, $pid, $desc, $imgurl, $weight)
    {
        global $xoopsDB;
        $query = 'Insert into ' . $xoopsDB->prefix('xquiz_categories') . "(cid ,pid ,title ,description ,imgurl ,weight)
				VALUES (NULL , '$pid', '$title', '$desc', '$imgurl', '$weight');";
        $res   = $xoopsDB->query($query);

        if (!$res) {
            throw new Exception(_AM_XQUIZ_QUEST_DATABASE);
        }

        return $xoopsDB->getInsertId();
    }

    public static function editCategory($cid, $title, $pid, $desc, $imgurl, $weight)
    {
        global $xoopsDB;
        $query = 'UPDATE ' . $xoopsDB->prefix('xquiz_categories') . " SET 
					  pid = '$pid'
					 ,title = '$title'
					 ,description = '$desc'
					 ,imgurl = '$imgurl'
					 ,weight = '$weight'
					 WHERE cid = '$cid' ";

        $res = $xoopsDB->query($query);

        if (!$res) {
            throw new Exception(_AM_XQUIZ_QUEST_DATABASE);
        }
    }

    public static function deleteCategory($id)
    {
        global $xoopsDB;
        $xt   = new QuizCategory($xoopsDB->prefix('xquiz_categories'), 'cid', 'pid');
        $list = $xt->getAllChildId($id);

        global $module_id;
        $perm_name = 'quiz_view';
        $query     = 'DELETE FROM ' . $xoopsDB->prefix('xquiz_categories') . " WHERE  
					  cid = '$id' ";
        $res       = $xoopsDB->query($query);
        xoops_groupperm_deletebymoditem($module_id, $perm_name, $id);
        if (!$res) {
            throw new Exception(_AM_XQUIZ_QUEST_DATABASE);
        }
        //delet quiz of category
        $query = 'DELETE FROM ' . $xoopsDB->prefix('xquiz_quizzes') . " WHERE  
					  cid = '$id' ";
        $res   = $xoopsDB->query($query);
        xoops_groupperm_deletebymoditem($module_id, $perm_name, $id);
        if (!$res) {
            throw new Exception(_AM_XQUIZ_QUEST_DATABASE);
        }

        //Delete subcategories and subquizs
        foreach ($list as $cid) {
            $perm_name = 'quiz_view';
            $query     = 'DELETE FROM ' . $xoopsDB->prefix('xquiz_categories') . " WHERE  
						cid = '$cid' ";
            $res       = $xoopsDB->query($query);
            xoops_groupperm_deletebymoditem($module_id, $perm_name, $cid);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public static function category_permissionForm()
    {
        global $module_id, $xoopsDB;
        $xt = new QuizCategory($xoopsDB->prefix('xquiz_categories'), 'cid', 'pid');
        if (!$xt->getChildTreeArray(0)) {
            throw new Exception(_AM_XQUIZ_NO_CATEGORY);
        }
        $listCategory  = $xt->getChildTreeArray(0, 'title');
        $title_of_form = _AM_XQUIZ_PERM_FORM_TITLE;
        $perm_name     = 'quiz_view';
        $perm_desc     = _AM_XQUIZ_PERM_FORM_DESC;
        $form          = new XoopsGroupPermForm($title_of_form, $module_id, $perm_name, $perm_desc);

        foreach ($listCategory as $key) {
            $form->addItem($key['cid'], $key['title'], $key['pid']);
        }
        echo $form->render();
    }

    public static function checkExistCategory($cid)
    {
        global $xoopsDB;
        $query = $xoopsDB->query('SELECT * FROM ' . $xoopsDB->prefix('xquiz_categories') . " WHERE cid='$cid'");
        $res   = $xoopsDB->getRowsNum($query);

        if ($res > 0) {
            return true;
        } else {
            return false;
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////
function showCategories($start, $limit)
{
    global $xoopsDB;
    $xt = new QuizCategory($xoopsDB->prefix('xquiz_categories'), 'cid', 'pid');

    $listCategory = $xt->getList($start, $limit, 'title');
    //$nume = $xt->getNumberList();
    $nume = count($listCategory);
    quiz_collapsableBar('newsub', 'topnewsubicon');
    $temp = "<img onclick=\"toggle('toptable'); toggleIcon('toptableicon');\" id='topnewsubicon' name='topnewsubicon' src='" . XOOPS_URL . "/modules/xquiz/assets/images/close12.gif' alt='' />
				 </a>&nbsp;" . _AM_XQUIZ_CATEGORIES . "</h4><br/>
					<div id='newsub' style='text-align:left;'>
					<table width='100%' cellspacing='1' cellpadding='3' border='0' class='outer'>
						<tr class='odd'>
							<td>
							<form method='get' action='index.php'>
							<input type='hidden' name='op' value='Category'>
							<input type='hidden' name='act' value='add'>
							<input type='submit' value='" . _AM_XQUIZ_NEW_CATEGORY . "'>
							</form>
							</td>
						</tr>
					</table>
					<table width='100%' cellspacing='1' cellpadding='3' border='0' class='outer'>
					<tr class='bg3'>
						<th>
							" . _AM_XQUIZ_CATEGORY_TITLE . '
						</th>
						<th>
							' . _AM_XQUIZ_CATEGORY_WEIGHT . '
						</th>
						<th>
							' . _AM_XQUIZ_ACTION . '
						</th>
					</tr>';

    $class     = 'even';
    $delImage  = '<img src= "' . XOOPS_URL . '/modules/xquiz/assets/images/delete.gif " title=' . _AM_XQUIZ_DEL . " alt='' >";
    $editImage = '<img src= "' . XOOPS_URL . '/modules/xquiz/assets/images/edit.gif " title=' . _AM_XQUIZ_EDIT . " alt='' >";
    $goImage   = '<img src= "' . XOOPS_URL . '/modules/xquiz/assets/images/cat.gif " title=' . _AM_XQUIZ_EDIT . " alt='' >";

    foreach ($listCategory as $key) {
        $class = ('even' == $class) ? 'odd' : 'even';
        $temp  .= "
			<tr class='"
                  . $class
                  . "'>
				<td>&nbsp;
				"
                  . $goImage
                  . '
					<a href="'
                  . XOOPS_URL
                  . '/modules/xquiz/index.php?cid='
                  . $key['cid']
                  . '"><img src="'
                  . XOOPS_URL
                  . '/uploads/xquiz/category/'
                  . $key['imgurl']
                  . "\" width='40px' height='40px' align='left' style='padding:5px'></a>&nbsp;<a href=\""
                  . XOOPS_URL
                  . '/modules/xquiz/index.php?cid='
                  . $key['cid']
                  . '">'
                  . $key['title']
                  . '</a><br>&nbsp;<small>'
                  . $key['description']
                  . '</small>
				</td>
				<td>
				'
                  . $key['weight']
                  . '
				</td>
				<td>
				<a href="'
                  . XOOPS_URL
                  . '/modules/xquiz/admin/index.php?op=Category&act=del&Id='
                  . $key['cid']
                  . '">
				'
                  . $delImage
                  . '
				</a>
				<a href="'
                  . XOOPS_URL
                  . '/modules/xquiz/admin/index.php?op=Category&act=edit&Id='
                  . $key['cid']
                  . '">
				'
                  . $editImage
                  . '
				</td>
				</tr>';
    }

    $temp .= '</table></div>';
    echo $temp;

    $nav = new XoopsPageNav($nume, $limit, $start, 'start', 'op=Category');
    echo "<div align='center'>" . $nav->renderImageNav() . '</div><br />';
}

function CategoryForm($op = 'add', $eId = 0)
{
    global $xoopsDB, $xoopsModule, $xoopsModuleConfig;
    $xt               = new QuizCategory($xoopsDB->prefix('xquiz_categories'), 'cid', 'pid');
    $myts             = MyTextSanitizer::getInstance();
    $maxuploadsize    = $xoopsModuleConfig['maxuploadsize'];
    $addCategory_form = new XoopsThemeForm(
        _AM_XQUIZ_NEW_CATEGORY, 'addcategoyfrom', XOOPS_URL . '/modules/xquiz/admin/backend.php', 'post', true
    );
    $addCategory_form->setExtra('enctype="multipart/form-data"');
    // Permissions
    $member_handler = xoops_getHandler('member');
    $group_list     = $member_handler->getGroupList();
    $gperm_handler  = xoops_getHandler('groupperm');
    $full_list      = array_keys($group_list);
    ////////////////
    if ('edit' == $op) {
        $category           = $xt->getCategory($eId);
        $category_id_v      = $eId;
        $category_title_v   = $myts->htmlSpecialChars($category[0]['title']);
        $category_desc_v    = $myts->htmlSpecialChars($category[0]['description']);
        $parent             = $xt->getAllParentId($category_id_v);
        $category_parent_id = (!empty($parent)) ? $parent[0] : 0;
        $category_weight_v  = $category[0]['weight'];
        $topicimage         = $myts->htmlSpecialChars($category[0]['imgurl']);

        $groups_ids                    = $gperm_handler->getGroupIds('quiz_view', $category_id_v, $xoopsModule->getVar('mid'));
        $groups_ids                    = array_values($groups_ids);
        $groups_quiz_can_view_checkbox = new XoopsFormCheckBox(_AM_XQUIZ_VIEWFORM, 'groups_quiz_can_view[]', $groups_ids);

        $category_id = new XoopsFormHidden('cateId', $category_id_v);
        $addCategory_form->addElement($category_id);
        $submit_button = new XoopsFormButton('', 'editCateSubmit', _AM_XQUIZ_SUBMIT, 'submit');
    } elseif ('add' == $op) {
        $category_title_v              = '';
        $category_desc_v               = '';
        $category_parent_id            = 0;
        $category_weight_v             = 0;
        $topicimage                    = 'blank.png';
        $groups_quiz_can_view_checkbox = new XoopsFormCheckBox(_AM_XQUIZ_VIEWFORM, 'groups_quiz_can_view[]', $full_list);
        $submit_button                 = new XoopsFormButton('', 'addCateSubmit', _AM_XQUIZ_SUBMIT, 'submit');
    }

    $category_title = new XoopsFormText(_AM_XQUIZ_CATEGORY_TITLE, 'cateTitle', 50, 100, $category_title_v);
    ob_start();
    $xt->makeMySelBox('title', 'cid', $category_parent_id, 1, 'cateParent');
    $category_parent = new XoopsFormLabel(_AM_XQUIZ_CATEGORY_PARENT, ob_get_contents());
    ob_end_clean();
    //$category_description = new XoopsFormDhtmlTextArea(_AM_XQUIZ_CATEGORY_DESC, "cateDesc", $category_desc_v);

    $options_tray = new XoopsFormElementTray(_AM_XQUIZ_CATEGORY_DESC, '<br />');
    if (class_exists('XoopsFormEditor')) {
        $options['name']   = 'cateDesc';
        $options['value']  = $category_desc_v;
        $options['rows']   = 25;
        $options['cols']   = '100%';
        $options['width']  = '100%';
        $options['height'] = '600px';
        $contents_contents = new XoopsFormEditor('', $xoopsModuleConfig['use_wysiwyg'], $options, $nohtml = false, $onfailure = 'textarea');
        $options_tray->addElement($contents_contents);
    } else {
        $contents_contents = new XoopsFormDhtmlTextArea('', 'cateDesc', $category_desc_v);
        $options_tray->addElement($contents_contents);
    }

    $category_weight = new XoopsFormText(_AM_XQUIZ_CATEGORY_WEIGHT, 'cateWeight', 5, 3, $category_weight_v);
    // $category_token = new XoopsFormHidden("XOOPS_TOKEN_REQUEST", $GLOBALS['xoopsSecurity']->createToken());

    $uploadirectory = 'uploads/' . $xoopsModule->dirname() . '/category';
    $imgtray        = new XoopsFormElementTray(_AM_XQUIZ_CATEGORYIMG, '<br />');

    $imgpath      = sprintf(_AM_XQUIZ_UPLOADEDIMG, 'uploads/' . $xoopsModule->dirname() . '/category/');
    $imageselect  = new XoopsFormSelect($imgpath, 'topic_imgurl', $topicimage);
    $topics_array = XoopsLists:: getImgListAsArray(XOOPS_ROOT_PATH . '/uploads/xquiz/category/');
    foreach ($topics_array as $image) {
        $imageselect->addOption((string)$image, $image);
    }
    $imageselect->setExtra("onchange='showImgSelected(\"image3\", \"topic_imgurl\", \"" . $uploadirectory . '", "", "' . XOOPS_URL . "\")'");
    $imgtray->addElement($imageselect, false);
    $imgtray->addElement(new XoopsFormLabel('', "<br /><img src='" . XOOPS_URL . '/' . $uploadirectory . '/' . $topicimage . "' name='image3' id='image3' alt='' />"));

    $uploadfolder = sprintf(_AM_XQUIZ_UPLOAD_WARNING, XOOPS_URL . '/uploads/' . $xoopsModule->dirname() . '/category');
    $fileseltray  = new XoopsFormElementTray('', '<br />');
    $fileseltray->addElement(new XoopsFormFile(_AM_XQUIZ_CATEGORY_PICTURE, 'attachedfile', $maxuploadsize), false);
    $fileseltray->addElement(new XoopsFormLabel($uploadfolder), false);
    $imgtray->addElement($fileseltray);

    $button_tray = new XoopsFormElementTray('', '');
    $button_tray->addElement($submit_button);

    $addCategory_form->addElement($category_title, true);
    $addCategory_form->addElement($category_parent, true);
    $addCategory_form->addElement($options_tray);
    $addCategory_form->addElement($imgtray);
    $addCategory_form->addElement($category_weight, true);
    $groups_quiz_can_view_checkbox->addOptionArray($group_list);
    $addCategory_form->addElement($groups_quiz_can_view_checkbox);
    //$addCategory_form->addElement($category_token, true);
    $addCategory_form->addElement($button_tray);

    quiz_collapsableBar('newquiz', 'topnewquiz');
    echo "<img onclick=\"toggle('toptable'); toggleIcon('toptableicon');\" id='topnewquiz' name='topnewquiz' src='" . XOOPS_URL . "/modules/xquiz/assets/images/close12.gif' alt='' />
		 	</a>&nbsp;" . _AM_XQUIZ_CATEGORY_NEW . "</h4><br/>
				<div id='newquiz' style='text-align: center;'>";
    $addCategory_form->display();
    echo '</div>';
}

#region create confirm form for delete question
function confirmForm($id)
{
    $delCategory_form = new XoopsThemeForm(
        _AM_XQUIZ_DELCATEGORY_FORM, 'delcategoryfrom', XOOPS_URL . '/modules/xquiz/admin/backend.php', 'post', true
    );
    $category_id      = new XoopsFormHidden('categoryId', $id);
    $category_confirm = new XoopsFormRadioYN(_AM_XQUIZ_DELETE_CAPTION, 'delConfirm', 0);
    $submit_button    = new XoopsFormButton('', 'delCateSubmit', _AM_XQUIZ_SUBMIT, 'submit');
    //$category_token = new XoopsFormHidden("XOOPS_TOKEN_REQUEST", $GLOBALS['xoopsSecurity']->createToken());

    $delCategory_form->addElement($category_id);
    //$delCategory_form->addElement($category_token, true);
    $delCategory_form->addElement($category_confirm, true);
    $delCategory_form->addElement($submit_button);

    quiz_collapsableBar('newquiz', 'topnewquiz');
    echo "<img onclick=\"toggle('toptable'); toggleIcon('toptableicon');\" id='topnewquiz' name='topnewquiz' src='" . XOOPS_URL . "/modules/xquiz/assets/images/close12.gif' alt='' />
				 	</a>&nbsp;" . _AM_XQUIZ_DELETE . "</h4><br/>
						<div id='newquiz' style='text-align: center;'>";
    $delCategory_form->display();
    echo '</div>';
}
#end region
