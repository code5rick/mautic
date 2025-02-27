<?php

namespace MauticPlugin\MauticFocusBundle\Form\Type;

use Mautic\CoreBundle\Form\Type\YesNoButtonGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class FocusPropertiesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [];

        // Type specific
        switch ($options['focus_style']) {
            case 'bar':
                $builder->add(
                    'allow_hide',
                    YesNoButtonGroupType::class,
                    [
                        'label' => 'mautic.focus.form.bar.allow_hide',
                        'data'  => $options['data']['allow_hide'] ?? true,
                        'attr'  => [
                            'onchange' => 'Mautic.focusUpdatePreview()',
                        ],
                    ]
                );

                $builder->add(
                    'push_page',
                    YesNoButtonGroupType::class,
                    [
                        'label' => 'mautic.focus.form.bar.push_page',
                        'attr'  => [
                            'tooltip'  => 'mautic.focus.form.bar.push_page.tooltip',
                            'onchange' => 'Mautic.focusUpdatePreview()',
                        ],
                        'data' => $options['data']['push_page'] ?? true,
                    ]
                );

                $builder->add(
                    'sticky',
                    YesNoButtonGroupType::class,
                    [
                        'label' => 'mautic.focus.form.bar.sticky',
                        'attr'  => [
                            'tooltip'  => 'mautic.focus.form.bar.sticky.tooltip',
                            'onchange' => 'Mautic.focusUpdatePreview()',
                        ],
                        'data' => $options['data']['sticky'] ?? true,
                    ]
                );

                $builder->add(
                    'size',
                    ChoiceType::class,
                    [
                        'choices'           => [
                            'mautic.focus.form.bar.size.large'   => 'large',
                            'mautic.focus.form.bar.size.regular' => 'regular',
                        ],
                        'label'      => 'mautic.focus.form.bar.size',
                        'label_attr' => ['class' => 'control-label'],
                        'attr'       => [
                            'class'    => 'form-control',
                            'onchange' => 'Mautic.focusUpdatePreview()',
                        ],
                        'required'    => false,
                        'placeholder' => false,
                    ]
                );

                $choices = [
                    'mautic.focus.form.placement.top'    => 'top',
                    'mautic.focus.form.placement.bottom' => 'bottom',
                ];
                break;
            case 'modal':
                $choices = [
                    'mautic.focus.form.placement.top'    => 'top',
                    'mautic.focus.form.placement.middle' => 'middle',
                    'mautic.focus.form.placement.bottom' => 'bottom',
                ];
                break;
            case 'notification':
                $choices = [
                    'mautic.focus.form.placement.top_left'     => 'top_left',
                    'mautic.focus.form.placement.top_right'    => 'top_right',
                    'mautic.focus.form.placement.bottom_left'  => 'bottom_left',
                    'mautic.focus.form.placement.bottom_right' => 'bottom_right',
                ];
                break;
            case 'page':
                break;
        }

        if (!empty($choices)) {
            $builder->add(
                'placement',
                ChoiceType::class,
                [
                    'choices'           => $choices,
                    'label'             => 'mautic.focus.form.placement',
                    'label_attr'        => ['class' => 'control-label'],
                    'attr'              => [
                        'class'    => 'form-control',
                        'onchange' => 'Mautic.focusUpdatePreview()',
                    ],
                    'required'    => false,
                    'placeholder' => false,
                ]
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['focus_style']);

        $resolver->setDefaults(
            [
                'label' => false,
            ]
        );
    }
}
