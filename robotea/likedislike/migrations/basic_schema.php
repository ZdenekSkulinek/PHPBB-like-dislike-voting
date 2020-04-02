<?php
/**
*
* @package Like/Dislike Mod
* @author Robotea technologies. s.r.o.
* @copyright (c) 2019
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace robotea\likedislike\migrations;

class basic_schema extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'likesdislikes');
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\rc4',
            //null
		);
	}

	public function update_schema()
	{
		return array(
			'add_tables' => array(
				$this->table_prefix . 'likesdislikes' => array(
					'COLUMNS' => array(
						'like_id' => array('UINT', null, 'auto_increment'),
						'forum_id' => array('UINT', null),
						'topic_id' => array('UINT', null),
						'user_id' => array('UINT', null),
						'post_id' => array('UINT', null),
                        'mod_like' => array('UINT', null)
					),
					'PRIMARY_KEY' => array('like_id'),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables' => array(
				$this->table_prefix . 'likesdislikes',
			),
		);
	}
}
