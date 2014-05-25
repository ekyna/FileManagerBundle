<?php

namespace Ekyna\Bundle\FileManagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * UploadType.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class UploadType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($options['action'])
            ->setMethod('POST')
            ->add('file', 'file', array(
        	    'label' => 'Nouveau fichier',
                'sizing' => 'sm',
                'attr' => array('class' =>  'input-sm'),
            ))
            ->add('rename', 'text', array(
        	    'label' => 'Renommer',
                'attr' => array(
                    'class' => 'rename-widget input-sm',
                )
            ))
            ->add('submit', 'submit', array(
        	    'label' => 'Uploader',
                'attr' => array(
            	    'class' => 'btn-sm',
                )
            ))
            ->add('cancel', 'button', array(
        	    'label' => 'Annuler',
                'attr' => array(
            	    'class' => 'efm-form-cancel btn-sm',
                )
            ))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'action' => null,
                'attr' => array(
                    'class' => 'form-inline',
                    'id' => 'upload',
                ),
            ))
            ->setRequired(array('action'))
            ->setAllowedTypes(array(
                'action' => 'string'
            ))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
    	return 'filemanager_upload';
    }
}
