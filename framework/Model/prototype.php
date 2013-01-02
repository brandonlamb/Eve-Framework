<?php

$model = new Model();

/**
 * SELECT
 */
$model->select('column1')
    ->select('column1', 'c1')
    ->select('column2 c2, column3 c3')
    ->select('(SELECT col1 FROM tbl2 WHERE id = 1')
    ->select(array('column4', 'column5'));

/**
 * FROM
 */
$model->from('Table1')
    ->from('Table2 t2')
    ->from('Table3', 't3')
    ->from(array('Table4', 'Table5 t5', 'Table6'))
    ->from('table(funcGetSomething(123))', 'x');

/**
 * JOIN
 */
$model->join('Table1', 'Table1.id = Table2.id')
    ->join('Table1', 't1.id = Table2.id', 't1')
    ->join('Table1', array('t1.id', '=', 'Table2.id'), 't1')
    ->innerJoin('Table1', array('t1.id', '=', 'Table2.id'), 't1')
    ->leftOuterJoin('Table1', array('t1.id', '=', 'Table2.id'), 't1')
    ->rightOuterJoin('Table1', array('t1.id', '=', 'Table2.id'), 't1')
    ->fullOuterJoin('Table1', array('t1.id', '=', 'Table2.id'), 't1')
    ->crossJoin('Table1', array('t1.id', '=', 'Table2.id'), 't1');

/**
 * WHERE
 */
$model->where('col1', $val)
    ->where(array('col1', '=', $val))
    ->where('col1 = ? AND col2 = ?', array($val1, $val2))
    ->whereEqual('col2', $val)
    ->whereNotEqual('col3', $val)
    ->whereLt('col4', $val)
    ->whereLte('col5', $val)
    ->whereGt('col6', $val)
    ->whereGte('col7', $val)
    ->whereLike('col8', '%val')
    ->whereNotLike('col9', '%val');

/**
 * HAVING
 */
$model->having('SUM(col1) > ?', array(100));

/**
 * ORDER
 */
$model->order('column1')
    ->order('column1', 'DESC')
    ->order(array('column1' => 'desc', 'column2' => 'asc'));

/**
 * GROUP
 */
$model->group('column1')
    ->group(array('column2', 'column3'));

/**
 * LIMIT
 */
$model->limit(1)
    ->limit(1, 10);

/**
 * OFFSET
 */
$model->offset(10);

/**
 * Get a model
 */
$model = Model::model()->query();
