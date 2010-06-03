<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Notice Helper
 *
 * A simple little helper that allows a developer to easily
 * set 'notice' messages to reflect system/action status, errors, etc.
 *
 * Types of status:
 *	success
 *	info
 *	warning
 *	error
 *
 * @author Kieran Graham, No More Art/AirPOS Ltd.
 */
class notice
{
	/**
	 * Notices messages
	 */
	static protected $notices	= array();
	/**
	 * Custom views
	 */
	static protected $views		= array();
	/**
	 * Notice Types
	 */
	static protected $types		= array('success', 'info', 'warning', 'error');

	/**
	 * Exists
	 *
	 * Determines whether any or a specific type of notice currently exists.
	 *
	 * @param string - Status type to check for.
	 * @return bool - Whether notices exist or not.
	 */
	public static function exists($type = NULL)
	{
		if ($type === NULL)
		{
			return (bool) (count(self::$notices) > 0 or count(self::$views) > 0);
		}
		else
		{
			return (bool) (isset(self::$notices[$type]) or isset(self::$views[$type]));
		}
	}
	
	/**
	 * Add Notice
	 *
	 * Adds a new notice onto the stack.
	 *
	 * @param string - The message of this notice
	 * @param string - Type of this notice
	 */
	public static function add($message, $type = 'success')
	{
		if (Request::$protocol === NULL)
			return;
			
		if ( ! in_array($type, self::$types))
			$type = 'success';
		self::$notices[$type][] = $message;
	}
	
	/**
	 * Get Notice Array
	 *
	 * Either returns the entire notice array and resets it,
	 * or returns and removes a specific type of notice from
	 * the array.
	 *
	 * @param string - Type of notice to return
	 * @return array - The notice array
	 */
	public static function get_array($type = NULL)
	{
		if ($type === NULL)
		{
			$notices = self::$notices;
			self::$notices = array();
			return $notices;
		}
		else
		{
			return arr::remove($type, self::$notices);
		}
	}
	
	/**
	 * Render Notices
	 *
	 * Renders the notices to the browser
	 *
	 * @param string - Type of notices to render.
	 * @param string - The view template to use when rendering notices.
	 * @return void
	 */
	public static function render($type = NULL, $template = 'notice/render')
	{
		if (Request::$protocol === NULL)
			return;
		
		if ($type == NULL)
		{
			//echo all the types
			if (sizeof(self::$notices) == 0)
			{
				if (sizeof(self::$views) != 0)
				{
					foreach (self::$views as $view)
					{
						//display all the views (this is a repeat render)
						echo $view;
					}
				}
			}
			else
			{
				foreach (self::$notices as $type => $notices)
				{
					//reset the saved views
					self::$views = array();
					//save it incase it needs to be rendered again
					self::$views[$type] = View::factory($template)
						->set('type', $type)
						->set('notices', $notices);
					echo self::$views[$type];
				}
				//clear $notices for new ones
				self::$notices = array();
			}
		}
		else
		{
			//only echo specified type (if not from $notices then from $views
			if (isset(self::$notices[$type]))
			{
				self::$views[$type] = View::factory($template)
											->set('type', $type)
											->set('notices', self::$notices[$type]);
				echo self::$views[$type];
			}
			else
			{
				echo (isset(self::$views[$type])) ? self::$views[$type] : NULL;
			}
		}
	}
	
	/**
	 * Save
	 *
	 * Saves the notices into user's session
	 *
	 * @return void
	 */
	public static function save()
	{
		if (Request::$protocol === NULL)
			return;
		Session::instance(Kohana::config('notice.session_driver'))->set('notices', self::$notices);
	}
	
	/**
	 * Load
	 *
	 * Loads notices from the user's session
	 *
	 * @return void
	 */
	public static function load()
	{
		if (Request::$protocol === NULL)
			return;
		
		self::$notices = Session::instance(Kohana::config('notice.session_driver'))->get('notices', array());
		Session::instance(Kohana::config('notice.session_driver'))->delete('notices');
	}
}
