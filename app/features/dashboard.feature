Feature: Deal with dashboard endpoints

Scenario: I get dashboard statistiques
  When I request "/dashboard"
  Then the response code is 200
  And the response body contains JSON:
    """
      {
        "nbCommits": "@variableType(integer)"
      }
    """
