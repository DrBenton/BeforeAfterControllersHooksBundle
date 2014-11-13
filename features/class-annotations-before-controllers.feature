Feature:
    In order to avoid repeating myself
    As a Symfony developer
    I should be able to re-use a method, runnable before any of a Controller class Actions, with Annotations

    Background:
        Given I have a Symfony HttpKernel

    Scenario: Run a Controller class self-contained method before running any of its Actions
        Given I have a Controller class with a @Before("beforeAction") Class Annotation
        And a self-contained method in a Controller with the following content:
            """
            public function beforeAction()
            {
                $this->controllerState[] = 'beforeAnyAction';
            }
            """
        And I have a Symfony ControllerListener
        When I run the Controller Action through the Symfony Kernel
        Then I should have a "controllerResponse" Http Response content
        And I should have "beforeAnyAction/controllerAction" strings in the Controller state

    Scenario: Run a Controller class self-contained method before running any of its Actions, with a params transmission
        Given I have a Controller class with a @Before("beforeAction", args={"Good morning", "Mr. Phelps"}) Class Annotation
        And a self-contained method in a Controller with the following content:
            """
            public function beforeAction($goodMorning, $who)
            {
                $this->controllerState[] = $goodMorning.' '.$who;
            }
            """
        And I have a Symfony ControllerListener
        When I run the Controller Action through the Symfony Kernel
        Then I should have a "controllerResponse" Http Response content
        And I should have "Good morning Mr. Phelps/controllerAction" strings in the Controller state

    Scenario: Run multiple Controller class self-contained methods before running any of its target Action, with a params transmission
        Given I have a Controller class with a @Before("beforeAction", args={"Good morning", "Mr. Phelps"}) Class Annotation
        And a @Before("beforeAction", args={"Well", "nobody’s perfect!"}) Class Annotation
        And a self-contained method in a Controller with the following content:
            """
            public function beforeAction($arg1, $arg2)
            {
                $this->controllerState[] = $arg1.' '.$arg2;
            }
            """
        And I have a Symfony ControllerListener
        When I run the Controller Action through the Symfony Kernel
        Then I should have a "controllerResponse" Http Response content
        And I should have "Good morning Mr. Phelps/Well nobody’s perfect!/controllerAction" strings in the Controller state

    Scenario: Run a Controller class self-contained method which short-circuit any of its Actions
        Given I have a Controller class with a @Before("beforeAction") Class Annotation
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

    Scenario: Run a Symfony Service method before running any of the Controller Actions
        Given I have a Symfony test Service
        And a Controller class with a @Before("@test_service::beforeHook") Class Annotation
        And a Symfony ControllerListener
        When I run the Controller Action through the Symfony Kernel
        Then I should have a "controllerResponse" Http Response content
        And I should have a "beforeHookTriggered" string in the Symfony test Service state

    Scenario: Run a Symfony Service method which short-circuit any of the Controller Actions
        Given I have a Symfony test Service
        And a Controller class with a @Before("@test_service::beforeHookWithResponse") Class Annotation
        And the target Controller Action throws an Exception
        And a Symfony ControllerListener
        When I run the Controller Action through the Symfony Kernel
        Then I should have a "serviceBeforeHook" Http Response content

    Scenario: Run a Symfony Service method before running any of the Controller Actions, with a params transmission
        Given I have a Symfony test Service
        And a Controller class with a @Before("@test_service::beforeHookWithArgs", args={"scalar", {1, 2}}) Class Annotation
        And a Symfony ControllerListener
        When I run the Controller Action through the Symfony Kernel
        Then I should have a "controllerResponse" Http Response content
        And I should have a "beforeHookTriggered: args=["scalar",[1,2]]" string in the Symfony test Service state
