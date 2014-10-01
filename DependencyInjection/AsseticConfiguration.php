<?php

namespace Ekyna\Bundle\FileManagerBundle\DependencyInjection;

/**
 * AsseticConfiguration
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AsseticConfiguration
{
    /**
     * Builds the assetic configuration.
     *
     * @param array $config
     *
     * @return array
     */
    public function build(array $config)
    {
        $output = array();

        // Fix path in output dir
        if ('/' !== substr($config['output_dir'], -1) && strlen($config['output_dir']) > 0) {
            $config['output_dir'] .= '/';
        }

        $output['filemanager_css'] = $this->buildCss($config);
        $output['filemanager_js'] = $this->buildJs($config);

        return $output;
    }

    /**
     * @param array $config
     *
     * @return array
     */
    protected function buildCss(array $config)
    {
        $inputs = array(
            '%kernel.root_dir%/../vendor/twbs/bootstrap/dist/css/bootstrap.min.css',
            '@EkynaFileManagerBundle/Resources/asset/css/browser.css',
        );

        return array(
            'inputs'  => $inputs,
            'filters' => array('cssrewrite', 'yui_css'),
            'output'  => $config['output_dir'].'css/filemanager.css',
            'debug'   => false,
        );
    }

    /**
     * @param array $config
     *
     * @return array
     */
    protected function buildJs(array $config)
    {        
        $inputs = array(
            '%kernel.root_dir%/../vendor/components/jquery/jquery.min.js',
    	    '%kernel.root_dir%/../vendor/twbs/bootstrap/dist/js/bootstrap.min.js',
    	    '%kernel.root_dir%/../vendor/jms/twig-js/twig.js',
            '%kernel.root_dir%/../vendor/malsup/form/jquery.form.js',
    	    'bundles/fosjsrouting/js/router.js',
    	    '@EkynaFileManagerBundle/Resources/asset/js/string.prototypes.js',
    	    '@EkynaFileManagerBundle/Resources/asset/js/forms.js',
    	    '@EkynaFileManagerBundle/Resources/asset/js/browser.js',
        );

        return array(
            'inputs'  => $inputs,
            'filters' => array('yui_js'),
            'output'  => $config['output_dir'].'js/filemanager.js',
            'debug'   => false,
        );
    }
}
