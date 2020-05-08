## Kubernetes Deployment

`make minikube`

### Update

`helm upgrade cqrs -f {YOUR CUSTOM YAML FILE} etc/deploy/chart`

### Recommendations

- Use your own chart registry. i.e: https://github.com/helm/chartmuseum
- Use separated `values.yaml` per environment and concat in deployments. i.e: `helm upgrade cqrs -f production.yaml etc/deploy/chart`
- Use helm secrets plugin: https://github.com/futuresimple/helm-secrets
