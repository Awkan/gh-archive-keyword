Feature: Deal with dashboard endpoints

# Getting data
Scenario: I get dashboard statistics
  When I request "/dashboard"
  Then the response code is 200
  And the response body contains JSON:
    """
      {
        "nbCommits": "@variableType(integer)"
      }
    """

# Filtering
Scenario: I get dashboard statistics by filtering on keyword
  # TODO : Load 2 data in DB (one containing keyword, the other not)
  # TODO : Then response body can be check with specified number
  When I request "/dashboard?keyword=ugly"
  Then the response code is 200
  And the response body contains JSON:
    """
      {
        "nbCommits": "@variableType(integer)"
      }
    """
