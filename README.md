dbs2
====

Goals

 - ~~read upgrade files from configured directory~~
 - ~~sort files into x.y.z release number order~~
 - ~~break upgrade file contents into XX.YY schema revisions~~
 - ~~determine what revisions need to be executed~~
 - ~~execute revisions & update tracker~~
 - built in cli script
 - ~~configuration loaded from json file~~
 - custom configuration plugins?

TODO
 - Switch to a specific CliRunner class
 - CliRunner class sets verbosity level
 - CliRunner class figures out what to do
 -- connection test
 -- DbSmart2 table test
 -- RevisionCheck
 -- Run Updates
 -- Run Downgrades
 -- Dump Upgrade Log
 - Runner Class tests database connection
 - Runner Class tests for DbSmart2 table
 - Runner Class can setup DbSmart2 table
 - Runner Class checks which revisions need to be run
 - Runner Class runs updates
 - Runner Class updates tracker table
 - CliRunner class handles all output responsibilities
