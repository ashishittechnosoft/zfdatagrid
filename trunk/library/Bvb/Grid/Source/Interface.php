<?php

/**
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license
 * It is  available through the world-wide-web at this URL:
 * http://www.petala-azul.com/bsd.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package    Bvb_Grid
 * @copyright  Copyright (c)  (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    $Id$
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */


interface Bvb_Grid_Source_Interface
{


    /**
     * Sould return true|false if this source support
     * crud operations
     * @return bool
     */
    function hasCrud ();


    /**
     *Gets a unique record as a associative array
     * @param $table
     * @param $condition
     */
    function getRecord ($table, array $condition);


    /**
     * builds a key=>value array
     *
     * they must have two options
     * title and field
     * field is used to perform queries.
     * Must have table name or table alias as a prefix
     * ex: user.id | country.population
     *
     * The key for this array is the output field
     * If raw sql is somehting like
     *
     * select name as alias, country from users
     *
     * the return array must be like this:
     *
     * array('alias'=>array('title'=>'alias','field'=>'users.name'));
     *
     * its not bad idea to apply this to fields titles
     * $title = ucwords(str_replace('_',' ',$title));
     *
     *
     */
    function buildFields ();


    /**
     * Should return the database server name or source name
     *
     * Ex: mysql, pgsql, array, xml
     */
    function getSourceName ();


    /**
     *Runs the query and returns the result as a associative array
     */
    function execute ();


    /**
     * Get a record detail based the current query
     * @param array $where
     * @return array
     */
    function fetchDetail (array $where);


    /**
     * Return the total of records
     */
    function getTotalRecords ();


    /**
     * @return key=>value array with all tables
     *
     * Ex: array('c'=>array('tableName'=>'Country'));
     * where c is the table alias. If the table as no alias,
     * c should be the table name
     */
    function getTableList ();


    /**
     * Return possible filters values based on field definion
     * This is mostly used for enum fields where the possibile
     * values are extracted
     *
     * Ex: enum('Yes','No','Empty');
     *
     * should return
     *
     * array('Yes'=>'Yes','No'=>'No','Empty'=>'Empty');
     *
     * @param $field
     */
    function getFilterValuesBasedOnFieldDefinition ($field);


    /**
     * Return te field type
     * char, varchar, int
     *
     * Note: If the field is enum or set,
     * the value returned must be set or enum,
     * and not the full definition
     *
     * @param string $field
     */

    function getFieldType ($field);


    /**
     * Returns the "main" table
     * the one after select * FROM {MAIN_TABLE}
     *
     */
    function getMainTable ();


    /**
     *
     * Build the order part from the query.
     *
     * The first arg is the field to be ordered and the $order
     * arg is the correspondent order (ASC|DESC)
     *
     * If the $reset is set to true, all previous order should be removed
     *
     * @param string $field
     * @param string $order
     * @param bool $reset
     */
    function buildQueryOrder ($field, $order, $reset = false);


    /**
     * Build the query limit clause
     * @param $start
     * @param $offset
     */
    function buildQueryLimit ($start, $offset);


    /**
     * Returns the select object
     */
    function getSelectObject ();


    /**
     * returns the selected order
     * that was defined by the user in the query entered
     * and not the one generated by the system
     *
     *If empty a empty array must be returned.
     *
     *Else the array must be like this:
     *
     *Array
     * (
     * [0] => field
     * [1] => ORDER (ASC|DESC)
     * )
     *
     *
     * @return array
     */
    function getSelectOrder ();


    /**
     * Should preform a query based on the provided by the user
     * select the two fields and return an array $field=>$value
     * as result
     *
     * ex: SELECT $field, $value FROM *
     * array('1'=>'Something','2'=>'Number','3'=>'history')....;
     *
     * @param string $field
     * @param string $value
     * @return array
     */
    function getDistinctValuesForFilters ($field, $value);


    /**
     *
     *Perform a sqlexp
     *
     *$value =  array ('functions' => array ('AVG'), 'value' => 'Population' );
     *
     *Should be converted to
     *SELECT AVG(Population) FROM *
     *
     *$value =  array ('functions' => array ('SUM','AVG'), 'value' => 'Population' );
     *
     *Should be converted to
     *SELECT SUM(AVG(Population)) FROM *
     *
     * @param array $value
     */
    function getSqlExp (array $value);


    /**
     * Adds a fulltext search instead of a addcondition method
     *
     *$field has an index search
     *$field['search'] = array('extra'=>'boolean|queryExpansion','indexes'=>'string|array');
     *
     *if no indexes provided, use the field name
     *
     *boolean =>  IN BOOLEAN MODE
     *queryExpansion =>  WITH QUERY EXPANSION
     *
     * @param $filter
     * @param $field
     */
    function addFullTextSearch ($filter, $field);


    /**
     * Adds a new condition to the current query
     * $filter is the value to be filtered
     * $op is the opreand to be used: =,>=, like, llike,REGEX,
     * $completeField. use the index $completField['field'] to
     * specify the field, to avoid ambiguous
     *
     * @param $filter
     * @param $op
     * @param $completeField
     */
    function addCondition ($filter, $op, $completeField);


    /**
     *Insert an array of key=>values in the specified table
     * @param string $table
     * @param array $post
     */
    function insert ($table, array $post);


    /**
     *Update values in a table using the $condition clause
     *
     *The condition clause is a $field=>$value array
     *that should be escaped by YOU (if your class doesn't do that for you)
     * and usinf the AND operand
     *
     *Ex: array('user_id'=>'1','id_site'=>'12');
     *
     *Raw SQL: * WHERE user_id='1' AND id_site='12'
     *
     * @param string $table
     * @param array $post
     * @param array $condition
     */
    function update ($table, array $post, array $condition);


    /**
     * Delete a record from a table
     *
     * The condition clause is a $field=>$value array
     * that should be escaped by YOU (if your class doesn't do that for you)
     * and usinf the AND operand
     *
     * Ex: array('user_id'=>'1','id_site'=>'12');
     * Raw SQL: * WHERE user_id='1' AND id_site='12'
     *
     * @param string $table
     * @param array $condition
     */
    function delete ($table, array $condition);


    /**
     * Removes any order in que query
     */
    function resetOrder();

    /**
     * Cache handler.
     */
    function setCache($cache);

}