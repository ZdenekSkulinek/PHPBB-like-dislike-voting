<?php
/**
*
* @package Like/Dislike Mod
* @author Robotea technologies. s.r.o.
* @copyright (c) 2019
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'DISLIKE_TEXT'		=> 'people dislike it',
	'LIKE_TEXT'		=> 'people like it',
     'DISLIKE_TEXT_VOTE'	=> 'Vote for dislike',
	'LIKE_TEXT_VOTE'	=> 'Vote for like',
     'DISLIKE_SORT'  	=> 'By "Dislike"',
     'LIKE_SORT'     	=> 'By "Like"',
     'TIME_SORT'     	=> 'By time',
	'ALREADY_VOTES_FOR_LIKE' => 'You are already votes for like',
	'ALREADY_VOTES_FOR_DISLIKE' => 'You are already votes for dislike'
));
