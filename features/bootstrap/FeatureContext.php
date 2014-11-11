<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Doctrine\Common\Annotations\AnnotationReader;
use DrBenton\Bundle\BeforeAfterControllersHooksBundle\EventListener\ControllerListener;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class FeatureContext implements Context, SnippetAcceptingContext
{
    /** @var \Symfony\Component\DependencyInjection\Container **/
    protected $container;
    /** @var \Symfony\Component\HttpFoundation\Request **/
    protected $request;
    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface **/
    protected $kernel;
    /** @var \Symfony\Component\HttpFoundation\Response **/
    protected $kernelResponse;

    /** @var \DrBenton\Bundle\BeforeAfterControllersHooksBundle\EventListener\ControllerListener **/
    protected $controllerListener;
    /** @var \Symfony\Component\HttpKernel\Event\FilterControllerEvent **/
    protected $filterControllerEvent;

    protected $targetControllerThrowsAnException = false;
    protected $displayControllersCode = false;
    protected $controllerDefinition = array();
    protected $filesToClean = array();

    public function __construct()
    {
        $this->displayControllersCode = true;
    }

    /**
     * @Given /^(?:I have )?a Symfony HttpKernel$/
     */
    public function aSymfonyHttpkernel()
    {
        $this->container = new Container();
        $this->kernel = new HttpKernel(new EventDispatcher(), new ControllerResolver());
    }

    /**
     * @Given /^(?:I have )?a Controller with a (?P<annotation>.+) Action Annotation$/
     */
    public function aControllerWithActionAnnotation($annotation)
    {
        $this->controllerDefinition['actionAnnotations'][] = '     * ' . $annotation . PHP_EOL;
    }

    /**
     * @Given /^(?:I have )?a Symfony ControllerListener$/
     */
    public function iHaveASymfonyControllerlistener()
    {
        $this->controllerListener = new ControllerListener(new AnnotationReader());
        $this->controllerListener->setContainer($this->container);
    }

    /**
     * @Given the target Controller Action throws an Exception
     */
    public function theTargetControllerActionThrowsAnException()
    {
        $this->targetControllerThrowsAnException = true;
    }


    /**
     * @Given a self-contained method in a Controller with the following content:
     */
    public function aSelfContainedMethodInAControllerWithTheFollowingContent(PyStringNode $string)
    {
        $this->controllerDefinition['methods'][] = implode(PHP_EOL . '    ', $string->getStrings());
    }

    /**
     * @When /^(?:I )?run the Controller Action through the Symfony Kernel$/
     */
    public function iRunTheControllerActionThroughTheSymfonyKernel()
    {
        list($controllerClassName, $controllerFilePath) = $this->createController();
        require_once $controllerFilePath;
        $this->controllerInstance = new $controllerClassName();
        $targetControllerAction = array($this->controllerInstance, 'indexAction');

        $this->request = new Request();

        if (null !== $this->controllerListener) {
            $this->filterControllerEvent = new FilterControllerEvent($this->kernel, $targetControllerAction, $this->request, HttpKernelInterface::MASTER_REQUEST);
            $this->controllerListener->onKernelController($this->filterControllerEvent);
            $targetControllerAction = $this->filterControllerEvent->getController();
        }

        $this->request->attributes->set('_controller', $targetControllerAction);
        $this->kernelResponse = $this->kernel->handle($this->request);
    }

    /**
     * @Then /^(?:I )?should have a "(?P<response>[^"]+)" Http Response content$/
     */
    public function iShouldHaveAHttpResponseContent($response)
    {
        if (null === $this->kernelResponse) {
            throw new \LogicException('You have to run the Controller Action before using "I should have a Http Response content"!');
        }

        $kernelResponseContent = $this->kernelResponse->getContent();
        if ($response !== $kernelResponseContent) {
            throw new \Exception(
                sprintf('The Http Response content "%s" doesn\'t match the expected response "%s"!', $kernelResponseContent, $response)
            );
        }
    }

    /**
     * @Then /^(?:I )?should have a "(?P<string>[^"]+)" string in the Controller state$/
     */
    public function iShouldHaveAStringInTheControllerState($string)
    {
        if (null === $this->controllerInstance) {
            throw new \LogicException('You have to run the Controller Action before using "I should have a "..." string in the Controller state"!');
        }

        $controllerState = $this->controllerInstance->controllerState;
        if (!in_array($string, $controllerState)) {
            throw new \Exception(
                sprintf('The Controller state array "%s" doesn\'t contain the expected string "%s"!', json_encode($controllerState), $string)
            );
        }
    }

    /**
     * @BeforeFeature
     */
    static public function beforeFeature()
    {
        // trigger the autoloading of our Controllers hooks annotation
        class_exists('DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation\BeforeControllerHook');
        class_exists('DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation\AfterControllerHook');
    }

    /**
     * @AfterScenario
     */
    public function cleanAfterScenario()
    {
        $this->container = null;
        $this->kernel = null;
        $this->request = null;
        $this->kernelResponse = null;
        $this->controllerDefinition = array();
        $this->controllerInstance= null;
        $this->targetControllerThrowsAnException = false;

        while($fileToClean = array_shift($this->filesToClean)) {
            unlink($fileToClean);
        }
    }

    protected function createController()
    {
        $defs = $this->controllerDefinition;

        // Class content setup
        if ($this->targetControllerThrowsAnException) {
            $controllerActionContent = 'throw new \\Exception(\'This Controller Action should not be triggered!\');';
        } else {
            $controllerActionContent = 'return new Response(\'controllerResponse\');';
        }
        if (isset($defs['classAnnotations'])) {
            $classAnnotations = '/**' . PHP_EOL . implode('', $defs['classAnnotations']) . '     */';
        } else {
            $classAnnotations = '';
        }
        if (isset($defs['actionAnnotations'])) {
            $controllerActionAnnotations = '/**' . PHP_EOL . implode('', $defs['actionAnnotations']) . '     */';
        } else {
            $controllerActionAnnotations = '';
        }
        if (isset($defs['methods'])) {
            $controllerMethods = PHP_EOL . '    ' . implode(PHP_EOL, $defs['methods']);
        } else {
            $controllerMethods = '';
        }
        $controllerId = uniqid();
        $controllerClassName = "TestController${controllerId}";

        // All right, now we can create our brand new PHP Controller class file!
        $controllerContent = <<<END
<?php
use Symfony\Component\HttpFoundation\Response;
use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation\BeforeControllerHook as Before;
use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation\AfterControllerHook as After;
{$classAnnotations}
class {$controllerClassName}
{
    public \$controllerState = array();

    {$controllerActionAnnotations}
    public function indexAction() {
        ${controllerActionContent}
    }
    {$controllerMethods}
}
END;
        if ($this->displayControllersCode) {
            echo $controllerContent;
        }

        $controllerFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $controllerClassName . '.php';
        file_put_contents($controllerFilePath, $controllerContent);
        $this->filesToClean[] = $controllerFilePath;

        return array($controllerClassName, $controllerFilePath);
    }
}