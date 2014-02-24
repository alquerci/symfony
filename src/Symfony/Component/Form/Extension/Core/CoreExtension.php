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
 * Represents the main form extension, which loads the core functionality.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Form_Extension_Core_CoreExtension extends Symfony_Component_Form_AbstractExtension
{
    protected function loadTypes()
    {
        return array(
            new Symfony_Component_Form_Extension_Core_Type_FieldType(),
            new Symfony_Component_Form_Extension_Core_Type_FormType(Symfony_Component_PropertyAccess_PropertyAccess::getPropertyAccessor()),
            new Symfony_Component_Form_Extension_Core_Type_BirthdayType(),
            new Symfony_Component_Form_Extension_Core_Type_CheckboxType(),
            new Symfony_Component_Form_Extension_Core_Type_ChoiceType(),
            new Symfony_Component_Form_Extension_Core_Type_CollectionType(),
            new Symfony_Component_Form_Extension_Core_Type_CountryType(),
            new Symfony_Component_Form_Extension_Core_Type_DateType(),
            new Symfony_Component_Form_Extension_Core_Type_DateTimeType(),
            new Symfony_Component_Form_Extension_Core_Type_EmailType(),
            new Symfony_Component_Form_Extension_Core_Type_HiddenType(),
            new Symfony_Component_Form_Extension_Core_Type_IntegerType(),
            new Symfony_Component_Form_Extension_Core_Type_LanguageType(),
            new Symfony_Component_Form_Extension_Core_Type_LocaleType(),
            new Symfony_Component_Form_Extension_Core_Type_MoneyType(),
            new Symfony_Component_Form_Extension_Core_Type_NumberType(),
            new Symfony_Component_Form_Extension_Core_Type_PasswordType(),
            new Symfony_Component_Form_Extension_Core_Type_PercentType(),
            new Symfony_Component_Form_Extension_Core_Type_RadioType(),
            new Symfony_Component_Form_Extension_Core_Type_RepeatedType(),
            new Symfony_Component_Form_Extension_Core_Type_SearchType(),
            new Symfony_Component_Form_Extension_Core_Type_TextareaType(),
            new Symfony_Component_Form_Extension_Core_Type_TextType(),
            new Symfony_Component_Form_Extension_Core_Type_TimeType(),
            new Symfony_Component_Form_Extension_Core_Type_TimezoneType(),
            new Symfony_Component_Form_Extension_Core_Type_UrlType(),
            new Symfony_Component_Form_Extension_Core_Type_FileType(),
        );
    }
}
