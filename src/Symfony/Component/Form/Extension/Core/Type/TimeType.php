<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Extension_Core_Type_TimeType extends Symfony_Component_Form_AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(Symfony_Component_Form_FormBuilderInterface $builder, array $options)
    {
        $parts  = array('hour');
        $format = 'H';

        if ($options['with_seconds'] && !$options['with_minutes']) {
            throw new Symfony_Component_Form_Exception_InvalidConfigurationException('You can not disable minutes if you have enabled seconds.');
        }

        if ($options['with_minutes']) {
            $format .= ':i';
            $parts[] = 'minute';
        }

        if ($options['with_seconds']) {
            $format .= ':s';
            $parts[] = 'second';
        }

        if ('single_text' === $options['widget']) {
            $builder->addViewTransformer(new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToStringTransformer($options['model_timezone'], $options['view_timezone'], $format));
        } else {
            $hourOptions = $minuteOptions = $secondOptions = array(
                'error_bubbling' => true,
            );

            if ('choice' === $options['widget']) {
                $hours = $minutes = array();

                foreach ($options['hours'] as $hour) {
                    $hours[$hour] = str_pad($hour, 2, '0', STR_PAD_LEFT);
                }

                // Only pass a subset of the options to children
                $hourOptions['choices'] = $hours;
                $hourOptions['empty_value'] = $options['empty_value']['hour'];

                if ($options['with_minutes']) {
                    foreach ($options['minutes'] as $minute) {
                        $minutes[$minute] = str_pad($minute, 2, '0', STR_PAD_LEFT);
                    }

                    $minuteOptions['choices'] = $minutes;
                    $minuteOptions['empty_value'] = $options['empty_value']['minute'];
                }

                if ($options['with_seconds']) {
                    $seconds = array();

                    foreach ($options['seconds'] as $second) {
                        $seconds[$second] = str_pad($second, 2, '0', STR_PAD_LEFT);
                    }

                    $secondOptions['choices'] = $seconds;
                    $secondOptions['empty_value'] = $options['empty_value']['second'];
                }

                // Append generic carry-along options
                foreach (array('required', 'translation_domain') as $passOpt) {
                    $hourOptions[$passOpt] = $options[$passOpt];

                    if ($options['with_minutes']) {
                        $minuteOptions[$passOpt] = $options[$passOpt];
                    }

                    if ($options['with_seconds']) {
                        $secondOptions[$passOpt] = $options[$passOpt];
                    }
                }
            }

            $builder->add('hour', $options['widget'], $hourOptions);

            if ($options['with_minutes']) {
                $builder->add('minute', $options['widget'], $minuteOptions);
            }

            if ($options['with_seconds']) {
                $builder->add('second', $options['widget'], $secondOptions);
            }

            $builder->addViewTransformer(new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToArrayTransformer($options['model_timezone'], $options['view_timezone'], $parts, 'text' === $options['widget']));
        }

        if ('string' === $options['input']) {
            $builder->addModelTransformer(new Symfony_Component_Form_ReversedTransformer(
                new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToStringTransformer($options['model_timezone'], $options['model_timezone'], 'H:i:s')
            ));
        } elseif ('timestamp' === $options['input']) {
            $builder->addModelTransformer(new Symfony_Component_Form_ReversedTransformer(
                new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToTimestampTransformer($options['model_timezone'], $options['model_timezone'])
            ));
        } elseif ('array' === $options['input']) {
            $builder->addModelTransformer(new Symfony_Component_Form_ReversedTransformer(
                new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToArrayTransformer($options['model_timezone'], $options['model_timezone'], $parts)
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(Symfony_Component_Form_FormView $view, Symfony_Component_Form_FormInterface $form, array $options)
    {
        $view->vars = array_replace($view->vars, array(
            'widget'       => $options['widget'],
            'with_minutes' => $options['with_minutes'],
            'with_seconds' => $options['with_seconds'],
        ));

        if ('single_text' === $options['widget']) {
            $view->vars['type'] = 'time';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(Symfony_Component_OptionsResolver_OptionsResolverInterface $resolver)
    {
        $compound = function (Symfony_Component_OptionsResolver_Options $options) {
            return $options['widget'] !== 'single_text';
        };

        $emptyValue = $emptyValueDefault = function (Symfony_Component_OptionsResolver_Options $options) {
            return $options['required'] ? null : '';
        };

        $emptyValueNormalizer = function (Symfony_Component_OptionsResolver_Options $options, $emptyValue) use ($emptyValueDefault) {
            if (is_array($emptyValue)) {
                $default = $emptyValueDefault($options);

                return array_merge(
                    array('hour' => $default, 'minute' => $default, 'second' => $default),
                    $emptyValue
                );
            }

            return array(
                'hour' => $emptyValue,
                'minute' => $emptyValue,
                'second' => $emptyValue
            );
        };

        // BC until Symfony 2.3
        $modelTimezone = function (Symfony_Component_OptionsResolver_Options $options) {
            return $options['data_timezone'];
        };

        // BC until Symfony 2.3
        $viewTimezone = function (Symfony_Component_OptionsResolver_Options $options) {
            return $options['user_timezone'];
        };

        $resolver->setDefaults(array(
            'hours'          => range(0, 23),
            'minutes'        => range(0, 59),
            'seconds'        => range(0, 59),
            'widget'         => 'choice',
            'input'          => 'datetime',
            'with_minutes'   => true,
            'with_seconds'   => false,
            'model_timezone' => $modelTimezone,
            'view_timezone'  => $viewTimezone,
            // Deprecated timezone options
            'data_timezone'  => null,
            'user_timezone'  => null,
            'empty_value'    => $emptyValue,
            // Don't modify DateTime classes by reference, we treat
            // them like immutable value objects
            'by_reference'   => false,
            'error_bubbling' => false,
            // If initialized with a DateTime object, FormType initializes
            // this option to "DateTime". Since the internal, normalized
            // representation is not DateTime, but an array, we need to unset
            // this option.
            'data_class'     => null,
            'compound'       => $compound,
        ));

        $resolver->setNormalizers(array(
            'empty_value' => $emptyValueNormalizer,
        ));

        $resolver->setAllowedValues(array(
            'input' => array(
                'datetime',
                'string',
                'timestamp',
                'array',
            ),
            'widget' => array(
                'single_text',
                'text',
                'choice',
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'field';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'time';
    }
}
