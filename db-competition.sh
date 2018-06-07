#!/bin/bash
./bin/console doctrine:mapping:convert --from-database 	-vvv --em=competition --namespace=Entity\\Competition\\  --no-debug --filter Player annotation ./src/
 
