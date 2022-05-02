<?php

declare(strict_types=1);

namespace App\Model\Repository;

use App\Constant\Constant;
use Nette\Database\Connection;
use Nette\Database\Context;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;

/**
 * Operations with database tables.
 */
abstract class Repository
{
    /**
     * Repository constructor.
     *
     * @param \Nette\Database\Connection $connection
     * @param \Nette\DI\Container $container
     */
    public function __construct(
        protected Connection $connection,
        protected Explorer $explorer,
        protected Container $container,
    ) {
    }


    /**
     * Returns table from database.
     *
     * @param string $name
     * @return \Nette\Database\Table\Selection
     */
    protected function getTable(string $name = null): Selection
    {
        if ($name) {
            return $this->explorer->table($name);
        } else {
            // get table name from class name
            preg_match('#(\w+)Repository$#', get_class($this), $m);

            return $this->explorer->table(strtolower($m[1]));
        }
    }


    /**
     * Returns all rows from table.
     *
     * @return \Nette\Database\Table\Selection
     */
    public function findAll(): Selection
    {
        return $this->getTable();
    }


    /**
     * Returns rows by filter, i.e. array('name' => 'John').
     *
     * @return \Nette\Database\Table\Selection
     */
    public function findBy(array $by): Selection
    {
        return $this->getTable()->where($by);
    }


    /**
     * @param $id
     *
     * @return \Nette\Database\Table\ActiveRow|null
     */
    public function findById($id): ActiveRow|null
    {
        return $this->getTable()->get($id);
    }


    /**
     * Inserts new data into table.
     *
     * @return \Nette\Database\Table\ActiveRow
     */
    public function add($values): ActiveRow
    {
        return $this->getTable()->insert($values);
    }


    /**
     * Updates existing row in table.
     *
     * @return bool
     */
    public function update($id, $values): bool
    {
        return $this->getTable()->get($id)->update($values);
    }


    /**
     * Removes existing row from table.
     */
    public function remove($id): void
    {
        $this->getTable()->get($id)->delete();
    }


    /**
     * Returns count of rows in table.
     *
     * @return int
     */
    public function getCount(): int
    {
        return $this->getTable()->count('*');
    }





    /**
     * @param $parent
     * @param $values
     * @param string $dictionaryTableSuffix
     */
    public function createDictionariesGlobal($parent, $values, $dictionaryTableSuffix = 'dictionary')
    {
        $parentColumn = (isset($parentColumn) ? $parentColumn : $this->getTable()->getName() . '_id');
        $dictionaryTable = $this->explorer->table($this->getTable()->getName() . $dictionaryTableSuffix);

        // create main domain dictionaries
        foreach ($values as $key => $value) {
            $values[$key][$parentColumn] = $parent->id;
            $values[$key]['language_id'] = $key;
            $dictionaryTable->insert($values[$key]);
        }
    }


    /**
     * @param $values
     * @param string $dictionaryTableSuffix
     */
    public function updateDictionariesGlobal($values, $dictionaryTableSuffix = 'dictionary')
    {
        $dictionaryTable = $this->explorer->table($this->getTable()->getName() . $dictionaryTableSuffix);

        foreach ($values as $val) {

            $exist = $dictionaryTable->get($val->id);

            if ($exist) {
                $exist->update($val);
            } else {
                unset($val->id);
                $dictionaryTable->insert($val);
            }
        }
    }

}
