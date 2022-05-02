<?php

declare(strict_types=1);

namespace App\AdminModule\Forms;

use Nette\Application\UI\Form;

trait BootstrapRenderTrait
{
    /**
     * Automatic Bootstrap form rendering - for Bootstrap 5.
     *
     * @param \Nette\Application\UI\Form $form
     *
     * @return \Nette\Application\UI\Form
     */
    public function setBootstrapRender($form): Form
    {
        $form->getElementPrototype()->addAttributes(['autocomplete' => 'off']);

        $renderer = $form->getRenderer();
        $renderer->wrappers['group']['container'] = 'div class="card mb-3"';
        $renderer->wrappers['group']['label'] = 'div class="card-header"';
        $renderer->wrappers['controls']['container'] = 'div class="card-body"';
        $renderer->wrappers['pair']['container'] = 'div class="mb-3"';
        $renderer->wrappers['label']['label'] = 'class="mb-3"';
        $renderer->wrappers['control']['container'] = null;
        $renderer->wrappers['control']['description'] = 'div class="form-text"';
        $renderer->wrappers['control']['errorcontainer'] = 'div class=invalid-feedback';

        $form->onRender[] = function ($form) {
            foreach ($form->getControls() as $control) {
                $type = $control->getOption('type');
                $htmlName = $control->getHtmlName();

                if ($type === 'button') {
                    $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-secondary');
                    $usedPrimary = true;
                } elseif ($type === 'text' && $htmlName === 'color') {
                    $control->getControlPrototype()->addClass('form-control form-control-color');
                } elseif (in_array($type, ['text', 'email', 'tel', 'password', 'textarea'], true)) {
                    $control->getControlPrototype()->addClass('form-control');
                } elseif ($type === 'select') {
                    $control->getControlPrototype()->addClass('form-select');
                } elseif ($type === 'file') {
                    $control->getControlPrototype()->addClass('form-control');
                } elseif (in_array($type, ['checkbox', 'radio'], true)) {
                    if ($control instanceof \Nette\Forms\Controls\Checkbox) {
                        $control->getLabelPrototype()->addClass('form-check-label');
                    } else {
                        $control->getItemLabelPrototype()->addClass('form-check-label');
                    }
                    $control->getControlPrototype()->addClass('form-check-input');
                    $control->getSeparatorPrototype()->setName('div')->addClass('form-check');
                }

                if ($control->hasErrors()) {
                    $control->getControlPrototype()->addClass('is-invalid');
                }

                $control->getLabelPrototype()->addClass('form-label');
            }
        };

        $form->onValidate[] = function ($form) {
            foreach ($form->getControls() as $control) {
                if ($control->hasErrors()) {
                    $control->getControlPrototype()->addClass('is-invalid');

                    if ($control->getOption('type') == 'radio') {
                        $control->getSeparatorPrototype()->setName('div')->addClass('is-invalid');
                    }
                } else {
                    $control->getControlPrototype()->addClass('is-valid');
                }
            }
        };

        return $form;
    }
}
