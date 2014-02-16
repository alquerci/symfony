<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @Annotation
 *
 * @api
 *
 * @deprecated Deprecated since version 2.1, to be removed in 2.3.
 */
class Symfony_Component_Validator_Constraints_MaxLength extends Symfony_Component_Validator_Constraint
{
    public $message = 'This value is too long. It should have {{ limit }} character or less.|This value is too long. It should have {{ limit }} characters or less.';
    public $limit;
    public $charset = 'UTF-8';

    public function __construct($options = null)
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('MaxLength is deprecated since version 2.1 and will be removed in 2.3. Use Length instead.', E_USER_DEPRECATED);

        parent::__construct($options);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOption()
    {
        return 'limit';
    }

    /**
     * {@inheritDoc}
     */
    public function getRequiredOptions()
    {
        return array('limit');
    }
}
