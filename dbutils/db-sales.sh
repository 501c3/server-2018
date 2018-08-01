#!/bin/bash
./bin/console doctrine:mapping:convert --from-database 	--em=sales  --namespace=Entity\\Sales\\  --no-debug annotation ./src/ --filter=Settings --force
 
