<?php

namespace Ekyna\Bundle\FileManagerBundle\Generator;

use Ekyna\Bundle\FileManagerBundle\Browser\Element;
use Ekyna\Bundle\FileManagerBundle\Browser\ElementTypes;
use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Palette\RGB;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Imagine\Image\Point;

/**
 * Class ThumbGenerator
 * @package Ekyna\Bundle\FileManagerBundle\Generator
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ThumbGenerator
{
    /**
     * @var \Imagine\Image\ImagineInterface
     */
    private $imagine;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $fs;

    /**
     * @var string
     */
    private $thumbDirPath;

    /**
     * @var string
     */
    private $iconsDirRealPath;

    /**
     * @var string
     */
    private $webDirRealPath;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel
     * @param ImagineInterface $imagine
     * @param string $thumbDirPath The file manager thumbs dir.
     */
    public function __construct(KernelInterface $kernel, ImagineInterface $imagine, $thumbDirPath)
    {
        $this->imagine = $imagine;

        $this->thumbDirPath = '/'.trim($thumbDirPath, '/');
        $this->iconsDirRealPath = realpath(__DIR__.'/../Resources/extensions');
        $this->webDirRealPath = realpath($kernel->getRootDir().'/../web');

        $this->fs = new Filesystem();
    }

    /**
     * Generates a thumb for the given element.
     * 
     * @return string the thumb path
     */
    public function generate(Element $element)
    {
        // Directory
        if (ElementTypes::BACK === $element->getType()) {
            return '/bundles/ekynafilemanager/img/back.jpg';
        }

        // Directory
        if (ElementTypes::DIRECTORY === $element->getType()) {
            return '/bundles/ekynafilemanager/img/folder.jpg';
        }

        // Image thumb
        if (ElementTypes::IMAGE === $element->getType() && null !== $webPath = $element->getWebPath()) {
            if (null !== $thumbPath = $this->generateImageThumb($element->getRealPath(), $webPath)) {
                return $thumbPath;
            }
        }

        // Extension thumb
        if (null !== $extension = $element->getExtension()) {
            if (null !== $thumbPath = $this->generateExtensionThumb($extension, $element->getType())) {
                return $thumbPath;
            }
        }

        // Default
        return '/bundles/ekynafilemanager/img/file.jpg';
    }

    public function remove(Element $element)
    {
        $target = $this->webDirRealPath . $element->getVisual();
        if ($this->fs->exists($target)) {
            $this->fs->remove($target);
        }
    }

    private function generateImageThumb($source, $webPath)
    {
        $thumbPath   = sprintf('%s/%s', $this->thumbDirPath, trim($webPath, '/'));
        $destination = $this->webDirRealPath . $thumbPath;

        if (file_exists($destination)) {
            return $thumbPath;
        }

        $this->checkDir(dirname($destination));
        try {
            $this->imagine
                ->open($source)
                ->thumbnail(new Box(120, 90), ManipulatorInterface::THUMBNAIL_OUTBOUND)
                ->save($destination)
            ;
            return $thumbPath;
        } catch (\Imagine\Exception\RuntimeException $e) {
            // Image thumb generation failed
        }

        return null;
    }

    private function generateExtensionThumb($extension, $type)
    {
        $thumbPath   = sprintf('%s/extension_icon_%s.jpg', $this->thumbDirPath, $extension);
        $destination = $this->webDirRealPath . $thumbPath;

        if (file_exists($destination)) {
            return $thumbPath;
        }

        $backgroundColor = ElementTypes::getColor($type);

        $iconPath = sprintf('%s/%s.png', $this->iconsDirRealPath, $extension);
        if (! file_exists($iconPath)) {
            $iconPath = $this->iconsDirRealPath.'/default.png';
        }

        $this->checkDir(dirname($destination));
        try {
            $palette = new RGB();
            $thumb = $this->imagine->create(new Box(120, 90), $palette->color($backgroundColor));

            $icon = $this->imagine->open($iconPath);
            $iconSize = $icon->getSize();
            $start = new Point(120/2 - $iconSize->getWidth()/2, 90/2 - $iconSize->getHeight()/2);

            $thumb->paste($icon, $start);
            $thumb->save($destination);

            return $thumbPath;
        } catch (\Imagine\Exception\RuntimeException $e) {
            // Image thumb generation failed
        }

        return null;
    }

    private function checkDir($dir)
    {
        if (! $this->fs->exists($dir)) {
            $this->fs->mkdir($dir);
        }
    }
}
