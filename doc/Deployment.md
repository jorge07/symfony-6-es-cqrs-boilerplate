## Kubernetes Deployment

Only in Minikube: `eval $(minikube docker-env)`

### Build docker images:

`docker-compose -f docker-compose.prod.yml build`

### Install chart dependencies

`helm dep up etc/deploy/chart`

### Install chart

`helm install --name cqrs etc/deploy/chart`

### Update

`helm upgrade cqrs -f {YOUR CUSTOM YAML FILE} etc/deploy/chart`

### Recommendations

- Use your own chart registry. i.e: https://github.com/helm/chartmuseum
- Use separated `values.yaml` per environment and concat in deployments. i.e: `helm upgrade cqrs -f production.yaml etc/deploy/chart`
- Use helm secrets plugin: https://github.com/futuresimple/helm-secrets
