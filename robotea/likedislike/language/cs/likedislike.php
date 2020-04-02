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
	$lang = array('cs');
}

$lang = array_merge($lang, array(
	'DISLIKE_TEXT'		=> 'lidem se to nelíbí',
	'LIKE_TEXT'		=> 'lidem se to líbí',
    	'DISLIKE_TEXT_VOTE'	=> 'Hlasuj pro nelíbí',
	'LIKE_TEXT_VOTE'	=> 'Hlasuj pro líbí',
    	'DISLIKE_SORT'  	=> 'Podle "Nelíbí"',
    	'LIKE_SORT'     	=> 'Podle "Líbí"',
    	'TIME_SORT'     	=> 'Podle času',
	'ALREADY_VOTES_FOR_LIKE' => 'Už jsi hlasoval pro Líbí',
	'ALREADY_VOTES_FOR_DISLIKE' => 'Už jsi hlasoval pro Nelíbí'
));
