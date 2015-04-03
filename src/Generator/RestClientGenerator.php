<?php
/**
 * File RestClientGenerator.php
 */

namespace Tebru\Retrofit\Generator;

use LogicException;
use PhpParser\Lexer;
use ReflectionParameter;
use Tebru;
use Tebru\Retrofit\Factory\AnnotationHandlerFactory;
use Tebru\Retrofit\Model\ClassModel;
use Tebru\Retrofit\Model\Method;
use Tebru\Retrofit\Provider\ClassMetaDataProvider;
use Tebru\Retrofit\Annotation\Headers;
use Tebru\Retrofit\Annotation\AnnotationToVariableMap;
use Tebru\Retrofit\Annotation\HttpRequest;
use Tebru\Retrofit\Provider\GeneratedClassMetaDataProvider;
use Twig_Environment;

/**
 * Class RestClientGenerator
 *
 * Generates a REST client based on an interface name
 *
 * @author Nate Brunette <n@tebru.net>
 */
class RestClientGenerator
{
    /**
     * @var Twig_Environment $twig
     */
    private $twig;

    /**
     * Creates an annotation handler
     *
     * @var AnnotationHandlerFactory
     */
    private $annotationHandlerFactory;

    /**
     * Constructor
     *
     * @param Twig_Environment $twig
     * @param AnnotationHandlerFactory $annotationHandlerFactory
     */
    public function __construct(Twig_Environment $twig, AnnotationHandlerFactory $annotationHandlerFactory)
    {
        $this->twig = $twig;
        $this->annotationHandlerFactory = $annotationHandlerFactory;
    }

    /**
     * Generate a REST client based on an interface
     *
     * @param ClassMetaDataProvider $classMetaDataProvider
     * @param GeneratedClassMetaDataProvider $generatedClassMetaDataProvider
     * @return string
     */
    public function generate(ClassMetaDataProvider $classMetaDataProvider, GeneratedClassMetaDataProvider $generatedClassMetaDataProvider)
    {
        // get interface methods
        $parsedMethods = $classMetaDataProvider->getInterfaceMethods();

        // loop through class annotations
        $class = new ClassModel();
        foreach ($classMetaDataProvider->getClassAnnotations() as $classAnnotation) {
            if ($classAnnotation instanceof Headers) {
                $class->addHeaders($classAnnotation->getHeaders());
            }
        }

        // loop over class methods
        foreach ($classMetaDataProvider->getReflectionMethods() as $index => $classMethod) {
            $parsedMethod = $parsedMethods[$index];
            $parameters = $classMethod->getParameters();

            $method = new Method();
            $method->setName($classMetaDataProvider->getMethodName($parsedMethod));
            $method->setDeclaration($classMetaDataProvider->getMethodDeclaration($parsedMethod));

            // loop through method annotations
            foreach ($classMetaDataProvider->getMethodAnnotations($classMethod) as $methodAnnotation) {
                if ($methodAnnotation instanceof HttpRequest) {
                    foreach ($methodAnnotation->getParameters() as $parameter) {
                        $this->assertParameter($parameters, $parameter);
                    }
                }

                if ($methodAnnotation instanceof AnnotationToVariableMap) {
                    $this->assertParameter($parameters, $methodAnnotation->getName());
                }

                $handler = $this->annotationHandlerFactory->make($methodAnnotation);
                $handler->handle($method, $methodAnnotation);

            }

            $methodOptions = $method->getOptions();
            $methodParts = $method->getParts();
            Tebru\assert(null !== $method->getType(), new LogicException('Request method must be set'));
            Tebru\assert(
                empty($methodOptions['body']) || empty($methodParts),
                new LogicException('Body and part cannot both be set')
            );

            $class->addMethod($method);
        }

        // render class as string
        $template = $this->twig->loadTemplate('service.php.twig');

        return $template->render([
            'uses' => $classMetaDataProvider->getUseStatements(),
            'namespace' => $generatedClassMetaDataProvider->getNamespaceFull(),
            'className' => $classMetaDataProvider->getInterfaceNameShort(),
            'interfaceName' => $classMetaDataProvider->getInterfaceNameFull(),
            'methods' => $class->getMethods(),
        ]);
    }

    /**
     * Asserts that a parameter exists on the method
     *
     * @param array $parameters
     * @param string $name
     * @return null
     */
    private function assertParameter(array $parameters, $name)
    {
        /** @var ReflectionParameter $parameter */
        foreach ($parameters as $parameter) {
            if ($parameter->getName() === $name) {
                return null;
            }
        }

        throw new LogicException('Parameter not found');
    }
}
