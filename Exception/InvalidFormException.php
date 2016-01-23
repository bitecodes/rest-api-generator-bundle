<?php

namespace Fludio\ApiAdminBundle\Exception;

use Symfony\Component\Form\Form;

class InvalidFormException extends \RuntimeException
{
    const DEFAULT_ERROR_MESSAGE = "The data submitted to the form was invalid.";

    /**
     * @var null|Form
     */
    protected $form;

    public function __construct($form = null, $message = self::DEFAULT_ERROR_MESSAGE)
    {
        parent::__construct($message);

        $this->form = $form;
    }

    public function getForm()
    {
        return $this->form;
    }
}