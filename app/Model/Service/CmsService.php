<?php

declare(strict_types=1);

namespace App\Model\Service;

use JetBrains\PhpStorm\ArrayShape;
use Nette\Database\Explorer;

class CmsService
{
    /***/
    public function __construct(private Explorer $explorer)
    {
    }


    /**
     * @param array $post
     */
    public function orderItems(array $post)
    {
        foreach ($post['position'] as $id => $position) {
            $this->explorer->table($post['repository'])->where('id', $id)->update(['ordered' => $position]);
        }
    }

    /**
     * @param $id
     * @return array|string[]
     */
    public function setVisible($id, $repository)
    {
        $row = $this->explorer->table($repository)->where('id', $id)->fetch();

        if ($row->is_visible) {
            try {
                $row->update(['is_visible' => 0]);
            } catch (\Exception $e) {
                return [
                    'message' => $e->getMessage(),
                    'type' => 'danger'
                ];
            }
        } else {
            try {
                $row->update(['is_visible' => 1]);
            } catch (\Exception $e) {
                return [
                    'message' => $e->getMessage(),
                    'type' => 'danger'
                ];
            }
        }
        return [
            'message' => 'Stav jazyka bol zmenený!',
            'type' => 'success'
        ];
    }

    /**
     * @param $id
     * @param $repository
     * @return array|string[]
     */
    public function setActive($id, $repository): array
    {
        $row = $this->explorer->table($repository)->where('id', $id)->fetch();

        if ($row->is_active) {
            try {
                $row->update(['is_active' => 0]);
            } catch (\Exception $e) {
                return [
                    'message' => $e->getMessage(),
                    'type' => 'danger'
                ];
            }
        } else {
            try {
                $row->update(['is_active' => 1]);
            } catch (\Exception $e) {
                return [
                    'message' => $e->getMessage(),
                    'type' => 'danger'
                ];
            }
        }
        return [
            'message' => 'Changed!',
            'type' => 'success'
        ];
    }
}