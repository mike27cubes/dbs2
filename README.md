dbs2
====

Goals

 - [x] read upgrade files from configured directory
 - [x] sort files into x.y.z release number order
 - [x] break upgrade file contents into XX.YY schema revisions
 - [x] determine what revisions need to be executed
 - [x] execute revisions & update tracker
 - [ ] built in cli script
 - [x] configuration loaded from json file
 - [x] custom configuration via script

TODO
 - [ ] Switch to a specific CliRunner class
 - [ ] CliRunner class sets verbosity level
 - [ ] CliRunner class figures out what to do
   - [ ] connection test
   - [ ] DbSmart2 table test
   - [ ] RevisionCheck
   - [ ] Run Updates
   - [ ] Run Downgrades
   - [ ] Dump Upgrade Log
 - [ ] Runner Class tests database connection
 - [ ] Runner Class tests for DbSmart2 table
 - [ ] Runner Class can setup DbSmart2 table
 - [ ] Runner Class checks which revisions need to be run
 - [ ] Runner Class runs updates
 - [ ] Runner Class updates tracker table
 - [ ] CliRunner class handles all output responsibilities
