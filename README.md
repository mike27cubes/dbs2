dbs2
====

Goals

 - [x] read upgrade files from configured directory
 - [x] sort files into x.y.z release number order
 - [x] break upgrade file contents into XX.YY schema revisions
 - [x] determine what revisions need to be executed
 - [x] execute revisions & update tracker
 - [x] built in cli script
 - [x] configuration loaded from json file
 - [x] custom configuration via script
 - [ ] Runner actions (tests, updates, downgrades, etc) use response object to track status and messaging
 - [ ] Documentation

TODO
 - [x] Switch to a specific CliRunner class
 - [x] CliRunner class sets verbosity level
 - [x] Runner class figures out what to do
   - [x] connection test
   - [x] DbSmart2 table test
   - [x] RevisionCheck
   - [x] Run Updates
   - [x] Run Downgrades
   - [ ] Dump Upgrade Log
 - [x] Runner Class tests database connection
 - [x] Runner Class tests for DbSmart2 table
 - [x] Runner Class can setup DbSmart2 table
 - [x] Runner Class checks which revisions need to be run
 - [x] Runner Class runs updates
 - [x] Runner Class updates tracker table
 - [x] CliRunner class handles all output responsibilities
