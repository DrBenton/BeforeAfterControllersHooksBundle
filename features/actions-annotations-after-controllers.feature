Feature:
    In order to avoid repeating myself
    As a Symfony developer
    I should be able to re-use a method, runnable after specific Controllers, with Annotations

    Background:
        Given I have a Symfony HttpKernel

    Scenario: Run a Controller class self-contained method after running its target Action
        Given I have a Controller with a @After("afterAction") Action Annotation
        And a self-contained method in a Controller with the following content:
            """
            public function afterAction()
            {
                $this->controllerState[] = 'afterAction';
            }
            """
        And I have a Symfony ResponseListener
        When I run the Controller Action through the Symfony Kernel
        Then I should have a "controllerResponse" Http Response content
        And I should have "controllerAction/afterAction" strings in the Controller state

    Scenario: Run a Controller class self-contained method after running its target Action, with a params transmission
        Given I have a Controller with a @After("afterAction", args={"Good morning", "Mr. Phelps"}) Action Annotation
        And a self-contained method in a Controller with the following content:
            """
            public function afterAction($goodMorning, $who)
            {
                $this->controllerState[] = $goodMorning.' '.$who;
            }
            """
        And I have a Symfony ResponseListener
        When I run the Controller Action through the Symfony Kernel
        Then I should have a "controllerResponse" Http Response content
        And I should have "controllerAction/Good morning Mr. Phelps" strings in the Controller state

    Scenario: Run a Controller class self-contained method which modifies the target Action Response
        Given I have a Controller with a @After("afterAction", args={"%response%"}) Action Annotation
        And a self-contained method in a Controller with the following content:
            """
            public function afterAction(Response $response)
            {
                $response->setContent('modified Response: ' . $response->getContent());
            }
            """
        And I have a Symfony ResponseListener
        When I run the Controller Action through the Symfony Kernel
        Then I should have a "modified Response: controllerResponse" Http Response content

    Scenario: Run multiple Controller class self-contained methods after running its target Action, with a params transmission
        Given I have a Controller with a @After("afterAction", args={"Hello", "Plum"}) Action Annotation
        And a @After("afterActionWithResponseModification", args={"P.G.", "%response%", "Wodehouse"}) Action Annotation
        And a self-contained method in a Controller with the following content:
            """
            public function afterAction($goodMorning, $who)
            {
                $this->controllerState[] = $goodMorning.' '.$who;
            }
            """
        And a self-contained method with the following content:
            """
            public function afterActionWithResponseModification($firstName, Response $response, $lastName)
            {
                $this->controllerState[] = $firstName.' '.$lastName;
                $response->setContent('modified Response: ' . $response->getContent());
            }
            """
        And I have a Symfony ResponseListener
        When I run the Controller Action through the Symfony Kernel
        Then I should have a "modified Response: controllerResponse" Http Response content
        And I should have "controllerAction/Hello Plum/P.G. Wodehouse" strings in the Controller state

    Scenario: Run a Symfony Service method after running the Controller target Action
        Given I have a Symfony test Service
        And a Controller with a @After("@test_service::afterHook") Action Annotation
        And a Symfony ResponseListener
        When I run the Controller Action through the Symfony Kernel
        Then I should have a "controllerResponse" Http Response content
        And I should have a "afterHookTriggered" string in the Symfony test Service state

    Scenario: Run a Symfony Service method which modifies the target Action Response
        Given I have a Symfony test Service
        And a Controller with a @After("@test_service::afterHookWithResponseModification", args={"%response%"}) Action Annotation
        And a Symfony ResponseListener
        When I run the Controller Action through the Symfony Kernel
        Then I should have a "controllerResponse + serviceHookResponse" Http Response content

    Scenario: Run a Symfony Service method after running the Controller target Action, with a params transmission
        Given I have a Symfony test Service
        And a Controller with a @After("@test_service::afterHookWithArgs", args={"scalar", {1, 2}}) Action Annotation
        And a Symfony ResponseListener
        When I run the Controller Action through the Symfony Kernel
        Then I should have a "controllerResponse" Http Response content
        And I should have a "afterHookTriggered: args=["scalar",[1,2]]" string in the Symfony test Service state
