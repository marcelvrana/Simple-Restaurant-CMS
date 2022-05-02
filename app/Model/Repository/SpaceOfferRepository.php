<?php

declare(strict_types=1);

namespace App\Model\Repository;

use Nette\Database\Table\ActiveRow;
use Nette\Utils\ArrayHash;

/**
 * DB table 'spaceoffer' model.
 */
class SpaceOfferRepository extends Repository
{
    /**
     * @param ArrayHash $values
     * @return ActiveRow
     */
    public function create(ArrayHash $values): ActiveRow
    {

        $dictionariesData = $values->dictionaries;
        unset($values->dictionaries);
        $item = $this->add($values);
        $this->createDictionariesGlobal($item, $dictionariesData);
        return $item;
    }

    /**
     * @param $id
     * @param \Nette\Utils\ArrayHash $values
     */
    public function edit($id, ArrayHash $values)
    {
        $dictionariesData = $values->dictionaries;
        unset($values->dictionaries);
        $this->update($id, $values);
        $this->updateDictionariesGlobal($dictionariesData);
    }
}
