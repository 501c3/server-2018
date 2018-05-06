Feature: Upload competition model and sales data
  In order to initialize the competition model and sales data
  As a database administrator
  I need to upload competition model and sales data and administrator permissions


Scenario: Upload model primitives
  Given There is a file name "../scripts/dancesport-primitives.yml"
  When I run  "bin/console competition:model:primitives ../scripts/dancesport-primitives.yml"
  Then I should see "Ready to build!S"