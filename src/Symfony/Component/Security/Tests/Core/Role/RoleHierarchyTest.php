<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_Role_RoleHierarchyTest extends PHPUnit_Framework_TestCase
{
    public function testGetReachableRoles()
    {
        $role = new Symfony_Component_Security_Core_Role_RoleHierarchy(array(
            'ROLE_ADMIN' => array('ROLE_USER'),
            'ROLE_SUPER_ADMIN' => array('ROLE_ADMIN', 'ROLE_FOO'),
        ));

        $this->assertEquals(array(new Symfony_Component_Security_Core_Role_Role('ROLE_USER')), $role->getReachableRoles(array(new Symfony_Component_Security_Core_Role_Role('ROLE_USER'))));
        $this->assertEquals(array(new Symfony_Component_Security_Core_Role_Role('ROLE_FOO')), $role->getReachableRoles(array(new Symfony_Component_Security_Core_Role_Role('ROLE_FOO'))));
        $this->assertEquals(array(new Symfony_Component_Security_Core_Role_Role('ROLE_ADMIN'), new Symfony_Component_Security_Core_Role_Role('ROLE_USER')), $role->getReachableRoles(array(new Symfony_Component_Security_Core_Role_Role('ROLE_ADMIN'))));
        $this->assertEquals(array(new Symfony_Component_Security_Core_Role_Role('ROLE_FOO'), new Symfony_Component_Security_Core_Role_Role('ROLE_ADMIN'), new Symfony_Component_Security_Core_Role_Role('ROLE_USER')), $role->getReachableRoles(array(new Symfony_Component_Security_Core_Role_Role('ROLE_FOO'), new Symfony_Component_Security_Core_Role_Role('ROLE_ADMIN'))));
        $this->assertEquals(array(new Symfony_Component_Security_Core_Role_Role('ROLE_SUPER_ADMIN'), new Symfony_Component_Security_Core_Role_Role('ROLE_ADMIN'), new Symfony_Component_Security_Core_Role_Role('ROLE_FOO'), new Symfony_Component_Security_Core_Role_Role('ROLE_USER')), $role->getReachableRoles(array(new Symfony_Component_Security_Core_Role_Role('ROLE_SUPER_ADMIN'))));
    }
}
