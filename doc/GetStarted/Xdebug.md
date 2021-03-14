# Using Xdebug

### Requirements: 

- `docker` 
- `docker-compose`

### Recommendations: 

- When in OSX, due to heavy performance issues with docker4mac I **strongly** recommend [dinghy](https://github.com/codekitchen/dinghy).

## Setup

- Start the project using `make start`. It will start the containers and setup the project. You'll end seeing something like that:

```bash
➜ docker-compose ps        
       Name                      Command               State                                             Ports                                           
---------------------------------------------------------------------------------------------------------------------------------------------------------
api_elasticsearch_1   /usr/local/bin/docker-entr ...   Up      0.0.0.0:9200->9200/tcp, 9300/tcp                                                          
api_kibana_1          /usr/local/bin/kibana-docker     Up      0.0.0.0:5601->5601/tcp                                                                    
api_mysql_1           docker-entrypoint.sh mysqld      Up      0.0.0.0:3306->3306/tcp                                                                    
api_nginx_1           nginx -g daemon off;             Up      443/tcp, 0.0.0.0:80->80/tcp                                                               
api_php_1             /sbin/tini -- supervisord  ...   Up      0.0.0.0:2323->22/tcp, 9000/tcp                                                            
api_rmq_1             docker-entrypoint.sh rabbi ...   Up      15671/tcp, 0.0.0.0:15672->15672/tcp, 25672/tcp, 4369/tcp, 5671/tcp, 0.0.0.0:5672->5672/tcp
api_workers_1         /sbin/tini -- /app/bin/con ...   Up      22/tcp, 9000/tcp  
```
- The php container for development has ssh installed and forwarding the `2323` port. We'll use this to connects to the container as a remote interpreter in our favourite IDE. In PHPStorm, i.e., go to `Languajes & Frameworks > PHP` and configure the connection to have something like:
![debugger](https://i.imgur.com/oTXsPlZ.png)
- Next you'll need to configure phpunit in your IDE to have something like:
![phpunit](https://i.imgur.com/AzFTN9k.png)
- Now you'll be able to run any php test file or phpunit config.
![run test](https://i.imgur.com/PCYXr1U.png) 
