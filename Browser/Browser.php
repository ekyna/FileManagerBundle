<?php

namespace Ekyna\Bundle\FileManagerBundle\Browser;

use Ekyna\Bundle\FileManagerBundle\Generator\ThumbGenerator;
use Ekyna\Bundle\FileManagerBundle\System\System;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Ekyna\Bundle\FileManagerBundle\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Browser.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Browser
{
    /**
     * @var \Ekyna\Bundle\FileManagerBundle\Generator\ThumbGenerator
     */
    private $generator;

    /**
     * @var \Ekyna\Bundle\FileManagerBundle\System\System
     */
    private $system;

    /**
     * The currently working directory disk relative path.
     * 
     * @var string
     */
    private $currentPath;

    /**
     * The currently managed element.
     * 
     * @var Element
     */
    private $currentElement;

    /**
     * @var \Symfony\Component\Finder\Finder
     */
    private $finder;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * @var \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser
     */
    private $mimeTypeGuesser;

    /**
     * @var Element[]
     */
    private $elements;

    /**
     * @var array
     */
    private $breadcrumb;

    /**
     * @var array
     */
    private $configuration;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->currentPath = '/';
        $this->mimeTypeGuesser = MimeTypeGuesser::getInstance();

        $this->filesystem = new Filesystem();
        $this->finder = new Finder();

        $this->configuration = array(
            'path'    => '',
            'filters' => array(),
            'sortBy'  => 'filename',
            'sortDir' => 'ASC',
            'search'  => '',
        );
    }

    /**
     * Sets the system.
     * 
     * @param \Ekyna\Bundle\FileManagerBundle\System\System $system
     * 
     * @return Browser
     */
    public function setSystem(System $system)
    {
        $this->system = $system;

        return $this;
    }

    /**
     * Returns the system.
     * 
     * @return \Ekyna\Bundle\FileManagerBundle\System\System
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * Sets the disk relative path of the current working directory.
     * 
     * @return string
     * 
     * @return Browser
     */
    private function setCurrentPath($path)
    {
        $this->currentPath = '/'.trim($path, '/');
        if (! $this->filesystem->exists($this->getCurrentRealPath())) {
            throw new \RuntimeException(sprintf('"%s" does not exists.', $path));
        }

        return $this;
    }

    /**
     * Returns the disk relative path of the current working directory.
     * 
     * @return string
     */
    private function getCurrentPath()
    {
        return $this->currentPath;
    }

    /**
     * Returns the absolute path of the current working directory.
     * 
     * @return string
     */
    private function getCurrentRealPath()
    {
        return $this->system->getRootPath().$this->currentPath;
    }

    /**
     * Returns the currently managed element.
     * 
     * @return \Ekyna\Bundle\FileManagerBundle\Browser\Element
     */
    public function getCurrentElement()
    {
        return $this->currentElement;
    }

    /**
     * Sets the thumb generator.
     * 
     * @param \Ekyna\Bundle\FileManagerBundle\Generator\ThumbGenerator $generator
     * 
     * @return Browser
     */
    public function setGenerator(ThumbGenerator $generator)
    {
        $this->generator = $generator;

        return $this;
    }

    /**
     * Returns the elements.
     * 
     * @return Element[]
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Returns the breadcrumb.
     * 
     * @return array
     */
    public function getBreadcrumb()
    {
        return $this->breadcrumb;
    }

    /**
     * Returns the configuration.
     * 
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Initialize the browse from the given request.
     * 
     * @param Request $request
     * 
     * @throws \RuntimeException
     * 
     * @return Browser
     */
    public function init(Request $request)
    {
        if (null === $this->system || null == $this->generator) {
            throw new \RuntimeException('Browser is not properly configured.');
        }

        // Update the configuration
        $parameterBag = null;
        if ($request->getMethod() == 'GET') {
            $parameterBag = $request->query;
        } elseif ($request->getMethod() == 'POST') {
            $parameterBag = $request->request;
        }
        if (null !== $parameterBag) {
            foreach (array_keys($this->configuration) as $key) {
                if (null !== $value = $parameterBag->get($key, null)) {
                    $this->configuration[$key] = $value;
                }
            }
        }

        $this->setCurrentPath($this->configuration['path']);
        $this->finder->in($this->getCurrentRealPath())->depth(0);

        $this->currentElement = null;
        if (null !== $name = $request->attributes->get('file', null)) {
            $this->currentElement = $this->findElement($name, true);
        }

        return $this;
    }

    /**
     * Finds an element matching the given name.
     * 
     * @param string $name
     */
    private function findElement($name, $throwException = false)
    {
        $finder = new Finder();
        $finder
            ->in($this->getCurrentRealPath())
            ->depth(0)
            ->name(addcslashes(sprintf('/^%s$/', $name), '().'))
        ;

        $result = current(iterator_to_array($finder->getIterator()));

        if (false !== $result) {
            return $this->transformSplFileInfoToElement($result);
        } elseif ($throwException) {
            throw new RuntimeException(sprintf('Unable to find "%s" element.', $name));
        }

        return null;
    }

    /**
     * Browses files for the given request.
     * 
     * @throws \RuntimeException
     */
    public function browse()
    {
        $this->elements = array();
        $this->breadcrumb = array(array('label' => 'Home', 'path' => ''));

        // Breadcrumb
        if ('' !== $path = $this->configuration['path']) {
            $path = trim($path, '/');
            $parts = explode('/', $path);

            $backPath = implode('/', array_slice($parts, 0, count($parts) - 1));
            $this->elements[] = $this->createBackButton($backPath);

            $breadcrumbPath = '';
            foreach ($parts as $part) {
                $breadcrumbPath .= '/'.$part;
                $this->breadcrumb[] = array('label' => $part, 'path' => trim($breadcrumbPath, '/'));
            }
        }

        // Elements
        foreach ($this->finder->directories() as $directory) {
            $this->elements[] = $this->transformSplFileInfoToElement($directory);
        }
        foreach ($this->finder->files() as $file) {
            $this->elements[] = $this->transformSplFileInfoToElement($file);
        }
    }

    /**
     * Creates a directory
     * 
     * @param string $name : the directory name
     * 
     * @throws RuntimeException
     */
    public function mkdir($name)
    {
        $newDirectoryPath = $this->getCurrentRealPath().'/'.$name;

        if ($this->filesystem->exists($newDirectoryPath)) {
            throw new RuntimeException(sprintf('"%s" folder allready exists.', $name));
        }

        try {
            $this->filesystem->mkdir($newDirectoryPath);
        } catch(\Exception $e) {
            throw new RuntimeException(sprintf('Failed to create "%s" directory.', $name));
        }
    }

    /**
     * Move an uploaded file.
     * 
     * @param UploadedFile $file
     */
    public function upload(UploadedFile $file, $name = null)
    {
        if (0 == strlen($name)) {
            $name = $file->getClientOriginalName();
        }

        // TODO: normalize name / check extension

        $filename = pathinfo($name, PATHINFO_FILENAME);
        $extension = pathinfo($name, PATHINFO_EXTENSION);

        if (0 == strlen($filename) || 0 == strlen($extension)) {
            throw new RuntimeException(sprintf('Bad formated file name "%s".', $name));
        }

        $target = sprintf('%s.%s', $filename, $extension);
        if ($this->filesystem->exists($this->getCurrentRealPath().'/'.$target)) {
            $count = 1;
            do {
                $count++;
                $target = sprintf('%s-(%d).%s', $filename, $count, $extension);
            } while ($this->filesystem->exists($this->getCurrentRealPath().'/'.$target));
        }

        try {
            $file->move($this->getCurrentRealPath(), $target);
        } catch(\Exception $e) {
            throw new RuntimeException(sprintf('Failed to upload "%s" file.', $filename));
        }
    }

    /**
     * Renames the current element.
     *
     * @param string $newName : the directory name
     *
     * @throws RuntimeException
     */
    public function rename($newName)
    {
        if (null === $this->currentElement) {
            throw new RuntimeException('Current element is not defined.');
        }

        // TODO: normalize name / check extension
        
        if ($this->filesystem->exists($this->getCurrentRealPath().'/'.$newName)) {
            throw new RuntimeException('Un autre fichier portant le même nom éxiste déjà.');
        }

        $this->generator->remove($this->currentElement);
        
        try {
            $this->filesystem->rename($this->currentElement->getRealPath(), $this->getCurrentRealPath().'/'.$newName);
        } catch(\Exception $e) {
            throw new RuntimeException(sprintf(
                'Failed to rename "%s" %s.',
                $this->currentElement->getFilename(),
                $this->currentElement->getType() === ElementTypes::DIRECTORY ? 'directory' : 'file'
            ));
        }
    }

    /**
     * Removes the current element.
     *
     * @throws RuntimeException
     */
    public function remove()
    {
        if (null === $this->currentElement) {
            throw new RuntimeException('Current element is not defined.');
        }

        $this->generator->remove($this->currentElement);

        try {
            $this->filesystem->remove($this->currentElement->getRealPath());
        } catch(\Exception $e) {
            throw new RuntimeException(sprintf(
                'Failed to delete "%s" %s.',
                $this->currentElement->getFilename(),
                $this->currentElement->getType() === ElementTypes::DIRECTORY ? 'directory' : 'file'
            ));
        }
    }

    /**
     * Transform a SplFileInfo object to an Element object
     * 
     * @param SplFileInfo $splFileInfo
     * 
     * @return \Ekyna\Bundle\FileManagerBundle\Browser\Element
     */
    private function transformSplFileInfoToElement(SplFileInfo $splFileInfo)
    {
        $mimeType = null;
        $type = ElementTypes::DIRECTORY;
        if (! $splFileInfo->isDir()) {
            $mimeType = $this->mimeTypeGuesser->guess($splFileInfo->getRealPath());
            $type = ElementTypes::guessTypeFromMimeType($mimeType);
        }

        $systemPath = trim($this->currentPath.'/'.$splFileInfo->getFilename(), '/');

        $element = new Element();
        $element
            ->setType($type)
            ->setFilename($splFileInfo->getFilename())
            ->setSize($splFileInfo->getSize())
            ->setExtension($splFileInfo->getExtension())
            ->setMimeType($mimeType)
            ->setSystemPath($systemPath)
            ->setRealPath($splFileInfo->getRealPath())
        ;

        if (null !== $webRoot = $this->system->getWebRootPath()) {
            $element->setWebPath($webRoot.'/'.$systemPath);
        }

        $element->setVisual($this->generator->generate($element));

        return $element;
    }

    /**
     * Creates a "back" element.
     * 
     * @param string $path
     * 
     * @return \Ekyna\Bundle\FileManagerBundle\Browser\Element
     */
    private function createBackButton($path)
    {
        $element = new Element();
        $element
            ->setType(ElementTypes::BACK)
            ->setSystemPath($path)
        ;

        return $element->setVisual($this->generator->generate($element));
    }
}
