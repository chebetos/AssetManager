<?php

namespace AssetManager\Service;

use AssetManager\Resolver\ResolverInterface;
use Zend\Uri\UriInterface;
use Zend\Stdlib\RequestInterface;
use Zend\Http\PhpEnvironment\Request;
use finfo;
use SplFileInfo;

/**
 * @category    AssetManager
 * @package     AssetManager
 */
class AssetManager
{
    /**
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * @param ResolverInterface $resolver
     */
    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Serve the asset for the supplied asset
     *
     * @param RequestInterface
     * @todo not sure this fits the asset manager directly. This may instead be handled directly in the lifecycle event
     */
    public function serveAsset(RequestInterface $request)
    {
        if (!$request instanceof Request) {

            return;
        }

        /* @var $request Request */
        /* @var $uri \Zend\Uri\UriInterface */
        $uri        = $request->getUri();
        $fullPath   = $uri->getPath();
        $path       = substr($fullPath, strlen($request->getBasePath()) + 1);

        if ($file = $this->resolver->resolve($path)) {
            $this->send($file);
        }
    }

    /**
     * Output any asset.
     *
     * @param string $file /Path/To/File for output
     */
    protected function send($file)
    {
        // @todo add filtering at this level
        $finfo      = new finfo(FILEINFO_MIME);
        $mimeType   = $finfo->file($file);
        $fileinfo   = new SplFileInfo($file);
        $file       = $fileinfo->openFile('rb');

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . $file->getSize());

        $file->fpassthru();
        exit;
    }
}
