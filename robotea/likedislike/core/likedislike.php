<?php
/**
*
* @package Like/Dislike Mod
* @author Robotea technologies. s.r.o.
* @copyright (c) 2019
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace robotea\likedislike\core;

define('MODE_LIKE', 1);
define('MODE_DISLIKE', 2);
global $table_prefix;
define('LIKEDISLIKE_TABLE', $table_prefix . 'likesdislikes');

class likedislike
{
    	protected $db;
    	protected $likes_table;
    	protected $root_path;
    	protected $php_ext;

    	protected $like_data_likes;
    	protected $like_data_dislikes;
    	protected $like_data_userlike;
    	protected $like_data_topic_id;
    	protected $like_data_forum_id;
    	protected $like_data_user_id;

    	public function __construct(
        	\phpbb\db\driver\driver_interface $db,
        	$likes_table,
        	$root_path,
        	$php_ext
    	) {
        	$this->db = $db;
        	$this->likes_table = $likes_table;
        	$this->root_path = $root_path;
        	$this->php_ext = $php_ext;

        	$this->like_data_likes = array();
        	$this->like_data_dislikes = array();
        	$this->like_data_userlike = array();
        	$this->like_data_userdeslike = array();
        	$this->like_data_topic_id = -1;
        	$this->like_data_forum_id = -1;
        	$this->like_data_user_id = -1;
    	}

    	public function get_like_exists($forum_id, $topic_id, $post_id, $user_id)
    	{
        	if ($forum_id == $this->like_data_forum_id &&
               $topic_id == $this->like_data_topic_id &&
               $user_id == $this->like_data_user_id) {
            	return
               	((isset($this->like_data_userlike[$post_id]) && $this->like_data_userlike[$post_id]) ||
                	(isset($this->like_data_userdislike[$post_id]) && $this->like_data_userdislike[$post_id]));
        	}
        	$sql = "SELECT COUNT(like_id) AS like_id_count
			FROM " . $this->likes_table . "
			WHERE forum_id = $forum_id
			AND topic_id = $topic_id
			AND user_id = $user_id
            	AND post_id = $post_id";

        	$result = $this->db->sql_query($sql);
        	$like_count = (int)$this->db->sql_fetchfield('like_id_count', false, $result);
        	$this->db->sql_freeresult($result);
        	return $like_count > 0;
    	}

    	public function submit_like($forum_id, $topic_id, $user_id, $likepost_id, $likemod)
    	{
        	if (!$this->get_like_exists($forum_id, $topic_id, $likepost_id, $user_id)) {
            	$data = array(
               	'forum_id'	=> $forum_id,
                	'topic_id'	=> $topic_id,
                	'user_id'		=> $user_id,
                	'post_id'		=> $likepost_id,
                    'mod_like'  	=> $likemod
            	);

            	$sql = "INSERT INTO " . $this->likes_table .
            	$this->db->sql_build_array('INSERT', $data);

            	$result = $this->db->sql_query($sql);
            	$this->db->sql_freeresult($result);
        	} else {
            	$data = array(
                	'forum_id'	=> $forum_id,
                	'topic_id'	=> $topic_id,
                	'user_id'		=> $user_id,
                	'post_id'		=> $likepost_id,
                	'mod_like'  	=> $likemod
            	);

            	$sql = "UPDATE " . $this->likes_table . " SET mod_like=".$likemod.
               	" WHERE ".
                	" forum_id=".$forum_id." AND".
                	" topic_id=".$topic_id." AND".
                	" user_id=".$user_id." AND".
               	" post_id=".$likepost_id;

            	$result = $this->db->sql_query($sql);
            	$this->db->sql_freeresult($result);
        	}
    	}

    	public function get_like_ranks($forum_id, $topic_id, $post_id, $user_id)
    	{
        	if ($forum_id == $this->like_data_forum_id &&
             	$topic_id == $this->like_data_topic_id &&
               $user_id == $this->like_data_user_id) {
            	$userlike = isset($this->like_data_userlike[$post_id]) && $this->like_data_userlike[$post_id];
            	$userdislike = isset($this->like_data_userdislike[$post_id]) && $this->like_data_userdislike[$post_id];
            	$like_count = isset($this->like_data_likes[$post_id]) ? $this->like_data_likes[$post_id] : 0;
            	$dislike_count = isset($this->like_data_dislikes[$post_id]) ? $this->like_data_dislikes[$post_id] : 0;
            	return array('LIKES' => $like_count, 'DISLIKES' => $dislike_count, 'USERLIKE' => $userlike, 'USERDISLIKE'=>$userdislike);
        	}

        	$sql = "SELECT COUNT(like_id) AS like_id_count
			FROM " . $this->likes_table . "
			WHERE forum_id = $forum_id
			AND topic_id = $topic_id
			AND post_id = $post_id
            	AND mod_like = ".MODE_LIKE;

        	$result = $this->db->sql_query($sql);
        	$like_count = (int)$this->db->sql_fetchfield('like_id_count', false, $result);
        	$this->db->sql_freeresult($result);

        	$sql = "SELECT COUNT(like_id) AS like_id_count
			FROM " . $this->likes_table . "
			WHERE forum_id = $forum_id
			AND topic_id = $topic_id
			AND post_id = $post_id
            	AND mod_like = ".MODE_DISLIKE;

        	$result = $this->db->sql_query($sql);
        	$dislike_count = (int)$this->db->sql_fetchfield('like_id_count', false, $result);
        	$this->db->sql_freeresult($result);


        	$sql = "SELECT mod_like AS like_id_count
			FROM " . $this->likes_table . "
			WHERE forum_id = $forum_id
			AND topic_id = $topic_id
			AND user_id = $user_id
            	AND post_id = $post_id";

        	$result = $this->db->sql_query($sql);
        	$userlike = false;
        	$userdislike = false;
        	if ($row = $this->db->sql_fetchrow($result)) {
            	if ($row['mod_like'] == MODE_DISLIKE) {
                	$userdislike = true;
            	} else {
                	$userlike = true;
            	}
        	}
        	$this->db->sql_freeresult($result);
        	return array('LIKES' => $like_count, 'DISLIKES' => $dislike_count, 'USERLIKE' => $userlike, 'USERDISLIKE'=>$userdislike);
    	}

    	public function prepare_likes($forum_id, $topic_id, $user_id)
    	{
        	$this->like_data_likes = array();
        	$this->like_data_dislikes = array();
        	$this->like_data_userlike = array();
        	$this->like_data_userdislike = array();
        	$this->like_data_topic_id = $topic_id;
        	$this->like_data_forum_id = $forum_id;
        	$this->like_data_user_id = $user_id;

        	$sql = "SELECT COUNT(like_id) AS like_id_count, mod_like, post_id
			FROM " . $this->likes_table . "
			GROUP BY mod_like,post_id";

        	$result = $this->db->sql_query($sql);
        	while ($row = $this->db->sql_fetchrow($result)) {
            	if ($row['mod_like'] == MODE_LIKE) {
                	$this->like_data_likes[$row['post_id']] = $row['like_id_count'];
            	} else {
                	$this->like_data_dislikes[$row['post_id']] = $row['like_id_count'];
            	}
        	}
        	$this->db->sql_freeresult($result);

        	$sql = "SELECT post_id,mod_like
            	FROM " . $this->likes_table . "
            	WHERE forum_id = $forum_id
            	AND topic_id = $topic_id
            	AND user_id = $user_id";
        	$result = $this->db->sql_query($sql);
        	while ($row = $this->db->sql_fetchrow($result)) {
            	if ($row['mod_like'] == MODE_LIKE) {
                	$this->like_data_userlike[$row['post_id']] = true;
            	} else {
                	$this->like_data_userdislike[$row['post_id']] = true;
            	}
        	}
        	$this->db->sql_freeresult($result);
    	}

    	public function sort(&$event, $sorting, $user_id)
    	{
        	global $config;
        	global $phpbb_container;
        	$phpbb_content_visibility = $phpbb_container->get('content.visibility');

        	$store_reverse = false;
        	$sql_limit = $config['posts_per_page'];
        	$sql_sort_order = $direction = '';
        	$start = $event['start'];
        	$forum_id = $event['forum_id'];
        	$topic_id = $event['topic_id'];

        	if ($sorting == 'dislikes') {
            	$join_likedislike_sql = ' LEFT JOIN ' . LIKEDISLIKE_TABLE . ' ld ON p.post_id=ld.post_id AND ld.mod_like=2 ';
            	$sql_sort_order = 'ldcount DESC';
            	$sql_count = ', COUNT(ld.mod_like=2) AS ldcount ';
        	} elseif ($sorting == 'time') {
            	$join_likedislike_sql = '';
            	$sql_sort_order = 'p.post_time DESC';
            	$sql_count = '';
        	} else { //'likes'
            	$join_likedislike_sql = ' LEFT JOIN ' . LIKEDISLIKE_TABLE . ' ld ON p.post_id=ld.post_id AND ld.mod_like=1 ';
            	$sql_sort_order = 'ldcount DESC';
            	$sql_count = ', COUNT(ld.mod_like=1) AS ldcount ';
        	}

        	$post_list = array();
        	$i = 0;

        	$sql = 'SELECT p.post_id'.$sql_count.
            	' FROM ' . POSTS_TABLE . ' p' .
            	$join_likedislike_sql .  "
            	WHERE p.topic_id = $topic_id AND " . $phpbb_content_visibility->get_visibility_sql('post', $forum_id, 'p.') . "
            	GROUP BY p.post_id
            	ORDER BY $sql_sort_order";
        	$event['sort_key'] = $sql;
        	$result = $this->db->sql_query_limit($sql, $sql_limit, $start);

        	while ($row = $this->db->sql_fetchrow($result)) {
            	$post_list[$i] = (int) $row['post_id'];
            	$i++;
        	}
        	$this->db->sql_freeresult($result);

        	$event['post_list'] = $post_list;

        	$sql_ary = array(
            	'SELECT'	=> 'u.*, z.friend, z.foe, p.*',
            		'FROM'		=> array(
                	USERS_TABLE		=> 'u',
                	POSTS_TABLE		=> 'p',
            	),

            	'LEFT_JOIN'	=> array(
               	array(
                    	'FROM'	=> array(ZEBRA_TABLE => 'z'),
                    	'ON'	=> 'z.user_id = ' . $user_id . ' AND z.zebra_id = p.poster_id',
                	),
            	),

            	'WHERE'		=> $this->db->sql_in_set('p.post_id', $post_list) . '
                AND u.user_id = p.poster_id',
        	);

        	$event['sql_ary'] = $sql_ary;
    	}
}
