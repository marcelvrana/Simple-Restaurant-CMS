<?php

declare(strict_types=1);

namespace App\Model\Admin;

use App\Model\Repository\LanguageRepository;

class LanguageManager
{
    public function __construct(private LanguageRepository $languageRepository)
    {
    }

    /**
     * @return \Nette\Database\Table\Selection
     */
    public function getActiveLanguages(): \Nette\Database\Table\Selection
    {
        return $this->languageRepository->findBy(['is_active' => 1]);
    }

    /**
     * @param $data
     * @return array
     */
    public function setDefault($id): array
    {
        $row = $this->languageRepository->findById($id);
        $default = $this->languageRepository->findBy(['is_default' => 1])->fetch();

        if($row->id == $default->id){
            return [
                'message' => 'Can\'t delete default language',
                'type' => 'warning'
            ];
        } else {
            try {
                $row->update(['is_default' => 1]);
                $default->update(['is_default' => 0]);
                return [
                    'message' => 'Default language was changed',
                    'type' => 'success'
                ];
            } catch (\Exception $e) {
                return [
                    'message' => $e->getMessage(),
                    'type' => 'danger'
                ];
            }
        }

    }


    /**
     * @param $id
     * @return array|string[]
     */
    public function setActive($id): array
    {
        $row = $this->languageRepository->findById($id);
        if ($row->is_active && $row->is_default) {
            return [
                'message' => 'You can\'t deactivate the default language!',
                'type' => 'warning'
            ];
        }
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
            'message' => 'Language status was changed!',
            'type' => 'success'
        ];
    }

    /**
     * @param $id
     * @return array|string[]
     */
    public function safeDeleteItem($id): array
    {
        $row = $this->languageRepository->findById($id);
        if ($row->is_default) {
            return [
                'message' => 'You can\'t delete default language!',
                'type' => 'warning'
            ];
        }
        try {
            $row->delete();
            return [
                'message' => 'Deleted!',
                'type' => 'warning'
            ];
        } catch (\Exception $e) {
            return [
                'message' => $e->getMessage(),
                'type' => 'danger'
            ];
        }
    }
}