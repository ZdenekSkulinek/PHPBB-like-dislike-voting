<?php
/**
*
* @package Like/Dislike Mod
* @author Robotea technologies. s.r.o.
* @copyright (c) 2019
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace robotea\likedislike\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\template\template */
    	protected $template;

    	/** @var \phpbb\user */
    	protected $user;

    	protected $request;
    	protected $auth;
    	protected $db;

    	protected $root_path;
    	protected $php_ext;

    	protected $like_functions;

    	protected $sorting;

    	public function __construct(
        	\phpbb\template\template $template,
        	\phpbb\user $user,
        	\phpbb\request\request $request,
        	\phpbb\auth\auth $auth,
        	\phpbb\db\driver\driver_interface $db,
        	$root_path,
        	$php_ext,
        	\robotea\likedislike\core\likedislike $like_functions
        	) {
        	$this->template = $template;
        	$this->user = $user;
        	$this->request = $request;
        	$this->auth = $auth;
        	$this->db = $db;
        	$this->root_path = $root_path;
        	$this->php_ext = $php_ext;
        	$this->like_functions = $like_functions;
    	}

    	/**
    	* Assign functions defined in this class to event listeners in the core
    	*
    	* @return array
    	* @static
    	* @access public
    	*/
    	public static function getSubscribedEvents()
    	{
        	return array(
            	'core.user_setup'				               => 'load_language',
            	'core.viewtopic_assign_template_vars_before'   	=> 'viewtopic_assign_vars',
            	'core.viewtopic_modify_post_row'	           	=> 'viewtopic_assign_vars_row',
            	'core.viewtopic_get_post_data'			     => 'viewtopic_get_like_data',
        	);
    	}

    	public function load_language($event)
    	{
        	$lang_set_ext = $event['lang_set_ext'];
        	array_push(
            	$lang_set_ext,
            	array(
            	'ext_name' => 'robotea/likedislike',
            	'lang_set' => 'likedislike',
            	)
        	);
        	$event['lang_set_ext'] = $lang_set_ext;
    	}

    	public function viewtopic_assign_vars($event)
    	{
        	$this->like_functions->prepare_likes($event['forum_id'], $event['topic_id'], $this->user->data['user_id']);
        	$this->template->set_style(array('ext/robotea/likedislike/styles', 'styles'));
    	}

    	public function viewtopic_assign_vars_row($event)
    	{
        	$likesdislikes = $this->like_functions->get_like_ranks($event['row']['forum_id'], $event['row']['topic_id'], $event['row']['post_id'], $this->user->data['user_id']);
        	$event['cp_row']=array('row' => array(
            	'U_LIKE_ID' => $event['row']['post_id'],
            	'U_URL_LIKE' => $this->user->data['is_registered'] ?
                	$likesdislikes['USERLIKE'] ?
                    "javascript:alert('".$this->user->lang('ALREADY_VOTES_FOR_LIKE')."')" :
                    append_sid($this->root_path . 'viewtopic.' . $this->php_ext, array('t' => $event['row']['topic_id'], 'likemod' => 1, 'hash' => generate_link_hash("topic_" . $event['row']['topic_id']. "_post_".$event['row']['post_id']), 'likepost' => $event['row']['post_id']))
                	:
                	"javascript:alert('nejsi prihlasen!');",
            	'U_URL_DISLIKE' => $this->user->data['is_registered'] ?
                	$likesdislikes['USERDISLIKE'] ?
                    "javascript:alert('".$this->user->lang('ALREADY_VOTES_FOR_DISLIKE')."')" :
                    append_sid($this->root_path . 'viewtopic.' . $this->php_ext, array('t' => $event['row']['topic_id'], 'likemod' => 2, 'hash' => generate_link_hash("topic_" . $event['row']['topic_id']. "_post_".$event['row']['post_id']), 'likepost' => $event['row']['post_id']))
                	:
                	"javascript:alert('nejsi prihlasen!');",
            	'U_LIKES' => $likesdislikes['LIKES'],
            	'U_DISLIKES' => $likesdislikes['DISLIKES'],
            	'U_QUOTE' => false,
            	'U_REPORT' => true,
            	'U_REGISTERED' => $this->user->data['is_registered']
        	)
        	);
    	}

    	public function viewtopic_get_like_data($event)
    	{
        	if (
            	$this->request->is_set('likemod') &&
            	$this->request->is_set('likepost') &&
            	($this->request->variable('likemod', 0) == MODE_LIKE || $this->request->variable('likemod', 0) == MODE_DISLIKE) &&
            	$this->user->data['is_registered'] &&
            	check_link_hash($this->request->variable('hash', ''), "topic_" . $event['topic_id']. "_post_".$this->request->variable('likepost', 0))
            	) {
            	$this->like_functions->submit_like(
                	$event['forum_id'],
                	$event['topic_id'],
                	$this->user->data['user_id'],
                	$this->request->variable('likepost', 0),
                	$this->request->variable('likemod', 0)
            	);

            	redirect(append_sid($this->root_path . 'viewtopic.' . $this->php_ext, array('f' => $event['forum_id'], 't' => $event['topic_id'])));
        	}

        	$this->sorting = 'likes';
        	if ($this->request->is_set('sld') &&
            	($this->request->variable('sld', 'like') == 'dislikes' || $this->request->variable('sld', 'like') == 'time')) {
            	$this->sorting = $this->request->variable('sld', 'like');
        	}
        	$this->template->assign_var('U_LIKEDISLIKE_SORT', $this->sorting);

        	$this->like_functions->sort($event, $this->sorting, $this->user->data['user_id']);

        	if (!count($event['post_list'])) {
            	trigger_error('NO_TOPIC');
        	}
    	}
}
