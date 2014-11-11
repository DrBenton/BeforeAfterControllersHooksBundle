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

    Scenario: Run a Symfony Service method before running the Controller target Action
        Given I have a Symfony test Service
        And a Controller with a @Before("@test_service::beforeHook") Action Annotation
        And a Symfony ControllerListener
        When I run the Controller Action through the Symfony Kernel
        Then I should have a "controllerResponse" Http Response content
        And I should have a "beforeHookTriggered" string in the Symfony test Service state

    Scenario: Run a Symfony Service method which short-circuit the target Action
        Given I have a Symfony test Service
        And a Controller with a @Before("@test_service::beforeHookWithResponse") Action Annotation
        And the target Controller Action throws an Exception
        And a Symfony ControllerListener
        When I run the Controller Action through the Symfony Kernel
        Then I should have a "serviceBeforeHook" Http Response content

    Scenario: Run a Controller class self-contained method before running its target Action, with a params transmission
        Given I have a Controller with a @Before("beforeAction", args={"Good morning", "Mr. Phelps"}) Action Annotation
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
        And I should have a "Good morning Mr. Phelps" string in the Controller state

    Scenario: Run a Symfony Service method before running the Controller target Action, with a params transmission
        Given I have a Symfony test Service
        And a Controller with a @Before("@test_service::beforeHookWithArgs", args={"scalar", {1, 2}}) Action Annotation
        And a Symfony ControllerListener
        When I run the Controller Action through the Symfony Kernel
        Then I should have a "controllerResponse" Http Response content
        And I should have a "beforeHookTriggered: args=["scalar",[1,2]]" string in the Symfony test Service state
