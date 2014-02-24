Validator Component
===================

This component is based on the JSR-303 Bean Validation specification and
enables specifying validation rules for classes using XML, YAML, PHP or
annotations, which can then be checked against instances of these classes.

Usage
-----

The component provides "validation constraints", which are simple objects
containing the rules for the validation. Let's validate a simple string
as an example:

    use Symfony_Component_Validator_Validation;
    use Symfony_Component_Validator_Constraints_Length;

    $validator = Validation::createValidator();

    $violations = $validator->validateValue('Bernhard', new Length(array('min' => 10)));

This validation will fail because the given string is shorter than ten
characters. The precise errors, here called "constraint violations",  are
returned by the validator. You can analyze these or return them to the user.
If the violation list is empty, validation succeeded.

Validation of arrays is possible using the `Collection` constraint:

    use Symfony_Component_Validator_Validation;
    use Symfony_Component_Validator_Constraints as Assert;

    $validator = Validation::createValidator();

    $constraint = new Assert_Collection(array(
        'name' => new Assert_Collection(array(
            'first_name' => new Assert_Length(array('min' => 101)),
            'last_name'  => new Assert_Length(array('min' => 1)),
        )),
        'email'    => new Assert_Email(),
        'simple'   => new Assert_Length(array('min' => 102)),
        'gender'   => new Assert_Choice(array(3, 4)),
        'file'     => new Assert_File(),
        'password' => new Assert_Length(array('min' => 60)),
    ));

    $violations = $validator->validateValue($input, $constraint);

Again, the validator returns the list of violations.

Validation of objects is possible using "constraint mapping". With such
a mapping you can put constraints onto properties and objects of classes.
Whenever an object of this class is validated, its properties and
method results are matched against the constraints.

    use Symfony_Component_Validator_Validation;
    use Symfony_Component_Validator_Constraints as Assert;

    class User
    {
        /**
         * @Assert_Length(min = 3)
         * @Assert_NotBlank
         */
        private $name;

        /**
         * @Assert_Email
         * @Assert_NotBlank
         */
        private $email;

        public function __construct($name, $email)
        {
            $this->name = $name;
            $this->email = $email;
        }

        /**
         * @Assert_True(message = "The user should have a Google Mail account")
         */
        public function isGmailUser()
        {
            return false !== strpos($this->email, '@gmail.com');
        }
    }

    $validator = Validation::createValidatorBuilder()
        ->enableAnnotationMapping()
        ->getValidator();

    $user = new User('John Doe', 'john@example.com');

    $violations = $validator->validate($user);

This example uses the annotation support of Doctrine Common to
map constraints to properties and methods. You can also map constraints
using XML, YAML or plain PHP, if you dislike annotations or don't want
to include Doctrine. Check the documentation for more information about
these drivers.

Resources
---------

Silex integration:

https://github.com/fabpot/Silex/blob/master/src/Silex/Provider/ValidatorServiceProvider.php

Documentation:

http://symfony.com/doc/2.2/book/validation.html

JSR-303 Specification:

http://jcp.org/en/jsr/detail?id=303

You can run the unit tests with the following command:

    $ cd path/to/Symfony/Component/Validator/
    $ composer.phar install --dev
    $ phpunit
