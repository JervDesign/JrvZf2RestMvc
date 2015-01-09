<?php

namespace JrvZf2RestMvc\Mvc\Controller;

use Zend\Mvc\Controller\AbstractController;
use Zend\Mvc\MvcEvent;

/**
 * Class AbstractRestController
 *
 * LongDescHere
 *
 * PHP version 5
 *
 * @category  ZF2
 * @package   JrvZf2RestMvc
 * @author    James Jervis <james@jervdesign.com>
 * @copyright 2015 JervDesign
 * @license   License.txt
 * @version   Release: <package_version>
 * @link      https://github.com/JervDesign
 */
class AbstractRestController extends AbstractController
{
    /**
     * Name of request or query parameter containing identifier
     *
     * @var string
     */
    protected $identifierName = 'id';
    protected $contentTypeIdName = 'ext';
    protected $methodListName = 'list';
    protected $methodSingleName = '';
    protected $methodActionName = 'action';

    protected $defaultContentType = 'html';

    protected $modelMap = [
        'json' => [
            'viewModel' =>'\Zf2Rest\Mvc\View\JsonApiModel',
            'contentParsingStategies' => []
        ],
        'html' => '\Zend\View\Model\ViewModel',
        'xml'  => '\Zend\View\Model\FeedModel',
    ];

    /**
     * Set the route match/query parameter name containing the identifier
     *
     * @param  string $name
     *
     * @return self
     */
    public function setIdentifierName($name)
    {
        $this->identifierName = (string)$name;
        return $this;
    }

    /**
     * Retrieve the route match/query parameter name containing the identifier
     *
     * @return string
     */
    public function getIdentifierName()
    {
        return $this->identifierName;
    }

    /**
     * getRequestValue
     *
     * @param MvcEvent $e
     * @param string   $name
     *
     * @return mixed|null
     */
    protected function getRequestValue(MvcEvent $e, $name)
    {
        $routeMatch = $e->getRouteMatch();
        $request = $e->getRequest();

        $value = $routeMatch->getParam($name, false);
        if ($value !== false) {
            return $value;
        }

        $value = $request->getQuery()->get($name, false);
        if ($value !== false) {
            return $value;
        }

        return null;
    }

    /**
     * getMethodName
     *
     * @param MvcEvent $e
     *
     * @return string
     */
    protected function getMethodName(MvcEvent $e)
    {
        $request = $e->getRequest();

        $method = strtolower($request->getMethod());
        $countMethod = ucfirst($this->getCountMethodName($e));
        $actionMethod = ucfirst($this->methodActionName);

        return $method . $countMethod . $actionMethod;
    }

    /**
     * getCountMethodName
     *
     * @param MvcEvent $e
     *
     * @return string
     */
    protected function getCountMethodName(MvcEvent $e)
    {
        $id = $this->getRequestValue($e, $this->getIdentifierName());

        if (null === $id) {
            return $this->methodListName;
        }

        return $this->methodSingleName;
    }



    /**
     * onDispatch
     *
     * @param MvcEvent $e
     *
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function onDispatch(MvcEvent $e)
    {
        $methodName = $this->getCountMethodName($e);

        if(!method_exists($this, $methodName)){

            $response = $e->getResponse();
            $response->setStatusCode(405);
            return $response;
        }

        $routeMatch = $e->getRouteMatch();

        $routeMatch->setParam('action', $methodName);

        $return = $this->$methodName(
            $this->getRequestValue($e, ''),
            $this->getData($e)
        );

        $e->setResult($return);
        return $return;
    }

    /**
     * getAcceptContentType
     *
     * @param MvcEvent $e
     *
     * @return mixed|null
     */
    protected function getAcceptContentType(MvcEvent $e)
    {
        $contentType = $this->getRequestValue($e, $this->contentTypeIdName);

        if (null === $contentType) {
            return $this->defaultContentType;
        }

        return $contentType;

        // @todo Check Accept content-type headers
        // $request = $e->getRequest()->getHeaders()->get('Accept');
    }

    //////////////////////////////////////////////////////////////////////////////
    /**
     * getViewModel
     *
     * @return void
     */
    protected function getViewModel()
    {
        // Get view model based on c
    }

    protected function getRequestData(MvcEvent $e)
    {
        $request = $e->getRequest();

        if ($this->requestHasContentType($request, self::CONTENT_TYPE_JSON)) {
            $data = Json::decode($request->getContent(), $this->jsonDecodeType);
        } else {
            $data = $request->getPost()->toArray();
        }
    }

    protected function processBodyContent(MvcEvent $e)
    {
        $request = $e->getRequest();

        $content = $request->getContent();

        // JSON content? decode and return it.
        if ($this->requestHasContentType($request, self::CONTENT_TYPE_JSON)) {
            return Json::decode($content, $this->jsonDecodeType);
        }

        parse_str($content, $parsedParams);

        // If parse_str fails to decode, or we have a single element with key
        // 0, return the raw content.
        if (!is_array($parsedParams)
            || (1 == count($parsedParams) && isset($parsedParams[0]))
        ) {
            return $content;
        }

        return $parsedParams;
    }


}