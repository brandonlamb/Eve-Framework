<?php
namespace Eve\Model;

class QueryBuilder
{
	/**
	 * Return the built SELECT section of the SQL statement
	 *
	 * @return string
	 */
	public function getSelect()
	{
		return 'SELECT ' . (count($this->select) == 0 ? '*' : implode($this->select, ', ')) . ' ';
	}

	/**
	 * Return the built FROM section of the SQL statement
	 *
	 * @return string
	 */
	public function getFrom()
	{
		return 'FROM ' . implode($this->from, ', ') . ' ';
	}

	/**
	 * Return the built JOIN sections of the SQL statement
	 *
	 * @return string
	 */
	public function getJoin()
	{
		return (count($this->join) == 0 ? null : implode($this->join, ', ')) . ' ';
	}

	/**
	 * Return the built ORDER BY section of the SQL statement
	 *
	 * @return string
	 */
	public function getOrder()
	{
		return count($this->order) == 0 ? null : 'ORDER BY ' . implode($this->order, ', ') . ' ';
	}

	/**
	 * Return the built GROUP BY section of the SQL statement
	 *
	 * @return string
	 */
	public function getGroup()
	{
		return count($this->group) == 0 ? null :  'GROUP BY ' . implode($this->group, ', ') . ' ';
	}

	/**
	 * Return the built LIMIT section of the SQL statement
	 *
	 * @return string
	 */
	public function getLimit()
	{
		return null === $this->limit ? null :  'LIMIT ' . (int) $this->limit . ' ';
	}

	/**
	 * Return the built OFFSET section of the SQL statement
	 *
	 * @return string
	 */
	public function getOffset()
	{
		return null === $this->offset ? null :  'OFFSET ' . (int) $this->offset . ' ';
	}
}
