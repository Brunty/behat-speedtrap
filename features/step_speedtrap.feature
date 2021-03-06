Feature: Slow steps are logged at the end of the test suite running
  In order to identify where I can improve the speed of my steps
  As a developer
  I need to be able to identify slow steps

  Background:
    Given I have the context:
    """
    <?php
    use Behat\Behat\Context\Context;
    use Behat\Behat\Context\SnippetAcceptingContext;
    class FeatureContext implements Context, SnippetAcceptingContext
    {
        /**
         * @Given I wait for :seconds second(s)
         */
        function iWaitFor(float $seconds)
        {
            sleep($seconds);
        }
    }
    """


  Scenario: Steps above the threshold are logged
    Given I have the feature:
    """
    Feature: Slow steps are logged
    Scenario: This step should be logged
      When I wait for 2 seconds
    """
    And I have the configuration:
    """
    default:
      extensions:
        Brunty\Behat\SpeedtrapExtension:
            scenario_threshold: 3000
            step_threshold: 500
    """
    When I run behat
    Then I should see:
    """
    The following steps were above your configured threshold: 500ms
    TIME to run step in features/feature.feature:3 - When I wait for 2 seconds
    """

  Scenario: Steps below the threshold are not logged
    Given I have the feature:
    """
    Feature: Slow steps are logged
    Scenario: This step should not be logged
      When I wait for 0.1 seconds
    """
    And I have the configuration:
    """
    default:
      extensions:
        Brunty\Behat\SpeedtrapExtension:
            scenario_threshold: 3000
            step_threshold: 500
    """
    When I run behat
    Then I should not see:
    """
    The following steps were above your configured threshold: 500ms
    """

  Scenario: Setting a step threshold of zero does not output long steps
    Given I have the feature:
    """
    Feature: Slow steps are logged
    Scenario: This step should not be logged
      When I wait for 2 seconds
    """
    And I have the configuration:
    """
    default:
      extensions:
        Brunty\Behat\SpeedtrapExtension:
            scenario_threshold: 500
            step_threshold: 0
    """
    When I run behat
    Then I should not see:
    """
    The following steps were above your configured threshold
    """

  Scenario: Omitting the step threshold (i.e. using default value) does not output long steps
    Given I have the feature:
    """
    Feature: Slow steps are logged
    Scenario: This step should not be logged
      When I wait for 2 seconds
    """
    And I have the configuration:
    """
    default:
      extensions:
        Brunty\Behat\SpeedtrapExtension:
            scenario_threshold: 500
    """
    When I run behat
    Then I should not see:
    """
    The following steps were above your configured threshold
    """
