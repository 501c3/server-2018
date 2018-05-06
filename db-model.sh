#!/bin/bash
./bin/console doctrine:mapping:convert --from-database 	-vvv --em=models --namespace=Entity\\Models\\  --no-debug annotation ./src/ 
 
