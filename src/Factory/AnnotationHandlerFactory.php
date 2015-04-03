<?php
/**
 * File AnnotationHandlerFactory.php
 */

namespace Tebru\Retrofit\Factory;

use Tebru\Retrofit\Annotation\HttpRequest;
use Tebru\Retrofit\Handler\BodyHandler;
use Tebru\Retrofit\Handler\HeaderHandler;
use Tebru\Retrofit\Handler\HeadersHandler;
use Tebru\Retrofit\Handler\HttpRequestHandler;
use Tebru\Retrofit\Handler\JsonBodyHandler;
use Tebru\Retrofit\Handler\PartHandler;
use Tebru\Retrofit\Handler\QueryHandler;
use Tebru\Retrofit\Handler\QueryMapHandler;
use Tebru\Retrofit\Handler\ReturnsHandler;
use UnexpectedValueException;

/**
 * Class AnnotationHandlerFactory
 *
 * @author Nate Brunette <n@tebru.net>
 */
class AnnotationHandlerFactory
{
    const CLASS_HTTP_REQUEST = 'Tebru\Retrofit\Annotation\HttpRequest';
    const CLASS_QUERY = 'Tebru\Retrofit\Annotation\Query';
    const CLASS_QUERY_MAP = 'Tebru\Retrofit\Annotation\QueryMap';
    const CLASS_PART = 'Tebru\Retrofit\Annotation\Part';
    const CLASS_HEADER = 'Tebru\Retrofit\Annotation\Header';
    const CLASS_HEADERS = 'Tebru\Retrofit\Annotation\Headers';
    const CLASS_BODY = 'Tebru\Retrofit\Annotation\Body';
    const CLASS_JSON_BODY = 'Tebru\Retrofit\Annotation\JsonBody';
    const CLASS_RETURNS = 'Tebru\Retrofit\Annotation\Returns';

    /**
     * Create an annotation handler
     *
     * @param $annotation
     * @return null|BodyHandler|HeaderHandler|HeadersHandler|HttpRequestHandler|PartHandler|QueryHandler|QueryMapHandler
     */
    public function make($annotation)
    {
        $handler = null;
        switch (get_class($annotation)) {
            case ($annotation instanceof HttpRequest):
                return new HttpRequestHandler();
                break;
            case self::CLASS_QUERY:
                $handler = new QueryHandler();
                break;
            case self::CLASS_QUERY_MAP:
                $handler = new QueryMapHandler();
                break;
            case self::CLASS_PART:
                $handler = new PartHandler();
                break;
            case self::CLASS_HEADER:
                $handler = new HeaderHandler();
                break;
            case self::CLASS_HEADERS:
                $handler = new HeadersHandler();
                break;
            case self::CLASS_BODY:
                $handler = new BodyHandler();
                break;
            case self::CLASS_JSON_BODY:
                $handler = new JsonBodyHandler();
                break;
            case self::CLASS_RETURNS:
                $handler = new ReturnsHandler();
                break;
            default:
                throw new UnexpectedValueException('Attempted to create annotation handler but did not understand annotation');
                break;
        }

        return $handler;
    }
}