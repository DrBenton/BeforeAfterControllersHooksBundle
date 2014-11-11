Feature:
    In order to avoid repeating myself
    As a Symfony developer
    I should be able to re-use a method, runnable before specific Controllers, with Annotations

    Background:
        Given I have a Symfony HttpKernel

    Scenario: Run a Controller class self-contained method before running its target Action
        Given I have a Controller with a @Before("beforeAction") Action Annotation
        And a self-contained method in a Controller with the following content:
            """
            public function beforeAction()
            {
                $this->controllerState[] = 'beforeAction';
            }
            """
        And I have a Symfony ControllerListener
        When I run the Controller Action through the Symfony Kernel
        Then I should have a "controllerResponse" Http Response content
        And I should have a "beforeAction" string in the Controller state

    Scenario: Run a Controller class self-contained method which short-circuit the target Action
        Given I have a Controller with a @Before("beforeAction") Action Annotation
        And a self-contained method in a Controller with the following content:
            """
            public function beforeAction()
            {
                return new Response('beforeResponse');
            }
            """
        And the target Controller Action throws an Exception
        And I have a Symfony ControllerListener
        When I run the Controller Action through the Symfony Kernel
        Then I should have a "beforeResponse" Http Response content