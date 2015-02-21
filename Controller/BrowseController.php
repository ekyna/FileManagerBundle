<?php

namespace Ekyna\Bundle\FileManagerBundle\Controller;

use Ekyna\Bundle\FileManagerBundle\Browser\ElementTypes;
use Ekyna\Bundle\FileManagerBundle\Exception\RuntimeException;
use Ekyna\Bundle\FileManagerBundle\Form\Type\MkdirType;
use Ekyna\Bundle\FileManagerBundle\Form\Type\UploadType;
use Ekyna\Bundle\FileManagerBundle\Form\Type\RenameType;
use Ekyna\Bundle\FileManagerBundle\Form\Type\RemoveType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class BrowseController
 * @package Ekyna\Bundle\FileManagerBundle\Controller
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class BrowseController extends Controller
{
    public function indexAction(Request $request)
    {
        $browser = $this->getBrowser($request);

        $browser->browse();

        if ($request->isXmlHttpRequest()) {
            return $this->createJsonResponse(array(
                'elements' => $browser->getElements(),
                'breadcrumb' => $browser->getBreadcrumb(),
            ));
        }

        return $this->render(
            'EkynaFileManagerBundle:Browse:index.html.twig',
            array(
                'browser' => $browser,
            )
        );
    }

    public function mkdirAction(Request $request)
    {
        if (! $request->isXmlHttpRequest()) {
            return new NotFoundHttpException();
        }

        $browser = $this->getBrowser($request);

        $form = $this->createForm(new MkdirType(), null, array(
            'action' => $this->generateUrl(
                'ekyna_filemanager_mkdir', 
                array('system' => $browser->getSystem()->getName())
            ),
        ));

        $datas = array();

        $form->handleRequest($request);
        if($form->isValid()) {
            try {
                $browser->mkdir($form->get('name')->getData());

                $datas['flash'] = array(
                    'type' => 'success',
                    'message' => 'Le répertoire a été créé avec succès.',
                );
                $datas['browse'] = true;

                return $this->createJsonResponse($datas);

            } catch(RuntimeException $e) {
                $datas['flash'] = array(
                    'type' => 'danger',
                    'message' => $e->getMessage(),
                );
            }
        }

        $datas['form'] = $this->renderView(
            'EkynaFileManagerBundle:Browse:form.html.twig',
            array('form' => $form->createView())
        );

        return $this->createJsonResponse($datas);
    }

    public function uploadAction(Request $request)
    {
        if (! $request->isXmlHttpRequest()) {
            return new NotFoundHttpException();
        }

        $browser = $this->getBrowser($request);

        $form = $this->createForm(new UploadType(), null, array(
            'action' => $this->generateUrl(
                'ekyna_filemanager_upload',
                array('system' => $browser->getSystem()->getName())
            ),
        ));

        $datas = array();

        $form->handleRequest($request);
        if($form->isValid()) {
            try {
                $browser->upload(
                    $form->get('file')->getData(),
                    $form->get('rename')->getData()
                );

                $datas['flash'] = array(
                    'type' => 'success',
                    'message' => 'Le fichier a été uploadé avec succès.',
                );
                $datas['browse'] = true;

                return $this->createJsonResponse($datas);

            } catch(RuntimeException $e) {
                $datas['flash'] = array(
                    'type' => 'danger',
                    'message' => $e->getMessage(),
                );
            }
        }

        $datas['form'] = $this->renderView(
            'EkynaFileManagerBundle:Browse:form.html.twig',
            array('form' => $form->createView())
        );

        return $this->createJsonResponse($datas);
    }

    public function showAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            return new NotFoundHttpException();
        }

        $browser = $this->getBrowser($request);
        $file = $browser->getCurrentElement();
        
        if (in_array($file->getType(), array(ElementTypes::DIRECTORY, ElementTypes::BACK))) {
            return new NotFoundHttpException();
        }

        return new BinaryFileResponse($file->getRealPath());
    }

    public function downloadAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            return new NotFoundHttpException();
        }

        $browser = $this->getBrowser($request);
        $file = $browser->getCurrentElement();
        
        if (in_array($file->getType(), array(ElementTypes::DIRECTORY, ElementTypes::BACK))) {
            return new NotFoundHttpException();
        }

        $response = new BinaryFileResponse($file->getRealPath());

        $d = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $file->getFilename()
        );
        $response->headers->set('Content-Disposition', $d);

        return $response;
    }

    public function renameAction(Request $request)
    {
        if (! $request->isXmlHttpRequest()) {
            return new NotFoundHttpException();
        }

        $browser = $this->getBrowser($request);
        $filename = $browser->getCurrentElement()->getFilename();

        $form = $this->createForm(
            new RenameType(), 
            array(
        	    'rename' => $filename
            ),
            array(
                'action' => $this->generateUrl(
                    'ekyna_filemanager_rename',
                    array(
                        'system' => $browser->getSystem()->getName(),
                        'file'   => $filename
                    )
                ),
            )
        );

        $datas = array();

        $form->handleRequest($request);
        if($form->isValid()) {
            if ($filename != $newName = $form->get('rename')->getData()) {
                try {
                    $browser->rename(
                        $newName
                    );
    
                    $datas['flash'] = array(
                        'type' => 'success',
                        'message' => 'Le fichier a été renommé avec succès.',
                    );
                    $datas['browse'] = true;
    
                    return $this->createJsonResponse($datas);
    
                } catch(RuntimeException $e) {
                    $datas['flash'] = array(
                        'type' => 'danger',
                        'message' => $e->getMessage(),
                    );
                }
            } else {
                $datas['flash'] = array(
                    'type' => 'warning',
                    'message' => sprintf('Veuillez saisir un nom de fichier différent de "%s".', $filename),
                );
            }
        }

        $datas['form'] = $this->renderView(
            'EkynaFileManagerBundle:Browse:form.html.twig',
            array('form' => $form->createView())
        );

        return $this->createJsonResponse($datas);
    }

    public function removeAction(Request $request)
    {
        if (! $request->isXmlHttpRequest()) {
            return new NotFoundHttpException();
        }

        $browser = $this->getBrowser($request);
        $filename = $browser->getCurrentElement()->getFilename();

        $form = $this->createForm(
            new RemoveType(), 
            null,
            array(
        	    'file' => $filename,
                'action' => $this->generateUrl(
                    'ekyna_filemanager_remove',
                    array(
                        'system' => $browser->getSystem()->getName(),
                        'file'   => $filename
                    )
                ),
            )
        );

        $datas = array();

        $form->handleRequest($request);
        if($form->isValid()) {
            try {
                $browser->remove();

                $datas['flash'] = array(
                    'type' => 'success',
                    'message' => 'Le fichier a été supprimé avec succès.',
                );
                $datas['browse'] = true;

                return $this->createJsonResponse($datas);

            } catch(RuntimeException $e) {
                $datas['flash'] = array(
                    'type' => 'danger',
                    'message' => $e->getMessage(),
                );
            }
        }

        $datas['form'] = $this->renderView(
            'EkynaFileManagerBundle:Browse:form.html.twig',
            array('form' => $form->createView())
        );

        return $this->createJsonResponse($datas);
        
    }

    /**
     * Return a browser for the given request.
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * 
     * @return \Ekyna\Bundle\FileManagerBundle\Browser\Browser
     */
    private function getBrowser(Request $request)
    {
        return $this->get('ekyna_file_manager.registry')->getBrowser($request->attributes->get('system'))->init($request);
    }

    /**
     * Returns a response with JSON formated datas
     *  
     * @param array $datas
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function createJsonResponse(array $datas)
    {
        return new Response($this->get('jms_serializer')->serialize($datas, 'json'));
    }
}
