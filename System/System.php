<?php

namespace Ekyna\Bundle\FileManagerBundle\System;

/**
 * Class System
 * @package Ekyna\Bundle\FileManagerBundle\System
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class System
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $rootPath;

    /**
     * @var string
     */
    private $webRootPath;

    /**
     * @var array
     */
    private $uploadRoles;

    /**
     * @var array
     */
    private $allowedTypes;

    /**
     * @var array
     */
    private $forbiddenTypes;

    /**
     * @var array
     */
    private $deleteRoles;

    /**
     * Constructor.
     * 
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name           = $name;
        $this->rootPath       = '/';
        $this->uploadRoles    = array('ROLE_ADMIN');
        $this->allowedTypes   = array();
        $this->forbiddenTypes = array();
        $this->deleteRoles    = array('ROLE_ADMIN');
    }

	/**
	 * Returns the name.
	 * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

	/**
	 * Sets the name.
	 * 
     * @param string $name
     * 
     * @return System
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

	/**
	 * Returns the root path.
	 * 
     * @return string
     */
    public function getRootPath()
    {
        return $this->rootPath;
    }

	/**
	 * Sets the root path.
	 * 
     * @param string $rootPath
     * 
     * @return System
     */
    public function setRootPath($rootPath)
    {
        $this->rootPath = $rootPath;

        return $this;
    }

	/**
	 * Returns the web root path.
	 * 
     * @return string
     */
    public function getWebRootPath()
    {
        return $this->webRootPath;
    }

	/**
	 * Sets the web root path.
	 * 
     * @param string $webRootPath
     * 
     * @return System
     */
    public function setWebRootPath($webRootPath)
    {
        $this->webRootPath = $webRootPath;

        return $this;
    }

	/**
	 * Returns the upload roles.
	 * 
     * @return array 
     */
    public function getUploadRoles()
    {
        return $this->uploadRoles;
    }

	/**
	 * Sets the upload roles.
	 * 
     * @param array $uploadRoles
     * 
     * @return System
     */
    public function setUploadRoles($uploadRoles)
    {
        $this->uploadRoles = $uploadRoles;

        return $this;
    }

	/**
	 * Returns the allowed types.
	 * 
     * @return array
     */
    public function getAllowedTypes()
    {
        return $this->allowedTypes;
    }

	/**
	 * Sets the allowad types.
	 * 
     * @param array $allowedTypes
     * 
     * @return System
     */
    public function setAllowedTypes($allowedTypes)
    {
        $this->allowedTypes = $allowedTypes;

        return $this;
    }

	/**
	 * Returns the forbidden types.
	 * 
     * @return array
     */
    public function getForbiddenTypes()
    {
        return $this->forbiddenTypes;
    }

	/**
	 * Sets the forbidden types.
	 * 
     * @param array $forbiddenTypes
     * 
     * @return System
     */
    public function setForbiddenTypes($forbiddenTypes)
    {
        $this->forbiddenTypes = $forbiddenTypes;

        return $this;
    }

	/**
	 * Returns the delete roles.
	 * 
     * @return array 
     */
    public function getDeleteRoles()
    {
        return $this->deleteRoles;
    }

	/**
	 * Sets the delete roles.
	 * 
     * @param array $deleteRoles
     * 
     * @return System
     */
    public function setDeleteRoles($deleteRoles)
    {
        $this->deleteRoles = $deleteRoles;

        return $this;
    }    
}
