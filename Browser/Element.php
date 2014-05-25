<?php

namespace Ekyna\Bundle\FileManagerBundle\Browser;

/**
 * Element.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Element
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $extension;

    /**
     * @var string
     */
    private $mimeType;

    /**
     * @var string
     */
    private $systemPath;

    /**
     * @var string
     */
    private $webPath;

    /**
     * @var string
     */
    private $realPath;

    /**
     * @var string
     */
    private $visual;


	/**
	 * Returns the type.
	 * 
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

	/**
	 * Sets the type.
	 * 
     * @param string $type
     * 
     * @return Element
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

	/**
	 * Returns the file name.
	 * 
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

	/**
	 * Sets the file name.
	 * 
     * @param string $filename
     * 
     * @return Element
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

	/**
	 * Returns the size.
	 * 
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

	/**
	 * Sets the size.
	 * 
     * @param int $size
     * 
     * @return Element
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

	/**
	 * Returns the extension.
	 * 
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

	/**
	 * Sets the extension.
	 * 
     * @param string $extension
     * 
     * @return Element
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

	/**
	 * Returns the mime type.
	 * 
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

	/**
	 * Sets the mime type.
	 * 
     * @param string $mimeType
     * 
     * @return Element
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

	/**
	 * Returns the system path.
	 * 
     * @return string
     */
    public function getSystemPath()
    {
        return $this->systemPath;
    }

	/**
	 * Sets the system path.
	 * 
     * @param string $systemPath
     * 
     * @return Element
     */
    public function setSystemPath($systemPath)
    {
        $this->systemPath = $systemPath;

        return $this;
    }

	/**
	 * Returns the web path.
	 * 
     * @return string
     */
    public function getWebPath()
    {
        return $this->webPath;
    }

	/**
	 * Sets the web path.
	 * 
     * @param string $webPath
     * 
     * @return Element
     */
    public function setWebPath($webPath)
    {
        $this->webPath = $webPath;

        return $this;
    }

	/**
	 * Returns the real path.
	 * 
     * @return string
     */
    public function getRealPath()
    {
        return $this->realPath;
    }

	/**
	 * Sets the real path.
	 * 
     * @param string $realPath
     * 
     * @return Element
     */
    public function setRealPath($realPath)
    {
        $this->realPath = $realPath;

        return $this;
    }

	/**
	 * Returns the visual.
	 * 
     * @return string
     */
    public function getVisual()
    {
        return $this->visual;
    }

	/**
	 * Sets the visual.
	 * 
     * @param string $visual
     * 
     * @return Element
     */
    public function setVisual($visual)
    {
        $this->visual = $visual;

        return $this;
    }
}
