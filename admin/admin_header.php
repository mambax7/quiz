<?php

/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright    XOOPS Project https://xoops.org/
 * @license      GNU GPL 2 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @package
 * @since
 * @author     XOOPS Development Team
 */

use Xmf\Module\Admin;
use XoopsModules\Quiz\{
    Helper
};
/** @var Admin $adminObject */
/** @var Helper $helper */

require dirname(__DIR__) . '/preloads/autoloader.php';

require dirname(__DIR__, 3) . '/include/cp_header.php';
require dirname(__DIR__, 3) . '/class/xoopsformloader.php';
require  dirname(__DIR__) . '/include/common.php';


$moduleDirName = basename(dirname(__DIR__));
$moduleDirNameUpper = mb_strtoupper($moduleDirName);
$helper = Helper::getInstance();

$adminObject = Admin::getInstance();

// Load language files
$helper->loadLanguage('admin');
$helper->loadLanguage('modinfo');
$helper->loadLanguage('common');
$helper->loadLanguage('main');


/** @var \XoopsPersistableObjectHandler $quizzesHandler */
$quizzesHandler  = $helper->getHandler('Quizzes');
/** @var \XoopsPersistableObjectHandler $categoriesHandler */
$categoriesHandler  = $helper->getHandler('Categories');
/** @var \XoopsPersistableObjectHandler $scoresHandler */
$scoresHandler  = $helper->getHandler('Scores');
/** @var \XoopsPersistableObjectHandler $useranswersHandler */
$useranswersHandler  = $helper->getHandler('Useranswers');
/** @var \XoopsPersistableObjectHandler $questionsHandler */
$questionsHandler  = $helper->getHandler('Questions');
/** @var \XoopsPersistableObjectHandler $answersHandler */
$answersHandler  = $helper->getHandler('Answers');

//$myts = \MyTextSanitizer::getInstance();

//if (!isset($GLOBALS['xoopsTpl']) || !($GLOBALS['xoopsTpl'] instanceof XoopsTpl)) {
//    require $GLOBALS['xoops']->path('class/template.php');
//    $xoopsTpl = new \XoopsTpl();
//}

//$pathIcon16      = Xmf\Module\Admin::iconUrl('', 16);
//$pathIcon32      = Xmf\Module\Admin::iconUrl('', 32);
//$pathModIcon32 = $helper->getModule()->getInfo('modicons32');

// Local icons path
//$xoopsTpl->assign('pathIcon16', $pathIcon16);
//$xoopsTpl->assign('pathIcon32', $pathIcon32);

//Module specific elements
//require $GLOBALS['xoops']->path("modules/{$moduleDirName}/include/functions.php");
//require $GLOBALS['xoops']->path("modules/{$moduleDirName}/include/config.php");

//xoops_cp_header();
