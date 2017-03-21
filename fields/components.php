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

// Import classes
jimport('joomla.html.html');
jimport('joomla.access.access');
jimport('joomla.form.formfield');

/**
 * Form Field-class for selecting a component
 */
class JFormFieldComponents extends JFormField
{
    /*
     * Form field type
     */
    public $type = 'Components';

    /*
     * Method to construct the HTML of this element
     *
     * @param null
     * @return string
     */
    protected function getInput()
    {
        $name = $this->name.'[]';
        $value = $this->value;
        $db = JFactory::getDBO();

        // load the list of components
        $query = 'SELECT * FROM `#__extensions` WHERE `type`="component" AND `enabled`=1';
        $db->setQuery( $query );
        $components = $db->loadObjectList();

        $options = array();
        foreach ($components as $component) {
            $options[] = JHTML::_('select.option',  $component->element, JText::_($component->name).' ['.$component->element.']', 'value', 'text');
        }

        $size = (count($options) > 12) ? 12 : count($options);
        $attribs = 'class="inputbox" multiple="multiple" size="'.$size.'"';
        return JHTML::_('select.genericlist',  $options, $name, $attribs, 'value', 'text', $value, $name);
    }
}
