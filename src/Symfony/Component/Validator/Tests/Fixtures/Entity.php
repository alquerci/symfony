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
 * @Symfony_Component_Validator_Tests_Fixtures_ConstraintA
 * @Symfony_Component_Validator_Constraints_GroupSequence({"Foo", "Symfony_Component_Validator_Tests_Fixtures_Entity"})
 */
class Symfony_Component_Validator_Tests_Fixtures_Entity extends Symfony_Component_Validator_Tests_Fixtures_EntityParent implements Symfony_Component_Validator_Tests_Fixtures_EntityInterface
{
    /**
     * @Symfony_Component_Validator_Constraints_NotNull
     * @Symfony_Component_Validator_Constraints_Range(min=3)
     * @Symfony_Component_Validator_Constraints_All({@Symfony_Component_Validator_Constraints_NotNull, @Symfony_Component_Validator_Constraints_Range(min=3)}),
     * @Symfony_Component_Validator_Constraints_All(constraints={@Symfony_Component_Validator_Constraints_NotNull, @Symfony_Component_Validator_Constraints_Range(min=3)})
     * @Symfony_Component_Validator_Constraints_Collection(fields={
     *   "foo" = {@Symfony_Component_Validator_Constraints_NotNull, @Symfony_Component_Validator_Constraints_Range(min=3)},
     *   "bar" = @Symfony_Component_Validator_Constraints_Range(min=5)
     * })
     * @Symfony_Component_Validator_Constraints_Choice(choices={"A", "B"}, message="Must be one of %choices%")
     */
    protected $firstName;
    protected $lastName;
    public $reference;

    private $internal;

    public function __construct($internal = null)
    {
        $this->internal = $internal;
    }

    public function getInternal()
    {
        return $this->internal . ' from getter';
    }

    /**
     * @Symfony_Component_Validator_Constraints_NotNull
     */
    public function getLastName()
    {
        return $this->lastName;
    }
}
