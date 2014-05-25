<?php

namespace Ekyna\Bundle\FileManagerBundle\Browser;

/**
 * ElementTypes.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
final class ElementTypes
{
    const BACK      = 'back';
    const DIRECTORY = 'directory';
    const FILE      = 'file';
    const IMAGE     = 'image';
    const VIDEO     = 'video';
    const AUDIO     = 'audio';
    const ARCHIVE   = 'archive';

    /**
     * Returns the type for the given mime type.
     * 
     * @param string $mimeType
     * 
     * @return string
     */
    public static function guessTypeFromMimeType($mimeType)
    {
        switch (substr($mimeType, 0, strpos($mimeType, '/'))) {
        	case 'audio' : return self::AUDIO; break;
        	case 'image' : return self::IMAGE; break;
        	case 'video' : return self::VIDEO; break;
        }

        if (preg_match('#zip|rar|compress#', $mimeType)) {
            return self::ARCHIVE;
        }

        return self::FILE;
    }

    /**
     * Returns the background color for the given type.
     * 
     * @param string $type
     * 
     * @return string
     */
    public static function getColor($type)
    {
        switch ($type) {
            case self::AUDIO :
                return 'b1212a';
                break;
            case self::VIDEO :
                return 'de4935';
                break;
            case self::IMAGE :
                return 'e6ab2e';
                break;
            case self::ARCHIVE :
                return '63996b';
                break;
        	case self::FILE :
        	    return '125955';
        	    break;
        }
        return '595959';
    }
}
