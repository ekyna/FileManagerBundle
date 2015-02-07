<?php

namespace Ekyna\Bundle\FileManagerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * EkynaFileManagerExtension.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaFileManagerExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if ($container->hasDefinition('ekyna_file_manager.registry')) {
            $registry = $container->getDefinition('ekyna_file_manager.registry');
            foreach ($config['systems'] as $name => $options) {
                $registry->addMethodCall('createAndRegister', array($name, $options));
            }
        }

        $container->setParameter('ekyna_file_manager.thumbs_directory', $config['thumbs_dir']);
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);

        if (array_key_exists('AsseticBundle', $bundles)) {
            $this->configureAsseticBundle($container, $config);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    protected function configureAsseticBundle(ContainerBuilder $container, array $config)
    {
        $asseticConfig = new AsseticConfiguration;
        $container->prependExtensionConfig('assetic', array(
            'bundles' => array('EkynaFileManagerBundle'),
            'assets' => $asseticConfig->build($config),
        ));
    }
}
