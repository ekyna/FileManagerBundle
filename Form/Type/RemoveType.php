<?php

namespace Ekyna\Bundle\FileManagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * RemoveType.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class RemoveType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($options['action'])
            ->setMethod('POST')
            ->add('confirm', 'checkbox', array(
        	    'label' => sprintf('Confirmer la suppression du fichier "%s" ?', $options['file']),
                'required' => true,
            ))
            ->add('submit', 'submit', array(
        	    'label' => 'Supprimer',
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
                'file' => null,
                'attr' => array(
                    'class' => 'form-inline',
                    'id' => 'remove',
                ),
            ))
            ->setRequired(array('action', 'file'))
            ->setAllowedTypes(array(
                'action' => 'string',
                'file' => 'string'
            ))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
    	return 'filemanager_remove';
    }
}
