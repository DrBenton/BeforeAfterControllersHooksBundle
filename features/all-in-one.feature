@all-in-one
Feature:
    In order to avoid repeating myself
    As a Symfony developer
    I should be able to add Before/After hooks on Controllers, with Annotations

    Scenario:
        Given I have a Symfony HttpKernel
        # Lets take some Controller class "@Before" Annotations...
        And I have a Controller class with a @Before("beforeAction", args={"beforeClass1"}) Class Annotation
        And a @Before("beforeAction", args={"beforeClass", "2"}) Class Annotation
        And a @Before("@test_service::beforeHookWithArgs", args={"beforeClass3"}) Class Annotation
        And a @Before("beforeAction", args={"beforeClass4"}) Class Annotation
        # ...with some Controller class "@After" Annotations...
        And a @After("afterAction", args={"afterClass1"}) Class Annotation
        And a @After("afterActionWithResponseModification", args={"afterClass2", "%response%"}) Class Annotation
        And a @After("@test_service::afterHookWithArgs", args={"afterClass3"}) Class Annotation
        And a @After("afterAction", args={"afterClass4"}) Class Annotation
        # Mix it with some Controller Action "@After" Annotations...
        # (yup, we test that even placed before @Before Annotations, @After Annotations run... after :-)
        And a @After("afterAction", args={"afterAction1"}) Action Annotation
        And a @After("afterActionWithResponseModification", args={"afterAction2", "%response%"}) Action Annotation
        And a @After("@test_service::afterHookWithResponseModificationWithArgs", args={"%response%", "afterAction3"}) Action Annotation
        And a @After("afterAction", args={"afterAction4"}) Action Annotation
        # ...add a pinch of Controller Action "@Before" Annotations...
        And a @Before("beforeAction", args={"beforeAction1"}) Action Annotation
        And a @Before("beforeAction", args={"beforeAction", "2"}) Action Annotation
        And a @Before("@test_service::beforeHookWithArgs", args={"beforeAction3"}) Action Annotation
        And a @Before("beforeAction", args={"beforeAction4"}) Action Annotation
        # Add some self-container methods...
        And a self-contained method in a Controller with the following content:
            """
            public function beforeAction()
            {
                $this->controllerState[] = implode('', func_get_args());
            }
            """
        And a self-contained method in a Controller with the following content:
            """
            public function afterAction()
            {
                $this->controllerState[] = implode('', func_get_args());
            }
            """
        And a self-contained method with the following content:
            """
            public function afterActionWithResponseModification($arg, Response $response)
            {
                $this->controllerState[] = $arg;
                $response->setContent('modified Response: ' . $response->getContent());
            }
            """
        And I have a Symfony test Service
        And I have a Symfony ControllerListener
        And I have a Symfony ResponseListener
        # Go!
        When I run the Controller Action through the Symfony Kernel
        # Now, prepare for some looooong strings, my friend...
        Then I should have a "modified Response: modified Response: controllerResponse + serviceHookResponse; args=["afterAction3"]" Http Response content
        And I should have "beforeClass1/beforeClass2/beforeClass4/beforeAction1/beforeAction2/beforeAction4/controllerAction/afterClass1/afterClass2/afterClass4/afterAction1/afterAction2/afterAction4" strings in the Controller state
        And I should have "beforeHookTriggered: args=["beforeClass3"]/beforeHookTriggered: args=["beforeAction3"]/afterHookTriggered: args=["afterClass3"]" strings in the Symfony test Service state