<?php

namespace App\Exception;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FormValidationFailedException extends HttpException
{
    /**
     * @var FormInterface
     */
    private $form;

    public function __construct(FormInterface $form)
    {
        $message = sprintf('Form validation failed');
        parent::__construct(400, $message);

        $this->form = $form;
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }
}
