<?php

include 'Query.php';

#$query = new \Eve\Model\Query();
$query = \Eve\Model\Query::factory()
	->select('column1 ')
	->select('column2', ' c2   ')
#	->select('column3 as c3   ')
#	->select(array('column4  ', 'column5 as c5'))
#	->select('column6, column7 as c7, column8   ')
#	->select('(SELECT col1, col2 FROM tbl2 WHERE id = 3)', 'c9  ');


	->from('table1')
/*
	->from('table2', 't2')
	->from('table3 t3')
	->from('table4, table5 t5')
	->from(array('table6', 'table7 t7'))
	->from('table(funcGetName(:name))', 'x');
*/

#	->join('Table2', 't1.col1 = t2.col1', 't2')
#	->join('Table2', array('t1.col1', '=', 't2.col1'), 't2')
	->innerJoin('Table2', 't1.col1 = t2.col1', 't2')

#	->where(array('col1', '=', 'Brandon'))
	->where(array(
		array('col1', '=', 'value1'),
		array('col2', '=', 'value2'),
#		array('col3', 'like', 'value3%'),
	), 'AND', 'AND')
#	->whereRaw('col1 = (SELECT 1 FROM tbl2 WHERE col2 = 1')
	->whereEqual('name', 'brandon')
	->whereNotEqual('day', 'today')
#	->whereLike('city', 'Belle%')
#	->whereNotLike('city', 'Sea%')
#	->whereGt('age', 100)
#	->whereGte('age', 99)
#	->whereLt('age', 10)

#	->having('sum(x) > ?', array(1))

	->order(array('col1' => 'desc', 'col2'))
#	->order('col3, col4')
#	->order(array('col5' => 'desc'))

	->limit(10)
	->offset(20);

/*
$query->select('username, password, userId AS uid')
	->from('User')
	->where(array('u.userId = ?'))
	->order('username')
	->group('uid')
	->limit(123);
*/

print_r($query);

/*
echo "\n\nSQL: ";
echo $query->getSelect();
echo $query->getFrom();
echo $query->getJoin();
echo $query->getOrder();
echo $query->getGroup();
echo $query->getLimit();
*/
die("<\n\n");
