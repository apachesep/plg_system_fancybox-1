<?php
/**
 * @package		SMZ Fancybox (plugin)
 * @author		Sergio Manzi - http://smz.it
 * @copyright	Copyright (c) 2013 - 2016 Sergio Manzi. All rights reserved.
 * @license		GNU General Public License version 3 or (at your option) any later version.
 * @version		3.5.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

// Import the parent class
jimport( 'joomla.plugin.plugin' );

/**
 * Fancybox System Plugin
 */
class plgSystemSMZ_fancybox extends JPlugin {

	/**
	 * Event onAfterRender
	 *
	 * @access public
	 * @param null
	 * @return null
	 */
	public function onAfterDispatch() {
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

		$elements = $this->getElements();
		if (empty($elements))
		{
			return;
		}

		// Get and parse the components from the plugin parameters
		$components = $this->params->get('exclude_components');
		if (empty($components))
		{
			$components = array();
		}
		elseif (!is_array($components))
		{
			$components = array($components);
		}

		// Do not load if the current component is excluded
		if (in_array(JRequest::getCmd('option'), $components))
		{
			return;
		}

		// Load CSS and JavaScript
		JHtml::_('jquery.framework');
		$this->loadStylesheet('jquery.fancybox.css', $this->params->get('load_css', 1));
		$this->loadStylesheet('jquery.fancybox-buttons.css', $this->params->get('load_css', 1));
		$this->loadStylesheet('jquery.fancybox-thumbs.css', $this->params->get('enable_thumbs', 0));
		$this->loadScript('jquery.fancybox.pack.js', $this->params->get('load_fancybox', 1));
		$this->loadScript('jquery.mousewheel.pack.js', $this->params->get('enable_mousewheel', 1));
		$this->loadScript('jquery.fancybox-buttons.js', $this->params->get('enable_buttons', 0));
		$this->loadScript('jquery.fancybox-media.js', $this->params->get('enable_media', 0));
		$this->loadScript('jquery.fancybox-thumbs.js', $this->params->get('enable_thumbs', 0));

		// Setup options and helpers
		$options = array();
		$helpers = array();

		// Close Button
		if ($this->params->get('enable_close', 1) == 0) // True is default
		{
			$options['closeBtn'] = false;
		}

		// Close on click inside
		if ($this->params->get('close_on_click_inside', 0)) // False is default
		{
			$options['closeClick'] = 'true';
		}

		// Close on click outside
		if ($this->params->get('close_on_click_outside', 1) == 0) // True is default
		{
			$helpers[] = 'overlay:{closeClick:false}';
		}

		// Mouse-wheel
		if ($this->params->get('enable_mousewheel', 1) == 0) // True is default
		{
			$options['mouseWheel'] = false;
		}

		// Buttons helper
		if ($this->params->get('enable_buttons', 0) == 1) // False is default
		{
			$helpers[] = 'buttons:{}';
		}

		// Content-type
		$content_type = $this->params->get('content_type', '');
		if (!empty($content_type))
		{
			$options['type'] = $content_type;
		}

		// Open/Close Transition
		$openclose_transition = $this->params->get('openclose_transition', 'none');
		if (!in_array($openclose_transition, array('', 'fade', 'elastic', 'none')))
		{
			$this->loadScript('jquery.easing-1.3.pack.js', $this->params->get('load_easing', 1));
			if (in_array($openclose_transition, array('swing', 'linear')))
			{
				$options['openEasing'] = $openclose_transition;
				$options['closeEasing'] = $openclose_transition;
			}
			else
			{
				$options['openEasing'] = 'easeInOut'.ucfirst($openclose_transition);
				$options['closeEasing'] = 'easeInOut'.ucfirst($openclose_transition);
			}
		}
		else
		{
			if ($openclose_transition != 'fade') // fade is Fancybox default
			{
				$options['openEffect'] = $openclose_transition;
				$options['closeEffect'] = $openclose_transition;
			}
		}

		$openclose_speed = $this->params->get('openclose_speed', 250);
		if ($openclose_speed != 250) // 250 is default
		{
			$options['openSpeed'] = $openclose_speed;
			$options['closeSpeed'] = $openclose_speed;
		}

		// Next/Prev Transition
		$nextprev_transition = $this->params->get('nextprev_transition', 'none');
		if ($nextprev_transition != 'elastic') // elastic is default
		{
			$options['nextEffect'] = $nextprev_transition;
			$options['prevEffect'] = $nextprev_transition;
		}

		$nextprev_speed = $this->params->get('nextprev_speed', 250);
		if ($nextprev_speed != 250) // 250 is default
		{
			$options['nextSpeed'] = $nextprev_speed;
			$options['prevSpeed'] = $nextprev_speed;
		}

		// Caption (using afterload function)
		if ($this->params->get('enable_caption', 1))
		{
			if ($this->params->get('enable_counter', 0)) // outside is default
			{
				$options['afterLoad'] = "function(){this.title='<span class=\"fancyboxCounter\">'+(this.index+1)+'</span>'+'<span class=\"fancyboxTotal\">'+this.group.length+'</span>'+(this.title?'<span class=\"fancyboxCaption\">'+this.title+'</span>':'');}";
			}

			// Title position
			if ($this->params->get('caption_position', 0)) // outside is default
			{
				$helpers[] = "title:{type:'inside'}";
			}
		}
		else
		{
			$helpers[] = 'title:null';
		}

		// Extra options
		$extraOptions = trim($this->params->get('options'));
		if (!empty($extraOptions))
		{
			$extraOptions = explode("\n", $extraOptions);
			foreach ($extraOptions as $extraOption)
			{
				$extraOption = explode('=', $extraOption);
				if (!empty($extraOption[0]) && !empty($extraOption[1]))
				{
					$options[$extraOption[0]] = trim($extraOption[1]);
				}
			}
		}

		// Sanitize options
		foreach ($options as $name => $value)
		{
			if (is_bool($value))
			{
				$bool = ($value) ? 'true' : 'false';
				$options[$name] = "'$name':$bool";
			}
			elseif (is_numeric($value))
			{
				$options[$name] = "'$name':$value";
			}
			elseif (empty($value))
			{
				unset($options[$name]);
			}
			elseif (stripos($value,'function') !== FALSE)
			{
				$options[$name] = "'$name':$value";
			}
			else
			{
				$options[$name] = "'$name':'$value'";
			}
		}

		// Media helper
		if ($this->params->get('enable_media', 0))
		{
			$helpers[] = 'media:{}';
		}

		// Thumbs helper
		if ($this->params->get('enable_thumbs', 0))
		{
			$helpers[] = 'thumbs:{width:50,height:50}';
		}

		if (!empty($helpers))
		{
			$options[] = 'helpers:{'.implode(',', $helpers).'}';
		}

		// Build the script
		$script = '';

		$namespace = trim($this->params->get('namespace', ''));
		if (empty($namespace))
		{
			$namespace = 'jQuery';
		}
		else
		{
			$script .= $namespace . '=jQuery.noConflict();';
		}

		$script .= $namespace . '(document).ready(function(){';
		foreach ($elements as $element)
		{
			$script .= $namespace . '("' . $element . '").fancybox(' ;
			if (!empty($options))
			{
				$script .= '{' . implode(',', $options) . '}';
			}
			$script .= ');';
		}
		$script .= '});';

		// Add the script to the head
		$document->addScriptDeclaration($script); 
	}

	/**
	 * Load a script
	 *
	 * @access private
	 * @param null
	 * @return null
	 */
	private function loadScript($file = null, $condition = true) {
		if ($condition)
		{
			JHtml::script('plg_smz_fancybox/' . $file, false, true);
		}
	}

	/**
	 * Load a stylesheet
	 *
	 * @access private
	 * @param null
	 * @return null
	 */
	private function loadStylesheet($file = null, $condition = true) {
		$condition = (bool)$condition;
		if ($condition)
		{
			JHtml::stylesheet('plg_smz_fancybox/' . $file, array(), true);
		}
	}

	/**
	 * Get the HTML elements
	 *
	 * @access private
	 * @param null
	 * @return JParameter
	 */
	private function getElements() {
		$elements = $this->params->get('elements');
		$elements = trim($elements);
		$elements = explode(",", $elements);
		if (!empty($elements))
		{
			foreach ($elements as $index => $element)
			{
				$element = trim($element);
				$element = preg_replace('/([^a-zA-Z0-9\[\]\=\-\_\.\#\ ]+)/', '', $element);
				if (empty($element))
				{
					unset($elements[$index]);
				}
				else
				{
					$elements[$index] = $element;
				}
			}
		}

		return $elements;
	}
}
