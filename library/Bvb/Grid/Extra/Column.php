<?php

/**
 * LICENSE
 *
 * This source file is subject to the new BSD license
 * It is  available through the world-wide-web at this URL:
 * http://www.petala-azul.com/bsd.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package   Bvb_Grid
 * @author    Bento Vilas Boas <geral@petala-azul.com>
 * @copyright 2010 ZFDatagrid
 * @license   http://www.petala-azul.com/bsd.txt   New BSD License
 * @version   $Id$
 * @link      http://zfdatagrid.com
 */
class Bvb_Grid_Extra_Column {

    /**
     * Columns to be added
     * @var array
     */
    public $_field;

    /**
     * Add new extra columns
     *
     * @param string $name
     * @param mixed  $args
     */
    public function __call($name, $args)
    {

        if (substr(strtolower($name), 0, 3) == 'set') {
            $name = substr($name, 3);
            $name[0] = strtolower($name[0]);
        }

        $this->_field[$name] = $args[0];
        return $this;
    }

    public function __construct($name='', $args='')
    {
        if(is_string($args))
            return $this;
        
        foreach ($args as $key=>$value)
        {
            $this->_field[$key] = $value;
        }
        
        $this->_field['name'] = $name;
        return $this;
    }

}