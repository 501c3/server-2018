CREATE USER 'www-data'@'%' IDENTIFIED BY 'replacePriorProduction';
GRANT SELECT,INSERT,UPDATE,DELETE ON sales.* TO 'www-data'@'%';
GRANT SELECT ON models.* TO 'www-data'@'%';
GRANT SELECT,INSERT,UPDATE,DELETE ON access.* TO 'www-data'@'%';
GRANT SELECT,INSERT,UPDATE,DELETE ON competition.* TO 'www-data'@'%';
GRANT SELECT,INSERT,UPDATE,DELETE ON configuration.* TO 'www-data'@'%';