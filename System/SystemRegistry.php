<?php

namespace Ekyna\Bundle\FileManagerBundle\System;

use Symfony\Component\HttpKernel\Kernel;
use Ekyna\Bundle\FileManagerBundle\Browser\Browser;
use Ekyna\Bundle\FileManagerBundle\Generator\ThumbGenerator;
use Symfony\Component\Filesystem\Filesystem;
use Ekyna\Bundle\FileManagerBundle\Util\Path;

/**
 * Class SystemRegistry
 * @package Ekyna\Bundle\FileManagerBundle\System
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class SystemRegistry
{
    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var ThumbGenerator
     */
    private $generator;

    /**
     * @var array
     */
    private $systems;


    /**
     * Constructor.
     *
     * @param Kernel $kernel
     * @param ThumbGenerator $generator
     * @param array $systems
     */
    public function __construct(Kernel $kernel, ThumbGenerator $generator, array $systems)
    {
        $this->kernel    = $kernel;
        $this->generator = $generator;
        $this->systems   = $systems;
    }

    /**
     * Creates and registers a system definition.
     *
     * @param string $name
     * @param array $options
     */
    public function createAndRegister($name, array $options)
    {
        $options = array_merge(array(
            'upload_roles'    => null,
            'allowed_types'   => null,
            'forbidden_types' => null,
            'delete_roles'    => null,
        ), $options);

        $fs = new Filesystem();
        $rootPath = rtrim($options['root_path'], '/');
        if (Path::isAbsolute($rootPath)) {
            if (false === strpos($this->kernel->getRootDir(), $rootPath)) {
                throw new \InvalidArgumentException('System root directory is not under application root directory.');
            }
        } else {
            $rootPath = Path::normalize($this->kernel->getRootDir() . '/' . $options['root_path']);
        }
        if (!$fs->exists($rootPath)) {
            $fs->mkdir($rootPath);
        }

        $webRootPath = null;
        $webPath = realpath($this->kernel->getRootDir() . '/../web');

        if (0 == strpos($rootPath, $webPath)) {
            $webRootPath = substr($rootPath, strlen($webPath));
        }

        $system = new System($name);
        $system
            ->setRootPath($rootPath)
            ->setWebRootPath($webRootPath)
            ->setUploadRoles($options['upload_roles'])
            ->setAllowedTypes($options['allowed_types'])
            ->setForbiddenTypes($options['forbidden_types'])
            ->setDeleteRoles($options['delete_roles'])
        ;
        $this->add($system);
    }

    /**
     * Adds a system definition.
     * 
     * @param System $system
     */
    public function add(System $system)
    {
        if (! $this->has($system->getName())) {
            $this->systems[$system->getName()] = $system;
        }
    }

    /**
     * Returns whether a system definition is registered or not for the given name.
     * 
     * @param string $name
     * 
     * @return boolean
     */
    public function has($name)
    {
        return array_key_exists($name, $this->systems);
    }

    /**
     * Returns a system definition for the given name.
     * 
     * @param string $name
     * 
     * @return System|NULL
     */
    public function get($name)
    {
        if ($this->has($name)) {
            return $this->systems[$name];
        }
        return null;
    }

    /**
     * Returns a browser for the given system name.
     * 
     * @param string $name
     * 
     * @return Browser
     */
    public function getBrowser($name)
    {
        if (! $this->has($name)) {
            throw new \InvalidArgumentException(sprintf('Unknown system "%s".', $name));
        }

        $browser = new Browser();
        $browser
            ->setSystem($this->get($name))
            ->setGenerator($this->generator)
        ;

        return $browser;
    }

    /**
     * Returns all registered systems.
     * 
     * @return array
     */
    public function getSystems()
    {
        return $this->systems;
    }
}
