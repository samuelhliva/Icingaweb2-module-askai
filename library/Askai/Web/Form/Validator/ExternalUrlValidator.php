<?php
/* Icinga Web 2 - AskAI Module | (c) 2025 Samuel Hliva | GPLv2+ */

namespace Icinga\Module\Askai\Web\Form\Validator;

use Zend_Validate_Abstract;

/**
 * Validator that checks whether the given URL is valid.
 */
class ExternalUrlValidator extends Zend_Validate_Abstract
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_messageTemplates = array('NOT_VALID_URL' => t(
            'The URL is not valid.'
        ));
    }

    /**
     * Validate the input value
     *
     * @param   string  $value      The URL to validate
     *
     * @return  bool    true if and only if the input is valid, otherwise false
     *
     * @see     Zend_Validate_Abstract::isValid()
     */
    public function isValid($value): mixed
    {
        // Remove all illegal characters from a url
        $url = filter_var($value, FILTER_SANITIZE_URL);
        $isValidUrl = filter_var($url, FILTER_VALIDATE_URL);

        if (! $isValidUrl){
            $this->_error('NOT_VALID_URL');
        }

        return $isValidUrl;
    }
}