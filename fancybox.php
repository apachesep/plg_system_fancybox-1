<?php
/**
 * @package		SMZ Fancybox (plugin)
 * @author		Sergio Manzi - http://smz.it
 * @copyright	Copyright (c) 2013 - 2016 Sergio Manzi. All rights reserved.
 * @license		GNU General Public License version 3 or (at your option) any later version.
 * @version		4.0.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

// Import the parent class
jimport( 'joomla.plugin.plugin' );

/**
 * Fancybox System Plugin
 */
class plgSystemFancybox extends JPlugin {

	/**
	 * Event onAfterRender
	 *
	 * @access public
	 * @param null
	 * @return null
	 */
	public function onAfterDispatch()
	{
		// Do not load in the backend
		if (JFactory::getApplication()->isAdmin())
		{
			return;
		}

		// Dot not load if this is not the right document class
		$document = JFactory::getDocument();
		if ($document->getType() != 'html')
		{
			return;
		}

		// Load CSS
		JHtml::stylesheet('plg_fancybox/jquery.fancybox.min.css', array(), true);

		// Load the script
		JHtml::script('plg_fancybox/jquery.fancybox.min.js', false, true);

		// Add customization script to the head
		$script = trim($this->params->get('script', ''));
		if ($script != '')
		{
			$document->addScriptDeclaration($script);
		}
	}
}
