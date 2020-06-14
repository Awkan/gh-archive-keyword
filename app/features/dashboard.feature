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
  # TODO : Load some data in DB (one containing keyword, the others not)
  # TODO : Then response body can be check with specified number (1)
  When I request "/dashboard?keyword=ugly"
  Then the response code is 200
  And the response body contains JSON:
    """
      {
        "nbCommits": "@variableType(integer)"
      }
    """

Scenario: I get dashboard statistics by filtering on date
  # TODO : Load some data in DB (one containing date, the others not)
  # TODO : Then response body can be check with specified number (1)
  When I request "/dashboard?date=2020-01-01"
  Then the response code is 200
  And the response body contains JSON:
    """
      {
        "nbCommits": "@variableType(integer)"
      }
    """

# TODO : See https://github.com/Awkan/gh-archive-keyword/issues/9
Scenario: I get dashboard statistics by filtering on date without right format
  When I request "/dashboard?date=20200101"
  Then the response code is 500
  #Then the response code is 400
  
