#!/bin/bash
./bin/console doctrine:mapping:convert --from-database 	--em=configuration  --namespace=Entity\\Configuration\\  --filter Competition  annotation ./src/ 
 
