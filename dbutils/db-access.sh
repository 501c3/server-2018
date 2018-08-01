#!/bin/bash
./bin/console doctrine:mapping:convert --from-database 	--em=access  --namespace=Entity\\Access\\  --force annotation ./src/  
